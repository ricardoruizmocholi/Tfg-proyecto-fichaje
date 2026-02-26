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

if (!isset($data['id_fichaje'])) {
    echo json_encode(['success' => false, 'message' => 'ID de fichaje no proporcionado']);
    exit;
}

$idFichaje = $data['id_fichaje'];
$empresa = $_SESSION['empresa_activa'];

try {
    // Verificar que el fichaje pertenece a un usuario de la empresa
    $stmtVerif = $pdo->prepare("
        SELECT F.id_fichaje 
        FROM FICHAJE F
        JOIN EMPRESA_USUARIO EU ON F.id_usuario = EU.id_usuario
        WHERE F.id_fichaje = ? AND EU.id_empresa = ? AND EU.activo = 1
    ");
    $stmtVerif->execute([$idFichaje, $empresa]);
    
    if (!$stmtVerif->fetch()) {
        throw new Exception('Fichaje no encontrado o no autorizado');
    }
    
    // Eliminar el fichaje
    $stmt = $pdo->prepare("DELETE FROM FICHAJE WHERE id_fichaje = ?");
    $stmt->execute([$idFichaje]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Fichaje eliminado correctamente'
    ]);
    
} catch (Exception $e) {
    error_log('Error en fichaje_eliminar.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>