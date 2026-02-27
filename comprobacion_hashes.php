<?php
require_once 'config.php';

$email = 'admin.global@example.com';
$passwordIntroducido = 'password1';

$sql = "SELECT password_hash FROM usuario WHERE email = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$usuario) {
    die("Usuario no encontrado");
}

$hash = $usuario['password_hash'];

var_dump($passwordIntroducido);
var_dump($hash);
var_dump(password_verify($passwordIntroducido, $hash));

if(password_verify($passwordIntroducido, $hash)) {
    echo "Login correcto!";
} else {
    echo "Usuario o contraseña incorrectos";
}

$password = 'password1';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hash generado: $hash\n";

if(password_verify($password, $hash)) {
    echo "Password_verify OK\n";
} else {
    echo "Password_verify FALLÓ\n";
}
echo "Longitud hash: " . strlen($hash) . "<br>";
