/**
 * tipos_jornada_helper.js
 * Carga los tipos de jornada (genéricos + custom de la empresa)
 * y rellena uno o varios <select> de la página.
 *
 * Uso:
 *   await cargarTiposJornada(['#edit_tipo', '#masivo_tipo']);
 *   // o para un select concreto:
 *   await cargarTiposJornada('#inputTipo', { mostrarSoloProductivos: true });
 */

let _tiposCache = null; // cache para no pedir la API múltiples veces por carga

async function cargarTiposJornada(selectores, opciones = {}) {
    const {
        valorSeleccionado  = null,   // pre-seleccionar un valor
        mostrarProductivos = true,   // incluir tipos productivos
        mostrarNoProductivos = true, // incluir vacaciones, médico, etc.
        incluirCustom      = true,   // incluir las plantillas personalizadas
    } = opciones;

    // Obtener tipos (con caché)
    if (!_tiposCache) {
        try {
            const res  = await fetch('api/obtener_tipos_jornada.php');
            _tiposCache = await res.json();
        } catch (e) {
            console.error('Error cargando tipos de jornada:', e);
            return;
        }
    }

    if (!_tiposCache.success) return;

    // Construir lista de opciones
    const todos = [
        ..._tiposCache.genericos,
        ...(incluirCustom ? _tiposCache.custom : []),
    ].filter(t => {
        if (!mostrarProductivos   && t.es_productivo) return false;
        if (!mostrarNoProductivos && !t.es_productivo) return false;
        return true;
    });

    // Rellenar cada selector
    const lista = Array.isArray(selectores) ? selectores : [selectores];
    lista.forEach(sel => {
        const el = typeof sel === 'string' ? document.querySelector(sel) : sel;
        if (!el) return;

        const valorActual = valorSeleccionado ?? el.value;
        el.innerHTML = '';

        // Grupo genéricos
        const grpGen = document.createElement('optgroup');
        grpGen.label = 'Tipos generales';
        _tiposCache.genericos
            .filter(t => (mostrarProductivos || !t.es_productivo) && (mostrarNoProductivos || t.es_productivo))
            .forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.valor_enum;
                // data-* para autorellenar horas y color
                opt.dataset.inicio      = t.inicio  || '';
                opt.dataset.fin         = t.fin     || '';
                opt.dataset.color       = t.color   || '#667eea';
                opt.dataset.productivo  = t.es_productivo;
                opt.dataset.idCustom    = '';
                opt.textContent = t.label;
                grpGen.appendChild(opt);
            });
        el.appendChild(grpGen);

        // Grupo custom (si hay)
        if (incluirCustom && _tiposCache.custom.length > 0) {
            const grpCus = document.createElement('optgroup');
            grpCus.label = '── Plantillas de la empresa ──';
            _tiposCache.custom
                .filter(t => (mostrarProductivos || !t.es_productivo) && (mostrarNoProductivos || t.es_productivo))
                .forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.valor_enum; // siempre 'TRABAJO' como fallback enum
                    opt.dataset.inicio      = t.inicio  || '';
                    opt.dataset.fin         = t.fin     || '';
                    opt.dataset.color       = t.color   || '#667eea';
                    opt.dataset.productivo  = t.es_productivo;
                    opt.dataset.idCustom    = t.id_tipo_custom;  // ← clave: id de la plantilla
                    opt.textContent = t.label;
                    grpCus.appendChild(opt);
                });
            el.appendChild(grpCus);
        }

        // Restaurar valor seleccionado si existe
        if (valorActual) el.value = valorActual;
    });
}

/**
 * Extrae datos de la opción seleccionada de un <select> de jornada.
 * Devuelve { tipoJornada, idTipoCustom, horaInicio, horaFin, color, esProductivo }
 */
function getDatosOpcionJornada(selectEl) {
    const opt = selectEl.options[selectEl.selectedIndex];
    if (!opt) return null;
    return {
        tipoJornada  : opt.value,
        idTipoCustom : opt.dataset.idCustom ? parseInt(opt.dataset.idCustom) : null,
        horaInicio   : opt.dataset.inicio   || null,
        horaFin      : opt.dataset.fin      || null,
        color        : opt.dataset.color    || '#667eea',
        esProductivo : opt.dataset.productivo === '1',
    };
}