<?php
/*
 * horario_admin_calendario.php — Vista del calendario de horarios para admin
 * Incluida por panel.php (seccion=horario&vista=calendario). Solo admin.
 * Permite ver y editar directamente el horario de cualquier empleado día a día.
 * Los cambios se guardan sin pasar por solicitudes (acceso directo a HORARIOS).
 * Hace fetch a: horario/horario_guardar.php, horario/horario_eliminar.php,
 *               horario/horario_guardar_masivo_admin.php, api/obtener_horarios_admin.php.
 */

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) die('No autorizado');

$empresa   = $_SESSION['empresa_activa'];
$idAdmin   = $_SESSION['usuario']['id'];

// Empleados de la empresa para el selector
$stmtEmps = $pdo->prepare("
    SELECT U.id_usuario, U.nombre, U.apellidos
    FROM USUARIO U
    JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
    WHERE EU.id_empresa = ? AND EU.activo = 1
    ORDER BY U.nombre, U.apellidos
");
$stmtEmps->execute([$empresa]);
$empleados = $stmtEmps->fetchAll(PDO::FETCH_ASSOC);

$idEmpleadoSel = $_GET['empleado'] ?? ($empleados[0]['id_usuario'] ?? null);
?>
<link rel="stylesheet" href="css/horario_empleado.css">


<!-- Banner informativo -->
<div class="hac-banner">
     <strong>Modo administrador:</strong> Los cambios se guardan directamente en el horario del empleado, sin necesidad de validación.
</div>

<!-- Selector de empleado -->
<div class="hac-selector">
    <label for="selEmpleadoCal"> Empleado:</label>
    <select id="selEmpleadoCal" onchange="cambiarEmpleadoCal(this.value)">
        <?php foreach ($empleados as $emp): ?>
            <option value="<?= $emp['id_usuario'] ?>"
                    <?= $idEmpleadoSel == $emp['id_usuario'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <span id="hac-empleado-label" style="color:#667eea;font-weight:600;font-size:13px;"></span>
</div>

<!-- Calendario reutilizado con modo admin -->
<div class="horario-container">
    <div class="toolbar">
        <div class="toolbar-left">
            <div class="view-toggle">
                <button class="active">📅 Mes</button>
            </div>
            <div class="month-nav">
                <button onclick="mesAnteriorAdmin()">◀</button>
                <span class="month-title" id="monthTitleAdmin"></span>
                <button onclick="mesSiguienteAdmin()">▶</button>
            </div>
        </div>
        <div class="toolbar-right">
            <button class="btn-toolbar btn-success" onclick="abrirModalMasivoAdmin()"> Añadir en bloque</button>
        </div>
    </div>

    <div class="legend">
        <div class="legend-item"><div class="legend-color" style="background:#1976d2;"></div><span>Trabajo</span></div>
        <div class="legend-item"><div class="legend-color" style="background:#388e3c;"></div><span>Vacaciones</span></div>
        <div class="legend-item"><div class="legend-color" style="background:#f57c00;"></div><span>Médico</span></div>
        <div class="legend-item"><div class="legend-color" style="background:#7b1fa2;"></div><span>Libre</span></div>
        <div class="legend-item"><div class="legend-color" style="background:#c62828;"></div><span>Festivo</span></div>
        <div class="legend-item"><div class="legend-color" style="background:#3f51b5;"></div><span>Partida M</span></div>
        <div class="legend-item"><div class="legend-color" style="background:#e91e63;"></div><span>Partida T</span></div>
    </div>

    <div class="calendar-container">
        <div class="calendar-month" id="calendarMonthAdmin"></div>
    </div>
</div>

<!-- ══ MODAL EDITAR DÍA (admin) ════════════════════════════════ -->
<div id="modalEditarDiaAdmin" class="modal-horario">
    <div class="modal-content-horario">
        <div class="modal-header-horario">
            <h3 id="tituloModalAdmin"> Editar jornada</h3>
            <button class="close-btn-horario" onclick="cerrarModalAdmin()">×</button>
        </div>
        <div class="modal-body-horario">
            <input type="hidden" id="adm_id_horario">
            <input type="hidden" id="adm_fecha">

            <div class="form-group-horario">
                <label>Tipo de jornada</label>
                <select class="form-control-horario" id="adm_tipo" onchange="cambiarTipoAdmin()">
                    <option value="TRABAJO">Jornada continua</option>
                    <option value="PARTIDA_M">⬆ Partida — Mañana</option>
                    <option value="PARTIDA_T">⬇ Partida — Tarde</option>
                    <option value="VACACIONES"> Vacaciones</option>
                    <option value="MEDICO"> Médico</option>
                    <option value="LIBRE"> Libre</option>
                    <option value="FESTIVO"> Festivo</option>
                </select>
            </div>

            <div id="admHorasDiv">
                <div class="form-row">
                    <div class="form-group-horario">
                        <label>Hora inicio</label>
                        <input type="time" class="form-control-horario" id="adm_inicio" value="09:00">
                    </div>
                    <div class="form-group-horario">
                        <label>Hora fin</label>
                        <input type="time" class="form-control-horario" id="adm_fin" value="17:00">
                    </div>
                </div>
            </div>

            <div class="form-group-horario">
                <label>Observaciones</label>
                <textarea class="form-control-horario" id="adm_obs" rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer-horario">
            <button class="btn-cancel-horario" onclick="cerrarModalAdmin()">Cancelar</button>
            <button class="btn-success-horario" onclick="guardarDiaAdmin()"> Guardar</button>
        </div>
    </div>
</div>

<!-- ══ MODAL MASIVO ADMIN ════════════════════════════════════════ -->
<div id="modalMasivoAdmin" class="modal-horario">
    <div class="modal-content-horario">
        <div class="modal-header-horario">
            <h3> Añadir horarios en bloque</h3>
            <button class="close-btn-horario" onclick="cerrarModalMasivoAdmin()">×</button>
        </div>
        <div class="modal-body-horario">
            <div class="form-row">
                <div class="form-group-horario">
                    <label>Desde *</label>
                    <input type="date" class="form-control-horario" id="admMasivo_inicio">
                </div>
                <div class="form-group-horario">
                    <label>Hasta *</label>
                    <input type="date" class="form-control-horario" id="admMasivo_fin">
                </div>
            </div>
            <div class="form-group-horario">
                <label>Días de la semana:</label>
                <div class="dias-checkbox">
                    <label><input type="checkbox" value="1" checked class="admDia-cb"> Lun</label>
                    <label><input type="checkbox" value="2" checked class="admDia-cb"> Mar</label>
                    <label><input type="checkbox" value="3" checked class="admDia-cb"> Mié</label>
                    <label><input type="checkbox" value="4" checked class="admDia-cb"> Jue</label>
                    <label><input type="checkbox" value="5" checked class="admDia-cb"> Vie</label>
                    <label><input type="checkbox" value="6" class="admDia-cb"> Sáb</label>
                    <label><input type="checkbox" value="7" class="admDia-cb"> Dom</label>
                </div>
            </div>
            <div class="form-group-horario">
                <label>Tipo *</label>
                <select class="form-control-horario" id="admMasivo_tipo" onchange="toggleHorasAdmMasivo()">
                    <option value="TRABAJO">Jornada continua</option>
                    <option value="PARTIDA_M">⬆ Partida Mañana</option>
                    <option value="PARTIDA_T">⬇ Partida Tarde</option>
                    <option value="VACACIONES"> Vacaciones</option>
                    <option value="MEDICO"> Médico</option>
                    <option value="LIBRE"> Libre</option>
                    <option value="FESTIVO"> Festivo</option>
                </select>
            </div>
            <div id="admMasivoHorasDiv">
                <div class="form-row">
                    <div class="form-group-horario">
                        <label>Inicio:</label>
                        <input type="time" class="form-control-horario" id="admMasivo_hi" value="09:00">
                    </div>
                    <div class="form-group-horario">
                        <label>Fin:</label>
                        <input type="time" class="form-control-horario" id="admMasivo_hf" value="17:00">
                    </div>
                </div>
            </div>
            <div class="form-group-horario">
                <label>Observaciones:</label>
                <textarea class="form-control-horario" id="admMasivo_obs" rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer-horario">
            <button class="btn-cancel-horario" onclick="cerrarModalMasivoAdmin()">Cancelar</button>
            <button class="btn-success-horario" onclick="guardarMasivoAdmin()"> Guardar</button>
        </div>
    </div>
</div>

<div class="loading-overlay" id="loadingOverlayAdmin"><div class="spinner"></div></div>

<script>
// ── Estado ────────────────────────────────────────────────────
let mesAdmin    = new Date();
let horAdminData = {};
let empSelAdmin  = <?= json_encode((int)$idEmpleadoSel) ?>;

// ── Navegación ────────────────────────────────────────────────
function mesAnteriorAdmin()  { mesAdmin.setMonth(mesAdmin.getMonth()-1); cargarCalAdmin(); }
function mesSiguienteAdmin() { mesAdmin.setMonth(mesAdmin.getMonth()+1); cargarCalAdmin(); }

function cambiarEmpleadoCal(idEmp) {
    empSelAdmin = parseInt(idEmp);
    cargarCalAdmin();
}

// ── Cargar datos ──────────────────────────────────────────────
async function cargarCalAdmin() {
    mostrarLoadingAdmin(true);

    const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                   'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    document.getElementById('monthTitleAdmin').textContent =
        `${meses[mesAdmin.getMonth()]} ${mesAdmin.getFullYear()}`;

    const mes  = mesAdmin.getMonth() + 1;
    const anio = mesAdmin.getFullYear();

    try {
        // Reutilizamos obtener_horarios.php pasando id_usuario del empleado seleccionado
        const r = await fetch(
            `secciones/api/obtener_horarios_admin.php?mes=${mes}&anio=${anio}&id_usuario=${empSelAdmin}`
        );
        const data = await r.json();
        if (data.success) {
            horAdminData = data.calendario || {};
            generarCalAdmin();
        } else {
            alert('Error cargando horarios: ' + data.message);
        }
    } catch(e) {
        alert('Error de conexión');
    } finally {
        mostrarLoadingAdmin(false);
    }
}

// ── Generar calendario ────────────────────────────────────────
function generarCalAdmin() {
    const container = document.getElementById('calendarMonthAdmin');
    const mes  = mesAdmin.getMonth();
    const anio = mesAdmin.getFullYear();
    container.innerHTML = '';

    const dias = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
    dias.forEach(d => {
        const h = document.createElement('div');
        h.className = 'day-header';
        h.textContent = d;
        container.appendChild(h);
    });

    const primerDia = new Date(anio, mes, 1);
    const diasMes   = new Date(anio, mes+1, 0).getDate();
    let dow = primerDia.getDay(); if (dow === 0) dow = 7;

    const diasAnt = new Date(anio, mes, 0).getDate();
    for (let i = dow-1; i > 0; i--) {
        const c = document.createElement('div');
        c.className = 'day-cell other-month';
        c.innerHTML = `<div class="day-number">${diasAnt-i+1}</div>`;
        container.appendChild(c);
    }

    const hoy = new Date().toISOString().split('T')[0];

    for (let dia = 1; dia <= diasMes; dia++) {
        const fecha = `${anio}-${String(mes+1).padStart(2,'0')}-${String(dia).padStart(2,'0')}`;
        const cell  = document.createElement('div');
        cell.className = 'day-cell' + (fecha === hoy ? ' today-highlight' : '');

        const num = document.createElement('div');
        num.className   = 'day-number';
        num.textContent = dia;
        cell.appendChild(num);

        const eventos = horAdminData[fecha] || [];
        eventos.forEach(ev => {
            const block = document.createElement('div');
            const clases = {
                TRABAJO:'schedule-trabajo', VACACIONES:'schedule-vacaciones',
                MEDICO:'schedule-medico',   LIBRE:'schedule-libre',
                FESTIVO:'schedule-festivo', PARTIDA_M:'schedule-partida-m',
                PARTIDA_T:'schedule-partida-t',
            };
            block.className = `schedule-block ${clases[ev.tipo_jornada] || 'schedule-trabajo'}`;
            block.style.position = 'relative';

            let txt = ev.tipo_jornada;
            if (ev.hora_inicio && ev.hora_fin) {
                txt = ev.hora_inicio.substr(0,5) + '-' + ev.hora_fin.substr(0,5);
            } else {
                const labels = {VACACIONES:'',MEDICO:'',LIBRE:'',FESTIVO:'',PARTIDA_M:'↑',PARTIDA_T:'↓'};
                txt = (labels[ev.tipo_jornada] || '') + ' ' + ev.tipo_jornada.charAt(0) + ev.tipo_jornada.slice(1).toLowerCase();
            }

            const span = document.createElement('span');
            span.textContent = txt;
            block.appendChild(span);

            // Botón eliminar
            const btnDel = document.createElement('button');
            btnDel.className = 'btn-eliminar-temporal';
            btnDel.innerHTML = '×';
            btnDel.onclick = (e) => {
                e.stopPropagation();
                if (confirm('¿Eliminar este horario?')) eliminarHorarioAdmin(ev.id_horario);
            };
            block.appendChild(btnDel);

            block.onclick = (e) => { e.stopPropagation(); editarDiaAdmin(fecha, ev); };
            cell.appendChild(block);
        });

        // Botón añadir
        const btnAdd = document.createElement('div');
        btnAdd.className   = 'btn-add-inline';
        btnAdd.textContent = '+';
        btnAdd.title       = 'Añadir jornada';
        btnAdd.onclick     = (e) => { e.stopPropagation(); editarDiaAdmin(fecha, null); };
        cell.appendChild(btnAdd);

        container.appendChild(cell);
    }

    // Completar semana
    const usados = (container.children.length - 7);
    const resto  = 7 - (usados % 7);
    if (resto < 7) {
        for (let i = 1; i <= resto; i++) {
            const c = document.createElement('div');
            c.className = 'day-cell other-month';
            c.innerHTML = `<div class="day-number">${i}</div>`;
            container.appendChild(c);
        }
    }
}

// ── Modal editar/añadir día ───────────────────────────────────
function editarDiaAdmin(fecha, ev) {
    document.getElementById('adm_id_horario').value = ev ? ev.id_horario : '';
    document.getElementById('adm_fecha').value      = fecha;
    document.getElementById('adm_tipo').value       = ev ? ev.tipo_jornada : 'TRABAJO';
    document.getElementById('adm_inicio').value     = ev && ev.hora_inicio ? ev.hora_inicio.substr(0,5) : '09:00';
    document.getElementById('adm_fin').value        = ev && ev.hora_fin    ? ev.hora_fin.substr(0,5)    : '17:00';
    document.getElementById('adm_obs').value        = ev ? (ev.observaciones || '') : '';
    document.getElementById('tituloModalAdmin').textContent = ev ? ' Editar jornada' : ' Añadir jornada';
    cambiarTipoAdmin();
    document.getElementById('modalEditarDiaAdmin').style.display = 'flex';
}

function cerrarModalAdmin() {
    document.getElementById('modalEditarDiaAdmin').style.display = 'none';
}

function cambiarTipoAdmin() {
    const tipo = document.getElementById('adm_tipo').value;
    const conHoras = ['TRABAJO','MEDICO','PARTIDA_M','PARTIDA_T'].includes(tipo);
    document.getElementById('admHorasDiv').style.display = conHoras ? 'block' : 'none';
}

// ── Guardar día (directo a HORARIOS) ─────────────────────────
async function guardarDiaAdmin() {
    const fecha    = document.getElementById('adm_fecha').value;
    const tipo     = document.getElementById('adm_tipo').value;
    const idHor    = document.getElementById('adm_id_horario').value || null;
    const conHoras = document.getElementById('admHorasDiv').style.display !== 'none';

    mostrarLoadingAdmin(true);
    try {
        const r = await fetch('secciones/horario/horario_guardar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_horario:    idHor,
                id_usuario:    empSelAdmin,
                id_empresa:    <?= $empresa ?>,
                fecha,
                tipo_jornada:  tipo,
                id_tipo_custom: null,
                hora_inicio:   conHoras ? document.getElementById('adm_inicio').value : null,
                hora_fin:      conHoras ? document.getElementById('adm_fin').value    : null,
                observaciones: document.getElementById('adm_obs').value || null,
            }),
        });
        const data = await r.json();
        if (data.success) { cerrarModalAdmin(); cargarCalAdmin(); }
        else alert('❌ ' + data.message);
    } catch(e) {
        alert('❌ Error de conexión');
    } finally {
        mostrarLoadingAdmin(false);
    }
}

// ── Eliminar ──────────────────────────────────────────────────
async function eliminarHorarioAdmin(idHorario) {
    mostrarLoadingAdmin(true);
    try {
        const r = await fetch('secciones/horario/horario_eliminar.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({id_horario: idHorario}),
        });
        const d = await r.json();
        if (d.success) cargarCalAdmin();
        else alert('❌ ' + d.message);
    } catch(e) { alert('❌ Error de conexión'); }
    finally { mostrarLoadingAdmin(false); }
}

// ── Modal masivo ──────────────────────────────────────────────
function abrirModalMasivoAdmin() {
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('admMasivo_inicio').value = hoy;
    document.getElementById('admMasivo_fin').value    = hoy;
    toggleHorasAdmMasivo();
    document.getElementById('modalMasivoAdmin').style.display = 'flex';
}
function cerrarModalMasivoAdmin() {
    document.getElementById('modalMasivoAdmin').style.display = 'none';
}
function toggleHorasAdmMasivo() {
    const tipo = document.getElementById('admMasivo_tipo').value;
    const conH = ['TRABAJO','MEDICO','PARTIDA_M','PARTIDA_T'].includes(tipo);
    document.getElementById('admMasivoHorasDiv').style.display = conH ? 'block' : 'none';
}

async function guardarMasivoAdmin() {
    const fi   = document.getElementById('admMasivo_inicio').value;
    const ff   = document.getElementById('admMasivo_fin').value;
    const tipo = document.getElementById('admMasivo_tipo').value;
    const dias = Array.from(document.querySelectorAll('.admDia-cb:checked')).map(cb=>parseInt(cb.value));
    const conH = document.getElementById('admMasivoHorasDiv').style.display !== 'none';

    if (!fi || !ff) { alert('Selecciona las fechas'); return; }
    if (!dias.length) { alert('Selecciona al menos un día'); return; }

    mostrarLoadingAdmin(true);
    try {
        const r = await fetch('secciones/horario/horario_guardar_masivo_admin.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({
                empleados:    [empSelAdmin],
                fecha_inicio: fi, fecha_fin: ff, dias,
                tipo_jornada: tipo, id_tipo_custom: null,
                hora_inicio: conH ? document.getElementById('admMasivo_hi').value : null,
                hora_fin:    conH ? document.getElementById('admMasivo_hf').value : null,
                observaciones: document.getElementById('admMasivo_obs').value || null,
                id_empresa:  <?= $empresa ?>,
            }),
        });
        const d = await r.json();
        if (d.success) {
            alert(`✅ ${d.insertados} registros guardados`);
            cerrarModalMasivoAdmin();
            cargarCalAdmin();
        } else alert('❌ ' + d.message);
    } catch(e) { alert('❌ Error de conexión'); }
    finally { mostrarLoadingAdmin(false); }
}

// ── Helpers ───────────────────────────────────────────────────
function mostrarLoadingAdmin(v) {
    const el = document.getElementById('loadingOverlayAdmin');
    if (v) el.classList.add('active'); else el.classList.remove('active');
}

window.onclick = e => {
    if (e.target.id === 'modalEditarDiaAdmin') cerrarModalAdmin();
    if (e.target.id === 'modalMasivoAdmin')    cerrarModalMasivoAdmin();
};

// Arrancar
document.addEventListener('DOMContentLoaded', cargarCalAdmin);
</script>