<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_reporte'])) {
    echo json_encode(['success' => false, 'message' => 'ID de reporte no proporcionado']);
    exit;
}

$empresa = $_SESSION['empresa_activa'];

try {
    // Obtener datos del reporte
    $stmt = $pdo->prepare("
        SELECT * FROM REPORTES 
        WHERE id_reporte = ? AND id_empresa = ?
    ");
    $stmt->execute([$data['id_reporte'], $empresa]);
    $reporte = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reporte) {
        throw new Exception('Reporte no encontrado');
    }
    
    // Eliminar archivo físico si existe
    if ($reporte['ruta_archivo']) {
        $rutaArchivo = __DIR__ . '/../' . $reporte['ruta_archivo'];
        if (file_exists($rutaArchivo)) {
            unlink($rutaArchivo);
        }
    }
    
    // Eliminar registro de la base de datos
    $stmtDelete = $pdo->prepare("DELETE FROM REPORTES WHERE id_reporte = ?");
    $stmtDelete->execute([$data['id_reporte']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Reporte eliminado correctamente'
    ]);
    
} catch (Exception $e) {
    error_log('Error en reporte_eliminar.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>