<?php
session_start();
header('Content-Type: application/json');

require_once '../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$idSolicitud = isset($data['id_solicitud']) ? (int)$data['id_solicitud'] : 0;
$accion = isset($data['accion']) ? $data['accion'] : '';
$motivoRechazo = isset($data['motivo_rechazo']) ? trim($data['motivo_rechazo']) : null;

if ($idSolicitud === 0 || !in_array($accion, ['APROBAR', 'RECHAZAR'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

if ($accion === 'RECHAZAR' && empty($motivoRechazo)) {
    echo json_encode(['success' => false, 'message' => 'Debes indicar un motivo para rechazar']);
    exit;
}

$idAdmin = $_SESSION['usuario']['id'];
$idEmpresa = $_SESSION['empresa_activa'];

try {
    $pdo->beginTransaction();

    // ============================================
    // VERIFICAR QUE LA SOLICITUD EXISTE Y ESTÁ PENDIENTE
    // ============================================
    $sqlVerificar = "
        SELECT 
            S.id_solicitud,
            S.id_usuario,
            S.tipo_solicitud,
            S.estado
        FROM SOLICITUDES_HORARIO S
        WHERE S.id_solicitud = :id_solicitud
        AND S.id_empresa = :id_empresa
        AND S.estado = 'PENDIENTE'
    ";

    $stmtVerificar = $pdo->prepare($sqlVerificar);
    $stmtVerificar->execute([
        'id_solicitud' => $idSolicitud,
        'id_empresa' => $idEmpresa
    ]);

    $solicitud = $stmtVerificar->fetch(PDO::FETCH_ASSOC);

    if (!$solicitud) {
        echo json_encode(['success' => false, 'message' => 'Solicitud no encontrada o ya procesada']);
        $pdo->rollBack();
        exit;
    }

    if ($accion === 'APROBAR') {
        // ============================================
        // APROBAR: Copiar detalle a HORARIOS
        // ============================================
        $sqlObtenerDetalle = "
            SELECT 
                fecha,
                tipo_jornada,
                hora_inicio,
                hora_fin,
                horas_totales,
                observaciones
            FROM DETALLE_SOLICITUD_HORARIO
            WHERE id_solicitud = :id_solicitud
        ";

        $stmtDetalle = $pdo->prepare($sqlObtenerDetalle);
        $stmtDetalle->execute(['id_solicitud' => $idSolicitud]);
        $detalles = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

        // Insertar en HORARIOS (reemplazando si ya existe)
        $sqlInsertHorario = "
            INSERT INTO HORARIOS (
                id_usuario,
                id_empresa,
                fecha,
                tipo_jornada,
                hora_inicio,
                hora_fin,
                horas_totales,
                observaciones
            ) VALUES (
                :id_usuario,
                :id_empresa,
                :fecha,
                :tipo_jornada,
                :hora_inicio,
                :hora_fin,
                :horas_totales,
                :observaciones
            )
            ON DUPLICATE KEY UPDATE
                tipo_jornada = VALUES(tipo_jornada),
                hora_inicio = VALUES(hora_inicio),
                hora_fin = VALUES(hora_fin),
                horas_totales = VALUES(horas_totales),
                observaciones = VALUES(observaciones),
                actualizado_en = CURRENT_TIMESTAMP
        ";

        $stmtInsertHorario = $pdo->prepare($sqlInsertHorario);

        foreach ($detalles as $detalle) {
            $stmtInsertHorario->execute([
                'id_usuario' => $solicitud['id_usuario'],
                'id_empresa' => $idEmpresa,
                'fecha' => $detalle['fecha'],
                'tipo_jornada' => $detalle['tipo_jornada'],
                'hora_inicio' => $detalle['hora_inicio'],
                'hora_fin' => $detalle['hora_fin'],
                'horas_totales' => $detalle['horas_totales'],
                'observaciones' => $detalle['observaciones']
            ]);
        }

        // Actualizar estado de la solicitud
        $nuevoEstado = 'APROBADO';
        $mensaje = 'Solicitud aprobada correctamente';

    } else {
        // ============================================
        // RECHAZAR: Solo actualizar estado
        // ============================================
        $nuevoEstado = 'RECHAZADO';
        $mensaje = 'Solicitud rechazada';
    }

    // Actualizar la solicitud
    $sqlUpdateSolicitud = "
        UPDATE SOLICITUDES_HORARIO
        SET 
            estado = :estado,
            motivo_rechazo = :motivo_rechazo,
            validado_por = :validado_por,
            validado_en = NOW()
        WHERE id_solicitud = :id_solicitud
    ";

    $stmtUpdate = $pdo->prepare($sqlUpdateSolicitud);
    $stmtUpdate->execute([
        'estado' => $nuevoEstado,
        'motivo_rechazo' => $motivoRechazo,
        'validado_por' => $idAdmin,
        'id_solicitud' => $idSolicitud
    ]);

    // ============================================
    // REGISTRAR EN HISTORIAL
    // ============================================
    $sqlHistorial = "
        INSERT INTO HISTORIAL_VALIDACIONES (
            id_solicitud,
            accion,
            motivo,
            validado_por
        ) VALUES (
            :id_solicitud,
            :accion,
            :motivo,
            :validado_por
        )
    ";

    $stmtHistorial = $pdo->prepare($sqlHistorial);
    $stmtHistorial->execute([
        'id_solicitud' => $idSolicitud,
        'accion' => $nuevoEstado === 'APROBADO' ? 'APROBADO' : 'RECHAZADO',
        'motivo' => $motivoRechazo,
        'validado_por' => $idAdmin
    ]);


    // ============================================
    // NOTIFICACIÓN PARA EL EMPLEADO (Resultado)
    // ============================================
    try {
        // Consultamos el id del empleado y el tipo de solicitud
        $stmtInfo = $pdo->prepare("SELECT id_usuario, tipo_solicitud FROM SOLICITUDES_HORARIO WHERE id_solicitud = :id");
        $stmtInfo->execute(['id' => $idSolicitud]);
        $infoSol = $stmtInfo->fetch(PDO::FETCH_ASSOC);

        if ($infoSol) {
            $idEmpleado = $infoSol['id_usuario'];
            $tipoSolicitud = $infoSol['tipo_solicitud'];
            
            if ($nuevoEstado === 'APROBADO') {
                $mensaje = "✅ Tu solicitud de $tipoSolicitud ha sido APROBADA.";
            } else {
                $mensaje = "❌ Tu solicitud de $tipoSolicitud ha sido RECHAZADA. Motivo: " . ($motivoRechazo ?: 'No especificado');
            }

            $sqlInsNotif = "INSERT INTO NOTIFICACIONES (id_usuario, mensaje, tipo) VALUES (:id_u, :msg, 'resultado_peticion')";
            $stmtInsNotif = $pdo->prepare($sqlInsNotif);
            $stmtInsNotif->execute([
                'id_u' => $idEmpleado,
                'msg'  => $mensaje
            ]);
        }
    } catch (Exception $e_notif) {
        error_log("Error enviando notificación al empleado: " . $e_notif->getMessage());
        // No lanzamos excepción para que la validación principal siga su curso
    }
    // ============================================


    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => $mensaje,
        'nuevo_estado' => $nuevoEstado
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error en validar_solicitud: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud'
    ]);
}
?>