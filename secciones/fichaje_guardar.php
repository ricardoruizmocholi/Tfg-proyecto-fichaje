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

if (!isset($data['cambios']) || !is_array($data['cambios'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$empresa = $_SESSION['empresa_activa'];
$cambios = $data['cambios'];

try {
    $pdo->beginTransaction();
    
    foreach ($cambios as $cambio) {
        $idFichaje = $cambio['id_fichaje'] ?? null;
        $idUsuario = $cambio['id_usuario'];
        $fecha = $cambio['fecha'];
        
        // Verificar que el usuario pertenece a la empresa
        $stmtVerif = $pdo->prepare("
            SELECT 1 FROM EMPRESA_USUARIO 
            WHERE id_usuario = ? AND id_empresa = ? AND activo = 1
        ");
        $stmtVerif->execute([$idUsuario, $empresa]);
        
        if (!$stmtVerif->fetch()) {
            throw new Exception('Usuario no pertenece a la empresa');
        }
        
        if ($idFichaje) {
            // Actualizar fichaje existente
            $campos = [];
            $valores = [];
            
            if (isset($cambio['hora_entrada'])) {
                $campos[] = "hora_entrada = ?";
                $valores[] = $cambio['hora_entrada'];
            }
            if (isset($cambio['hora_pausa'])) {
                $campos[] = "hora_pausa = ?";
                $valores[] = $cambio['hora_pausa'];
            }
            if (isset($cambio['hora_reanudacion'])) {
                $campos[] = "hora_reanudacion = ?";
                $valores[] = $cambio['hora_reanudacion'];
            }
            if (isset($cambio['hora_salida'])) {
                $campos[] = "hora_salida = ?";
                $valores[] = $cambio['hora_salida'];
            }
            if (isset($cambio['observaciones'])) {
                $campos[] = "observaciones = ?";
                $valores[] = $cambio['observaciones'];
            }
            
            if (count($campos) > 0) {
                $valores[] = $idFichaje;
                $sql = "UPDATE FICHAJE SET " . implode(', ', $campos) . " WHERE id_fichaje = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($valores);
            }
        } else {
            // Crear nuevo fichaje si se modificaron campos
            $tieneHoras = false;
            foreach (['hora_entrada', 'hora_pausa', 'hora_reanudacion', 'hora_salida'] as $campo) {
                if (isset($cambio[$campo]) && $cambio[$campo]) {
                    $tieneHoras = true;
                    break;
                }
            }
            
            if ($tieneHoras) {
                $sql = "INSERT INTO FICHAJE (id_usuario, fecha, hora_entrada, hora_pausa, hora_reanudacion, hora_salida, tipo, observaciones) 
                        VALUES (?, ?, ?, ?, ?, ?, 'normal', ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $idUsuario,
                    $fecha,
                    $cambio['hora_entrada'] ?? null,
                    $cambio['hora_pausa'] ?? null,
                    $cambio['hora_reanudacion'] ?? null,
                    $cambio['hora_salida'] ?? null,
                    $cambio['observaciones'] ?? null
                ]);
            }
        }
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Cambios guardados correctamente']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Error en fichaje_guardar.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
}
?>