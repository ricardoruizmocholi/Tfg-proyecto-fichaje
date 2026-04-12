<?php
// secciones/horario_guardar.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
}

$data    = json_decode(file_get_contents('php://input'), true);
$empresa = $_SESSION['empresa_activa'];

if (!isset($data['id_usuario']) || !isset($data['fecha']) || !isset($data['tipo_jornada'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']); exit;
}

// id_tipo_custom: viene del frontend cuando se selecciona una plantilla, NULL para tipos genéricos
$idTipoCustom = isset($data['id_tipo_custom']) && $data['id_tipo_custom'] ? (int)$data['id_tipo_custom'] : null;

// Si viene id_tipo_custom, verificar que pertenece a esta empresa
if ($idTipoCustom) {
    $stmtVer = $pdo->prepare("SELECT hora_inicio_predeterminada, hora_fin_predeterminada
                               FROM tipos_jornada_custom
                               WHERE id_tipo_custom = ? AND id_empresa = ? AND activo = 1");
    $stmtVer->execute([$idTipoCustom, $empresa]);
    $custom = $stmtVer->fetch(PDO::FETCH_ASSOC);
    if (!$custom) {
        echo json_encode(['success' => false, 'message' => 'Plantilla no encontrada']); exit;
    }
}

try {
    // Verificar que el usuario pertenece a la empresa
    $stmtVerif = $pdo->prepare("SELECT 1 FROM EMPRESA_USUARIO WHERE id_usuario=? AND id_empresa=? AND activo=1");
    $stmtVerif->execute([$data['id_usuario'], $empresa]);
    if (!$stmtVerif->fetch()) throw new Exception('Usuario no pertenece a la empresa');

    $TIPOS_CON_HORAS = ['TRABAJO','PARTIDA_M','PARTIDA_T','MEDICO'];
    $tieneHoras = in_array($data['tipo_jornada'], $TIPOS_CON_HORAS) || $idTipoCustom;

    $horasTotales = null;
    if ($tieneHoras && !empty($data['hora_inicio']) && !empty($data['hora_fin'])) {
        $seg = strtotime($data['hora_fin']) - strtotime($data['hora_inicio']);
        $horasTotales = $seg > 0 ? round($seg / 3600, 2) : null;
    }

    if (!empty($data['id_horario'])) {
        // ── UPDATE ────────────────────────────────────────────
        $pdo->prepare("
            UPDATE HORARIOS SET
                tipo_jornada   = ?,
                id_tipo_custom = ?,
                hora_inicio    = ?,
                hora_fin       = ?,
                horas_totales  = ?,
                observaciones  = ?,
                actualizado_en = NOW()
            WHERE id_horario = ? AND id_empresa = ?
        ")->execute([
            $data['tipo_jornada'], $idTipoCustom,
            $tieneHoras ? ($data['hora_inicio'] ?? null) : null,
            $tieneHoras ? ($data['hora_fin']    ?? null) : null,
            $horasTotales, $data['observaciones'] ?? null,
            $data['id_horario'], $empresa
        ]);
    } else {
        // ── INSERT ────────────────────────────────────────────
        $stmtOrden = $pdo->prepare("
            SELECT COALESCE(MAX(orden_dia), 0) + 1
            FROM HORARIOS WHERE id_usuario=? AND id_empresa=? AND fecha=?
        ");
        $stmtOrden->execute([$data['id_usuario'], $empresa, $data['fecha']]);
        $orden = (int)$stmtOrden->fetchColumn();

        $pdo->prepare("
            INSERT INTO HORARIOS
                (id_usuario, id_empresa, fecha, orden_dia, tipo_jornada, id_tipo_custom,
                 hora_inicio, hora_fin, horas_totales, observaciones)
            VALUES (?,?,?,?,?,?,?,?,?,?)
        ")->execute([
            $data['id_usuario'], $empresa, $data['fecha'], $orden,
            $data['tipo_jornada'], $idTipoCustom,
            $tieneHoras ? ($data['hora_inicio'] ?? null) : null,
            $tieneHoras ? ($data['hora_fin']    ?? null) : null,
            $horasTotales, $data['observaciones'] ?? null
        ]);
    }

    // Notificación al empleado
    $msg = "El administrador ha actualizado tu horario para el día " . date('d/m/Y', strtotime($data['fecha']));
    $pdo->prepare("INSERT INTO NOTIFICACIONES (id_usuario, mensaje, tipo) VALUES (?,?,'cambio_horario')")
        ->execute([$data['id_usuario'], $msg]);

    echo json_encode(['success' => true, 'message' => 'Horario guardado correctamente']);

} catch (Exception $e) {
    error_log('Error horario_guardar: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>