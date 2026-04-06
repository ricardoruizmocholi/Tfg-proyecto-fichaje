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
        
        if (data.success) {
            horariosData = data.calendario || {};
            totalTemporales = data.total_temporales || 0;
            generarCalendarioHTML();
            actualizarContadorTemporales();
        } else {
            console.error('Error al cargar horarios:', data.message);
            alert('Error al cargar horarios: ' + data.message);
        }
    } catch (error) {
        console.error('Error de conexión:', error);
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

// ============================================
// HELPER: Formatear hora de forma segura
// ============================================
function formatearHora(hora) {
    if (!hora || hora === null || hora === undefined) return '--:--';
    return String(hora).substr(0, 5);
}

// ============================================
// HELPER: Obtener texto del bloque según tipo
// ============================================
function obtenerTextoBloque(evento) {
    const tipo = evento.tipo_jornada;
    
    // Tipos con horas
    if (['TRABAJO', 'MEDICO', 'PARTIDA_M', 'PARTIDA_T'].includes(tipo)) {
        const inicio = formatearHora(evento.hora_inicio);
        const fin    = formatearHora(evento.hora_fin);
        
        if (tipo === 'PARTIDA_M') return `🌅 ${inicio}-${fin}`;
        if (tipo === 'PARTIDA_T') return `🌆 ${inicio}-${fin}`;
        if (tipo === 'MEDICO')    return `🏥 ${inicio}-${fin}`;
        return `${inicio}-${fin}`;
    }
    
    // Tipos sin horas
    const etiquetas = {
        'VACACIONES': '🏖️ Vacaciones',
        'LIBRE':      '💤 Libre',
        'FESTIVO':    '🎉 Festivo'
    };
    return etiquetas[tipo] || tipo.charAt(0) + tipo.slice(1).toLowerCase();
}

// ============================================
// HELPER: Obtener clase CSS según tipo
// ============================================
function obtenerClaseBloque(tipo) {
    const clases = {
        'TRABAJO':   'schedule-trabajo',
        'VACACIONES':'schedule-vacaciones',
        'MEDICO':    'schedule-medico',
        'LIBRE':     'schedule-libre',
        'FESTIVO':   'schedule-festivo',
        'PARTIDA_M': 'schedule-partida-m',
        'PARTIDA_T': 'schedule-partida-t'
    };
    return clases[tipo] || 'schedule-trabajo';
}

// ============================================
// CREAR CELDA DE DÍA
// ============================================
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
        // Si está rechazado no lo mostramos
        if (evento.estado === 'RECHAZADO') return;

        const scheduleBlock = document.createElement('div');
        
        // Clase de estado
        let claseEstado = '';
        if (evento.estado === 'PENDIENTE')      claseEstado = 'status-pendiente';
        else if (evento.estado === 'TEMPORAL')  claseEstado = 'status-temporal';
        else                                     claseEstado = 'status-aprobado';

        const claseVisual = obtenerClaseBloque(evento.tipo_jornada);
        scheduleBlock.className = `schedule-block ${claseVisual} ${claseEstado}`;
        scheduleBlock.style.position = 'relative';

        // Botón eliminar para temporales
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

        // Texto del bloque — SIN substr directo, usando la función segura
        const texto = obtenerTextoBloque(evento);
        const textSpan = document.createElement('span');
        textSpan.textContent = texto;
        scheduleBlock.appendChild(textSpan);
        
        // Click según estado
        if (evento.estado === 'TEMPORAL') {
            scheduleBlock.onclick = (e) => { e.stopPropagation(); editarDia(fecha); };
        } else {
            scheduleBlock.onclick = (e) => { e.stopPropagation(); verEventosDelDia(fecha); };
        }

        cell.appendChild(scheduleBlock);
    });

    // Botón "+" para añadir (solo días del mes actual)
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
    console.log("Intentando eliminar:", fecha, "Orden:", orden_dia);

    mostrarLoading(true);
    try {
        const response = await fetch('secciones/api/eliminar_horario_temporal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                fecha: fecha, 
                orden_dia: parseInt(orden_dia) 
            })
        });

        const text = await response.text();
        const data = JSON.parse(text);

        if (data.success) {
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
    const partidaInfo = document.getElementById('partidaInfo');
    
    const tiposConHoras = ['TRABAJO', 'MEDICO', 'PARTIDA_M', 'PARTIDA_T'];
    horariosContainer.style.display = tiposConHoras.includes(tipo) ? 'block' : 'none';
    
    // Mostrar info especial de jornada partida
    if (partidaInfo) {
        partidaInfo.style.display = (tipo === 'PARTIDA_M' || tipo === 'PARTIDA_T') ? 'block' : 'none';
    }
    
    // Sugerir horas según el tramo
    if (tipo === 'PARTIDA_M') {
        document.getElementById('inputHoraInicio').value = '09:00';
        document.getElementById('inputHoraFin').value = '14:00';
    } else if (tipo === 'PARTIDA_T') {
        document.getElementById('inputHoraInicio').value = '16:00';
        document.getElementById('inputHoraFin').value = '19:00';
    }
}

// ============================================
// GUARDAR DÍA
// ============================================
async function guardarDia() {
    const fecha = document.getElementById('inputFecha').value;
    const tipo = document.getElementById('inputTipo').value;
    const horaInicio = document.getElementById('inputHoraInicio').value;
    const horaFin = document.getElementById('inputHoraFin').value;
    const observaciones = document.getElementById('inputObservaciones').value;
    
    const tiposConHoras = ['TRABAJO', 'MEDICO', 'PARTIDA_M', 'PARTIDA_T'];
    if (tiposConHoras.includes(tipo)) {
        const inicioMin = convertirHoraAMinutos(horaInicio);
        const finMin    = convertirHoraAMinutos(horaFin);
        
        if (finMin <= inicioMin) {
            alert('⚠️ La hora de fin debe ser posterior a la hora de inicio');
            return;
        }
    }
    
    mostrarLoading(true);
    
    try {
        const response = await fetch('secciones/api/guardar_horario_dia.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                fecha: fecha,
                tipo_jornada: tipo,
                hora_inicio: tiposConHoras.includes(tipo) ? horaInicio : null,
                hora_fin:    tiposConHoras.includes(tipo) ? horaFin    : null,
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
    if (!hora) return 0;
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
    
    document.querySelectorAll('.dia-cb').forEach((cb, index) => {
        cb.checked = index < 5;
    });
    
    toggleHorasMasivo();
    document.getElementById('modalMasivo').style.display = 'block';
}

function cerrarModalMasivo() {
    document.getElementById('modalMasivo').style.display = 'none';
}

function toggleHorasMasivo() {
    const tipo = document.getElementById('masivo_tipo').value;
    const tiposConHoras = ['TRABAJO', 'MEDICO', 'PARTIDA_M', 'PARTIDA_T'];
    document.getElementById('horasMasivoDiv').style.display = 
        tiposConHoras.includes(tipo) ? 'block' : 'none';
    
    // Sugerir horas para jornada partida
    if (tipo === 'PARTIDA_M') {
        document.getElementById('masivo_inicio_hora').value = '09:00';
        document.getElementById('masivo_fin_hora').value = '14:00';
    } else if (tipo === 'PARTIDA_T') {
        document.getElementById('masivo_inicio_hora').value = '16:00';
        document.getElementById('masivo_fin_hora').value = '19:00';
    }
}

async function guardarMasivo() {
    const fechaInicio = document.getElementById('masivo_inicio').value;
    const fechaFin    = document.getElementById('masivo_fin').value;
    const tipo        = document.getElementById('masivo_tipo').value;
    const horaInicio  = document.getElementById('masivo_inicio_hora').value;
    const horaFin     = document.getElementById('masivo_fin_hora').value;
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
    
    const tiposConHoras = ['TRABAJO', 'MEDICO', 'PARTIDA_M', 'PARTIDA_T'];
    
    mostrarLoading(true);
    
    try {
        const response = await fetch('secciones/horario_guardar_masivo.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                fecha_inicio: fechaInicio,
                fecha_fin:    fechaFin,
                dias:         dias,
                tipo_jornada: tipo,
                hora_inicio: tiposConHoras.includes(tipo) ? horaInicio : null,
                hora_fin:    tiposConHoras.includes(tipo) ? horaFin    : null,
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
    if (!confirm('¿Copiar el horario del mes anterior como plantilla?')) return;
    
    mostrarLoading(true);
    
    const mes  = mesActual.getMonth() + 1;
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
            alert(`✅ ${data.cantidad} días copiados del mes anterior`);
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

    if (!confirm(`¿Enviar ${totalTemporales} eventos a validación?\n\nEl administrador los revisará y no podrás modificarlos hasta que sean aprobados o rechazados.`)) return;
    
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
// VER EVENTOS DEL DÍA (solo lectura)
// ============================================
function verEventosDelDia(fecha) {
    const eventos = horariosData[fecha] || [];
    if (eventos.length === 0) return;
    
    let info = `📅 ${fecha}\n\n`;
    eventos.forEach(ev => {
        info += `• ${ev.tipo_jornada}`;
        if (ev.hora_inicio && ev.hora_fin) {
            info += `: ${formatearHora(ev.hora_inicio)} - ${formatearHora(ev.hora_fin)}`;
        }
        if (ev.horas_totales) info += ` (${ev.horas_totales}h)`;
        info += `\n  Estado: ${ev.estado}`;
        if (ev.observaciones) info += `\n  Obs: ${ev.observaciones}`;
        info += '\n\n';
    });
    alert(info);
}

// ============================================
// UTILIDADES
// ============================================
function mostrarLoading(mostrar) {
    const overlay = document.getElementById('loadingOverlay');
    if (mostrar) overlay.classList.add('active');
    else         overlay.classList.remove('active');
}

window.onclick = function(event) {
    const modal = document.getElementById('modalEditarDia');
    const modalMasivo = document.getElementById('modalMasivo');
    if (event.target === modal) cerrarModal();
    if (event.target === modalMasivo) cerrarModalMasivo();
}

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btnMasivo');
    if (btn) btn.addEventListener('click', abrirModalMasivo);
});