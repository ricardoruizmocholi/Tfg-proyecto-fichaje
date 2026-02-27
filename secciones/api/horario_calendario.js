// ============================================
// VARIABLES GLOBALES
// ============================================
let mesActual = new Date();
let vistaActual = 'mes';
let horariosData = {};
let totalTemporales = 0;

// ============================================
// INICIALIZACIÓN
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    cargarCalendario();
});

// ============================================
// NAVEGACIÓN DEL CALENDARIO
// ============================================
function cambiarVista(vista) {
    vistaActual = vista;
    document.querySelectorAll('.view-toggle button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    cargarCalendario();
}

function mesAnterior() {
    mesActual.setMonth(mesActual.getMonth() - 1);
    cargarCalendario();
}

function mesSiguiente() {
    mesActual.setMonth(mesActual.getMonth() + 1);
    cargarCalendario();
}

// ============================================
// CARGAR CALENDARIO
// ============================================
async function cargarCalendario() {
    mostrarLoading(true);
    
    const mes = mesActual.getMonth() + 1;
    const anio = mesActual.getFullYear();
    
    actualizarTituloMes();
    
    try {
        const response = await fetch(`secciones/api/obtener_horarios.php?mes=${mes}&anio=${anio}`);
        const data = await response.json();
        
        console.log('Respuesta del servidor:', data);
        
        if (data.success) {
            horariosData = data.calendario || {};
            totalTemporales = data.total_temporales || 0;
            generarCalendarioHTML();
            actualizarContadorTemporales();
        } else {
            alert('Error al cargar horarios: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión al cargar horarios');
    } finally {
        mostrarLoading(false);
    }
}

function actualizarTituloMes() {
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                   'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    document.getElementById('monthTitle').textContent = 
        `${meses[mesActual.getMonth()]} ${mesActual.getFullYear()}`;
}

function actualizarContadorTemporales() {
    const btnEnviar = document.querySelector('.btn-primary');
    if (btnEnviar) {
        if (totalTemporales > 0) {
            btnEnviar.textContent = `✅ Enviar a validar (${totalTemporales} eventos)`;
            btnEnviar.style.background = '#28a745';
            btnEnviar.style.color = 'white';
            btnEnviar.style.animation = 'pulse 2s infinite';
        } else {
            btnEnviar.textContent = '✅ Enviar a validar';
            btnEnviar.style.background = 'white';
            btnEnviar.style.color = '#667eea';
            btnEnviar.style.animation = 'none';
        }
    }
}

// ============================================
// GENERAR HTML DEL CALENDARIO
// ============================================
function generarCalendarioHTML() {
    const container = document.getElementById('calendarMonth');
    const mes = mesActual.getMonth();
    const anio = mesActual.getFullYear();
    
    container.innerHTML = '';
    
    // Headers de días
    const diasSemana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
    diasSemana.forEach(dia => {
        const header = document.createElement('div');
        header.className = 'day-header';
        header.textContent = dia;
        container.appendChild(header);
    });
    
    // Calcular días del mes
    const primerDia = new Date(anio, mes, 1);
    const ultimoDia = new Date(anio, mes + 1, 0);
    const diasMes = ultimoDia.getDate();
    
    let diaSemanaInicio = primerDia.getDay();
    if (diaSemanaInicio === 0) diaSemanaInicio = 7;
    
    // Días del mes anterior
    const diasMesAnterior = new Date(anio, mes, 0).getDate();
    for (let i = diaSemanaInicio - 1; i > 0; i--) {
        const cell = crearCeldaDia(diasMesAnterior - i + 1, mes - 1, anio, true);
        container.appendChild(cell);
    }
    
    // Días del mes actual
    for (let dia = 1; dia <= diasMes; dia++) {
        const cell = crearCeldaDia(dia, mes, anio, false);
        container.appendChild(cell);
    }
    
    // Días del mes siguiente
    const diasRestantes = 7 - ((container.children.length - 7) % 7);
    if (diasRestantes < 7) {
        for (let dia = 1; dia <= diasRestantes; dia++) {
            const cell = crearCeldaDia(dia, mes + 1, anio, true);
            container.appendChild(cell);
        }
    }
}

function crearCeldaDia(dia, mes, anio, otroMes) {
    const cell = document.createElement('div');
    cell.className = 'day-cell';
    if (otroMes) cell.classList.add('other-month');

    const hoy = new Date();
    const esHoy = !otroMes && 
                  dia === hoy.getDate() && 
                  mes === hoy.getMonth() && 
                  anio === hoy.getFullYear();

    if (esHoy) cell.classList.add('today-highlight');
    
    const fecha = `${anio}-${String(mes + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
    const dayNumber = document.createElement('div');
    dayNumber.className = 'day-number';
    dayNumber.textContent = dia;
    cell.appendChild(dayNumber);

    let eventosDelDia = horariosData[fecha] || [];
    if (!Array.isArray(eventosDelDia)) eventosDelDia = [eventosDelDia];

    eventosDelDia.forEach((evento) => {
        // --- IMPORTANTE: SI ESTÁ RECHAZADO, NO LO DIBUJAMOS ---
        if (evento.estado === 'RECHAZADO') return;

        const scheduleBlock = document.createElement('div');
        
        // Asignamos clases según el estado para el CSS
        let claseEstado = '';
        if (evento.estado === 'PENDIENTE') {
            claseEstado = 'status-pendiente'; // Color naranja
        } else if (evento.estado === 'TEMPORAL') {
            claseEstado = 'status-temporal';  // Borde punteado
        } else {
            claseEstado = 'status-aprobado';  // Color normal
        }

        scheduleBlock.className = `schedule-block schedule-${evento.tipo_jornada.toLowerCase()} ${claseEstado}`;
        scheduleBlock.style.position = 'relative';

        // Si es temporal, añadimos la X de borrar
        if (evento.estado === 'TEMPORAL') {
            const btnEliminar = document.createElement('div');
            btnEliminar.innerHTML = '&times;';
            btnEliminar.className = 'btn-eliminar-temporal';
            btnEliminar.onclick = (e) => {
                e.stopPropagation();
                if(confirm('¿Eliminar este borrador?')) eliminarEventoTemporal(fecha, evento.orden_dia);
            };
            scheduleBlock.appendChild(btnEliminar);
        }

        let contenido = evento.tipo_jornada === 'TRABAJO' 
            ? `${evento.hora_inicio.substr(0,5)}-${evento.hora_fin.substr(0,5)}`
            : evento.tipo_jornada.charAt(0) + evento.tipo_jornada.slice(1).toLowerCase();
        
        const textSpan = document.createElement('span');
        textSpan.textContent = contenido;
        scheduleBlock.appendChild(textSpan);
        
        // Solo permitimos editar si es TEMPORAL
        if (evento.estado === 'TEMPORAL') {
            scheduleBlock.onclick = (e) => { e.stopPropagation(); editarDia(fecha); };
        } else {
            // Si está pendiente o aprobado, solo ver detalles
            scheduleBlock.onclick = (e) => { e.stopPropagation(); verEventosDelDia(fecha); };
        }

        cell.appendChild(scheduleBlock);
    });

    // Botón "+" para añadir (solo si el día no tiene eventos bloqueados)
   
        if (!otroMes) {
        const btnAdd = document.createElement('div');
        btnAdd.innerHTML = '+';
        btnAdd.className = 'btn-add-inline'; 
        btnAdd.title = "Añadir tramo horario";
        btnAdd.onclick = (e) => {
            e.stopPropagation();
            editarDia(fecha);
        };
        cell.appendChild(btnAdd);
    }

    return cell;
}

// ============================================
// ELIMINAR EVENTO TEMPORAL
// ============================================

async function eliminarEventoTemporal(fecha, orden_dia) {
    // 1. Pequeño log para depurar en la consola (F12)
    console.log("Intentando eliminar:", fecha, "Orden:", orden_dia);

    mostrarLoading(true);
    try {
        const response = await fetch('secciones/api/eliminar_horario_temporal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            // Aseguramos que los nombres coincidan con lo que lee el PHP
            body: JSON.stringify({ 
                fecha: fecha, 
                orden_dia: parseInt(orden_dia) 
            })
        });

        // 2. Verificamos si la respuesta es texto antes de JSON por si hay un error de PHP
        const text = await response.text();
        console.log("Respuesta bruta del servidor:", text);
        
        const data = JSON.parse(text);

        if (data.success) {
            // 3. Recargamos los datos y el HTML
            await cargarCalendario(); 
        } else {
            alert('No se pudo eliminar: ' + (data.message || 'Error desconocido'));
        }
    } catch (error) {
        console.error('Error al eliminar:', error);
        alert('Error de conexión al intentar eliminar el evento');
    } finally {
        mostrarLoading(false);
    }
}
// ============================================
// MODAL EDITAR DÍA
// ============================================
function editarDia(fecha) {
    document.getElementById('inputFecha').value = fecha;
    document.getElementById('inputTipo').value = 'TRABAJO';
    document.getElementById('inputHoraInicio').value = '09:00';
    document.getElementById('inputHoraFin').value = '17:00';
    document.getElementById('inputObservaciones').value = '';
    
    cambiarTipoJornada();
    document.getElementById('modalEditarDia').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modalEditarDia').style.display = 'none';
}

function cambiarTipoJornada() {
    const tipo = document.getElementById('inputTipo').value;
    const horariosContainer = document.getElementById('horariosContainer');
    
    if (tipo === 'VACACIONES' || tipo === 'LIBRE' || tipo === 'FESTIVO') {
        horariosContainer.style.display = 'none';
    } else {
        horariosContainer.style.display = 'block';
    }
}

// ============================================
// GUARDAR DÍA
// ============================================
// ============================================
// GUARDAR DÍA (VALIDACIÓN DE SOLAPAMIENTO ELIMINADA)
// ============================================
async function guardarDia() {
    const fecha = document.getElementById('inputFecha').value;
    const tipo = document.getElementById('inputTipo').value;
    const horaInicio = document.getElementById('inputHoraInicio').value;
    const horaFin = document.getElementById('inputHoraFin').value;
    const observaciones = document.getElementById('inputObservaciones').value;
    
    // Mantenemos solo la validación básica de coherencia horaria
    if (tipo === 'TRABAJO' || tipo === 'MEDICO') {
        const inicioNuevo = convertirHoraAMinutos(horaInicio);
        const finNuevo = convertirHoraAMinutos(horaFin);
        
        if (finNuevo <= inicioNuevo) {
            alert('⚠️ La hora de fin debe ser posterior a la hora de inicio');
            return;
        }
        // SE HA ELIMINADO EL BUCLE DE SOLAPAMIENTO CON EVENTOS EXISTENTES
    }
    
    mostrarLoading(true);
    
    try {
        const response = await fetch('secciones/api/guardar_horario_dia.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                fecha: fecha,
                tipo_jornada: tipo,
                hora_inicio: tipo === 'TRABAJO' || tipo === 'MEDICO' ? horaInicio : null,
                hora_fin: tipo === 'TRABAJO' || tipo === 'MEDICO' ? horaFin : null,
                observaciones: observaciones
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            cerrarModal();
            cargarCalendario(); 
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    } finally {
        mostrarLoading(false);
    }
}

function convertirHoraAMinutos(hora) {
    const partes = hora.split(':');
    return parseInt(partes[0]) * 60 + parseInt(partes[1]);
}

// ============================================
// MODAL MASIVO
// ============================================
function abrirModalMasivo() {
    const hoy = new Date();
    const fecha_inicio = hoy.toISOString().split('T')[0];
    const fecha_fin = new Date(hoy.getTime() + 6 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    
    document.getElementById('masivo_inicio').value = fecha_inicio;
    document.getElementById('masivo_fin').value = fecha_fin;
    document.getElementById('masivo_tipo').value = 'TRABAJO';
    document.getElementById('masivo_inicio_hora').value = '09:00';
    document.getElementById('masivo_fin_hora').value = '17:00';
    document.getElementById('masivo_obs').value = '';
    
    // Marcar de lunes a viernes por defecto
    document.querySelectorAll('.dia-cb').forEach((cb, index) => {
        cb.checked = index < 5; // Lun-Vie
    });
    
    toggleHorasMasivo();
    document.getElementById('modalMasivo').style.display = 'block';
}

function cerrarModalMasivo() {
    document.getElementById('modalMasivo').style.display = 'none';
}

function toggleHorasMasivo() {
    const tipo = document.getElementById('masivo_tipo').value;
    document.getElementById('horasMasivoDiv').style.display = 
        (tipo === 'TRABAJO' || tipo === 'MEDICO') ? 'block' : 'none';
}

async function guardarMasivo() {
    const fechaInicio = document.getElementById('masivo_inicio').value;
    const fechaFin = document.getElementById('masivo_fin').value;
    const tipo = document.getElementById('masivo_tipo').value;
    const horaInicio = document.getElementById('masivo_inicio_hora').value;
    const horaFin = document.getElementById('masivo_fin_hora').value;
    const observaciones = document.getElementById('masivo_obs').value;
    
    const diasCheckboxes = document.querySelectorAll('.dia-cb:checked');
    const dias = Array.from(diasCheckboxes).map(cb => parseInt(cb.value));
    
    if (!fechaInicio || !fechaFin) {
        alert('Debes seleccionar las fechas');
        return;
    }
    
    if (dias.length === 0) {
        alert('Selecciona al menos un día de la semana');
        return;
    }
    
    if (new Date(fechaFin) < new Date(fechaInicio)) {
        alert('La fecha final debe ser posterior a la inicial');
        return;
    }
    
    mostrarLoading(true);
    
    try {
        const response = await fetch('secciones/horario_guardar_masivo.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                dias: dias,
                tipo_jornada: tipo,
                hora_inicio: (tipo === 'TRABAJO' || tipo === 'MEDICO') ? horaInicio : null,
                hora_fin: (tipo === 'TRABAJO' || tipo === 'MEDICO') ? horaFin : null,
                observaciones: observaciones
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`✅ Guardados ${data.dias_guardados} días`);
            cerrarModalMasivo();
            cargarCalendario();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    } finally {
        mostrarLoading(false);
    }
}

// ============================================
// COPIAR MES ANTERIOR
// ============================================
async function copiarMesAnterior() {
    if (!confirm('¿Copiar el horario del mes anterior como plantilla?')) {
        return;
    }
    
    mostrarLoading(true);
    
    const mes = mesActual.getMonth() + 1;
    const anio = mesActual.getFullYear();
    
    try {
        const response = await fetch('secciones/api/copiar_mes_anterior.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ mes: mes, anio: anio })
        });
        
        const data = await response.json();
        
        if (data.success) {
            cargarCalendario();
            alert(`✅ ${data.dias_copiados} días copiados del mes anterior`);
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    } finally {
        mostrarLoading(false);
    }
}

// ============================================
// ENVIAR A VALIDACIÓN
// ============================================
async function enviarValidacion() {
    if (totalTemporales === 0) {
        alert('⚠️ No hay eventos pendientes de enviar.\n\nCrea eventos primero haciendo clic en los días del calendario.');
        return;
    }

    if (!confirm(`¿Enviar ${totalTemporales} eventos a validación?\n\nEl administrador los revisará y no podrás modificarlos hasta que sean aprobados o rechazados.`)) {
        return;
    }
    
    mostrarLoading(true);
    
    try {
        const response = await fetch('secciones/api/enviar_solicitud.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        });
        
        const data = await response.json();
        
        if (data.success) {
            cargarCalendario();
            alert(`✅ ¡Solicitud enviada!\n\n• ${data.dias_enviados} días\n• Tipo: ${data.tipo_solicitud}\n\nEl administrador la revisará pronto.`);
        } else {
            alert('❌ Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error de conexión');
    } finally {
        mostrarLoading(false);
    }
}

// ============================================
// UTILIDADES
// ============================================
function mostrarLoading(mostrar) {
    const overlay = document.getElementById('loadingOverlay');
    if (mostrar) {
        overlay.classList.add('active');
    } else {
        overlay.classList.remove('active');
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('modalEditarDia');
    const modalMasivo = document.getElementById('modalMasivo');
    if (event.target === modal) {
        cerrarModal();
    }
    if (event.target === modalMasivo) {
        cerrarModalMasivo();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btnMasivo');
    if (btn) {
        btn.addEventListener('click', abrirModalMasivo);
    }
});

