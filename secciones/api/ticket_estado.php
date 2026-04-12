<?php
// secciones/api/ticket_estado.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
}

$data         = json_decode(file_get_contents('php://input'), true);
$idEmpresa    = (int)$_SESSION['empresa_activa'];
$idAdmin      = (int)$_SESSION['usuario']['id'];
$idIncidencia = (int)($data['id_incidencia'] ?? 0);
$estado       = $data['estado'] ?? '';

$estadosValidos = ['PENDIENTE', 'EN_PROCESO', 'RESUELTA', 'CERRADA'];
if (!$idIncidencia || !in_array($estado, $estadosValidos)) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']); exit;
}

try {
    // Verificar pertenencia a la empresa
    $stmtV = $pdo->prepare("SELECT id_usuario, asunto, estado FROM incidencias WHERE id_incidencia=? AND id_empresa=?");
    $stmtV->execute([$idIncidencia, $idEmpresa]);
    $ticket = $stmtV->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        echo json_encode(['success' => false, 'message' => 'Ticket no encontrado']); exit;
    }

    $cerradoPor = in_array($estado, ['RESUELTA','CERRADA']) ? $idAdmin : null;

    $pdo->prepare("
        UPDATE incidencias
        SET estado = ?, cerrado_por = ?, fecha_respuesta = NOW()
        WHERE id_incidencia = ? AND id_empresa = ?
    ")->execute([$estado, $cerradoPor, $idIncidencia, $idEmpresa]);

    // Notificar al empleado del cambio de estado
    $mensajes = [
        'EN_PROCESO' => " Tu ticket '{$ticket['asunto']}' está siendo procesado.",
        'RESUELTA'   => " Tu ticket '{$ticket['asunto']}' ha sido marcado como resuelto.",
        'CERRADA'    => " Tu ticket '{$ticket['asunto']}' ha sido cerrado.",
    ];

    if (isset($mensajes[$estado])) {
        $pdo->prepare("INSERT INTO notificaciones (id_usuario, mensaje, tipo) VALUES (?, ?, 'estado_ticket')")
            ->execute([$ticket['id_usuario'], $mensajes[$estado]]);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log('Error ticket_estado: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>