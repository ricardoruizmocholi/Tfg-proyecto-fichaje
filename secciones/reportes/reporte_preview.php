<?php
/*
 * reporte_preview.php — Endpoint POST del módulo Reportes
 * Devuelve un JSON con HTML renderizado para previsualizar el reporte en pantalla.
 * Acceso: solo admin. No guarda nada; solo genera el HTML de vista previa.
 * Devuelve JSON {success, html}.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$empresa = $_SESSION['empresa_activa'];

function calcularHorasFichaje($f) {
    if (empty($f['hora_entrada']) || empty($f['hora_salida'])) return 0;
    $entrada = strtotime($f['hora_entrada']);
    $salida  = strtotime($f['hora_salida']);
    $pausa   = 0;
    if (!empty($f['hora_pausa']) && !empty($f['hora_reanudacion'])) {
        $pausa = strtotime($f['hora_reanudacion']) - strtotime($f['hora_pausa']);
    }
    return max(0, $salida - $entrada - $pausa) / 3600;
}

try {
    $stmtEmpleado = $pdo->prepare("
        SELECT U.*, E.nombre as nombre_empresa FROM USUARIO U
        JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
        JOIN EMPRESA E ON EU.id_empresa = E.id_empresa
        WHERE U.id_usuario = ? AND EU.id_empresa = ?
    ");
    $stmtEmpleado->execute([$data['id_usuario'], $empresa]);
    $empleado = $stmtEmpleado->fetch(PDO::FETCH_ASSOC);
    
    if (!$empleado) throw new Exception('Empleado no encontrado');
    
    $primerDia = date('Y-m-01', strtotime($data['anio'] . '-' . $data['mes'] . '-01'));
    $ultimoDia = date('Y-m-t', strtotime($data['anio'] . '-' . $data['mes'] . '-01'));
    
    $stmtFichajes = $pdo->prepare("SELECT * FROM FICHAJE WHERE id_usuario = ? AND fecha BETWEEN ? AND ? ORDER BY fecha ASC, id_fichaje ASC");
    $stmtFichajes->execute([$data['id_usuario'], $primerDia, $ultimoDia]);
    $rawFichajes = $stmtFichajes->fetchAll(PDO::FETCH_ASSOC);
    
    $fichajesPorDia = [];
    $tienePartida = false;
    foreach ($rawFichajes as $f) {
        $fecha = $f['fecha'];
        if (!isset($fichajesPorDia[$fecha])) $fichajesPorDia[$fecha] = [];
        $fichajesPorDia[$fecha][] = $f;
        if (count($fichajesPorDia[$fecha]) > 1) $tienePartida = true;
    }
    
    $nombresMeses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    
    $html = '<div class="preview-reporte">';
    $html .= '<h4> ' . htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellidos']) . '</h4>';
    $html .= '<p><strong>Período:</strong> ' . $nombresMeses[$data['mes']] . ' ' . $data['anio'] . '</p>';
    
    $html .= '<table class="preview-table"><thead><tr><th>Día</th>';
    if ($tienePartida) {
        $html .= '<th>Ent 1</th><th>Sal 1</th><th>Ent 2</th><th>Sal 2</th><th>Horas</th></tr></thead><tbody>';
    } else {
        $html .= '<th>Entrada</th><th>Salida</th><th>Horas</th></tr></thead><tbody>';
    }
    
    $diasEnMes = date('t', strtotime($primerDia));
    $totalHoras = 0;
    
    for ($dia = 1; $dia <= $diasEnMes; $dia++) {
        $fecha = sprintf('%04d-%02d-%02d', $data['anio'], $data['mes'], $dia);
        $tramos = $fichajesPorDia[$fecha] ?? [];
        
        $f1 = $tramos[0] ?? null;
        $f2 = $tramos[1] ?? null;
        
        $ent1 = $f1 && $f1['hora_entrada'] ? substr($f1['hora_entrada'], 0, 5) : '-';
        $sal1 = $f1 && $f1['hora_salida'] ? substr($f1['hora_salida'], 0, 5) : '-';
        $ent2 = $f2 && $f2['hora_entrada'] ? substr($f2['hora_entrada'], 0, 5) : '-';
        $sal2 = $f2 && $f2['hora_salida'] ? substr($f2['hora_salida'], 0, 5) : '-';
        
        $horasDia = ($f1 ? calcularHorasFichaje($f1) : 0) + ($f2 ? calcularHorasFichaje($f2) : 0);
        $totalHoras += $horasDia;
        $strHoras = $horasDia > 0 ? number_format($horasDia, 2) . 'h' : '-';
        
        $html .= "<tr><td>$dia</td>";
        if ($tienePartida) {
            $html .= "<td>$ent1</td><td>$sal1</td><td>$ent2</td><td>$sal2</td><td>$strHoras</td></tr>";
        } else {
            $html .= "<td>$ent1</td><td>$sal1</td><td>$strHoras</td></tr>";
        }
    }
    
    $cols = $tienePartida ? 5 : 3;
    $html .= "<tr class='total-row'><td colspan='$cols'><strong>TOTAL</strong></td><td><strong>" . number_format($totalHoras, 2) . "h</strong></td></tr>";
    $html .= '</tbody></table></div>';
    
    $html .= '<style>
        .preview-reporte { font-family: Arial, sans-serif; }
        .preview-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size:14px; }
        .preview-table th { background: #4e73df; color: white; padding: 8px; text-align: center; }
        .preview-table td { padding: 8px; border-bottom: 1px solid #ddd; text-align: center; }
        .total-row { background: #f0f0f0; font-weight: bold; }
    </style>';
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>