<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

// Log para debug
error_log('=== empleado_actualizar.php iniciado ===');

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    error_log('Usuario no autorizado');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
error_log('Datos recibidos: ' . print_r($data, true));

if (!isset($data['id_usuario'])) {
    error_log('ID de usuario no proporcionado');
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
    exit;
}

$empresa = $_SESSION['empresa_activa'];
error_log('Empresa activa: ' . $empresa);

try {
    $pdo->beginTransaction();
    
    // Verificar que el usuario pertenece a la empresa
    $stmtVerif = $pdo->prepare("SELECT 1 FROM EMPRESA_USUARIO WHERE id_usuario = ? AND id_empresa = ?");
    $stmtVerif->execute([$data['id_usuario'], $empresa]);
    
    if (!$stmtVerif->fetch()) {
        throw new Exception('Usuario no pertenece a esta empresa');
    }
    
    // Verificar si el email ya existe en otro usuario
    if (isset($data['email'])) {
        $stmtCheckEmail = $pdo->prepare("SELECT id_usuario FROM USUARIO WHERE email = ? AND id_usuario != ?");
        $stmtCheckEmail->execute([$data['email'], $data['id_usuario']]);
        
        if ($stmtCheckEmail->fetch()) {
            throw new Exception('El email ya está siendo usado por otro usuario');
        }
    }
    
    // Actualizar datos del usuario
    $sqlUsuario = "UPDATE USUARIO SET 
        nombre = ?,
        apellidos = ?,
        NIF = ?,
        Numero_Afiliciacion = ?,
        email = ?";
    
    $paramsUsuario = [
        $data['nombre'] ?? '',
        $data['apellidos'] ?? '',
        $data['NIF'] ?? null,
        $data['Numero_Afiliciacion'] ?? null,
        $data['email'] ?? ''
    ];
    
    // Si se proporciona nueva contraseña, actualizarla
    if (!empty($data['password'])) {
        $sqlUsuario .= ", password_hash = ?";
        $paramsUsuario[] = password_hash($data['password'], PASSWORD_DEFAULT);
        error_log('Actualizando contraseña');
    }
    
    $sqlUsuario .= " WHERE id_usuario = ?";
    $paramsUsuario[] = $data['id_usuario'];
    
    error_log('SQL Usuario: ' . $sqlUsuario);
    error_log('Params Usuario: ' . print_r($paramsUsuario, true));
    
    $stmtUsuario = $pdo->prepare($sqlUsuario);
    $stmtUsuario->execute($paramsUsuario);
    
    error_log('Usuario actualizado en tabla USUARIO');
    
    // Actualizar relación con la empresa (admin y activo)
    $sqlEmpresaUsuario = "UPDATE EMPRESA_USUARIO SET 
        admin = ?,
        activo = ?
        WHERE id_usuario = ? AND id_empresa = ?";
    
    $adminValue = isset($data['admin']) ? (int)$data['admin'] : 0;
    $activoValue = isset($data['activo']) ? (int)$data['activo'] : 1;
    
    error_log('Admin: ' . $adminValue . ', Activo: ' . $activoValue);
    
    $stmtEmpresaUsuario = $pdo->prepare($sqlEmpresaUsuario);
    $stmtEmpresaUsuario->execute([
        $adminValue,
        $activoValue,
        $data['id_usuario'],
        $empresa
    ]);
    
    error_log('Usuario actualizado en tabla EMPRESA_USUARIO');
    
    $pdo->commit();
    
    error_log('=== Actualización exitosa ===');
    
    echo json_encode([
        'success' => true,
        'message' => 'Empleado actualizado correctamente'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('ERROR: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>