<?php
/*
 * config_ip.php — Vista de configuración de restricción por IP (admin)
 * Incluida por panel.php (seccion=fichaje&vista=config_ip). Solo admin.
 * Permite activar/desactivar la restricción de fichaje por IP y gestionar
 * la lista blanca de IPs/subredes autorizadas en EMPRESA_IPS_AUTORIZADAS.
 * Las operaciones AJAX apuntan a secciones/api/config_ip_api.php.
 */
if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) die('No autorizado');

require_once __DIR__ . '/../../config.php';
$idEmpresa = (int)$_SESSION['empresa_activa'];
$empresa   = $idEmpresa;

// Leer estado actual del toggle
$stmtE = $pdo->prepare("SELECT restringir_ip FROM empresa WHERE id_empresa = ?");
$stmtE->execute([$idEmpresa]);
$empresaRow  = $stmtE->fetch(PDO::FETCH_ASSOC);
$restringir  = (bool)($empresaRow['restringir_ip'] ?? 0);

// Leer lista de IPs
$stmtIPs = $pdo->prepare(
    "SELECT id, ip_address, etiqueta, creado_en
     FROM empresa_ips_autorizadas
     WHERE id_empresa = ?
     ORDER BY creado_en ASC"
);
$stmtIPs->execute([$idEmpresa]);
$ipsAutorizadas = $stmtIPs->fetchAll(PDO::FETCH_ASSOC);

$ipActual = $_SERVER['REMOTE_ADDR'] ?? '';
?>
<link rel="stylesheet" href="css/config_ip.css">

<div class="cip-page">

    <!-- ══ CABECERA ══════════════════════════════════════════ -->
    <div class="cip-hero">
        <div class="cip-hero-icon"></div>
        <div>
            <h2 class="cip-hero-title">Control de Acceso por IP</h2>
            <p class="cip-hero-sub">Define desde qué ubicaciones pueden fichar los empleados de tu empresa.</p>
        </div>
        <div class="cip-tu-ip">
            <span class="cip-tu-ip-label">Tu IP actual</span>
            <code class="cip-tu-ip-val"><?= htmlspecialchars($ipActual) ?></code>
        </div>
    </div>

    <!-- ══ TARJETA TOGGLE ════════════════════════════════════ -->
    <div class="cip-card cip-card-toggle">
        <div class="cip-card-left">
            <div class="cip-toggle-icon <?= $restringir ? 'on' : 'off' ?>" id="toggleIcon">
                <?= $restringir ? '' : '' ?>
            </div>
            <div>
                <h3 class="cip-toggle-title" id="toggleTitle">
                    <?= $restringir ? 'Restricción activa' : 'Acceso libre' ?>
                </h3>
                <p class="cip-toggle-desc" id="toggleDesc">
                    <?= $restringir
                        ? 'Solo las IPs de tu lista pueden fichar.'
                        : 'Cualquier IP puede fichar. Activa la restricción para limitar el acceso.' ?>
                </p>
            </div>
        </div>
        <label class="cip-switch" title="Activar/desactivar restricción por IP">
            <input type="checkbox" id="chkRestriccion" <?= $restringir ? 'checked' : '' ?>
                   onchange="handleToggle(this.checked)">
            <span class="cip-switch-track">
                <span class="cip-switch-thumb"></span>
            </span>
        </label>
    </div>

    <!-- ══ TARJETA LISTA DE IPs ══════════════════════════════ -->
    <div class="cip-card" id="cardLista" <?= !$restringir ? 'style="opacity:.55;pointer-events:none;"' : '' ?>>
        <div class="cip-card-header-row">
            <div>
                <h3 class="cip-card-title"> IPs autorizadas</h3>
                <p class="cip-card-hint">
                    IP exacta <code>192.168.1.50</code> o prefijo de subred <code>192.168.1.</code>
                </p>
            </div>
            <button class="cip-btn-add" onclick="abrirModal()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em" style="vertical-align:-0.125em"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg> Añadir IP</button>
        </div>

        <!-- Lista -->
        <div class="cip-ip-list" id="ipList">
            <?php if (empty($ipsAutorizadas)): ?>
                <div class="cip-empty" id="emptyState">
                    <span class="cip-empty-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/></svg></span>
                    <p>Sin IPs autorizadas. Con restricción activa <strong>nadie podrá fichar</strong>.</p>
                </div>
            <?php else: ?>
                <?php foreach ($ipsAutorizadas as $row): ?>
                    <?= renderIpRow($row, $ipActual) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ TOAST ════════════════════════════════════════════ -->
    <div class="cip-toast" id="toast"></div>
</div>

<!-- ══ MODAL AÑADIR IP ══════════════════════════════════════ -->
<div class="cip-backdrop" id="backdrop" onclick="cerrarModal()"></div>
<div class="cip-modal" id="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <button class="cip-modal-close" onclick="cerrarModal()" aria-label="Cerrar">×</button>
    <h3 class="cip-modal-title" id="modalTitle">Añadir IP autorizada</h3>
    <p class="cip-modal-hint">Puedes escribir una IP exacta o un rango por prefijo.</p>

    <div class="cip-field">
        <label class="cip-label" for="inputIP">Dirección IP *</label>
        <input id="inputIP" type="text" class="cip-input" placeholder="192.168.1.50 ó 192.168.1."
               autocomplete="off" onkeydown="if(event.key==='Enter')guardarIP()">
        <button class="cip-btn-miip" onclick="usarMiIP()" title="Usar mi IP actual">
             Usar mi IP
        </button>
    </div>

    <div class="cip-field">
        <label class="cip-label" for="inputEtiqueta">Etiqueta (opcional)</label>
        <input id="inputEtiqueta" type="text" class="cip-input" placeholder="Ej: Oficina central, VPN empresa…"
               maxlength="100" onkeydown="if(event.key==='Enter')guardarIP()">
    </div>

    <div class="cip-error" id="modalError" style="display:none;"></div>

    <div class="cip-modal-footer">
        <button class="cip-btn-cancel" onclick="cerrarModal()">Cancelar</button>
        <button class="cip-btn-save"   onclick="guardarIP()" id="btnGuardar">Guardar IP</button>
    </div>
</div>

<?php
// Helper para renderizar una fila de IP (reutilizado en PHP y replicado en JS)
function renderIpRow(array $row, string $ipActual): string {
    $esMia = ($ipActual === $row['ip_address'] || str_starts_with($ipActual, $row['ip_address']));
    $etq   = htmlspecialchars($row['etiqueta'] ?? '');
    $ip    = htmlspecialchars($row['ip_address']);
    $id    = (int)$row['id'];
    $badge = $esMia ? '<span class="cip-badge-mia">Tu IP</span>' : '';
    return "
    <div class='cip-ip-row' id='ip-row-{$id}' data-id='{$id}'>
        <span class='cip-row-icon'></span>
        <div class='cip-row-info'>
            <code class='cip-row-ip'>{$ip}</code>
            " . ($etq ? "<span class='cip-row-etq'>{$etq}</span>" : '') . "
        </div>
        {$badge}
        <button class='cip-btn-del' onclick='eliminarIP({$id})' title='Eliminar'><svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' width='1em' height='1em'><path stroke-linecap='round' stroke-linejoin='round' d='M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0'/></svg></button>
    </div>";
}
?>

<script>
const IP_ACTUAL = <?= json_encode($ipActual) ?>;
const API_URL   = 'secciones/api/config_ip_api.php';

// ── Toggle ────────────────────────────────────────────────────
async function handleToggle(activo) {
    const card = document.getElementById('cardLista');
    try {
        const r = await apiFetch({ accion: 'toggle', activo });
        if (!r.ok) throw new Error(r.error);
        card.style.opacity       = activo ? '1'    : '.55';
        card.style.pointerEvents = activo ? 'auto' : 'none';
        document.getElementById('toggleIcon').textContent  = activo ? '' : '';
        document.getElementById('toggleIcon').className    = 'cip-toggle-icon ' + (activo ? 'on' : 'off');
        document.getElementById('toggleTitle').textContent = activo ? 'Restricción activa' : 'Acceso libre';
        document.getElementById('toggleDesc').textContent  = activo
            ? 'Solo las IPs de tu lista pueden fichar.'
            : 'Cualquier IP puede fichar. Activa la restricción para limitar el acceso.';
        toast(activo ? '🔒 Restricción activada' : '🔓 Restricción desactivada', activo ? 'warn' : 'ok');
    } catch (e) {
        document.getElementById('chkRestriccion').checked = !activo; // revertir
        toast('❌ ' + e.message, 'err');
    }
}

// ── Modal ─────────────────────────────────────────────────────
function abrirModal() {
    document.getElementById('inputIP').value       = '';
    document.getElementById('inputEtiqueta').value = '';
    document.getElementById('modalError').style.display = 'none';
    document.getElementById('backdrop').classList.add('visible');
    document.getElementById('modal').classList.add('visible');
    setTimeout(() => document.getElementById('inputIP').focus(), 80);
}
function cerrarModal() {
    document.getElementById('backdrop').classList.remove('visible');
    document.getElementById('modal').classList.remove('visible');
}
function usarMiIP() {
    document.getElementById('inputIP').value = IP_ACTUAL;
    document.getElementById('inputIP').focus();
}

// ── Guardar IP ────────────────────────────────────────────────
async function guardarIP() {
    const ip  = document.getElementById('inputIP').value.trim();
    const etq = document.getElementById('inputEtiqueta').value.trim();
    const errEl = document.getElementById('modalError');
    errEl.style.display = 'none';

    if (!ip) { mostrarErrorModal('La dirección IP es obligatoria.'); return; }
    if (!/^[\d\.:a-fA-F]{3,45}$/.test(ip)) {
        mostrarErrorModal('Formato no válido. Solo dígitos, puntos o dos puntos.'); return;
    }

    const btn = document.getElementById('btnGuardar');
    btn.disabled = true; btn.textContent = 'Guardando…';

    try {
        const r = await apiFetch({ accion: 'add_ip', ip_address: ip, etiqueta: etq });
        if (!r.ok) throw new Error(r.error);

        // Añadir fila al DOM
        insertarFilaIP(r.ip);
        document.getElementById('emptyState')?.remove();
        cerrarModal();
        toast('✅ IP añadida correctamente', 'ok');
    } catch (e) {
        mostrarErrorModal(e.message);
    } finally {
        btn.disabled = false; btn.textContent = 'Guardar IP';
    }
}

// ── Eliminar IP ───────────────────────────────────────────────
async function eliminarIP(id) {
    if (!confirm('¿Eliminar esta IP de la lista autorizada?')) return;
    try {
        const r = await apiFetch({ accion: 'delete_ip', id });
        if (!r.ok) throw new Error(r.error);
        const row = document.getElementById('ip-row-' + id);
        row.style.transform = 'translateX(30px)';
        row.style.opacity   = '0';
        setTimeout(() => {
            row.remove();
            if (!document.querySelector('.cip-ip-row')) {
                document.getElementById('ipList').innerHTML = `
                    <div class="cip-empty" id="emptyState">
                        <span class="cip-empty-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/></svg></span>
                        <p>Sin IPs autorizadas. Con restricción activa <strong>nadie podrá fichar</strong>.</p>
                    </div>`;
            }
        }, 280);
        toast('🗑 IP eliminada', 'warn');
    } catch (e) {
        toast('❌ ' + e.message, 'err');
    }
}

// ── Helpers ───────────────────────────────────────────────────
function insertarFilaIP(ip) {
    const esMia  = IP_ACTUAL === ip.ip_address || IP_ACTUAL.startsWith(ip.ip_address);
    const badge  = esMia ? '<span class="cip-badge-mia">Tu IP</span>' : '';
    const etqHtml = ip.etiqueta
        ? `<span class="cip-row-etq">${escapeHtml(ip.etiqueta)}</span>` : '';
    const html = `
    <div class="cip-ip-row cip-row-enter" id="ip-row-${ip.id}" data-id="${ip.id}">
        <span class="cip-row-icon"></span>
        <div class="cip-row-info">
            <code class="cip-row-ip">${escapeHtml(ip.ip_address)}</code>
            ${etqHtml}
        </div>
        ${badge}
        <button class="cip-btn-del" onclick="eliminarIP(${ip.id})" title="Eliminar"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></button>
    </div>`;
    document.getElementById('ipList').insertAdjacentHTML('beforeend', html);
    requestAnimationFrame(() =>
        document.getElementById('ip-row-' + ip.id)?.classList.remove('cip-row-enter')
    );
}

function mostrarErrorModal(msg) {
    const el = document.getElementById('modalError');
    el.textContent    = msg;
    el.style.display  = 'block';
}

async function apiFetch(data) {
    const r = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return r.json();
}

function escapeHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

let toastTimer;
function toast(msg, tipo = 'ok') {
    const el = document.getElementById('toast');
    el.textContent  = msg;
    el.className    = 'cip-toast visible cip-toast-' + tipo;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('visible'), 3200);
}

// Cerrar modal con Escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });
</script>