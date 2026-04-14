<?php
// secciones/tickets.php — Vista empleado
if (!isset($_SESSION['usuario'])) die('No autorizado');

$idUsuario = $_SESSION['usuario']['id'];
$idEmpresa = (int)$_SESSION['empresa_activa'];

// ── Vista detalle de un ticket ────────────────────────────
$idTicket = isset($_GET['ticket']) ? (int)$_GET['ticket'] : 0;
if ($idTicket) {
    // Verificar que el ticket pertenece al empleado
    $stmtT = $pdo->prepare("
        SELECT i.*, U.nombre, U.apellidos, U.foto_perfil
        FROM incidencias i
        JOIN usuario U ON i.id_usuario = U.id_usuario
        WHERE i.id_incidencia = ? AND i.id_usuario = ? AND i.id_empresa = ?
    ");
    $stmtT->execute([$idTicket, $idUsuario, $idEmpresa]);
    $ticket = $stmtT->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        header('Location: panel.php?seccion=tickets');
        exit;
    }

    // Mensajes del hilo
    $stmtM = $pdo->prepare("
        SELECT m.*, U.nombre, U.apellidos, U.foto_perfil
        FROM mensajes_ticket m
        JOIN usuario U ON m.id_usuario = U.id_usuario
        WHERE m.id_incidencia = ?
        ORDER BY m.fecha_creacion ASC
    ");
    $stmtM->execute([$idTicket]);
    $mensajes = $stmtM->fetchAll(PDO::FETCH_ASSOC);
}

// ── Lista de tickets del empleado ─────────────────────────
$stmtLista = $pdo->prepare("
    SELECT i.*,
        (SELECT COUNT(*) FROM mensajes_ticket m WHERE m.id_incidencia = i.id_incidencia) AS total_mensajes
    FROM incidencias i
    WHERE i.id_usuario = ? AND i.id_empresa = ?
    ORDER BY
        FIELD(i.estado,'PENDIENTE','EN_PROCESO','RESUELTA','CERRADA'),
        i.fecha_creacion DESC
");
$stmtLista->execute([$idUsuario, $idEmpresa]);
$tickets = $stmtLista->fetchAll(PDO::FETCH_ASSOC);

$estados = ['PENDIENTE'=>0,'EN_PROCESO'=>0,'RESUELTA'=>0,'CERRADA'=>0];
foreach ($tickets as $t) {
    if (isset($estados[$t['estado']])) $estados[$t['estado']]++;
}

$labelEstado = ['PENDIENTE'=>'Pendiente','EN_PROCESO'=>'En proceso','RESUELTA'=>'Resuelta','CERRADA'=>'Cerrada'];
$labelCategoria = ['HORARIO'=>'Horario','NOMINA'=>'Nómina','VACACIONES'=>'Vacaciones','FICHAJE'=>'Fichaje','OTRO'=>'Otro'];
$labelPrioridad = ['BAJA'=>'Baja','MEDIA'=>'Media','ALTA'=>'Alta'];
$iconoCategoria = ['HORARIO'=>'','NOMINA'=>'','VACACIONES'=>'','FICHAJE'=>'','OTRO'=>''];
?>

<link rel="stylesheet" href="css/tickets.css">

<div class="tickets-wrapper">

<?php if ($idTicket && $ticket): ?>
    <!-- ══════════════════════════════════════════
         DETALLE DE UN TICKET
    ══════════════════════════════════════════ -->
    <div class="ticket-detalle">
        <div class="ticket-detalle-header">
            <div>
                <button class="btn-volver" style="margin-bottom:10px;"
                        onclick="location.href='panel.php?seccion=tickets'">← Volver</button>
                <h3><?= htmlspecialchars($ticket['asunto']) ?></h3>
                <div class="meta">
                    <span class="badge-ticket badge-<?= strtolower($ticket['estado']) ?>">
                        <?= $labelEstado[$ticket['estado']] ?>
                    </span>
                    &nbsp;·&nbsp;
                    <?= $iconoCategoria[$ticket['categoria']] ?> <?= $labelCategoria[$ticket['categoria']] ?>
                    &nbsp;·&nbsp;
                    Prioridad: <?= $labelPrioridad[$ticket['prioridad']] ?>
                    &nbsp;·&nbsp;
                    <?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?>
                </div>
            </div>
        </div>

        <!-- Hilo de mensajes -->
        <div class="hilo-mensajes" id="hiloMensajes">
            <?php foreach ($mensajes as $msg): ?>
                <div class="mensaje-bubble <?= $msg['es_admin'] ? 'admin' : 'empleado' ?>">
                    <div class="mensaje-contenido">
                        <?= nl2br(htmlspecialchars($msg['mensaje'])) ?>
                    </div>
                    <div class="mensaje-autor">
                        <img src="<?= htmlspecialchars($msg['foto_perfil'] ?? 'secciones/uploads/perfil_default.jpg') ?>"
                             class="mensaje-avatar"
                             onerror="this.src='secciones/uploads/perfil_default.jpg'">
                        <span><?= htmlspecialchars($msg['nombre'] . ' ' . $msg['apellidos']) ?></span>
                        <span>· <?= date('d/m/Y H:i', strtotime($msg['fecha_creacion'])) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($mensajes)): ?>
                <p style="color:#adb5bd;text-align:center;padding:20px 0;">Sin mensajes aún.</p>
            <?php endif; ?>
        </div>

        <!-- Caja de respuesta (solo si el ticket no está cerrado) -->
        <?php if (!in_array($ticket['estado'], ['RESUELTA','CERRADA'])): ?>
        <div class="respuesta-box">
            <textarea id="txtRespuesta" placeholder="Escribe tu mensaje..."></textarea>
            <div class="respuesta-acciones">
                <span style="font-size:13px;color:#6c757d;">Pulsa Enviar para añadir al hilo</span>
                <button class="btn-primary" onclick="enviarMensaje(<?= $idTicket ?>)">
                    Enviar mensaje
                </button>
            </div>
        </div>
        <?php else: ?>
        <div style="padding:14px 25px;background:#f8f9fa;border-top:1px solid #e9ecef;font-size:13px;color:#6c757d;text-align:center;">
             Este ticket está <?= strtolower($labelEstado[$ticket['estado']]) ?>. No se pueden añadir más mensajes.
        </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- ══════════════════════════════════════════
         LISTA DE TICKETS DEL EMPLEADO
    ══════════════════════════════════════════ -->
    <div class="tickets-header">
        <div>
            <h2> Mis Tickets</h2>
            <p>Comunica incidencias o consultas a tu administrador</p>
        </div>
        <button class="btn-nuevo-ticket" onclick="abrirModalNuevo()">
             Nuevo Ticket
        </button>
    </div>

    <!-- Stats -->
    <div class="tickets-stats">
        <div class="stat-ticket pendiente">
            <div class="stat-ticket-num"><?= $estados['PENDIENTE'] ?></div>
            <div class="stat-ticket-lbl">Pendientes</div>
        </div>
        <div class="stat-ticket proceso">
            <div class="stat-ticket-num"><?= $estados['EN_PROCESO'] ?></div>
            <div class="stat-ticket-lbl">En proceso</div>
        </div>
        <div class="stat-ticket resuelta">
            <div class="stat-ticket-num"><?= $estados['RESUELTA'] ?></div>
            <div class="stat-ticket-lbl">Resueltos</div>
        </div>
        <div class="stat-ticket cerrada">
            <div class="stat-ticket-num"><?= $estados['CERRADA'] ?></div>
            <div class="stat-ticket-lbl">Cerrados</div>
        </div>
    </div>

    <!-- Lista -->
    <div class="tickets-lista">
        <?php if (empty($tickets)): ?>
            <div class="tickets-empty">
                <div class="empty-icon"></div>
                <h3>Aún no tienes tickets</h3>
                <p>Usa el botón "Nuevo Ticket" para comunicarte con tu administrador.</p>
            </div>
        <?php else: ?>
            <?php foreach ($tickets as $t): ?>
                <div class="ticket-card <?= strtolower($t['estado']) ?>"
                     onclick="location.href='panel.php?seccion=tickets&ticket=<?= $t['id_incidencia'] ?>'">
                    <div class="ticket-main">
                        <div class="ticket-asunto">
                            <?= $iconoCategoria[$t['categoria']] ?>
                            <?= htmlspecialchars($t['asunto']) ?>
                            <?php if ($t['total_mensajes'] > 1): ?>
                                <span style="font-size:12px;font-weight:400;color:#6c757d;">
                                    (<?= $t['total_mensajes'] ?> mensajes)
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="ticket-meta">
                            <span> <?= $labelCategoria[$t['categoria']] ?></span>
                            <span> <?= date('d/m/Y H:i', strtotime($t['fecha_creacion'])) ?></span>
                            <?php if ($t['fecha_respuesta']): ?>
                                <span> Última respuesta: <?= date('d/m/Y', strtotime($t['fecha_respuesta'])) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="ticket-badges">
                        <span class="badge-ticket badge-<?= strtolower($t['estado']) ?>">
                            <?= $labelEstado[$t['estado']] ?>
                        </span>
                        <span class="badge-ticket badge-<?= strtolower($t['prioridad']) ?>">
                            <?= $labelPrioridad[$t['prioridad']] ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<?php endif; ?>
</div>

<!-- ── Modal Nuevo Ticket ──────────────────────────────── -->
<div id="modalNuevoTicket" class="modal-ticket" style="display:none;">
    <div class="modal-ticket-content">
        <div class="modal-ticket-header">
            <h3> Nuevo Ticket</h3>
            <button class="close-btn" onclick="cerrarModal()">&times;</button>
        </div>
        <div class="modal-ticket-body">
            <div class="ticket-form-row">
                <div class="ticket-form-group">
                    <label>Categoría *</label>
                    <select id="tk_categoria">
                        <option value="HORARIO"> Horario</option>
                        <option value="NOMINA"> Nómina</option>
                        <option value="VACACIONES"> Vacaciones</option>
                        <option value="FICHAJE"> Fichaje</option>
                        <option value="OTRO" selected> Otro</option>
                    </select>
                </div>
                <div class="ticket-form-group">
                    <label>Prioridad</label>
                    <select id="tk_prioridad">
                        <option value="BAJA"> Baja</option>
                        <option value="MEDIA" selected> Media</option>
                        <option value="ALTA"> Alta</option>
                    </select>
                </div>
            </div>
            <div class="ticket-form-group">
                <label>Asunto *</label>
                <input type="text" id="tk_asunto" placeholder="Resumen breve del problema o consulta" maxlength="200">
            </div>
            <div class="ticket-form-group">
                <label>Descripción *</label>
                <textarea id="tk_mensaje" placeholder="Explica tu problema o consulta con el mayor detalle posible..."></textarea>
            </div>
        </div>
        <div class="modal-ticket-footer">
            <button class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-primary" onclick="crearTicket()"> Enviar Ticket</button>
        </div>
    </div>
</div>

<script>
function abrirModalNuevo() {
    document.getElementById('tk_asunto').value   = '';
    document.getElementById('tk_mensaje').value  = '';
    document.getElementById('tk_categoria').value = 'OTRO';
    document.getElementById('tk_prioridad').value = 'MEDIA';
    document.getElementById('modalNuevoTicket').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modalNuevoTicket').style.display = 'none';
}

function crearTicket() {
    const asunto   = document.getElementById('tk_asunto').value.trim();
    const mensaje  = document.getElementById('tk_mensaje').value.trim();
    if (!asunto)  { alert('El asunto es obligatorio'); return; }
    if (!mensaje) { alert('La descripción es obligatoria'); return; }

    fetch('secciones/api/ticket_crear.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            asunto,
            mensaje,
            categoria: document.getElementById('tk_categoria').value,
            prioridad: document.getElementById('tk_prioridad').value,
        })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            cerrarModal();
            location.href = 'panel.php?seccion=tickets&ticket=' + d.id_incidencia;
        } else {
            alert('❌ ' + d.message);
        }
    })
    .catch(() => alert('❌ Error de conexión'));
}

function enviarMensaje(idTicket) {
    const txt = document.getElementById('txtRespuesta').value.trim();
    if (!txt) { alert('Escribe un mensaje antes de enviar'); return; }

    fetch('secciones/api/ticket_responder.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id_incidencia: idTicket, mensaje: txt })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            location.reload();
        } else {
            alert('❌ ' + d.message);
        }
    })
    .catch(() => alert('❌ Error de conexión'));
}

// Scroll al final del hilo al cargar
window.addEventListener('DOMContentLoaded', () => {
    const hilo = document.getElementById('hiloMensajes');
    if (hilo) hilo.scrollTop = hilo.scrollHeight;
});

window.onclick = e => {
    if (e.target.classList.contains('modal-ticket'))
        cerrarModal();
};
</script>