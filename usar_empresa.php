<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['id_empresa'])) {
    die("Datos incompletos.");
}

$id_empresa_seleccionada = $_POST['id_empresa'];

// Guardar empresa activa
$_SESSION['empresa_activa'] = $id_empresa_seleccionada;

// Guardar admin según la empresa seleccionada
foreach ($_SESSION['usuario']['empresas'] as $empresa) {
    if ($empresa['id_empresa'] == $id_empresa_seleccionada) {
        $_SESSION['es_admin'] = $empresa['admin'];
        break;
    }
}

// Siempre redirige a panel.php
header("Location: panel.php");
exit;
?>
