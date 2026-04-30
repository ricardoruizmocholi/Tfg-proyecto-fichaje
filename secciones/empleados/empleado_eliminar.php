<?php
/*
 * empleado_eliminar.php — Endpoint POST del módulo Empleados
 * Elimina la relación empleado-empresa en EMPRESA_USUARIO (no borra el usuario global).
 * Acceso: solo admin. Valida que el empleado pertenezca a la empresa activa antes de borrar.
 * Devuelve JSON {success, message}.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
    exit;
}

$empresa = $_SESSION['empresa_activa'];
$idUsuarioActual = $_SESSION['usuario']['id'];

try {
    // No permitir que se elimine a sí mismo
    if ($data['id_usuario'] == $idUsuarioActual) {
        throw new Exception('No puedes eliminarte a ti mismo');
    }
    
    // Verificar que el usuario pertenece a la empresa
    $stmtVerif = $pdo->prepare("SELECT 1 FROM EMPRESA_USUARIO WHERE id_usuario = ? AND id_empresa = ?");
    $stmtVerif->execute([$data['id_usuario'], $empresa]);
    
    if (!$stmtVerif->fetch()) {
        throw new Exception('Usuario no pertenece a esta empresa');
    }
    
    $pdo->beginTransaction();
    
    // Eliminar de la relación empresa-usuario
    $stmtEmpresaUsuario = $pdo->prepare("DELETE FROM EMPRESA_USUARIO WHERE id_usuario = ? AND id_empresa = ?");
    $stmtEmpresaUsuario->execute([$data['id_usuario'], $empresa]);
    
    // Verificar si el usuario pertenece a otras empresas
    $stmtOtrasEmpresas = $pdo->prepare("SELECT COUNT(*) FROM EMPRESA_USUARIO WHERE id_usuario = ?");
    $stmtOtrasEmpresas->execute([$data['id_usuario']]);
    $tieneOtrasEmpresas = $stmtOtrasEmpresas->fetchColumn() > 0;
    
    // Si no pertenece a otras empresas, eliminar completamente el usuario
    if (!$tieneOtrasEmpresas) {
        // Eliminar fichajes
        $pdo->prepare("DELETE FROM FICHAJE WHERE id_usuario = ?")->execute([$data['id_usuario']]);
        
        // Eliminar horarios
        $pdo->prepare("DELETE FROM HORARIOS WHERE id_usuario = ?")->execute([$data['id_usuario']]);
        
        // Eliminar vacaciones
        $pdo->prepare("DELETE FROM VACACIONES WHERE id_usuario = ?")->execute([$data['id_usuario']]);
        $pdo->prepare("DELETE FROM VACACIONES_ACUMULADO WHERE id_usuario = ?")->execute([$data['id_usuario']]);
        
        // Eliminar usuario
        $pdo->prepare("DELETE FROM USUARIO WHERE id_usuario = ?")->execute([$data['id_usuario']]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Empleado eliminado correctamente'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Error en empleado_eliminar.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>