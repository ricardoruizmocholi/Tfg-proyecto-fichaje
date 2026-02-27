<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

// Incluir TCPDF
require_once __DIR__ . '/../tcpdf/tcpdf.php';

// Verificar que el usuario es admin

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

try {
    // Obtener datos del empleado
    $stmtEmpleado = $pdo->prepare("
        SELECT U.*, E.nombre as nombre_empresa, E.CIF, E.CCC
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
    
    // Crear directorio para PDFs si no existe
    //Revision 
    $dirPdf = __DIR__ . '/../reportes_pdf/';
    if (!file_exists($dirPdf)) {
        mkdir($dirPdf, 0755, true);
    }
    
    // Generar PDF
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');
    
    // Configuración del documento
    $pdf->SetCreator('Sistema de Fichajes');
    $pdf->SetAuthor($empleado['nombre_empresa']);
    $pdf->SetTitle('Registro de Jornada - ' . $empleado['nombre'] . ' ' . $empleado['apellidos']);
    
    // Quitar header y footer por defecto
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Configurar márgenes
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 10);
    
    // Añadir página
    $pdf->AddPage();
    
    // Título
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Listado Resumen mensual del registro de jornada (completo)', 0, 1, 'C');
    
    $pdf->Ln(5);
    
    // Información de la empresa y trabajador
    $pdf->SetFont('helvetica', '', 9);
    
    // Tabla de información
    $html = '<table border="1" cellpadding="3">
        <tr>
            <td width="25%"><b>Empresa:</b></td>
            <td width="25%">' . htmlspecialchars($empleado['nombre_empresa']) . '</td>
            <td width="25%"><b>Trabajador:</b></td>
            <td width="25%">' . htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellidos']) . '</td>
        </tr>
        <tr>
            <td><b>C.I.F./N.I.F.:</b></td>
            <td>' . htmlspecialchars($empleado['CIF'] ?? '') . '</td>
            <td><b>N.I.F.:</b></td>
            <td>' . htmlspecialchars($empleado['NIF'] ?? '') . '</td>
        </tr>
        <tr>
            <td><b>Centro de Trabajo:</b></td>
            <td>' . htmlspecialchars($empleado['nombre_empresa']) . '</td>
            <td><b>Nº Afiliación:</b></td>
            <td>' . htmlspecialchars($empleado['Numero_Afiliciacion'] ?? '') . '</td>
        </tr>
        <tr>
            <td><b>C.C.C.:</b></td>
            <td>' . htmlspecialchars($empleado['CCC'] ?? '') . '</td>
            <td><b>Mes y Año:</b></td>
            <td>' . $data['mes'] . '/' . $data['anio'] . '</td>
        </tr>
    </table>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    
    $pdf->Ln(5);
    
    // Tabla de fichajes
    $nombresMeses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                     'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    
    $htmlTabla = '<table border="1" cellpadding="2" style="font-size:8px;">
        <thead>
            <tr style="background-color:#cccccc;">
                <th width="16.666666666666666666%"><b>DÍA</b></th>
                <th width="16.666666666666666666%"><b>HORA ENTRADA</b></th>
                <th width="16.666666666666666666%"><b>HORA SALIDA</b></th>
                <th width="16.666666666666666666%"><b>HORAS ORDINARIAS</b></th>
                <th width="16.666666666666666666%"><b>REGISTRO DE FIRMAS<br>ENTRADA</b></th>
                <th width="16.666666666666666666%"><b>REGISTRO DE FIRMAS<br>SALIDA</b></th>
            </tr>
        </thead>
        <tbody>';
    
    // Organizar fichajes por día
    $fichajesPorDia = [];
    foreach ($fichajes as $f) {
        $fichajesPorDia[$f['fecha']] = $f;
    }
    
    // Generar filas para cada día del mes
    $diasEnMes = date('t', strtotime($primerDia));
    $totalHoras = 0;
    
    for ($dia = 1; $dia <= $diasEnMes; $dia++) {
        $fecha = date('Y-m-d', strtotime($data['anio'] . '-' . $data['mes'] . '-' . str_pad($dia, 2, '0', STR_PAD_LEFT)));
        $fichaje = $fichajesPorDia[$fecha] ?? null;
        
        $horaEntrada = $fichaje ? substr($fichaje['hora_entrada'], 0, 5) : '';
        $horaSalida = $fichaje ? substr($fichaje['hora_salida'], 0, 5) : '';
        
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
        
        $horasOrdinarias = $horasDia > 0 ? number_format($horasDia, 2) : '';
        
        $htmlTabla .= '<tr>
            <td align="center">' . $dia . '</td>
            <td align="center">' . $horaEntrada . '</td>
            <td align="center">' . $horaSalida . '</td>
            <td align="center">' . $horasOrdinarias . '</td>
            <td></td>
            <td></td>
        </tr>';
    }
    
    // Fila de total
    $htmlTabla .= '<tr style="background-color:#eeeeee;">
        <td align="center"><b>TOTAL</b></td>
        <td></td>
        <td></td>
        <td align="center"><b>' . number_format($totalHoras, 2) . '</b></td>
        <td></td>
        <td></td>
    </tr>';
    
    $htmlTabla .= '</tbody></table>';
    
    $pdf->writeHTML($htmlTabla, true, false, true, false, '');
    
    $pdf->Ln(5);
    
    // Firmas
    $htmlFirmas = '<table cellpadding="5">
        <tr>
            <td width="50%">Firma de la empresa:</td>
            <td width="50%">Firma del trabajador:</td>
        </tr>
    </table>';
    
    $altoPie = 22; // mm reservados para texto legal
    $yMaxTabla = $pdf->getPageHeight() - $altoPie - 10;
    
    $pdf->writeHTML($htmlFirmas, true, false, true, false, '');
    
    $pdf->Ln(5);
    
    // Fecha y lugar
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(30, 5, 'En', 0, 0);
    $pdf->Cell(60, 5, 'VALÈNCIA', 'B', 0, 'C');
    $pdf->Cell(10, 5, ', a', 0, 0);
    $pdf->Cell(20, 5, date('d'), 'B', 0, 'C');
    $pdf->Cell(10, 5, 'de', 0, 0);
    $pdf->Cell(40, 5, strtoupper($nombresMeses[$data['mes']]), 'B', 0, 'C');
    $pdf->Cell(10, 5, 'de', 0, 0);
    $pdf->Cell(20, 5, $data['anio'], 'B', 0, 'C');


 

    // ===============================
    // TEXTO LEGAL PIE DE PÁGINA
    // ===============================
    $textoLegal = 'Registro realizado en cumplimiento de la letra h) del artículo 1 del R.D.-Ley 16/2013, de 20 de diciembre, por el que se modifica el artículo 12.5 del E.T., por el que se establece que "La jornada de los trabajadores a tiempo parcial se registrará día a día y se totalizará mensualmente, entregando copia al trabajador junto con el recibo de salarios del resumen de todas las horas realizadas en cada mes, tanto de las ordinarias como de las complementarias en sus distintas modalidades. El empresario deberá conservar los resúmenes mensuales de los registros de jornada por un periodo mínimo de cuatro años. El incumplimiento empresarial de estas obligaciones de registro tendrá como consecuencia jurídica la presunción de que el contrato se ha celebrado a jornada completa, salvo prueba en contrario que acredite el carácter parcial de los servicios."';

    $pdf->SetFont('helvetica', '', 6);
    $pdf->SetY(-20);
    $pdf->MultiCell(0, 4, $textoLegal, 0, 'J');

    
    // Guardar PDF
    $nombreArchivo = 'reporte_' . $data['id_usuario'] . '_' . $data['mes'] . '_' . $data['anio'] . '_' . time() . '.pdf';
    $rutaArchivo = $dirPdf . $nombreArchivo;
    $pdf->Output($rutaArchivo, 'F');
    
    // Guardar en base de datos
    $stmtReporte = $pdo->prepare("
        INSERT INTO REPORTES (id_usuario, id_empresa, tipo_reporte, mes, anio, generado_por, ruta_archivo)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtReporte->execute([
        $data['id_usuario'],
        $empresa,
        $data['tipo'] ?? 'registro_jornada',
        $data['mes'],
        $data['anio'],
        $idUsuarioSolicitante,
        'reportes_pdf/' . $nombreArchivo
    ]);
    
    $idReporte = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Reporte generado correctamente',
        'id_reporte' => $idReporte,
        'url_pdf' => 'reportes_pdf/' . $nombreArchivo
    ]);
    
} catch (Exception $e) {
    error_log('Error en reporte_generar_pdf.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>