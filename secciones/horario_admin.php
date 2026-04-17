<link rel="stylesheet" href="css/peticiones_horarios.css">

<div class="admin-container">
    <!-- HEADER -->
    <div class="admin-header">
        <h2> Panel de Administrador - Gestión de Horarios</h2>
        <p>Revisa y valida las solicitudes de horarios de tus empleados</p>
    </div>

    <!-- ESTADÍSTICAS -->
    <div class="stats-bar">
        <div class="stat-item">
            <div class="number" id="statPendientes">0</div>
            <div class="label">Pendientes</div>
        </div>
        <div class="stat-item">
            <div class="number" id="statAprobadas">0</div>
            <div class="label">Aprobadas</div>
        </div>
        <div class="stat-item">
            <div class="number" id="statRechazadas">0</div>
            <div class="label">Rechazadas</div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filters-bar">
        <div class="filter-group">
            <label>Estado:</label>
            <select class="filter-select" id="filtroEstado" onchange="filtrarSolicitudes()">
                <option value="TODOS">Todos</option>
                <option value="PENDIENTE" selected>Pendientes</option>
                <option value="APROBADO">Aprobadas</option>
                <option value="RECHAZADO">Rechazadas</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Tipo:</label>
            <select class="filter-select" id="filtroTipo" onchange="filtrarSolicitudes()">
                <option value="TODOS">Todos</option>
                <option value="HORARIO_MES">Horario mensual</option>
                <option value="VACACIONES">Vacaciones</option>
                <option value="MEDICO">Médico</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Empleado:</label>
            <select class="filter-select" id="filtroEmpleado" onchange="filtrarSolicitudes()">
                <option value="TODOS">Todos</option>
                <!-- Se llena dinámicamente -->
            </select>
        </div>

        <button class="btn-action btn-ver" onclick="cargarSolicitudes()" style="margin-left: auto;">
             Actualizar
        </button>
    </div>

    <!-- LISTA DE SOLICITUDES -->
    <div class="solicitudes-list" id="solicitudesList">
        <!-- Se genera dinámicamente con JavaScript -->
    </div>
</div>

<!-- Modal RECHAZO -->
<div id="modalConfirmarRechazo" class="modal-admin-rechazo">
    <div class="modal-rechazo-content">
        <div class="modal-rechazo-header">
            <h3> Confirmar Rechazo de Solicitud</h3>
        </div>
        <div class="modal-rechazo-body">
            <p>Estás a punto de denegar la solicitud de <strong><span id="spanNombreEmpleado"></span></strong>.</p>
            <label for="inputMotivoRechazoFinal">Motivo del rechazo (Obligatorio):</label>
            <textarea id="inputMotivoRechazoFinal" placeholder="Ej: Las horas extras no coinciden con el registro de fichaje..."></textarea>
        </div>
        <div class="modal-rechazo-footer">
            <button class="btn-secundario" onclick="cerrarModalRechazo()">Cancelar</button>
            <button class="btn-peligro" onclick="ejecutarRechazoFinal()">Denegar Solicitud</button>
        </div>
    </div>
</div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

<!-- MODAL DETALLE SOLICITUD -->
<div id="modalDetalle" class="modal-detalle">
    <div class="modal-detalle-content">
        <div class="modal-detalle-header">
            <h3 id="modalTitulo">Detalle de Solicitud</h3>
            <button class="close-modal-btn" onclick="cerrarModalDetalle()">&times;</button>
        </div>
        
        <div class="modal-detalle-body">
            <!-- Información general -->
            <div class="detalle-info-grid" id="modalInfo">
                <!-- Se llena dinámicamente -->
            </div>

            <!-- Calendario con los días solicitados -->
            <div class="calendario-detalle">
                <h4> Detalle del horario solicitado</h4>
                <div class="dias-grid" id="modalCalendario">
                    <!-- Se llena dinámicamente -->
                </div>
            </div>

            <!-- Formulario de rechazo (oculto por defecto) -->
            <div class="form-rechazo" id="formRechazo">
                <label>Motivo del rechazo:</label>
                <textarea id="inputMotivoRechazo" placeholder="Explica por qué se rechaza esta solicitud..."></textarea>
            </div>
        </div>

        <div class="modal-detalle-footer">
            <button class="btn-action btn-rechazar" onclick="mostrarFormRechazo()">
                 Rechazar
            </button>
            <div class="btn-group">
                <button class="btn-action" onclick="cerrarModalDetalle()" style="background: #6c757d; color: white;">
                    Cerrar
                </button>
                <button class="btn-action btn-aprobar" onclick="aprobarSolicitud()">
                     Aprobar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="secciones/api/horario_admin.js"></script>