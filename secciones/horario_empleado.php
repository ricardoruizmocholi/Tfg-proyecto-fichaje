<style>
    .horario-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        flex-wrap: wrap;
        gap: 15px;
    }

    .toolbar-left {
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }

    .view-toggle {
        display: flex;
        background: rgba(255,255,255,0.2);
        border-radius: 8px;
        overflow: hidden;
    }



    /* Resaltado del día actual */
.day-cell.today-highlight {
    border: 2px solid #667eea !important; /* El azul de tu cabecera */
    background-color: #f8f9ff !important; /* Un fondo azulado muy sutil */
    position: relative;
    box-shadow: inset 0 0 8px rgba(102, 126, 234, 0.2);
}

/* Opcional: una etiqueta que diga "Hoy" encima del número */
.day-cell.today-highlight::after {
    content: 'HOY';
    position: absolute;
    top: 5px;
    right: 5px;
    font-size: 9px;
    font-weight: bold;
    background: #667eea;
    color: white;
    padding: 2px 5px;
    border-radius: 3px;
}

/* Hacer que el número del día sea más visible hoy */
.today-highlight .day-number {
    color: #667eea;
    font-weight: 800;
}

    

    .view-toggle button {
        padding: 10px 20px;
        border: none;
        background: transparent;
        color: white;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s;
    }

    .view-toggle button.active {
        background: white;
        color: #667eea;
    }

    .month-nav {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .month-nav button {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        padding: 8px 15px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s;
    }

    .month-nav button:hover {
        background: rgba(255,255,255,0.3);
    }

    .month-title {
        font-size: 20px;
        font-weight: 600;
        min-width: 200px;
        text-align: center;
    }

    .toolbar-right {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-toolbar {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s;
        font-size: 14px;
    }

    .btn-primary {
        background: white;
        color: #667eea;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .btn-secondary {
        background: rgba(255,255,255,0.2);
        color: white;
        border: 1px solid rgba(255,255,255,0.3);
    }

    .btn-secondary:hover {
        background: rgba(255,255,255,0.3);
    }

    .btn-success {
        background: #28a745;
        color: white;
    }

    .btn-success:hover {
        background: #218838;
    }

    .legend {
        display: flex;
        gap: 20px;
        padding: 15px 30px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
    }

    .calendar-container {
        padding: 30px;
    }

    .calendar-month {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
    }

    .day-header {
        text-align: center;
        font-weight: 600;
        padding: 10px;
        color: #495057;
        font-size: 14px;
        background: #f8f9fa;
        border-radius: 6px;
    }

    .day-cell {
        min-height: 120px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 8px;
        cursor: pointer;
        transition: all 0.2s;
        background: white;
        position: relative;
    }

    .day-cell:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .day-cell.other-month {
        opacity: 0.3;
        pointer-events: none;
    }

    .day-cell.today {
        border: 2px solid #667eea;
        background: #f0f4ff;
    }

    .day-number {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
    }

    .schedule-block {
        margin-top: 5px;
        padding: 6px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        text-align: center;
    }

    .schedule-trabajo {
        background: #e3f2fd;
        color: #1976d2;
        border-left: 3px solid #1976d2;
    }

    .schedule-vacaciones {
        background: #e8f5e9;
        color: #388e3c;
        border-left: 3px solid #388e3c;
    }

    .schedule-medico {
        background: #fff3e0;
        color: #f57c00;
        border-left: 3px solid #f57c00;
    }

    .schedule-libre {
        background: #f3e5f5;
        color: #7b1fa2;
        border-left: 3px solid #7b1fa2;
    }

    .schedule-festivo {
        background: #ffebee;
        color: #c62828;
        border-left: 3px solid #c62828;
    }

    .schedule-pendiente {
        background: #fff9c4;
        color: #f57f17;
        border-left: 3px solid #f57f17;
        opacity: 0.7;
    }

    .status-badge {
        position: absolute;
        top: 5px;
        right: 5px;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 10px;
        font-weight: 600;
    }

    .badge-pendiente {
        background: #fff9c4;
        color: #f57f17;
    }

    .badge-aprobado {
        background: #e8f5e9;
        color: #388e3c;
    }

    .badge-rechazado {
        background: #ffebee;
        color: #c62828;
    }

    .modal-horario {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-content-horario {
        background: white;
        margin: 50px auto;
        width: 90%;
        max-width: 600px;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        animation: slideDown 0.3s;
        max-height: 90vh;
        overflow-y: auto;
    }

    @keyframes slideDown {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header-horario {
        padding: 20px 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header-horario h3 {
        margin: 0;
    }

    .close-btn-horario {
        background: none;
        border: none;
        color: white;
        font-size: 28px;
        cursor: pointer;
        line-height: 1;
    }

    .btn-delete-x {
    position: absolute;
    right: -5px;
    top: -5px;
    background: #ff4d4d;
    color: white;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}
.btn-add-inline {
    margin-top: 4px;
    border: 1px dashed #667eea;
    color: #667eea;
    text-align: center;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    font-size: 14px;
}
.btn-add-inline:hover {
    background: #f0f2ff;
}
.btn-add-evento:hover {
    background-color: #667eea !important;
    color: white !important;
    border-style: solid !important;
}

/* Estilo para la X de eliminar */
.btn-eliminar-temporal {
    position: absolute;
    top: -5px;
    right: -2px;
    background: #ff4d4d;
    color: white;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    z-index: 10;
    line-height: 1;
}

.btn-eliminar-temporal:hover {
    background: #cc0000;
    transform: scale(1.1);
}

/* Estilo para el botón + */
.btn-add-inline {
    margin-top: 5px;
    text-align: center;
    color: #667eea;
    cursor: pointer;
    border: 1px dashed #667eea;
    border-radius: 4px;
    font-weight: bold;
    font-size: 14px;
    background: rgba(102, 126, 234, 0.05);
}

.btn-add-inline:hover {
    background: #667eea;
    color: white;
}

/* Estado: ENVIADO A VALIDAR (Naranja/Amarillo) */
.schedule-block.status-pendiente {
    background-color: #fef3c7 !important; /* Amarillo suave */
    border-left: 4px solid #f59e0b !important; /* Naranja fuerte */
    color: #92400e !important;
    cursor: help;
}

/* Estado: BORRADOR (Azul con borde punteado) */
.schedule-block.status-temporal {
    background-color: #ebf4ff !important;
    border: 2px dashed #667eea !important;
    color: #4c51bf !important;
}

/* Estado: APROBADO (Verde o el color original) */
.schedule-block.status-aprobado {
    border-left: 4px solid #10b981 !important; /* Verde */
}

/* Opcional: Icono de reloj para los pendientes */
.status-pendiente::before {
    content: '⏳ ';
    font-size: 10px;
}

    .modal-body-horario {
        padding: 30px;
    }

    .form-group-horario {
        margin-bottom: 20px;
    }

    .form-group-horario label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #495057;
    }

    .form-control-horario {
        width: 100%;
        padding: 12px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        font-size: 14px;
    }

    .form-control-horario:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .dias-checkbox {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .dias-checkbox label {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 8px 12px;
        background: #f8f9fa;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .dias-checkbox label:hover {
        background: #e9ecef;
    }

    .dias-checkbox input[type="checkbox"]:checked + span {
        font-weight: 600;
        color: #667eea;
    }

    .modal-footer-horario {
        padding: 20px 30px;
        border-top: 1px solid #dee2e6;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-success-horario {
        background: #28a745;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
    }

    .btn-success-horario:hover {
        background: #218838;
    }

    .btn-cancel-horario {
        background: #6c757d;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
    }

    .btn-cancel-horario:hover {
        background: #5a6268;
    }

    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .loading-overlay.active {
        display: flex;
    }

    .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
</style>

<div class="horario-container">
    <!-- BARRA SUPERIOR -->
    <div class="toolbar">
        <div class="toolbar-left">
            <div class="view-toggle">
                <button class="active" onclick="cambiarVista('mes')">📅 Mes</button>
            </div>
            
            <div class="month-nav">
                <button onclick="mesAnterior()">◀</button>
                <span class="month-title" id="monthTitle"></span>
                <button onclick="mesSiguiente()">▶</button>
            </div>
        </div>

        <div class="toolbar-right">
            <button class="btn-toolbar btn-success" id="btnMasivo" onclick="abrirModalMasivo()">➕ Añadir múltiples días</button>
            <button class="btn-toolbar btn-secondary" onclick="copiarMesAnterior()">📋 Copiar mes anterior</button>
            <button class="btn-toolbar btn-primary" onclick="enviarValidacion()">✅ Enviar a validar</button>
        </div>
    </div>

    <!-- LEYENDA -->
    <div class="legend">
        <div class="legend-item">
            <div class="legend-color" style="background: #1976d2;"></div>
            <span>Jornada laboral</span>
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
            <h3>📝 Editar jornada</h3>
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
                    <option value="TRABAJO">Jornada laboral</option>
                    <option value="VACACIONES">Vacaciones</option>
                    <option value="MEDICO">Médico</option>
                    <option value="LIBRE">Día libre</option>
                    <option value="FESTIVO">Festivo</option>
                </select>
            </div>

            <div id="horariosContainer">
                <div class="form-group-horario">
                    <label>Hora inicio</label>
                    <input type="time" class="form-control-horario" id="inputHoraInicio" value="09:00">
                </div>

                <div class="form-group-horario">
                    <label>Hora fin</label>
                    <input type="time" class="form-control-horario" id="inputHoraFin" value="17:00">
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
            <h3>➕ Añadir horarios masivamente</h3>
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
                    <label>
                        <input type="checkbox" value="1" checked class="dia-cb">
                        <span>Lun</span>
                    </label>
                    <label>
                        <input type="checkbox" value="2" checked class="dia-cb">
                        <span>Mar</span>
                    </label>
                    <label>
                        <input type="checkbox" value="3" checked class="dia-cb">
                        <span>Mié</span>
                    </label>
                    <label>
                        <input type="checkbox" value="4" checked class="dia-cb">
                        <span>Jue</span>
                    </label>
                    <label>
                        <input type="checkbox" value="5" checked class="dia-cb">
                        <span>Vie</span>
                    </label>
                    <label>
                        <input type="checkbox" value="6" class="dia-cb">
                        <span>Sáb</span>
                    </label>
                    <label>
                        <input type="checkbox" value="7" class="dia-cb">
                        <span>Dom</span>
                    </label>
                </div>
            </div>
            
            <div class="form-group-horario">
                <label>Tipo:*</label>
                <select class="form-control-horario" id="masivo_tipo" onchange="toggleHorasMasivo()">
                    <option value="TRABAJO">Jornada laboral</option>
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