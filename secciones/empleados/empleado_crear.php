<?php
/*
 * empleado_crear.php — Endpoint POST del módulo Empleados
 * Crea un nuevo usuario en USUARIO y lo vincula a la empresa activa en EMPRESA_USUARIO.
 * Acceso: solo admin. Valida email único y hashea la contraseña con password_hash().
 * Devuelve JSON {success, message, id_usuario}.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['nombre']) || !isset($data['apellidos']) || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$empresa = $_SESSION['empresa_activa'];

try {
    $pdo->beginTransaction();

    // Verificar si el email ya existe
    $stmtCheck = $pdo->prepare("SELECT id_usuario FROM USUARIO WHERE email = ?");
    $stmtCheck->execute([$data['email']]);
    if ($stmtCheck->fetch()) {
        throw new Exception('El email ya está registrado en el sistema');
    }

    $passwordHash   = password_hash($data['password'], PASSWORD_DEFAULT);
    $tipoContrato   = $data['tipo_contrato']            ?? 'completa';
    $horasMes       = (int)($data['horas_contrato_mes']          ?? 160);
    $vacAnuales     = (float)($data['vacaciones_totales_anuales'] ?? 30);
    $limiteExtra    = (int)($data['horas_extra_anuales_limite']   ?? 80);

    // Insertar usuario
    $sqlUsuario = "INSERT INTO USUARIO (
        nombre, apellidos, NIF, Numero_Afiliciacion, email, password_hash,
        activo, foto_perfil,
        tipo_contrato, horas_contrato_mes, vacaciones_totales_anuales, horas_extra_anuales_limite
    ) VALUES (?, ?, ?, ?, ?, ?, 1, 'secciones/uploads/perfil_default.jpg', ?, ?, ?, ?)";

    $pdo->prepare($sqlUsuario)->execute([
        $data['nombre'], $data['apellidos'],
        $data['NIF'] ?? null, $data['Numero_Afiliciacion'] ?? null,
        $data['email'], $passwordHash,
        $tipoContrato, $horasMes, $vacAnuales, $limiteExtra,
    ]);

    $nuevoId = $pdo->lastInsertId();

    // Vincular a la empresa
    $pdo->prepare("INSERT INTO EMPRESA_USUARIO (id_empresa, id_usuario, admin, activo) VALUES (?, ?, ?, ?)")
        ->execute([$empresa, $nuevoId, $data['admin'] ?? 0, $data['activo'] ?? 1]);

    // Crear registro de vacaciones acumulado para el año actual
    $anio = date('Y');
    $pdo->prepare("
        INSERT INTO vacaciones_acumulado (id_usuario, año, dias_totales, dias_utilizados, dias_restantes)
        VALUES (?, ?, ?, 0, ?)
        ON DUPLICATE KEY UPDATE id_usuario = id_usuario
    ")->execute([$nuevoId, $anio, $vacAnuales, $vacAnuales]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Empleado creado correctamente', 'id_usuario' => $nuevoId]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Error en empleado_crear.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>