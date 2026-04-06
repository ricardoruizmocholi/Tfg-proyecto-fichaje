<?php 
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once __DIR__ . "/../config.php";

if(!isset($_SESSION['usuario']['id'])) {
    header("Location: ../login.php");
    exit();
}

$id_usuario = $_SESSION['usuario']['id'];
$accion = $_POST['accion'] ?? '';

// Obtener todos los fichajes de hoy ordenados ASC
$sql = "SELECT * FROM FICHAJE 
        WHERE id_usuario = :id_usuario 
        AND fecha = CURDATE() 
        ORDER BY id_fichaje ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id_usuario' => $id_usuario]);
$fichajesHoy = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Primer fichaje = mañana/normal  |  Segundo fichaje = tarde (partida)
$fichaje      = $fichajesHoy[0] ?? null;
$fichajeTarde = $fichajesHoy[1] ?? null;

// ------------------------------------------------------------------
// ENTRADA NORMAL o TRAMO MAÑANA
// Crea un nuevo fichaje solo si NO hay ninguno abierto (sin salida)
// ------------------------------------------------------------------
if ($accion === 'entrada') {
    $hayAbierto = false;
    foreach ($fichajesHoy as $f) {
        if ($f['hora_salida'] === null) { $hayAbierto = true; break; }
    }
    if (!$hayAbierto) {
        $pdo->prepare("INSERT INTO FICHAJE (id_usuario, fecha, hora_entrada, tipo) VALUES (:u, CURDATE(), CURTIME(), 'normal')")
            ->execute(['u' => $id_usuario]);
    }
}

// ------------------------------------------------------------------
// PAUSA
// ------------------------------------------------------------------
if ($accion === 'pausa' && $fichaje) {
    if ($fichaje['hora_entrada'] !== null && $fichaje['hora_pausa'] === null && $fichaje['hora_salida'] === null) {
        $pdo->prepare("UPDATE FICHAJE SET hora_pausa = CURTIME() WHERE id_fichaje = :id")
            ->execute(['id' => $fichaje['id_fichaje']]);
    }
}

// ------------------------------------------------------------------
// REANUDAR
// ------------------------------------------------------------------
if ($accion === 'reanudar' && $fichaje) {
    if ($fichaje['hora_pausa'] !== null && $fichaje['hora_reanudacion'] === null && $fichaje['hora_salida'] === null) {
        $pdo->prepare("UPDATE FICHAJE SET hora_reanudacion = CURTIME() WHERE id_fichaje = :id")
            ->execute(['id' => $fichaje['id_fichaje']]);
    }
}

// ------------------------------------------------------------------
// SALIDA NORMAL o CIERRE TRAMO MAÑANA
// ------------------------------------------------------------------
if ($accion === 'salida' && $fichaje) {
    if ($fichaje['hora_entrada'] !== null && $fichaje['hora_salida'] === null) {
        $pdo->prepare("UPDATE FICHAJE SET hora_salida = CURTIME() WHERE id_fichaje = :id")
            ->execute(['id' => $fichaje['id_fichaje']]);
    }
}

// ------------------------------------------------------------------
// ENTRADA TARDE (segundo tramo jornada partida)
// Solo si el tramo mañana está cerrado
// ------------------------------------------------------------------
if ($accion === 'entrada_tarde') {
    $mananaCerrado = $fichaje && $fichaje['hora_salida'] !== null;
    if ($mananaCerrado) {
        // No crear si ya hay un fichaje de tarde abierto
        $tardeAbierto = $fichajeTarde && $fichajeTarde['hora_salida'] === null;
        if (!$tardeAbierto) {
            $pdo->prepare("INSERT INTO FICHAJE (id_usuario, fecha, hora_entrada, tipo) VALUES (:u, CURDATE(), CURTIME(), 'partida_tarde')")
                ->execute(['u' => $id_usuario]);
        }
    }
}

// ------------------------------------------------------------------
// SALIDA TARDE
// ------------------------------------------------------------------
if ($accion === 'salida_tarde') {
    // Recargar para coger el fichaje tarde aunque se haya creado justo ahora
    $stmt2 = $pdo->prepare("SELECT * FROM FICHAJE WHERE id_usuario = :u AND fecha = CURDATE() ORDER BY id_fichaje ASC");
    $stmt2->execute(['u' => $id_usuario]);
    $todos = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    $tardeFichaje = $todos[1] ?? null;
    if ($tardeFichaje && $tardeFichaje['hora_entrada'] !== null && $tardeFichaje['hora_salida'] === null) {
        $pdo->prepare("UPDATE FICHAJE SET hora_salida = CURTIME() WHERE id_fichaje = :id")
            ->execute(['id' => $tardeFichaje['id_fichaje']]);
    }
}

header("Location: ../panel.php?seccion=fichaje");
exit();
?>