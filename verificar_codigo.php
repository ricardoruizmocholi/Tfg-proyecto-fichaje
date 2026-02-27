<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['reset_code']) || !isset($_SESSION['reset_code_time']) || !isset($data['code'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión inválida']);
    exit;
}

$code = trim($data['code']);
$tiempoTranscurrido = time() - $_SESSION['reset_code_time'];

// Verificar que el código no haya expirado (15 minutos = 900 segundos)
if ($tiempoTranscurrido > 900) {
    unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_code_time']);
    echo json_encode(['success' => false, 'message' => 'El código ha expirado']);
    exit;
}

// Verificar que el código coincide
if ($code === $_SESSION['reset_code']) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Código incorrecto']);
}
?>