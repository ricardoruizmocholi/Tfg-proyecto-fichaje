<?php
// Lista de empleados de la empresa

$sqlEmpleados = "SELECT 
    U.id_usuario, U.nombre, U.apellidos, U.NIF, U.Numero_Afiliciacion,
    U.email, U.foto_perfil, U.activo as usuario_activo, U.created_at,
    U.tipo_contrato, U.horas_contrato_mes,
    U.vacaciones_totales_anuales, U.horas_extra_anuales_limite,
    EU.admin, EU.activo as empresa_activo
FROM USUARIO U
JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
WHERE EU.id_empresa = ?
ORDER BY U.nombre, U.apellidos";

$stmtEmpleados = $pdo->prepare($sqlEmpleados);
$stmtEmpleados->execute([$empresa]);
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="css/empleado_listado.css">

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
         Añadir Empleado
    </a>
</div>

<?php if (count($empleados) === 0): ?>
    <div class="no-results">
        <p> No hay empleados registrados en esta empresa</p>
        <a href="panel.php?seccion=empleados&vista=nuevo" class="btn-primary" style="margin-top:20px;display:inline-block;text-decoration:none;">
             Añadir Primer Empleado
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
                    <th>Contrato</th>
                    <th>Vacaciones</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empleados as $emp): ?>
                    <tr class="<?= $emp['empresa_activo'] == 0 ? 'empleado-inactivo' : '' ?>">
                        <td>
                            <img src="<?= htmlspecialchars($emp['foto_perfil']) ?>" 
                                 alt="Foto" class="empleado-foto"
                                 onerror="this.src='secciones/uploads/perfil_default.jpg'">
                        </td>
                        <td><strong><?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']) ?></strong></td>
                        <td><?= htmlspecialchars($emp['email']) ?></td>
                        <td>
                            <span class="badge <?= $emp['tipo_contrato'] === 'parcial' ? 'badge-warning' : 'badge-info' ?>">
                                <?= $emp['tipo_contrato'] === 'parcial' ? '½ Parcial' : 'Completa' ?>
                            </span>
                            <div style="font-size:11px;color:#666;margin-top:2px;"><?= $emp['horas_contrato_mes'] ?? 160 ?>h/mes</div>
                        </td>
                        <td>
                            <span style="font-weight:700;color:#667eea;"><?= $emp['vacaciones_totales_anuales'] ?? 30 ?></span>
                            <span style="font-size:11px;color:#666;"> días/año</span>
                        </td>
                        <td>
                            <?php if ($emp['admin'] == 1): ?>
                                <span class="badge badge-admin"> Admin</span>
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
                        <td class="acciones-col">
                            <button class="btn-action btn-edit" onclick="editarEmpleado(<?= $emp['id_usuario'] ?>)" title="Editar">✏️</button>
                            <?php if ($emp['empresa_activo'] == 1): ?>
                                <button class="btn-action btn-deactivate" onclick="cambiarEstadoEmpleado(<?= $emp['id_usuario'] ?>, 0)" title="Desactivar">🚫</button>
                            <?php else: ?>
                                <button class="btn-action btn-activate" onclick="cambiarEstadoEmpleado(<?= $emp['id_usuario'] ?>, 1)" title="Activar">✅</button>
                            <?php endif; ?>
                            <button class="btn-action btn-delete" onclick="eliminarEmpleado(<?= $emp['id_usuario'] ?>)" title="Eliminar">🗑️</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- ============================================================
     MODAL EDITAR EMPLEADO — con sección de contrato
============================================================= -->
<div id="modalEditarEmpleado" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:780px;">
        <span class="close" onclick="cerrarModalEditar()">&times;</span>
        <h3> Editar Empleado</h3>
        
        <form id="formEditarEmpleado" onsubmit="return guardarCambios(event)">
            <input type="hidden" id="edit_id_usuario">

            <!-- ── Datos Personales ── -->
            <div class="form-section">
                <h4> Datos Personales</h4>
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

            <!-- ── Contrato ── -->
            <div class="form-section" style="border-left-color:#28a745;">
                <h4> Contrato y Jornada</h4>
                <p style="font-size:13px;color:#666;margin:0 0 15px;">
                    Estos valores determinan las vacaciones generadas, las horas contratadas 
                    y el límite de horas extra del empleado.
                </p>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tipo de jornada:</label>
                        <select id="edit_tipo_contrato" onchange="ajustarHorasYVacaciones()">
                            <option value="completa">Jornada Completa (40h/sem)</option>
                            <option value="parcial">Jornada Parcial</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Horas contratadas al mes:</label>
                        <input type="number" id="edit_horas_mes" min="1" max="240" step="1">
                        <small>Completa = 160h · Media jornada = 80h</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Días de vacaciones anuales:</label>
                        <input type="number" id="edit_vacaciones" min="0" max="60" step="0.5">
                        <small>Completa = 30 días · Media jornada = 15 días (proporcional)</small>
                    </div>
                    <div class="form-group">
                        <label>Límite horas extra al año:</label>
                        <input type="number" id="edit_limite_extra" min="0" max="80" step="1" value="80">
                        <small>Máximo legal = 80h/año según Estatuto de los Trabajadores</small>
                    </div>
                </div>
            </div>

            <!-- ── Rol en la empresa ── -->
            <div class="form-section">
                <h4> Configuración en la Empresa</h4>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="edit_admin">
                        <span> Este usuario es administrador</span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="edit_activo" checked>
                        <span> Usuario activo en la empresa</span>
                    </label>
                </div>
            </div>

            <!-- ── Cambiar contraseña ── -->
            <div class="form-section">
                <h4> Cambiar Contraseña</h4>
                <small style="color:#666;">Deja en blanco si no quieres cambiarla</small>
                <div class="form-row" style="margin-top:10px;">
                    <div class="form-group">
                        <label>Nueva Contraseña:</label>
                        <input type="password" id="edit_password" minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirmar Contraseña:</label>
                        <input type="password" id="edit_password_confirm" minlength="6">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">💾 Guardar Cambios</button>
                <button type="button" class="btn-secondary" onclick="cerrarModalEditar()">Cancelar</button>
            </div>
        </form>
    </div>
</div>



<script>
function editarEmpleado(idUsuario) {
    fetch(`secciones/empleado_obtener.php?id=${idUsuario}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) { alert('❌ Error: ' + data.message); return; }
            const emp = data.empleado;

            // Datos personales
            document.getElementById('edit_id_usuario').value    = emp.id_usuario;
            document.getElementById('edit_nombre').value        = emp.nombre || '';
            document.getElementById('edit_apellidos').value     = emp.apellidos || '';
            document.getElementById('edit_nif').value           = emp.NIF || '';
            document.getElementById('edit_afiliacion').value    = emp.Numero_Afiliciacion || '';
            document.getElementById('edit_email').value         = emp.email || '';
            document.getElementById('edit_admin').checked       = emp.admin == 1;
            document.getElementById('edit_activo').checked      = emp.activo == 1;

            // Contrato
            document.getElementById('edit_tipo_contrato').value = emp.tipo_contrato || 'completa';
            document.getElementById('edit_horas_mes').value     = emp.horas_contrato_mes || 160;
            document.getElementById('edit_vacaciones').value    = emp.vacaciones_totales_anuales || 30;
            document.getElementById('edit_limite_extra').value  = emp.horas_extra_anuales_limite || 80;

            // Contraseña en blanco
            document.getElementById('edit_password').value         = '';
            document.getElementById('edit_password_confirm').value = '';

            document.getElementById('modalEditarEmpleado').style.display = 'block';
        })
        .catch(err => alert('❌ Error de conexión: ' + err.message));
}

// Ajustar automáticamente horas y vacaciones al cambiar el tipo
function ajustarHorasYVacaciones() {
    const tipo = document.getElementById('edit_tipo_contrato').value;
    if (tipo === 'completa') {
        document.getElementById('edit_horas_mes').value  = 160;
        document.getElementById('edit_vacaciones').value = 30;
    } else {
        document.getElementById('edit_horas_mes').value  = 80;
        document.getElementById('edit_vacaciones').value = 15;
    }
}

function cerrarModalEditar() {
    document.getElementById('modalEditarEmpleado').style.display = 'none';
}

function guardarCambios(event) {
    event.preventDefault();

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
        id_usuario:                  document.getElementById('edit_id_usuario').value,
        nombre:                      document.getElementById('edit_nombre').value.trim(),
        apellidos:                   document.getElementById('edit_apellidos').value.trim(),
        NIF:                         document.getElementById('edit_nif').value.trim() || null,
        Numero_Afiliciacion:         document.getElementById('edit_afiliacion').value.trim() || null,
        email:                       document.getElementById('edit_email').value.trim(),
        admin:                       document.getElementById('edit_admin').checked ? 1 : 0,
        activo:                      document.getElementById('edit_activo').checked ? 1 : 0,
        password:                    password || null,
        // Contrato
        tipo_contrato:               document.getElementById('edit_tipo_contrato').value,
        horas_contrato_mes:          parseInt(document.getElementById('edit_horas_mes').value),
        vacaciones_totales_anuales:  parseFloat(document.getElementById('edit_vacaciones').value),
        horas_extra_anuales_limite:  parseInt(document.getElementById('edit_limite_extra').value),
    };

    fetch('secciones/empleado_actualizar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(datos)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Empleado actualizado correctamente');
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(err => alert('❌ Error de conexión: ' + err.message));

    return false;
}

function cambiarEstadoEmpleado(idUsuario, nuevoEstado) {
    if (!confirm(`¿Seguro que quieres ${nuevoEstado == 1 ? 'activar' : 'desactivar'} este empleado?`)) return;
    fetch('secciones/empleado_cambiar_estado.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id_usuario: idUsuario, activo: nuevoEstado })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) { alert('✅ ' + data.message); location.reload(); }
        else alert('❌ Error: ' + data.message);
    });
}

function eliminarEmpleado(idUsuario) {
    if (!confirm('⚠️ ¿Eliminar este empleado?\n\nSe eliminarán sus fichajes, horarios y vacaciones.')) return;
    fetch('secciones/empleado_eliminar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id_usuario: idUsuario})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) { alert('✅ Empleado eliminado'); location.reload(); }
        else alert('❌ Error: ' + data.message);
    });
}

window.onclick = function(e) {
    if (e.target.classList.contains('modal')) e.target.style.display = 'none';
}
</script>