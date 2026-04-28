<?php
// ─────────────────────────────────────────────────────────────
// secciones/ia/ia_handler.php — Streaming SSE
// Envía tokens al cliente conforme los genera Ollama
// ─────────────────────────────────────────────────────────────

set_time_limit(600);

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
header('Access-Control-Allow-Origin: *');

while (ob_get_level()) ob_end_clean();

require_once __DIR__ . '/../../config.php';

// ── Parámetros de entrada ────────────────────────────────────
$ip_vm      = "192.168.0.19";
$url_ollama = "http://$ip_vm:11434/api/generate";

$input    = json_decode(file_get_contents('php://input'), true);
$pregunta = trim($input['mensaje']  ?? '');
$seccion  = trim($input['seccion']  ?? '');
$vista    = trim($input['vista']    ?? '');
$esAdmin  = isset($input['es_admin']) ? (bool)$input['es_admin'] : false;
$historial = $input['historial'] ?? [];

if ($pregunta === '') {
    echo "data: " . json_encode(['error' => 'Mensaje vacío']) . "\n\n";
    flush();
    exit;
}

// ── Rol del usuario ──────────────────────────────────────────
$rolUsuario = $esAdmin ? 'administrador' : 'empleado';

// ── Mapa completo de contextos por sección y vista ──────────
// Cubre TODAS las secciones existentes en panel.php
$contextoSecciones = [

    // ── FICHAJE ──────────────────────────────────────────────
    'fichaje' => [
        'inicio'    => 'El usuario está en la pantalla principal de fichaje. Desde aquí puede pulsar los botones de Entrada, Pausa, Reanudar y Salida para registrar su jornada. Si tiene jornada partida verá también los botones de Entrada/Salida del 2.º tramo.',
        'ver'       => 'El usuario está en "Ver fichajes". Puede consultar el historial de sus fichajes filtrando por día, mes o rango de fechas y por empleado. Los registros muestran hora de entrada, pausa, reanudación, salida y total de horas.',
        'modificar' => 'El administrador está en "Modificar fichajes". Puede editar directamente las horas de cualquier fichaje existente, añadir nuevos registros olvidados (incluyendo el 2.º tramo de jornada partida) y eliminar fichajes incorrectos.',
        'config_ip' => 'El administrador está en "Restricción por IP". Puede activar o desactivar la restricción de fichaje por IP, añadir IPs o prefijos de subred autorizados (hasta 50 por empresa) con etiqueta descriptiva, y eliminar IPs de la lista blanca.',
    ],

    // ── HORARIO ──────────────────────────────────────────────
    'horario' => [
        'peticiones' => 'El administrador está en "Peticiones de horario". Ve todas las solicitudes de los empleados (PENDIENTE, APROBADO, RECHAZADO) con filtros por estado, tipo y empleado. Puede aprobar directamente, rechazar introduciendo un motivo obligatorio, o abrir el modal de detalle para ver el calendario día a día de la solicitud antes de decidir.',
        'cuadrantes' => 'El usuario está en el "Cuadrante de horarios". Muestra una tabla con todos los empleados en filas y los días del período en columnas. El administrador puede editar celdas individuales o usar "Añadir en bloque" para asignar horarios a varios empleados a la vez. Permite seleccionar períodos de 1 semana, 2 semanas, 3 semanas o mes completo.',
        'vacaciones' => 'El administrador está en el "Cuadrante de vacaciones". Muestra un calendario mensual con las vacaciones aprobadas (verde ✓) y pendientes (amarillo ?) de cada empleado. Las celdas en rojo con ⚠️ indican que ese día supera el umbral del 33% de la plantilla de vacaciones simultáneas. Muestra estadísticas de empleados sin días disponibles y días con riesgo de cobertura.',
        'plantillas' => 'El administrador está en "Plantillas de Jornada". Puede crear tipos de jornada personalizados para su empresa (Turno Noche, Guardia, Teletrabajo, etc.) con nombre, color hexadecimal, horas predeterminadas de inicio/fin e indicador de si es jornada productiva. Las plantillas activas aparecen en todos los selectores de tipo de jornada de la empresa.',
        'calendario' => 'El administrador está en "Calendario empleados". Puede ver y editar directamente el calendario mensual de cualquier empleado seleccionado, sin necesidad de solicitudes ni validaciones. Los cambios se guardan inmediatamente en la base de datos.',
        // Vista por defecto para empleado (sin vista específica)
        '*' => 'El empleado está en su calendario de horario mensual. Puede añadir eventos de jornada haciendo clic en los días, usar "Añadir múltiples días" para rangos, copiar el mes anterior y enviar todos los borradores a validar con el botón "Enviar a validar". Los colores: azul=trabajo, verde=vacaciones, naranja=médico, morado=libre, rojo=festivo, amarillo punteado=pendiente.',
    ],

    // ── REPORTES ─────────────────────────────────────────────
    'reportes' => [
        'generar'   => 'El administrador está en "Generar reporte". Selecciona empleado, mes/año y tipo (mensual o anual). Puede previsualizar los datos en pantalla antes de generar el PDF oficial. También puede exportar a Excel con el botón "Excel Export". El PDF sigue el formato del "Listado Resumen mensual del registro de jornada" exigido por el RDL 8/2019.',
        'historial' => 'El administrador está en "Historial de reportes". Ve todos los reportes PDF generados con filtros por empleado, tipo, mes y año. Puede abrir el PDF en el navegador, descargarlo o eliminarlo. Si el archivo físico fue borrado del servidor, aparece el aviso "No disponible".',
    ],

    // ── EMPLEADOS ────────────────────────────────────────────
    'empleados' => [
        'lista'   => 'El administrador está en la lista de empleados. Ve una tabla con todos los empleados de la empresa (foto, nombre, email, tipo de contrato, vacaciones, rol y estado). Puede editar la ficha de cada empleado (datos personales, contrato, vacaciones, contraseña, rol admin), activar/desactivar empleados y eliminarlos.',
        'nuevo'   => 'El administrador está añadiendo un nuevo empleado. Debe rellenar: datos personales (nombre, apellidos, NIF, n.º afiliación, email), contraseña inicial, tipo de jornada (completa/parcial), horas contratadas al mes, días de vacaciones anuales, límite de horas extra y si será administrador.',
        'nominas' => 'El administrador está en "Nóminas y documentos". Puede subir archivos PDF (máx. 10 MB) asignándolos a un empleado con título, mes y año. Al subir, el empleado recibe una notificación automática. También puede ver el historial de documentos subidos y eliminar los que ya no sean necesarios.',
        'tickets' => 'El administrador está en la gestión de tickets de empleados. Ve todos los tickets con filtros por estado (PENDIENTE, EN PROCESO, RESUELTA, CERRADA), categoría y empleado. Al abrir un ticket accede al hilo de conversación donde puede responder, cambiar el estado o marcarlo como resuelto/cerrado.',
    ],

    // ── SECCIONES COMUNES ────────────────────────────────────
    'perfil' => [
        '*' => 'El usuario está en su perfil. Puede cambiar su foto (JPG/PNG/GIF/WEBP, máx. 5 MB), cambiar su contraseña (introduciendo la actual y la nueva dos veces) y ver sus datos personales y de contrato (estos últimos son de solo lectura, solo el admin puede modificarlos). También ve el resumen laboral con vacaciones disponibles, horas del mes y horas extra anuales.',
    ],
    'notificaciones' => [
        '*' => 'El usuario está en su panel de notificaciones. Ve el historial completo de avisos: aprobaciones/rechazos de horario, cambios de fichaje, respuestas a tickets y nuevas nóminas. Puede eliminar notificaciones individualmente o limpiar todas con "Limpiar todo". Las notificaciones con más de 30 días se eliminan automáticamente.',
    ],
    'tickets' => [
        '*' => 'El empleado está en su sección de tickets. Puede crear un ticket nuevo (asunto, descripción, categoría: HORARIO/NOMINA/VACACIONES/FICHAJE/OTRO, prioridad: BAJA/MEDIA/ALTA) y ver el hilo de conversación de cada uno. Los estados posibles son PENDIENTE, EN PROCESO, RESUELTA y CERRADA. Al responder a un ticket RESUELTA, el sistema lo reabre automáticamente.',
    ],
    'documentos' => [
        '*' => 'El empleado está en "Mis Nóminas". Ve todos los documentos PDF que la empresa ha subido para él, organizados por año. Puede visualizarlos en el navegador o descargarlos. Los documentos nuevos generan una notificación automática.',
    ],
];

// ── Resolver contexto activo ─────────────────────────────────
$contextoSeccion = '';
if (isset($contextoSecciones[$seccion])) {
    $mapa = $contextoSecciones[$seccion];
    // Busca la vista exacta, luego comodín '*', luego primer elemento
    $contextoSeccion = $mapa[$vista] ?? $mapa['*'] ?? reset($mapa);
}

// ── System prompt principal ──────────────────────────────────
// Dividido en bloques temáticos para mayor claridad al modelo
$systemPrompt = <<<PROMPT
Eres el asistente virtual de FesolCheck, un sistema de control de jornada y fichajes.
El usuario actual es un {$rolUsuario}.

## TU FUNCIÓN
Ayudar a los usuarios con dudas sobre el uso de FesolCheck: cómo fichar, gestionar horarios, solicitar vacaciones, revisar reportes, usar los tickets, etc.

## REGLAS DE RESPUESTA — DEBES SEGUIRLAS SIEMPRE
1. BREVEDAD: Responde en 3-5 frases como máximo. Si la respuesta requiere pasos, usa una lista numerada corta (máximo 5 puntos). Nunca te extiendas innecesariamente.
2. CERTEZA: Solo afirma lo que sabes con seguridad sobre FesolCheck. Si no conoces la respuesta exacta, di literalmente: "No tengo información exacta sobre eso. Consulta con el administrador del sistema."
3. NO INVENTES: Prohibido inventar rutas, botones, funcionalidades o configuraciones que no existan en el sistema. Si no estás seguro, no lo digas.
4. IDIOMA: Responde siempre en español.
5. TEMA: Solo responde preguntas sobre FesolCheck o sobre legislación laboral española relacionada con el registro de jornada. Si la pregunta es ajena a estos temas, responde: "Solo puedo ayudarte con dudas sobre FesolCheck o la normativa de registro horario."

## DATOS SENSIBLES — NUNCA REVELES
- Credenciales de acceso (contraseñas, hashes, tokens).
- Datos de configuración del servidor (IPs internas, rutas del servidor, credenciales de base de datos).
- Información personal de otros empleados (salarios, NIF, datos médicos, fichajes de otras personas).
- Estructura interna de la base de datos o código fuente.
- Datos de empresa que no correspondan al usuario que pregunta.
Si se pregunta por cualquiera de estos datos, responde: "No puedo proporcionar esa información por motivos de seguridad y privacidad."

## LO QUE PUEDES RESPONDER
- Cómo usar cada sección del panel (fichaje, horario, reportes, empleados, perfil, tickets, nóminas).
- Flujo de solicitudes de horario y vacaciones (crear borrador → enviar → pendiente → aprobado/rechazado).
- Tipos de jornada: TRABAJO, PARTIDA_M (mañana), PARTIDA_T (tarde), VACACIONES, MEDICO, LIBRE, FESTIVO, y plantillas personalizadas de empresa.
- Colores del calendario: azul=trabajo continuo, azul índigo=partida mañana, rosa=partida tarde, verde=vacaciones, naranja=médico, morado=libre, rojo=festivo, amarillo punteado=pendiente, azul discontinuo=borrador temporal.
- Restricción de fichaje por IP: cómo activarla, añadir IPs/prefijos de subred, eliminarlas.
- Generación de reportes PDF (formato oficial RDL 8/2019) y exportación a Excel.
- Gestión de empleados, contratos, vacaciones acumuladas y horas extra (límite legal: 80h/año).
- Sistema de tickets: categorías, estados, hilo de conversación.
- Notificaciones automáticas del sistema.
- Normativa española de registro horario (RDL 8/2019, ET art. 34-35, LISOS).

## FORMATO DE RESPUESTA
- Para preguntas simples: 1-2 frases directas.
- Para procedimientos: lista numerada, máximo 5 pasos.
- Para aclaraciones de conceptos: máximo 3 frases.
- Nunca uses encabezados Markdown (##, **negrita**) en la respuesta al usuario, solo texto plano y listas simples con números o guiones.
PROMPT;

// Añadir contexto de sección si se dispone de él
if ($contextoSeccion !== '') {
    $systemPrompt .= "\n\n## CONTEXTO ACTUAL\nEl usuario se encuentra ahora en: {$contextoSeccion}";
    $systemPrompt .= "\nAdapta tu respuesta a lo que el usuario puede hacer en esta pantalla.";
}

// ── Construir prompt con historial ───────────────────────────
// Se limita a los últimos 8 turnos para no inflar el contexto
$historialReciente = array_slice($historial, -8);
$promptConversacion = '';
foreach ($historialReciente as $turno) {
    $rol = $turno['rol'] === 'usuario' ? 'Usuario' : 'Asistente';
    $promptConversacion .= "{$rol}: " . trim($turno['texto']) . "\n";
}

$promptFinal = "Sistema: {$systemPrompt}\n\n{$promptConversacion}Usuario: {$pregunta}\nAsistente:";

// ── Llamada a Ollama con stream:true ─────────────────────────
$payload = [
    "model"  => "asistente-fichajes",
    "prompt" => $promptFinal,
    "stream" => true,
    "options" => [
        "temperature"   => 0.3,   // Más bajo = respuestas más precisas y menos creativas
        "num_predict"   => 300,   // Límite de tokens para forzar brevedad
        "top_p"         => 0.85,  // Reduce variabilidad manteniendo coherencia
        "repeat_penalty"=> 1.1,   // Evita que el modelo repita frases
        "stop"          => ["Usuario:", "Sistema:"]  // Detiene si el modelo empieza a simular diálogo
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

// Callback: recibe cada chunk NDJSON de Ollama y lo reenvía al cliente
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use (&$respuestaCompleta) {
    $lineas = explode("\n", $data);
    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if ($linea === '') continue;

        $json = json_decode($linea, true);
        if (!$json) continue;

        // Token normal
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

$ok   = curl_exec($ch);
$err  = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err || $code !== 200) {
    echo "data: " . json_encode(['error' => "Error conectando con el asistente (HTTP {$code})"]) . "\n\n";
    flush();
}