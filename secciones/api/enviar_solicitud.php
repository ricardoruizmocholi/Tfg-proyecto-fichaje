<?php
// secciones/api/enviar_solicitud.php
session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$idUsuario = $_SESSION['usuario']['id'];
$idEmpresa = $_SESSION['empresa_activa'];

if (!isset($_SESSION['horarios_temporales']) || count($_SESSION['horarios_temporales']) === 0) {
    echo json_encode(['success' => false, 'message' => 'No hay cambios pendientes para enviar']);
    exit;
}

$horariosTemporal = $_SESSION['horarios_temporales'];

try {
    $pdo->beginTransaction();

    // ── 1. Rango de fechas ────────────────────────────────────
    // Las claves son "YYYY-MM-DD_N" → ordenar por la parte de fecha
    $fechas = array_map(fn($k) => substr($k, 0, 10), array_keys($horariosTemporal));
    sort($fechas);
    $fechaInicio = $fechas[0];
    $fechaFin    = end($fechas);

    // ── 2. Determinar tipo de solicitud ───────────────────────
    // Contar tipos de jornada en los temporales
    $tiposContador = [];
    foreach ($horariosTemporal as $horario) {
        $tipo = $horario['tipo_jornada'] ?? 'TRABAJO';
        $tiposContador[$tipo] = ($tiposContador[$tipo] ?? 0) + 1;
    }

    $total = count($horariosTemporal);

    // Regla: si MÁS DE LA MITAD son vacaciones → VACACIONES
    //         si HAY ALGUNO médico y ningún trabajo → MEDICO
    //         si MÁS DE LA MITAD son LIBRE/FESTIVO → AUSENCIA
    //         resto → HORARIO_MES
    if (($tiposContador['VACACIONES'] ?? 0) > $total / 2) {
        $tipoSolicitud = 'VACACIONES';
    } elseif (
        ($tiposContador['MEDICO'] ?? 0) > 0 &&
        ($tiposContador['TRABAJO'] ?? 0) === 0 &&
        ($tiposContador['PARTIDA_M'] ?? 0) === 0 &&
        ($tiposContador['PARTIDA_T'] ?? 0) === 0
    ) {
        $tipoSolicitud = 'MEDICO';
    } else {
        $tipoSolicitud = 'HORARIO_MES';
    }

    // ── 3. Observación general legible ────────────────────────
    $mesNombre = strftime('%B %Y', strtotime($fechaInicio));
    $obs = match($tipoSolicitud) {
        'VACACIONES' => "Solicitud de vacaciones del $fechaInicio al $fechaFin",
        'MEDICO'     => "Solicitud de horas médicas ($total día" . ($total > 1 ? 's' : '') . ")",
        default      => "Solicitud de horario — $mesNombre ($total evento" . ($total > 1 ? 's' : '') . ")",
    };

    // ── 4. Insertar solicitud principal ───────────────────────
    $pdo->prepare("
        INSERT INTO SOLICITUDES_HORARIO
            (id_usuario, id_empresa, tipo_solicitud, fecha_inicio, fecha_fin, estado, observaciones)
        VALUES (?, ?, ?, ?, ?, 'PENDIENTE', ?)
    ")->execute([$idUsuario, $idEmpresa, $tipoSolicitud, $fechaInicio, $fechaFin, $obs]);

    $idSolicitud = $pdo->lastInsertId();

    // ── 5. Insertar detalle día a día ─────────────────────────
    $stmtD = $pdo->prepare("
        INSERT INTO DETALLE_SOLICITUD_HORARIO
            (id_solicitud, fecha, orden_dia, tipo_jornada, hora_inicio, hora_fin, horas_totales, observaciones)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $ordenPorFecha = [];
    foreach ($horariosTemporal as $claveEvento => $horario) {
        $fecha    = $horario['fecha'];
        $ordenDia = $horario['orden_dia'] ?? 1;

        $stmtD->execute([
            $idSolicitud,
            $fecha,
            $ordenDia,
            $horario['tipo_jornada'],
            $horario['hora_inicio']    ?: null,
            $horario['hora_fin']       ?: null,
            $horario['horas_totales']  ?: null,
            $horario['observaciones']  ?: null,
        ]);
    }

    // ── 6. Notificar admins ───────────────────────────────────
    $stmtAdmins = $pdo->prepare(
        "SELECT id_usuario FROM EMPRESA_USUARIO WHERE id_empresa = ? AND admin = 1 AND activo = 1"
    );
    $stmtAdmins->execute([$idEmpresa]);
    $admins = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($admins)) {
        $nombreEmpl = $_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellidos'];
        $tipoLabel  = match($tipoSolicitud) {
            'VACACIONES' => 'vacaciones',
            'MEDICO'     => 'horas médicas',
            default      => 'horario',
        };
        $msg = " Nueva solicitud de $tipoLabel de $nombreEmpl";

        $stmtN = $pdo->prepare(
            "INSERT INTO NOTIFICACIONES (id_usuario, mensaje, tipo) VALUES (?, ?, 'nueva_peticion')"
        );
        foreach ($admins as $adminId) {
            $stmtN->execute([$adminId, $msg]);
        }
    }

    $pdo->commit();
    unset($_SESSION['horarios_temporales']);

    echo json_encode([
        'success'       => true,
        'message'       => 'Solicitud enviada correctamente al administrador',
        'id_solicitud'  => $idSolicitud,
        'tipo_solicitud'=> $tipoSolicitud,
        'dias_enviados' => count($horariosTemporal),
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('enviar_solicitud: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}