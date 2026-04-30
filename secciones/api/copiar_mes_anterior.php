<?php
/*
 * copiar_mes_anterior.php — Endpoint POST de la API de Horario
 * Copia los horarios temporales del mes anterior al mes actual del empleado.
 * Acceso: empleado y admin. Devuelve JSON {success, message, copiados}.
 */
session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$idUsuario = $_SESSION['usuario']['id'];
$data = json_decode(file_get_contents('php://input'), true);
$mesDestino = (int)$data['mes'];
$anioDestino = (int)$data['anio'];

try {
    // 1. Calcular mes origen
    $fechaDestinoObj = new DateTime("$anioDestino-$mesDestino-01");
    $fechaOrigenObj = clone $fechaDestinoObj;
    $fechaOrigenObj->modify('-1 month');
    
    $mesOrigen = $fechaOrigenObj->format('n');
    $anioOrigen = $fechaOrigenObj->format('Y');

    // 2. Obtener los horarios EXACTOS día a día del mes anterior
    // Usamos DAY(fecha) para saber qué número de día es (1, 2, 3...)
    $sql = "SELECT DAY(fecha) as dia_num, tipo_jornada, hora_inicio, hora_fin, horas_totales, observaciones 
            FROM horarios 
            WHERE id_usuario = :id AND MONTH(fecha) = :m AND YEAR(fecha) = :a
            ORDER BY fecha ASC, hora_inicio ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $idUsuario, 'm' => $mesOrigen, 'a' => $anioOrigen]);
    $registrosMesAnterior = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($registrosMesAnterior)) {
        echo json_encode(['success' => false, 'message' => 'No hay datos en el mes anterior para copiar']);
        exit;
    }

    // Mapeamos los días (ojo: un día puede tener varias entradas, como TRABAJO + MEDICO)
    $mapaDias = [];
    foreach ($registrosMesAnterior as $reg) {
        $mapaDias[$reg['dia_num']][] = $reg;
    }

    // 3. Generar borradores para el mes destino
    $numDiasDestino = cal_days_in_month(CAL_GREGORIAN, $mesDestino, $anioDestino);
    $horariosCopia = [];

    for ($d = 1; $d <= $numDiasDestino; $d++) {
        $fechaNueva = sprintf("%04d-%02d-%02d", $anioDestino, $mesDestino, $d);
        
        // Si el día existía en el mes anterior, lo clonamos
        if (isset($mapaDias[$d])) {
            foreach ($mapaDias[$d] as $indice => $datosDia) {
                // Creamos una clave única: fecha + indice (por si hay varios registros el mismo día)
                $clave = ($indice === 0) ? $fechaNueva : $fechaNueva . "_$indice";
                
                $horariosCopia[$clave] = [
                    'fecha' => $fechaNueva,
                    'tipo_jornada' => $datosDia['tipo_jornada'],
                    'hora_inicio' => $datosDia['hora_inicio'],
                    'hora_fin' => $datosDia['hora_fin'],
                    'horas_totales' => $datosDia['horas_totales'],
                    'observaciones' => $datosDia['observaciones'] ?: 'Copiado del mes anterior',
                    'estado' => 'TEMPORAL'
                ];
            }
        }
    }

    // 4. Guardar en sesión
    if (!isset($_SESSION['horarios_temporales'])) $_SESSION['horarios_temporales'] = [];
    
    // Limpiamos lo anterior para que la copia sea limpia o combinamos:
    $_SESSION['horarios_temporales'] = array_merge($_SESSION['horarios_temporales'], $horariosCopia);

    echo json_encode([
        'success' => true, 
        'message' => 'Copia exacta realizada con éxito',
        'cantidad' => count($horariosCopia)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}