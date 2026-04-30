<?php
/*
 * ticket_crear.php — Endpoint POST de la API de Tickets
 * Crea un nuevo ticket de soporte en la tabla INCIDENCIAS.
 * Acceso: empleado y admin. Campos: asunto, descripcion, categoria, prioridad.
 * Devuelve JSON {success, message, id_incidencia}.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
}

$data      = json_decode(file_get_contents('php://input'), true);
$idUsuario = (int)$_SESSION['usuario']['id'];
$idEmpresa = (int)$_SESSION['empresa_activa'];

$asunto    = trim($data['asunto']    ?? '');
$mensaje   = trim($data['mensaje']   ?? '');
$categoria = in_array($data['categoria'] ?? '', ['HORARIO','NOMINA','VACACIONES','FICHAJE','OTRO'])
             ? $data['categoria'] : 'OTRO';
$prioridad = in_array($data['prioridad'] ?? '', ['BAJA','MEDIA','ALTA'])
             ? $data['prioridad'] : 'MEDIA';

if (!$asunto || !$mensaje) {
    echo json_encode(['success' => false, 'message' => 'Asunto y descripción son obligatorios']); exit;
}

try {
    $pdo->beginTransaction();

    // Crear el ticket
    $pdo->prepare("
        INSERT INTO incidencias (id_usuario, id_empresa, asunto, categoria, prioridad, mensaje, estado, fecha_creacion)
        VALUES (?, ?, ?, ?, ?, ?, 'PENDIENTE', NOW())
    ")->execute([$idUsuario, $idEmpresa, $asunto, $categoria, $prioridad, $mensaje]);

    $idIncidencia = $pdo->lastInsertId();

    // Añadir el primer mensaje al hilo
    $pdo->prepare("
        INSERT INTO mensajes_ticket (id_incidencia, id_usuario, mensaje, es_admin, fecha_creacion)
        VALUES (?, ?, ?, 0, NOW())
    ")->execute([$idIncidencia, $idUsuario, $mensaje]);

    // Notificar a los admins de la empresa
    $stmtAdmins = $pdo->prepare("SELECT id_usuario FROM empresa_usuario WHERE id_empresa = ? AND admin = 1 AND activo = 1");
    $stmtAdmins->execute([$idEmpresa]);
    $admins = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN);

    $nombreEmp = $_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellidos'];
    $msgNotif  = "🎫 Nuevo ticket de {$nombreEmp}: {$asunto}";

    $stmtNotif = $pdo->prepare("INSERT INTO notificaciones (id_usuario, mensaje, tipo) VALUES (?, ?, 'nuevo_ticket')");
    foreach ($admins as $adminId) {
        $stmtNotif->execute([$adminId, $msgNotif]);
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'id_incidencia' => $idIncidencia]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Error ticket_crear: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>