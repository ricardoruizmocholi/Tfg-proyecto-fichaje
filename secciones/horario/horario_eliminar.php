<?php
/*
 * horario_eliminar.php — Endpoint POST del módulo Horario
 * Elimina un horario individual de la tabla HORARIOS por ID.
 * Acceso: solo admin. Valida que el horario pertenezca a un empleado de la empresa activa.
 * Devuelve JSON {success, message}.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

// Verificar sesión y permisos de admin
if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_horario'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$empresa = $_SESSION['empresa_activa'];

try {
    // Verificar que el horario pertenece a la empresa
    $stmtVerif = $pdo->prepare("SELECT id_horario FROM HORARIOS WHERE id_horario = ? AND id_empresa = ?");
    $stmtVerif->execute([$data['id_horario'], $empresa]);
    
    if (!$stmtVerif->fetch()) {
        throw new Exception('Horario no encontrado o no autorizado');
    }
    
    // Eliminar el horario
    $stmt = $pdo->prepare("DELETE FROM HORARIOS WHERE id_horario = ?");
    $stmt->execute([$data['id_horario']]);
    
    echo json_encode(['success' => true, 'message' => 'Horario eliminado correctamente']);
    
} catch (Exception $e) {
    error_log('Error en horario_eliminar.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>