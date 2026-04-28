<?php
// obtener_horarios.php
session_start();
header('Content-Type: application/json');

require_once '../../config.php';

// Configuración de respuesta inicial
$response = [
    'success' => false, 
    'message' => 'Error desconocido',
    'calendario' => []
];

if (!isset($_SESSION['usuario'])) {
    $response['message'] = 'No autorizado';
    echo json_encode($response);
    exit;
}

$idUsuario = $_SESSION['usuario']['id'];
$idEmpresa = $_SESSION['empresa_activa'];

// Sanitización de entrada
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('n');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');

try {
    // 1. Preparación de fechas
    $primerDia = "$anio-" . str_pad($mes, 2, "0", STR_PAD_LEFT) . "-01";
    $ultimoDia = date('Y-m-t', strtotime($primerDia));

    // Estructura principal donde agruparemos todo por fecha
    $calendario = [];

    // ============================================
    // 2. OBTENER HORARIOS APROBADOS (BD)
    // ============================================
    $sqlHorarios = "SELECT fecha, orden_dia, tipo_jornada, hora_inicio, hora_fin, horas_totales, estado, observaciones 
                    FROM HORARIOS 
                    WHERE id_usuario = ? AND id_empresa = ? AND fecha BETWEEN ? AND ?
                    ORDER BY fecha ASC, orden_dia ASC";
    
    $stmt = $pdo->prepare($sqlHorarios);
    $stmt->execute([$idUsuario, $idEmpresa, $primerDia, $ultimoDia]);
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($horarios as $h) {
        $calendario[$h['fecha']][] = [
            'fecha'         => $h['fecha'],
            'orden_dia'     => (int)$h['orden_dia'],
            'tipo_jornada'  => $h['tipo_jornada'],
            'hora_inicio'   => $h['hora_inicio'],
            'hora_fin'      => $h['hora_fin'],
            'horas_totales' => $h['horas_totales'],
            'observaciones' => $h['observaciones'] ?? '',
            'estado'        => $h['estado'],
            'editable'      => false
        ];
    }

    // 2. OBTENER FESTIVOS DE LA EMPRESA (De la tabla festivos_empresa)
    $sqlFestivos = "SELECT fecha, nombre_festivo 
                    FROM festivos_empresa 
                    WHERE id_empresa = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ?";
    
    $stmtF = $pdo->prepare($sqlFestivos);
    $stmtF->execute([$idEmpresa, $mes, $anio]);
    $festivos = $stmtF->fetchAll(PDO::FETCH_ASSOC);

    foreach ($festivos as $f) {
        // Añadimos el festivo al array del día correspondiente
        $calendario[$f['fecha']][] = [
            'tipo_jornada'  => $f['nombre_festivo'],
            'estado'        => 'FESTIVO_GLOBAL', // Etiqueta clave para el CSS
            'hora_inicio'   => null,
            'hora_fin'      => null,
            'horas_totales' => null,
            'editable'      => false
        ];
    }

    // ============================================
    // 4. OBTENER HORARIOS TEMPORALES (SESIÓN)
    // ============================================
    $totalTemporalesGlobal = 0;
    if (isset($_SESSION['horarios_temporales']) && !empty($_SESSION['horarios_temporales'])) {
        foreach ($_SESSION['horarios_temporales'] as $temp) {
            $totalTemporalesGlobal++;
            if ($temp['fecha'] >= $primerDia && $temp['fecha'] <= $ultimoDia) {
                $calendario[$temp['fecha']][] = [
                    'fecha'         => $temp['fecha'],
                    'orden_dia'     => (int)($temp['orden_dia'] ?? 1),
                    'tipo_jornada'  => $temp['tipo_jornada'],
                    'hora_inicio'   => $temp['hora_inicio'],
                    'hora_fin'      => $temp['hora_fin'],
                    'horas_totales' => $temp['horas_totales'],
                    'observaciones' => $temp['observaciones'] ?? '',
                    'estado'        => 'TEMPORAL',
                    'editable'      => true
                ];
            }
        }
    }

    // ============================================
    // 5. NORMALIZACIÓN Y ORDENACIÓN
    // ============================================
    foreach ($calendario as $fecha => &$eventos) {
        usort($eventos, function($a, $b) {
            return $a['orden_dia'] <=> $b['orden_dia'];
        });
    }

    // Respuesta final exitosa
    echo json_encode([
        'success' => true,
        'mes' => $mes,
        'anio' => $anio,
        'calendario' => $calendario,
        'total_temporales' => $totalTemporalesGlobal
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener horarios: ' . $e->getMessage()
    ]);
}