<?php
// Formulario para añadir nuevo empleado
?>
<link rel="stylesheet" href="css/empleado.css">

<div class="section-header">
    <h2> Añadir Nuevo Empleado</h2>
    <p>Completa los datos del nuevo empleado de tu empresa</p>
</div>

<div class="breadcrumb">
    <a href="panel.php?seccion=empleados&vista=lista">← Volver a lista de empleados</a>
</div>

<div class="form-container">
    <form id="formNuevoEmpleado" onsubmit="return guardarNuevoEmpleado(event)">

        <!-- ── Datos Personales ── -->
        <div class="form-section">
            <h3> Datos Personales</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre: *</label>
                    <input type="text" id="nuevo_nombre" required placeholder="Ej: Juan">
                </div>
                <div class="form-group">
                    <label>Apellidos: *</label>
                    <input type="text" id="nuevo_apellidos" required placeholder="Ej: García López">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>NIF / DNI:</label>
                    <input type="text" id="nuevo_nif" maxlength="20" placeholder="12345678A">
                </div>
                <div class="form-group">
                    <label>Nº Afiliación S.S.:</label>
                    <input type="text" id="nuevo_afiliacion" maxlength="30" placeholder="12 3456789012">
                </div>
            </div>
        </div>

        <!-- ── Acceso ── -->
        <div class="form-section">
            <h3> Datos de Acceso</h3>
            <div class="form-group">
                <label>Email: *</label>
                <input type="email" id="nuevo_email" required placeholder="empleado@ejemplo.com">
                <small>Este será el usuario para iniciar sesión</small>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Contraseña: *</label>
                    <input type="password" id="nuevo_password" required minlength="6" placeholder="Mínimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label>Confirmar Contraseña: *</label>
                    <input type="password" id="nuevo_password_confirm" required minlength="6" placeholder="Repite la contraseña">
                </div>
            </div>
            <div class="password-strength" id="passwordStrength"></div>
        </div>

        <!-- ── Contrato ── -->
        <div class="form-section" style="border-left-color:#28a745;">
            <h3> Contrato y Jornada</h3>
            <p style="font-size:13px;color:#666;margin:0 0 15px;">
                Determina las vacaciones generadas (2.5 días/mes a jornada completa, proporcional a parcial), 
                las horas contratadas al mes y el límite de horas extra anuales.
            </p>
            <div class="form-row">
                <div class="form-group">
                    <label>Tipo de jornada: *</label>
                    <select id="nuevo_tipo_contrato" onchange="ajustarContratoNuevo()">
                        <option value="completa">Jornada Completa (40h/sem)</option>
                        <option value="parcial">Jornada Parcial</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Horas contratadas al mes: *</label>
                    <input type="number" id="nuevo_horas_mes" value="160" min="1" max="240" step="1">
                    <small>Completa = 160h · Media jornada = 80h</small>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Días de vacaciones anuales: *</label>
                    <input type="number" id="nuevo_vacaciones" value="30" min="0" max="60" step="0.5">
                    <small>Se calculan a razón de 2.5 días/mes trabajado (proporcional a la jornada)</small>
                </div>
                <div class="form-group">
                    <label>Límite horas extra al año:</label>
                    <input type="number" id="nuevo_limite_extra" value="80" min="0" max="80" step="1">
                    <small>Máximo legal = 80h/año (Estatuto de los Trabajadores)</small>
                </div>
            </div>
        </div>

        <!-- ── Empresa ── -->
        <div class="form-section">
            <h3> Configuración en la Empresa</h3>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="nuevo_admin">
                    <span> Este usuario será administrador</span>
                </label>
                <small>Los administradores tienen acceso completo para gestionar empleados, horarios, fichajes, etc.</small>
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="nuevo_activo" checked>
                    <span> Usuario activo desde el inicio</span>
                </label>
            </div>
        </div>

        <div class="form-actions-sticky">
            <button type="button" class="btn-secondary" onclick="window.location.href='panel.php?seccion=empleados&vista=lista'">
                 Cancelar
            </button>
            <button type="submit" class="btn-primary">
                 Guardar Empleado
            </button>
        </div>
    </form>
</div>



<script>
// Ajusta horas/vacaciones automáticamente al cambiar el tipo de contrato
function ajustarContratoNuevo() {
    const tipo = document.getElementById('nuevo_tipo_contrato').value;
    if (tipo === 'completa') {
        document.getElementById('nuevo_horas_mes').value  = 160;
        document.getElementById('nuevo_vacaciones').value = 30;
    } else {
        document.getElementById('nuevo_horas_mes').value  = 80;
        document.getElementById('nuevo_vacaciones').value = 15;
    }
}

// Indicador de fuerza de contraseña
document.getElementById('nuevo_password').addEventListener('input', function() {
    const pw = this.value;
    const div = document.getElementById('passwordStrength');
    if (!pw.length) { div.style.display='none'; return; }
    let s = 0;
    if (pw.length >= 6) s++;
    if (pw.length >= 8) s++;
    if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) s++;
    if (/\d/.test(pw)) s++;
    if (/[^a-zA-Z\d]/.test(pw)) s++;
    div.className = 'password-strength';
    if (s <= 2) { div.classList.add('password-weak');   div.textContent = '⚠️ Contraseña débil'; }
    else if (s <= 3) { div.classList.add('password-medium'); div.textContent = '🔶 Contraseña media'; }
    else              { div.classList.add('password-strong'); div.textContent = '✅ Contraseña fuerte'; }
});

function guardarNuevoEmpleado(event) {
    event.preventDefault();

    const password = document.getElementById('nuevo_password').value;
    const passwordConfirm = document.getElementById('nuevo_password_confirm').value;

    if (password !== passwordConfirm) { alert('❌ Las contraseñas no coinciden'); return false; }
    if (password.length < 6)          { alert('❌ Mínimo 6 caracteres'); return false; }

    const datos = {
        nombre:                      document.getElementById('nuevo_nombre').value.trim(),
        apellidos:                   document.getElementById('nuevo_apellidos').value.trim(),
        NIF:                         document.getElementById('nuevo_nif').value.trim()        || null,
        Numero_Afiliciacion:         document.getElementById('nuevo_afiliacion').value.trim() || null,
        email:                       document.getElementById('nuevo_email').value,
        password:                    password,
        tipo_contrato:               document.getElementById('nuevo_tipo_contrato').value,
        horas_contrato_mes:          parseInt(document.getElementById('nuevo_horas_mes').value),
        vacaciones_totales_anuales:  parseFloat(document.getElementById('nuevo_vacaciones').value),
        horas_extra_anuales_limite:  parseInt(document.getElementById('nuevo_limite_extra').value),
        admin:                       document.getElementById('nuevo_admin').checked   ? 1 : 0,
        activo:                      document.getElementById('nuevo_activo').checked  ? 1 : 0,
    };

    const btn = event.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = ' Guardando...';

    fetch('secciones/empleado_crear.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(datos)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Empleado creado correctamente');
            window.location.href = 'panel.php?seccion=empleados&vista=lista';
        } else {
            alert('❌ Error: ' + data.message);
            btn.disabled = false;
            btn.textContent = ' Guardar Empleado';
        }
    })
    .catch(() => {
        alert('❌ Error de conexión');
        btn.disabled = false;
        btn.textContent = ' Guardar Empleado';
    });

    return false;
}

// Verificar email duplicado en tiempo real
document.getElementById('nuevo_email').addEventListener('blur', function() {
    if (this.value) {
        fetch(`secciones/empleado_verificar_email.php?email=${encodeURIComponent(this.value)}`)
            .then(r => r.json())
            .then(d => { if (d.existe) { alert('⚠️ Este email ya está registrado'); this.focus(); } });
    }
});
</script>