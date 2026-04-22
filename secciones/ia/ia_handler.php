<?php
// ─────────────────────────────────────────────────────────────
// secciones/ia/ia_handler.php — Streaming SSE
// Envía tokens al cliente conforme los genera Ollama
// ─────────────────────────────────────────────────────────────

set_time_limit(600);

// Cabeceras SSE — el cliente leerá el stream con fetch + ReadableStream
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');   // evitar que nginx/apache bufferice
header('Access-Control-Allow-Origin: *');

// Vaciar todos los buffers de salida
while (ob_get_level()) ob_end_clean();

require_once __DIR__ . '/../../config.php';

// ── Parámetros ───────────────────────────────────────────────
$ip_vm      = "192.168.0.19";
$url_ollama = "http://$ip_vm:11434/api/generate";

$input       = json_decode(file_get_contents('php://input'), true);
$pregunta    = trim($input['mensaje']  ?? '');
$seccion     = trim($input['seccion']  ?? '');
$vista       = trim($input['vista']    ?? '');
$esAdmin     = isset($input['es_admin']) ? (bool)$input['es_admin'] : false;
$historial   = $input['historial'] ?? [];

if ($pregunta === '') {
    echo "data: " . json_encode(['error' => 'Mensaje vacío']) . "\n\n";
    flush();
    exit;
}

// ── System prompt ────────────────────────────────────────────
$rolUsuario = $esAdmin ? 'administrador' : 'empleado';

$contextoSecciones = [
    'fichaje' => [
        'inicio'    => 'El usuario está en la pantalla de fichaje. Puede registrar su entrada y salida.',
        'ver'       => 'El usuario está viendo el historial de sus fichajes.',
        'modificar' => 'El administrador está corrigiendo fichajes de empleados.',
    ],
    'horario' => [
        'peticiones' => 'El usuario está en peticiones de horario.',
        'cuadrantes' => 'El usuario está en el cuadrante de horario.',
        'vacaciones' => 'El usuario está viendo el cuadrante de vacaciones.',
    ],
    'reportes' => [
        'generar'   => 'El usuario está generando un reporte PDF o Excel.',
        'historial' => 'El usuario está viendo reportes generados.',
    ],
    'empleados' => [
        'lista'  => 'El administrador está viendo la lista de empleados.',
        'nuevo'  => 'El administrador está añadiendo un nuevo empleado.',
    ],
    'perfil'         => ['*' => 'El usuario está en su perfil.'],
    'notificaciones' => ['*' => 'El usuario está en notificaciones.'],
    'tickets'        => ['*' => 'El usuario está en el sistema de tickets.'],
];

$contextoSeccion = '';
if (isset($contextoSecciones[$seccion])) {
    $mapa = $contextoSecciones[$seccion];
    $contextoSeccion = $mapa[$vista] ?? $mapa['*'] ?? reset($mapa);
}

$systemPrompt = "Eres el asistente inteligente del sistema de fichajes FesolCheck.
Tu función es ayudar a los usuarios ({$rolUsuario}s) con dudas sobre el uso de la aplicación.
Responde siempre en español, de forma concisa, clara y amable.
No inventes funcionalidades. Si no sabes algo, dilo con educación.
No respondas preguntas ajenas al sistema de fichajes o gestión de personal.
Los tipos de jornada son: TRABAJO, PARTIDA (mañana/tarde), VACACIONES, MEDICO, LIBRE, FESTIVO.
Las plantillas de jornada son configurables por empresa.";

if ($contextoSeccion !== '') {
    $systemPrompt .= "\n\nContexto: {$contextoSeccion}";
}

// ── Construir prompt con historial ───────────────────────────
$promptConversacion = '';
foreach ($historial as $turno) {
    $rol = $turno['rol'] === 'usuario' ? 'Usuario' : 'Asistente';
    $promptConversacion .= "{$rol}: " . trim($turno['texto']) . "\n";
}

$promptFinal = "Sistema: {$systemPrompt}\n\n{$promptConversacion}Usuario: {$pregunta}\nAsistente:";

// ── Llamada a Ollama con stream:true ─────────────────────────
$payload = [
    "model"   => "asistente-fichajes",
    "prompt"  => $promptFinal,
    "stream"  => true,
    "options" => [
        "temperature" => 0.7,
        "num_predict" => 512
    ]
];

$respuestaCompleta = '';

$ch = curl_init($url_ollama);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
curl_setopt($ch, CURLOPT_TIMEOUT, 450);

// Callback que recibe cada chunk NDJSON de Ollama y lo reenvía al cliente
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use (&$respuestaCompleta) {
    // Ollama devuelve una línea JSON por cada token
    $lineas = explode("\n", $data);
    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if ($linea === '') continue;

        $json = json_decode($linea, true);
        if (!$json) continue;

        // Token normal — reenviarlo al cliente
        if (isset($json['response']) && $json['response'] !== '') {
            $respuestaCompleta .= $json['response'];
            echo "data: " . json_encode(['token' => $json['response']]) . "\n\n";
            flush();
        }

        // Fin del stream
        if (!empty($json['done'])) {
            echo "data: [DONE]\n\n";
            flush();
        }
    }
    return strlen($data);
});

$ok = curl_exec($ch);
$err = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Si hubo error de conexión, mandar evento de error para que el JS lo muestre
if ($err || $code !== 200) {
    echo "data: " . json_encode(['error' => "Error conectando con el asistente (HTTP {$code})"]) . "\n\n";
    flush();
}