<?php 

session_start();
require_once 'config.php';

if(!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario  = $_SESSION['usuario'];
$empresa  = $_SESSION['empresa_activa'] ?? null;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($nombreEmpresa) ?></title>
    <link rel="stylesheet" href="<?= $cssFile ?>">
    <link rel="stylesheet" href="css/panel.css">
    
</head>
<body>
    
    <header>
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
                    🔔
                    <?php if ($countNotif > 0): ?>
                        <span class="notification-badge"><?= $badge ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <a href="logout.php" style="color:white; margin-left:20px;">Cerrar sesión</a>
        </div>
    </header>

    <?php if($esAdmin): ?>
        <div class="sidebar">
            <div class="sidebar_container">
                <h3 class="sidebar-title">Administrador</h3>
    
                <a class="submenu-btn">
                    Fichaje
                    <svg class="arrow-icon" width="16" height="16" viewBox="0 0 20 20">
                        <path d="M 10 13.7 a 0.897 0.897 0 0 1 -0.636 -0.264 l -4.6 -4.6 a 0.9 0.9 0 1 1 1.272 -1.273 L 10 11.526 l 3.964 -3.963 a 0.9 0.9 0 0 1 1.272 1.273 l -4.6 4.6 A 0.897 0.897 0 0 1 10 13.7 Z"></path>
                    </svg>
                </a>
                <div class="submenu-content">
                    <a href="panel.php?seccion=fichaje&vista=inicio">Fichar</a>
                    <a href="panel.php?seccion=fichaje&vista=ver">Ver fichajes</a>
                    <a href="panel.php?seccion=fichaje&vista=modificar">Modificar fichajes</a>
                    <a href="panel.php?seccion=fichaje&vista=config_ip"> Restricción por IP</a>
                </div>
    
                <a class="submenu-btn">
                    Horario
                    <svg class="arrow-icon" width="16" height="16" viewBox="0 0 20 20">
                        <path d="M 10 13.7 a 0.897 0.897 0 0 1 -0.636 -0.264 l -4.6 -4.6 a 0.9 0.9 0 1 1 1.272 -1.273 L 10 11.526 l 3.964 -3.963 a 0.9 0.9 0 0 1 1.272 1.273 l -4.6 4.6 A 0.897 0.897 0 0 1 10 13.7 Z"></path>
                    </svg>
                </a>
                <div class="submenu-content">
                    <a href="panel.php?seccion=horario&vista=peticiones">Peticiones</a>
                    <a href="panel.php?seccion=horario&vista=cuadrantes">Cuadrantes</a>
                    <a href="panel.php?seccion=horario&vista=vacaciones">Vacaciones</a>
                      <a href="panel.php?seccion=horario&vista=plantillas"> Plantillas de Jornada</a>
                </div>
    
                <a class="submenu-btn">
                    Reportes
                    <svg class="arrow-icon" width="16" height="16" viewBox="0 0 20 20">
                        <path d="M 10 13.7 a 0.897 0.897 0 0 1 -0.636 -0.264 l -4.6 -4.6 a 0.9 0.9 0 1 1 1.272 -1.273 L 10 11.526 l 3.964 -3.963 a 0.9 0.9 0 0 1 1.272 1.273 l -4.6 4.6 A 0.897 0.897 0 0 1 10 13.7 Z"></path>
                    </svg>
                </a>
                <div class="submenu-content">
                    <a href="panel.php?seccion=reportes&vista=generar">Generar reportes</a>
                    <a href="panel.php?seccion=reportes&vista=historial">Historial reportes</a>
                </div>
    
                <a class="submenu-btn">
                    Gestión empleados
                    <svg class="arrow-icon" width="16" height="16" viewBox="0 0 20 20">
                        <path d="M 10 13.7 a 0.897 0.897 0 0 1 -0.636 -0.264 l -4.6 -4.6 a 0.9 0.9 0 1 1 1.272 -1.273 L 10 11.526 l 3.964 -3.963 a 0.9 0.9 0 0 1 1.272 1.273 l -4.6 4.6 A 0.897 0.897 0 0 1 10 13.7 Z"></path>
                    </svg>
                </a>
                <div class="submenu-content">
                    <a href="panel.php?seccion=empleados&vista=lista">Ver empleados</a>
                    <a href="panel.php?seccion=empleados&vista=nuevo">Añadir empleados</a>
                    <a href="panel.php?seccion=empleados&vista=nominas">Subir nominas</a>
                    <a href="panel.php?seccion=empleados&vista=tickets"> Tickets</a>
                </div>
    
                <a href="panel.php?seccion=perfil">Perfil</a>
            </div>
        </div>
    <?php else: ?>
        <div class="sidebar">
            <div class="sidebar_container">              
                <h3>Empleado</h3>
                <a href="panel.php?seccion=fichaje">Fichaje</a>
                <a href="panel.php?seccion=horario">Horario</a>
                <a href="panel.php?seccion=documentos">Mis Nominas</a>
                <a href="panel.php?seccion=tickets"> Mis Tickets</a>
                <a href="panel.php?seccion=perfil">Perfil</a>
            </div>
        </div>
    <?php endif; ?>

    <main>
        <?php
        if ($esAdmin) {
            if ($seccion === "fichaje") {
                if ($vista === "ver")      include "secciones/fichaje_ver.php";
                if ($vista === "config_ip")  include "secciones/config_ip.php";
                if ($vista === "modificar") include "secciones/fichaje_modificar.php";
                if ($vista === "inicio")   include "secciones/fichaje.php";
            }
            if ($seccion === "horario") {
                if ($vista === "peticiones") include "secciones/horario.php";
                if ($vista === "cuadrantes") include "secciones/horario_cuadrantes.php";
                if ($vista === "vacaciones")    include "secciones/vacaciones_cuadrante.php";
                if ($vista === "plantillas") include "secciones/tipos_jornada_admin.php";
                if (!$vista)                 include "secciones/horario.php";
            }
            if ($seccion === "reportes") {
                if ($vista === "generar")   include "secciones/reportes.php";
                if ($vista === "historial") include "secciones/reportes_historial.php";
                if (!$vista)                include "secciones/reportes.php";
            }
            if ($seccion === "empleados") {
                if ($vista === "lista") include "secciones/empleados_lista.php";
                if ($vista === "nuevo") include "secciones/empleados_nuevo.php";
                if ($vista === "nominas") include "secciones/nominas_admin.php";
                if ($vista === "tickets")  include "secciones/tickets_admin.php";
            }
        }

        if (!$esAdmin) {
            if ($seccion === "fichaje") include "secciones/fichaje.php";
            if ($seccion === "horario") include "secciones/horario.php";
        }

        if ($seccion === "documentos")     include "secciones/mis_nominas.php";
        if ($seccion === "perfil")         include "secciones/perfil.php";
        if ($seccion === "notificaciones") include "secciones/notificaciones.php";
        if ($seccion === "tickets")       include "secciones/tickets.php";
        ?>
    </main>

    <!-- =============================================
         CHATBOT: Botón flotante
    ============================================= -->
    <button class="chat-fab" id="chatFab" title="Asistente IA" aria-label="Abrir asistente IA">
        
    </button>

    <!-- Modal del chatbot -->
    <div class="chat-modal" id="chatModal" role="dialog" aria-label="Asistente IA">
        <div class="chat-header">
            <div class="chat-header-title">
                <span class="dot"></span>
                Asistente IA
            </div>
            <button class="chat-close-btn" id="chatCloseBtn" aria-label="Cerrar chat">×</button>
        </div>

        <div class="chat-messages" id="chatMessages">
            <!-- Mensaje de bienvenida -->
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
        // ---- Submenús acordeón ----
        document.querySelectorAll(".submenu-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                const content = btn.nextElementSibling;
                btn.classList.toggle("active");
                content.classList.toggle("open");
                document.querySelectorAll(".submenu-content").forEach(other => {
                    if (other !== content) {
                        other.classList.remove("open");
                        other.previousElementSibling.classList.remove("active");
                    }
                });
            });
        });

        // ---- Notificaciones ----
        function toggleNotificaciones() {
            const dropdown = document.getElementById('notif-dropdown');
            const isVisible = dropdown.style.display === 'block';
            dropdown.style.display = isVisible ? 'none' : 'block';
            if (!isVisible) {
                const list = document.getElementById('notif-list');
                list.innerHTML = '<div style="padding:15px; text-align:center;">Cargando...</div>';
                fetch('secciones/notificaciones_obtener.php')
                    .then(res => res.json())
                    .then(data => {
                        list.innerHTML = '';
                        if (data.length === 0) {
                            list.innerHTML = '<div class="notif-item">No tienes notificaciones pendientes</div>';
                        } else {
                            data.forEach(n => {
                                const div = document.createElement('div');
                                div.className = `notif-item ${n.leida == 0 ? 'unread' : ''}`;
                                const fecha = new Date(n.fecha_creacion).toLocaleString();
                                div.innerHTML = `
                                    <div style="margin-bottom:4px;">${n.mensaje}</div>
                                    <small style="color:#888;font-size:11px;">${fecha}</small>
                                `;
                                list.appendChild(div);
                            });
                            const footer = document.createElement('div');
                            footer.style = "padding:10px;text-align:center;border-top:1px solid #eee;background:#f9f9f9;border-radius:0 0 8px 8px;";
                            footer.innerHTML = `<a href="panel.php?seccion=notificaciones" style="text-decoration:none;color:#007bff;font-weight:bold;font-size:12px;">VER TODAS LAS NOTIFICACIONES</a>`;
                            list.appendChild(footer);
                        }
                        const badge = document.querySelector('.notification-badge');
                        if (badge) badge.style.display = 'none';
                    });
            }
        }

        // ---- CHATBOT ----
        const chatFab     = document.getElementById('chatFab');
        const chatModal   = document.getElementById('chatModal');
        const chatCloseBtn = document.getElementById('chatCloseBtn');
        const chatInput   = document.getElementById('chatInput');
        const chatSendBtn = document.getElementById('chatSendBtn');
        const chatMessages = document.getElementById('chatMessages');
        const chatClearBtn = document.getElementById('chatClearBtn');

        // Contexto de la página actual (inyectado desde PHP)
        const ctxSeccion = <?= json_encode($seccion) ?>;
        const ctxVista   = <?= json_encode($vista) ?>;
        const ctxEsAdmin = <?= json_encode((bool)$esAdmin) ?>;

        // Historial de conversación en memoria
        let historialChat = [];

        // Abrir/cerrar modal
        chatFab.addEventListener('click', () => {
            chatModal.classList.toggle('open');
            if (chatModal.classList.contains('open')) {
                chatInput.focus();
            }
        });
        chatCloseBtn.addEventListener('click', () => chatModal.classList.remove('open'));

        // Limpiar conversación
        chatClearBtn.addEventListener('click', () => {
            historialChat = [];
            chatMessages.innerHTML = `
                <div class="chat-bubble bot">
                    👋 Hola, soy tu asistente. Puedo ayudarte con dudas sobre fichajes, horarios, reportes y más. ¿En qué puedo ayudarte?
                </div>`;
        });

        // Enviar con Enter (Shift+Enter = salto de línea)
        chatInput.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                enviarMensaje();
            }
        });
        chatSendBtn.addEventListener('click', enviarMensaje);

        // Auto-resize del textarea
        chatInput.addEventListener('input', () => {
            chatInput.style.height = 'auto';
            chatInput.style.height = Math.min(chatInput.scrollHeight, 100) + 'px';
        });

        function scrollAbajo() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function agregarBurbuja(texto, tipo) {
            const div = document.createElement('div');
            div.className = `chat-bubble ${tipo}`;
            div.textContent = texto;
            chatMessages.appendChild(div);
            scrollAbajo();
            return div;
        }

        function mostrarTyping() {
            const div = document.createElement('div');
            div.className = 'chat-bubble bot typing';
            div.id = 'typingIndicator';
            div.innerHTML = `
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>`;
            chatMessages.appendChild(div);
            scrollAbajo();
        }

        function quitarTyping() {
            const el = document.getElementById('typingIndicator');
            if (el) el.remove();
        }

        async function enviarMensaje() {
            const texto = chatInput.value.trim();
            if (!texto) return;

            // Mostrar mensaje del usuario
            agregarBurbuja(texto, 'user');
            chatInput.value = '';
            chatInput.style.height = 'auto';

            // Guardar en historial
            historialChat.push({ rol: 'usuario', texto });

            // Bloquear input mientras espera
            chatSendBtn.disabled = true;
            chatInput.disabled   = true;
            mostrarTyping();

            try {
                const respuesta = await fetch('secciones/ia/ia_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        mensaje:  texto,
                        seccion:  ctxSeccion,
                        vista:    ctxVista,
                        es_admin: ctxEsAdmin,
                        historial: historialChat.slice(-10) // últimos 10 turnos para no saturar el prompt
                    })
                });

                const data = await respuesta.json();
                quitarTyping();

                const textoRespuesta = data.success
                    ? data.respuesta
                    : '⚠️ ' + (data.respuesta || 'Error desconocido al contactar con la IA.');

                agregarBurbuja(textoRespuesta, 'bot');

                // Guardar respuesta en historial
                if (data.success) {
                    historialChat.push({ rol: 'asistente', texto: textoRespuesta });
                }

            } catch (err) {
                quitarTyping();
                agregarBurbuja('⚠️ No se pudo conectar con el asistente. Inténtalo de nuevo.', 'bot');
                console.error('Error chatbot:', err);
            } finally {
                chatSendBtn.disabled = false;
                chatInput.disabled   = false;
                chatInput.focus();
            }
        }

        
    </script>
 <script src="secciones/tipos_jornada_helper.js"></script>
</body>
</html>