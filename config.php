<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sistema_fichajes";

// En config.php
define('IP_OFICINA', '149.74.30.101'); // Sustituye por la IP pública real del despacho
// O si quieres permitir cualquier IP local (si el servidor está en la oficina):
define('RANGO_LOCAL', '192.168.1.');

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