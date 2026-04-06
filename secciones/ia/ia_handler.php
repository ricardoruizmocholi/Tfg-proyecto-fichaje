<?php
// 1. Configuración de límites de tiempo (Vital para Ollama en CPU)
set_time_limit(600);

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

// 2. Parámetros de la Máquina Virtual
$ip_vm = "192.168.0.11";
$url_ollama = "http://$ip_vm:11434/api/generate";

// 3. Capturar datos del usuario (pregunta + contexto de sección)
$input = json_decode(file_get_contents('php://input'), true);
$preguntaUsuario  = trim($input['mensaje']  ?? '');
$seccionActual    = trim($input['seccion']  ?? '');
$vistaActual      = trim($input['vista']    ?? '');
$esAdmin          = isset($input['es_admin']) ? (bool)$input['es_admin'] : false;

if ($preguntaUsuario === '') {
    echo json_encode(["success" => false, "respuesta" => "No se ha recibido ningún mensaje."]);
    exit;
}

// 4. Construir contexto dinámico según la sección/vista en la que está el usuario
$rolUsuario = $esAdmin ? 'administrador' : 'empleado';

$contextoSecciones = [
    'fichaje' => [
        'inicio'    => 'El usuario está en la pantalla de fichaje. Puede registrar su entrada y salida. Explica cómo fichar, qué ocurre si olvida fichar y cómo se registran las pausas.',
        'ver'       => 'El usuario está viendo el historial de sus fichajes. Puede filtrar por fechas y revisar las horas registradas cada día.',
        'modificar' => 'El usuario (administrador) está en la sección para corregir o modificar fichajes de empleados. Puede cambiar horas de entrada/salida y añadir justificaciones.',
    ],
    'horario' => [
        'peticiones' => 'El usuario está en la sección de peticiones de horario. Aquí puede solicitar cambios de turno, días libres o vacaciones, y ver el estado de sus solicitudes.',
        'cuadrantes' => 'El usuario está viendo los cuadrantes de horario. Se muestran los turnos asignados a cada empleado por semana o mes.',
        'eventos'    => 'El usuario está en la sección de eventos del horario, donde se gestionan días especiales, festivos y ausencias.',
    ],
    'reportes' => [
        'generar'   => 'El usuario está generando un reporte. Puede exportar datos de fichajes a PDF o Excel, filtrando por empleado, rango de fechas o departamento.',
        'historial' => 'El usuario está viendo el historial de reportes generados anteriormente. Puede descargarlos de nuevo.',
    ],
    'empleados' => [
        'lista'  => 'El usuario (administrador) está viendo la lista de todos los empleados. Puede buscar, filtrar y acceder al perfil de cada uno.',
        'nuevo'  => 'El usuario (administrador) está añadiendo un nuevo empleado. Debe rellenar nombre, apellidos, email, empresa y rol.',
    ],
    'perfil'         => ['*' => 'El usuario está en su perfil. Puede cambiar su contraseña, ver sus datos personales y actualizar su información de contacto.'],
    'notificaciones' => ['*' => 'El usuario está en la sección de notificaciones. Aquí aparecen alertas del sistema: fichajes pendientes, aprobaciones de solicitudes y avisos del administrador.'],
    'documentos'     => ['*' => 'El usuario está en la sección de documentos, donde puede consultar o descargar documentos relacionados con su empresa.'],
];

// Buscar el contexto específico de la sección y vista actuales
$contextoSeccion = '';
if (isset($contextoSecciones[$seccionActual])) {
    $mapa = $contextoSecciones[$seccionActual];
    if (isset($mapa[$vistaActual])) {
        $contextoSeccion = $mapa[$vistaActual];
    } elseif (isset($mapa['*'])) {
        $contextoSeccion = $mapa['*'];
    } elseif (!empty($mapa)) {
        // Tomar el primero disponible si no hay coincidencia exacta
        $contextoSeccion = reset($mapa);
    }
}

// 5. System prompt completo y dinámico
$systemPrompt = "Eres el asistente inteligente del sistema de fichajes. 
Tu función es ayudar a los usuarios ({$rolUsuario}s) con dudas sobre el uso de la aplicación.
Responde siempre en español, de forma concisa, clara y amable.
No inventes funcionalidades que no existan. Si no sabes algo, dilo con educación.
No respondas preguntas que no tengan relación con el sistema de fichajes o gestión de personal.";

if ($contextoSeccion !== '') {
    $systemPrompt .= "\n\nContexto actual del usuario: {$contextoSeccion}";
}

// 6. Historial de conversación (para mantener contexto entre mensajes)
// El frontend puede enviar un array 'historial' con los mensajes previos
$historial = $input['historial'] ?? [];
$promptConversacion = '';

foreach ($historial as $turno) {
    $rol = $turno['rol'] === 'usuario' ? 'Usuario' : 'Asistente';
    $promptConversacion .= "{$rol}: " . trim($turno['texto']) . "\n";
}

$promptFinal = "Sistema: {$systemPrompt}\n\n{$promptConversacion}Usuario: {$preguntaUsuario}\nAsistente:";

// 7. Payload para Ollama
$data = [
    "model"  => "phi3",
    "prompt" => $promptFinal,
    "stream" => false,
    "options" => [
        "temperature" => 0.7,
        "num_predict" => 512
    ]
];

// 8. Llamada cURL a Ollama
$ch = curl_init($url_ollama);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
curl_setopt($ch, CURLOPT_TIMEOUT, 450);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode([
        "success"   => false,
        "respuesta" => "Error de conexión con el servidor IA: " . curl_error($ch)
    ]);
} elseif ($http_code !== 200) {
    echo json_encode([
        "success"   => false,
        "respuesta" => "Ollama devolvió un error (Código {$http_code}). Revisa que el servicio esté corriendo."
    ]);
} else {
    $result   = json_decode($response, true);
    $respuesta = isset($result['response']) ? trim($result['response']) : 'No se obtuvo respuesta.';
    echo json_encode([
        "success"   => true,
        "respuesta" => $respuesta
    ]);
}

curl_close($ch);
?>