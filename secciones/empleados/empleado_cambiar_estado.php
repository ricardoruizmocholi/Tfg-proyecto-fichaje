<?php
/*
 * empleado_cambiar_estado.php — Endpoint POST del módulo Empleados
 * Activa o desactiva un empleado en la empresa (campo `activo` en EMPRESA_USUARIO).
 * Acceso: solo admin. No borra el usuario; solo cambia su acceso a esta empresa.
 * Devuelve JSON {success, message, nuevo_estado}.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

// Log para debug
error_log('=== empleado_cambiar_estado.php iniciado ===');

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    error_log('Usuario no autorizado');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
error_log('Datos recibidos: ' . print_r($data, true));

if (!isset($data['id_usuario']) || !isset($data['activo'])) {
    error_log('Datos incompletos');
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$empresa = $_SESSION['empresa_activa'];
error_log('Empresa: ' . $empresa . ', Usuario: ' . $data['id_usuario'] . ', Nuevo estado: ' . $data['activo']);

try {
    // Verificar que el usuario pertenece a la empresa
    $stmtVerif = $pdo->prepare("SELECT 1 FROM EMPRESA_USUARIO WHERE id_usuario = ? AND id_empresa = ?");
    $stmtVerif->execute([$data['id_usuario'], $empresa]);
    
    if (!$stmtVerif->fetch()) {
        throw new Exception('Usuario no pertenece a esta empresa');
    }
    
    error_log('Usuario verificado, actualizando estado...');
    
    // Actualizar estado
    $stmt = $pdo->prepare("UPDATE EMPRESA_USUARIO SET activo = ? WHERE id_usuario = ? AND id_empresa = ?");
    $resultado = $stmt->execute([
        (int)$data['activo'],
        $data['id_usuario'],
        $empresa
    ]);
    
    if (!$resultado) {
        throw new Exception('Error al actualizar el estado');
    }
    
    $mensaje = $data['activo'] == 1 ? 'Empleado activado correctamente' : 'Empleado desactivado correctamente';
    
    error_log('=== Estado actualizado correctamente ===');
    
    echo json_encode([
        'success' => true,
        'message' => $mensaje
    ]);
    
} catch (Exception $e) {
    error_log('ERROR: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>