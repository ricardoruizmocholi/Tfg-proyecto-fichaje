<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Evitar que errores PHP rompan el JSON de respuesta
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
$idUsuario = $data['id_usuario'] ?? null;
$anio = $data['anio'] ?? date('Y');
$empresa = $_SESSION['empresa_activa'];

if (!$idUsuario) {
    echo json_encode(['success' => false, 'message' => 'Usuario no especificado']);
    exit;
}

// Función para calcular horas (la misma lógica que usamos en el mensual)
function calcularHoras($f) {
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
    // 1. Datos del empleado
    $stmtEmp = $pdo->prepare("SELECT U.*, E.nombre as nombre_empresa, E.CIF 
                             FROM USUARIO U 
                             JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario 
                             JOIN EMPRESA E ON EU.id_empresa = E.id_empresa
                             WHERE U.id_usuario = ? AND EU.id_empresa = ?");
    $stmtEmp->execute([$idUsuario, $empresa]);
    $empleado = $stmtEmp->fetch(PDO::FETCH_ASSOC);

    // 2. Obtener TODOS los fichajes del año
    $stmtFichajes = $pdo->prepare("SELECT fecha, hora_entrada, hora_salida, hora_pausa, hora_reanudacion 
                                  FROM FICHAJE 
                                  WHERE id_usuario = ? AND YEAR(fecha) = ? 
                                  ORDER BY fecha ASC");
    $stmtFichajes->execute([$idUsuario, $anio]);
    $fichajes = $stmtFichajes->fetchAll(PDO::FETCH_ASSOC);

    // 3. Agrupar y sumar horas por mes
    $totalesMes = array_fill(1, 12, 0); // Inicializar meses 1-12 a cero
    foreach ($fichajes as $f) {
        $mes = (int)date('n', strtotime($f['fecha']));
        $totalesMes[$mes] += calcularHoras($f);
    }

    // 4. Generar PDF con TCPDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
    $pdf->SetTitle("Reporte Anual $anio - " . $empleado['nombre']);
    $pdf->setPrintHeader(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, "RESUMEN ANUAL DE HORAS TRABAJADAS - $anio", 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 7, "Empleado: " . $empleado['nombre'] . " " . $empleado['apellidos'], 0, 1);
    $pdf->Cell(0, 7, "Empresa: " . $empleado['nombre_empresa'] . " (CIF: " . $empleado['CIF'] . ")", 0, 1);
    $pdf->Ln(5);

    // Tabla de resultados (Sin columna de firma ni fila, solo Mes y Horas)
    $html = '<table border="1" cellpadding="5" style="text-align:center;">
                <thead>
                    <tr style="background-color:#4e73df; color:white; font-weight:bold;">
                        <th width="50%">MES</th>
                        <th width="50%">TOTAL HORAS</th>
                    </tr>
                </thead>
                <tbody>';

    $mesesNombres = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $granTotal = 0;

    for ($i = 1; $i <= 12; $i++) {
        $horas = $totalesMes[$i];
        $granTotal += $horas;
        $html .= '<tr>
                    <td>' . $mesesNombres[$i] . '</td>
                    <td>' . ($horas > 0 ? number_format($horas, 2) . ' h' : '-') . '</td>
                  </tr>';
    }

    $html .= '  <tr style="background-color:#f8f9fc; font-weight:bold;">
                    <td>TOTAL ANUAL</td>
                    <td>' . number_format($granTotal, 2) . ' h</td>
                </tr>
              </tbody>
            </table>';

    $pdf->writeHTML($html, true, false, true, false, '');

    // Guardar archivo
    $nombreArchivo = "reporte_anual_{$idUsuario}_{$anio}_" . time() . ".pdf";
    $rutaRelativa = "uploads/reportes_pdf/" . $nombreArchivo;
    $pdf->Output(__DIR__ . "/../" . $rutaRelativa, 'F');

    // Registrar en la base de datos (opcional, para el historial)
    $stmtReg = $pdo->prepare("INSERT INTO REPORTES (id_usuario, id_empresa, tipo_reporte, mes, anio, generado_por, ruta_archivo) 
                             VALUES (?, ?, 'anual', 0, ?, ?, ?)");
    $stmtReg->execute([$idUsuario, $empresa, $anio, $_SESSION['usuario']['id'], $rutaRelativa]);

    echo json_encode(['success' => true, 'url' => $rutaRelativa]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}