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

$id_horario = $_GET['id'] ?? null;

if (!$id_horario) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$empresa = $_SESSION['empresa_activa'];

try {
    $stmt = $pdo->prepare("SELECT * FROM HORARIOS WHERE id_horario = ? AND id_empresa = ?");
    $stmt->execute([$id_horario, $empresa]);
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$horario) {
        throw new Exception('Horario no encontrado');
    }
    
    echo json_encode(['success' => true, 'horario' => $horario]);
    
} catch (Exception $e) {
    error_log('Error en horario_obtener.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>