<link rel="stylesheet" href="css/fichaje_modificar.css">

<?php
// Vista para modificar fichajes - VERSIÓN CORREGIDA CON MODAL
?>

<div class="section-header">
    <h2> Modificar Fichajes</h2>
    <p>Busca, edita y añade fichajes de tus empleados</p>
</div>

<div class="filter-container">
    <form method="GET" action="panel.php" class="filter-form">
        <input type="hidden" name="seccion" value="fichaje">
        <input type="hidden" name="vista" value="modificar">
        
        <div class="filter-group">
            <label>Empleado:</label>
            <select name="empleado">
                <option value="">Todos</option>
                <?php
                $stmtEmpleados = $pdo->prepare("
                    SELECT U.id_usuario, U.nombre, U.apellidos 
                    FROM USUARIO U
                    JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
                    WHERE EU.id_empresa = ? AND EU.activo = 1
                    ORDER BY U.nombre, U.apellidos
                ");
                $stmtEmpleados->execute([$empresa]);
                $empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);
                
                $filtroEmpleado = $_GET['empleado'] ?? '';
                foreach ($empleados as $emp) {
                    $selected = ($filtroEmpleado == $emp['id_usuario']) ? 'selected' : '';
                    echo "<option value='{$emp['id_usuario']}' $selected>{$emp['nombre']} {$emp['apellidos']}</option>";
                }
                ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Desde:</label>
            <input type="date" name="fecha_desde" value="<?= $_GET['fecha_desde'] ?? date('Y-m-01') ?>">
        </div>
        
        <div class="filter-group">
            <label>Hasta:</label>
            <input type="date" name="fecha_hasta" value="<?= $_GET['fecha_hasta'] ?? date('Y-m-d') ?>">
        </div>
        
        <button type="submit" class="btn-primary">🔍 Buscar</button>
        <button type="button" class="btn-success" onclick="abrirModalAnadir()">➕ Añadir Fichaje</button>
    </form>
</div>

<?php
// Obtener fichajes según filtros
$sql = "SELECT F.*, U.nombre, U.apellidos 
        FROM FICHAJE F
        JOIN USUARIO U ON F.id_usuario = U.id_usuario
        JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
        WHERE EU.id_empresa = ? AND EU.activo = 1";

$params = [$empresa];

if (!empty($_GET['empleado'])) {
    $sql .= " AND F.id_usuario = ?";
    $params[] = $_GET['empleado'];
}

if (!empty($_GET['fecha_desde'])) {
    $sql .= " AND F.fecha >= ?";
    $params[] = $_GET['fecha_desde'];
}

if (!empty($_GET['fecha_hasta'])) {
    $sql .= " AND F.fecha <= ?";
    $params[] = $_GET['fecha_hasta'];
}

$sql .= " ORDER BY F.fecha DESC, F.hora_entrada DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$fichajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="stats-info">
    <p><strong>Total fichajes encontrados:</strong> <?= count($fichajes) ?></p>
</div>

<?php if (count($fichajes) > 0): ?>
    <div class="table-container">
        <table class="fichajes-table">
            <thead>
                <tr>
                    <th style="width:60px;">ID</th>
                    <th>Empleado</th>
                    <th>Fecha</th>
                    <th>Entrada</th>
                    <th>Pausa</th>
                    <th>Reanudación</th>
                    <th>Salida</th>
                    <th>Horas</th>
                    <th>Tipo</th>
                    <th>Observaciones</th>
                    <th style="width:120px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fichajes as $f): 

                    // Valor por defecto
                    $horasTrabajadas = '0:00';
                    $segundosTotales = 0;

                    if ($f['hora_entrada'] && $f['hora_salida']) {

                        $entrada = strtotime($f['hora_entrada']);
                        $salida  = strtotime($f['hora_salida']);

                        $pausa = 0;
                        if ($f['hora_pausa'] && $f['hora_reanudacion']) {
                            $pausa = strtotime($f['hora_reanudacion']) - strtotime($f['hora_pausa']);
                        }

                        $segundosTotales = $salida - $entrada ;

                        if ($segundosTotales > 0) {
                            $horas   = floor($segundosTotales / 3600);
                            $minutos = floor(($segundosTotales % 3600) / 60);

                            // Formato HH:MM
                            $horasTrabajadas = sprintf('%d:%02d', $horas, $minutos);
                        }
                    }

                    // Determinar color de fila
                    $claseEstado = '';
                    if ($f['hora_entrada'] && $f['hora_salida']) {
                        $claseEstado = 'fila-completo';
                    } elseif ($f['hora_entrada'] && !$f['hora_salida']) {
                        $claseEstado = 'fila-proceso';
                    }

                ?>

                <tr id="fila-<?= $f['id_fichaje'] ?>" class="<?= $claseEstado ?>">
                    <td><?= $f['id_fichaje'] ?></td>
                    <td><strong><?= htmlspecialchars($f['nombre'] . ' ' . $f['apellidos']) ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($f['fecha'])) ?></td>
                    <td>
                        <input type="time" 
                               value="<?= $f['hora_entrada'] ? substr($f['hora_entrada'], 0, 5) : '' ?>" 
                               class="input-time" 
                               data-id="<?= $f['id_fichaje'] ?>" 
                               data-campo="hora_entrada">
                    </td>
                    <td>
                        <input type="time" 
                               value="<?= $f['hora_pausa'] ? substr($f['hora_pausa'], 0, 5) : '' ?>" 
                               class="input-time" 
                               data-id="<?= $f['id_fichaje'] ?>" 
                               data-campo="hora_pausa">
                    </td>
                    <td>
                        <input type="time" 
                               value="<?= $f['hora_reanudacion'] ? substr($f['hora_reanudacion'], 0, 5) : '' ?>" 
                               class="input-time" 
                               data-id="<?= $f['id_fichaje'] ?>" 
                               data-campo="hora_reanudacion">
                    </td>
                    <td>
                        <input type="time" 
                               value="<?= $f['hora_salida'] ? substr($f['hora_salida'], 0, 5) : '' ?>" 
                               class="input-time" 
                               data-id="<?= $f['id_fichaje'] ?>" 
                               data-campo="hora_salida">
                    </td>
                    <td style="text-align:center;">
                        <span class="horas-badge"><?= $horasTrabajadas > 0 ? $horasTrabajadas . 'h' : '-' ?></span>
                    </td>
                    <td>
                        <select class="input-select" data-id="<?= $f['id_fichaje'] ?>" data-campo="tipo">
                            <option value="normal" <?= $f['tipo'] === 'normal' ? 'selected' : '' ?>>Normal</option>
                            <option value="extra" <?= $f['tipo'] === 'extra' ? 'selected' : '' ?>>Extra</option>
                            <option value="festivo" <?= $f['tipo'] === 'festivo' ? 'selected' : '' ?>>Festivo</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" 
                               value="<?= htmlspecialchars($f['observaciones'] ?? '') ?>" 
                               class="input-text" 
                               data-id="<?= $f['id_fichaje'] ?>" 
                               data-campo="observaciones"
                               placeholder="Observaciones...">
                    </td>
                    <td style="text-align:center;">
                        <button class="btn-save" onclick="guardarFichaje(<?= $f['id_fichaje'] ?>)" title="Guardar">💾</button>
                        <button class="btn-delete" onclick="eliminarFichaje(<?= $f['id_fichaje'] ?>)" title="Eliminar">🗑️</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="no-results">
        <p>📭 No se encontraron fichajes con los filtros aplicados</p>
        <p style="margin-top:15px;">
            <button class="btn-success" onclick="abrirModalAnadir()">➕ Añadir Nuevo Fichaje</button>
        </p>
    </div>
<?php endif; ?>

<!-- Modal para añadir fichaje -->
<div id="modalAnadirFichaje" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <h3>➕ Añadir Nuevo Fichaje</h3>
        <form id="formAnadirFichaje" onsubmit="return anadirFichaje(event)">
            <div class="form-group">
                <label>Empleado: *</label>
                <select name="id_usuario" required>
                    <option value="">Seleccionar empleado</option>
                    <?php foreach ($empleados as $emp): ?>
                        <option value="<?= $emp['id_usuario'] ?>">
                            <?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Fecha: *</label>
                <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Hora Entrada:</label>
                    <input type="time" name="hora_entrada">
                </div>
                
                <div class="form-group">
                    <label>Hora Salida:</label>
                    <input type="time" name="hora_salida">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Hora Pausa:</label>
                    <input type="time" name="hora_pausa">
                </div>
                
                <div class="form-group">
                    <label>Hora Reanudación:</label>
                    <input type="time" name="hora_reanudacion">
                </div>
            </div>
            
            <div class="form-group">
                <label>Tipo:</label>
                <select name="tipo">
                    <option value="normal">Normal</option>
                    <option value="extra">Extra</option>
                    <option value="festivo">Festivo</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Observaciones:</label>
                <textarea name="observaciones" rows="3" placeholder="Detalles adicionales..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">💾 Guardar</button>
                <button type="button" class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>



<script>
function guardarFichaje(idFichaje) {
    const fila = document.getElementById('fila-' + idFichaje);
    const inputs = fila.querySelectorAll('[data-id="' + idFichaje + '"]');
    
    const datos = {
        id_fichaje: idFichaje
    };
    
    inputs.forEach(input => {
        const campo = input.getAttribute('data-campo');
        datos[campo] = input.value || null;
    });
    
    fetch('secciones/fichaje_modificar_guardar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(datos)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Resaltar fila guardada
            fila.style.background = '#90EE90';
            setTimeout(() => {
                fila.style.background = '';
                location.reload();
            }, 1000);
            alert('✅ Fichaje guardado correctamente');
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('❌ Error de conexión');
    });
}

function eliminarFichaje(idFichaje) {
    if (!confirm('¿Estás seguro de eliminar este fichaje?')) return;
    
    fetch('secciones/fichaje_eliminar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id_fichaje: idFichaje})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('fila-' + idFichaje).remove();
            alert('✅ Fichaje eliminado');
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('❌ Error de conexión');
    });
}

// Modal funciones
function abrirModalAnadir() {
    document.getElementById('modalAnadirFichaje').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modalAnadirFichaje').style.display = 'none';
    document.getElementById('formAnadirFichaje').reset();
}

function anadirFichaje(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const datos = Object.fromEntries(formData);
    
    fetch('secciones/fichaje_anadir.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(datos)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Fichaje añadido correctamente');
            cerrarModal();
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('❌ Error de conexión');
    });
    
    return false;
}

// Cerrar modal al hacer click fuera
window.onclick = function(event) {
    const modal = document.getElementById('modalAnadirFichaje');
    if (event.target === modal) {
        cerrarModal();
    }
}
</script>