// ============================================
// VARIABLES GLOBALES
// ============================================
let solicitudesData = [];
let solicitudActual = null;

// ============================================
// INICIALIZACIÓN
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    cargarSolicitudes();
});

// ============================================
// CARGAR SOLICITUDES
// ============================================
async function cargarSolicitudes() {
    const filtroEstado = document.getElementById('filtroEstado').value;
    const filtroTipo = document.getElementById('filtroTipo').value;
    const filtroEmpleado = document.getElementById('filtroEmpleado').value;

    try {
        const url = `secciones/api/obtener_solicitudes_admin.php?estado=${filtroEstado}&tipo=${filtroTipo}&empleado=${filtroEmpleado}`;
        const response = await fetch(url);
        const data = await response.json();

        if (data.success) {
            solicitudesData = data.solicitudes;
            actualizarEstadisticas(data.estadisticas);
            llenarFiltroEmpleados(data.empleados);
            renderizarSolicitudes();
        } else {
            alert('Error al cargar solicitudes: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión al cargar solicitudes');
    }
}

function actualizarEstadisticas(stats) {
    document.getElementById('statPendientes').textContent = stats.PENDIENTE || 0;
    document.getElementById('statAprobadas').textContent = stats.APROBADO || 0;
    document.getElementById('statRechazadas').textContent = stats.RECHAZADO || 0;
}

function llenarFiltroEmpleados(empleados) {
    const select = document.getElementById('filtroEmpleado');
    const valorActual = select.value;
    
    // Mantener "Todos" y agregar empleados
    select.innerHTML = '<option value="TODOS">Todos</option>';
    
    empleados.forEach(emp => {
        const option = document.createElement('option');
        option.value = emp.id_usuario;
        option.textContent = `${emp.nombre} ${emp.apellidos}`;
        select.appendChild(option);
    });
    
    // Restaurar valor anterior si existía
    if (valorActual !== 'TODOS') {
        select.value = valorActual;
    }
}

function filtrarSolicitudes() {
    cargarSolicitudes();
}

// ============================================
// RENDERIZAR SOLICITUDES
// ============================================
function renderizarSolicitudes() {
    const container = document.getElementById('solicitudesList');
    
    if (solicitudesData.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3>No hay solicitudes</h3>
                <p>No se encontraron solicitudes con los filtros aplicados</p>
            </div>
        `;
        return;
    }

    container.innerHTML = '';

    solicitudesData.forEach(solicitud => {
        const card = crearTarjetaSolicitud(solicitud);
        container.appendChild(card);
    });
}

function crearTarjetaSolicitud(solicitud) {
    const card = document.createElement('div');
    card.className = `solicitud-card ${solicitud.estado.toLowerCase()}`;
    
    const tipoTexto = {
        'HORARIO_MES': 'Horario Mensual',
        'VACACIONES': 'Vacaciones',
        'MEDICO': 'Horas Médicas'
    };

    const estadoClase = {
        'PENDIENTE': 'pendiente',
        'APROBADO': 'aprobado',
        'RECHAZADO': 'rechazado'
    };

    card.innerHTML = `
        <div class="solicitud-header">
            <div class="solicitud-info">
                <h3>${solicitud.nombre} ${solicitud.apellidos}</h3>
                <div class="meta">
                    ${tipoTexto[solicitud.tipo_solicitud]} • 
                    Solicitado el ${formatearFecha(solicitud.creado_en)}
                </div>
            </div>
            <span class="solicitud-badge badge-${estadoClase[solicitud.estado]}">
                ${solicitud.estado}
            </span>
        </div>

        <div class="solicitud-body">
            <div class="info-item">
                <span class="label">Tipo</span>
                <span class="value">${tipoTexto[solicitud.tipo_solicitud]}</span>
            </div>
            <div class="info-item">
                <span class="label">Período</span>
                <span class="value">
                    ${formatearFechaCorta(solicitud.fecha_inicio)} - 
                    ${formatearFechaCorta(solicitud.fecha_fin)}
                </span>
            </div>
            <div class="info-item">
                <span class="label">Días</span>
                <span class="value">${solicitud.total_dias} días</span>
            </div>
            ${solicitud.estado === 'RECHAZADO' && solicitud.motivo_rechazo ? `
                <div class="info-item" style="grid-column: 1/-1;">
                    <span class="label">Motivo de rechazo</span>
                    <span class="value" style="color: #dc3545;">${solicitud.motivo_rechazo}</span>
                </div>
            ` : ''}
            ${solicitud.observaciones ? `
                <div class="info-item" style="grid-column: 1/-1;">
                    <span class="label">Observaciones</span>
                    <span class="value">${solicitud.observaciones}</span>
                </div>
            ` : ''}
        </div>

        <div class="solicitud-footer">
            <div>
                ${solicitud.estado === 'APROBADO' ? 
                    `<small style="color: #28a745;">✓ Validado por ${solicitud.validador_nombre} ${solicitud.validador_apellidos}</small>` : 
                solicitud.estado === 'RECHAZADO' ?
                    `<small style="color: #dc3545;">✗ Rechazado por ${solicitud.validador_nombre} ${solicitud.validador_apellidos}</small>` :
                    '<small style="color: #856404;">⏳ Esperando validación</small>'
                }
            </div>
            <div class="btn-group">
                <button class="btn-action btn-ver" onclick="verDetalleSolicitud(${solicitud.id_solicitud})">
                     Ver detalle
                </button>
                ${solicitud.estado === 'PENDIENTE' ? `
                    <button class="btn-action btn-aprobar" onclick="aprobarDirecto(${solicitud.id_solicitud})">
                        ✅ Aprobar
                    </button>
                    <button class="btn-action btn-rechazar" onclick="rechazarDirecto(${solicitud.id_solicitud})">
                        ❌ Rechazar
                    </button>
                ` : ''}
            </div>
        </div>
    `;

    return card;
}

// ============================================
// MODAL DETALLE
// ============================================
async function verDetalleSolicitud(id) {
    try {


        const response = await fetch(`secciones/api/obtener_detalle_solicitud.php?id_solicitud=${id}`);
        const data = await response.json();

        if (data.success) {
            solicitudActual = data.solicitud;
            mostrarModalDetalle(data);
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar detalle');
    }
}

// ... (Tus funciones anteriores de verDetalleSolicitud permanecen igual)

// ============================================
// FLUJO DE RECHAZO
// ============================================

let idSolicitudPendienteRechazo = null;

function mostrarFormRechazo() {
    if (!solicitudActual) return;
    
    // Guardamos el ID antes de cerrar nada
    idSolicitudPendienteRechazo = solicitudActual.id_solicitud;
    
    // Rellenamos el nombre en el modal de rechazo
    document.getElementById('spanNombreEmpleado').textContent = solicitudActual.nombre_empleado;
    
    // Cerramos el de detalles y abrimos el de rechazo
    cerrarModalDetalle();
    document.getElementById('modalConfirmarRechazo').style.display = 'block';
}

function cerrarModalRechazo() {
    document.getElementById('modalConfirmarRechazo').style.display = 'none';
    idSolicitudPendienteRechazo = null;
    document.getElementById('inputMotivoRechazoFinal').value = '';
}

async function ejecutarRechazoFinal() {
    const motivo = document.getElementById('inputMotivoRechazoFinal').value.trim();
    
    if (!motivo || motivo.length < 5) {
        alert('Debes indicar un motivo válido para rechazar');
        return;
    }

    // Llamamos a tu función original pasando el ID que guardamos
    await procesarValidacion(idSolicitudPendienteRechazo, 'RECHAZAR', motivo);
    
    // Si todo va bien, cerramos el modal de rechazo
    cerrarModalRechazo();
}

// ============================================
// UTILIDADES (ESTO SOLUCIONA EL ERROR DE CONSOLA)
// ============================================
function mostrarLoading(mostrar) {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        if (mostrar) overlay.classList.add('active');
        else overlay.classList.remove('active');
    }
}

function mostrarModalDetalle(data) {
    const solicitud = data.solicitud;
    const detalleDias = data.detalle_dias;
    const stats = data.estadisticas;

    // Título
    const tipoTexto = {
        'HORARIO_MES': 'Horario Mensual',
        'VACACIONES': 'Solicitud de Vacaciones',
        'MEDICO': 'Solicitud Horas Médicas'
    };
    document.getElementById('modalTitulo').textContent = tipoTexto[solicitud.tipo_solicitud];

    // Información general
    const infoHTML = `
        <div class="info-item">
            <span class="label">Empleado</span>
            <span class="value">${solicitud.nombre} ${solicitud.apellidos}</span>
        </div>
        <div class="info-item">
            <span class="label">Email</span>
            <span class="value">${solicitud.email}</span>
        </div>
        <div class="info-item">
            <span class="label">Período</span>
            <span class="value">
                ${formatearFechaCorta(solicitud.fecha_inicio)} - 
                ${formatearFechaCorta(solicitud.fecha_fin)}
            </span>
        </div>
        <div class="info-item">
            <span class="label">Total días</span>
            <span class="value">${stats.total_dias} días</span>
        </div>
        <div class="info-item">
            <span class="label">Total horas</span>
            <span class="value">${stats.total_horas} horas</span>
        </div>
        <div class="info-item">
            <span class="label">Estado</span>
            <span class="value">
                <span class="solicitud-badge badge-${solicitud.estado.toLowerCase()}">
                    ${solicitud.estado}
                </span>
            </span>
        </div>
        ${solicitud.observaciones ? `
            <div class="info-item" style="grid-column: 1/-1;">
                <span class="label">Observaciones del empleado</span>
                <span class="value">${solicitud.observaciones}</span>
            </div>
        ` : ''}
        ${solicitud.motivo_rechazo ? `
            <div class="info-item" style="grid-column: 1/-1;">
                <span class="label">Motivo de rechazo</span>
                <span class="value" style="color: #dc3545;">${solicitud.motivo_rechazo}</span>
            </div>
        ` : ''}
    `;

    document.getElementById('modalInfo').innerHTML = infoHTML;

    // Calendario de días
    let calendarioHTML = '';
    detalleDias.forEach(dia => {
        const clasesTipo = dia.tipo_jornada.toLowerCase();
        const fecha = new Date(dia.fecha + 'T00:00:00');
        const nombreDia = fecha.toLocaleDateString('es-ES', { weekday: 'short' });
        const numeroDia = fecha.getDate();

        let contenidoHorario = '';
        if (dia.tipo_jornada === 'TRABAJO' || dia.tipo_jornada === 'PARTIDA') {
            const prefijo = dia.tipo_jornada === 'PARTIDA' ? '◑ ' : '';
            contenidoHorario = `${prefijo}${dia.hora_inicio?.substr(0,5)} - ${dia.hora_fin?.substr(0,5)}<br>(${dia.horas_totales}h)`;
        }else {
            contenidoHorario = dia.tipo_jornada.charAt(0) + dia.tipo_jornada.slice(1).toLowerCase();
            if (dia.hora_inicio && dia.hora_fin) {
                contenidoHorario += `<br>${dia.hora_inicio.substr(0,5)} - ${dia.hora_fin.substr(0,5)}`;
            }
        }

        calendarioHTML += `
            <div class="dia-mini ${clasesTipo}">
                <div class="fecha">${nombreDia} ${numeroDia}</div>
                <div class="horario">${contenidoHorario}</div>
            </div>
        `;
    });

    document.getElementById('modalCalendario').innerHTML = calendarioHTML;

    // Ocultar formulario de rechazo por defecto
    document.getElementById('formRechazo').classList.remove('visible');
    document.getElementById('inputMotivoRechazo').value = '';

    // Mostrar modal
    document.getElementById('modalDetalle').style.display = 'block';
}

function cerrarModalDetalle() {
    document.getElementById('modalDetalle').style.display = 'none';
    solicitudActual = null;
}


// ============================================
// APROBAR / RECHAZAR
// ============================================
async function aprobarSolicitud() {
    if (!solicitudActual) return;

    if (!confirm('¿Confirmas que deseas APROBAR esta solicitud?\n\nSe aplicarán todos los horarios al calendario del empleado.')) {
        return;
    }

    await procesarValidacion(solicitudActual.id_solicitud, 'APROBAR', null);
}

async function aprobarDirecto(idSolicitud) {
    if (!confirm('¿Aprobar esta solicitud?')) return;
    await procesarValidacion(idSolicitud, 'APROBAR', null);
}

function rechazarDirecto(idSolicitud) {
    const sol = solicitudesData.find(s => s.id_solicitud == idSolicitud);
    idSolicitudPendienteRechazo = idSolicitud;
    document.getElementById('spanNombreEmpleado').textContent =
        sol ? `${sol.nombre} ${sol.apellidos}` : '';
    document.getElementById('inputMotivoRechazoFinal').value = '';
    document.getElementById('modalConfirmarRechazo').style.display = 'block';
}

async function procesarValidacion(idSolicitud, accion, motivo) {
    // Si estamos en el modal y es rechazo, obtener motivo del textarea
    if (accion === 'RECHAZAR' && solicitudActual && solicitudActual.id_solicitud === idSolicitud) {
        motivo = document.getElementById('inputMotivoRechazo').value.trim();
        
        if (!motivo) {
            alert('Debes indicar un motivo para rechazar');
            document.getElementById('inputMotivoRechazo').focus();
            return;
        }

        if (!confirm('¿Confirmas que deseas RECHAZAR esta solicitud?')) {
            return;
        }
    }

    try {
        const response = await fetch('secciones/api/validar_solicitud.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                id_solicitud: idSolicitud,
                accion: accion,
                motivo_rechazo: motivo
            })
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            cerrarModalDetalle();
            cargarSolicitudes();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

// ============================================
// UTILIDADES
// ============================================
function formatearFecha(fechaISO) {
    const fecha = new Date(fechaISO);
    return fecha.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatearFechaCorta(fechaISO) {
    const fecha = new Date(fechaISO + 'T00:00:00');
    return fecha.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// ============================================
// LIMPIAR PETICIONES RESUELTAS
// ============================================
async function limpiarResueltas() {
    const dias = typeof DIAS_RETENER !== 'undefined' ? DIAS_RETENER : 30;
    if (!confirm(`¿Eliminar todas las peticiones aprobadas/rechazadas con más de ${dias} días?\n\nEsta acción no se puede deshacer. Las pendientes no se tocarán.`)) return;
    try {
        const r = await fetch('secciones/api/limpiar_peticiones.php', { method: 'POST' });
        const d = await r.json();
        if (d.success) {
            const n = d.eliminadas;
            alert(`Limpieza completada: ${n} petición${n !== 1 ? 'es' : ''} eliminada${n !== 1 ? 's' : ''}.`);
            cargarSolicitudes();
        } else {
            alert('Error: ' + d.message);
        }
    } catch (e) {
        console.error(e);
        alert('Error de conexión al limpiar peticiones');
    }
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('modalDetalle');
    if (event.target === modal) {
        cerrarModalDetalle();
    }
}