'use strict';
/*
 * chatbot.worker.js — SharedWorker del chatbot IA
 *
 * Gestiona la petición fetch a Ollama de forma completamente autónoma.
 * Sobrevive a cambios de sección mientras haya al menos un puerto conectado.
 *
 * Mensajes que acepta (port.postMessage):
 *   { type: 'SEND',         payload: { prompt, model, options, ollamaUrl } }
 *   { type: 'GET_PENDING' }      → responde con PENDING_STATE
 *   { type: 'CLEAR_PENDING' }    → limpia el estado acumulado cuando la UI ya lo recibió
 *   { type: 'ABORT' }            → sólo se usa en cierre real del navegador
 *
 * Mensajes que emite (broadcast a todos los puertos):
 *   { type: 'TOKEN',        token: string }
 *   { type: 'DONE',         fullText: string }
 *   { type: 'ERROR',        message: string }
 *   { type: 'PENDING_STATE', tokens, done, error, active }   (respuesta a GET_PENDING)
 */

const ports = new Set();

// Estado compartido: acumula la respuesta aunque no haya puertos activos
let state = {
    active: false,
    tokens: '',
    done:   false,
    error:  null,
};

let controller = null;  // AbortController — solo se llama explícitamente (ABORT)

// ── Conexión de nuevos puertos ────────────────────────────
self.onconnect = function (e) {
    const port = e.ports[0];
    ports.add(port);
    port.start();

    port.onmessage = function (evt) {
        const { type, payload } = evt.data;

        switch (type) {
            case 'SEND':
                if (state.active) {
                    port.postMessage({ type: 'BUSY' });
                    return;
                }
                startFetch(payload);
                break;

            case 'GET_PENDING':
                // El puerto (re)conectado pregunta si hay respuesta en vuelo o acumulada
                port.postMessage({
                    type:   'PENDING_STATE',
                    tokens: state.tokens,
                    done:   state.done,
                    error:  state.error,
                    active: state.active,
                });
                break;

            case 'CLEAR_PENDING':
                // La UI ya renderizó la respuesta completa; limpiar para la siguiente
                if (!state.active) {
                    state = { active: false, tokens: '', done: false, error: null };
                }
                break;

            case 'ABORT':
                if (controller) controller.abort();
                break;
        }
    };

    // Limpiar el puerto al desconectarse (cierre de pestaña, navegación)
    port.onmessageerror = function () { ports.delete(port); };
};

// ── Emitir a todos los puertos conectados ─────────────────
function broadcast(msg) {
    for (const p of [...ports]) {
        try {
            p.postMessage(msg);
        } catch (e) {
            ports.delete(p);
        }
    }
}

// ── Fetch a Ollama ────────────────────────────────────────
async function startFetch({ prompt, model, options, ollamaUrl }) {
    state      = { active: true, tokens: '', done: false, error: null };
    controller = new AbortController();

    try {
        const response = await fetch(ollamaUrl, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ model, prompt, stream: true, options }),
            signal:  controller.signal,
        });

        const reader  = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop();   // la última línea puede estar incompleta

            for (const line of lines) {
                const raw = line.trim();
                if (!raw) continue;
                try {
                    const json = JSON.parse(raw);
                    if (json.response) {
                        state.tokens += json.response;
                        broadcast({ type: 'TOKEN', token: json.response });
                    }
                    // json.done === true indica fin natural del stream de Ollama
                } catch (e) { /* línea incompleta o no JSON */ }
            }
        }

        state.active = false;
        state.done   = true;
        broadcast({ type: 'DONE', fullText: state.tokens });

    } catch (err) {
        state.active = false;
        if (err.name === 'AbortError') {
            state.error = 'ABORTED';
        } else {
            state.error = err.message;
            broadcast({ type: 'ERROR', message: err.message });
        }
    } finally {
        controller = null;
    }
}
