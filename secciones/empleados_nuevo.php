<?php
// Formulario para añadir nuevo empleado
?>

<div class="section-header">
    <h2>➕ Añadir Nuevo Empleado</h2>
    <p>Completa los datos del nuevo empleado de tu empresa</p>
</div>

<div class="breadcrumb">
    <a href="panel.php?seccion=empleados&vista=lista">← Volver a lista de empleados</a>
</div>

<div class="form-container">
    <form id="formNuevoEmpleado" onsubmit="return guardarNuevoEmpleado(event)">
        
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
                    <small>Opcional</small>
                </div>
                
                <div class="form-group">
                    <label>Nº Afiliación Seguridad Social:</label>
                    <input type="text" id="nuevo_afiliacion" maxlength="30" placeholder="12 3456789012">
                    <small>Opcional</small>
                </div>
            </div>
        </div>
        
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
                    <span>✅ Usuario activo desde el inicio</span>
                </label>
                <small>Si está desactivado, el usuario no podrá acceder al sistema hasta que lo actives</small>
            </div>
        </div>
        
        <div class="form-actions-sticky">
            <button type="button" class="btn-secondary" onclick="window.location.href='panel.php?seccion=empleados&vista=lista'">
                ❌ Cancelar
            </button>
            <button type="submit" class="btn-primary">
                💾 Guardar Empleado
            </button>
        </div>
    </form>
</div>

<style>
.breadcrumb {
    margin-bottom: 20px;
}

.breadcrumb a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.form-container {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-width: 900px;
    margin: 0 auto;
}

.form-section {
    margin-bottom: 35px;
    padding: 25px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.form-section h3 {
    margin-bottom: 20px;
    color: #333;
    font-size: 18px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group small {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #666;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-weight: normal !important;
    padding: 10px;
    background: white;
    border-radius: 5px;
    transition: background 0.2s;
}

.checkbox-label:hover {
    background: #e3f2fd;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    cursor: pointer;
    transform: scale(1.2);
}

.checkbox-label span {
    font-size: 15px;
    font-weight: 600;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.password-strength {
    margin-top: 10px;
    padding: 10px;
    border-radius: 5px;
    font-size: 13px;
    font-weight: 600;
    display: none;
}

.password-weak {
    background: #f8d7da;
    color: #721c24;
    display: block;
}

.password-medium {
    background: #fff3cd;
    color: #856404;
    display: block;
}

.password-strong {
    background: #d4edda;
    color: #155724;
    display: block;
}

.form-actions-sticky {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    padding: 20px;
    background: white;
    border-top: 3px solid #eee;
    position: sticky;
    bottom: 0;
    margin: 0 -30px -30px -30px;
    border-radius: 0 0 8px 8px;
}

.btn-primary {
    background: #667eea;
    color: white;
    padding: 14px 30px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.3s;
}

.btn-primary:hover {
    background: #5568d3;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    background: #6c757d;
    color: white;
    padding: 14px 30px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.3s;
}

.btn-secondary:hover {
    background: #5a6268;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions-sticky {
        flex-direction: column;
    }
}
</style>

<script>
// Validar fuerza de contraseña
document.getElementById('nuevo_password').addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('passwordStrength');
    
    if (password.length === 0) {
        strengthDiv.style.display = 'none';
        return;
    }
    
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;
    
    strengthDiv.className = 'password-strength';
    
    if (strength <= 2) {
        strengthDiv.classList.add('password-weak');
        strengthDiv.textContent = '⚠️ Contraseña débil';
    } else if (strength <= 3) {
        strengthDiv.classList.add('password-medium');
        strengthDiv.textContent = '🔶 Contraseña media';
    } else {
        strengthDiv.classList.add('password-strong');
        strengthDiv.textContent = '✅ Contraseña fuerte';
    }
});

function guardarNuevoEmpleado(event) {
    event.preventDefault();
    
    // Validaciones
    const password = document.getElementById('nuevo_password').value;
    const passwordConfirm = document.getElementById('nuevo_password_confirm').value;
    
    if (password !== passwordConfirm) {
        alert('❌ Las contraseñas no coinciden');
        return false;
    }
    
    if (password.length < 6) {
        alert('❌ La contraseña debe tener al menos 6 caracteres');
        return false;
    }
    
    const email = document.getElementById('nuevo_email').value;
    if (!email.includes('@')) {
        alert('❌ El email no es válido');
        return false;
    }
    
    // Recopilar datos
    const datos = {
        nombre: document.getElementById('nuevo_nombre').value.trim(),
        apellidos: document.getElementById('nuevo_apellidos').value.trim(),
        NIF: document.getElementById('nuevo_nif').value.trim() || null,
        Numero_Afiliciacion: document.getElementById('nuevo_afiliacion').value.trim() || null,
        email: email,
        password: password,
        admin: document.getElementById('nuevo_admin').checked ? 1 : 0,
        activo: document.getElementById('nuevo_activo').checked ? 1 : 0
    };
    
    // Deshabilitar botón para evitar doble envío
    const btnSubmit = event.target.querySelector('button[type="submit"]');
    btnSubmit.disabled = true;
    btnSubmit.textContent = '⏳ Guardando...';
    
    // Enviar al servidor
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
            btnSubmit.disabled = false;
            btnSubmit.textContent = ' Guardar Empleado';
        }
    })
    .catch(err => {
        console.error(err);
        alert('❌ Error de conexión al servidor');
        btnSubmit.disabled = false;
        btnSubmit.textContent = ' Guardar Empleado';
    });
    
    return false;
}

// Validar email en tiempo real
document.getElementById('nuevo_email').addEventListener('blur', function() {
    const email = this.value;
    if (email) {
        // Verificar si el email ya existe
        fetch(`secciones/empleado_verificar_email.php?email=${encodeURIComponent(email)}`)
            .then(res => res.json())
            .then(data => {
                if (data.existe) {
                    alert('⚠️ Este email ya está registrado en el sistema');
                    this.focus();
                }
            });
    }
});
</script>