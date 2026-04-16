<?php
// secciones/api/guardar_ausencia.php
// Crea una solicitud de ausencia (VACACIONES, MEDICO, LIBRE, FESTIVO)
// directamente desde el calendario del empleado, sin pasar por temporales.
session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$idUsuario = (int)$_SESSION['usuario']['id'];
$idEmpresa = (int)$_SESSION['empresa_activa'];

$data = json_decode(file_get_contents('php://input'), true);

$tipoJornada  = strtoupper(trim($data['tipo_jornada']  ?? ''));
$fechaInicio  = $data['fecha_inicio']  ?? '';
$fechaFin     = $data['fecha_fin']     ?? $fechaInicio;
$observaciones= trim($data['observaciones'] ?? '');
$dias         = $data['dias']          ?? [1,2,3,4,5];   // días de semana a incluir (1=Lun)

// Validar tipo
$tiposAusencia = ['VACACIONES', 'MEDICO', 'LIBRE', 'FESTIVO'];
if (!in_array($tipoJornada, $tiposAusencia)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de ausencia no válido']);
    exit;
}

// Validar fechas
if (!$fechaInicio || !strtotime($fechaInicio)) {
    echo json_encode(['success' => false, 'message' => 'Fecha de inicio no válida']);
    exit;
}

try {
    $pdo->beginTransaction();

    // ── Determinar tipo de solicitud ──────────────────────────
    $tipoSolicitud = match($tipoJornada) {
        'VACACIONES' => 'VACACIONES',
        'MEDICO'     => 'MEDICO',
        default      => 'HORARIO_MES',
    };

    $tipoLabel = match($tipoJornada) {
        'VACACIONES' => 'vacaciones',
        'MEDICO'     => 'horas médicas',
        'LIBRE'      => 'día(s) libre',
        'FESTIVO'    => 'festivo(s)',
        default      => 'ausencia',
    };

    $obsGeneral = $observaciones ?: "Solicitud de $tipoLabel del $fechaInicio" . ($fechaFin !== $fechaInicio ? " al $fechaFin" : '');

    // ── Insertar solicitud ─────────────────────────────────────
    $pdo->prepare("
        INSERT INTO SOLICITUDES_HORARIO
            (id_usuario, id_empresa, tipo_solicitud, fecha_inicio, fecha_fin, estado, observaciones)
        VALUES (?, ?, ?, ?, ?, 'PENDIENTE', ?)
    ")->execute([$idUsuario, $idEmpresa, $tipoSolicitud, $fechaInicio, $fechaFin, $obsGeneral]);

    $idSolicitud = $pdo->lastInsertId();

    // ── Generar detalle por rango de fechas ───────────────────
    $stmtD = $pdo->prepare("
        INSERT INTO DETALLE_SOLICITUD_HORARIO
            (id_solicitud, fecha, orden_dia, tipo_jornada, observaciones)
        VALUES (?, ?, 1, ?, ?)
    ");

    $fechaActual = new DateTime($fechaInicio);
    $fechaFinal  = new DateTime($fechaFin);
    $insertados  = 0;

    while ($fechaActual <= $fechaFinal) {
        $dow = (int)$fechaActual->format('N'); // 1=Lun, 7=Dom
        if (in_array($dow, $dias)) {
            $stmtD->execute([
                $idSolicitud,
                $fechaActual->format('Y-m-d'),
                $tipoJornada,
                $observaciones ?: null,
            ]);
            $insertados++;
        }
        $fechaActual->modify('+1 day');
    }

    if ($insertados === 0) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Ningún día del rango coincide con los días seleccionados']);
        exit;
    }

    // ── Notificar admins ───────────────────────────────────────
    $stmtAdmins = $pdo->prepare(
        "SELECT id_usuario FROM EMPRESA_USUARIO WHERE id_empresa = ? AND admin = 1 AND activo = 1"
    );
    $stmtAdmins->execute([$idEmpresa]);
    $admins = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($admins)) {
        $nombre = $_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellidos'];
        $msg    = " Nueva solicitud de $tipoLabel de $nombre";
        $stmtN  = $pdo->prepare(
            "INSERT INTO NOTIFICACIONES (id_usuario, mensaje, tipo) VALUES (?, ?, 'nueva_peticion')"
        );
        foreach ($admins as $adminId) {
            $stmtN->execute([$adminId, $msg]);
        }
    }

    $pdo->commit();

    echo json_encode([
        'success'      => true,
        'message'      => 'Solicitud de ' . $tipoLabel . ' enviada correctamente',
        'id_solicitud' => $idSolicitud,
        'insertados'   => $insertados,
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('guardar_ausencia: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}