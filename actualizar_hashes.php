<?php
require_once 'config.php';
// Paso 1: Obtener todos los usuarios con su contraseña en texto plano
$sqlSelect = "SELECT id_usuario, password_hash FROM USUARIO";
$stmtSelect = $pdo->prepare($sqlSelect);
$stmtSelect->execute();

$usuarios = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

foreach ($usuarios as $usuario) {
    $id = $usuario['id_usuario'];
    $passPlano = $usuario['password_hash']; // Aquí está la contraseña en texto plano

    // Saltar si la contraseña ya está hasheada (opcional, para no rehashear)
    // Asumimos que un hash tiene longitud >= 60 (bcrypt)
    if (strlen($passPlano) >= 60 && preg_match('/^\$2[ayb]\$.{56}$/', $passPlano)) {
        echo "Usuario $id ya tiene contraseña hasheada. Saltando.\n";
        continue;
    }

    // Paso 2: Generar el hash seguro
    $hashSeguro = password_hash($passPlano, PASSWORD_DEFAULT);

    // Paso 3: Actualizar la base de datos
    $sqlUpdate = "UPDATE USUARIO SET password_hash = :hash WHERE id_usuario = :id_usuario";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute(['hash' => $hashSeguro, 'id_usuario' => $id]);

    echo "Usuario $id actualizado correctamente.\n";
}

echo "Actualización completada.";

?>