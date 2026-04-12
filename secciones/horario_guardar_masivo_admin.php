<?php
// secciones/horario_guardar_masivo_admin.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['empleados'], $data['fecha_inicio'], $data['fecha_fin'], $data['dias'], $data['tipo_jornada'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']); exit;
}

$empresa      = $_SESSION['empresa_activa'];
$empleados    = $data['empleados'];
$fechaInicio  = $data['fecha_inicio'];
$fechaFin     = $data['fecha_fin'];
$dias         = $data['dias'];
$tipoJornada  = $data['tipo_jornada'];
$idTipoCustom = isset($data['id_tipo_custom']) && $data['id_tipo_custom'] ? (int)$data['id_tipo_custom'] : null;
$horaInicio   = $data['hora_inicio']  ?? null;
$horaFin      = $data['hora_fin']     ?? null;
$observaciones= $data['observaciones'] ?? null;

// Si hay custom, verificar que pertenece a esta empresa
if ($idTipoCustom) {
    $stmtVer = $pdo->prepare("SELECT es_productivo FROM tipos_jornada_custom WHERE id_tipo_custom=? AND id_empresa=? AND activo=1");
    $stmtVer->execute([$idTipoCustom, $empresa]);
    $custom = $stmtVer->fetch();
    if (!$custom) {
        echo json_encode(['success' => false, 'message' => 'Plantilla no válida para esta empresa']); exit;
    }
}

$TIPOS_CON_HORAS = ['TRABAJO', 'MEDICO', 'PARTIDA_M', 'PARTIDA_T'];

$horasTotales = null;
if ($horaInicio && $horaFin) {
    $seg = strtotime($horaFin) - strtotime($horaInicio);
    if ($seg < 0) { echo json_encode(['success'=>false,'message'=>'Hora fin anterior a inicio']); exit; }
    $horasTotales = round($seg/3600, 2);
}

// Expandir "todos"
if (in_array('todos', $empleados)) {
    $s = $pdo->prepare("SELECT id_usuario FROM EMPRESA_USUARIO WHERE id_empresa=? AND activo=1");
    $s->execute([$empresa]);
    $empleados = $s->fetchAll(PDO::FETCH_COLUMN);
}

try {
    $pdo->beginTransaction();
    $insertados = 0;

    $fa = new DateTime($fechaInicio);
    $fb = new DateTime($fechaFin);

    while ($fa <= $fb) {
        $dow   = (int)$fa->format('N');
        $fecha = $fa->format('Y-m-d');

        if (in_array($dow, $dias)) {
            foreach ($empleados as $idU) {
                // Verificar pertenencia
                $sv = $pdo->prepare("SELECT 1 FROM EMPRESA_USUARIO WHERE id_usuario=? AND id_empresa=? AND activo=1");
                $sv->execute([$idU, $empresa]);
                if (!$sv->fetch()) continue;

                // Siguiente orden_dia
                $so = $pdo->prepare("SELECT COALESCE(MAX(orden_dia),0)+1 FROM HORARIOS WHERE id_usuario=? AND id_empresa=? AND fecha=?");
                $so->execute([$idU, $empresa, $fecha]);
                $orden = (int)$so->fetchColumn();

                $conHoras = in_array($tipoJornada, $TIPOS_CON_HORAS) || $idTipoCustom;

                $pdo->prepare("
                    INSERT INTO HORARIOS
                        (id_usuario, id_empresa, fecha, orden_dia, tipo_jornada, id_tipo_custom,
                         hora_inicio, hora_fin, horas_totales, observaciones)
                    VALUES (?,?,?,?,?,?,?,?,?,?)
                ")->execute([
                    $idU, $empresa, $fecha, $orden,
                    $tipoJornada, $idTipoCustom,
                    $conHoras ? $horaInicio   : null,
                    $conHoras ? $horaFin      : null,
                    $conHoras ? $horasTotales : null,
                    $observaciones
                ]);
                $insertados++;
            }
        }
        $fa->modify('+1 day');
    }

    $pdo->commit();
    echo json_encode(['success'=>true, 'message'=>'Horarios guardados', 'insertados'=>$insertados]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Error horario_guardar_masivo_admin: '.$e->getMessage());
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>