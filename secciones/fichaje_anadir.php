<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

// Verificar sesión y permisos de admin
if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_usuario']) || !isset($data['fecha'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$empresa = $_SESSION['empresa_activa'];
$idUsuario = $data['id_usuario'];
$fecha = $data['fecha'];

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
    
    // Verificar si ya existe un fichaje para ese día
    $stmtExiste = $pdo->prepare("
        SELECT id_fichaje FROM FICHAJE 
        WHERE id_usuario = ? AND fecha = ?
    ");
    $stmtExiste->execute([$idUsuario, $fecha]);
    
    if ($stmtExiste->fetch()) {
        throw new Exception('Ya existe un fichaje para este usuario en esta fecha');
    }
    
    // Insertar nuevo fichaje
    $sql = "INSERT INTO FICHAJE (
        id_usuario, 
        fecha, 
        hora_entrada, 
        hora_pausa, 
        hora_reanudacion, 
        hora_salida, 
        tipo, 
        observaciones
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $idUsuario,
        $fecha,
        !empty($data['hora_entrada']) ? $data['hora_entrada'] : null,
        !empty($data['hora_pausa']) ? $data['hora_pausa'] : null,
        !empty($data['hora_reanudacion']) ? $data['hora_reanudacion'] : null,
        !empty($data['hora_salida']) ? $data['hora_salida'] : null,
        $data['tipo'] ?? 'normal',
        $data['observaciones'] ?? null
    ]);

    // ============================================
    // NOTIFICACIÓN PARA EL EMPLEADO (Fichaje añadido)
    // ============================================
    try {
        $fechaFichaje = date('d/m/Y', strtotime($fecha));
        $mensaje = "➕ El administrador ha añadido un nuevo fichaje a tu registro del día $fechaFichaje.";

        $sqlInsNotif = "INSERT INTO NOTIFICACIONES (id_usuario, mensaje, tipo) VALUES (:id_u, :msg, 'fichaje_anadido')";
        $stmtInsNotif = $pdo->prepare($sqlInsNotif);
        $stmtInsNotif->execute([
            'id_u' => $idUsuario,
            'msg'  => $mensaje
        ]);
    } catch (Exception $e_notif) {
        error_log("Error notificando fichaje añadido: " . $e_notif->getMessage());
    }
    // ============================================
    
    echo json_encode([
        'success' => true, 
        'message' => 'Fichaje añadido correctamente',
        'id_fichaje' => $pdo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    error_log('Error en fichaje_anadir.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>