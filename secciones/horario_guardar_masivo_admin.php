<?php
// secciones/horario_guardar_masivo_admin.php
// Versión del ADMIN: inserta directamente en BD para los empleados seleccionados

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['empleados'], $data['fecha_inicio'], $data['fecha_fin'], $data['dias'], $data['tipo_jornada'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$empresa     = $_SESSION['empresa_activa'];
$empleados   = $data['empleados'];
$fechaInicio = $data['fecha_inicio'];
$fechaFin    = $data['fecha_fin'];
$dias        = $data['dias'];
$tipoJornada = $data['tipo_jornada'];
$horaInicio  = $data['hora_inicio']  ?? null;
$horaFin     = $data['hora_fin']     ?? null;
$observaciones = $data['observaciones'] ?? null;

// Tipos que necesitan horas
$TIPOS_CON_HORAS = ['TRABAJO', 'MEDICO', 'PARTIDA_M', 'PARTIDA_T'];

// Calcular horas totales
$horasTotales = null;
if ($horaInicio && $horaFin) {
    $seg = strtotime($horaFin) - strtotime($horaInicio);
    if ($seg < 0) {
        echo json_encode(['success' => false, 'message' => 'La hora fin debe ser posterior a la hora inicio']);
        exit;
    }
    $horasTotales = round($seg / 3600, 2);
}

// Si se selecciona TODOS, expandir
if (in_array('todos', $empleados)) {
    $stmtTodos = $pdo->prepare("SELECT id_usuario FROM EMPRESA_USUARIO WHERE id_empresa = ? AND activo = 1");
    $stmtTodos->execute([$empresa]);
    $empleados = $stmtTodos->fetchAll(PDO::FETCH_COLUMN);
}

try {
    $pdo->beginTransaction();
    $insertados = 0;

    $fechaActual = new DateTime($fechaInicio);
    $fechaFinal  = new DateTime($fechaFin);

    while ($fechaActual <= $fechaFinal) {
        $diaSemana = (int)$fechaActual->format('N'); // 1=Lun, 7=Dom
        
        if (in_array($diaSemana, $dias)) {
            $fecha = $fechaActual->format('Y-m-d');
            
            foreach ($empleados as $idUsuario) {
                // Verificar que el usuario pertenece a la empresa
                $stmtVerif = $pdo->prepare("SELECT 1 FROM EMPRESA_USUARIO WHERE id_usuario = ? AND id_empresa = ? AND activo = 1");
                $stmtVerif->execute([$idUsuario, $empresa]);
                if (!$stmtVerif->fetch()) continue;

                // Calcular el siguiente orden_dia para ese usuario+empresa+fecha
                $stmtOrden = $pdo->prepare("SELECT COALESCE(MAX(orden_dia), 0) + 1 FROM HORARIOS WHERE id_usuario = ? AND id_empresa = ? AND fecha = ?");
                $stmtOrden->execute([$idUsuario, $empresa, $fecha]);
                $ordenDia = (int)$stmtOrden->fetchColumn();

                $sql = "INSERT INTO HORARIOS (
                            id_usuario, id_empresa, fecha, orden_dia,
                            tipo_jornada, hora_inicio, hora_fin, horas_totales, observaciones
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $pdo->prepare($sql)->execute([
                    $idUsuario, $empresa, $fecha, $ordenDia,
                    $tipoJornada,
                    in_array($tipoJornada, $TIPOS_CON_HORAS) ? $horaInicio  : null,
                    in_array($tipoJornada, $TIPOS_CON_HORAS) ? $horaFin     : null,
                    in_array($tipoJornada, $TIPOS_CON_HORAS) ? $horasTotales : null,
                    $observaciones
                ]);
                $insertados++;
            }
        }
        $fechaActual->modify('+1 day');
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Horarios guardados', 'insertados' => $insertados]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Error horario_guardar_masivo_admin: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>