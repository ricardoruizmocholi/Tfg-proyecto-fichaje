<?php
/*
 * panel.php — Punto de entrada principal de la aplicación
 * Gestiona la sesión, carga el CSS de empresa, renderiza el layout (header + sidebar + main)
 * y actúa como router: incluye la sección correcta de secciones/ según los parámetros
 * GET ?seccion= y ?vista=. También aloja el chatbot IA flotante (SSE via ia_handler.php).
 * Rutas protegidas: redirige a login.php si no hay sesión activa.
 */

session_start();
require_once 'config.php';

if(!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario  = $_SESSION['usuario'];
$empresa  = $_SESSION['empresa_activa'];
$idUsuario = $usuario['id'];
$esAdmin  = $_SESSION['es_admin'] ?? null;

if(!$empresa) {
    die("Error no he encontrado empresa vinculada al usuario.");
}

$seccion = $_GET['seccion'] ?? 'fichaje';
$vista   = $_GET['vista']   ?? 'inicio';

$empresa  = $_SESSION['empresa_activa']; 
$cssFile  = "css/empresa_$empresa.css";
if (!file_exists($cssFile)) {
    $cssFile = "css/default.css";
}

$nombreEmpresa = "Empresa desconocida";
foreach ($_SESSION['usuario']['empresas'] as $emp) {
    if ($emp['id_empresa'] == $empresa) {
        $nombreEmpresa = $emp['nombre'];
        break;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($nombreEmpresa) ?></title>
    <link rel="stylesheet" href="<?= $cssFile ?>">
    <link rel="stylesheet" href="css/panel.css">
    <link rel="stylesheet" href="css/mobile_menu.css">
    <link rel="stylesheet" href="css/modales_global.css">
    <link rel="stylesheet" href="css/tipografia.css">
    
</head>
<body>

<!-- Overlay oscuro (click para cerrar menú en mobile) -->
<div id="menu-overlay" onclick="closeMobileMenu()" aria-hidden="true"></div>

<header>
    <!-- Botón hamburguesa — solo visible en mobile (CSS lo controla) -->
    <button id="hamburger-btn"
           
            aria-label="Abrir menú"
            aria-expanded="false"
            aria-controls="main-sidebar">
        <span class="hbg-bar"></span>
        <span class="hbg-bar"></span>
        <span class="hbg-bar"></span>
    </button>

    <div class="logo"><?= htmlspecialchars($nombreEmpresa) ?></div>

    <div>
        <span style="color:white; margin-right:15px;">
            <?= $esAdmin ? ' Admin' : ' Empleado' ?> - 
            <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']) ?>
        </span>
        <div class="notification-container" style="display: inline-block; position: relative; margin-right: 15px;">
            <?php
            $stmtNotif = $pdo->prepare("SELECT COUNT(*) FROM NOTIFICACIONES WHERE id_usuario = ? AND leida = 0");
            $stmtNotif->execute([$idUsuario]);
            $countNotif = $stmtNotif->fetchColumn();
            $badge = ($countNotif > 9) ? '9+' : $countNotif;
            ?>
            <a href="panel.php?seccion=notificaciones" style="text-decoration: none; font-size: 20px;">
                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon-bell">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <?php if ($countNotif > 0): ?>
                    <span class="notification-badge"><?= $badge ?></span>
                <?php endif; ?>
            </a>
        </div>
        <a href="logout.php" style="color:white; margin-left:20px;">Cerrar sesión</a>
    </div>
</header>

    <?php if($esAdmin): ?>
        <div class="sidebar" id="main-sidebar" role="navigation" aria-label="Menú principal">
            <!-- Cabecera del menú en mobile (nombre empresa) -->
            <div class="sidebar-mobile-header">
                <?= htmlspecialchars($nombreEmpresa) ?>
            </div>

            <div class="sidebar_container">
                <h3 class="sidebar-title">Administrador</h3>

                <a class="submenu-btn">
                     Fichaje
                    <svg class="arrow-icon" width="16" height="16" viewBox="0 0 20 20">
                        <path d="M 10 13.7 a 0.897 0.897 0 0 1 -0.636 -0.264 l -4.6 -4.6 a 0.9 0.9 0 1 1 1.272 -1.273 L 10 11.526 l 3.964 -3.963 a 0.9 0.9 0 0 1 1.272 1.273 l -4.6 4.6 A 0.897 0.897 0 0 1 10 13.7 Z"></path>
                    </svg>
                </a>
                <div class="submenu-content">
                    <a href="panel.php?seccion=fichaje&vista=inicio" onclick="closeMobileMenu()">Fichar</a>
                    <a href="panel.php?seccion=fichaje&vista=ver" onclick="closeMobileMenu()">Ver fichajes</a>
                    <a href="panel.php?seccion=fichaje&vista=modificar" onclick="closeMobileMenu()">Modificar fichajes</a>
                    <a href="panel.php?seccion=fichaje&vista=config_ip" onclick="closeMobileMenu()"> Restricción por IP</a>
                </div>

                <a class="submenu-btn">
                     Horario
                    <svg class="arrow-icon" width="16" height="16" viewBox="0 0 20 20">
                        <path d="M 10 13.7 a 0.897 0.897 0 0 1 -0.636 -0.264 l -4.6 -4.6 a 0.9 0.9 0 1 1 1.272 -1.273 L 10 11.526 l 3.964 -3.963 a 0.9 0.9 0 0 1 1.272 1.273 l -4.6 4.6 A 0.897 0.897 0 0 1 10 13.7 Z"></path>
                    </svg>
                </a>
                <div class="submenu-content">
                    <a href="panel.php?seccion=horario&vista=peticiones" onclick="closeMobileMenu()">Peticiones</a>
                    <a href="panel.php?seccion=horario&vista=cuadrantes" onclick="closeMobileMenu()">Cuadrantes</a>
                    <a href="panel.php?seccion=horario&vista=vacaciones" onclick="closeMobileMenu()">Vacaciones</a>
                    <a href="panel.php?seccion=horario&vista=plantillas" onclick="closeMobileMenu()">Plantillas de Jornada</a>
                    <a href="panel.php?seccion=horario&vista=calendario" onclick="closeMobileMenu()">Calendario empleados</a>
                </div>

                <a class="submenu-btn">
                     Reportes
                    <svg class="arrow-icon" width="16" height="16" viewBox="0 0 20 20">
                        <path d="M 10 13.7 a 0.897 0.897 0 0 1 -0.636 -0.264 l -4.6 -4.6 a 0.9 0.9 0 1 1 1.272 -1.273 L 10 11.526 l 3.964 -3.963 a 0.9 0.9 0 0 1 1.272 1.273 l -4.6 4.6 A 0.897 0.897 0 0 1 10 13.7 Z"></path>
                    </svg>
                </a>
                <div class="submenu-content">
                    <a href="panel.php?seccion=reportes&vista=generar" onclick="closeMobileMenu()">Generar reportes</a>
                    <a href="panel.php?seccion=reportes&vista=historial" onclick="closeMobileMenu()">Historial reportes</a>
                </div>

                <a class="submenu-btn">
                     Gestión empleados
                    <svg class="arrow-icon" width="16" height="16" viewBox="0 0 20 20">
                        <path d="M 10 13.7 a 0.897 0.897 0 0 1 -0.636 -0.264 l -4.6 -4.6 a 0.9 0.9 0 1 1 1.272 -1.273 L 10 11.526 l 3.964 -3.963 a 0.9 0.9 0 0 1 1.272 1.273 l -4.6 4.6 A 0.897 0.897 0 0 1 10 13.7 Z"></path>
                    </svg>
                </a>
                <div class="submenu-content">
                    <a href="panel.php?seccion=empleados&vista=lista" onclick="closeMobileMenu()">Ver empleados</a>
                    <a href="panel.php?seccion=empleados&vista=nuevo" onclick="closeMobileMenu()">Añadir empleados</a>
                    <a href="panel.php?seccion=empleados&vista=nominas" onclick="closeMobileMenu()">Subir nóminas</a>
                    <a href="panel.php?seccion=empleados&vista=tickets" onclick="closeMobileMenu()"> Tickets</a>
                </div>

                <a href="panel.php?seccion=perfil" onclick="closeMobileMenu()"> Perfil</a>
            </div>
        </div>
    <?php else: ?>
        <div class="sidebar" id="main-sidebar" role="navigation" aria-label="Menú principal">
            <div class="sidebar-mobile-header">
                <?= htmlspecialchars($nombreEmpresa) ?>
            </div>

            <div class="sidebar_container">              
                <h3> Empleado</h3>
                <a href="panel.php?seccion=fichaje" onclick="closeMobileMenu()"> Fichaje</a>
                <a href="panel.php?seccion=horario" onclick="closeMobileMenu()"> Horario</a>
                <a href="panel.php?seccion=documentos" onclick="closeMobileMenu()"> Mis Nóminas</a>
                <a href="panel.php?seccion=tickets" onclick="closeMobileMenu()"> Mis Tickets</a>
                <a href="panel.php?seccion=perfil" onclick="closeMobileMenu()"> Perfil</a>
            </div>
        </div>
    <?php endif; ?>

    <main>
        <?php
        if ($esAdmin) {
            if ($seccion === "fichaje") {
                if ($vista === "ver")        include "secciones/fichaje/fichaje_ver.php";
                if ($vista === "config_ip")  include "secciones/config/config_ip.php";
                if ($vista === "modificar")  include "secciones/fichaje/fichaje_modificar.php";
                if ($vista === "inicio")     include "secciones/fichaje/fichaje.php";
            }
            if ($seccion === "horario") {
                if ($vista === "peticiones") include "secciones/horario/horario.php";
                if ($vista === "cuadrantes") include "secciones/horario/horario_cuadrantes.php";
                if ($vista === "vacaciones") include "secciones/vacaciones/vacaciones_cuadrante.php";
                if ($vista === "plantillas") include "secciones/tipos-jornada/tipos_jornada_admin.php";
                if ($vista === "calendario") include "secciones/horario/horario_admin_calendario.php";
                if (!$vista)                 include "secciones/horario/horario.php";
            }
            if ($seccion === "reportes") {
                if ($vista === "generar")    include "secciones/reportes/reportes.php";
                if ($vista === "historial")  include "secciones/reportes/reportes_historial.php";
                if (!$vista)                 include "secciones/reportes/reportes.php";
            }
            if ($seccion === "empleados") {
                if ($vista === "lista")    include "secciones/empleados/empleados_lista.php";
                if ($vista === "nuevo")    include "secciones/empleados/empleados_nuevo.php";
                if ($vista === "nominas")  include "secciones/nominas/nominas_admin.php";
                if ($vista === "tickets")  include "secciones/tickets/tickets_admin.php";
            }
        }

        if (!$esAdmin) {
            if ($seccion === "fichaje") include "secciones/fichaje/fichaje.php";
            if ($seccion === "horario") include "secciones/horario/horario.php";
        }

        if ($seccion === "documentos")     include "secciones/nominas/mis_nominas.php";
        if ($seccion === "perfil")         include "secciones/perfil/perfil.php";
        if ($seccion === "notificaciones") include "secciones/notificaciones/notificaciones.php";
        if ($seccion === "tickets")        include "secciones/tickets/tickets.php";
        ?>
    </main>

    <!-- =============================================
         CHATBOT: Botón flotante
    ============================================= -->
    <button class="chat-fab" id="chatFab" title="Asistente IA" aria-label="Abrir asistente IA">
        💬
    </button>

    <div class="chat-modal" id="chatModal" role="dialog" aria-label="Asistente IA">
        <div class="chat-header">
            <div class="chat-header-title">
                <span class="dot"></span>
                Asistente IA
            </div>
            <button class="chat-close-btn" id="chatCloseBtn" aria-label="Cerrar chat">×</button>
        </div>

        <div class="chat-messages" id="chatMessages">
            <div class="chat-bubble bot">
                👋 Hola, soy tu asistente. Puedo ayudarte con dudas sobre fichajes, horarios, reportes y más. ¿En qué puedo ayudarte?
            </div>
        </div>

        <button class="chat-clear-btn" id="chatClearBtn">Limpiar conversación</button>

        <div class="chat-input-area">
            <textarea
                class="chat-input"
                id="chatInput"
                rows="1"
                placeholder="Escribe tu pregunta..."
                aria-label="Mensaje para el asistente"
            ></textarea>
            <button class="chat-send-btn" id="chatSendBtn" aria-label="Enviar mensaje" title="Enviar">
                ➤
            </button>
        </div>
    </div>

    <!-- =============================================
         JAVASCRIPT
    ============================================= -->
   <script>
// ─────────────────────────────────────────────────────────
// 1. GESTIÓN DEL MENÚ (REESCRITURA LIMPIA)
// ─────────────────────────────────────────────────────────

function closeMobileMenu() {
    document.body.classList.remove('menu-open');
    const btn = document.getElementById('hamburger-btn');
    if (btn) btn.setAttribute('aria-expanded', 'false');
}

function toggleMobileMenu() {
    const isOpen = document.body.classList.toggle('menu-open');
    const btn = document.getElementById('hamburger-btn');
    if (btn) btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

// Escuchador exclusivo para el botón hamburguesa
const btnHamburguesa = document.getElementById('hamburger-btn');
if (btnHamburguesa) {
    btnHamburguesa.addEventListener('click', (e) => {
        e.stopPropagation(); // Previene que otros clics interfieran
        toggleMobileMenu();
    });
}

// Cierra el menú al pulsar la tecla Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMobileMenu();
});

// Cierra el menú si se hace grande la pantalla (vuelve a desktop)
window.addEventListener('resize', () => {
    if (window.innerWidth > 768) closeMobileMenu();
});
// ─────────────────────────────────────────────────────────
// 2. SUBMENÚS ACORDEÓN (ADMIN)
// ─────────────────────────────────────────────────────────
document.querySelectorAll(".submenu-btn").forEach(btn => {
    btn.addEventListener("click", (e) => {
        const content = btn.nextElementSibling;
        const isOpening = !content.classList.contains('open');
        
        // Cerrar los demás submenús
        document.querySelectorAll(".submenu-content").forEach(other => {
            if (other !== content) {
                other.classList.remove("open");
                other.previousElementSibling?.classList.remove("active");
            }
        });

        btn.classList.toggle("active", isOpening);
        content.classList.toggle("open", isOpening);
    });
});


// ─────────────────────────────────────────────────────────
// 3. CHATBOT (STREAMING DIRECTO A OLLAMA)
// ─────────────────────────────────────────────────────────
const chatFab      = document.getElementById('chatFab');
const chatModal    = document.getElementById('chatModal');
const chatCloseBtn = document.getElementById('chatCloseBtn');
const chatInput    = document.getElementById('chatInput');
const chatSendBtn  = document.getElementById('chatSendBtn');
const chatMessages = document.getElementById('chatMessages');
const chatClearBtn = document.getElementById('chatClearBtn');

const ctxSeccion  = <?= json_encode($seccion) ?>;
const ctxVista    = <?= json_encode($vista) ?>;
const ctxEsAdmin  = <?= json_encode((bool)$esAdmin) ?>;
const STORAGE_KEY = 'chat_historial_<?= $idUsuario ?>';
const OLLAMA_URL  = 'https://ia.ricardorm.es/api/generate';

// ── Mapa de contextos por sección/vista (portado desde ia_handler.php) ────
const contextoSecciones = {
    fichaje: {
        inicio:    'El usuario está en la pantalla principal de fichaje. Desde aquí puede pulsar los botones de Entrada, Pausa, Reanudar y Salida para registrar su jornada. Si tiene jornada partida verá también los botones de Entrada/Salida del 2.º tramo.',
        ver:       'El usuario está en "Ver fichajes". Puede consultar el historial de sus fichajes filtrando por día, mes o rango de fechas y por empleado. Los registros muestran hora de entrada, pausa, reanudación, salida y total de horas.',
        modificar: 'El administrador está en "Modificar fichajes". Puede editar directamente las horas de cualquier fichaje existente, añadir nuevos registros olvidados (incluyendo el 2.º tramo de jornada partida) y eliminar fichajes incorrectos.',
        config_ip: 'El administrador está en "Restricción por IP". Puede activar o desactivar la restricción de fichaje por IP, añadir IPs o prefijos de subred autorizados (hasta 50 por empresa) con etiqueta descriptiva, y eliminar IPs de la lista blanca.',
    },
    horario: {
        peticiones: 'El administrador está en "Peticiones de horario". Ve todas las solicitudes de los empleados (PENDIENTE, APROBADO, RECHAZADO) con filtros por estado, tipo y empleado. Puede aprobar directamente, rechazar introduciendo un motivo obligatorio, o abrir el modal de detalle para ver el calendario día a día de la solicitud antes de decidir.',
        cuadrantes: 'El usuario está en el "Cuadrante de horarios". Muestra una tabla con todos los empleados en filas y los días del período en columnas. El administrador puede editar celdas individuales o usar "Añadir en bloque" para asignar horarios a varios empleados a la vez. Permite seleccionar períodos de 1 semana, 2 semanas, 3 semanas o mes completo.',
        vacaciones: 'El administrador está en el "Cuadrante de vacaciones". Muestra un calendario mensual con las vacaciones aprobadas (verde) y pendientes (amarillo) de cada empleado. Las celdas con alerta indican que ese día supera el umbral del 33% de la plantilla de vacaciones simultáneas. Muestra estadísticas de empleados sin días disponibles y días con riesgo de cobertura.',
        plantillas: 'El administrador está en "Plantillas de Jornada". Puede crear tipos de jornada personalizados para su empresa (Turno Noche, Guardia, Teletrabajo, etc.) con nombre, color hexadecimal, horas predeterminadas de inicio/fin e indicador de si es jornada productiva. Las plantillas activas aparecen en todos los selectores de tipo de jornada de la empresa.',
        calendario: 'El administrador está en "Calendario empleados". Puede ver y editar directamente el calendario mensual de cualquier empleado seleccionado, sin necesidad de solicitudes ni validaciones. Los cambios se guardan inmediatamente en la base de datos.',
        '*':        'El empleado está en su calendario de horario mensual. Puede añadir eventos de jornada haciendo clic en los días, usar "Añadir múltiples días" para rangos, copiar el mes anterior y enviar todos los borradores a validar con el botón "Enviar a validar". Los colores: azul=trabajo, verde=vacaciones, naranja=médico, morado=libre, rojo=festivo, amarillo punteado=pendiente.',
    },
    reportes: {
        generar:   'El administrador está en "Generar reporte". Selecciona empleado, mes/año y tipo (mensual o anual). Puede previsualizar los datos en pantalla antes de generar el PDF oficial. También puede exportar a Excel con el botón "Excel Export". El PDF sigue el formato del "Listado Resumen mensual del registro de jornada" exigido por el RDL 8/2019.',
        historial: 'El administrador está en "Historial de reportes". Ve todos los reportes PDF generados con filtros por empleado, tipo, mes y año. Puede abrir el PDF en el navegador, descargarlo o eliminarlo. Si el archivo físico fue borrado del servidor, aparece el aviso "No disponible".',
    },
    empleados: {
        lista:   'El administrador está en la lista de empleados. Ve una tabla con todos los empleados de la empresa (foto, nombre, email, tipo de contrato, vacaciones, rol y estado). Puede editar la ficha de cada empleado (datos personales, contrato, vacaciones, contraseña, rol admin), activar/desactivar empleados y eliminarlos.',
        nuevo:   'El administrador está añadiendo un nuevo empleado. Debe rellenar: datos personales (nombre, apellidos, NIF, n.º afiliación, email), contraseña inicial, tipo de jornada (completa/parcial), horas contratadas al mes, días de vacaciones anuales, límite de horas extra y si será administrador.',
        nominas: 'El administrador está en "Nóminas y documentos". Puede subir archivos PDF (máx. 10 MB) asignándolos a un empleado con título, mes y año. Al subir, el empleado recibe una notificación automática. También puede ver el historial de documentos subidos y eliminar los que ya no sean necesarios.',
        tickets: 'El administrador está en la gestión de tickets de empleados. Ve todos los tickets con filtros por estado (PENDIENTE, EN PROCESO, RESUELTA, CERRADA), categoría y empleado. Al abrir un ticket accede al hilo de conversación donde puede responder, cambiar el estado o marcarlo como resuelto/cerrado.',
    },
    perfil:         { '*': 'El usuario está en su perfil. Puede cambiar su foto (JPG/PNG/GIF/WEBP, máx. 5 MB), cambiar su contraseña (introduciendo la actual y la nueva dos veces) y ver sus datos personales y de contrato (estos últimos son de solo lectura, solo el admin puede modificarlos). También ve el resumen laboral con vacaciones disponibles, horas del mes y horas extra anuales.' },
    notificaciones: { '*': 'El usuario está en su panel de notificaciones. Ve el historial completo de avisos: aprobaciones/rechazos de horario, cambios de fichaje, respuestas a tickets y nuevas nóminas. Puede eliminar notificaciones individualmente o limpiar todas con "Limpiar todo". Las notificaciones con más de 30 días se eliminan automáticamente.' },
    tickets:        { '*': 'El empleado está en su sección de tickets. Puede crear un ticket nuevo (asunto, descripción, categoría: HORARIO/NOMINA/VACACIONES/FICHAJE/OTRO, prioridad: BAJA/MEDIA/ALTA) y ver el hilo de conversación de cada uno. Los estados posibles son PENDIENTE, EN PROCESO, RESUELTA y CERRADA. Al responder a un ticket RESUELTA, el sistema lo reabre automáticamente.' },
    documentos:     { '*': 'El empleado está en "Mis Nóminas". Ve todos los documentos PDF que la empresa ha subido para él, organizados por año. Puede visualizarlos en el navegador o descargarlos. Los documentos nuevos generan una notificación automática.' },
};

// ── Construye el prompt final igual que ia_handler.php ────────────────────
function construirPrompt(pregunta, historial) {
    const rol = ctxEsAdmin ? 'administrador' : 'empleado';

    const mapa = contextoSecciones[ctxSeccion];
    const ctx  = mapa ? (mapa[ctxVista] || mapa['*'] || Object.values(mapa)[0] || '') : '';

    const system = [
        `Eres el asistente virtual de FesolCheck, un sistema de control de jornada y fichajes.`,
        `El usuario actual es un ${rol}.`,
        ``,
        `## TU FUNCIÓN`,
        `Ayudar a los usuarios con dudas sobre el uso de FesolCheck: cómo fichar, gestionar horarios, solicitar vacaciones, revisar reportes, usar los tickets, etc.`,
        ``,
        `## REGLAS DE RESPUESTA — DEBES SEGUIRLAS SIEMPRE`,
        `1. BREVEDAD: Responde en 3-5 frases como máximo. Si la respuesta requiere pasos, usa una lista numerada corta (máximo 5 puntos). Nunca te extiendas innecesariamente.`,
        `2. CERTEZA: Solo afirma lo que sabes con seguridad sobre FesolCheck. Si no conoces la respuesta exacta, di literalmente: "No tengo información exacta sobre eso. Consulta con el administrador del sistema."`,
        `3. NO INVENTES: Prohibido inventar rutas, botones, funcionalidades o configuraciones que no existan en el sistema. Si no estás seguro, no lo digas.`,
        `4. IDIOMA: Responde siempre en español.`,
        `5. TEMA: Solo responde preguntas sobre FesolCheck o sobre legislación laboral española relacionada con el registro de jornada. Si la pregunta es ajena a estos temas, responde: "Solo puedo ayudarte con dudas sobre FesolCheck o la normativa de registro horario."`,
        ``,
        `## DATOS SENSIBLES — NUNCA REVELES`,
        `- Credenciales de acceso (contraseñas, hashes, tokens).`,
        `- Datos de configuración del servidor (IPs internas, rutas del servidor, credenciales de base de datos).`,
        `- Información personal de otros empleados (salarios, NIF, datos médicos, fichajes de otras personas).`,
        `- Estructura interna de la base de datos o código fuente.`,
        `- Datos de empresa que no correspondan al usuario que pregunta.`,
        `Si se pregunta por cualquiera de estos datos, responde: "No puedo proporcionar esa información por motivos de seguridad y privacidad."`,
        ``,
        `## LO QUE PUEDES RESPONDER`,
        `- Cómo usar cada sección del panel (fichaje, horario, reportes, empleados, perfil, tickets, nóminas).`,
        `- Flujo de solicitudes de horario y vacaciones (crear borrador → enviar → pendiente → aprobado/rechazado).`,
        `- Tipos de jornada: TRABAJO, PARTIDA_M (mañana), PARTIDA_T (tarde), VACACIONES, MEDICO, LIBRE, FESTIVO, y plantillas personalizadas de empresa.`,
        `- Colores del calendario: azul=trabajo continuo, azul índigo=partida mañana, rosa=partida tarde, verde=vacaciones, naranja=médico, morado=libre, rojo=festivo, amarillo punteado=pendiente, azul discontinuo=borrador temporal.`,
        `- Restricción de fichaje por IP: cómo activarla, añadir IPs/prefijos de subred, eliminarlas.`,
        `- Generación de reportes PDF (formato oficial RDL 8/2019) y exportación a Excel.`,
        `- Gestión de empleados, contratos, vacaciones acumuladas y horas extra (límite legal: 80h/año).`,
        `- Sistema de tickets: categorías, estados, hilo de conversación.`,
        `- Notificaciones automáticas del sistema.`,
        `- Normativa española de registro horario (RDL 8/2019, ET art. 34-35, LISOS).`,
        ``,
        `## FORMATO DE RESPUESTA`,
        `- Para preguntas simples: 1-2 frases directas.`,
        `- Para procedimientos: lista numerada, máximo 5 pasos.`,
        `- Para aclaraciones de conceptos: máximo 3 frases.`,
        `- Nunca uses encabezados Markdown (##, **negrita**) en la respuesta al usuario, solo texto plano y listas simples con números o guiones.`,
        ...(ctx ? [
            ``,
            `## CONTEXTO ACTUAL`,
            `El usuario se encuentra ahora en: ${ctx}`,
            `Adapta tu respuesta a lo que el usuario puede hacer en esta pantalla.`,
        ] : []),
    ].join('\n');

    // Últimos 8 turnos (igual que PHP: array_slice($historial, -8))
    const recientes = historial.slice(-8);
    const conversacion = recientes
        .map(t => `${t.rol === 'usuario' ? 'Usuario' : 'Asistente'}: ${t.texto.trim()}`)
        .join('\n');

    return `Sistema: ${system}\n\n${conversacion ? conversacion + '\n' : ''}Usuario: ${pregunta}\nAsistente:`;
}

let historialChat = [];

function cargarHistorial() {
    try {
        const guardado = sessionStorage.getItem(STORAGE_KEY);
        if (!guardado) return;
        historialChat = JSON.parse(guardado);
        chatMessages.innerHTML = '';
        historialChat.forEach(turno => {
            const div = document.createElement('div');
            div.className = 'chat-bubble ' + (turno.rol === 'usuario' ? 'user' : 'bot');
            div.textContent = turno.texto;
            chatMessages.appendChild(div);
        });
        scrollAbajo();
    } catch (e) { sessionStorage.removeItem(STORAGE_KEY); }
}

function guardarHistorial() {
    try { sessionStorage.setItem(STORAGE_KEY, JSON.stringify(historialChat)); } catch (e) {}
}

chatFab.addEventListener('click', () => {
    chatModal.classList.toggle('open');
    if (chatModal.classList.contains('open')) chatInput.focus();
});

chatCloseBtn.addEventListener('click', () => chatModal.classList.remove('open'));

chatClearBtn.addEventListener('click', () => {
    historialChat = [];
    sessionStorage.removeItem(STORAGE_KEY);
    chatMessages.innerHTML = `<div class="chat-bubble bot">👋 Hola, ¿en qué puedo ayudarte?</div>`;
});

chatInput.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); enviarMensaje(); }
});

chatSendBtn.addEventListener('click', enviarMensaje);

function scrollAbajo() { chatMessages.scrollTop = chatMessages.scrollHeight; }

async function enviarMensaje() {
    const texto = chatInput.value.trim();
    if (!texto || chatSendBtn.disabled) return;

    const burbujaUser = document.createElement('div');
    burbujaUser.className = 'chat-bubble user';
    burbujaUser.textContent = texto;
    chatMessages.appendChild(burbujaUser);

    chatInput.value = '';
    historialChat.push({ rol: 'usuario', texto });
    guardarHistorial();

    chatSendBtn.disabled = true;
    const burbujaBot = document.createElement('div');
    burbujaBot.className = 'chat-bubble bot streaming';
    chatMessages.appendChild(burbujaBot);
    scrollAbajo();

    try {
        const response = await fetch(OLLAMA_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                model:   'asistente-fichajes',
                prompt:  construirPrompt(texto, historialChat),
                stream:  true,
                options: {
                    temperature:    0.3,
                    num_predict:    300,
                    top_p:          0.85,
                    repeat_penalty: 1.1,
                    stop:           ['Usuario:', 'Sistema:']
                }
            })
        });

        const reader  = response.body.getReader();
        const decoder = new TextDecoder();
        let respuestaCompleta = '';
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            // Acumular en buffer para manejar líneas partidas entre chunks
            buffer += decoder.decode(value, { stream: true });
            const lineas = buffer.split('\n');
            buffer = lineas.pop(); // la última puede estar incompleta

            for (const linea of lineas) {
                const raw = linea.trim();
                if (!raw) continue;
                try {
                    const json = JSON.parse(raw);
                    if (json.response) {
                        respuestaCompleta += json.response;
                        burbujaBot.textContent = respuestaCompleta;
                        scrollAbajo();
                    }
                    // json.done === true → fin natural del stream
                } catch (e) { /* línea incompleta o no JSON */ }
            }
        }

        historialChat.push({ rol: 'asistente', texto: respuestaCompleta });
        guardarHistorial();
    } catch (err) {
        burbujaBot.textContent = '⚠️ Error de conexión con el asistente.';
    } finally {
        burbujaBot.classList.remove('streaming');
        chatSendBtn.disabled = false;
    }
}

// Cargar al inicio
document.addEventListener('DOMContentLoaded', cargarHistorial);

</script>

    <script src="secciones/tipos-jornada/tipos_jornada_helper.js"></script>
</body>
</html>