<!--
  horario_empleado.php — Vista del calendario de horarios del empleado
  Incluida desde horario.php cuando el usuario es empleado.
  Muestra un calendario mensual interactivo donde el empleado puede
  introducir sus horarios diarios y enviarlos a validar al admin.
  Carga horario_calendario.js (lógica del calendario) y permite solicitar ausencias.
-->
<link rel="stylesheet" href="css/horario_empleado.css">

<div class="horario-container">
    <!-- BARRA SUPERIOR -->
    <div class="toolbar">
        <div class="toolbar-left">
            <div class="view-toggle">
                <button class="active" onclick="cambiarVista('mes')"> Mes</button>
            </div>
            <div class="month-nav">
                <button onclick="mesAnterior()">◀</button>
                <span class="month-title" id="monthTitle"></span>
                <button onclick="mesSiguiente()">▶</button>
            </div>
        </div>

        <div class="toolbar-right">
            <button class="btn-toolbar btn-ausencia"  onclick="abrirModalAusencia()"> Solicitar ausencia</button>
            <!--
                <button class="btn-toolbar btn-success"   id="btnMasivo" onclick="abrirModalMasivo()"> Añadir múltiples días</button>

                <button class="btn-toolbar btn-secondary" onclick="copiarMesAnterior()"> Copiar mes anterior</button>
            -->
            <button class="btn-toolbar btn-primary"   onclick="enviarValidacion()"> Enviar a validar</button>
        </div>
    </div>

    <!-- LEYENDA -->
    <div class="legend">
        <div class="legend-item">
            <div class="legend-color" style="background:#1976d2;"></div><span>Jornada continua</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background:#3f51b5;"></div><span>↑ Partida 1º tramo</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background:#e91e63;"></div><span>↓ Partida 2º tramo</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background:#388e3c;"></div><span>Vacaciones</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background:#f57c00;"></div><span>Médico</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background:#7b1fa2;"></div><span>Día libre</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background:#c62828;"></div><span>Festivo</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background:#fef3c7;border:1px solid #f59e0b;"></div><span>⏳ Pendiente</span>
        </div>
    </div>

    <!-- CALENDARIO -->
    <div class="calendar-container">
        <div class="calendar-month" id="calendarMonth"></div>
    </div>
</div>

<!-- ══ MODAL SOLICITAR AUSENCIA ════════════════════════════════ -->
<div id="modalAusencia" class="modal-horario">
    <div class="modal-content-horario">
        <div class="modal-header-horario">
            <h3> Solicitar Ausencia</h3>
            <button class="close-btn-horario" onclick="cerrarModalAusencia()">×</button>
        </div>
        <div class="modal-body-horario">
            <div class="form-group-horario">
                <label>Tipo de ausencia *</label>
                <select class="form-control-horario" id="aus_tipo" onchange="cambiarTipoAusencia()">
                    <option value="VACACIONES"> Vacaciones</option>
                    <option value="MEDICO"> Cita médica</option>
                    <option value="LIBRE"> Día libre</option>
                    <option value="FESTIVO"> Festivo local</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group-horario">
                    <label>Desde *</label>
                    <input type="date" class="form-control-horario" id="aus_inicio">
                </div>
                <div class="form-group-horario">
                    <label>Hasta</label>
                    <input type="date" class="form-control-horario" id="aus_fin">
                    <small style="color:#888;font-size:11px;">Deja igual que "Desde" para un solo día</small>
                </div>
            </div>

            <!-- Días de la semana (solo para rangos largos) -->
            <div class="form-group-horario" id="aus_dias_grupo">
                <label>Incluir días:</label>
                <div class="dias-checkbox">
                    <label><input type="checkbox" value="1" checked class="aus-dia-cb"> Lun</label>
                    <label><input type="checkbox" value="2" checked class="aus-dia-cb"> Mar</label>
                    <label><input type="checkbox" value="3" checked class="aus-dia-cb"> Mié</label>
                    <label><input type="checkbox" value="4" checked class="aus-dia-cb"> Jue</label>
                    <label><input type="checkbox" value="5" checked class="aus-dia-cb"> Vie</label>
                    <label><input type="checkbox" value="6" class="aus-dia-cb"> Sáb</label>
                    <label><input type="checkbox" value="7" class="aus-dia-cb"> Dom</label>
                </div>
            </div>

            <div class="form-group-horario">
                <label>Observaciones</label>
                <textarea class="form-control-horario" id="aus_obs" rows="2"
                          placeholder="Motivo o comentario adicional (opcional)…"></textarea>
            </div>

            <!-- Info contextual por tipo -->
            <div id="aus_info" class="aus-info-box"></div>
        </div>
        <div class="modal-footer-horario">
            <button class="btn-cancel-horario" onclick="cerrarModalAusencia()">Cancelar</button>
            <button class="btn-success-horario" onclick="guardarAusencia()"> Enviar solicitud</button>
        </div>
    </div>
</div>

<!-- ══ MODAL EDITAR DÍA ════════════════════════════════════════ -->
<div id="modalEditarDia" class="modal-horario">
    <div class="modal-content-horario">
        <div class="modal-header-horario">
            <h3> Añadir / Editar jornada</h3>
            <button class="close-btn-horario" onclick="cerrarModal()">×</button>
        </div>
        <div class="modal-body-horario">
            <div class="form-group-horario">
                <label>Fecha</label>
                <input type="date" class="form-control-horario" id="inputFecha" readonly>
            </div>

            <div class="form-group-horario">
                <label>Tipo de petición</label>
                <select class="form-control-horario" id="inputTipo" onchange="cambiarTipoJornada()">
                    <option value="VACACIONES"> Vacaciones</option>
                    <option value="MEDICO"> Médico</option>
                    <option value="LIBRE"> Día libre</option>
                    <option value="FESTIVO"> Festivo</option>
                    <!--
                        <option value="TRABAJO">Jornada continua</option>
                        <option value="PARTIDA_M">⬆ Jornada partida — Primer Tramo (mañana)</option>
                        <option value="PARTIDA_T">⬇ Jornada partida — Segundo Tramo (tarde)</option>
                        
                        -->
                </select>
            </div>

            <div class="partida-info-box" id="partidaInfo">
                <strong> Jornada partida</strong>
                Para una jornada partida añade <strong>dos bloques</strong> en el mismo día:<br>
                1. Selecciona "Primer Tramo" e introduce horas de mañana (ej: 09:00–14:00)<br>
                2. Vuelve a este día y añade "Segundo Tramo" (ej: 16:00–19:00)
            </div>

            <div id="horariosContainer">
                <div class="form-row">
                    <div class="form-group-horario">
                        <label>Hora inicio</label>
                        <input type="time" class="form-control-horario" id="inputHoraInicio" value="09:00">
                    </div>
                    <div class="form-group-horario">
                        <label>Hora fin</label>
                        <input type="time" class="form-control-horario" id="inputHoraFin" value="17:00">
                    </div>
                </div>
            </div>

            <div class="form-group-horario">
                <label>Observaciones</label>
                <textarea class="form-control-horario" id="inputObservaciones" rows="2"
                          placeholder="Notas adicionales…"></textarea>
            </div>
        </div>
        <div class="modal-footer-horario">
            <button class="btn-cancel-horario" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-success-horario" onclick="guardarDia()"> Guardar</button>
        </div>
    </div>
</div>

<!-- ══ MODAL AÑADIR MASIVO ══════════════════════════════════════ -->
<div id="modalMasivo" class="modal-horario">
    <div class="modal-content-horario">
        <div class="modal-header-horario">
            <h3>Añadir horarios en bloque</h3>
            <button class="close-btn-horario" onclick="cerrarModalMasivo()">×</button>
        </div>
        <div class="modal-body-horario">
            <div class="form-row">
                <div class="form-group-horario">
                    <label>Desde *</label>
                    <input type="date" class="form-control-horario" id="masivo_inicio">
                </div>
                <div class="form-group-horario">
                    <label>Hasta *</label>
                    <input type="date" class="form-control-horario" id="masivo_fin">
                </div>
            </div>

            <div class="form-group-horario">
                <label>Días de la semana:</label>
                <div class="dias-checkbox">
                    <label><input type="checkbox" value="1" checked class="dia-cb"> Lun</label>
                    <label><input type="checkbox" value="2" checked class="dia-cb"> Mar</label>
                    <label><input type="checkbox" value="3" checked class="dia-cb"> Mié</label>
                    <label><input type="checkbox" value="4" checked class="dia-cb"> Jue</label>
                    <label><input type="checkbox" value="5" checked class="dia-cb"> Vie</label>
                    <label><input type="checkbox" value="6" class="dia-cb"> Sáb</label>
                    <label><input type="checkbox" value="7" class="dia-cb"> Dom</label>
                </div>
            </div>

            <div class="form-group-horario">
                <label>Tipo *</label>
                <select class="form-control-horario" id="masivo_tipo" onchange="toggleHorasMasivo()">
                    <option value="VACACIONES"> Vacaciones</option>
                    <option value="MEDICO"> Médico</option>
                    <option value="LIBRE"> Día libre</option>
                    <option value="FESTIVO"> Festivo</option>
                    <!--
                        <option value="TRABAJO">Jornada continua</option>
                        <option value="PARTIDA_M">⬆ Jornada partida — Primer Tramo</option>
                        <option value="PARTIDA_T">⬇ Jornada partida — Segundo Tramo</option>
                
                    -->
                </select>
            </div>

            <div id="horasMasivoDiv">
                <div class="form-row">
                    <div class="form-group-horario">
                        <label>Inicio:</label>
                        <input type="time" class="form-control-horario" id="masivo_inicio_hora" value="09:00">
                    </div>
                    <div class="form-group-horario">
                        <label>Fin:</label>
                        <input type="time" class="form-control-horario" id="masivo_fin_hora" value="17:00">
                    </div>
                </div>
            </div>

            <div class="form-group-horario">
                <label>Observaciones:</label>
                <textarea class="form-control-horario" id="masivo_obs" rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer-horario">
            <button class="btn-cancel-horario" onclick="cerrarModalMasivo()">Cancelar</button>
            <button class="btn-success-horario" onclick="guardarMasivo()"> Guardar</button>
        </div>
    </div>
</div>

<!-- LOADING -->
<div class="loading-overlay" id="loadingOverlay"><div class="spinner"></div></div>



<script src="secciones/api/horario_calendario.js"></script>
<script>
// ── Modal ausencia ────────────────────────────────────────────
function abrirModalAusencia(fechaPreseleccionada) {
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('aus_inicio').value = fechaPreseleccionada || hoy;
    document.getElementById('aus_fin').value    = fechaPreseleccionada || hoy;
    document.getElementById('aus_obs').value    = '';
    document.getElementById('aus_tipo').value   = 'VACACIONES';
    cambiarTipoAusencia();
    document.getElementById('modalAusencia').style.display = 'flex';
}

function cerrarModalAusencia() {
    document.getElementById('modalAusencia').style.display = 'none';
}

function cambiarTipoAusencia() {
    const tipo = document.getElementById('aus_tipo').value;
    const infoEl = document.getElementById('aus_info');
    const textos = {
        VACACIONES: { cls: 'aus-info-vacaciones', txt: ' La solicitud se enviará al administrador. Recibirás una notificación cuando sea aprobada.' },
        MEDICO:     { cls: 'aus-info-medico',     txt: ' Indica las fechas de tu cita médica. El administrador deberá validarla.' },
        LIBRE:      { cls: 'aus-info-libre',       txt: ' Solicitud de día(s) libre. Sujeto a aprobación del administrador.' },
        FESTIVO:    { cls: 'aus-info-festivo',     txt: ' Festivo local no recogido en el calendario general.' },
    };
    const info = textos[tipo] || {};
    infoEl.textContent = info.txt || '';
    infoEl.className   = 'aus-info-box ' + (info.cls || '');
    infoEl.style.display = info.txt ? 'block' : 'none';
}

async function guardarAusencia() {
    const tipo   = document.getElementById('aus_tipo').value;
    const inicio = document.getElementById('aus_inicio').value;
    const fin    = document.getElementById('aus_fin').value    || inicio;
    const obs    = document.getElementById('aus_obs').value;
    const dias   = Array.from(document.querySelectorAll('.aus-dia-cb:checked')).map(cb => parseInt(cb.value));

    if (!inicio) { alert('Selecciona al menos una fecha'); return; }
    if (fin < inicio) { alert('La fecha final no puede ser anterior a la inicial'); return; }
    if (!dias.length) { alert('Selecciona al menos un día de la semana'); return; }

    mostrarLoading(true);
    try {
        const r = await fetch('secciones/api/guardar_ausencia.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tipo_jornada: tipo, fecha_inicio: inicio, fecha_fin: fin, observaciones: obs, dias }),
        });
        const data = await r.json();
        if (data.success) {
            cerrarModalAusencia();
            cargarCalendario();
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.message);
        }
    } catch (e) {
        alert('❌ Error de conexión');
    } finally {
        mostrarLoading(false);
    }
}

// Inicializar info al cargar
document.addEventListener('DOMContentLoaded', cambiarTipoAusencia);
</script>