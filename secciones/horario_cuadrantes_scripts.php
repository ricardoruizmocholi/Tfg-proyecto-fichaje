<script src="secciones/tipos_jornada_helper.js"></script>

<script src="tipos_jornada_helper.js"></script>
<script>
// Tipos genéricos que necesitan horas (fallback para cuando no hay custom)
const TIPOS_CON_HORAS = ['TRABAJO', 'MEDICO', 'PARTIDA_M', 'PARTIDA_T'];

// ── Inicialización ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
    // Rellenar ambos selects con tipos genéricos + custom
    await cargarTiposJornada(['#edit_tipo', '#masivo_tipo']);

    // Cuando el usuario cambia el tipo en el modal individual
    document.getElementById('edit_tipo').addEventListener('change', function() {
        aplicarDatosOpcion(this, 'edit_inicio', 'edit_fin', 'horasDiv');
    });
    document.getElementById('masivo_tipo').addEventListener('change', function() {
        aplicarDatosOpcion(this, 'masivo_inicio_hora', 'masivo_fin_hora', 'horasMasivoDiv');
    });
});

/**
 * Aplica hora inicio/fin predeterminadas y muestra/oculta el bloque de horas
 * según la opción seleccionada (genérica o custom).
 */
function aplicarDatosOpcion(selectEl, idInicio, idFin, idContenedor) {
    const datos = getDatosOpcionJornada(selectEl);
    if (!datos) return;

    const mostrar = datos.esProductivo || datos.tipoJornada === 'MEDICO';
    document.getElementById(idContenedor).style.display = mostrar ? 'block' : 'none';

    if (datos.horaInicio) document.getElementById(idInicio).value = datos.horaInicio;
    if (datos.horaFin)    document.getElementById(idFin).value    = datos.horaFin;
}

// ── Modal editar celda ──────────────────────────────────────
function editarCelda(celda) {
    const idHorario  = celda.getAttribute('data-id-horario');
    const idUsuario  = celda.getAttribute('data-id-usuario');
    const fecha      = celda.getAttribute('data-fecha');
    const idCustom   = celda.getAttribute('data-id-custom') || '';

    if (idHorario) {
        fetch(`secciones/horario_obtener.php?id=${idHorario}`)
            .then(r => r.json())
            .then(async d => {
                if (!d.success) return;
                const h = d.horario;

                // Recargar select con todos los tipos
                await cargarTiposJornada('#edit_tipo');

                // Si tiene custom: seleccionar por data-id-custom
                const sel = document.getElementById('edit_tipo');
                if (h.id_tipo_custom) {
                    // Buscar la option con ese data-id-custom
                    [...sel.options].forEach(o => {
                        if (o.dataset.idCustom == h.id_tipo_custom) sel.value = o.value;
                    });
                    // Guardar referencia para enviarlo al backend
                    sel.dataset.currentCustom = h.id_tipo_custom;
                } else {
                    sel.value = h.tipo_jornada;
                    sel.dataset.currentCustom = '';
                }

                document.getElementById('edit_id_horario').value = h.id_horario;
                document.getElementById('edit_inicio').value     = h.hora_inicio ? h.hora_inicio.substr(0,5) : '';
                document.getElementById('edit_fin').value        = h.hora_fin    ? h.hora_fin.substr(0,5)    : '';
                document.getElementById('edit_obs').value        = h.observaciones || '';
                aplicarDatosOpcion(sel, 'edit_inicio', 'edit_fin', 'horasDiv');
            });
    } else {
        (async () => {
            await cargarTiposJornada('#edit_tipo');
            document.getElementById('edit_id_horario').value = '';
            document.getElementById('edit_inicio').value     = '09:00';
            document.getElementById('edit_fin').value        = '17:00';
            document.getElementById('edit_obs').value        = '';
            document.getElementById('edit_tipo').dataset.currentCustom = '';
            aplicarDatosOpcion(document.getElementById('edit_tipo'), 'edit_inicio', 'edit_fin', 'horasDiv');
        })();
    }

    document.getElementById('edit_id_usuario').value = idUsuario;
    document.getElementById('edit_fecha').value      = fecha;
    document.getElementById('tituloModal').textContent = idHorario ? 'Editar Horario' : 'Añadir Horario';
    document.getElementById('modalEditar').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modalEditar').style.display = 'none';
}

// ── Guardar horario individual ──────────────────────────────
function guardarHorario(event) {
    event.preventDefault();

    const sel        = document.getElementById('edit_tipo');
    const datos      = getDatosOpcionJornada(sel);
    const mostrar    = document.getElementById('horasDiv').style.display !== 'none';

    const payload = {
        id_horario:    document.getElementById('edit_id_horario').value || null,
        id_usuario:    document.getElementById('edit_id_usuario').value,
        id_empresa:    <?= $empresa ?>,
        fecha:         document.getElementById('edit_fecha').value,
        tipo_jornada:  datos.tipoJornada,
        id_tipo_custom: datos.idTipoCustom,
        hora_inicio:   mostrar ? (document.getElementById('edit_inicio').value || null) : null,
        hora_fin:      mostrar ? (document.getElementById('edit_fin').value    || null) : null,
        observaciones: document.getElementById('edit_obs').value || null
    };

    fetch('secciones/horario_guardar.php', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { alert('✅ Guardado'); location.reload(); }
        else alert('❌ ' + d.message);
    })
    .catch(() => alert('❌ Error de conexión'));

    return false;
}

// ── Eliminar horario ────────────────────────────────────────
function eliminarHorario(event, idHorario) {
    event.stopPropagation();
    if (!confirm('¿Eliminar este horario?')) return;
    fetch('secciones/horario_eliminar.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({id_horario: idHorario})
    })
    .then(r=>r.json())
    .then(d=>{ if(d.success) location.reload(); else alert('❌ '+d.message); });
}

// ── Modal masivo ────────────────────────────────────────────
function abrirModalMasivo() {
    document.getElementById('modalMasivo').style.display = 'block';
    aplicarDatosOpcion(
        document.getElementById('masivo_tipo'),
        'masivo_inicio_hora', 'masivo_fin_hora', 'horasMasivoDiv'
    );
}

function cerrarModalMasivo() {
    document.getElementById('modalMasivo').style.display = 'none';
}

function guardarMasivos(event) {
    event.preventDefault();

    const empleados = Array.from(document.getElementById('masivo_emps').selectedOptions).map(o => o.value);
    const dias      = Array.from(document.querySelectorAll('.dia-cb:checked')).map(cb => parseInt(cb.value));
    if (!empleados.length) { alert('Selecciona al menos un empleado'); return false; }
    if (!dias.length)       { alert('Selecciona al menos un día');      return false; }

    const sel    = document.getElementById('masivo_tipo');
    const datos  = getDatosOpcionJornada(sel);
    const mostrar= document.getElementById('horasMasivoDiv').style.display !== 'none';

    const payload = {
        empleados,
        fecha_inicio: document.getElementById('masivo_inicio').value,
        fecha_fin:    document.getElementById('masivo_fin').value,
        dias,
        tipo_jornada:  datos.tipoJornada,
        id_tipo_custom: datos.idTipoCustom,
        hora_inicio:   mostrar ? document.getElementById('masivo_inicio_hora').value : null,
        hora_fin:      mostrar ? document.getElementById('masivo_fin_hora').value    : null,
        observaciones: document.getElementById('masivo_obs').value || null,
        id_empresa:    <?= $empresa ?>
    };

    fetch('secciones/horario_guardar_masivo_admin.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify(payload)
    })
    .then(r=>r.json())
    .then(d=>{
        if(d.success){ alert('✅ '+d.insertados+' registros guardados'); location.reload(); }
        else alert('❌ '+d.message);
    })
    .catch(()=>alert('❌ Error de conexión'));
    return false;
}

window.onclick = e => {
    if (e.target.classList.contains('modal')) e.target.style.display = 'none';
};
</script>