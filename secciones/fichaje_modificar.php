<link rel="stylesheet" href="css/fichaje_modificar.css">

<?php
// ================================================================
// FICHAJE_MODIFICAR.PHP — Con soporte para jornada partida
// ================================================================

// Empleados de la empresa para el filtro
$stmtEmpleados = $pdo->prepare("
    SELECT U.id_usuario, U.nombre, U.apellidos 
    FROM USUARIO U
    JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
    WHERE EU.id_empresa = ? AND EU.activo = 1
    ORDER BY U.nombre, U.apellidos
");
$stmtEmpleados->execute([$empresa]);
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

// ================================================================
// CONSULTA: trae TODOS los fichajes del rango, incluyendo
// los 2 filas de una jornada partida para el mismo usuario/día.
// ================================================================
$sql = "
    SELECT 
        F.id_fichaje, F.id_usuario, F.fecha,
        F.hora_entrada, F.hora_pausa, F.hora_reanudacion, F.hora_salida,
        F.tipo, F.observaciones,
        U.nombre, U.apellidos,
        ROW_NUMBER() OVER (PARTITION BY F.id_usuario, F.fecha ORDER BY F.id_fichaje ASC) AS num_tramo
    FROM FICHAJE F
    JOIN USUARIO U ON F.id_usuario = U.id_usuario
    JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
    WHERE EU.id_empresa = ? AND EU.activo = 1
";

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

$sql .= " ORDER BY F.fecha DESC, F.id_usuario ASC, F.id_fichaje ASC LIMIT 200";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rawFichajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ================================================================
// AGRUPAR: $grupos[fecha][id_usuario] = [tramo1, tramo2, ...]
// ================================================================
$grupos = [];
foreach ($rawFichajes as $f) {
    $grupos[$f['fecha']][$f['id_usuario']][] = $f;
}

// Helper: calcular horas trabajadas de un fichaje
function horasTrabajadas($f) {
    if (!$f['hora_entrada'] || !$f['hora_salida']) return null;
    $seg = strtotime($f['hora_salida']) - strtotime($f['hora_entrada']);
    if ($f['hora_pausa'] && $f['hora_reanudacion']) {
        $seg -= strtotime($f['hora_reanudacion']) - strtotime($f['hora_pausa']);
    }
    if ($seg <= 0) return null;
    return sprintf('%d:%02d', floor($seg/3600), floor(($seg%3600)/60));
}

// Valores actuales de los filtros
$filtroEmpleado  = $_GET['empleado']    ?? '';
$filtroDesde     = $_GET['fecha_desde'] ?? date('Y-m-01');
$filtroHasta     = $_GET['fecha_hasta'] ?? date('Y-m-d');
?>

<div class="section-header">
    <h2>✏️ Modificar Fichajes</h2>
    <p>Edita o añade fichajes — incluye soporte para jornada partida (2 tramos por día)</p>
</div>

<!-- FILTROS -->
<div class="filter-container">
    <form method="GET" action="panel.php" class="filter-form">
        <input type="hidden" name="seccion" value="fichaje">
        <input type="hidden" name="vista"   value="modificar">
        
        <div class="filter-group">
            <label>Empleado:</label>
            <select name="empleado">
                <option value="">Todos</option>
                <?php foreach ($empleados as $emp): ?>
                    <option value="<?= $emp['id_usuario'] ?>" <?= $filtroEmpleado==$emp['id_usuario']?'selected':'' ?>>
                        <?= htmlspecialchars($emp['nombre'].' '.$emp['apellidos']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Desde:</label>
            <input type="date" name="fecha_desde" value="<?= $filtroDesde ?>">
        </div>
        
        <div class="filter-group">
            <label>Hasta:</label>
            <input type="date" name="fecha_hasta" value="<?= $filtroHasta ?>">
        </div>
        
        <button type="submit" class="btn-primary">🔍 Buscar</button>
        <button type="button" class="btn-success" onclick="abrirModalAnadir()">➕ Añadir Fichaje</button>
    </form>
</div>

<div class="stats-info">
    <p><strong>Registros encontrados:</strong> <?= count($rawFichajes) ?></p>
</div>

<?php if (empty($grupos)): ?>
    <div class="no-results">
        <p>📭 No se encontraron fichajes con los filtros aplicados</p>
        <p style="margin-top:15px;">
            <button class="btn-success" onclick="abrirModalAnadir()">➕ Añadir Nuevo Fichaje</button>
        </p>
    </div>
<?php else: ?>

    <?php foreach ($grupos as $fecha => $usuariosDelDia): ?>
        <!-- ── BLOQUE DÍA ── -->
        <div style="margin-bottom:25px;">
            <div style="background:#667eea;color:white;padding:10px 20px;border-radius:8px 8px 0 0;font-weight:700;font-size:15px;">
                📅 <?= date('d/m/Y', strtotime($fecha)) ?> — <?= ucfirst(strftime('%A', strtotime($fecha))) ?>
            </div>

            <?php foreach ($usuariosDelDia as $idUsuario => $tramosUsuario): 
                $nombre = $tramosUsuario[0]['nombre'] . ' ' . $tramosUsuario[0]['apellidos'];
                $esPartida = count($tramosUsuario) > 1;
                $tramo1 = $tramosUsuario[0];
                $tramo2 = $tramosUsuario[1] ?? null;
            ?>
                <div style="border:1px solid #ddd;border-top:none;background:white;padding:15px 20px;<?= $tramo2 ? 'border-left:4px solid #3f51b5;' : '' ?>">
                    
                    <!-- Cabecera del empleado en ese día -->
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                        <div>
                            <strong style="font-size:15px;"><?= htmlspecialchars($nombre) ?></strong>
                            <?php if ($esPartida): ?>
                                <span style="margin-left:10px;background:#e8eaf6;color:#283593;padding:2px 8px;border-radius:12px;font-size:12px;font-weight:600;">🌅🌆 Jornada Partida</span>
                            <?php else: ?>
                                <span style="margin-left:10px;background:#e3f2fd;color:#1976d2;padding:2px 8px;border-radius:12px;font-size:12px;font-weight:600;">☀️ Jornada Continua</span>
                            <?php endif; ?>
                        </div>
                        <?php
                        // Total horas del día
                        $segTotal = 0;
                        foreach ($tramosUsuario as $tr) {
                            if ($tr['hora_entrada'] && $tr['hora_salida']) {
                                $s = strtotime($tr['hora_salida']) - strtotime($tr['hora_entrada']);
                                if ($tr['hora_pausa'] && $tr['hora_reanudacion']) {
                                    $s -= strtotime($tr['hora_reanudacion']) - strtotime($tr['hora_pausa']);
                                }
                                $segTotal += max(0, $s);
                            }
                        }
                        ?>
                        <?php if ($segTotal > 0): ?>
                            <span style="font-weight:700;color:#28a745;font-size:14px;">
                                ⏱ Total: <?= sprintf('%d:%02d', floor($segTotal/3600), floor(($segTotal%3600)/60)) ?>h
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- TABLA DE TRAMOS -->
                    <div class="table-container" style="margin-bottom:10px;">
                        <table class="fichajes-table" style="font-size:13px;">
                            <thead>
                                <tr>
                                    <th>Tramo</th>
                                    <th>Entrada</th>
                                    <th>Pausa</th>
                                    <th>Reanud.</th>
                                    <th>Salida</th>
                                    <th>Horas</th>
                                    <th>Tipo</th>
                                    <th>Observaciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tramosUsuario as $idx => $tr): 
                                    $horas = horasTrabajadas($tr);
                                    $estadoFila = $tr['hora_salida'] ? 'fila-completo' : 'fila-proceso';
                                    $etiquetaTramo = $esPartida
                                        ? ($idx === 0 ? '🌅 Mañana' : '🌆 Tarde')
                                        : '☀️ Normal';
                                ?>
                                    <tr id="fila-<?= $tr['id_fichaje'] ?>" class="<?= $estadoFila ?>">
                                        <td style="font-weight:600;font-size:12px;white-space:nowrap;">
                                            <?= $etiquetaTramo ?>
                                        </td>
                                        <td>
                                            <input type="time"
                                                   value="<?= $tr['hora_entrada'] ? substr($tr['hora_entrada'],0,5) : '' ?>"
                                                   class="input-time"
                                                   data-id="<?= $tr['id_fichaje'] ?>"
                                                   data-campo="hora_entrada">
                                        </td>
                                        <td>
                                            <input type="time"
                                                   value="<?= $tr['hora_pausa'] ? substr($tr['hora_pausa'],0,5) : '' ?>"
                                                   class="input-time"
                                                   data-id="<?= $tr['id_fichaje'] ?>"
                                                   data-campo="hora_pausa">
                                        </td>
                                        <td>
                                            <input type="time"
                                                   value="<?= $tr['hora_reanudacion'] ? substr($tr['hora_reanudacion'],0,5) : '' ?>"
                                                   class="input-time"
                                                   data-id="<?= $tr['id_fichaje'] ?>"
                                                   data-campo="hora_reanudacion">
                                        </td>
                                        <td>
                                            <input type="time"
                                                   value="<?= $tr['hora_salida'] ? substr($tr['hora_salida'],0,5) : '' ?>"
                                                   class="input-time"
                                                   data-id="<?= $tr['id_fichaje'] ?>"
                                                   data-campo="hora_salida">
                                        </td>
                                        <td style="text-align:center;">
                                            <span class="horas-badge">
                                                <?= $horas ? $horas.'h' : '-' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <select class="input-select" data-id="<?= $tr['id_fichaje'] ?>" data-campo="tipo" style="font-size:12px;padding:4px 6px;">
                                                <option value="normal"        <?= $tr['tipo']==='normal'        ?'selected':'' ?>>Normal</option>
                                                <option value="partida_tarde" <?= $tr['tipo']==='partida_tarde' ?'selected':'' ?>>P. Tarde</option>
                                                <option value="extra"         <?= $tr['tipo']==='extra'         ?'selected':'' ?>>Extra</option>
                                                <option value="festivo"       <?= $tr['tipo']==='festivo'       ?'selected':'' ?>>Festivo</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text"
                                                   value="<?= htmlspecialchars($tr['observaciones'] ?? '') ?>"
                                                   class="input-text"
                                                   data-id="<?= $tr['id_fichaje'] ?>"
                                                   data-campo="observaciones"
                                                   placeholder="Observaciones..."
                                                   style="font-size:12px;">
                                        </td>
                                        <td style="text-align:center;white-space:nowrap;">
                                            <button class="btn-save"   onclick="guardarFichaje(<?= $tr['id_fichaje'] ?>)" title="Guardar cambios">💾</button>
                                            <button class="btn-delete" onclick="eliminarFichaje(<?= $tr['id_fichaje'] ?>)" title="Eliminar este tramo">🗑️</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Botón para añadir segundo tramo (solo si ya hay 1 y no hay 2) -->
                    <?php if (!$esPartida): ?>
                        <button class="btn-success"
                                style="font-size:12px;padding:6px 14px;"
                                onclick="abrirModalAnadirTramo(<?= $idUsuario ?>, '<?= $fecha ?>')">
                            ➕ Añadir tramo tarde (jornada partida)
                        </button>
                    <?php endif; ?>
                </div>

            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

<?php endif; ?>

<!-- ============================================================
     MODAL: AÑADIR FICHAJE (nuevo día / nuevo tramo)
============================================================= -->
<div id="modalAnadirFichaje" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <h3 id="modalAnadirTitulo">➕ Añadir Fichaje</h3>

        <form id="formAnadirFichaje" onsubmit="return anadirFichaje(event)">
            <input type="hidden" id="modal_tipo_tramo" value="normal">

            <div class="form-group">
                <label>Empleado: *</label>
                <select id="modal_empleado" required>
                    <option value="">Seleccionar empleado</option>
                    <?php foreach ($empleados as $emp): ?>
                        <option value="<?= $emp['id_usuario'] ?>">
                            <?= htmlspecialchars($emp['nombre'].' '.$emp['apellidos']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Fecha: *</label>
                <input type="date" id="modal_fecha" value="<?= date('Y-m-d') ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Hora Entrada:</label>
                    <input type="time" id="modal_entrada">
                </div>
                <div class="form-group">
                    <label>Hora Salida:</label>
                    <input type="time" id="modal_salida">
                </div>
            </div>
            
            <!-- Pausa/reanudación solo para tramo normal -->
            <div id="modal_pausa_section">
                <div class="form-row">
                    <div class="form-group">
                        <label>Hora Pausa:</label>
                        <input type="time" id="modal_pausa">
                    </div>
                    <div class="form-group">
                        <label>Hora Reanudación:</label>
                        <input type="time" id="modal_reanudacion">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Tipo:</label>
                <select id="modal_tipo">
                    <option value="normal">Normal</option>
                    <option value="partida_tarde">Partida tarde</option>
                    <option value="extra">Extra</option>
                    <option value="festivo">Festivo</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Observaciones:</label>
                <textarea id="modal_obs" rows="2" placeholder="Detalles adicionales..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">💾 Guardar</button>
                <button type="button" class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
// ============================================================
// GUARDAR FICHAJE EXISTENTE
// ============================================================
function guardarFichaje(idFichaje) {
    const fila = document.getElementById('fila-' + idFichaje);
    const inputs = fila.querySelectorAll('[data-id="' + idFichaje + '"]');
    
    const datos = { id_fichaje: idFichaje };
    inputs.forEach(input => {
        datos[input.getAttribute('data-campo')] = input.value || null;
    });
    
    fetch('secciones/fichaje_modificar_guardar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(datos)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            fila.style.background = '#d4edda';
            setTimeout(() => { fila.style.background = ''; location.reload(); }, 800);
            alert('✅ Fichaje guardado correctamente');
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(() => alert('❌ Error de conexión'));
}

// ============================================================
// ELIMINAR FICHAJE (tramo individual)
// ============================================================
function eliminarFichaje(idFichaje) {
    if (!confirm('¿Eliminar este tramo de fichaje?')) return;
    
    fetch('secciones/fichaje_eliminar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id_fichaje: idFichaje})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('fila-' + idFichaje).remove();
            alert('✅ Tramo eliminado');
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(() => alert('❌ Error de conexión'));
}

// ============================================================
// ABRIR MODAL — Añadir fichaje nuevo (día libre)
// ============================================================
function abrirModalAnadir() {
    document.getElementById('modalAnadirTitulo').textContent = '➕ Añadir Fichaje';
    document.getElementById('modal_empleado').disabled = false;
    document.getElementById('modal_fecha').readOnly = false;
    document.getElementById('modal_tipo').value = 'normal';
    document.getElementById('modal_tipo_tramo').value = 'normal';
    document.getElementById('modal_fecha').value = '<?= date('Y-m-d') ?>';
    document.getElementById('modal_entrada').value = '';
    document.getElementById('modal_salida').value  = '';
    document.getElementById('modal_pausa').value   = '';
    document.getElementById('modal_reanudacion').value = '';
    document.getElementById('modal_obs').value = '';
    document.getElementById('modal_pausa_section').style.display = 'block';
    document.getElementById('modalAnadirFichaje').style.display = 'block';
}

// ============================================================
// ABRIR MODAL — Añadir tramo tarde (jornada partida)
// ============================================================
function abrirModalAnadirTramo(idUsuario, fecha) {
    document.getElementById('modalAnadirTitulo').textContent = '🌆 Añadir Tramo Tarde (Jornada Partida)';
    
    // Pre-seleccionar empleado y bloquear cambio
    const sel = document.getElementById('modal_empleado');
    sel.value = idUsuario;
    sel.disabled = true;

    // Pre-seleccionar fecha y bloquear
    document.getElementById('modal_fecha').value = fecha;
    document.getElementById('modal_fecha').readOnly = true;

    // Tipo fijo a partida_tarde
    document.getElementById('modal_tipo').value = 'partida_tarde';
    document.getElementById('modal_tipo_tramo').value = 'partida_tarde';

    // Sugerir horas de tarde
    document.getElementById('modal_entrada').value = '16:00';
    document.getElementById('modal_salida').value  = '19:00';
    document.getElementById('modal_pausa').value   = '';
    document.getElementById('modal_reanudacion').value = '';
    document.getElementById('modal_obs').value = '';

    // Ocultar sección pausa (tramo tarde no suele tener pausa)
    document.getElementById('modal_pausa_section').style.display = 'none';

    document.getElementById('modalAnadirFichaje').style.display = 'block';
}

// ============================================================
// CERRAR MODAL
// ============================================================
function cerrarModal() {
    const sel = document.getElementById('modal_empleado');
    sel.disabled = false;
    document.getElementById('modal_fecha').readOnly = false;
    document.getElementById('modalAnadirFichaje').style.display = 'none';
}

// ============================================================
// ENVIAR FICHAJE NUEVO
// ============================================================
function anadirFichaje(event) {
    event.preventDefault();
    
    const idUsuario = document.getElementById('modal_empleado').value;
    if (!idUsuario) { alert('Selecciona un empleado'); return false; }

    const datos = {
        id_usuario:       idUsuario,
        fecha:            document.getElementById('modal_fecha').value,
        hora_entrada:     document.getElementById('modal_entrada').value    || null,
        hora_pausa:       document.getElementById('modal_pausa').value      || null,
        hora_reanudacion: document.getElementById('modal_reanudacion').value || null,
        hora_salida:      document.getElementById('modal_salida').value     || null,
        tipo:             document.getElementById('modal_tipo').value,
        observaciones:    document.getElementById('modal_obs').value        || null
    };
    
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
    .catch(() => alert('❌ Error de conexión'));
    
    return false;
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    if (event.target === document.getElementById('modalAnadirFichaje')) cerrarModal();
}
</script>