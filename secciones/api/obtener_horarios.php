<?php
/*
 * obtener_horarios.php — Endpoint GET de la API de Horario
 * Devuelve JSON con los horarios del empleado en sesión para un mes/año (?mes=X&anio=Y).
 * Incluye tanto los registros aprobados (HORARIOS) como los temporales pendientes.
 * Acceso: empleado y admin. Usado por horario_calendario.js.
 */
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
    $sqlHorarios = "SELECT fecha, orden_dia, tipo_jornada, hora_inicio, hora_fin, 
                           horas_totales, observaciones, 'APROBADO' as estado 
                    FROM HORARIOS 
                    WHERE id_usuario = :id_u AND id_empresa = :id_e 
                    AND fecha BETWEEN :p1 AND :u1";
    
    $stmt = $pdo->prepare($sqlHorarios);
    $stmt->execute(['id_u' => $idUsuario, 'id_e' => $idEmpresa, 'p1' => $primerDia, 'u1' => $ultimoDia]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['editable'] = false;
        $calendario[$row['fecha']][] = $row;
    }

    // ============================================
    // 3. OBTENER SOLICITUDES (PENDIENTES/RECHAZADAS)
    // ============================================
    $sqlSolicitudes = "SELECT D.fecha, D.orden_dia, D.tipo_jornada, D.hora_inicio, 
                              D.hora_fin, D.horas_totales, 
                              COALESCE(D.observaciones, S.observaciones) as observaciones,
                              S.estado, S.motivo_rechazo, S.id_solicitud
                       FROM SOLICITUDES_HORARIO S
                       JOIN DETALLE_SOLICITUD_HORARIO D ON S.id_solicitud = D.id_solicitud
                       WHERE S.id_usuario = :id_u AND S.id_empresa = :id_e
                       AND S.estado IN ('PENDIENTE', 'RECHAZADO')
                       AND D.fecha BETWEEN :p1 AND :u1";
    
    $stmt = $pdo->prepare($sqlSolicitudes);
    $stmt->execute(['id_u' => $idUsuario, 'id_e' => $idEmpresa, 'p1' => $primerDia, 'u1' => $ultimoDia]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['editable'] = ($row['estado'] === 'RECHAZADO');
        $calendario[$row['fecha']][] = $row;
    }

    // ============================================
    // 4. OBTENER TEMPORALES (SESIÓN)
    // ============================================
    $totalTemporalesGlobal = 0;
    if (isset($_SESSION['horarios_temporales']) && is_array($_SESSION['horarios_temporales'])) {
        $totalTemporalesGlobal = count($_SESSION['horarios_temporales']);
        
        foreach ($_SESSION['horarios_temporales'] as $temp) {
            // Solo procesar si está en el rango del mes visible
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
    error_log("Error en obtener_horarios.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor al cargar datos'
    ]);
}