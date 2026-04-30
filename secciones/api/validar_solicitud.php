<?php
/*
 * validar_solicitud.php — Endpoint POST de la API de Horario
 * Aprueba o rechaza una solicitud de horario. Si se aprueba, inserta los registros
 * correspondientes en la tabla HORARIOS. Registra la acción en HISTORIAL_VALIDACIONES.
 * Acceso: solo admin. Devuelve JSON {success, message}.
 */
session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data          = json_decode(file_get_contents('php://input'), true);
$idSolicitud   = isset($data['id_solicitud'])   ? (int)$data['id_solicitud']    : 0;
$accion        = isset($data['accion'])          ? $data['accion']               : '';
$motivoRechazo = isset($data['motivo_rechazo']) ? trim($data['motivo_rechazo']) : null;

if ($idSolicitud === 0 || !in_array($accion, ['APROBAR', 'RECHAZAR'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}
if ($accion === 'RECHAZAR' && empty($motivoRechazo)) {
    echo json_encode(['success' => false, 'message' => 'Debes indicar un motivo para rechazar']);
    exit;
}

$idAdmin   = $_SESSION['usuario']['id'];
$idEmpresa = (int)$_SESSION['empresa_activa'];

try {
    $pdo->beginTransaction();

    // Verificar que la solicitud existe, pertenece a esta empresa y está PENDIENTE
    $stmtV = $pdo->prepare("
        SELECT id_solicitud, id_usuario, tipo_solicitud, estado
        FROM solicitudes_horario
        WHERE id_solicitud = ? AND id_empresa = ? AND estado = 'PENDIENTE'
    ");
    $stmtV->execute([$idSolicitud, $idEmpresa]);
    $solicitud = $stmtV->fetch(PDO::FETCH_ASSOC);

    if (!$solicitud) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Solicitud no encontrada o ya procesada']);
        exit;
    }

    $idUsuario = (int)$solicitud['id_usuario'];

    if ($accion === 'APROBAR') {
        // ── Obtener detalle día a día ─────────────────────────
        $stmtD = $pdo->prepare("
            SELECT fecha, orden_dia, tipo_jornada, hora_inicio, hora_fin, horas_totales, observaciones
            FROM detalle_solicitud_horario
            WHERE id_solicitud = ?
            ORDER BY fecha ASC, orden_dia ASC
        ");
        $stmtD->execute([$idSolicitud]);
        $detalles = $stmtD->fetchAll(PDO::FETCH_ASSOC);

        $stmtCheck  = $pdo->prepare("SELECT id_horario FROM horarios WHERE id_usuario=? AND id_empresa=? AND fecha=? AND orden_dia=?");
        $stmtUpdate = $pdo->prepare("UPDATE horarios SET tipo_jornada=?,hora_inicio=?,hora_fin=?,horas_totales=?,observaciones=?,actualizado_en=NOW() WHERE id_usuario=? AND id_empresa=? AND fecha=? AND orden_dia=?");
        $stmtInsert = $pdo->prepare("INSERT INTO horarios (id_usuario,id_empresa,fecha,orden_dia,tipo_jornada,hora_inicio,hora_fin,horas_totales,observaciones) VALUES (?,?,?,?,?,?,?,?,?)");

        $diasVacaciones = 0; // contador para descontar

        foreach ($detalles as $det) {
            $fecha = $det['fecha'];
            $orden = (int)($det['orden_dia'] ?? 1);

            $stmtCheck->execute([$idUsuario, $idEmpresa, $fecha, $orden]);
            $existente = $stmtCheck->fetchColumn();

            if ($existente) {
                $stmtUpdate->execute([
                    $det['tipo_jornada'], $det['hora_inicio'], $det['hora_fin'],
                    $det['horas_totales'], $det['observaciones'],
                    $idUsuario, $idEmpresa, $fecha, $orden
                ]);
            } else {
                $stmtInsert->execute([
                    $idUsuario, $idEmpresa, $fecha, $orden,
                    $det['tipo_jornada'], $det['hora_inicio'],
                    $det['hora_fin'], $det['horas_totales'], $det['observaciones']
                ]);
            }

            // Contar días de vacaciones aprobados
            if ($det['tipo_jornada'] === 'VACACIONES') {
                $diasVacaciones++;
            }
        }

        // ── Descontar vacaciones de vacaciones_acumulado ──────
        if ($diasVacaciones > 0) {
            $anioSolicitud = (int)date('Y', strtotime($detalles[0]['fecha']));

            // Asegurar que existe el registro del año
            $pdo->prepare("
                INSERT INTO vacaciones_acumulado (id_usuario, año, dias_totales, dias_utilizados, dias_restantes)
                VALUES (?, ?, 30, 0, 30)
                ON DUPLICATE KEY UPDATE id_usuario = id_usuario
            ")->execute([$idUsuario, $anioSolicitud]);

            // Actualizar días utilizados y restantes
            $pdo->prepare("
                UPDATE vacaciones_acumulado
                SET dias_utilizados = dias_utilizados + ?,
                    dias_restantes  = GREATEST(dias_restantes - ?, 0)
                WHERE id_usuario = ? AND año = ?
            ")->execute([$diasVacaciones, $diasVacaciones, $idUsuario, $anioSolicitud]);
        }

        $nuevoEstado = 'APROBADO';
        $mensaje     = "Solicitud aprobada correctamente" . ($diasVacaciones > 0 ? " ($diasVacaciones días de vacaciones descontados)" : '');

    } else {
        // ── RECHAZAR ─────────────────────────────────────────
        $nuevoEstado = 'RECHAZADO';
        $mensaje     = 'Solicitud rechazada';
    }

    // ── Actualizar estado solicitud ───────────────────────────
    $pdo->prepare("
        UPDATE solicitudes_horario
        SET estado=?, motivo_rechazo=?, validado_por=?, validado_en=NOW()
        WHERE id_solicitud=?
    ")->execute([$nuevoEstado, $motivoRechazo, $idAdmin, $idSolicitud]);

    // ── Historial ─────────────────────────────────────────────
    $pdo->prepare("
        INSERT INTO historial_validaciones (id_solicitud, accion, motivo, validado_por)
        VALUES (?,?,?,?)
    ")->execute([$idSolicitud, $nuevoEstado, $motivoRechazo, $idAdmin]);

    // ── Notificación al empleado ──────────────────────────────
    try {
        $stmtInfo = $pdo->prepare("SELECT id_usuario, tipo_solicitud FROM solicitudes_horario WHERE id_solicitud=?");
        $stmtInfo->execute([$idSolicitud]);
        $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);
        if ($info) {
            $msgNotif = $nuevoEstado === 'APROBADO'
                ? "✅ Tu solicitud de {$info['tipo_solicitud']} ha sido APROBADA."
                : "❌ Tu solicitud de {$info['tipo_solicitud']} ha sido RECHAZADA. Motivo: " . ($motivoRechazo ?: 'No especificado');
            $pdo->prepare("INSERT INTO notificaciones (id_usuario, mensaje, tipo) VALUES (?,?,'resultado_peticion')")
                ->execute([$info['id_usuario'], $msgNotif]);
        }
    } catch (Exception $eN) {
        error_log("Error notificación: " . $eN->getMessage());
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => $mensaje, 'nuevo_estado' => $nuevoEstado]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error en validar_solicitud: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>