<?php 

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
</head>
<body>

<!-- Overlay oscuro (click para cerrar menú en mobile) -->
<div id="menu-overlay" onclick="closeMobileMenu()" aria-hidden="true"></div>

<header>
    <!-- Botón hamburguesa — solo visible en mobile (CSS lo controla) -->
    <button id="hamburger-btn"
            onclick="toggleMobileMenu()"
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
                <h3>👤 Empleado</h3>
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
                if ($vista === "ver")        include "secciones/fichaje_ver.php";
                if ($vista === "config_ip")  include "secciones/config_ip.php";
                if ($vista === "modificar")  include "secciones/fichaje_modificar.php";
                if ($vista === "inicio")     include "secciones/fichaje.php";
            }
            if ($seccion === "horario") {
                if ($vista === "peticiones") include "secciones/horario.php";
                if ($vista === "cuadrantes") include "secciones/horario_cuadrantes.php";
                if ($vista === "vacaciones") include "secciones/vacaciones_cuadrante.php";
                if ($vista === "plantillas") include "secciones/tipos_jornada_admin.php";
                if ($vista === "calendario") include "secciones/horario_admin_calendario.php";
                if (!$vista)                 include "secciones/horario.php";
            }
            if ($seccion === "reportes") {
                if ($vista === "generar")    include "secciones/reportes.php";
                if ($vista === "historial")  include "secciones/reportes_historial.php";
                if (!$vista)                 include "secciones/reportes.php";
            }
            if ($seccion === "empleados") {
                if ($vista === "lista")    include "secciones/empleados_lista.php";
                if ($vista === "nuevo")    include "secciones/empleados_nuevo.php";
                if ($vista === "nominas")  include "secciones/nominas_admin.php";
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
        if ($seccion === "tickets")        include "secciones/tickets.php";
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
    // MENÚ HAMBURGUESA MOBILE
    // ─────────────────────────────────────────────────────────
    function toggleMobileMenu() {
        const isOpen = document.body.classList.toggle('menu-open');
        const btn    = document.getElementById('hamburger-btn');
        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        btn.setAttribute('aria-label',    isOpen ? 'Cerrar menú' : 'Abrir menú');
    }

    function closeMobileMenu() {
        document.body.classList.remove('menu-open');
        const btn = document.getElementById('hamburger-btn');
        btn.setAttribute('aria-expanded', 'false');
        btn.setAttribute('aria-label', 'Abrir menú');
    }

    // Cerrar con tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.body.classList.contains('menu-open')) {
            closeMobileMenu();
        }
    });

    // Cerrar si el usuario hace resize a desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeMobileMenu();
        }
    });

    // ─────────────────────────────────────────────────────────
    // SUBMENÚS ACORDEÓN (admin)
    // ─────────────────────────────────────────────────────────
    document.querySelectorAll(".submenu-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const content = btn.nextElementSibling;
            const isOpening = !content.classList.contains('open');
            
            // Cerrar todos los demás
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
    // NOTIFICACIONES
    // ─────────────────────────────────────────────────────────
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
                        footer.style = "padding:10px;text-align:center;border-top:1px solid #eee;";
                        footer.innerHTML = `<a href="panel.php?seccion=notificaciones" style="text-decoration:none;color:#007bff;font-size:12px;">VER TODAS</a>`;
                        list.appendChild(footer);
                    }
                    const badge = document.querySelector('.notification-badge');
                    if (badge) badge.style.display = 'none';
                });
        }
    }

    // ─────────────────────────────────────────────────────────
    // CHATBOT
    // ─────────────────────────────────────────────────────────
    const chatFab      = document.getElementById('chatFab');
    const chatModal    = document.getElementById('chatModal');
    const chatCloseBtn = document.getElementById('chatCloseBtn');
    const chatInput    = document.getElementById('chatInput');
    const chatSendBtn  = document.getElementById('chatSendBtn');
    const chatMessages = document.getElementById('chatMessages');
    const chatClearBtn = document.getElementById('chatClearBtn');

    const ctxSeccion = <?= json_encode($seccion) ?>;
    const ctxVista   = <?= json_encode($vista) ?>;
    const ctxEsAdmin = <?= json_encode((bool)$esAdmin) ?>;

    let historialChat = [];

    chatFab.addEventListener('click', () => {
        chatModal.classList.toggle('open');
        if (chatModal.classList.contains('open')) chatInput.focus();
    });
    chatCloseBtn.addEventListener('click', () => chatModal.classList.remove('open'));

    chatClearBtn.addEventListener('click', () => {
        historialChat = [];
        chatMessages.innerHTML = `
            <div class="chat-bubble bot">
                👋 Hola, soy tu asistente. ¿En qué puedo ayudarte?
            </div>`;
    });

    chatInput.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); enviarMensaje(); }
    });
    chatSendBtn.addEventListener('click', enviarMensaje);

    chatInput.addEventListener('input', () => {
        chatInput.style.height = 'auto';
        chatInput.style.height = Math.min(chatInput.scrollHeight, 100) + 'px';
    });

    function scrollAbajo() { chatMessages.scrollTop = chatMessages.scrollHeight; }

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
        div.innerHTML = `<span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>`;
        chatMessages.appendChild(div);
        scrollAbajo();
    }

    function quitarTyping() {
        document.getElementById('typingIndicator')?.remove();
    }

    async function enviarMensaje() {
        const texto = chatInput.value.trim();
        if (!texto) return;
        agregarBurbuja(texto, 'user');
        chatInput.value = '';
        chatInput.style.height = 'auto';
        historialChat.push({ rol: 'usuario', texto });
        chatSendBtn.disabled = true;
        chatInput.disabled   = true;
        mostrarTyping();

        try {
            const respuesta = await fetch('secciones/ia/ia_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    mensaje:   texto,
                    seccion:   ctxSeccion,
                    vista:     ctxVista,
                    es_admin:  ctxEsAdmin,
                    historial: historialChat.slice(-10)
                })
            });
            const data = await respuesta.json();
            quitarTyping();
            const textoResp = data.success ? data.respuesta : '⚠️ ' + (data.respuesta || 'Error desconocido.');
            agregarBurbuja(textoResp, 'bot');
            if (data.success) historialChat.push({ rol: 'asistente', texto: textoResp });
        } catch (err) {
            quitarTyping();
            agregarBurbuja('⚠️ No se pudo conectar con el asistente.', 'bot');
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