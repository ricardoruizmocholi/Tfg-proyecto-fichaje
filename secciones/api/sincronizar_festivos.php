<?php
// secciones/api/sincronizar_festivos.php
session_start();
header('Content-Type: application/json');
require_once '../../config.php';

$data = json_decode(file_get_contents('php://input'), true);
$id_empresa = $_SESSION['empresa_activa'];
$anio = date('Y');

// Obtenemos la región del selector (ej: "ES-VC", "ES-MD")
// Nager.Date usa el formato ISO, pero para filtrar en PHP usaremos el código de provincia si viene informado
$region_seleccionada = $data['region'] ?? ''; 

// URL de Nager.Date (Sin API Key, directo y gratis)
$url = "https://date.nager.at/api/v3/PublicHolidays/$anio/ES";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'SistemaHorariosApp/1.0'); // Identificador amigable
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$resData = json_decode($response, true);

if ($http_code === 200 && is_array($resData)) {
    try {
        $pdo->beginTransaction();

        // 1. Limpiamos festivos previos de este año para esta empresa
        $pdo->prepare("DELETE FROM FESTIVOS_EMPRESA WHERE id_empresa = ? AND YEAR(fecha) = ?")
            ->execute([$id_empresa, $anio]);

        $stmt = $pdo->prepare("INSERT INTO FESTIVOS_EMPRESA (id_empresa, fecha, nombre_festivo, tipo_festivo) VALUES (?, ?, ?, ?)");
        
        $contador = 0;
        foreach ($resData as $h) {
            /* FILTRO DE REGIÓN:
               Nager.Date devuelve 'counties' como un array de regiones donde es festivo.
               Si 'counties' es NULL, es festivo NACIONAL.
               Si tiene datos, comprobamos si nuestra región está ahí.
            */
            $es_nacional = is_null($h['counties']);
            $es_de_mi_region = !$es_nacional && in_array($region_seleccionada, $h['counties']);

            if ($es_nacional || $es_de_mi_region) {
                $stmt->execute([
                    $id_empresa, 
                    $h['date'], 
                    $h['localName'], // Nombre en español
                    $h['types'][0]   // Suele ser 'Public'
                ]);
                $contador++;
            }
        }

        $pdo->commit();
        echo json_encode([
            'success' => true, 
            'mensaje' => "¡Éxito! Se han cargado $contador festivos (Nacionales y de la región $region_seleccionada) sin usar API Keys."
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => "Error en la base de datos: " . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => "No se pudo conectar con Nager.Date (Código: $http_code)"]);
}