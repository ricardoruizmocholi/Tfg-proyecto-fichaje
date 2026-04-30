<?php
/*
 * fichaje_accion.php — Endpoint POST del módulo Fichaje
 * Registra la acción de fichaje (entrada, pausa, reanudación, salida) en la tabla FICHAJE.
 * Acceso: empleado y admin. Llamado desde los formularios de fichaje.php.
 * Verifica IP autorizada si la empresa tiene restricción activa.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../config.php";

if(!isset($_SESSION['usuario']['id'])) {
    header("Location: ../login.php");
    exit();
}

// ── Validación de IP (lee config de BD, no de config.php) ──
require_once __DIR__ . '/../api/fichaje_ip_helper.php';
if (!fichaje_ip_permitida($pdo, (int)$_SESSION['empresa_activa'])) {
    header("Location: ../../panel.php?seccion=fichaje&error=ip_restringida");
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

// Para jornada normal, el fichaje "activo" es el que tiene entrada pero NO salida.
// Puede ser [0], [1], [2]... si el empleado fichó salida por error y re-fichó entrada.
$fichajeActivo = null;
foreach ($fichajesHoy as $f) {
    if ($f['hora_entrada'] !== null && $f['hora_salida'] === null) {
        $fichajeActivo = $f;
        break;
    }
}

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
// PAUSA — usa el fichaje activo (puede ser [1], [2]... no solo [0])
// ------------------------------------------------------------------
if ($accion === 'pausa' && $fichajeActivo) {
    if ($fichajeActivo['hora_pausa'] === null) {
        $pdo->prepare("UPDATE FICHAJE SET hora_pausa = CURTIME() WHERE id_fichaje = :id")
            ->execute(['id' => $fichajeActivo['id_fichaje']]);
    }
}

// ------------------------------------------------------------------
// REANUDAR — usa el fichaje activo
// ------------------------------------------------------------------
if ($accion === 'reanudar' && $fichajeActivo) {
    if ($fichajeActivo['hora_pausa'] !== null && $fichajeActivo['hora_reanudacion'] === null) {
        $pdo->prepare("UPDATE FICHAJE SET hora_reanudacion = CURTIME() WHERE id_fichaje = :id")
            ->execute(['id' => $fichajeActivo['id_fichaje']]);
    }
}

// ------------------------------------------------------------------
// SALIDA NORMAL — usa el fichaje activo
// El empleado puede haber fichado entrada/salida/entrada: hay que cerrar
// el último abierto, no siempre el [0].
// ------------------------------------------------------------------
if ($accion === 'salida' && $fichajeActivo) {
    $pdo->prepare("UPDATE FICHAJE SET hora_salida = CURTIME() WHERE id_fichaje = :id")
        ->execute(['id' => $fichajeActivo['id_fichaje']]);
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

header("Location: ../../panel.php?seccion=fichaje");
exit();
?>