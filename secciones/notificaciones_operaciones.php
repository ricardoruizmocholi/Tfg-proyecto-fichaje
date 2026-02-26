<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['usuario'])) exit;

$idUsuario = $_SESSION['usuario']['id'];
$accion = $_POST['accion'] ?? '';

try {
    if ($accion === 'eliminar') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM NOTIFICACIONES WHERE id_notificacion = ? AND id_usuario = ?");
        $stmt->execute([$id, $idUsuario]);
    } 
    elseif ($accion === 'eliminar_todas') {
        $stmt = $pdo->prepare("DELETE FROM NOTIFICACIONES WHERE id_usuario = ?");
        $stmt->execute([$idUsuario]);
    }
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false]);
}