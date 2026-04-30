<?php
/*
 * tickets_admin.php — Vista de gestión de tickets (admin)
 * Incluida por panel.php (seccion=empleados&vista=tickets). Solo admin.
 * Muestra todos los tickets de la empresa con filtros por estado, categoría y empleado.
 * Permite responder (ticket_responder.php) y cambiar estado (ticket_estado.php).
 */
if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) die('No autorizado');

require_once __DIR__ . '/../../config.php';
$idEmpresa = (int)$_SESSION['empresa_activa'];
$empresa   = $idEmpresa;
$idAdmin   = $_SESSION['usuario']['id'];

$labelEstado    = ['PENDIENTE'=>'Pendiente','EN_PROCESO'=>'En proceso','RESUELTA'=>'Resuelta','CERRADA'=>'Cerrada'];
$labelCategoria = ['HORARIO'=>'Horario','NOMINA'=>'Nómina','VACACIONES'=>'Vacaciones','FICHAJE'=>'Fichaje','OTRO'=>'Otro'];
$labelPrioridad = ['BAJA'=>'Baja','MEDIA'=>'Media','ALTA'=>'Alta'];
$iconoCategoria = ['HORARIO'=>'','NOMINA'=>'','VACACIONES'=>'','FICHAJE'=>'','OTRO'=>''];

// ── Detalle de un ticket ──────────────────────────────────
$idTicket = isset($_GET['ticket']) ? (int)$_GET['ticket'] : 0;
if ($idTicket) {
    $stmtT = $pdo->prepare("
        SELECT i.*, U.nombre, U.apellidos, U.foto_perfil, U.email
        FROM incidencias i
        JOIN usuario U ON i.id_usuario = U.id_usuario
        WHERE i.id_incidencia = ? AND i.id_empresa = ?
    ");
    $stmtT->execute([$idTicket, $idEmpresa]);
    $ticket = $stmtT->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) { header('Location: panel.php?seccion=empleados&vista=tickets'); exit; }

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

// ── Lista con filtros ─────────────────────────────────────
$filtroEstado   = $_GET['estado']   ?? 'TODOS';
$filtroCateg    = $_GET['categoria'] ?? 'TODOS';
$filtroEmpleado = isset($_GET['empleado']) ? (int)$_GET['empleado'] : 0;

$sql = "
    SELECT i.*,
        U.nombre, U.apellidos, U.foto_perfil,
        (SELECT COUNT(*) FROM mensajes_ticket m WHERE m.id_incidencia = i.id_incidencia) AS total_mensajes
    FROM incidencias i
    JOIN usuario U ON i.id_usuario = U.id_usuario
    WHERE i.id_empresa = ?
";
$params = [$idEmpresa];
if ($filtroEstado !== 'TODOS')  { $sql .= " AND i.estado = ?";    $params[] = $filtroEstado; }
if ($filtroCateg  !== 'TODOS')  { $sql .= " AND i.categoria = ?"; $params[] = $filtroCateg; }
if ($filtroEmpleado > 0)        { $sql .= " AND i.id_usuario = ?"; $params[] = $filtroEmpleado; }
$sql .= " ORDER BY FIELD(i.estado,'PENDIENTE','EN_PROCESO','RESUELTA','CERRADA'), i.fecha_creacion DESC";

$stmtLista = $pdo->prepare($sql);
$stmtLista->execute($params);
$tickets = $stmtLista->fetchAll(PDO::FETCH_ASSOC);

// Stats totales
$stmtStats = $pdo->prepare("SELECT estado, COUNT(*) AS cnt FROM incidencias WHERE id_empresa=? GROUP BY estado");
$stmtStats->execute([$idEmpresa]);
$statsRaw = $stmtStats->fetchAll(PDO::FETCH_ASSOC);
$stats = ['PENDIENTE'=>0,'EN_PROCESO'=>0,'RESUELTA'=>0,'CERRADA'=>0];
foreach ($statsRaw as $s) $stats[$s['estado']] = (int)$s['cnt'];

// Empleados para filtro
$stmtEmps = $pdo->prepare("SELECT U.id_usuario, U.nombre, U.apellidos FROM usuario U JOIN empresa_usuario EU ON U.id_usuario=EU.id_usuario WHERE EU.id_empresa=? AND EU.activo=1 AND EU.admin=0 ORDER BY U.nombre");
$stmtEmps->execute([$idEmpresa]);
$empleados = $stmtEmps->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="css/tickets.css">

<div class="tickets-wrapper">

<?php if ($idTicket && $ticket): ?>
    <!-- ══════════════════════════════════════════
         DETALLE Y GESTIÓN DEL TICKET
    ══════════════════════════════════════════ -->
    <div class="ticket-detalle">
        <div class="ticket-detalle-header">
            <div style="flex:1;">
                <button class="btn-volver" style="margin-bottom:10px;"
                        onclick="location.href='panel.php?seccion=empleados&vista=tickets'">← Volver</button>
                <div class="ticket-empleado-info" style="margin-bottom:8px;">
                    <img src="<?= htmlspecialchars($ticket['foto_perfil'] ?? 'secciones/uploads/perfil_default.jpg') ?>"
                         onerror="this.src='secciones/uploads/perfil_default.jpg'">
                    <div>
                        <div style="font-weight:700;"><?= htmlspecialchars($ticket['nombre'] . ' ' . $ticket['apellidos']) ?></div>
                        <div style="font-size:12px;opacity:.85;"><?= htmlspecialchars($ticket['email']) ?></div>
                    </div>
                </div>
                <h3><?= htmlspecialchars($ticket['asunto']) ?></h3>
                <div class="meta">
                    <span class="badge-ticket badge-<?= strtolower($ticket['estado']) ?>">
                        <?= $labelEstado[$ticket['estado']] ?>
                    </span>
                    &nbsp;·&nbsp;
                    <?= $iconoCategoria[$ticket['categoria']] ?> <?= $labelCategoria[$ticket['categoria']] ?>
                    &nbsp;·&nbsp; Prioridad: <?= $labelPrioridad[$ticket['prioridad']] ?>
                    &nbsp;·&nbsp; <?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?>
                </div>
            </div>
            <!-- Acciones de estado -->
            <div style="display:flex;flex-direction:column;gap:8px;min-width:160px;">
                <?php if ($ticket['estado'] === 'PENDIENTE'): ?>
                    <button class="btn-info" onclick="cambiarEstado(<?= $idTicket ?>, 'EN_PROCESO')"> Marcar en proceso</button>
                <?php endif; ?>
                <?php if (in_array($ticket['estado'], ['PENDIENTE','EN_PROCESO'])): ?>
                    <button class="btn-success" onclick="cambiarEstado(<?= $idTicket ?>, 'RESUELTA')"> Marcar resuelta</button>
                    <button class="btn-danger" onclick="cambiarEstado(<?= $idTicket ?>, 'CERRADA')"> Cerrar ticket</button>
                <?php endif; ?>
                <?php if ($ticket['estado'] === 'RESUELTA'): ?>
                    <button class="btn-danger" onclick="cambiarEstado(<?= $idTicket ?>, 'CERRADA')"> Cerrar ticket</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hilo -->
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
                        <span><?= htmlspecialchars($msg['nombre'] . ' ' . $msg['apellidos']) ?>
                            <?= $msg['es_admin'] ? '<span style="font-size:10px;background:#667eea;color:white;padding:1px 5px;border-radius:4px;">Admin</span>' : '' ?>
                        </span>
                        <span>· <?= date('d/m/Y H:i', strtotime($msg['fecha_creacion'])) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Respuesta admin -->
        <?php if (!in_array($ticket['estado'], ['CERRADA'])): ?>
        <div class="respuesta-box">
            <textarea id="txtRespuesta" placeholder="Escribe tu respuesta al empleado..."></textarea>
            <div class="respuesta-acciones">
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button class="btn-primary" onclick="enviarRespuesta(<?= $idTicket ?>)">
                         Enviar respuesta
                    </button>
                    <button class="btn-success" onclick="enviarYResolver(<?= $idTicket ?>)">
                         Responder y resolver
                    </button>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div style="padding:14px 25px;background:#f8f9fa;border-top:1px solid #e9ecef;font-size:13px;color:#6c757d;text-align:center;">
             Ticket cerrado.
        </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- ══════════════════════════════════════════
         LISTA DE TODOS LOS TICKETS
    ══════════════════════════════════════════ -->
    <div class="tickets-header">
        <div>
            <h2> Gestión de Tickets</h2>
            <p>Consultas e incidencias de los empleados</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="tickets-stats">
        <div class="stat-ticket pendiente">
            <div class="stat-ticket-num"><?= $stats['PENDIENTE'] ?></div>
            <div class="stat-ticket-lbl">Pendientes</div>
        </div>
        <div class="stat-ticket proceso">
            <div class="stat-ticket-num"><?= $stats['EN_PROCESO'] ?></div>
            <div class="stat-ticket-lbl">En proceso</div>
        </div>
        <div class="stat-ticket resuelta">
            <div class="stat-ticket-num"><?= $stats['RESUELTA'] ?></div>
            <div class="stat-ticket-lbl">Resueltos</div>
        </div>
        <div class="stat-ticket cerrada">
            <div class="stat-ticket-num"><?= $stats['CERRADA'] ?></div>
            <div class="stat-ticket-lbl">Cerrados</div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="tickets-filters">
        <div class="f-group">
            <label>Estado</label>
            <select onchange="filtrar(this, 'estado')">
                <option value="TODOS" <?= $filtroEstado==='TODOS'?'selected':'' ?>>Todos</option>
                <option value="PENDIENTE"  <?= $filtroEstado==='PENDIENTE' ?'selected':'' ?>>Pendiente</option>
                <option value="EN_PROCESO" <?= $filtroEstado==='EN_PROCESO'?'selected':'' ?>>En proceso</option>
                <option value="RESUELTA"   <?= $filtroEstado==='RESUELTA'  ?'selected':'' ?>>Resuelta</option>
                <option value="CERRADA"    <?= $filtroEstado==='CERRADA'   ?'selected':'' ?>>Cerrada</option>
            </select>
        </div>
        <div class="f-group">
            <label>Categoría</label>
            <select onchange="filtrar(this, 'categoria')">
                <option value="TODOS" <?= $filtroCateg==='TODOS'?'selected':'' ?>>Todas</option>
                <?php foreach ($labelCategoria as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $filtroCateg===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="f-group">
            <label>Empleado</label>
            <select onchange="filtrar(this, 'empleado')">
                <option value="0" <?= $filtroEmpleado===0?'selected':'' ?>>Todos</option>
                <?php foreach ($empleados as $e): ?>
                    <option value="<?= $e['id_usuario'] ?>" <?= $filtroEmpleado==$e['id_usuario']?'selected':'' ?>>
                        <?= htmlspecialchars($e['nombre'].' '.$e['apellidos']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Lista -->
    <div class="tickets-lista">
        <?php if (empty($tickets)): ?>
            <div class="tickets-empty">
                <div class="empty-icon"></div>
                <h3>No hay tickets con estos filtros</h3>
                <p>Cambia los filtros para ver otros tickets.</p>
            </div>
        <?php else: ?>
            <?php foreach ($tickets as $t): ?>
                <div class="ticket-card <?= strtolower($t['estado']) ?>"
                     onclick="location.href='panel.php?seccion=empleados&vista=tickets&ticket=<?= $t['id_incidencia'] ?>'">
                    <div style="display:flex;align-items:center;gap:12px;margin-right:10px;">
                        <img src="<?= htmlspecialchars($t['foto_perfil'] ?? 'secciones/uploads/perfil_default.jpg') ?>"
                             onerror="this.src='secciones/uploads/perfil_default.jpg'"
                             style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #e9ecef;flex-shrink:0;">
                    </div>
                    <div class="ticket-main">
                        <div class="ticket-asunto">
                            <?= $iconoCategoria[$t['categoria']] ?>
                            <?= htmlspecialchars($t['asunto']) ?>
                            <?php if ($t['total_mensajes'] > 0): ?>
                                <span style="font-size:11px;background:#e3f2fd;color:#1565c0;padding:2px 7px;border-radius:10px;">
                                    <?= $t['total_mensajes'] ?> msgs
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="ticket-meta">
                            <span> <?= htmlspecialchars($t['nombre'] . ' ' . $t['apellidos']) ?></span>
                            <span> <?= date('d/m/Y H:i', strtotime($t['fecha_creacion'])) ?></span>
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

<script>
function filtrar(sel, param) {
    const url = new URL(window.location.href);
    url.searchParams.set('seccion', 'empleados');
    url.searchParams.set('vista', 'tickets');
    url.searchParams.set(param, sel.value);
    window.location.href = url.toString();
}

function cambiarEstado(idTicket, nuevoEstado) {
    const labels = {EN_PROCESO:'en proceso', RESUELTA:'resuelta', CERRADA:'cerrada'};
    if (!confirm(`¿Marcar el ticket como ${labels[nuevoEstado]}?`)) return;
    fetch('secciones/api/ticket_estado.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id_incidencia: idTicket, estado: nuevoEstado })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
        else alert('❌ ' + d.message);
    });
}

function enviarRespuesta(idTicket) {
    const txt = document.getElementById('txtRespuesta').value.trim();
    if (!txt) { alert('Escribe una respuesta'); return; }
    fetch('secciones/api/ticket_responder.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id_incidencia: idTicket, mensaje: txt })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
        else alert('❌ ' + d.message);
    });
}

function enviarYResolver(idTicket) {
    const txt = document.getElementById('txtRespuesta').value.trim();
    if (!txt) { alert('Escribe una respuesta antes de resolver'); return; }
    fetch('secciones/api/ticket_responder.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id_incidencia: idTicket, mensaje: txt, resolver: true })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
        else alert('❌ ' + d.message);
    });
}

window.addEventListener('DOMContentLoaded', () => {
    const hilo = document.getElementById('hiloMensajes');
    if (hilo) hilo.scrollTop = hilo.scrollHeight;
});
</script>