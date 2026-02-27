<?php
session_start();
header('Content-Type: application/json');

require 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['reset_email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

$password = trim($data['password']);
$email = $_SESSION['reset_email'];

// Validar contraseña
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

// Hashear la nueva contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Actualizar en la base de datos
$stmt = $pdo->prepare("UPDATE USUARIO SET password_hash = ? WHERE email = ? AND activo = 1");
$updated = $stmt->execute([$password_hash, $email]);

if ($updated && $stmt->rowCount() > 0) {
    // Limpiar sesión
    unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_code_time']);
    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error actualizando la contraseña']);
}
?>