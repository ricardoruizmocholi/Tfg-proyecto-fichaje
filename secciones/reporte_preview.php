<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_usuario']) || !isset($data['mes']) || !isset($data['anio'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$empresa = $_SESSION['empresa_activa'];

try {
    // Obtener datos del empleado
    $stmtEmpleado = $pdo->prepare("
        SELECT U.*, E.nombre as nombre_empresa
        FROM USUARIO U
        JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
        JOIN EMPRESA E ON EU.id_empresa = E.id_empresa
        WHERE U.id_usuario = ? AND EU.id_empresa = ?
    ");
    $stmtEmpleado->execute([$data['id_usuario'], $empresa]);
    $empleado = $stmtEmpleado->fetch(PDO::FETCH_ASSOC);
    
    if (!$empleado) {
        throw new Exception('Empleado no encontrado');
    }
    
    // Calcular primer y último día del mes
    $primerDia = date('Y-m-01', strtotime($data['anio'] . '-' . $data['mes'] . '-01'));
    $ultimoDia = date('Y-m-t', strtotime($data['anio'] . '-' . $data['mes'] . '-01'));
    
    // Obtener fichajes del mes
    $stmtFichajes = $pdo->prepare("
        SELECT * FROM FICHAJE 
        WHERE id_usuario = ? AND fecha BETWEEN ? AND ?
        ORDER BY fecha
    ");
    $stmtFichajes->execute([$data['id_usuario'], $primerDia, $ultimoDia]);
    $fichajes = $stmtFichajes->fetchAll(PDO::FETCH_ASSOC);
    
    // Organizar fichajes por día
    $fichajesPorDia = [];
    foreach ($fichajes as $f) {
        $fichajesPorDia[$f['fecha']] = $f;
    }
    
    // Generar HTML preview
    $html = '<div class="preview-reporte">';
    $html .= '<h4> ' . htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellidos']) . '</h4>';
    $html .= '<p><strong>Empresa:</strong> ' . htmlspecialchars($empleado['nombre_empresa']) . '</p>';
    
    $nombresMeses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                     'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $html .= '<p><strong>Período:</strong> ' . $nombresMeses[$data['mes']] . ' ' . $data['anio'] . '</p>';
    
    $html .= '<table class="preview-table">';
    $html .= '<thead><tr>';
    $html .= '<th>Día</th><th>Entrada</th><th>Salida</th><th>Horas</th>';
    $html .= '</tr></thead><tbody>';
    
    $diasEnMes = date('t', strtotime($primerDia));
    $totalHoras = 0;
    
    for ($dia = 1; $dia <= $diasEnMes; $dia++) {
        $fecha = date('Y-m-d', strtotime($data['anio'] . '-' . $data['mes'] . '-' . str_pad($dia, 2, '0', STR_PAD_LEFT)));
        $fichaje = $fichajesPorDia[$fecha] ?? null;
        
        $horasDia = 0;
        if ($fichaje && $fichaje['hora_entrada'] && $fichaje['hora_salida']) {
            $entrada = strtotime($fichaje['hora_entrada']);
            $salida = strtotime($fichaje['hora_salida']);
            $pausa = 0;
            if ($fichaje['hora_pausa'] && $fichaje['hora_reanudacion']) {
                $pausa = strtotime($fichaje['hora_reanudacion']) - strtotime($fichaje['hora_pausa']);
            }
            $horasDia = round(($salida - $entrada - $pausa) / 3600, 2);
            $totalHoras += $horasDia;
        }
        
        $html .= '<tr>';
        $html .= '<td>' . $dia . '</td>';
        $html .= '<td>' . ($fichaje ? substr($fichaje['hora_entrada'], 0, 5) : '-') . '</td>';
        $html .= '<td>' . ($fichaje ? substr($fichaje['hora_salida'], 0, 5) : '-') . '</td>';
        $html .= '<td>' . ($horasDia > 0 ? number_format($horasDia, 2) . 'h' : '-') . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '<tr class="total-row">';
    $html .= '<td colspan="3"><strong>TOTAL</strong></td>';
    $html .= '<td><strong>' . number_format($totalHoras, 2) . 'h</strong></td>';
    $html .= '</tr>';
    
    $html .= '</tbody></table>';
    $html .= '<p style="margin-top:15px;color:#666;font-size:13px;">Total de días con fichaje: ' . count($fichajes) . '</p>';
    $html .= '</div>';
    
    $html .= '<style>
        .preview-reporte { font-family: Arial, sans-serif; }
        .preview-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .preview-table th { background: #667eea; color: white; padding: 8px; text-align: left; }
        .preview-table td { padding: 8px; border-bottom: 1px solid #ddd; }
        .preview-table tr:hover { background: #f8f9fa; }
        .total-row { background: #f0f0f0; font-weight: bold; }
    </style>';
    
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
    
} catch (Exception $e) {
    error_log('Error en reporte_preview.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>