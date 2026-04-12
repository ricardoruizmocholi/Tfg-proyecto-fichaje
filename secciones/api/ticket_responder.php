<?php
// secciones/api/ticket_responder.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
}

$data         = json_decode(file_get_contents('php://input'), true);
$idUsuario    = (int)$_SESSION['usuario']['id'];
$idEmpresa    = (int)$_SESSION['empresa_activa'];
$esAdmin      = (bool)$_SESSION['es_admin'];
$idIncidencia = (int)($data['id_incidencia'] ?? 0);
$mensaje      = trim($data['mensaje'] ?? '');
$resolver     = (bool)($data['resolver'] ?? false); // solo admin

if (!$idIncidencia || !$mensaje) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']); exit;
}

try {
    // Verificar que el ticket pertenece a esta empresa
    // y que el usuario tiene acceso (es el dueño o es admin)
    $stmtV = $pdo->prepare("
        SELECT id_incidencia, id_usuario, estado, asunto
        FROM incidencias
        WHERE id_incidencia = ? AND id_empresa = ?
    ");
    $stmtV->execute([$idIncidencia, $idEmpresa]);
    $ticket = $stmtV->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        echo json_encode(['success' => false, 'message' => 'Ticket no encontrado']); exit;
    }
    if (!$esAdmin && $ticket['id_usuario'] != $idUsuario) {
        echo json_encode(['success' => false, 'message' => 'No tienes acceso a este ticket']); exit;
    }
    if ($ticket['estado'] === 'CERRADA') {
        echo json_encode(['success' => false, 'message' => 'El ticket está cerrado']); exit;
    }

    $pdo->beginTransaction();

    // Insertar mensaje en el hilo
    $pdo->prepare("
        INSERT INTO mensajes_ticket (id_incidencia, id_usuario, mensaje, es_admin, fecha_creacion)
        VALUES (?, ?, ?, ?, NOW())
    ")->execute([$idIncidencia, $idUsuario, $mensaje, $esAdmin ? 1 : 0]);

    // Actualizar fecha de respuesta y estado si es admin
    if ($esAdmin) {
        $nuevoEstado = $resolver ? 'RESUELTA' : 'EN_PROCESO';
        $pdo->prepare("
            UPDATE incidencias
            SET respuesta = ?, fecha_respuesta = NOW(), estado = ?, cerrado_por = ?
            WHERE id_incidencia = ?
        ")->execute([$mensaje, $nuevoEstado, $resolver ? $idUsuario : null, $idIncidencia]);

        // Notificar al empleado dueño del ticket
        $nombreAdmin = $_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellidos'];
        $msgNotif = $resolver
            ? "✅ Tu ticket '{$ticket['asunto']}' ha sido resuelto por {$nombreAdmin}"
            : "💬 {$nombreAdmin} respondió a tu ticket: {$ticket['asunto']}";

        $pdo->prepare("INSERT INTO notificaciones (id_usuario, mensaje, tipo) VALUES (?, ?, 'respuesta_ticket')")
            ->execute([$ticket['id_usuario'], $msgNotif]);
    } else {
        // Empleado responde: mover a PENDIENTE si estaba resuelta (reabre)
        if ($ticket['estado'] === 'RESUELTA') {
            $pdo->prepare("UPDATE incidencias SET estado='PENDIENTE' WHERE id_incidencia=?")
                ->execute([$idIncidencia]);
        }

        // Notificar a los admins
        $nombreEmp = $_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellidos'];
        $msgNotif  = "💬 {$nombreEmp} respondió al ticket: {$ticket['asunto']}";

        $stmtAdmins = $pdo->prepare("SELECT id_usuario FROM empresa_usuario WHERE id_empresa=? AND admin=1 AND activo=1");
        $stmtAdmins->execute([$idEmpresa]);
        $admins = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN);

        $stmtNotif = $pdo->prepare("INSERT INTO notificaciones (id_usuario, mensaje, tipo) VALUES (?, ?, 'respuesta_ticket')");
        foreach ($admins as $adminId) {
            $stmtNotif->execute([$adminId, $msgNotif]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Error ticket_responder: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>