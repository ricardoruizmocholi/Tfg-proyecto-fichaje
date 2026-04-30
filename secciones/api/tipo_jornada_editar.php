<?php
/*
 * tipo_jornada_editar.php — Endpoint POST de la API de Tipos de Jornada
 * Actualiza nombre_display, color_hex o es_productivo de una plantilla custom.
 * Acceso: solo admin. Valida que la plantilla pertenezca a la empresa activa.
 * Devuelve JSON {success, message}.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success'=>false,'message'=>'No autorizado']); exit;
}

$data      = json_decode(file_get_contents('php://input'), true);
$idEmpresa = (int)$_SESSION['empresa_activa'];
$id        = (int)($data['id_tipo_custom'] ?? 0);

if (!$id) { echo json_encode(['success'=>false,'message'=>'ID inválido']); exit; }

$nombre = trim($data['nombre_display'] ?? '');
$color  = preg_match('/^#[0-9a-fA-F]{6}$/', $data['color_hex'] ?? '') ? $data['color_hex'] : '#667eea';

if (!$nombre) { echo json_encode(['success'=>false,'message'=>'El nombre es obligatorio']); exit; }

try {
    // Verificar que pertenece a esta empresa
    $stmtV = $pdo->prepare("SELECT 1 FROM tipos_jornada_custom WHERE id_tipo_custom=? AND id_empresa=?");
    $stmtV->execute([$id, $idEmpresa]);
    if (!$stmtV->fetch()) {
        echo json_encode(['success'=>false,'message'=>'Plantilla no encontrada']); exit;
    }

    $pdo->prepare("
        UPDATE tipos_jornada_custom SET
            nombre_display             = ?,
            color_hex                  = ?,
            hora_inicio_predeterminada = ?,
            hora_fin_predeterminada    = ?,
            es_productivo              = ?
        WHERE id_tipo_custom = ? AND id_empresa = ?
    ")->execute([
        $nombre, $color,
        !empty($data['hora_inicio_predeterminada']) ? $data['hora_inicio_predeterminada'] : null,
        !empty($data['hora_fin_predeterminada'])    ? $data['hora_fin_predeterminada']    : null,
        isset($data['es_productivo'])               ? (int)$data['es_productivo']         : 1,
        $id, $idEmpresa
    ]);

    echo json_encode(['success'=>true]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}