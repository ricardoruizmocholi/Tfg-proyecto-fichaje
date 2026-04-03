<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';

// Verificar autorización
if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    die('No autorizado');
}

if (!isset($_GET['id_usuario']) || !isset($_GET['mes']) || !isset($_GET['anio'])) {
    die('Datos incompletos');
}

$idUsuario = $_GET['id_usuario'];
$mes = $_GET['mes'];
$anio = $_GET['anio'];

try {
    // 1. OBTENER DATOS DEL EMPLEADO Y EMPRESA (Para el encabezado)
    $stmtEmp = $pdo->prepare("
        SELECT U.nombre, U.apellidos, E.nombre as nombre_empresa, E.CIF
        FROM USUARIO U
        JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
        JOIN EMPRESA E ON EU.id_empresa = E.id_empresa
        WHERE U.id_usuario = ? AND EU.activo = 1
        LIMIT 1
    ");
    $stmtEmp->execute([$idUsuario]);
    $datosBase = $stmtEmp->fetch(PDO::FETCH_ASSOC);

    // 2. CONSULTA CORREGIDA: Suma de todos los fichajes del día
    // Eliminamos 'id_empresa' de la cláusula WHERE porque no existe en la tabla FICHAJE
    $stmt = $pdo->prepare("
        SELECT 
            fecha, 
            MIN(hora_entrada) as primera_entrada, 
            MAX(hora_salida) as ultima_salida,
            SUM(TIME_TO_SEC(TIMEDIFF(IFNULL(hora_salida, hora_entrada), hora_entrada))) as segundos_totales
        FROM FICHAJE 
        WHERE id_usuario = ? 
        AND MONTH(fecha) = ? 
        AND YEAR(fecha) = ?
        GROUP BY fecha
        ORDER BY fecha ASC
    ");
    $stmt->execute([$idUsuario, $mes, $anio]);
    $fichajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. CONFIGURACIÓN DE DESCARGA
    $nombresMeses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $filename = "Reporte_" . str_replace(' ', '_', $datosBase['nombre']) . "_" . $nombresMeses[$mes] . ".xls";
    
    header("Content-Type: application/vnd.ms-excel; charset=utf-16");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    // 4. GENERACIÓN DEL CONTENIDO (HTML compatible con Excel)
    echo "<table border='1'>";
    // Encabezado de Marca (Ideal para TFG)
    echo "<tr><th colspan='4' style='background-color:#4e73df; color:white; font-size:16px;'>REGISTRO DE JORNADA - " . strtoupper($datosBase['nombre_empresa']) . "</th></tr>";
    echo "<tr><td colspan='2'><strong>Empleado:</strong> " . $datosBase['nombre'] . " " . $datosBase['apellidos'] . "</td><td colspan='2'><strong>CIF:</strong> " . $datosBase['CIF'] . "</td></tr>";
    echo "<tr><td colspan='4'><strong>Periodo:</strong> " . $nombresMeses[$mes] . " " . $anio . "</td></tr>";
    echo "<tr></tr>"; // Fila vacía de separación

    // Cabecera de tabla
    echo "<tr style='background-color:#f8f9fc;'>
            <th>Fecha</th>
            <th>Entrada (Primera)</th>
            <th>Salida (Última)</th>
            <th>Total Horas Reales</th>
          </tr>";

    $granTotalSegundos = 0; 

    foreach ($fichajes as $f) {
        $segundos = $f['segundos_totales'];
        $granTotalSegundos += $segundos;
        
        // Convertir segundos a formato HH:MM
        $h = floor($segundos / 3600);
        $m = floor(($segundos % 3600) / 60);
        $tiempoFormateado = sprintf('%02d:%02d', $h, $m);

        echo "<tr>";
        echo "<td style='text-align:center;'>" . date('d/m/Y', strtotime($f['fecha'])) . "</td>";
        echo "<td style='text-align:center;'>" . substr($f['primera_entrada'], 0, 5) . "</td>";
        echo "<td style='text-align:center;'>" . ($f['ultima_salida'] ? substr($f['ultima_salida'], 0, 5) : '-') . "</td>";
        echo "<td style='text-align:right; background-color:#f1f3f9;'><strong>" . $tiempoFormateado . "</strong></td>";
        echo "</tr>";
    }

    // Fila de Totales Finales
    $totalH = floor($granTotalSegundos / 3600);
    $totalM = floor(($granTotalSegundos % 3600) / 60);
    echo "<tr></tr>";
    echo "<tr>
            <td colspan='3' style='text-align:right; background-color:#4e73df; color:white;'><strong>TOTAL HORAS TRABAJADAS:</strong></td>
            <td style='text-align:right; background-color:#e74a3b; color:white;'><strong>" . sprintf('%02d:%02d', $totalH, $totalM) . "</strong></td>
          </tr>";
    echo "</table>";

} catch (Exception $e) {
    die('Error en el reporte: ' . $e->getMessage());
}