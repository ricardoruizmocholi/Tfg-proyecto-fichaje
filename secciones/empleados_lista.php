<?php
// Lista de empleados de la empresa

// Obtener empleados de la empresa
$sqlEmpleados = "SELECT 
    U.id_usuario,
    U.nombre,
    U.apellidos,
    U.NIF,
    U.Numero_Afiliciacion,
    U.email,
    U.foto_perfil,
    U.activo as usuario_activo,
    U.created_at,
    EU.admin,
    EU.activo as empresa_activo
FROM USUARIO U
JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
WHERE EU.id_empresa = ?
ORDER BY U.nombre, U.apellidos";

$stmtEmpleados = $pdo->prepare($sqlEmpleados);
$stmtEmpleados->execute([$empresa]);
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="section-header">
    <h2> Gestión de Empleados</h2>
    <p>Administra los empleados de tu empresa</p>
</div>

<div class="acciones-header">
    <div class="stats-rapidas">
        <div class="stat-item">
            <span class="stat-label">Total Empleados:</span>
            <span class="stat-value"><?= count($empleados) ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Activos:</span>
            <span class="stat-value"><?= count(array_filter($empleados, fn($e) => $e['empresa_activo'] == 1)) ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Administradores:</span>
            <span class="stat-value"><?= count(array_filter($empleados, fn($e) => $e['admin'] == 1)) ?></span>
        </div>
    </div>
    <a href="panel.php?seccion=empleados&vista=nuevo" class="btn-primary">
        ➕ Añadir Empleado
    </a>
</div>

<?php if (count($empleados) === 0): ?>
    <div class="no-results">
        <p>📭 No hay empleados registrados en esta empresa</p>
        <a href="panel.php?seccion=empleados&vista=nuevo" class="btn-primary" style="margin-top:20px;display:inline-block;text-decoration:none;">
            ➕ Añadir Primer Empleado
        </a>
    </div>
<?php else: ?>
    <div class="table-container">
        <table class="empleados-table">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nombre Completo</th>
                    <th>Email</th>
                    <th>NIF</th>
                    <th>Nº Afiliación</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Fecha Alta</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empleados as $emp): ?>
                    <tr class="<?= $emp['empresa_activo'] == 0 ? 'empleado-inactivo' : '' ?>">
                        <td>
                            <img src="<?= htmlspecialchars($emp['foto_perfil']) ?>" 
                                 alt="Foto" 
                                 class="empleado-foto"
                                 onerror="this.src='secciones/uploads/perfil_default.jpg'">
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($emp['email']) ?></td>
                        <td><?= htmlspecialchars($emp['NIF'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($emp['Numero_Afiliciacion'] ?? '-') ?></td>
                        <td>
                            <?php if ($emp['admin'] == 1): ?>
                                <span class="badge badge-admin"> Administrador</span>
                            <?php else: ?>
                                <span class="badge badge-empleado"> Empleado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($emp['empresa_activo'] == 1): ?>
                                <span class="badge badge-activo">✅ Activo</span>
                            <?php else: ?>
                                <span class="badge badge-inactivo">❌ Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($emp['created_at'])) ?></td>
                        <td class="acciones-col">
                            <button class="btn-action btn-edit" 
                                    onclick="editarEmpleado(<?= $emp['id_usuario'] ?>)" 
                                    title="Editar">
                                ✏️
                            </button>
                            
                            <?php if ($emp['empresa_activo'] == 1): ?>
                                <button class="btn-action btn-deactivate" 
                                        onclick="cambiarEstadoEmpleado(<?= $emp['id_usuario'] ?>, 0)" 
                                        title="Desactivar">
                                    🚫
                                </button>
                            <?php else: ?>
                                <button class="btn-action btn-activate" 
                                        onclick="cambiarEstadoEmpleado(<?= $emp['id_usuario'] ?>, 1)" 
                                        title="Activar">
                                    ✅
                                </button>
                            <?php endif; ?>
                            
                            <button class="btn-action btn-delete" 
                                    onclick="eliminarEmpleado(<?= $emp['id_usuario'] ?>)" 
                                    title="Eliminar">
                                🗑️
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Modal para editar empleado -->
<div id="modalEditarEmpleado" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="cerrarModalEditar()">&times;</span>
        <h3>✏️ Editar Empleado</h3>
        
        <form id="formEditarEmpleado" onsubmit="return guardarCambios(event)">
            <input type="hidden" id="edit_id_usuario">
            
            <div class="form-section">
                <h4>📋 Datos Personales</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre: *</label>
                        <input type="text" id="edit_nombre" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Apellidos: *</label>
                        <input type="text" id="edit_apellidos" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>NIF:</label>
                        <input type="text" id="edit_nif" maxlength="20">
                    </div>
                    
                    <div class="form-group">
                        <label>Nº Afiliación S.S.:</label>
                        <input type="text" id="edit_afiliacion" maxlength="30">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email: *</label>
                    <input type="email" id="edit_email" required>
                </div>
            </div>
            
            <div class="form-section">
                <h4> Configuración en la Empresa</h4>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="edit_admin">
                        <span> Este usuario es administrador</span>
                    </label>
                    <small>Los administradores tienen acceso completo a la gestión</small>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="edit_activo" checked>
                        <span>✅ Usuario activo en la empresa</span>
                    </label>
                    <small>Los usuarios inactivos no pueden acceder al sistema</small>
                </div>
            </div>
            
            <div class="form-section">
                <h4>🔐 Cambiar Contraseña</h4>
                <small style="color:#666;">Deja en blanco si no quieres cambiarla</small>
                
                <div class="form-group">
                    <label>Nueva Contraseña:</label>
                    <input type="password" id="edit_password" minlength="6">
                </div>
                
                <div class="form-group">
                    <label>Confirmar Contraseña:</label>
                    <input type="password" id="edit_password_confirm" minlength="6">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">💾 Guardar Cambios</button>
                <button type="button" class="btn-secondary" onclick="cerrarModalEditar()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<style>
.section-header {
    margin-bottom: 20px;
}

.acciones-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stats-rapidas {
    display: flex;
    gap: 30px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.stat-label {
    font-size: 13px;
    color: #666;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #667eea;
}

.table-container {
    background: white;
    border-radius: 8px;
    overflow-x: auto;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.empleados-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.empleados-table th {
    background: #667eea;
    color: white;
    padding: 12px 10px;
    text-align: left;
    font-weight: 600;
    white-space: nowrap;
}

.empleados-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #eee;
}

.empleados-table tbody tr:hover {
    background: #f8f9fa;
}

.empleado-inactivo {
    opacity: 0.6;
    background: #f8d7da !important;
}

.empleado-foto {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ddd;
}

.badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
    display: inline-block;
}

.badge-admin {
    background: #ffd700;
    color: #856404;
}

.badge-empleado {
    background: #e3f2fd;
    color: #1976d2;
}

.badge-activo {
    background: #d4edda;
    color: #155724;
}

.badge-inactivo {
    background: #f8d7da;
    color: #721c24;
}

.acciones-col {
    white-space: nowrap;
}

.btn-action {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 18px;
    padding: 5px 8px;
    transition: transform 0.2s;
}

.btn-action:hover {
    transform: scale(1.2);
}

.btn-primary {
    background: #667eea;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover {
    background: #5568d3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
}

.no-results {
    background: white;
    padding: 60px 40px;
    text-align: center;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Modal */
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background: white;
    margin: 2% auto;
    padding: 30px;
    width: 90%;
    max-width: 700px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    max-height: 90vh;
    overflow-y: auto;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 20px;
}

.close:hover {
    color: #000;
}

.form-section {
    margin-bottom: 25px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.form-section h4 {
    margin-bottom: 15px;
    color: #333;
    font-size: 16px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
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
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    cursor: pointer;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 2px solid #eee;
}
</style>

<script>
function editarEmpleado(idUsuario) {
    console.log('Editando empleado ID:', idUsuario);
    
    // Obtener datos del empleado
    fetch(`secciones/empleado_obtener.php?id=${idUsuario}`)
        .then(res => {
            console.log('Response status:', res.status);
            return res.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            
            if (data.success) {
                const emp = data.empleado;
                document.getElementById('edit_id_usuario').value = emp.id_usuario;
                document.getElementById('edit_nombre').value = emp.nombre || '';
                document.getElementById('edit_apellidos').value = emp.apellidos || '';
                document.getElementById('edit_nif').value = emp.NIF || '';
                document.getElementById('edit_afiliacion').value = emp.Numero_Afiliciacion || '';
                document.getElementById('edit_email').value = emp.email || '';
                document.getElementById('edit_admin').checked = emp.admin == 1;
                document.getElementById('edit_activo').checked = emp.activo == 1;
                document.getElementById('edit_password').value = '';
                document.getElementById('edit_password_confirm').value = '';
                
                document.getElementById('modalEditarEmpleado').style.display = 'block';
            } else {
                alert('❌ Error al cargar empleado: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Error completo:', err);
            alert('❌ Error de conexión: ' + err.message);
        });
}

function cerrarModalEditar() {
    document.getElementById('modalEditarEmpleado').style.display = 'none';
}

function guardarCambios(event) {
    event.preventDefault();
    
    console.log('Guardando cambios...');
    
    const password = document.getElementById('edit_password').value;
    const passwordConfirm = document.getElementById('edit_password_confirm').value;
    
    if (password && password !== passwordConfirm) {
        alert('❌ Las contraseñas no coinciden');
        return false;
    }
    
    if (password && password.length < 6) {
        alert('❌ La contraseña debe tener al menos 6 caracteres');
        return false;
    }
    
    const datos = {
        id_usuario: document.getElementById('edit_id_usuario').value,
        nombre: document.getElementById('edit_nombre').value.trim(),
        apellidos: document.getElementById('edit_apellidos').value.trim(),
        NIF: document.getElementById('edit_nif').value.trim() || null,
        Numero_Afiliciacion: document.getElementById('edit_afiliacion').value.trim() || null,
        email: document.getElementById('edit_email').value.trim(),
        admin: document.getElementById('edit_admin').checked ? 1 : 0,
        activo: document.getElementById('edit_activo').checked ? 1 : 0,
        password: password || null
    };
    
    console.log('Datos a enviar:', datos);
    
    fetch('secciones/empleado_actualizar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(datos)
    })
    .then(res => {
        console.log('Response status:', res.status);
        return res.json();
    })
    .then(data => {
        console.log('Respuesta del servidor:', data);
        
        if (data.success) {
            alert('✅ Empleado actualizado correctamente');
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error completo:', err);
        alert('❌ Error de conexión: ' + err.message);
    });
    
    return false;
}

function cambiarEstadoEmpleado(idUsuario, nuevoEstado) {
    const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
    if (!confirm(`¿Seguro que quieres ${accion} este empleado?`)) return;
    
    console.log('Cambiando estado - Usuario:', idUsuario, 'Nuevo estado:', nuevoEstado);
    
    fetch('secciones/empleado_cambiar_estado.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            id_usuario: idUsuario,
            activo: nuevoEstado
        })
    })
    .then(res => {
        console.log('Response status:', res.status);
        return res.json();
    })
    .then(data => {
        console.log('Respuesta del servidor:', data);
        
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error completo:', err);
        alert('❌ Error de conexión: ' + err.message);
    });
}

function eliminarEmpleado(idUsuario) {
    if (!confirm('⚠️ ¿Seguro que quieres ELIMINAR este empleado?\n\nEsta acción eliminará:\n- Sus fichajes\n- Sus horarios\n- Sus vacaciones\n\n¿Continuar?')) return;
    
    console.log('Eliminando empleado ID:', idUsuario);
    
    fetch('secciones/empleado_eliminar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id_usuario: idUsuario})
    })
    .then(res => {
        console.log('Response status:', res.status);
        return res.json();
    })
    .then(data => {
        console.log('Respuesta del servidor:', data);
        
        if (data.success) {
            alert('✅ Empleado eliminado correctamente');
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error completo:', err);
        alert('❌ Error de conexión: ' + err.message);
    });
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>