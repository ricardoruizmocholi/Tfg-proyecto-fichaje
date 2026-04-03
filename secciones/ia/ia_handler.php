<?php
// 1. Configuración de límites de tiempo (Vital para Ollama en CPU)
set_time_limit(600); // Permite que el script corra hasta 10 minutos

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php'; // Ajusta la ruta según tu carpeta

// 2. Parámetros de la Máquina Virtual
$ip_vm = "192.168.0.11"; // CAMBIA ESTO por la IP de tu Ubuntu Server (ip a)
$url_ollama = "http://$ip_vm:11434/api/generate";

// 3. Capturar la pregunta del usuario (desde un fetch de JS)
$input = json_decode(file_get_contents('php://input'), true);
$preguntaUsuario = $input['mensaje'] ?? 'Hola';

// 4. Configuración del Contexto (System Prompt)
// Esto hace que la IA se comporte como el asistente de TU proyecto.
$contexto = "Eres el asistente inteligente del sistema de fichajes de Ricardo Ruiz. 
Tu objetivo es ayudar a los empleados con dudas sobre horarios, registro de jornada y vacaciones. 
Responde de forma concisa y profesional en español.";

$data = [
    "model" => "phi3", // El modelo ligero que descargamos
    "prompt" => "Contexto: $contexto \n\n Usuario pregunta: $preguntaUsuario",
    "stream" => false  // Para que mande la respuesta de golpe y no letra a letra
];

// 5. Ejecución de la llamada a la IA con cURL
$ch = curl_init($url_ollama);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

// Tiempos de espera extendidos
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); // 15 seg para conectar
curl_setopt($ch, CURLOPT_TIMEOUT, 450);        // 7.5 min para generar respuesta

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    echo json_encode([
        "success" => false, 
        "respuesta" => "Error de conexión con el servidor IA: " . $error_msg
    ]);
} else {
    if ($http_code !== 200) {
        echo json_encode([
            "success" => false, 
            "respuesta" => "Ollama devolvió un error (Código $http_code). Revisa que el servicio esté corriendo."
        ]);
    } else {
        $result = json_decode($response, true);
        echo json_encode([
            "success" => true,
            "respuesta" => $result['response']
        ]);
    }
}

curl_close($ch);
?>