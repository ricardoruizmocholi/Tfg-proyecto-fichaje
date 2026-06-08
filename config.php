<?php
/*
 * config.php — Conexión global a la base de datos
 * Crea el objeto PDO ($pdo) para MySQL con la base de datos sistema_fichajes.
 * Incluido por panel.php y por todos los endpoints que acceden a la BD.
 * Si la conexión falla en una petición AJAX devuelve JSON de error; en páginas normales hace die().
 */
require_once __DIR__ . '/secrets.php';

$host = "localhost";
$user = "root";
$pass = "";
$db   = "sistema_fichajes";



try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {

     // Si es una petición AJAX (JSON), devolver JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
        exit;
    }
    // Si no, mostrar error normal

    die("Error de conexión: " . $e->getMessage());
}
?>