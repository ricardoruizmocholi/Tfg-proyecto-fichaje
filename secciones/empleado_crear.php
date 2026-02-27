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

if (!isset($data['nombre']) || !isset($data['apellidos']) || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$empresa = $_SESSION['empresa_activa'];

try {
    $pdo->beginTransaction();
    
    // Verificar si el email ya existe
    $stmtCheck = $pdo->prepare("SELECT id_usuario FROM USUARIO WHERE email = ?");
    $stmtCheck->execute([$data['email']]);
    
    if ($stmtCheck->fetch()) {
        throw new Exception('El email ya está registrado en el sistema');
    }
    
    // Hashear la contraseña
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Insertar el nuevo usuario
    $sqlUsuario = "INSERT INTO USUARIO (
        nombre,
        apellidos,
        NIF,
        Numero_Afiliciacion,
        email,
        password_hash,
        activo,
        foto_perfil
    ) VALUES (?, ?, ?, ?, ?, ?, 1, 'secciones/uploads/perfil_default.jpg')";
    
    $stmtUsuario = $pdo->prepare($sqlUsuario);
    $stmtUsuario->execute([
        $data['nombre'],
        $data['apellidos'],
        $data['NIF'],
        $data['Numero_Afiliciacion'],
        $data['email'],
        $passwordHash
    ]);
    
    $nuevoIdUsuario = $pdo->lastInsertId();
    
    // Vincular el usuario con la empresa
    $sqlEmpresaUsuario = "INSERT INTO EMPRESA_USUARIO (
        id_empresa,
        id_usuario,
        admin,
        activo
    ) VALUES (?, ?, ?, ?)";
    
    $stmtEmpresaUsuario = $pdo->prepare($sqlEmpresaUsuario);
    $stmtEmpresaUsuario->execute([
        $empresa,
        $nuevoIdUsuario,
        $data['admin'] ?? 0,
        $data['activo'] ?? 1
    ]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Empleado creado correctamente',
        'id_usuario' => $nuevoIdUsuario
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Error en empleado_crear.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>