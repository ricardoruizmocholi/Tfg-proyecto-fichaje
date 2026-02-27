<?php
session_start();
header('Content-Type: application/json');

require_once '../../config.php';

// Log de debug
error_log("=== GUARDAR HORARIO DIA ===");

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

if (!isset($data['fecha']) || !isset($data['tipo_jornada'])) {
    error_log("ERROR: Datos incompletos");
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$fecha = $data['fecha'];
$tipoJornada = $data['tipo_jornada'];
$horaInicio = $data['hora_inicio'] ?? null;
$horaFin = $data['hora_fin'] ?? null;
$observaciones = $data['observaciones'] ?? null;

// Validar que la fecha no sea anterior a hoy (permitir mes actual y futuros)
$fechaActual = date('Y-m-d');

if ($fecha < $fechaActual) {
    error_log("ERROR: Fecha rechazada - No se permite crear horarios en el pasado");
    echo json_encode([
        'success' => false, 
        'message' => 'No puedes crear horarios para fechas pasadas'
    ]);
    exit;
}

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

    // Contar eventos existentes para esta fecha para calcular orden_dia
    $eventosExistentes = array_filter($_SESSION['horarios_temporales'], function($h) use ($fecha) {
        return isset($h['fecha']) && $h['fecha'] === $fecha;
    });

    $ordenDia = count($eventosExistentes) + 1;
    
    error_log("Eventos existentes en $fecha: " . count($eventosExistentes));
    error_log("Nuevo orden_dia: $ordenDia");

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

    error_log("Guardado con clave: $claveEvento");
    error_log("Total eventos en sesión: " . count($_SESSION['horarios_temporales']));
    error_log("Contenido sesión: " . json_encode($_SESSION['horarios_temporales']));

    // Obtener todos los eventos del día para devolverlos
    $eventosDelDia = array_filter($_SESSION['horarios_temporales'], function($h) use ($fecha) {
        return isset($h['fecha']) && $h['fecha'] === $fecha;
    });

    $response = [
        'success' => true,
        'message' => 'Horario guardado temporalmente',
        'data' => $_SESSION['horarios_temporales'][$claveEvento],
        'eventos_del_dia' => array_values($eventosDelDia),
        'total_temporales' => count($_SESSION['horarios_temporales']),
        'clave_evento' => $claveEvento
    ];

    error_log("Respuesta exitosa: " . json_encode($response));

    echo json_encode($response);

} catch (Exception $e) {
    error_log("EXCEPCIÓN en guardar_horario_dia: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar horario: ' . $e->getMessage()
    ]);
}
?>