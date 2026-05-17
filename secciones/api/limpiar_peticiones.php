<?php
/*
 * limpiar_peticiones.php — Endpoint POST
 * Elimina solicitudes de horario resueltas (APROBADO/RECHAZADO) con más de
 * DIAS_RETENER_PETICIONES días desde su validación. Nunca toca PENDIENTES.
 * Acceso: solo admin. Devuelve JSON {success, eliminadas}.
 */
session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

defined('DIAS_RETENER_PETICIONES') || define('DIAS_RETENER_PETICIONES', 30);

$idEmpresa = (int)$_SESSION['empresa_activa'];

try {
    // Borrar primero los detalles (tabla hija)
    $pdo->prepare("
        DELETE D FROM detalle_solicitud_horario D
        INNER JOIN solicitudes_horario S ON D.id_solicitud = S.id_solicitud
        WHERE S.id_empresa = ?
          AND S.estado IN ('APROBADO','RECHAZADO')
          AND S.validado_en < DATE_SUB(NOW(), INTERVAL " . DIAS_RETENER_PETICIONES . " DAY)
    ")->execute([$idEmpresa]);

    // Borrar después las solicitudes (tabla padre)
    $stmtP = $pdo->prepare("
        DELETE FROM solicitudes_horario
        WHERE id_empresa = ?
          AND estado IN ('APROBADO','RECHAZADO')
          AND validado_en < DATE_SUB(NOW(), INTERVAL " . DIAS_RETENER_PETICIONES . " DAY)
    ");
    $stmtP->execute([$idEmpresa]);

    echo json_encode(['success' => true, 'eliminadas' => $stmtP->rowCount()]);

} catch (PDOException $e) {
    error_log('limpiar_peticiones: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al limpiar peticiones']);
}
?>
