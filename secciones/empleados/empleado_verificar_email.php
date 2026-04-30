<?php
/*
 * empleado_verificar_email.php — Endpoint GET del módulo Empleados
 * Comprueba si un email ya está registrado en la tabla USUARIO.
 * Acceso: empleado y admin. Usado en tiempo real desde empleados_nuevo.php.
 * Devuelve JSON {existe: true|false}.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$email = $_GET['email'] ?? null;

if (!$email) {
    echo json_encode(['existe' => false]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_usuario FROM USUARIO WHERE email = ?");
    $stmt->execute([$email]);
    $existe = $stmt->fetch() !== false;
    
    echo json_encode(['existe' => $existe]);
    
} catch (Exception $e) {
    error_log('Error en empleado_verificar_email.php: ' . $e->getMessage());
    echo json_encode(['existe' => false]);
}
?>