<?php
/*
 * actualizar_contrato.php — Endpoint POST de la API de Empleados
 * Actualiza los datos de contrato de un empleado (tipo contrato, horas semanales, etc.).
 * Acceso: solo admin. Valida que el empleado pertenezca a la empresa activa.
 * Devuelve JSON {success, message}.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data      = json_decode(file_get_contents('php://input'), true);
$idEmpresa = (int)$_SESSION['empresa_activa'];
$esAdmin   = (bool)$_SESSION['es_admin'];

// Admin puede editar el contrato de cualquier empleado de su empresa
$idUsuario = ($esAdmin && isset($data['id_usuario']))
    ? (int)$data['id_usuario']
    : (int)$_SESSION['usuario']['id'];

$tipoContrato = in_array($data['tipo_contrato'] ?? '', ['completa','parcial'])
    ? $data['tipo_contrato'] : 'completa';
$horasMes  = max(1, min(240, (int)($data['horas_contrato_mes'] ?? 160)));
$vacAnuales= max(0, min(60, (float)($data['vacaciones_totales_anuales'] ?? 30)));

try {
    // Verificar que el usuario pertenece a la empresa
    $stmtCheck = $pdo->prepare("SELECT 1 FROM empresa_usuario WHERE id_usuario=? AND id_empresa=?");
    $stmtCheck->execute([$idUsuario, $idEmpresa]);
    if (!$stmtCheck->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado en esta empresa']);
        exit;
    }

    // Actualizar en tabla USUARIO
    $stmt = $pdo->prepare("
        UPDATE usuario
        SET tipo_contrato              = ?,
            horas_contrato_mes         = ?,
            vacaciones_totales_anuales = ?
        WHERE id_usuario = ?
    ");
    $stmt->execute([$tipoContrato, $horasMes, $vacAnuales, $idUsuario]);

    // Sincronizar vacaciones_acumulado del año actual
    $anio = date('Y');
    $pdo->prepare("
        INSERT INTO vacaciones_acumulado (id_usuario, año, dias_totales, dias_utilizados, dias_restantes)
        VALUES (?, ?, ?, 0, ?)
        ON DUPLICATE KEY UPDATE 
            dias_totales  = VALUES(dias_totales),
            dias_restantes = GREATEST(VALUES(dias_totales) - dias_utilizados, 0)
    ")->execute([$idUsuario, $anio, $vacAnuales, $vacAnuales]);

    echo json_encode(['success' => true, 'message' => 'Contrato actualizado correctamente']);

} catch (Exception $e) {
    error_log('Error actualizar_contrato: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>