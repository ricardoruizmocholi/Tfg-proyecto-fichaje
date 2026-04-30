<?php
/*
 * empleado_actualizar.php — Endpoint POST del módulo Empleados
 * Actualiza nombre, apellidos, email y NIF de un empleado existente.
 * Acceso: solo admin. Valida que el empleado pertenezca a la empresa activa.
 * Devuelve JSON {success, message}.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
    exit;
}

$empresa = $_SESSION['empresa_activa'];

try {
    $pdo->beginTransaction();

    // Verificar que el usuario pertenece a la empresa
    $stmtVerif = $pdo->prepare("SELECT 1 FROM EMPRESA_USUARIO WHERE id_usuario = ? AND id_empresa = ?");
    $stmtVerif->execute([$data['id_usuario'], $empresa]);
    if (!$stmtVerif->fetch()) {
        throw new Exception('Usuario no pertenece a esta empresa');
    }

    // Verificar si el email ya existe en otro usuario
    if (isset($data['email'])) {
        $stmtCheckEmail = $pdo->prepare("SELECT id_usuario FROM USUARIO WHERE email = ? AND id_usuario != ?");
        $stmtCheckEmail->execute([$data['email'], $data['id_usuario']]);
        if ($stmtCheckEmail->fetch()) {
            throw new Exception('El email ya está siendo usado por otro usuario');
        }
    }

    // ── Actualizar datos personales + contrato en USUARIO ──
    $sqlUsuario = "UPDATE USUARIO SET 
        nombre                      = ?,
        apellidos                   = ?,
        NIF                         = ?,
        Numero_Afiliciacion         = ?,
        email                       = ?,
        tipo_contrato               = ?,
        horas_contrato_mes          = ?,
        vacaciones_totales_anuales  = ?,
        horas_extra_anuales_limite  = ?";

    $paramsUsuario = [
        $data['nombre']                    ?? '',
        $data['apellidos']                 ?? '',
        $data['NIF']                       ?? null,
        $data['Numero_Afiliciacion']       ?? null,
        $data['email']                     ?? '',
        $data['tipo_contrato']             ?? 'completa',
        (int)($data['horas_contrato_mes']           ?? 160),
        (float)($data['vacaciones_totales_anuales'] ?? 30),
        (int)($data['horas_extra_anuales_limite']   ?? 80),
    ];

    // Contraseña opcional
    if (!empty($data['password'])) {
        $sqlUsuario .= ", password_hash = ?";
        $paramsUsuario[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    $sqlUsuario .= " WHERE id_usuario = ?";
    $paramsUsuario[] = $data['id_usuario'];

    $pdo->prepare($sqlUsuario)->execute($paramsUsuario);

    // ── Actualizar rol y estado en EMPRESA_USUARIO ──
    $pdo->prepare("UPDATE EMPRESA_USUARIO SET admin = ?, activo = ? WHERE id_usuario = ? AND id_empresa = ?")
        ->execute([
            (int)($data['admin']  ?? 0),
            (int)($data['activo'] ?? 1),
            $data['id_usuario'],
            $empresa
        ]);

    // ── Sincronizar vacaciones_acumulado con los nuevos días totales ──
    $anio = date('Y');
    $vacNuevas = (float)($data['vacaciones_totales_anuales'] ?? 30);

    $pdo->prepare("
        INSERT INTO vacaciones_acumulado (id_usuario, año, dias_totales, dias_utilizados, dias_restantes)
        VALUES (?, ?, ?, 0, ?)
        ON DUPLICATE KEY UPDATE
            dias_totales   = VALUES(dias_totales),
            dias_restantes = GREATEST(VALUES(dias_totales) - dias_utilizados, 0)
    ")->execute([$data['id_usuario'], $anio, $vacNuevas, $vacNuevas]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Empleado actualizado correctamente']);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Error empleado_actualizar.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>