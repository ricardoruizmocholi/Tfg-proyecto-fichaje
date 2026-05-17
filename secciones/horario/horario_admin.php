<?php
// Días que se conservan peticiones resueltas antes de limpiarlas automáticamente
defined('DIAS_RETENER_PETICIONES') || define('DIAS_RETENER_PETICIONES', 30);

$_haIdEmpresa = (int)$_SESSION['empresa_activa'];
try {
    // Borrar primero los detalles (tabla hija) para no violar FK
    $pdo->prepare("
        DELETE D FROM detalle_solicitud_horario D
        INNER JOIN solicitudes_horario S ON D.id_solicitud = S.id_solicitud
        WHERE S.id_empresa = ?
          AND S.estado IN ('APROBADO','RECHAZADO')
          AND S.validado_en < DATE_SUB(NOW(), INTERVAL " . DIAS_RETENER_PETICIONES . " DAY)
    ")->execute([$_haIdEmpresa]);
    // Borrar después las solicitudes (tabla padre)
    $pdo->prepare("
        DELETE FROM solicitudes_horario
        WHERE id_empresa = ?
          AND estado IN ('APROBADO','RECHAZADO')
          AND validado_en < DATE_SUB(NOW(), INTERVAL " . DIAS_RETENER_PETICIONES . " DAY)
    ")->execute([$_haIdEmpresa]);
} catch (Exception $_haEx) {
    error_log('Auto-limpieza peticiones horario: ' . $_haEx->getMessage());
}
unset($_haIdEmpresa, $_haEx);
?>
<!--
  horario_admin.php — Vista de gestión de solicitudes de horario (admin)
  Incluida desde horario.php cuando el usuario es admin.
  Muestra estadísticas (pendientes/aprobadas/rechazadas), filtros y lista de solicitudes.
  La lógica AJAX (cargar, aprobar, rechazar) está en secciones/api/horario_admin.js.
-->
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
        <button class="btn-action btn-rechazar" onclick="limpiarResueltas()" title="Elimina peticiones aprobadas/rechazadas con más de <?= DIAS_RETENER_PETICIONES ?> días">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em" style="vertical-align:-0.125em"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            Limpiar resueltas
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

<script>const DIAS_RETENER = <?= DIAS_RETENER_PETICIONES ?>;</script>
<script src="secciones/api/horario_admin.js"></script>