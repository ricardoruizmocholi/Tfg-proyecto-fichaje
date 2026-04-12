<?php
// secciones/api/tipo_jornada_crear.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success'=>false,'message'=>'No autorizado']); exit;
}

$data      = json_decode(file_get_contents('php://input'), true);
$idEmpresa = (int)$_SESSION['empresa_activa'];

$nombre = trim($data['nombre_display'] ?? '');
$color  = preg_match('/^#[0-9a-fA-F]{6}$/', $data['color_hex'] ?? '') ? $data['color_hex'] : '#667eea';

if (!$nombre) {
    echo json_encode(['success'=>false,'message'=>'El nombre es obligatorio']); exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO tipos_jornada_custom
            (id_empresa, nombre_display, color_hex,
             hora_inicio_predeterminada, hora_fin_predeterminada, es_productivo)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $idEmpresa,
        $nombre,
        $color,
        !empty($data['hora_inicio_predeterminada']) ? $data['hora_inicio_predeterminada'] : null,
        !empty($data['hora_fin_predeterminada'])    ? $data['hora_fin_predeterminada']    : null,
        isset($data['es_productivo'])               ? (int)$data['es_productivo']         : 1,
    ]);
    echo json_encode(['success'=>true, 'id_tipo_custom'=>$pdo->lastInsertId()]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}