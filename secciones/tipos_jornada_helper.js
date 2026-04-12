/**
 * tipos_jornada_helper.js
 * Ubicación: secciones/tipos_jornada_helper.js
 *
 * Guard anti-doble-carga: panel.php y horario_cuadrantes_scripts.php
 * incluyen este archivo. El guard evita redeclarar variables.
 */
if (typeof window._tiposJornadaLoaded === 'undefined') {
    window._tiposJornadaLoaded = true;
    window._tiposCache = null;

    /**
     * Rellena uno o varios <select> con los tipos genéricos + plantillas
     * custom activas de la empresa actual.
     *
     * @param {string|string[]|Element} selectores  CSS selector, array de selectores, o elemento DOM
     * @param {object} opciones
     *   - mostrarProductivos   {bool}  incluir tipos que cuentan como horas (default: true)
     *   - mostrarNoProductivos {bool}  incluir vacaciones, libre, etc. (default: true)
     *   - incluirCustom        {bool}  incluir plantillas de empresa (default: true)
     */
    window.cargarTiposJornada = async function(selectores, opciones = {}) {
        const {
            mostrarProductivos   = true,
            mostrarNoProductivos = true,
            incluirCustom        = true,
        } = opciones;

        // Cargar tipos desde la API (con caché para no repetir llamadas)
        if (!window._tiposCache) {
            try {
                // URL relativa al root del proyecto (panel.php).
                // panel.php está en la raíz, por lo que esta ruta es siempre válida.
                const res = await fetch('secciones/api/obtener_tipos_jornada.php');
                if (!res.ok) {
                    throw new Error('Error HTTP ' + res.status + ' al cargar tipos de jornada');
                }
                window._tiposCache = await res.json();
            } catch (e) {
                console.error('[tipos_jornada_helper] No se pudieron cargar los tipos:', e.message);
                return;
            }
        }

        if (!window._tiposCache || !window._tiposCache.success) return;

        const lista = Array.isArray(selectores) ? selectores : [selectores];

        lista.forEach(sel => {
            const el = typeof sel === 'string' ? document.querySelector(sel) : sel;
            if (!el) return;

            const valorAnterior = el.value; // para restaurar la selección al recargar
            el.innerHTML = '';

            // ── Grupo tipos genéricos ─────────────────────────────
            const grpGen = document.createElement('optgroup');
            grpGen.label = 'Tipos generales';

            (window._tiposCache.genericos || [])
                .filter(t =>
                    (mostrarProductivos   || !t.es_productivo) &&
                    (mostrarNoProductivos ||  t.es_productivo)
                )
                .forEach(t => grpGen.appendChild(_buildOption(t)));

            el.appendChild(grpGen);

            // ── Grupo plantillas custom ───────────────────────────
            const customs = (window._tiposCache.custom || [])
                .filter(t =>
                    (mostrarProductivos   || !t.es_productivo) &&
                    (mostrarNoProductivos ||  t.es_productivo)
                );

            if (incluirCustom && customs.length > 0) {
                const grpCus = document.createElement('optgroup');
                grpCus.label = '── Plantillas de la empresa ──';
                customs.forEach(t => grpCus.appendChild(_buildOption(t)));
                el.appendChild(grpCus);
            }

            // Restaurar valor seleccionado anteriormente si aún existe
            if (valorAnterior) el.value = valorAnterior;
        });
    };

    /** Construye un <option> con todos los data-* necesarios */
    window._buildOption = function(t) {
        const opt = document.createElement('option');
        opt.value              = t.valor_enum || 'TRABAJO';
        opt.dataset.inicio     = t.inicio        || '';
        opt.dataset.fin        = t.fin           || '';
        opt.dataset.color      = t.color         || '#667eea';
        opt.dataset.productivo = t.es_productivo ? '1' : '0';
        opt.dataset.idCustom   = (t.id_tipo_custom != null) ? t.id_tipo_custom : '';
        opt.textContent        = t.label;
        return opt;
    };

    /**
     * Lee los data-* de la opción seleccionada en un <select> de jornada.
     *
     * @param  {HTMLSelectElement} selectEl
     * @returns {{ tipoJornada, idTipoCustom, horaInicio, horaFin, color, esProductivo }}
     */
    window.getDatosOpcionJornada = function(selectEl) {
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
    };
}