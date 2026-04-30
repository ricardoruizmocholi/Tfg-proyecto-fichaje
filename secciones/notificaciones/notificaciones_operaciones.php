<?php
/*
 * notificaciones_operaciones.php — Endpoint POST del módulo Notificaciones
 * Ejecuta operaciones sobre notificaciones: marcar_leida, marcar_todas_leidas, eliminar.
 * Acceso: empleado y admin. La operación se indica en el campo JSON `accion`.
 * Devuelve JSON {success, message}.
 */
session_start();
require_once __DIR__ . '/../../config.php';

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