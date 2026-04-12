<?php
// secciones/tipos_jornada_admin.php
if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) die('No autorizado');
$idEmpresa = (int)$_SESSION['empresa_activa'];

// Obtener plantillas de esta empresa
$stmt = $pdo->prepare("
    SELECT * FROM tipos_jornada_custom
    WHERE id_empresa = ?
    ORDER BY activo DESC, nombre_display ASC
");
$stmt->execute([$idEmpresa]);
$plantillas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="css/tipos_jornadas.css">


<div class="section-header">
    <h2> Plantillas de Jornada</h2>
    <p>Crea tipos de jornada personalizados para tu empresa. Se añadirán a todos los selectores de horario.</p>
</div>

<div class="plantillas-topbar">
    <div style="font-size:14px;color:#6c757d;">
        <strong><?= count($plantillas) ?></strong> plantilla<?= count($plantillas) != 1 ? 's' : '' ?> configurada<?= count($plantillas) != 1 ? 's' : '' ?>
    </div>
    <button class="btn-primary" onclick="abrirModalCrear()"> Nueva Plantilla</button>
</div>

<?php if (empty($plantillas)): ?>
    <div class="plantillas-empty">
        <div class="empty-icon"></div>
        <h3>Aún no tienes plantillas personalizadas</h3>
        <p>Crea turnos de noche, jornadas intensivas, teletrabajo... lo que necesite tu empresa.</p>
    </div>
<?php else: ?>
    <div class="plantillas-grid">
        <?php foreach ($plantillas as $p): ?>
            <div class="plantilla-card <?= !$p['activo'] ? 'inactiva' : '' ?>"
                 id="plantilla-<?= $p['id_tipo_custom'] ?>">
                <div class="plantilla-card-header" style="background:<?= htmlspecialchars($p['color_hex']) ?>;"></div>
                <div class="plantilla-card-body">
                    <div class="plantilla-nombre">
                        <span class="plantilla-color-dot" style="background:<?= htmlspecialchars($p['color_hex']) ?>;"></span>
                        <?= htmlspecialchars($p['nombre_display']) ?>
                    </div>
                    <div class="plantilla-meta">
                        <?php if ($p['hora_inicio_predeterminada'] && $p['hora_fin_predeterminada']): ?>
                            <span> <?= substr($p['hora_inicio_predeterminada'],0,5) ?> – <?= substr($p['hora_fin_predeterminada'],0,5) ?></span>
                        <?php else: ?>
                            <span style="color:#adb5bd;"> Sin horario predeterminado</span>
                        <?php endif; ?>
                    </div>
                    <span class="plantilla-badge <?= $p['es_productivo'] ? 'productivo' : 'noproductivo' ?>">
                        <?= $p['es_productivo'] ? '✅ Productiva' : '⏸ No productiva' ?>
                    </span>
                    <?php if (!$p['activo']): ?>
                        <span class="plantilla-badge inactiva">🔴 Inactiva</span>
                    <?php endif; ?>
                </div>
                <div class="plantilla-actions">
                    <button class="btn-action btn-edit" title="Editar"
                            onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($p)) ?>)"> Editar</button>
                    <button class="btn-action" title="<?= $p['activo'] ? 'Desactivar' : 'Activar' ?>"
                            onclick="toggleActivo(<?= $p['id_tipo_custom'] ?>, <?= $p['activo'] ? 0 : 1 ?>)"
                            style="color:<?= $p['activo'] ? '#dc3545' : '#28a745' ?>">
                        <?= $p['activo'] ? '🚫 Desactivar' : '✅ Activar' ?>
                    </button>
                    <button class="btn-action" title="Eliminar"
                            onclick="eliminarPlantilla(<?= $p['id_tipo_custom'] ?>, '<?= htmlspecialchars($p['nombre_display']) ?>')"
                            style="color:#dc3545;margin-left:auto;">🗑️</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ── Modal Crear / Editar ───────────────────────────────── -->
<div id="modalPlantilla" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:520px;">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <h3 id="modalPlantillaTitulo">Nueva Plantilla</h3>

        <form id="formPlantilla" onsubmit="return guardarPlantilla(event)">
            <input type="hidden" id="pla_id">

            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" id="pla_nombre" required maxlength="80"
                       placeholder="Ej: Turno Noche, Guardia, Teletrabajo...">
            </div>

            <div class="form-group">
                <label>Color en cuadrante *</label>
                <div class="color-input-row">
                    <div class="color-preview" id="colorPreview" onclick="document.getElementById('pla_color_picker').click()"></div>
                    <input type="color" id="pla_color_picker" value="#667eea"
                           oninput="actualizarColor(this.value)">
                    <input type="text" id="pla_color" value="#667eea" maxlength="7"
                           style="width:90px;font-family:monospace;"
                           oninput="actualizarColorTexto(this.value)">
                </div>
                <div class="presets-colores" id="presetsColores">
                    <!-- se generan por JS -->
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Hora inicio predeterminada</label>
                    <input type="time" id="pla_inicio">
                    <small>Opcional. Se rellena automáticamente al seleccionar.</small>
                </div>
                <div class="form-group">
                    <label>Hora fin predeterminada</label>
                    <input type="time" id="pla_fin">
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="pla_productivo" checked>
                    <span> Es jornada productiva (cuenta como horas trabajadas)</span>
                </label>
                <small style="display:block;margin-top:6px;color:#6c757d;">
                    Desactívalo para festivos, bajas u otros tipos no laborables.
                </small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">💾 Guardar</button>
                <button type="button" class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
const PRESETS = [
    '#3498db','#e67e22','#8e44ad','#e74c3c','#27ae60',
    '#16a085','#f39c12','#2c3e50','#667eea','#e91e63',
    '#ff5722','#009688','#795548','#607d8b','#ff9800',
];

document.addEventListener('DOMContentLoaded', () => {
    const cont = document.getElementById('presetsColores');
    PRESETS.forEach(c => {
        const d = document.createElement('div');
        d.className = 'preset-color';
        d.style.background = c;
        d.title = c;
        d.onclick = () => actualizarColor(c);
        cont.appendChild(d);
    });
    actualizarColor('#667eea');
});

function actualizarColor(hex) {
    if (!/^#[0-9a-fA-F]{6}$/.test(hex)) return;
    document.getElementById('pla_color').value        = hex;
    document.getElementById('pla_color_picker').value = hex;
    document.getElementById('colorPreview').style.background = hex;
    // marcar preset activo
    document.querySelectorAll('.preset-color').forEach(d => {
        d.classList.toggle('activo', d.style.background === hexToRgb(hex) || d.title === hex);
    });
}

function actualizarColorTexto(val) {
    if (/^#[0-9a-fA-F]{6}$/.test(val)) actualizarColor(val);
}

function hexToRgb(hex) {
    const r = parseInt(hex.slice(1,3),16);
    const g = parseInt(hex.slice(3,5),16);
    const b = parseInt(hex.slice(5,7),16);
    return `rgb(${r}, ${g}, ${b})`;
}

// ── Modal ──────────────────────────────────────────────────────
function abrirModalCrear() {
    document.getElementById('modalPlantillaTitulo').textContent = ' Nueva Plantilla';
    document.getElementById('pla_id').value      = '';
    document.getElementById('pla_nombre').value  = '';
    document.getElementById('pla_inicio').value  = '';
    document.getElementById('pla_fin').value     = '';
    document.getElementById('pla_productivo').checked = true;
    actualizarColor('#667eea');
    document.getElementById('modalPlantilla').style.display = 'block';
}

function abrirModalEditar(p) {
    document.getElementById('modalPlantillaTitulo').textContent = ' Editar Plantilla';
    document.getElementById('pla_id').value      = p.id_tipo_custom;
    document.getElementById('pla_nombre').value  = p.nombre_display;
    document.getElementById('pla_inicio').value  = p.hora_inicio_predeterminada
                                                    ? p.hora_inicio_predeterminada.substr(0,5) : '';
    document.getElementById('pla_fin').value     = p.hora_fin_predeterminada
                                                    ? p.hora_fin_predeterminada.substr(0,5) : '';
    document.getElementById('pla_productivo').checked = p.es_productivo == 1;
    actualizarColor(p.color_hex || '#667eea');
    document.getElementById('modalPlantilla').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modalPlantilla').style.display = 'none';
}

// ── CRUD ───────────────────────────────────────────────────────
function guardarPlantilla(e) {
    e.preventDefault();
    const id     = document.getElementById('pla_id').value;
    const datos  = {
        id_tipo_custom:             id || null,
        nombre_display:             document.getElementById('pla_nombre').value.trim(),
        color_hex:                  document.getElementById('pla_color').value,
        hora_inicio_predeterminada: document.getElementById('pla_inicio').value || null,
        hora_fin_predeterminada:    document.getElementById('pla_fin').value    || null,
        es_productivo:              document.getElementById('pla_productivo').checked ? 1 : 0,
    };

    const url = id ? 'secciones/api/tipo_jornada_editar.php' : 'secciones/api/tipo_jornada_crear.php';

    fetch(url, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(datos)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { cerrarModal(); location.reload(); }
        else alert('❌ ' + d.message);
    })
    .catch(() => alert('❌ Error de conexión'));
    return false;
}

function toggleActivo(id, nuevoEstado) {
    fetch('secciones/api/tipo_jornada_toggle.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({id_tipo_custom: id, activo: nuevoEstado})
    })
    .then(r => r.json())
    .then(d => { if (d.success) location.reload(); else alert('❌ ' + d.message); });
}

function eliminarPlantilla(id, nombre) {
    if (!confirm(`¿Eliminar la plantilla "${nombre}"?\n\nLos horarios que la usen pasarán a mostrar el tipo genérico.`)) return;
    fetch('secciones/api/tipo_jornada_eliminar.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({id_tipo_custom: id})
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            const card = document.getElementById('plantilla-' + id);
            card.style.opacity = '0';
            card.style.transition = 'opacity .3s';
            setTimeout(() => card.remove(), 300);
        } else alert('❌ ' + d.message);
    });
}

window.onclick = e => { if (e.target.classList.contains('modal')) cerrarModal(); };
</script>