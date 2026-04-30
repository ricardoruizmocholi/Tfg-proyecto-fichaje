<?php
/*
 * eliminar_horario_temporal.php — Endpoint POST de la API de Horario
 * Elimina un horario temporal (no aprobado) del calendario del empleado.
 * Acepta fecha o ID en el body JSON. Solo borra registros del empleado en sesión.
 * Acceso: empleado y admin. Devuelve JSON {success, message}.
 */
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
// El calendario suele enviar la fecha o un 'id' que es la fecha
$fecha = $data['fecha'] ?? $data['id'] ?? null;

if (!$fecha) {
    echo json_encode(['success' => false, 'message' => 'No se proporcionó la fecha']);
    exit;
}

if (isset($_SESSION['horarios_temporales'][$fecha])) {
    // Borramos el registro usando la fecha como clave
    unset($_SESSION['horarios_temporales'][$fecha]);
    echo json_encode(['success' => true]);
} else {
    // Si no lo encuentra, comprobamos si es un registro múltiple (ej: 2026-02-12_1)
    $encontrado = false;
    foreach ($_SESSION['horarios_temporales'] as $key => $val) {
        if (strpos($key, $fecha) === 0) { // Si la clave empieza por la fecha
            unset($_SESSION['horarios_temporales'][$key]);
            $encontrado = true;
        }
    }

    if ($encontrado) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró el borrador para ' . $fecha]);
    }
}