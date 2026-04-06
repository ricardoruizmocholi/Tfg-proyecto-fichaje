<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

// Verificar sesión y permisos de admin
if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_usuario']) || !isset($data['fecha']) || !isset($data['tipo_jornada'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$empresa = $_SESSION['empresa_activa'];

try {
    // Verificar que el usuario pertenece a la empresa
    $stmtVerif = $pdo->prepare("SELECT 1 FROM EMPRESA_USUARIO WHERE id_usuario = ? AND id_empresa = ? AND activo = 1");
    $stmtVerif->execute([$data['id_usuario'], $empresa]);
    
    if (!$stmtVerif->fetch()) {
        throw new Exception('Usuario no pertenece a la empresa');
    }
    
    // Calcular horas totales si es tipo TRABAJO
    $horasTotales = null;
    if (in_array($data['tipo_jornada'], ['TRABAJO', 'PARTIDA', 'MEDICO']) && !empty($data['hora_inicio']) && !empty($data['hora_fin'])) {
        $inicio = strtotime($data['hora_inicio']);
        $fin = strtotime($data['hora_fin']);
        $horasTotales = round(($fin - $inicio) / 3600, 2);
    }
    
    if (!empty($data['id_horario'])) {
        // Actualizar horario existente
        $sql = "UPDATE HORARIOS SET 
                tipo_jornada = ?,
                hora_inicio = ?,
                hora_fin = ?,
                horas_totales = ?,
                observaciones = ?
                WHERE id_horario = ? AND id_empresa = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['tipo_jornada'],
            $data['hora_inicio'],
            $data['hora_fin'],
            $horasTotales,
            $data['observaciones'],
            $data['id_horario'],
            $empresa
        ]);

                // --- SISTEMA DE NOTIFICACIONES ---
        // El administrador es quien ejecuta este archivo, notificamos al empleado ($data['id_usuario'])
        $mensajeNotif = "El administrador ha actualizado tu horario para el día " . date('d/m/Y', strtotime($data['fecha']));
        $sqlNotif = "INSERT INTO NOTIFICACIONES (id_usuario, mensaje, tipo) VALUES (?, ?, 'cambio_horario')";
        $stmtNotif = $pdo->prepare($sqlNotif);
        $stmtNotif->execute([$data['id_usuario'], $mensajeNotif]);
    } else {
        // Insertar nuevo horario
        // Verificar si ya existe un horario para ese usuario y fecha
       
        if (empty($data['id_horario'])) {
            // Calcular el siguiente orden_dia para ese usuario+empresa+fecha
            $stmtOrden = $pdo->prepare("
                SELECT COALESCE(MAX(orden_dia), 0) + 1 
                FROM HORARIOS 
                WHERE id_usuario = ? AND id_empresa = ? AND fecha = ?
            ");
            $stmtOrden->execute([$data['id_usuario'], $empresa, $data['fecha']]);
            $siguienteOrden = $stmtOrden->fetchColumn();

            $sql = "INSERT INTO HORARIOS (
                        id_usuario, id_empresa, fecha, orden_dia,
                        tipo_jornada, hora_inicio, hora_fin, horas_totales, observaciones
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['id_usuario'], $empresa, $data['fecha'],
                $siguienteOrden,  // <-- orden dinámico
                $data['tipo_jornada'], $data['hora_inicio'],
                $data['hora_fin'], $horasTotales, $data['observaciones']
            ]);
        }

                // --- SISTEMA DE NOTIFICACIONES ---
        // El administrador es quien ejecuta este archivo, notificamos al empleado ($data['id_usuario'])
        $mensajeNotif = "El administrador ha actualizado tu horario para el día " . date('d/m/Y', strtotime($data['fecha']));
        $sqlNotif = "INSERT INTO NOTIFICACIONES (id_usuario, mensaje, tipo) VALUES (?, ?, 'cambio_horario')";
        $stmtNotif = $pdo->prepare($sqlNotif);
        $stmtNotif->execute([$data['id_usuario'], $mensajeNotif]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Horario guardado correctamente']);
    
} catch (Exception $e) {
    error_log('Error en horario_guardar.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>