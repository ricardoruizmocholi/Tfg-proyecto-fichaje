<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FichajeApp — Acceso</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">

    
</head>
<body>

<!-- Fondo hexagonal reactivo -->
<canvas id="hexCanvas"></canvas>
<div class="vignette"></div>

<div class="stage">
    <div class="card">

        <!-- Logo -->
        <div class="logo-wrap">
            <div class="logo-icon">
                <!-- Icono: reloj con checkmark — representa fichaje/jornada -->
                <img class="imagen_logo" src="img/fesol.png" alt="logo">
            </div>
            <div>
                <div class="logo-name">FesolCheck</div>
                <div class="logo-sub">Control de jornada laboral</div>
            </div>
        </div>

        <!-- Cabecera -->
        <div class="card-title">Bienvenido de nuevo</div>
        <div class="card-sub">Introduce tus credenciales para acceder al panel</div>

        <!-- Error banner (oculto por defecto) -->
        <div class="error-banner" id="errorBanner" role="alert">
            <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span id="errorMsg">Credenciales incorrectas</span>
        </div>

        <!-- Formulario -->
        <form id="loginForm" novalidate>
            <div class="field" id="fieldEmail">
                <label for="email">Correo electrónico</label>
                <div class="field-input-wrap">
                    <input type="email" id="email" name="email" placeholder="tu@empresa.com"
                           autocomplete="username" required>
                    <!-- Icono email -->
                    <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="2" y="4" width="20" height="16" rx="3"/>
                        <polyline points="2,4 12,13 22,4"/>
                    </svg>
                </div>
            </div>

            <div class="field" id="fieldPass">
                <label for="password">Contraseña</label>
                <div class="field-input-wrap">
                    <input type="password" id="password" name="password"
                           placeholder="••••••••" autocomplete="current-password" required>
                    <!-- Icono candado -->
                    <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="5" y="11" width="14" height="10" rx="2"/>
                        <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                    </svg>
                    <button type="button" class="toggle-pass" id="togglePass" aria-label="Mostrar contraseña" tabindex="-1">
                        <svg id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="btnSubmit">
                <span class="btn-text">Acceder al panel</span>
                <div class="spinner"></div>
            </button>
        </form>

        <div class="forgot-link">
            <a href="#" id="forgotLink">¿Has olvidado tu contraseña?</a>
        </div>

        <div class="card-footer">
            <div class="status-dot"></div>
            Sistema activo · Sesión cifrada
        </div>
    </div>
</div>

<!-- ══ Modal 1: Introducir email ═══════════════════════════════ -->
<div class="modal-overlay" id="modalEmail">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('modalEmail')">×</button>
        <h3>Recuperar contraseña</h3>
        <p>Introduce tu email y te enviaremos un código de verificación de 6 dígitos.</p>
        <div class="field">
            <label for="emailReset">Email registrado</label>
            <div class="field-input-wrap">
                <input type="email" id="emailReset" placeholder="tu@empresa.com" autocomplete="off">
                <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="4" width="20" height="16" rx="3"/>
                    <polyline points="2,4 12,13 22,4"/>
                </svg>
            </div>
        </div>
        <div id="emailMsg" class="modal-msg"></div>
        <div class="modal-actions">
            <button class="btn-modal-cancel" onclick="closeModal('modalEmail')">Cancelar</button>
            <button class="btn-modal-primary" id="btnSendCode" onclick="sendResetEmail()">Enviar código</button>
        </div>
    </div>
</div>

<!-- ══ Modal 2: Verificar código ═══════════════════════════════ -->
<div class="modal-overlay" id="modalCode">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('modalCode')">×</button>
        <h3>Verificar código</h3>
        <p>Introduce el código de 6 dígitos enviado a tu correo. Expira en 15 minutos.</p>
        <div class="field">
            <label for="codeInput">Código de verificación</label>
            <div class="field-input-wrap">
                <input type="text" id="codeInput" class="code-input"
                       maxlength="6" placeholder="000000" autocomplete="one-time-code"
                       inputmode="numeric" pattern="[0-9]{6}">
            </div>
        </div>
        <div id="codeMsg" class="modal-msg"></div>
        <div class="modal-actions">
            <button class="btn-modal-cancel" onclick="closeModal('modalCode')">Cancelar</button>
            <button class="btn-modal-primary" id="btnVerify" onclick="verifyCode()">Verificar</button>
        </div>
    </div>
</div>

<!-- ══ Modal 3: Nueva contraseña ═══════════════════════════════ -->
<div class="modal-overlay" id="modalNewPass">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('modalNewPass')">×</button>
        <h3>Nueva contraseña</h3>
        <p>Elige una contraseña segura de al menos 6 caracteres.</p>
        <div class="field">
            <label for="newPass1">Nueva contraseña</label>
            <div class="field-input-wrap">
                <input type="password" id="newPass1" placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="5" y="11" width="14" height="10" rx="2"/>
                    <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                </svg>
            </div>
        </div>
        <div class="field">
            <label for="newPass2">Repetir contraseña</label>
            <div class="field-input-wrap">
                <input type="password" id="newPass2" placeholder="Repite la contraseña" autocomplete="new-password">
                <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="5" y="11" width="14" height="10" rx="2"/>
                    <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                </svg>
            </div>
        </div>
        <div id="passMsg" class="modal-msg"></div>
        <div class="modal-actions">
            <button class="btn-modal-cancel" onclick="closeModal('modalNewPass')">Cancelar</button>
            <button class="btn-modal-primary" id="btnChangePass" onclick="changePassword()">Guardar contraseña</button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════════ -->
<script>
/* ────────────────────────────────────────────────────────────
   CANVAS HEXAGONAL REACTIVO
   Cuadrícula de hexágonos que ondean al mover el ratón.
   Cada hexágono calcula su distancia al cursor y brilla/escala.
──────────────────────────────────────────────────────────── */
(function() {
    const canvas = document.getElementById('hexCanvas');
    const ctx    = canvas.getContext('2d');

    const HEX_SIZE    = 36;     // radio del hexágono
    const GAP         = 5;      // separación entre hexágonos
    const WAVE_RADIUS = 180;    // radio de influencia del cursor
    const WAVE_SPEED  = 0.006;  // velocidad de la onda autónoma

    let W, H, cols, rows, hexes = [];
    let mouseX = -999, mouseY = -999;
    let time = 0;

    // Paleta extraída de las CSS vars
    const COLOR_STROKE = 'rgba(102, 126, 234, 0.22)';
    const COLOR_GLOW   = 'rgba(102, 126, 234, ';
    const COLOR_ACCENT = 'rgba(0, 212, 170, ';

    function hexPoints(cx, cy, r) {
        const pts = [];
        for (let i = 0; i < 6; i++) {
            const a = Math.PI / 180 * (60 * i - 30);
            pts.push([cx + r * Math.cos(a), cy + r * Math.sin(a)]);
        }
        return pts;
    }

    function buildGrid() {
        hexes = [];
        const col_w = HEX_SIZE * Math.sqrt(3) + GAP;
        const row_h = HEX_SIZE * 1.5 + GAP * 0.5;
        cols = Math.ceil(W / col_w) + 2;
        rows = Math.ceil(H / row_h) + 2;

        for (let r = -1; r < rows; r++) {
            for (let c = -1; c < cols; c++) {
                const cx = c * col_w + (r % 2 === 0 ? 0 : col_w * 0.5);
                const cy = r * row_h;
                hexes.push({ cx, cy, phase: Math.random() * Math.PI * 2 });
            }
        }
    }

    function resize() {
        W = canvas.width  = window.innerWidth;
        H = canvas.height = window.innerHeight;
        buildGrid();
    }

    function draw() {
        ctx.clearRect(0, 0, W, H);
        time += WAVE_SPEED;

        for (const h of hexes) {
            const dx   = h.cx - mouseX;
            const dy   = h.cy - mouseY;
            const dist = Math.sqrt(dx * dx + dy * dy);

            // Onda autónoma suave
            const wave = Math.sin(time + h.phase) * 0.5 + 0.5;
            // Onda por cursor
            const cursor = dist < WAVE_RADIUS
                ? Math.pow(1 - dist / WAVE_RADIUS, 2.5)
                : 0;

            const intensity = wave * 0.25 + cursor * 0.75;
            const scale     = 1 + cursor * 0.12 + wave * 0.02;
            const r         = HEX_SIZE * scale;

            const pts = hexPoints(h.cx, h.cy, r);
            ctx.beginPath();
            ctx.moveTo(pts[0][0], pts[0][1]);
            for (let i = 1; i < 6; i++) ctx.lineTo(pts[i][0], pts[i][1]);
            ctx.closePath();

            // Fill con glow
            if (intensity > 0.05) {
                // Mezclar entre primary y accent según cursor vs wave
                const useAccent = cursor > 0.3;
                const alpha = intensity * (useAccent ? 0.18 : 0.09);
                ctx.fillStyle = useAccent
                    ? COLOR_ACCENT + alpha + ')'
                    : COLOR_GLOW + alpha + ')';
                ctx.fill();
            }

            // Stroke
            const strokeAlpha = 0.12 + intensity * 0.25;
            ctx.strokeStyle = `rgba(102, 126, 234, ${strokeAlpha})`;
            ctx.lineWidth   = 0.8 + cursor * 0.6;
            ctx.stroke();
        }

        requestAnimationFrame(draw);
    }

    window.addEventListener('resize', resize);
    window.addEventListener('mousemove', e => { mouseX = e.clientX; mouseY = e.clientY; });
    window.addEventListener('touchmove', e => {
        mouseX = e.touches[0].clientX;
        mouseY = e.touches[0].clientY;
    }, { passive: true });

    resize();
    draw();
})();


/* ────────────────────────────────────────────────────────────
   TOGGLE CONTRASEÑA
──────────────────────────────────────────────────────────── */
document.getElementById('togglePass').addEventListener('click', () => {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eyeIcon');
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    icon.innerHTML = isPass
        ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
           <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
           <line x1="1" y1="1" x2="23" y2="23"/>`
        : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
           <circle cx="12" cy="12" r="3"/>`;
});


/* ────────────────────────────────────────────────────────────
   LOGIN VÍA AJAX — sin redirección, errores inline
──────────────────────────────────────────────────────────── */
function showError(msg) {
    const banner   = document.getElementById('errorBanner');
    const msgEl    = document.getElementById('errorMsg');
    const fEmail   = document.getElementById('fieldEmail');
    const fPass    = document.getElementById('fieldPass');

    msgEl.textContent = msg;

    // Remover y re-añadir para re-disparar la animación shake
    banner.classList.remove('visible');
    void banner.offsetWidth; // reflow
    banner.classList.add('visible');

    fEmail.classList.add('has-error');
    fPass.classList.add('has-error');
}

function clearError() {
    document.getElementById('errorBanner').classList.remove('visible');
    document.getElementById('fieldEmail').classList.remove('has-error');
    document.getElementById('fieldPass').classList.remove('has-error');
}

document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    clearError();

    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const btn      = document.getElementById('btnSubmit');

    if (!email || !password) {
        showError('Por favor completa todos los campos');
        return;
    }

    btn.disabled = true;
    btn.classList.add('loading');

    try {
        const form = new FormData();
        form.append('email', email);
        form.append('password', password);

        const res  = await fetch('login_procesar.php', { method: 'POST', body: form });
        const data = await res.json();

        if (data.success) {
            // Animación de salida antes de redirigir
            btn.classList.remove('loading');
            btn.querySelector('.btn-text').textContent = '✓ Accediendo…';
            btn.style.background = 'linear-gradient(135deg, #00d4aa, #00b894)';
            setTimeout(() => { window.location.href = data.redirect; }, 400);
        } else {
            btn.disabled = false;
            btn.classList.remove('loading');
            showError(data.message || 'Email o contraseña incorrectos');
        }
    } catch (err) {
        btn.disabled = false;
        btn.classList.remove('loading');
        showError('Error de conexión. Inténtalo de nuevo.');
    }
});

// Limpiar error al escribir
document.getElementById('email').addEventListener('input', clearError);
document.getElementById('password').addEventListener('input', clearError);


/* ────────────────────────────────────────────────────────────
   MODALES RECUPERAR CONTRASEÑA
──────────────────────────────────────────────────────────── */
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.getElementById('forgotLink').addEventListener('click', e => {
    e.preventDefault();
    document.getElementById('emailReset').value = '';
    document.getElementById('emailMsg').className = 'modal-msg';
    document.getElementById('emailMsg').textContent = '';
    openModal('modalEmail');
});

// Cerrar al hacer clic fuera del modal-box
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) overlay.classList.remove('open');
    });
});

// Cerrar con Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
    }
});

async function sendResetEmail() {
    const email = document.getElementById('emailReset').value.trim();
    const msg   = document.getElementById('emailMsg');
    const btn   = document.getElementById('btnSendCode');
    if (!email) { setModalMsg('emailMsg', 'err', 'Introduce tu email'); return; }

    btn.disabled = true;
    btn.textContent = 'Enviando…';

    try {
        const r = await fetch('recuperar_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email })
        });
        const d = await r.json();
        if (d.success) {
            setModalMsg('emailMsg', 'ok', '✓ Código enviado. Revisa tu bandeja de entrada.');
            setTimeout(() => {
                closeModal('modalEmail');
                document.getElementById('codeInput').value = '';
                document.getElementById('codeMsg').className = 'modal-msg';
                openModal('modalCode');
            }, 1200);
        } else {
            setModalMsg('emailMsg', 'err', d.message || 'Email no registrado');
        }
    } catch {
        setModalMsg('emailMsg', 'err', 'Error de conexión');
    }

    btn.disabled = false;
    btn.textContent = 'Enviar código';
}

async function verifyCode() {
    const code = document.getElementById('codeInput').value.trim();
    const btn  = document.getElementById('btnVerify');
    if (!code || code.length !== 6) { setModalMsg('codeMsg', 'err', 'Introduce los 6 dígitos'); return; }

    btn.disabled = true;
    btn.textContent = 'Verificando…';

    try {
        const r = await fetch('verificar_codigo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code })
        });
        const d = await r.json();
        if (d.success) {
            setModalMsg('codeMsg', 'ok', '✓ Código correcto');
            setTimeout(() => {
                closeModal('modalCode');
                document.getElementById('newPass1').value = '';
                document.getElementById('newPass2').value = '';
                document.getElementById('passMsg').className = 'modal-msg';
                openModal('modalNewPass');
            }, 800);
        } else {
            setModalMsg('codeMsg', 'err', 'Código incorrecto o expirado');
        }
    } catch {
        setModalMsg('codeMsg', 'err', 'Error de conexión');
    }

    btn.disabled = false;
    btn.textContent = 'Verificar';
}

async function changePassword() {
    const p1  = document.getElementById('newPass1').value;
    const p2  = document.getElementById('newPass2').value;
    const btn = document.getElementById('btnChangePass');

    if (p1.length < 6) { setModalMsg('passMsg', 'err', 'Mínimo 6 caracteres'); return; }
    if (p1 !== p2)     { setModalMsg('passMsg', 'err', 'Las contraseñas no coinciden'); return; }

    btn.disabled = true;
    btn.textContent = 'Guardando…';

    try {
        const r = await fetch('cambiar_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password: p1 })
        });
        const d = await r.json();
        if (d.success) {
            setModalMsg('passMsg', 'ok', '✓ Contraseña actualizada. Ya puedes iniciar sesión.');
            setTimeout(() => { closeModal('modalNewPass'); }, 2000);
        } else {
            setModalMsg('passMsg', 'err', d.message || 'Error al cambiar contraseña');
        }
    } catch {
        setModalMsg('passMsg', 'err', 'Error de conexión');
    }

    btn.disabled = false;
    btn.textContent = 'Guardar contraseña';
}

function setModalMsg(id, type, text) {
    const el = document.getElementById(id);
    el.className   = 'modal-msg ' + type;
    el.textContent = text;
}

// Solo dígitos en el código
document.getElementById('codeInput').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
});
</script>
</body>
</html>