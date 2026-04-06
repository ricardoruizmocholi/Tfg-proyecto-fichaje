<?php
session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$idSolicitud  = isset($data['id_solicitud'])   ? (int)$data['id_solicitud']  : 0;
$accion       = isset($data['accion'])          ? $data['accion']             : '';
$motivoRechazo = isset($data['motivo_rechazo']) ? trim($data['motivo_rechazo']) : null;

if ($idSolicitud === 0 || !in_array($accion, ['APROBAR', 'RECHAZAR'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}
if ($accion === 'RECHAZAR' && empty($motivoRechazo)) {
    echo json_encode(['success' => false, 'message' => 'Debes indicar un motivo para rechazar']);
    exit;
}

$idAdmin  = $_SESSION['usuario']['id'];
$idEmpresa = $_SESSION['empresa_activa'];

try {
    $pdo->beginTransaction();

    // Verificar que la solicitud existe y está pendiente
    $stmtV = $pdo->prepare("
        SELECT id_solicitud, id_usuario, tipo_solicitud, estado
        FROM SOLICITUDES_HORARIO
        WHERE id_solicitud = :id AND id_empresa = :ide AND estado = 'PENDIENTE'
    ");
    $stmtV->execute(['id' => $idSolicitud, 'ide' => $idEmpresa]);
    $solicitud = $stmtV->fetch(PDO::FETCH_ASSOC);

    if (!$solicitud) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Solicitud no encontrada o ya procesada']);
        exit;
    }

    if ($accion === 'APROBAR') {
        // Obtener el detalle día a día
        $stmtD = $pdo->prepare("
            SELECT fecha, orden_dia, tipo_jornada, hora_inicio, hora_fin, horas_totales, observaciones
            FROM DETALLE_SOLICITUD_HORARIO
            WHERE id_solicitud = :id
            ORDER BY fecha ASC, orden_dia ASC
        ");
        $stmtD->execute(['id' => $idSolicitud]);
        $detalles = $stmtD->fetchAll(PDO::FETCH_ASSOC);

        // Para cada detalle: si ya existe (mismo usuario+empresa+fecha+orden_dia) → UPDATE, si no → INSERT
        $sqlCheck = "SELECT id_horario FROM HORARIOS 
                     WHERE id_usuario = ? AND id_empresa = ? AND fecha = ? AND orden_dia = ?";
        $sqlUpdate = "UPDATE HORARIOS SET 
                        tipo_jornada  = ?,
                        hora_inicio   = ?,
                        hora_fin      = ?,
                        horas_totales = ?,
                        observaciones = ?,
                        actualizado_en = CURRENT_TIMESTAMP
                      WHERE id_usuario = ? AND id_empresa = ? AND fecha = ? AND orden_dia = ?";
        $sqlInsert = "INSERT INTO HORARIOS 
                        (id_usuario, id_empresa, fecha, orden_dia, tipo_jornada, hora_inicio, hora_fin, horas_totales, observaciones)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmtCheck  = $pdo->prepare($sqlCheck);
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtInsert = $pdo->prepare($sqlInsert);

        foreach ($detalles as $det) {
            $idU   = $solicitud['id_usuario'];
            $fecha = $det['fecha'];
            $orden = (int)($det['orden_dia'] ?? 1);

            $stmtCheck->execute([$idU, $idEmpresa, $fecha, $orden]);
            $existente = $stmtCheck->fetchColumn();

            if ($existente) {
                // Actualizar el registro existente
                $stmtUpdate->execute([
                    $det['tipo_jornada'],
                    $det['hora_inicio'],
                    $det['hora_fin'],
                    $det['horas_totales'],
                    $det['observaciones'],
                    // WHERE
                    $idU, $idEmpresa, $fecha, $orden
                ]);
            } else {
                // Insertar nuevo
                $stmtInsert->execute([
                    $idU, $idEmpresa, $fecha, $orden,
                    $det['tipo_jornada'],
                    $det['hora_inicio'],
                    $det['hora_fin'],
                    $det['horas_totales'],
                    $det['observaciones']
                ]);
            }
        }

        $nuevoEstado = 'APROBADO';
        $mensaje     = 'Solicitud aprobada correctamente';

    } else {
        $nuevoEstado = 'RECHAZADO';
        $mensaje     = 'Solicitud rechazada';
    }

    // Actualizar estado de la solicitud
    $pdo->prepare("
        UPDATE SOLICITUDES_HORARIO
        SET estado = :est, motivo_rechazo = :mot, validado_por = :vp, validado_en = NOW()
        WHERE id_solicitud = :id
    ")->execute([
        'est' => $nuevoEstado,
        'mot' => $motivoRechazo,
        'vp'  => $idAdmin,
        'id'  => $idSolicitud
    ]);

    // Historial
    $pdo->prepare("
        INSERT INTO HISTORIAL_VALIDACIONES (id_solicitud, accion, motivo, validado_por)
        VALUES (:id, :acc, :mot, :vp)
    ")->execute([
        'id'  => $idSolicitud,
        'acc' => $nuevoEstado,
        'mot' => $motivoRechazo,
        'vp'  => $idAdmin
    ]);

    // Notificación al empleado
    try {
        $stmtInfo = $pdo->prepare("SELECT id_usuario, tipo_solicitud FROM SOLICITUDES_HORARIO WHERE id_solicitud = :id");
        $stmtInfo->execute(['id' => $idSolicitud]);
        $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);
        if ($info) {
            $msgNotif = $nuevoEstado === 'APROBADO'
                ? "✅ Tu solicitud de {$info['tipo_solicitud']} ha sido APROBADA."
                : "❌ Tu solicitud de {$info['tipo_solicitud']} ha sido RECHAZADA. Motivo: " . ($motivoRechazo ?: 'No especificado');
            $pdo->prepare("INSERT INTO NOTIFICACIONES (id_usuario, mensaje, tipo) VALUES (:u, :m, 'resultado_peticion')")
                ->execute(['u' => $info['id_usuario'], 'm' => $msgNotif]);
        }
    } catch (Exception $eN) {
        error_log("Error notificación validar_solicitud: " . $eN->getMessage());
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => $mensaje, 'nuevo_estado' => $nuevoEstado]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error en validar_solicitud: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()]);
}
?>