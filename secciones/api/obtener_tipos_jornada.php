<?php
/*
 * obtener_tipos_jornada.php — Endpoint GET de la API de Tipos de Jornada
 * Devuelve JSON con todos los tipos de jornada disponibles para rellenar selectores.
 * Combina los tipos ENUM genéricos (TRABAJO, VACACIONES, etc.) con los tipos
 * custom activos de la empresa (TIPOS_JORNADA_CUSTOM).
 * Acceso: empleado y admin. Usado por tipos_jornada_helper.js globalmente.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success'=>false,'message'=>'No autorizado']); exit;
}

$idEmpresa = (int)$_SESSION['empresa_activa'];

// Tipos genéricos siempre disponibles
$genericos = [
    ['id_tipo_custom' => null, 'valor_enum' => 'TRABAJO',    'label' => 'Trabajo (jornada continua)', 'color' => '#1976d2', 'es_productivo' => 1, 'inicio' => '09:00', 'fin' => '17:00'],
    ['id_tipo_custom' => null, 'valor_enum' => 'PARTIDA_M',  'label' => ' Jornada partida — Mañana', 'color' => '#3f51b5', 'es_productivo' => 1, 'inicio' => '09:00', 'fin' => '14:00'],
    ['id_tipo_custom' => null, 'valor_enum' => 'PARTIDA_T',  'label' => ' Jornada partida — Tarde',  'color' => '#e91e63', 'es_productivo' => 1, 'inicio' => '16:00', 'fin' => '19:00'],
    ['id_tipo_custom' => null, 'valor_enum' => 'VACACIONES', 'label' => ' Vacaciones',              'color' => '#388e3c', 'es_productivo' => 0, 'inicio' => null,    'fin' => null],
    ['id_tipo_custom' => null, 'valor_enum' => 'MEDICO',     'label' => ' Médico',                  'color' => '#f57c00', 'es_productivo' => 0, 'inicio' => null,    'fin' => null],
    ['id_tipo_custom' => null, 'valor_enum' => 'LIBRE',      'label' => ' Día libre',               'color' => '#7b1fa2', 'es_productivo' => 0, 'inicio' => null,    'fin' => null],
    ['id_tipo_custom' => null, 'valor_enum' => 'FESTIVO',    'label' => ' Festivo',                 'color' => '#c62828', 'es_productivo' => 0, 'inicio' => null,    'fin' => null],
];

// Plantillas custom activas de la empresa
$stmt = $pdo->prepare("
    SELECT id_tipo_custom, nombre_display, color_hex, es_productivo,
           hora_inicio_predeterminada, hora_fin_predeterminada
    FROM tipos_jornada_custom
    WHERE id_empresa = ? AND activo = 1
    ORDER BY nombre_display ASC
");
$stmt->execute([$idEmpresa]);
$custom = $stmt->fetchAll(PDO::FETCH_ASSOC);

$customFormateado = array_map(fn($c) => [
    'id_tipo_custom' => (int)$c['id_tipo_custom'],
    'valor_enum'     => 'TRABAJO',   // fallback para columna tipo_jornada
    'label'          => 'Etiqueta ' . $c['nombre_display'],
    'color'          => $c['color_hex'],
    'es_productivo'  => (int)$c['es_productivo'],
    'inicio'         => $c['hora_inicio_predeterminada'] ? substr($c['hora_inicio_predeterminada'],0,5) : null,
    'fin'            => $c['hora_fin_predeterminada']    ? substr($c['hora_fin_predeterminada'],0,5)    : null,
], $custom);

echo json_encode([
    'success'   => true,
    'genericos' => $genericos,
    'custom'    => $customFormateado,
]);