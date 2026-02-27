<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['usuario'])) {
    die('No autorizado');
}

$idReporte = $_GET['id'] ?? null;
$preview = isset($_GET['preview']); // Si preview=1, se abre en el navegador

if (!$idReporte) {
    die('ID de reporte no proporcionado');
}

$empresa = $_SESSION['empresa_activa'];

try {
    // Obtener datos del reporte
    $stmt = $pdo->prepare("
        SELECT * FROM REPORTES 
        WHERE id_reporte = ? AND id_empresa = ?
    ");
    $stmt->execute([$idReporte, $empresa]);
    $reporte = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reporte) {
        die('Reporte no encontrado o no tienes permisos para verlo');
    }
    
    $rutaArchivo = __DIR__ . '/../' . $reporte['ruta_archivo'];
    
    if (!file_exists($rutaArchivo)) {
        die('Archivo PDF no encontrado en el servidor. Es posible que haya sido eliminado.');
    }
    
    // Obtener nombre del empleado para el archivo
    $stmtEmp = $pdo->prepare("SELECT nombre, apellidos FROM USUARIO WHERE id_usuario = ?");
    $stmtEmp->execute([$reporte['id_usuario']]);
    $empleado = $stmtEmp->fetch(PDO::FETCH_ASSOC);
    
    $nombresMeses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                     'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    
    $nombreDescarga = 'Registro_Jornada_' . 
                     str_replace(' ', '_', $empleado['nombre'] . '_' . $empleado['apellidos']) . '_' .
                     $nombresMeses[$reporte['mes']] . '_' . 
                     $reporte['anio'] . '.pdf';
    
    // Limpiar buffer de salida
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Enviar headers
    header('Content-Type: application/pdf');
    header('Content-Length: ' . filesize($rutaArchivo));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Si es preview, abrir en navegador. Si no, descargar
    if ($preview) {
        header('Content-Disposition: inline; filename="' . $nombreDescarga . '"');
    } else {
        header('Content-Disposition: attachment; filename="' . $nombreDescarga . '"');
    }
    
    // Enviar archivo
    readfile($rutaArchivo);
    exit;
    
} catch (Exception $e) {
    error_log('Error en reporte_descargar.php: ' . $e->getMessage());
    die('Error al procesar el reporte: ' . $e->getMessage());
}
?>