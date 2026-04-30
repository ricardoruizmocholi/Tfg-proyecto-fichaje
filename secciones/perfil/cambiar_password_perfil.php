<?php
/*
 * cambiar_password_perfil.php — Endpoint POST del módulo Perfil
 * Cambia la contraseña del usuario autenticado.
 * Verifica la contraseña actual antes de actualizar; hashea la nueva con password_hash().
 * Acceso: empleado y admin. Devuelve JSON {success, message}.
 */
if (session_status() === PHP_SESSION_NONE){
    session_start();
}
header('Content-Type: application/json');
require_once __DIR__ . "/../../config.php";

// Verificar sessión
if (!isset($_SESSION['usuario']['id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

$id_usuario = $_SESSION['usuario']['id'];
// Obtener datos del JSON
$data = json_decode(file_get_contents('php://input'), true);

// comprobar que se han enviado los datos necesarios
if(!isset($data['password_actual']) || !isset($data['password_nueva'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

$passwordActual = trim($data['password_actual']);
$passwordNueva = trim($data['password_nueva']);

//Validar logitud de la contraseña nueva
if (strlen($passwordNueva)<6){
    echo json_encode(['success' => false, 'message' => 'La contraseña nueva debe tener al menos 6 caracteres']);
    exit();
}

// Obtener la contraseña actual del usuario
$stmtObtenerHash = $pdo->prepare("SELECT password_hash FROM USUARIO WHERE id_usuario = :id_usuario");
$stmtObtenerHash->execute(['id_usuario' => $id_usuario]);
$usuario = $stmtObtenerHash->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit();
}

// Verificar la contraseña actual si es correcta
if (!password_verify($passwordActual, $usuario['password_hash'])) {
    echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
    exit();
}

// Hashear la nueva contraseña
$passwordHashNueva = password_hash($passwordNueva, PASSWORD_DEFAULT);

// Actualizar la contraseña en la base de datos
$sqlActualizar = "UPDATE USUARIO SET password_hash = :password_hash WHERE id_usuario = :id_usuario";
$stmtActualizar = $pdo->prepare($sqlActualizar);
$actualizado = $stmtActualizar->execute([
    'password_hash' => $passwordHashNueva,
    'id_usuario' => $id_usuario
]);

if ($actualizado) {
    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña']);
}


?>