
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
            <button class="btn-toolbar btn-success" id="btnMasivo" onclick="abrirModalMasivo()"> Añadir múltiples días</button>
            <button class="btn-toolbar btn-secondary" onclick="copiarMesAnterior()"> Copiar mes anterior</button>
            <button class="btn-toolbar btn-primary" onclick="enviarValidacion()"> Enviar a validar</button>
        </div>
    </div>

    <!-- LEYENDA -->
    <div class="legend">
        <div class="legend-item">
            <div class="legend-color" style="background: #1976d2;"></div>
            <span>Jornada continua</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #3f51b5;"></div>
            <span> Partida 1º Tramo</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #e91e63;"></div>
            <span> Partida 2º Tramo</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #388e3c;"></div>
            <span>Vacaciones</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #f57c00;"></div>
            <span>Médico</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #7b1fa2;"></div>
            <span>Día libre</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #c62828;"></div>
            <span>Festivo</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #fef3c7; border: 1px solid #f59e0b;"></div>
            <span>⏳ Pendiente validación</span>
        </div>
    </div>

    <!-- CALENDARIO -->
    <div class="calendar-container">
        <div class="calendar-month" id="calendarMonth">
            <!-- Se genera dinámicamente con JavaScript -->
        </div>
    </div>
</div>

<!-- MODAL EDITAR DÍA -->
<div id="modalEditarDia" class="modal-horario">
    <div class="modal-content-horario">
        <div class="modal-header-horario">
            <h3> Añadir / Editar jornada</h3>
            <button class="close-btn-horario" onclick="cerrarModal()">&times;</button>
        </div>
        <div class="modal-body-horario">
            <div class="form-group-horario">
                <label>Fecha</label>
                <input type="date" class="form-control-horario" id="inputFecha" readonly>
            </div>
            
            <div class="form-group-horario">
                <label>Tipo de jornada</label>
                <select class="form-control-horario" id="inputTipo" onchange="cambiarTipoJornada()">
                    <option value="TRABAJO">Jornada continua</option>
                    <option value="PARTIDA_M">⬆️ Jornada partida — Primer Tramo  (1º)</option>
                    <option value="PARTIDA_T">⬇️ Jornada partida — Segundo tarde (2º)</option>
                    <option value="VACACIONES">Vacaciones</option>
                    <option value="MEDICO">Médico</option>
                    <option value="LIBRE">Día libre</option>
                    <option value="FESTIVO">Festivo</option>
                </select>
            </div>

            <!-- Info box jornada partida -->
            <div class="partida-info-box" id="partidaInfo">
                <strong>ℹ️ Jornada partida</strong>
                Para registrar una jornada partida añade <strong>dos bloques separados</strong> en el mismo día:<br>
                1. Primero selecciona "Primer Tramo " e introduce las horas de mañana (ej: 09:00 – 14:00)<br>
                2. Luego vuelve a este día y añade "Segundo Tramo " (ej: 16:00 – 19:00)
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
                <textarea class="form-control-horario" id="inputObservaciones" rows="3" placeholder="Notas adicionales..."></textarea>
            </div>
        </div>
        <div class="modal-footer-horario">
            <button class="btn-cancel-horario" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-success-horario" onclick="guardarDia()">Guardar</button>
        </div>
    </div>
</div>

<!-- MODAL AÑADIR MASIVO -->
<div id="modalMasivo" class="modal-horario">
    <div class="modal-content-horario">
        <div class="modal-header-horario">
            <h3> Añadir horarios </h3>
            <button class="close-btn-horario" onclick="cerrarModalMasivo()">&times;</button>
        </div>
        <div class="modal-body-horario">
            <div class="form-row">
                <div class="form-group-horario">
                    <label>Desde:*</label>
                    <input type="date" class="form-control-horario" id="masivo_inicio">
                </div>
                <div class="form-group-horario">
                    <label>Hasta:*</label>
                    <input type="date" class="form-control-horario" id="masivo_fin">
                </div>
            </div>
            
            <div class="form-group-horario">
                <label>Días de la semana:</label>
                <div class="dias-checkbox">
                    <label><input type="checkbox" value="1" checked class="dia-cb"> <span>Lun</span></label>
                    <label><input type="checkbox" value="2" checked class="dia-cb"> <span>Mar</span></label>
                    <label><input type="checkbox" value="3" checked class="dia-cb"> <span>Mié</span></label>
                    <label><input type="checkbox" value="4" checked class="dia-cb"> <span>Jue</span></label>
                    <label><input type="checkbox" value="5" checked class="dia-cb"> <span>Vie</span></label>
                    <label><input type="checkbox" value="6" class="dia-cb"> <span>Sáb</span></label>
                    <label><input type="checkbox" value="7" class="dia-cb"> <span>Dom</span></label>
                </div>
            </div>
            
            <div class="form-group-horario">
                <label>Tipo:*</label>
                <select class="form-control-horario" id="masivo_tipo" onchange="toggleHorasMasivo()">
                    <option value="TRABAJO">Jornada continua</option>
                    <option value="PARTIDA_M">⬆️ Jornada partida — Primer Tramo</option>
                    <option value="PARTIDA_T">⬇️ Jornada partida — Segundo Tramo </option>
                    <option value="VACACIONES">Vacaciones</option>
                    <option value="MEDICO">Médico</option>
                    <option value="LIBRE">Día libre</option>
                    <option value="FESTIVO">Festivo</option>
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
            <button class="btn-success-horario" onclick="guardarMasivo()">💾 Guardar</button>
        </div>
    </div>
</div>

<!-- LOADING OVERLAY -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

<script src="secciones/api/horario_calendario.js"></script>