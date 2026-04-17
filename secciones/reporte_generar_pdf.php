<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Desactivar visualización de errores HTML para que no rompa el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_usuario']) || !isset($data['mes']) || !isset($data['anio'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$empresa = $_SESSION['empresa_activa'];
$idUsuarioSolicitante = $_SESSION['usuario']['id'];

// Función helper para calcular horas igual que en fichaje_ver.php
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
        SELECT U.*, E.nombre as nombre_empresa, E.CIF, E.CCC
        FROM USUARIO U
        JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
        JOIN EMPRESA E ON EU.id_empresa = E.id_empresa
        WHERE U.id_usuario = ? AND EU.id_empresa = ?
    ");
    $stmtEmpleado->execute([$data['id_usuario'], $empresa]);
    $empleado = $stmtEmpleado->fetch(PDO::FETCH_ASSOC);
    
    if (!$empleado) throw new Exception('Empleado no encontrado');
    
    $primerDia = date('Y-m-01', strtotime($data['anio'] . '-' . $data['mes'] . '-01'));
    $ultimoDia = date('Y-m-t', strtotime($data['anio'] . '-' . $data['mes'] . '-01'));
    
    // Obtenemos TODOS los fichajes sin agrupar en SQL
    $stmtFichajes = $pdo->prepare("
        SELECT * FROM FICHAJE 
        WHERE id_usuario = ? AND fecha BETWEEN ? AND ?
        ORDER BY fecha ASC, id_fichaje ASC
    ");
    $stmtFichajes->execute([$data['id_usuario'], $primerDia, $ultimoDia]);
    $rawFichajes = $stmtFichajes->fetchAll(PDO::FETCH_ASSOC);
    
    // Agrupar en PHP y detectar si hay jornada partida
    $fichajesPorDia = [];
    $tienePartida = false;
    foreach ($rawFichajes as $f) {
        $fecha = $f['fecha'];
        if (!isset($fichajesPorDia[$fecha])) $fichajesPorDia[$fecha] = [];
        $fichajesPorDia[$fecha][] = $f;
        if (count($fichajesPorDia[$fecha]) > 1) {
            $tienePartida = true; // Si hay más de 1 fichaje en un día, es partida
        }
    }
    
    $dirPdf = __DIR__ . '/../uploads/reportes_pdf/';
    if (!file_exists($dirPdf)) mkdir($dirPdf, 0755, true);
    
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');
    $pdf->SetCreator('Sistema de Fichajes');
    $pdf->SetAuthor($empleado['nombre_empresa']);
    $pdf->SetTitle('Registro de Jornada - ' . $empleado['nombre']);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->AddPage();
    
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'Listado Resumen mensual del registro de jornada', 0, 1, 'C');
    $pdf->Ln(2);
    
    $pdf->SetFont('helvetica', '', 9);
    $html = '<table border="1" cellpadding="3">
        <tr>
            <td width="25%"><b>Empresa:</b></td><td width="25%">' . htmlspecialchars($empleado['nombre_empresa']) . '</td>
            <td width="25%"><b>Trabajador:</b></td><td width="25%">' . htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellidos']) . '</td>
        </tr>
        <tr>
            <td><b>C.I.F./N.I.F.:</b></td><td>' . htmlspecialchars($empleado['CIF'] ?? '') . '</td>
            <td><b>N.I.F.:</b></td><td>' . htmlspecialchars($empleado['NIF'] ?? '') . '</td>
        </tr>
        <tr>
            <td><b>Mes y Año:</b></td><td colspan="3">' . $data['mes'] . '/' . $data['anio'] . '</td>
        </tr>
    </table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln(3);
    
    // TABLA DINÁMICA (4 columnas o 2 columnas)
    $htmlTabla = '<table border="1" cellpadding="2" style="font-size:8px; text-align:center;">
        <thead style="background-color:#cccccc; font-weight:bold;">
            <tr>
                ';
                
    if ($tienePartida) {
        $htmlTabla .= '
                        <th width="14.3%">DÍA</th>
                       <th width="14.3%">ENTRADA 1</th><th width="14.3%">SALIDA 1</th>
                       <th width="14.3%">ENTRADA 2</th><th width="14.3%">SALIDA 2</th>
                       <th width="14.3%">TOTAL H.</th><th width="14.2%">FIRMA</th></tr></thead><tbody>';
    } else {
        $htmlTabla .= '
                       <th width="20%">DÍA</th>
                       <th width="20%">HORA ENTRADA</th><th width="20%">HORA SALIDA</th>
                       <th width="20%">HORAS ORDINARIAS</th><th width="20%">FIRMA TRABAJADOR</th></tr></thead><tbody>';
    }
    
    $diasEnMes = date('t', strtotime($primerDia));
    $totalHoras = 0;
    
    for ($dia = 1; $dia <= $diasEnMes; $dia++) {
        $fecha = sprintf('%04d-%02d-%02d', $data['anio'], $data['mes'], $dia);
        $tramos = $fichajesPorDia[$fecha] ?? [];
        
        $f1 = $tramos[0] ?? null;
        $f2 = $tramos[1] ?? null;
        
        $ent1 = $f1 && $f1['hora_entrada'] ? substr($f1['hora_entrada'], 0, 5) : '';
        $sal1 = $f1 && $f1['hora_salida'] ? substr($f1['hora_salida'], 0, 5) : '';
        $ent2 = $f2 && $f2['hora_entrada'] ? substr($f2['hora_entrada'], 0, 5) : '';
        $sal2 = $f2 && $f2['hora_salida'] ? substr($f2['hora_salida'], 0, 5) : '';
        
        $horasDia = ($f1 ? calcularHorasFichaje($f1) : 0) + ($f2 ? calcularHorasFichaje($f2) : 0);
        $totalHoras += $horasDia;
        
        $strHoras = $horasDia > 0 ? number_format($horasDia, 2) : '';
        
        $htmlTabla .= '<tr><td>' . $dia . '</td>';
        if ($tienePartida) {
            $htmlTabla .= "<td>$ent1</td><td>$sal1</td><td>$ent2</td><td>$sal2</td><td>$strHoras</td><td></td></tr>";
        } else {
            $htmlTabla .= "<td>$ent1</td><td>$sal1</td><td>$strHoras</td><td></td></tr>";
        }
    }
    
    $htmlTabla .= '<tr style="background-color:#eeeeee; font-weight:bold;">';
    if ($tienePartida) {
        $htmlTabla .= '<td colspan="5" align="right">TOTAL HORAS:</td><td>' . number_format($totalHoras, 2) . '</td><td></td>';
    } else {
        $htmlTabla .= '<td colspan="3" align="right">TOTAL HORAS:</td><td>' . number_format($totalHoras, 2) . '</td><td></td>';
    }
    $htmlTabla .= '</tr></tbody></table>';
    
    $pdf->writeHTML($htmlTabla, true, false, true, false, '');
    
    $textoLegal = 'Registro realizado en cumplimiento de la letra h) del artículo 1 del R.D.-Ley 16/2013... (El empresario deberá conservar los resúmenes por cuatro años).';
    $pdf->SetFont('helvetica', '', 6);
    $pdf->SetY(-15);
    $pdf->MultiCell(0, 4, $textoLegal, 0, 'J');
    
    $nombreArchivo = 'reporte_' . $data['id_usuario'] . '_' . $data['mes'] . '_' . $data['anio'] . '_' . time() . '.pdf';
    $rutaArchivo = $dirPdf . $nombreArchivo;
    $pdf->Output($rutaArchivo, 'F');
    
    $stmtReporte = $pdo->prepare("INSERT INTO REPORTES (id_usuario, id_empresa, tipo_reporte, mes, anio, generado_por, ruta_archivo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtReporte->execute([$data['id_usuario'], $empresa, $data['tipo'] ?? 'registro_jornada', $data['mes'], $data['anio'], $idUsuarioSolicitante, 'uploads/reportes_pdf/' . $nombreArchivo]);
    
    echo json_encode(['success' => true, 'id_reporte' => $pdo->lastInsertId(), 'url_pdf' => 'uploads/reportes_pdf/' . $nombreArchivo]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>