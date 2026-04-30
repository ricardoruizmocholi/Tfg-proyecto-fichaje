<?php
/*
 * empleado_obtener.php — Endpoint GET del módulo Empleados
 * Devuelve JSON con los datos de un empleado por ?id=X.
 * Acceso: solo admin. Usado por el modal de edición en empleados_lista.php.
 * Valida que el empleado pertenezca a la empresa activa del admin.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$id_usuario = $_GET['id'] ?? null;
$empresa    = $_SESSION['empresa_activa'];

if (!$id_usuario) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            U.id_usuario,
            U.nombre,
            U.apellidos,
            U.NIF,
            U.Numero_Afiliciacion,
            U.email,
            U.activo as usuario_activo,
            U.tipo_contrato,
            U.horas_contrato_mes,
            U.vacaciones_totales_anuales,
            U.horas_extra_anuales_limite,
            EU.admin,
            EU.activo
        FROM USUARIO U
        JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
        WHERE U.id_usuario = ? AND EU.id_empresa = ?
    ");
    $stmt->execute([$id_usuario, $empresa]);
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$empleado) throw new Exception('Empleado no encontrado');

    echo json_encode(['success' => true, 'empleado' => $empleado]);

} catch (Exception $e) {
    error_log('Error en empleado_obtener.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>