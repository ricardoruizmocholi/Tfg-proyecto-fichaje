<?php
// secciones/api/tipo_jornada_eliminar.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success'=>false,'message'=>'No autorizado']); exit;
}

$data      = json_decode(file_get_contents('php://input'), true);
$idEmpresa = (int)$_SESSION['empresa_activa'];
$id        = (int)($data['id_tipo_custom'] ?? 0);

try {
    // La FK tiene ON DELETE SET NULL, así los horarios existentes no se rompen
    $pdo->prepare("DELETE FROM tipos_jornada_custom WHERE id_tipo_custom=? AND id_empresa=?")
        ->execute([$id, $idEmpresa]);
    echo json_encode(['success'=>true]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}