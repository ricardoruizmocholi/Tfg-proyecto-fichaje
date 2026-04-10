<?php 
$id_usuario = $usuario['id'];
$sqlUsuario = "SELECT * FROM USUARIO WHERE id_usuario = :id_usuario";
$stmtUsuario = $pdo->prepare($sqlUsuario);
$stmtUsuario->execute(['id_usuario' => $id_usuario]);
$datosUsuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
if (!$datosUsuario) die("Error al cargar datos del usuario.");

$imagenPerfil = $datosUsuario['foto_perfil'] ?? 'secciones/uploads/perfil_default.jpg';

$labelTipoContrato = [
    'completa' => '📋 Jornada Completa',
    'parcial'  => '📋 Jornada Parcial',
];
?>

<link rel="stylesheet" href="css/perfil.css">


<div class="perfil-container">

    <!-- ── Foto y datos básicos ──────────────────────────── -->
    <div class="perfil-card">
        <div class="perfil-header">
            <div class="foto-perfil-container">
                <img src="<?= htmlspecialchars($imagenPerfil) ?>"
                     alt="Foto de perfil" class="foto-perfil" id="fotoPerfil">
                <button class="cambiar-foto-btn"
                        onclick="document.getElementById('inputFoto').click();"
                        title="Cambiar foto">📷</button>
                <form id="formFoto" enctype="multipart/form-data" style="display:none;">
                    <input type="file" id="inputFoto" name="foto" accept="image/*" class="input-file-custom">
                </form>
            </div>

            <div class="usuario-info">
                <h2><?= htmlspecialchars($datosUsuario['nombre'] . ' ' . $datosUsuario['apellidos']) ?></h2>
                <p> <?= htmlspecialchars($datosUsuario['email']) ?></p>
                <p> Empresa: <strong><?= htmlspecialchars($usuario['empresas'][0]['nombre'] ?? 'N/A') ?></strong></p>
                <p>
                    <?= $labelTipoContrato[$datosUsuario['tipo_contrato'] ?? 'completa'] ?>
                    &nbsp;·&nbsp;
                     <strong><?= $datosUsuario['horas_contrato_mes'] ?? 160 ?>h/mes</strong>
                    &nbsp;·&nbsp;
                     <strong><?= $datosUsuario['vacaciones_totales_anuales'] ?? 30 ?> días/año</strong>
                </p>
            </div>
        </div>

        <div class="datos-grid">
            <div class="dato-item">
                <div class="dato-label"> NIF</div>
                <div class="dato-valor"><?= htmlspecialchars($datosUsuario['NIF'] ?? 'No registrado') ?></div>
            </div>
            <div class="dato-item">
                <div class="dato-label"> Nº Afiliación</div>
                <div class="dato-valor"><?= htmlspecialchars($datosUsuario['Numero_Afiliciacion'] ?? 'No registrado') ?></div>
            </div>
            <div class="dato-item">
                <div class="dato-label"> Cuenta creada</div>
                <div class="dato-valor"><?= date('d/m/Y', strtotime($datosUsuario['created_at'] ?? 'now')) ?></div>
            </div>
            <div class="dato-item">
                <div class="dato-label"> Estado</div>
                <div class="dato-valor"><?= $datosUsuario['activo'] ? '✅ Activo' : '❌ Inactivo' ?></div>
            </div>
        </div>

        <div style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;">
            <button class="boton-accion" onclick="abrirModalPassword()"> Cambiar Contraseña</button>
            <button class="boton-accion" onclick="abrirModalContrato()" style="background:#28a745;"> Mi Contrato</button>
        </div>
    </div>

    <!-- ── Resumen Laboral ───────────────────────────────── -->
    <div class="perfil-card">
        <h3 style="margin:0 0 16px;font-size:17px;color:#333;">
             Mi Resumen Laboral — <?= date('Y') ?>
            <span style="font-size:13px;font-weight:400;color:#6c757d;margin-left:8px;"><?= date('F') ?></span>
        </h3>

        <div id="alertasDiv"></div>

        <!-- Widgets (skeleton mientras carga) -->
        <div class="resumen-grid" id="resumenGrid">
            <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="widget-card">
                <div class="skeleton" style="height:13px;width:55%;margin-bottom:8px;"></div>
                <div class="skeleton" style="height:36px;width:45%;"></div>
                <div class="skeleton" style="height:6px;width:100%;margin-top:10px;"></div>
            </div>
            <?php endfor; ?>
        </div>
    </div>

</div>

<!-- ── Modal contraseña ──────────────────────────────────── -->
<div id="modalPassword" class="modal-perfil">
    <div class="modal-content-perfil">
        <span class="close-modal" onclick="cerrarModalPassword()">&times;</span>
        <h3 style="margin-bottom:20px;color:#333;"> Cambiar Contraseña</h3>
        <form id="formCambiarPassword">
            <div class="form-group-perfil">
                <label>Contraseña Actual:</label>
                <input type="password" id="passwordActual" required placeholder="••••••••">
            </div>
            <div class="form-group-perfil">
                <label>Nueva Contraseña:</label>
                <input type="password" id="passwordNueva1" required minlength="6" placeholder="••••••••">
                <small style="color:#888;">Mínimo 6 caracteres</small>
            </div>
            <div class="form-group-perfil">
                <label>Repetir Nueva Contraseña:</label>
                <input type="password" id="passwordNueva2" required placeholder="••••••••">
            </div>
            <button type="submit" class="boton-accion" style="width:100%;">Actualizar Contraseña</button>
        </form>
        <div id="mensajePassword"></div>
    </div>
</div>

<!-- ── Modal contrato ────────────────────────────────────── -->
<div id="modalContrato" class="modal-perfil">
    <div class="modal-content-perfil">
        <span class="close-modal" onclick="cerrarModalContrato()">&times;</span>
        <h3 style="margin-bottom:6px;color:#333;"> Datos de mi Contrato</h3>
        <p style="color:#6c757d;font-size:13px;margin-bottom:20px;">
            Estos datos determinan cuántas vacaciones generas y cuándo se detectan horas extra.
        </p>
        <form id="formContrato">
            <div class="modal-field">
                <label>Tipo de Jornada:</label>
                <select id="ctr_tipo">
                    <option value="completa"  <?= ($datosUsuario['tipo_contrato']??'completa')==='completa'  ?'selected':'' ?>>Jornada Completa</option>
                    <option value="parcial"   <?= ($datosUsuario['tipo_contrato']??'')==='parcial'   ?'selected':'' ?>>Jornada Parcial</option>
                </select>
            </div>
            <div class="modal-field">
                <label>Horas contratadas al mes:</label>
                <input type="number" id="ctr_horas" min="1" max="240" step="1"
                       value="<?= $datosUsuario['horas_contrato_mes'] ?? 160 ?>">
                <small>
                    Jornada completa = 160h/mes · Media jornada = 80h/mes<br>
                    Este valor se usa para detectar horas extra (lo que supere este número).
                </small>
            </div>
            <div class="modal-field">
                <label>Días de vacaciones anuales:</label>
                <input type="number" id="ctr_vac" min="0" max="60" step="0.5"
                       value="<?= $datosUsuario['vacaciones_totales_anuales'] ?? 30 ?>">
                <small>
                    Jornada completa habitual = 30 días · Media jornada = 15 días<br>
                    Se descuentan automáticamente cuando el admin aprueba vacaciones.
                </small>
            </div>
            <button type="submit" class="boton-accion" style="width:100%;background:#28a745;"> Guardar</button>
        </form>
        <div id="mensajeContrato"></div>
    </div>
</div>

<script>
// ── Cargar resumen laboral ─────────────────────────────────────
async function cargarResumen() {
    try {
        const res  = await fetch('secciones/api/obtener_resumen_empleado.php');
        const data = await res.json();
        if (!data.success) { console.error(data.message); return; }

        const v  = data.vacaciones;
        const hm = data.horas_mes;
        const ha = data.horas_extra_anio;

        // ── Alertas ──────────────────────────────────────────
        let alertas = '';

        if (ha.excede_limite) {
            alertas += `<div class="alerta-laboral danger">
                 <div><strong>Límite de horas extra superado.</strong>
                Llevas <strong>${ha.acumuladas}h</strong> extra este año.
                El límite legal son <strong>${ha.limite}h/año</strong>. Consulta con tu responsable.</div>
            </div>`;
        } else if (hm.excede) {
            alertas += `<div class="alerta-laboral aviso">
                ⚠️ <div><strong>Has excedido tu jornada este mes en ${hm.horas_extra}h.</strong>
                Se computarán como horas extra. Llevas <strong>${ha.acumuladas}h</strong> extra acumuladas
                de ${ha.limite}h permitidas al año.</div>
            </div>`;
        }

        if (v.dias_disponibles <= 2 && v.dias_disponibles > 0) {
            alertas += `<div class="alerta-laboral aviso">
                 <div>Solo te quedan <strong>${v.dias_disponibles} días</strong> de vacaciones disponibles este año.</div>
            </div>`;
        }
        if (v.dias_pendientes > 0) {
            alertas += `<div class="alerta-laboral info">
                ⏳ <div>Tienes <strong>${v.dias_pendientes} días</strong> de vacaciones solicitados pendientes de aprobación.</div>
            </div>`;
        }

        document.getElementById('alertasDiv').innerHTML = alertas;

        // ── Clases de color según estado ─────────────────────
        const claseVac  = v.dias_disponibles <= 2 ? 'rojo' : v.dias_disponibles <= 5 ? 'naranja' : 'verde';
        const claseHora = ha.excede_limite ? 'rojo' : ha.porcentaje > 75 ? 'naranja' : '';
        const pctVac    = Math.min(100, v.porcentaje_usado);
        const pctHora   = Math.min(100, ha.porcentaje);
        const fillVac   = pctVac > 90 ? 'rojo' : pctVac > 70 ? 'naranja' : 'verde';
        const fillHora  = pctHora > 100 ? 'rojo' : pctHora > 75 ? 'naranja' : '';

        // ── Widgets ───────────────────────────────────────────
        document.getElementById('resumenGrid').innerHTML = `
            <div class="widget-card ${claseVac}">
                <div class="widget-icon"></div>
                <div class="widget-label">Vacaciones disponibles</div>
                <div class="widget-valor">${v.dias_disponibles}</div>
                <div class="widget-sub">días de ${v.total_anuales} anuales</div>
                <div class="progress-wrap">
                    <div class="progress-fill ${fillVac}" style="width:${pctVac}%"></div>
                </div>
            </div>

            <div class="widget-card">
                <div class="widget-icon">✅</div>
                <div class="widget-label">Vacaciones disfrutadas</div>
                <div class="widget-valor">${v.dias_usados}</div>
                <div class="widget-sub">días usados en ${v.anio}
                    ${v.dias_pendientes > 0 ? `<br><span style="color:#856404;">+ ${v.dias_pendientes} pendientes</span>` : ''}
                </div>
            </div>

            <div class="widget-card">
                <div class="widget-icon"></div>
                <div class="widget-label">Horas este mes</div>
                <div class="widget-valor ${hm.excede ? 'rojo' : ''}">${hm.horas_fichadas}</div>
                <div class="widget-sub">de ${hm.horas_contrato}h contratadas
                    ${hm.excede ? `<br><span style="color:#dc3545;">+${hm.horas_extra}h extra</span>` : ''}
                </div>
            </div>

            <div class="widget-card ${claseHora}">
                <div class="widget-icon"></div>
                <div class="widget-label">Horas extra año</div>
                <div class="widget-valor">${ha.acumuladas}</div>
                <div class="widget-sub">de ${ha.limite}h máximo legal</div>
                <div class="progress-wrap">
                    <div class="progress-fill ${fillHora}" style="width:${pctHora}%"></div>
                </div>
            </div>
        `;

    } catch (e) {
        console.error('Error cargando resumen:', e);
    }
}

cargarResumen();

// ── Modal contraseña ──────────────────────────────────────────
function abrirModalPassword()  { document.getElementById('modalPassword').style.display = 'block'; }
function cerrarModalPassword() {
    document.getElementById('modalPassword').style.display = 'none';
    document.getElementById('formCambiarPassword').reset();
    document.getElementById('mensajePassword').innerHTML = '';
}

document.getElementById('formCambiarPassword').onsubmit = async function(e) {
    e.preventDefault();
    const p1 = document.getElementById('passwordActual').value;
    const p2 = document.getElementById('passwordNueva1').value;
    const p3 = document.getElementById('passwordNueva2').value;
    const msg = document.getElementById('mensajePassword');

    if (p2 !== p3)    { msg.innerHTML = '<div class="mensaje-perfil error">❌ Las contraseñas no coinciden.</div>'; return; }
    if (p2.length < 6){ msg.innerHTML = '<div class="mensaje-perfil error">❌ Mínimo 6 caracteres.</div>'; return; }
    if (p1 === p2)    { msg.innerHTML = '<div class="mensaje-perfil error">❌ La nueva no puede ser igual a la actual.</div>'; return; }

    msg.innerHTML = '<div class="mensaje-perfil" style="background:#e3f2fd;color:#0277bd;">⏳ Verificando...</div>';
    const res  = await fetch('secciones/cambiar_password_perfil.php', {
        method: 'POST', headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ password_actual: p1, password_nueva: p2 })
    });
    const data = await res.json();
    msg.innerHTML = data.success
        ? '<div class="mensaje-perfil success">✅ Contraseña actualizada.</div>'
        : `<div class="mensaje-perfil error">❌ ${data.message}</div>`;
    if (data.success) setTimeout(cerrarModalPassword, 1500);
};

// ── Modal contrato ────────────────────────────────────────────
function abrirModalContrato()  { document.getElementById('modalContrato').style.display = 'block'; }
function cerrarModalContrato() {
    document.getElementById('modalContrato').style.display = 'none';
    document.getElementById('mensajeContrato').innerHTML = '';
}

// Autoajustar horas y vacaciones según tipo de contrato
document.getElementById('ctr_tipo').addEventListener('change', function() {
    if (this.value === 'completa') {
        document.getElementById('ctr_horas').value = 160;
        document.getElementById('ctr_vac').value   = 30;
    } else {
        document.getElementById('ctr_horas').value = 80;
        document.getElementById('ctr_vac').value   = 15;
    }
});

document.getElementById('formContrato').onsubmit = async function(e) {
    e.preventDefault();
    const msg = document.getElementById('mensajeContrato');
    msg.innerHTML = '<div class="mensaje-perfil" style="background:#e3f2fd;color:#0277bd;">⏳ Guardando...</div>';

    const res  = await fetch('secciones/api/actualizar_contrato.php', {
        method: 'POST', headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            tipo_contrato:             document.getElementById('ctr_tipo').value,
            horas_contrato_mes:        parseInt(document.getElementById('ctr_horas').value),
            vacaciones_totales_anuales: parseFloat(document.getElementById('ctr_vac').value),
        })
    });
    const data = await res.json();
    msg.innerHTML = data.success
        ? '<div class="mensaje-perfil success">✅ Contrato actualizado. Recargando...</div>'
        : `<div class="mensaje-perfil error">❌ ${data.message}</div>`;
    if (data.success) setTimeout(() => location.reload(), 1200);
};

// ── Cambiar foto ──────────────────────────────────────────────
document.getElementById('inputFoto').onchange = async function() {
    const file = this.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) { alert('Selecciona una imagen válida.'); return; }
    if (file.size > 5 * 1024 * 1024)    { alert('Máximo 5MB.');                  return; }
    const formData = new FormData();
    formData.append('foto', file);
    const res  = await fetch('secciones/cambiar_foto_perfil.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
        document.getElementById('fotoPerfil').src = data.nueva_foto + '?t=' + Date.now();
        alert('✅ Foto actualizada.');
    } else { alert('❌ ' + data.message); }
};

window.onclick = function(e) {
    ['modalPassword','modalContrato'].forEach(id => {
        if (e.target === document.getElementById(id))
            document.getElementById(id).style.display = 'none';
    });
};
</script>