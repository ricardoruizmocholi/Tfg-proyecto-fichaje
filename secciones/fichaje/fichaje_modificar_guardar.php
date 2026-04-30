<?php
/*
 * fichaje_modificar_guardar.php — Endpoint POST del módulo Fichaje
 * Guarda los cambios de edición sobre un fichaje existente (hora entrada, pausa, salida, tipo).
 * Acceso: solo admin. Valida que el fichaje pertenezca a un empleado de la empresa activa.
 * Devuelve JSON {success, message}.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

// Verificar sesión y permisos de admin
if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_fichaje'])) {
    echo json_encode(['success' => false, 'message' => 'ID de fichaje no proporcionado']);
    exit;
}

$idFichaje = $data['id_fichaje'];
$empresa = $_SESSION['empresa_activa'];

try {
    // Verificar que el fichaje pertenece a un usuario de la empresa
    $stmtVerif = $pdo->prepare("
        SELECT F.id_fichaje 
        FROM FICHAJE F
        JOIN EMPRESA_USUARIO EU ON F.id_usuario = EU.id_usuario
        WHERE F.id_fichaje = ? AND EU.id_empresa = ? AND EU.activo = 1
    ");
    $stmtVerif->execute([$idFichaje, $empresa]);
    
    if (!$stmtVerif->fetch()) {
        throw new Exception('Fichaje no encontrado o no autorizado');
    }
    
    // Construir la actualización
    $campos = [];
    $valores = [];
    
    $camposPermitidos = ['hora_entrada', 'hora_pausa', 'hora_reanudacion', 'hora_salida', 'tipo', 'observaciones'];
    
    foreach ($camposPermitidos as $campo) {
        if (isset($data[$campo])) {
            $campos[] = "$campo = ?";
            $valores[] = !empty($data[$campo]) ? $data[$campo] : null;
        }
    }
    
    if (count($campos) === 0) {
        echo json_encode(['success' => true, 'message' => 'No hay cambios para guardar']);
        exit;
    }
    
    $valores[] = $idFichaje;
    $sql = "UPDATE FICHAJE SET " . implode(', ', $campos) . " WHERE id_fichaje = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    // ============================================
    // NOTIFICACIÓN PARA EL EMPLEADO (Cambio en Fichaje)
    // ============================================
    try {
        // 1. Obtener la fecha del fichaje y el ID del usuario afectado
        $stmtInfo = $pdo->prepare("SELECT id_usuario, DATE(hora_entrada) as fecha FROM FICHAJE WHERE id_fichaje = ?");
        $stmtInfo->execute([$idFichaje]);
        $fichajeInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

        if ($fichajeInfo) {
            $idEmpleado = $fichajeInfo['id_usuario'];
            $fechaFichaje = date('d/m/Y', strtotime($fichajeInfo['fecha']));
            
            $mensaje = " El administrador ha modificado tu fichaje del día $fechaFichaje.";

            $sqlInsNotif = "INSERT INTO NOTIFICACIONES (id_usuario, mensaje, tipo) VALUES (:id_u, :msg, 'cambio_fichaje')";
            $stmtInsNotif = $pdo->prepare($sqlInsNotif);
            $stmtInsNotif->execute([
                'id_u' => $idEmpleado,
                'msg'  => $mensaje
            ]);
        }
    } catch (Exception $e_notif) {
        error_log("Error notificando cambio de fichaje: " . $e_notif->getMessage());
        // No bloqueamos la respuesta principal si falla la notificación
    }
    // ============================================
    
    echo json_encode([
        'success' => true, 
        'message' => 'Fichaje actualizado correctamente'
    ]);
    
} catch (Exception $e) {
    error_log('Error en fichaje_modificar_guardar.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>