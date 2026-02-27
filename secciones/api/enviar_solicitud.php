<?php
session_start();
header('Content-Type: application/json');

require_once '../../config.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$idUsuario = $_SESSION['usuario']['id'];
$idEmpresa = $_SESSION['empresa_activa'];

// Verificar que hay horarios temporales para enviar
if (!isset($_SESSION['horarios_temporales']) || count($_SESSION['horarios_temporales']) === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'No hay cambios pendientes para enviar'
    ]);
    exit;
}

$horariosTemporal = $_SESSION['horarios_temporales'];

try {
    $pdo->beginTransaction();

    // Determinar el rango de fechas
    $fechas = array_keys($horariosTemporal);
    sort($fechas);
    $fechaInicio = $fechas[0];
    $fechaFin = $fechas[count($fechas) - 1];

    // Determinar el tipo de solicitud predominante
    $tiposContador = [];
    foreach ($horariosTemporal as $horario) {
        $tipo = $horario['tipo_jornada'];
        if (!isset($tiposContador[$tipo])) {
            $tiposContador[$tipo] = 0;
        }
        $tiposContador[$tipo]++;
    }

    // Si la mayoría son vacaciones o médico, es una solicitud específica
    $tipoSolicitud = 'HORARIO_MES';
    if (isset($tiposContador['VACACIONES']) && $tiposContador['VACACIONES'] > count($horariosTemporal) / 2) {
        $tipoSolicitud = 'VACACIONES';
    } elseif (isset($tiposContador['MEDICO']) && $tiposContador['MEDICO'] > 0) {
        $tipoSolicitud = 'MEDICO';
    }

    // ============================================
    // 1. CREAR LA SOLICITUD PRINCIPAL
    // ============================================
    $sqlSolicitud = "
        INSERT INTO SOLICITUDES_HORARIO (
            id_usuario,
            id_empresa,
            tipo_solicitud,
            fecha_inicio,
            fecha_fin,
            estado,
            observaciones
        ) VALUES (
            :id_usuario,
            :id_empresa,
            :tipo_solicitud,
            :fecha_inicio,
            :fecha_fin,
            'PENDIENTE',
            :observaciones
        )
    ";

    $observacionGeneral = "Solicitud de horario para el mes de " . date('F Y', strtotime($fechaInicio));

    $stmtSolicitud = $pdo->prepare($sqlSolicitud);
    $stmtSolicitud->execute([
        'id_usuario' => $idUsuario,
        'id_empresa' => $idEmpresa,
        'tipo_solicitud' => $tipoSolicitud,
        'fecha_inicio' => $fechaInicio,
        'fecha_fin' => $fechaFin,
        'observaciones' => $observacionGeneral
    ]);

    $idSolicitud = $pdo->lastInsertId();

    // ============================================
    // 2. INSERTAR DETALLE DÍA A DÍA
    // ============================================
    $sqlDetalle = "
        INSERT INTO DETALLE_SOLICITUD_HORARIO (
            id_solicitud,
            fecha,
            tipo_jornada,
            hora_inicio,
            hora_fin,
            horas_totales,
            observaciones
        ) VALUES (
            :id_solicitud,
            :fecha,
            :tipo_jornada,
            :hora_inicio,
            :hora_fin,
            :horas_totales,
            :observaciones
        )
    ";

    $stmtDetalle = $pdo->prepare($sqlDetalle);

    foreach ($horariosTemporal as $fecha => $horario) {
        $stmtDetalle->execute([
            'id_solicitud' => $idSolicitud,
            'fecha' => $fecha,
            'tipo_jornada' => $horario['tipo_jornada'],
            'hora_inicio' => $horario['hora_inicio'],
            'hora_fin' => $horario['hora_fin'],
            'horas_totales' => $horario['horas_totales'],
            'observaciones' => $horario['observaciones']
        ]);
    }

    // ============================================
    // AÑADIDO: NOTIFICACIONES PARA ADMIN
    // ============================================
    // 1. Buscar IDs de los administradores
    $sqlAdmins = "SELECT id_usuario FROM EMPRESA_USUARIO WHERE id_empresa = :id_empresa AND admin = 1";
    $stmtAdmins = $pdo->prepare($sqlAdmins);
    $stmtAdmins->execute(['id_empresa' => $idEmpresa]);
    $admins = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN);

    // 2. Si hay admins, enviarles la notificación
    if (!empty($admins)) {
        $nombreEmpl = $_SESSION['usuario']['nombre'] . " " . $_SESSION['usuario']['apellidos'];
        $msg = "Nueva solicitud de $tipoSolicitud de $nombreEmpl";
        
        $sqlInsNotif = "INSERT INTO NOTIFICACIONES (id_usuario, mensaje, tipo) VALUES (:id_a, :msg, 'nueva_peticion')";
        $stmtInsNotif = $pdo->prepare($sqlInsNotif);
        
        foreach ($admins as $adminId) {
            $stmtInsNotif->execute([
                'id_a' => $adminId,
                'msg' => $msg
            ]);
        }
    }
    $pdo->commit();

    

    // Limpiar horarios temporales de la sesión
    unset($_SESSION['horarios_temporales']);

    echo json_encode([
        'success' => true,
        'message' => 'Solicitud enviada correctamente al administrador',
        'id_solicitud' => $idSolicitud,
        'tipo_solicitud' => $tipoSolicitud,
        'dias_enviados' => count($horariosTemporal)
    ]);

} catch (Exception $e) { // Cambia PDOException por Exception para capturar todo
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // ESTA LÍNEA ES LA CLAVE: nos dirá el error real
    echo json_encode([
        'success' => false, 
        'message' => 'Error real: ' . $e->getMessage()
    ]);
}
?>