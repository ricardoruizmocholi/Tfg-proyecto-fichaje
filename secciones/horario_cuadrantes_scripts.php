<script>
// Tipos que necesitan horas
const TIPOS_CON_HORAS = ['TRABAJO', 'MEDICO', 'PARTIDA_M', 'PARTIDA_T'];

function editarCelda(celda) {
    const idHorario = celda.getAttribute('data-id-horario');
    const idUsuario = celda.getAttribute('data-id-usuario');
    const fecha     = celda.getAttribute('data-fecha');
    
    if (idHorario) {
        fetch(`secciones/horario_obtener.php?id=${idHorario}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('edit_id_horario').value = data.horario.id_horario;
                    document.getElementById('edit_tipo').value       = data.horario.tipo_jornada;
                    document.getElementById('edit_inicio').value     = data.horario.hora_inicio ? data.horario.hora_inicio.substring(0,5) : '';
                    document.getElementById('edit_fin').value        = data.horario.hora_fin    ? data.horario.hora_fin.substring(0,5)    : '';
                    document.getElementById('edit_obs').value        = data.horario.observaciones || '';
                    toggleHoras();
                }
            });
    } else {
        document.getElementById('edit_id_horario').value = '';
        document.getElementById('edit_tipo').value       = 'TRABAJO';
        document.getElementById('edit_inicio').value     = '09:00';
        document.getElementById('edit_fin').value        = '17:00';
        document.getElementById('edit_obs').value        = '';
        toggleHoras();
    }
    
    document.getElementById('edit_id_usuario').value = idUsuario;
    document.getElementById('edit_fecha').value       = fecha;
    document.getElementById('tituloModal').textContent = idHorario ? 'Editar Horario' : 'Añadir Horario';
    document.getElementById('modalEditar').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modalEditar').style.display = 'none';
}

function toggleHoras() {
    const tipo = document.getElementById('edit_tipo').value;
    document.getElementById('horasDiv').style.display = TIPOS_CON_HORAS.includes(tipo) ? 'block' : 'none';

    // Sugerir horas según tramo de partida
    if (tipo === 'PARTIDA_M') {
        document.getElementById('edit_inicio').value = '09:00';
        document.getElementById('edit_fin').value    = '14:00';
    } else if (tipo === 'PARTIDA_T') {
        document.getElementById('edit_inicio').value = '16:00';
        document.getElementById('edit_fin').value    = '19:00';
    }
}

function guardarHorario(event) {
    event.preventDefault();
    
    const tipo = document.getElementById('edit_tipo').value;
    const datos = {
        id_horario:   document.getElementById('edit_id_horario').value || null,
        id_usuario:   document.getElementById('edit_id_usuario').value,
        id_empresa:   <?= $empresa ?>,
        fecha:        document.getElementById('edit_fecha').value,
        tipo_jornada: tipo,
        hora_inicio:  TIPOS_CON_HORAS.includes(tipo) ? (document.getElementById('edit_inicio').value || null) : null,
        hora_fin:     TIPOS_CON_HORAS.includes(tipo) ? (document.getElementById('edit_fin').value    || null) : null,
        observaciones: document.getElementById('edit_obs').value || null
    };
    
    fetch('secciones/horario_guardar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(datos)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Guardado correctamente');
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('❌ Error de conexión'); });
    
    return false;
}

function eliminarHorario(event, idHorario) {
    event.stopPropagation();
    if (!confirm('¿Eliminar este horario?')) return;
    
    fetch('secciones/horario_eliminar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id_horario: idHorario})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('❌ Error: ' + data.message);
    });
}

function abrirModalMasivo() {
    document.getElementById('modalMasivo').style.display = 'block';
    toggleHorasMasivo();
}

function cerrarModalMasivo() {
    document.getElementById('modalMasivo').style.display = 'none';
}

function toggleHorasMasivo() {
    const tipo = document.getElementById('masivo_tipo').value;
    document.getElementById('horasMasivoDiv').style.display = TIPOS_CON_HORAS.includes(tipo) ? 'block' : 'none';

    // Sugerir horas según tramo
    if (tipo === 'PARTIDA_M') {
        document.getElementById('masivo_inicio_hora').value = '09:00';
        document.getElementById('masivo_fin_hora').value    = '14:00';
    } else if (tipo === 'PARTIDA_T') {
        document.getElementById('masivo_inicio_hora').value = '16:00';
        document.getElementById('masivo_fin_hora').value    = '19:00';
    } else if (tipo === 'TRABAJO') {
        document.getElementById('masivo_inicio_hora').value = '09:00';
        document.getElementById('masivo_fin_hora').value    = '17:00';
    }
}

function guardarMasivos(event) {
    event.preventDefault();
    
    const empleadosSelect = document.getElementById('masivo_emps');
    const empleados = Array.from(empleadosSelect.selectedOptions).map(opt => opt.value);
    const dias      = Array.from(document.querySelectorAll('.dia-cb:checked')).map(cb => parseInt(cb.value));
    
    if (empleados.length === 0) { alert('Selecciona al menos un empleado'); return false; }
    if (dias.length === 0)       { alert('Selecciona al menos un día');      return false; }
    
    const tipo = document.getElementById('masivo_tipo').value;
    
    const datos = {
        empleados:    empleados,
        fecha_inicio: document.getElementById('masivo_inicio').value,
        fecha_fin:    document.getElementById('masivo_fin').value,
        dias:         dias,
        tipo_jornada: tipo,
        hora_inicio:  TIPOS_CON_HORAS.includes(tipo) ? (document.getElementById('masivo_inicio_hora').value || null) : null,
        hora_fin:     TIPOS_CON_HORAS.includes(tipo) ? (document.getElementById('masivo_fin_hora').value    || null) : null,
        observaciones: document.getElementById('masivo_obs').value || null,
        id_empresa:   <?= $empresa ?>
    };
    
    fetch('secciones/horario_guardar_masivo_admin.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(datos)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Guardados ' + data.insertados + ' registros');
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(err => { console.error(err); alert('❌ Error de conexión'); });
    
    return false;
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>