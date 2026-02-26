<?php 
session_start();
require_once 'config.php';



//------------------------------------------------
// Validar que se envien los datos del formulario
//------------------------------------------------

if(!isset($_POST['email']) || !isset($_POST['password'])) {
    die("Por favor complete el formulario de login.");
}

// Variables del formulario
$email =htmlspecialchars(trim($_POST['email']));
$password = trim($_POST['password']);

//------------------------------------------------
// Buscar el usuario en la base de datos
//------------------------------------------------

$sqlBuscarUsuario = "SELECT * FROM USUARIO WHERE email = :email AND activo = 1 LIMIT 1";
// $pdo es la conexion a la base de datos creada en config.php, objeto PDO de la  base de datos
$consultaUsuario = $pdo->prepare($sqlBuscarUsuario);
// execute inserta el valor en :email, escapa el valor para evitar inyeccion SQL y ejecuta la consulta
$consultaUsuario->execute(['email' => $email]);

// Fetch obtiene el primer resultado de la consulta y lo guarda en un array asociativo  
$usuarioEncontrado = $consultaUsuario->fetch(PDO::FETCH_ASSOC);

//si no se encuentra el usuario o esta inactivo
if(!$usuarioEncontrado) {
    die("usuario no encontrado.");
}

//------------------------------------------------
// Verificar la contraseña
//------------------------------------------------

$hashGuardado = $usuarioEncontrado['password_hash'];

if(!password_verify($password, $hashGuardado)) {
    die("Usuario o contraseña incorrectos.");
}

//------------------------------------------------
// Consultar las empresas asociadas al usuario
//------------------------------------------------

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
// fetchAll obtiene todos los resultados de la consulta y los guarda en un array de arrays asociativos
$empresasAsociadas = $consultaEmpresas->fetchAll(PDO::FETCH_ASSOC);

// si el ususario no tiene empresas asociadas
if(count($empresasAsociadas) === 0) {
    die("El usuario no tiene empresas asociadas.");
}

//------------------------------------------------
// Guardar datos del usuario en la sesión
//------------------------------------------------

$_SESSION['usuario'] = [
    "id" => $usuarioEncontrado['id_usuario'],
    "nombre" => $usuarioEncontrado['nombre'],
    "apellidos" => $usuarioEncontrado['apellidos'],
    "email" => $usuarioEncontrado['email'],
    "foto_perfil" => $usuarioEncontrado['foto_perfil'],
    "empresas" => $empresasAsociadas
];

//------------------------------------------------
// Redirección lógica según cantidad de empresas
//------------------------------------------------

if(count($empresasAsociadas) === 1) {
    // Si solo tiene una empresa, redirigir directamente al panel de esa empresa
    $empresa = $empresasAsociadas[0];

    $_SESSION['empresa_activa'] = $empresa['id_empresa'];

    $_SESSION['es_admin'] = $empresa['admin'];

    // Redirigir al panel de destino de la empresa
    header("Location: panel.php");
    exit();
} else {
    // Si tiene varias empresas, redirigir a la página de selección de empresa
    header("Location: seleccionar_empresa.php");
    exit();
}



?>