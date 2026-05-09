<?php
/*
 * perfil.php — Vista del perfil de usuario
 * Incluida por panel.php (seccion=perfil). Admin y empleado.
 * Muestra los datos personales del usuario en sesión (nombre, email, NIF, etc.).
 * Permite cambiar la foto de perfil (fetch a cambiar_foto_perfil.php)
 * y cambiar la contraseña (fetch a cambiar_password_perfil.php).
 * También muestra un resumen de actividad (fetch a api/obtener_resumen_empleado.php).
 */
// Variables de sesión necesarias para este archivo
$usuario   = $_SESSION['usuario'];
$empresa   = $_SESSION['empresa_activa'];
$idUsuario = $_SESSION['usuario']['id'];
$esAdmin   = $_SESSION['es_admin'] ?? 0;

$id_usuario = $usuario['id'];
$sqlUsuario = "SELECT * FROM USUARIO WHERE id_usuario = :id_usuario";
$stmtUsuario = $pdo->prepare($sqlUsuario);
$stmtUsuario->execute(['id_usuario' => $id_usuario]);
$datosUsuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
if (!$datosUsuario) die("Error al cargar datos del usuario.");

$imagenPerfil = $datosUsuario['foto_perfil'] ?? 'secciones/uploads/perfil_default.jpg';

$labelTipoContrato = [
    'completa' => 'Jornada Completa',
    'parcial'  => 'Jornada Parcial',
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
                        title="Cambiar foto"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/></svg></button>
                <form id="formFoto" enctype="multipart/form-data" style="display:none;">
                    <input type="file" id="inputFoto" name="foto" accept="image/*" class="input-file-custom">
                </form>
            </div>

            <div class="usuario-info">
                <h2><?= htmlspecialchars($datosUsuario['nombre'] . ' ' . $datosUsuario['apellidos']) ?></h2>
                <p> <?= htmlspecialchars($datosUsuario['email']) ?></p>
                <p> Empresa: <strong><?= htmlspecialchars($usuario['empresas'][0]['nombre'] ?? 'N/A') ?></strong></p>
            </div>
        </div>

        <div class="datos-grid">
            <div class="dato-item">
                <div class="dato-label"> NIF</div>
                <div class="dato-valor"><?= htmlspecialchars($datosUsuario['NIF'] ?? 'No registrado') ?></div>
            </div>
            <div class="dato-item">
                <div class="dato-label"> Nº Afiliación S.S.</div>
                <div class="dato-valor"><?= htmlspecialchars($datosUsuario['Numero_Afiliciacion'] ?? 'No registrado') ?></div>
            </div>
            <div class="dato-item">
                <div class="dato-label"> Alta en sistema</div>
                <div class="dato-valor"><?= date('d/m/Y', strtotime($datosUsuario['created_at'] ?? 'now')) ?></div>
            </div>
            <div class="dato-item">
                <div class="dato-label"> Estado</div>
                <div class="dato-valor"><?= $datosUsuario['activo'] ? '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em" style="vertical-align:-0.125em"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg> Activo' : '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em" style="vertical-align:-0.125em"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg> Inactivo' ?></div>
            </div>
        </div>

        <!-- Info del contrato (solo lectura) -->
        <div class="contrato-info-box">
            <h4> Mi Contrato</h4>
            <div class="contrato-info-grid">
                <div class="contrato-info-item">
                    <span class="ci-label">Tipo de jornada</span>
                    <span class="ci-value">
                        <?= $labelTipoContrato[$datosUsuario['tipo_contrato'] ?? 'completa'] ?? 'Jornada Completa' ?>
                    </span>
                </div>
                <div class="contrato-info-item">
                    <span class="ci-label">Horas contratadas/mes</span>
                    <span class="ci-value"><?= $datosUsuario['horas_contrato_mes'] ?? 160 ?>h</span>
                </div>
                <div class="contrato-info-item">
                    <span class="ci-label">Vacaciones anuales</span>
                    <span class="ci-value"><?= $datosUsuario['vacaciones_totales_anuales'] ?? 30 ?> días</span>
                </div>
                <div class="contrato-info-item">
                    <span class="ci-label">Límite horas extra/año</span>
                    <span class="ci-value"><?= $datosUsuario['horas_extra_anuales_limite'] ?? 80 ?>h</span>
                </div>
            </div>
            <p class="contrato-nota">
                 Los datos del contrato son gestionados por el administrador. Si hay algún error, contacta con RRHH.
            </p>
        </div>

        <div style="margin-top:20px;">
            <button class="boton-accion" onclick="abrirModalPassword()"> Cambiar Contraseña</button>
        </div>
    </div>

    <!-- ── Resumen Laboral ───────────────────────────────── -->
    <div class="perfil-card">
        <h3 style="margin:0 0 16px;font-size:17px;color:#333;">
             Mi Resumen Laboral — <?= date('Y') ?>
            <span style="font-size:13px;font-weight:400;color:#6c757d;margin-left:8px;"><?= date('F') ?></span>
        </h3>

        <div id="alertasDiv"></div>

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

<script>
// Cargar resumen laboral
async function cargarResumen() {
    try {
        const res  = await fetch('secciones/api/obtener_resumen_empleado.php');
        const data = await res.json();
        if (!data.success) { console.error(data.message); return; }

        const v  = data.vacaciones;
        const hm = data.horas_mes;
        const ha = data.horas_extra_anio;

        let alertas = '';
        if (ha.excede_limite) {
            alertas += `<div class="alerta-laboral danger"> <div><strong>Límite de horas extra superado.</strong>
                Llevas <strong>${ha.acumuladas}h</strong> extra este año. El límite legal son <strong>${ha.limite}h/año</strong>.
                Habla con tu responsable.</div></div>`;
        } else if (hm.excede) {
            alertas += `<div class="alerta-laboral aviso"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em" style="vertical-align:-0.125em"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg> <div><strong>Has excedido tu jornada este mes en ${hm.horas_extra}h.</strong>
                Se computarán como horas extra. Llevas <strong>${ha.acumuladas}h</strong> de ${ha.limite}h permitidas al año.</div></div>`;
        }
        if (v.dias_disponibles <= 2 && v.dias_disponibles > 0) {
            alertas += `<div class="alerta-laboral aviso"> <div>Solo te quedan <strong>${v.dias_disponibles} días</strong> de vacaciones disponibles este año.</div></div>`;
        }
        if (v.dias_pendientes > 0) {
            alertas += `<div class="alerta-laboral info"> <div>Tienes <strong>${v.dias_pendientes} días</strong> de vacaciones solicitados pendientes de aprobación.</div></div>`;
        }
        document.getElementById('alertasDiv').innerHTML = alertas;

        const claseVac  = v.dias_disponibles <= 2 ? 'rojo' : v.dias_disponibles <= 5 ? 'naranja' : 'verde';
        const claseHora = ha.excede_limite ? 'rojo' : ha.porcentaje > 75 ? 'naranja' : '';
        const pctVac    = Math.min(100, v.porcentaje_usado);
        const pctHora   = Math.min(100, ha.porcentaje);
        const fillVac   = pctVac > 90 ? 'rojo' : pctVac > 70 ? 'naranja' : 'verde';
        const fillHora  = pctHora > 100 ? 'rojo' : pctHora > 75 ? 'naranja' : '';

        document.getElementById('resumenGrid').innerHTML = `
            <div class="widget-card ${claseVac}">
                <div class="widget-icon"></div>
                <div class="widget-label">Vacaciones disponibles</div>
                <div class="widget-valor">${v.dias_disponibles}</div>
                <div class="widget-sub">días de ${v.total_anuales} anuales</div>
                <div class="progress-wrap"><div class="progress-fill ${fillVac}" style="width:${pctVac}%"></div></div>
            </div>
            <div class="widget-card">
                <div class="widget-icon"></div>
                <div class="widget-label">Vacaciones disfrutadas</div>
                <div class="widget-valor">${v.dias_usados}</div>
                <div class="widget-sub">días en ${v.anio}${v.dias_pendientes > 0 ? `<br><span style="color:#856404;">+${v.dias_pendientes} pendientes</span>` : ''}</div>
            </div>
            <div class="widget-card">
                <div class="widget-icon"></div>
                <div class="widget-label">Horas este mes</div>
                <div class="widget-valor ${hm.excede ? 'rojo' : ''}">${hm.horas_fichadas}</div>
                <div class="widget-sub">de ${hm.horas_contrato}h contratadas${hm.excede ? `<br><span style="color:#dc3545;">+${hm.horas_extra}h extra</span>` : ''}</div>
            </div>
            <div class="widget-card ${claseHora}">
                <div class="widget-icon"></div>
                <div class="widget-label">Horas extra año</div>
                <div class="widget-valor">${ha.acumuladas}</div>
                <div class="widget-sub">de ${ha.limite}h máximo legal</div>
                <div class="progress-wrap"><div class="progress-fill ${fillHora}" style="width:${pctHora}%"></div></div>
            </div>`;
    } catch (e) { console.error('Error cargando resumen:', e); }
}
cargarResumen();

// Modal contraseña
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
    const res  = await fetch('secciones/perfil/cambiar_password_perfil.php', {
        method: 'POST', headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ password_actual: p1, password_nueva: p2 })
    });
    const data = await res.json();
    msg.innerHTML = data.success
        ? '<div class="mensaje-perfil success">✅ Contraseña actualizada.</div>'
        : `<div class="mensaje-perfil error">❌ ${data.message}</div>`;
    if (data.success) setTimeout(cerrarModalPassword, 1500);
};

// Cambiar foto
document.getElementById('inputFoto').onchange = async function() {
    const file = this.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) { alert('Selecciona una imagen válida.'); return; }
    if (file.size > 5 * 1024 * 1024)    { alert('Máximo 5MB.'); return; }
    const formData = new FormData();
    formData.append('foto', file);
    const res  = await fetch('secciones/perfil/cambiar_foto_perfil.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
        document.getElementById('fotoPerfil').src = data.nueva_foto + '?t=' + Date.now();
        alert('✅ Foto actualizada.');
    } else { alert('❌ ' + data.message); }
};

window.onclick = function(e) {
    if (e.target === document.getElementById('modalPassword'))
        document.getElementById('modalPassword').style.display = 'none';
};
</script>