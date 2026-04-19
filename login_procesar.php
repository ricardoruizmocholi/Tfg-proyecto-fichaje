<?php 
session_start();
require_once 'config.php';

// Siempre devolver JSON
header('Content-Type: application/json');

// Validar que se envíen los datos del formulario
if (!isset($_POST['email']) || !isset($_POST['password'])) {
    echo json_encode(['success' => false, 'message' => 'Por favor completa el formulario']);
    exit;
}

$email    = htmlspecialchars(trim($_POST['email']));
$password = trim($_POST['password']);

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'El email y la contraseña son obligatorios']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El formato del email no es válido']);
    exit;
}

// Buscar el usuario en la base de datos
$sqlBuscarUsuario = "SELECT * FROM USUARIO WHERE email = :email AND activo = 1 LIMIT 1";
$consultaUsuario  = $pdo->prepare($sqlBuscarUsuario);
$consultaUsuario->execute(['email' => $email]);
$usuarioEncontrado = $consultaUsuario->fetch(PDO::FETCH_ASSOC);

// Mensaje genérico para no revelar si el email existe
if (!$usuarioEncontrado) {
    echo json_encode(['success' => false, 'message' => 'Email o contraseña incorrectos']);
    exit;
}

// Verificar la contraseña
$hashGuardado = $usuarioEncontrado['password_hash'];

if (!password_verify($password, $hashGuardado)) {
    echo json_encode(['success' => false, 'message' => 'Email o contraseña incorrectos']);
    exit;
}

// Consultar las empresas asociadas al usuario
$sqlBuscarEmpresas = "SELECT 
            E.id_empresa,
            E.nombre,
            E.panel_destino,
            EU.admin
        FROM EMPRESA_USUARIO EU
        JOIN EMPRESA E ON E.id_empresa = EU.id_empresa
        WHERE EU.id_usuario = :id_usuario AND EU.activo = 1";

$consultaEmpresas = $pdo->prepare($sqlBuscarEmpresas);
$consultaEmpresas->execute(['id_usuario' => $usuarioEncontrado['id_usuario']]);
$empresasAsociadas = $consultaEmpresas->fetchAll(PDO::FETCH_ASSOC);

if (count($empresasAsociadas) === 0) {
    echo json_encode(['success' => false, 'message' => 'Este usuario no tiene empresas asociadas. Contacta con el administrador.']);
    exit;
}

// Guardar datos del usuario en la sesión
$_SESSION['usuario'] = [
    "id"        => $usuarioEncontrado['id_usuario'],
    "nombre"    => $usuarioEncontrado['nombre'],
    "apellidos" => $usuarioEncontrado['apellidos'],
    "email"     => $usuarioEncontrado['email'],
    "foto_perfil" => $usuarioEncontrado['foto_perfil'],
    "empresas"  => $empresasAsociadas
];

// Redirección según cantidad de empresas
if (count($empresasAsociadas) === 1) {
    $empresa = $empresasAsociadas[0];
    $_SESSION['empresa_activa'] = $empresa['id_empresa'];
    $_SESSION['es_admin']       = $empresa['admin'];
    echo json_encode(['success' => true, 'redirect' => 'panel.php']);
} else {
    echo json_encode(['success' => true, 'redirect' => 'seleccionar_empresa.php']);
}