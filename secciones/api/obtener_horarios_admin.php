<?php
/*
 * obtener_horarios_admin.php — Endpoint GET de la API de Horario
 * Versión admin de obtener_horarios.php. Acepta ?id_usuario=X&mes=M&anio=Y.
 * Permite al admin consultar el calendario de cualquier empleado de la empresa.
 * Acceso: solo admin. Usado por horario_admin_calendario.php.
 */
session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$idEmpresa = (int)$_SESSION['empresa_activa'];

// El admin pasa el id_usuario del empleado a ver
$idUsuario = isset($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : 0;
if (!$idUsuario) {
    echo json_encode(['success' => false, 'message' => 'id_usuario requerido']);
    exit;
}

// Verificar que el empleado pertenece a la empresa del admin
$stmtV = $pdo->prepare(
    "SELECT 1 FROM EMPRESA_USUARIO WHERE id_usuario = ? AND id_empresa = ? AND activo = 1"
);
$stmtV->execute([$idUsuario, $idEmpresa]);
if (!$stmtV->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Empleado no pertenece a esta empresa']);
    exit;
}

$mes  = isset($_GET['mes'])  ? (int)$_GET['mes']  : (int)date('n');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');

$primerDia = "$anio-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-01";
$ultimoDia = date('Y-m-t', strtotime($primerDia));

$calendario = [];

try {
    // ── Horarios aprobados ─────────────────────────────────────
    $stmtH = $pdo->prepare("
        SELECT fecha, orden_dia, tipo_jornada, hora_inicio, hora_fin,
               horas_totales, observaciones, id_horario, 'APROBADO' as estado
        FROM HORARIOS
        WHERE id_usuario = ? AND id_empresa = ?
          AND fecha BETWEEN ? AND ?
        ORDER BY fecha, orden_dia
    ");
    $stmtH->execute([$idUsuario, $idEmpresa, $primerDia, $ultimoDia]);
    while ($row = $stmtH->fetch(PDO::FETCH_ASSOC)) {
        $calendario[$row['fecha']][] = $row;
    }

    // ── Solicitudes pendientes/rechazadas ─────────────────────
    $stmtS = $pdo->prepare("
        SELECT D.fecha, D.orden_dia, D.tipo_jornada, D.hora_inicio,
               D.hora_fin, D.horas_totales,
               COALESCE(D.observaciones, S.observaciones) AS observaciones,
               S.estado, S.motivo_rechazo, S.id_solicitud, NULL AS id_horario
        FROM SOLICITUDES_HORARIO S
        JOIN DETALLE_SOLICITUD_HORARIO D ON S.id_solicitud = D.id_solicitud
        WHERE S.id_usuario = ? AND S.id_empresa = ?
          AND S.estado IN ('PENDIENTE', 'RECHAZADO')
          AND D.fecha BETWEEN ? AND ?
    ");
    $stmtS->execute([$idUsuario, $idEmpresa, $primerDia, $ultimoDia]);
    while ($row = $stmtS->fetch(PDO::FETCH_ASSOC)) {
        $calendario[$row['fecha']][] = $row;
    }

    // Ordenar por orden_dia
    foreach ($calendario as &$eventos) {
        usort($eventos, fn($a, $b) => $a['orden_dia'] <=> $b['orden_dia']);
    }

    echo json_encode([
        'success'    => true,
        'mes'        => $mes,
        'anio'       => $anio,
        'calendario' => $calendario,
    ]);

} catch (Exception $e) {
    error_log('obtener_horarios_admin: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}