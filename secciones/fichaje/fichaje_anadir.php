<?php
/*
 * fichaje_anadir.php — Endpoint POST del módulo Fichaje
 * Añade un registro de fichaje de forma manual/retroactiva.
 * Acceso: solo admin. Valida que el empleado pertenezca a la empresa activa.
 * Devuelve JSON {success, message}.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_usuario']) || !isset($data['fecha'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$empresa   = $_SESSION['empresa_activa'];
$idUsuario = $data['id_usuario'];
$fecha     = $data['fecha'];
$tipo      = $data['tipo'] ?? 'normal';

try {
    // Verificar que el usuario pertenece a la empresa
    $stmtVerif = $pdo->prepare("
        SELECT 1 FROM EMPRESA_USUARIO 
        WHERE id_usuario = ? AND id_empresa = ? AND activo = 1
    ");
    $stmtVerif->execute([$idUsuario, $empresa]);
    if (!$stmtVerif->fetch()) {
        throw new Exception('Usuario no pertenece a la empresa');
    }
    
    // Contar cuántos fichajes hay ya para ese usuario/día
    $stmtContar = $pdo->prepare("
        SELECT COUNT(*) FROM FICHAJE 
        WHERE id_usuario = ? AND fecha = ?
    ");
    $stmtContar->execute([$idUsuario, $fecha]);
    $numExistentes = (int)$stmtContar->fetchColumn();

    // Reglas:
    // - Si no hay ninguno → permitir siempre
    // - Si hay 1 y el nuevo tipo es 'partida_tarde' → permitir (segundo tramo)
    // - Si ya hay 2 o más → no permitir más
    // - Si hay 1 y el nuevo tipo NO es 'partida_tarde' → ya existe, rechazar
    if ($numExistentes >= 2) {
        throw new Exception('Ya existen 2 fichajes para este usuario en esta fecha (jornada partida completa)');
    }

    if ($numExistentes === 1 && $tipo !== 'partida_tarde') {
        throw new Exception('Ya existe un fichaje para este usuario en esta fecha. Para añadir jornada partida usa el tipo "partida_tarde"');
    }
    
    // Insertar fichaje
    $sql = "INSERT INTO FICHAJE (
        id_usuario, fecha,
        hora_entrada, hora_pausa, hora_reanudacion, hora_salida,
        tipo, observaciones
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $idUsuario, $fecha,
        !empty($data['hora_entrada'])     ? $data['hora_entrada']     : null,
        !empty($data['hora_pausa'])       ? $data['hora_pausa']       : null,
        !empty($data['hora_reanudacion']) ? $data['hora_reanudacion'] : null,
        !empty($data['hora_salida'])      ? $data['hora_salida']      : null,
        $tipo,
        $data['observaciones'] ?? null
    ]);

    $idNuevoFichaje = $pdo->lastInsertId();

    // Notificación al empleado
    try {
        $fechaFormateada = date('d/m/Y', strtotime($fecha));
        $esTrameTarde    = ($tipo === 'partida_tarde');
        $msgTramo        = $esTrameTarde ? 'tramo de tarde' : 'fichaje';
        $mensaje = "➕ El administrador ha añadido un nuevo $msgTramo a tu registro del día $fechaFormateada.";

        $pdo->prepare("INSERT INTO NOTIFICACIONES (id_usuario, mensaje, tipo) VALUES (?, ?, 'fichaje_anadido')")
            ->execute([$idUsuario, $mensaje]);
    } catch (Exception $eNotif) {
        error_log("Error notificando fichaje añadido: " . $eNotif->getMessage());
    }
    
    echo json_encode([
        'success'    => true,
        'message'    => 'Fichaje añadido correctamente',
        'id_fichaje' => $idNuevoFichaje
    ]);
    
} catch (Exception $e) {
    error_log('Error en fichaje_anadir.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>