<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';


error_log("=== GUARDAR HORARIO MASIVO EMPLEADO ===");

if (!isset($_SESSION['usuario'])) {
    error_log("ERROR: Usuario no autenticado");
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$idUsuario = $_SESSION['usuario']['id'];
$idEmpresa = $_SESSION['empresa_activa'];

error_log("Usuario: $idUsuario, Empresa: $idEmpresa");

// Obtener datos del POST
$data = json_decode(file_get_contents('php://input'), true);

error_log("Datos recibidos: " . json_encode($data));

if (!isset($data['fecha_inicio']) || !isset($data['fecha_fin']) || !isset($data['dias']) || !isset($data['tipo_jornada'])) {
    error_log("ERROR: Datos incompletos");
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$fechaInicio = $data['fecha_inicio'];
$fechaFin = $data['fecha_fin'];
$dias = $data['dias']; // Array de días de la semana [1=Lun, 2=Mar, ..., 7=Dom]
$tipoJornada = $data['tipo_jornada'];
$horaInicio = $data['hora_inicio'] ?? null;
$horaFin = $data['hora_fin'] ?? null;
$observaciones = $data['observaciones'] ?? null;

// Calcular horas totales
$horasTotales = null;
if ($horaInicio && $horaFin) {
    $inicio = strtotime($horaInicio);
    $fin = strtotime($horaFin);
    $horasTotales = ($fin - $inicio) / 3600;
    
    if ($horasTotales < 0) {
        error_log("ERROR: Horas negativas");
        echo json_encode([
            'success' => false,
            'message' => 'La hora de fin debe ser posterior a la hora de inicio'
        ]);
        exit;
    }
}

try {
    // Inicializar array de temporales si no existe
    if (!isset($_SESSION['horarios_temporales'])) {
        $_SESSION['horarios_temporales'] = [];
        error_log("Inicializando array horarios_temporales");
    }

    // Generar fechas en el rango
    $fechaActual = new DateTime($fechaInicio);
    $fechaFinal = new DateTime($fechaFin);
    $diasGuardados = 0;

    while ($fechaActual <= $fechaFinal) {
        $diaSemana = (int)$fechaActual->format('N'); // 1=Lun, 7=Dom
        
        // Verificar si este día de la semana está seleccionado
        if (in_array($diaSemana, $dias)) {
            $fecha = $fechaActual->format('Y-m-d');
            
            // Contar eventos existentes para esta fecha para calcular orden_dia
            $eventosExistentes = array_filter($_SESSION['horarios_temporales'], function($h) use ($fecha) {
                return isset($h['fecha']) && $h['fecha'] === $fecha;
            });

            $ordenDia = count($eventosExistentes) + 1;
            
            // Crear clave única: fecha_ordendia
            $claveEvento = $fecha . '_' . $ordenDia;
            
            // Guardar en sesión
            $_SESSION['horarios_temporales'][$claveEvento] = [
                'fecha' => $fecha,
                'orden_dia' => $ordenDia,
                'tipo_jornada' => $tipoJornada,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'horas_totales' => $horasTotales,
                'observaciones' => $observaciones,
                'estado' => 'TEMPORAL'
            ];
            
            $diasGuardados++;
            error_log("Guardado: $claveEvento");
        }
        
        $fechaActual->modify('+1 day');
    }

    error_log("Total guardados: $diasGuardados");
    error_log("Total en sesión: " . count($_SESSION['horarios_temporales']));

    $response = [
        'success' => true,
        'message' => 'Horarios guardados temporalmente',
        'dias_guardados' => $diasGuardados,
        'total_temporales' => count($_SESSION['horarios_temporales'])
    ];

    error_log("Respuesta exitosa: " . json_encode($response));

    echo json_encode($response);

} catch (Exception $e) {
    error_log("EXCEPCIÓN en guardar_horario_masivo: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar horarios: ' . $e->getMessage()
    ]);
}
?>