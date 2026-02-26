<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode([]);
    exit;
}

$idUsuario = $_SESSION['usuario']['id'];

try {
    // 1. Obtener las últimas 10 notificaciones
    $stmt = $pdo->prepare("SELECT id_notificacion, mensaje, leida, fecha_creacion 
                           FROM NOTIFICACIONES 
                           WHERE id_usuario = ? 
                           ORDER BY fecha_creacion DESC 
                           LIMIT 10");
    $stmt->execute([$idUsuario]);
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Marcar como leídas una vez que se han consultado (opcional, o podrías hacerlo con otro botón)
    $update = $pdo->prepare("UPDATE NOTIFICACIONES SET leida = 1 WHERE id_usuario = ? AND leida = 0");
    $update->execute([$idUsuario]);

    echo json_encode($notificaciones);

} catch (Exception $e) {
    echo json_encode([]);
}