<?php 
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once __DIR__ . "/../config.php";

// Verificar que existe la sesión
if(!isset($_SESSION['usuario']['id'])) {
    header("Location: ../login.php");
    exit();
}

$id_usuario = $_SESSION['usuario']['id'];
$accion = $_POST['accion'] ?? '';

// Buscar último fichaje del día
$sql = "SELECT * FROM FICHAJE 
        WHERE id_usuario = :id_usuario 
        AND fecha = CURDATE() 
        ORDER BY id_fichaje DESC 
        LIMIT 1";
        
$stmt = $pdo->prepare($sql);
$stmt->execute(['id_usuario' => $id_usuario]);
$fichaje = $stmt->fetch(PDO::FETCH_ASSOC);


// ENTRADA - Crear nuevo fichaje solo si NO existe uno hoy o si el último ya tiene salida
if($accion === 'entrada'){
    if(!$fichaje || $fichaje['hora_salida'] !== null) {
        
        
        $hora_ajustada = date("H:i:s", strtotime("-5 minute"));
        
        $sqlInsert = "INSERT INTO FICHAJE (id_usuario, fecha, hora_entrada, tipo) 
                      VALUES (:id_usuario, CURDATE(), :hora_entrada, 'normal')";
        
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([
            'id_usuario' => $id_usuario,
            'hora_entrada' => $hora_ajustada // Pasamos la hora con el minuto restado
        ]);
    }
}

// PAUSA - Solo si existe fichaje hoy, tiene entrada y NO tiene pausa registrada
if($accion === "pausa" && $fichaje){
    if($fichaje['hora_entrada'] !== null && $fichaje['hora_pausa'] === null) {
        $sql = "UPDATE FICHAJE SET hora_pausa = CURTIME() WHERE id_fichaje = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $fichaje['id_fichaje']]);
    }
}

// REANUDAR - Solo si existe fichaje hoy, tiene pausa y NO tiene reanudación
if($accion === "reanudar" && $fichaje){
    if($fichaje['hora_pausa'] !== null && $fichaje['hora_reanudacion'] === null) {
        $sql = "UPDATE FICHAJE SET hora_reanudacion = CURTIME() WHERE id_fichaje = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $fichaje['id_fichaje']]);
    }
}

// SALIDA - Solo si existe fichaje hoy y NO tiene salida registrada
// PERMITE finalizar incluso si está en pausa (sin reanudar)
if($accion === "salida" && $fichaje){
    if($fichaje['hora_entrada'] !== null && $fichaje['hora_salida'] === null) {
        $sql = "UPDATE FICHAJE SET hora_salida = CURTIME() WHERE id_fichaje = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $fichaje['id_fichaje']]);
    }
}

// Redirección
header("Location: ../panel.php?seccion=fichaje");
exit();
?>