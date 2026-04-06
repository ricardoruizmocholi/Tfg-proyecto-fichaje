<link rel="stylesheet" href="css/horario_cuadrante.css">

<?php
$empleado_filtro    = $_GET['empleado']     ?? '';
$vista_periodo      = $_GET['periodo']      ?? 'semana';
$fecha_inicio_filtro = $_GET['fecha_inicio'] ?? date('Y-m-d');

$fecha_inicio = new DateTime($fecha_inicio_filtro);
$fecha_fin    = clone $fecha_inicio;

switch ($vista_periodo) {
    case 'semana':
        $fecha_inicio->modify('monday this week');
        $fecha_fin = clone $fecha_inicio;
        $fecha_fin->modify('+6 days');
        break;
    case '2semanas':
        $fecha_inicio->modify('monday this week');
        $fecha_fin = clone $fecha_inicio;
        $fecha_fin->modify('+13 days');
        break;
    case '3semanas':
        $fecha_inicio->modify('monday this week');
        $fecha_fin = clone $fecha_inicio;
        $fecha_fin->modify('+20 days');
        break;
    case 'mes':
        $fecha_inicio = new DateTime($fecha_inicio->format('Y-m-01'));
        $fecha_fin    = new DateTime($fecha_inicio->format('Y-m-t'));
        break;
}

$fechas = [];
$fc = clone $fecha_inicio;
while ($fc <= $fecha_fin) {
    $fechas[] = $fc->format('Y-m-d');
    $fc->modify('+1 day');
}

// Empleados
$sqlEmpleados = "SELECT U.id_usuario, U.nombre, U.apellidos 
                 FROM USUARIO U JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
                 WHERE EU.id_empresa = ? AND EU.activo = 1";
$paramsEmp = [$empresa];
if ($empleado_filtro) { $sqlEmpleados .= " AND U.id_usuario = ?"; $paramsEmp[] = $empleado_filtro; }
$sqlEmpleados .= " ORDER BY U.nombre, U.apellidos";
$stmtEmp = $pdo->prepare($sqlEmpleados);
$stmtEmp->execute($paramsEmp);
$empleados = $stmtEmp->fetchAll(PDO::FETCH_ASSOC);

// Horarios del período — TODOS los registros (incluye múltiples por día para jornada partida)
$sqlH = "SELECT * FROM HORARIOS WHERE id_empresa = :ide AND fecha BETWEEN :ini AND :fin";
$paramsH = ['ide' => $empresa, 'ini' => $fecha_inicio->format('Y-m-d'), 'fin' => $fecha_fin->format('Y-m-d')];
if ($empleado_filtro) { $sqlH .= " AND id_usuario = :idu"; $paramsH['idu'] = $empleado_filtro; }
$sqlH .= " ORDER BY id_usuario, fecha, orden_dia ASC";
$stmtH = $pdo->prepare($sqlH);
$stmtH->execute($paramsH);

// Organizar: $horarios[id_usuario][fecha] = array de eventos
$horarios = [];
while ($h = $stmtH->fetch(PDO::FETCH_ASSOC)) {
    $horarios[$h['id_usuario']][$h['fecha']][] = $h;
}

// Todos los empleados para el selector
$stmtTodos = $pdo->prepare("SELECT U.id_usuario, U.nombre, U.apellidos FROM USUARIO U JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario WHERE EU.id_empresa = ? AND EU.activo = 1 ORDER BY U.nombre, U.apellidos");
$stmtTodos->execute([$empresa]);
$todosEmpleados = $stmtTodos->fetchAll(PDO::FETCH_ASSOC);

setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');

// Helper: clase CSS según tipo
function claseTipoCuadrante($tipo) {
    return match(strtoupper($tipo)) {
        'VACACIONES' => 'horario-vacaciones',
        'MEDICO'     => 'horario-medico',
        'LIBRE'      => 'horario-libre',
        'FESTIVO'    => 'horario-festivo',
        'PARTIDA_M'  => 'horario-partida-m',
        'PARTIDA_T'  => 'horario-partida-t',
        default      => 'horario-trabajo',
    };
}

// Helper: etiqueta corta
function etiquetaTipo($tipo) {
    return match(strtoupper($tipo)) {
        'VACACIONES' => 'VACAC.',
        'MEDICO'     => 'MÉDICO',
        'LIBRE'      => 'LIBRE',
        'FESTIVO'    => 'FESTIVO',
        'PARTIDA_M'  => '🌅 MAÑANA',
        'PARTIDA_T'  => '🌆 TARDE',
        default      => 'TRABAJO',
    };
}

// Helper: mostrar horas solo en tipos que las tienen
function mostrarHoras($tipo) {
    return in_array(strtoupper($tipo), ['TRABAJO','MEDICO','PARTIDA_M','PARTIDA_T']);
}
?>

<div class="section-header">
    <h2>📅 Cuadrante de Horarios</h2>
    <p>Doble clic en cualquier celda para editar</p>
</div>

<div class="filter-container">
    <form method="GET" action="panel.php" class="filter-form">
        <input type="hidden" name="seccion" value="horario">
        <input type="hidden" name="vista"   value="cuadrantes">
        
        <div class="filter-group">
            <label>Empleado:</label>
            <select name="empleado" onchange="this.form.submit()">
                <option value="">Todos</option>
                <?php foreach ($todosEmpleados as $emp): ?>
                    <option value="<?= $emp['id_usuario'] ?>" <?= $empleado_filtro == $emp['id_usuario'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['nombre'].' '.$emp['apellidos']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Período:</label>
            <select name="periodo" onchange="this.form.submit()">
                <option value="semana"   <?= $vista_periodo==='semana'   ? 'selected':'' ?>>1 Semana</option>
                <option value="2semanas" <?= $vista_periodo==='2semanas' ? 'selected':'' ?>>2 Semanas</option>
                <option value="3semanas" <?= $vista_periodo==='3semanas' ? 'selected':'' ?>>3 Semanas</option>
                <option value="mes"      <?= $vista_periodo==='mes'      ? 'selected':'' ?>>Mes completo</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Fecha inicio:</label>
            <input type="date" name="fecha_inicio" value="<?= $fecha_inicio_filtro ?>" onchange="this.form.submit()">
        </div>
        
        <button type="button" class="btn-success" onclick="abrirModalMasivo()">➕ Añadir Horarios</button>
    </form>
</div>

<div class="periodo-info">
    <strong>📆 Período:</strong> <?= $fecha_inicio->format('d/m/Y') ?> - <?= $fecha_fin->format('d/m/Y') ?> (<?= count($fechas) ?> días)
</div>

<?php if (count($empleados) === 0): ?>
    <div class="no-results"><p>No hay empleados</p></div>
<?php else: ?>
    <div class="cuadrante-container">
        <table class="cuadrante-table">
            <thead>
                <tr>
                    <th class="columna-empleado">Empleado</th>
                    <?php foreach ($fechas as $fecha):
                        $diaSemana = strftime('%a', strtotime($fecha));
                        $diaNum    = date('d', strtotime($fecha));
                        $mes       = strftime('%b', strtotime($fecha));
                        $esFinde   = date('N', strtotime($fecha)) >= 6;
                    ?>
                        <th class="columna-dia <?= $esFinde ? 'fin-semana' : '' ?>">
                            <div><?= ucfirst($diaSemana) ?></div>
                            <div style="font-size:11px;opacity:0.9;"><?= $diaNum ?> <?= $mes ?></div>
                        </th>
                    <?php endforeach; ?>
                    <th class="columna-total">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empleados as $emp):
                    $totalHoras = 0;
                ?>
                    <tr>
                        <td class="celda-empleado">
                            <strong><?= htmlspecialchars($emp['nombre'].' '.$emp['apellidos']) ?></strong>
                        </td>
                        
                        <?php foreach ($fechas as $fecha):
                            $eventosDia = $horarios[$emp['id_usuario']][$fecha] ?? [];
                            $esFinde    = date('N', strtotime($fecha)) >= 6;

                            // Sumar horas del día (todos los tramos)
                            $horasDia = 0;
                            foreach ($eventosDia as $ev) {
                                if (!empty($ev['horas_totales'])) {
                                    $horasDia += floatval($ev['horas_totales']);
                                }
                            }
                            $totalHoras += $horasDia;
                        ?>
                            <td class="td-horario <?= $esFinde ? 'finde' : '' ?>">
                                <div class="celda-contenido">
                                    <?php foreach ($eventosDia as $ev):
                                        $tipo   = strtoupper($ev['tipo_jornada']);
                                        $clase  = claseTipoCuadrante($tipo);
                                        $label  = etiquetaTipo($tipo);
                                        $tieneH = mostrarHoras($tipo);
                                    ?>
                                        <div class="horario-item <?= $clase ?>"
                                             onclick="editarCelda(this)"
                                             data-id-horario="<?= $ev['id_horario'] ?>"
                                             data-id-usuario="<?= $emp['id_usuario'] ?>"
                                             data-fecha="<?= $fecha ?>">
                                            <span class="tipo-label"><?= $label ?></span>
                                            <?php if ($tieneH && $ev['hora_inicio'] && $ev['hora_fin']): ?>
                                                <div class="horario-horas">
                                                    <?= substr($ev['hora_inicio'],0,5) ?> - <?= substr($ev['hora_fin'],0,5) ?>
                                                </div>
                                            <?php endif; ?>
                                            <button class="btn-eliminar" onclick="eliminarHorario(event, <?= $ev['id_horario'] ?>)">×</button>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="btn-add-siempre"
                                         onclick="editarCelda(this)"
                                         data-id-usuario="<?= $emp['id_usuario'] ?>"
                                         data-fecha="<?= $fecha ?>"
                                         title="Añadir registro">+</div>
                                </div>
                            </td>
                        <?php endforeach; ?>
                        
                        <td class="celda-total"><strong><?= number_format($totalHoras, 2) ?>h</strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- ============================================================
     MODAL EDITAR (celda individual)
============================================================= -->
<div id="modalEditar" class="modal">
    <div class="modal-content modal-small">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <h3 id="tituloModal">Editar Horario</h3>
        <form id="formEditar" onsubmit="return guardarHorario(event)">
            <input type="hidden" id="edit_id_horario">
            <input type="hidden" id="edit_id_usuario">
            <input type="hidden" id="edit_fecha">
            
            <div class="form-group">
                <label>Tipo:</label>
                <select id="edit_tipo" onchange="toggleHoras()">
                    <option value="TRABAJO">Trabajo (jornada continua)</option>
                    <option value="PARTIDA_M">🌅 Jornada partida — Tramo mañana</option>
                    <option value="PARTIDA_T">🌆 Jornada partida — Tramo tarde</option>
                    <option value="VACACIONES">Vacaciones</option>
                    <option value="MEDICO">Médico</option>
                    <option value="LIBRE">Libre</option>
                    <option value="FESTIVO">Festivo</option>
                </select>
            </div>
            
            <div id="horasDiv">
                <div class="form-row">
                    <div class="form-group">
                        <label>Inicio:</label>
                        <input type="time" id="edit_inicio">
                    </div>
                    <div class="form-group">
                        <label>Fin:</label>
                        <input type="time" id="edit_fin">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Observaciones:</label>
                <textarea id="edit_obs" rows="2"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">💾 Guardar</button>
                <button type="button" class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================
     MODAL MASIVO
============================================================= -->
<div id="modalMasivo" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModalMasivo()">&times;</span>
        <h3>➕ Añadir Horarios en bloque</h3>
        <form id="formMasivo" onsubmit="return guardarMasivos(event)">
            <div class="form-group">
                <label>Empleados:*</label>
                <select id="masivo_emps" multiple size="5" style="height:120px;">
                    <option value="todos">TODOS</option>
                    <?php foreach ($todosEmpleados as $emp): ?>
                        <option value="<?= $emp['id_usuario'] ?>"><?= htmlspecialchars($emp['nombre'].' '.$emp['apellidos']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small>Ctrl/Cmd para seleccionar varios</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Desde:*</label>
                    <input type="date" id="masivo_inicio" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Hasta:*</label>
                    <input type="date" id="masivo_fin" value="<?= date('Y-m-d', strtotime('+6 days')) ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Días:</label>
                <div class="dias-checkbox">
                    <label><input type="checkbox" value="1" checked class="dia-cb"> Lun</label>
                    <label><input type="checkbox" value="2" checked class="dia-cb"> Mar</label>
                    <label><input type="checkbox" value="3" checked class="dia-cb"> Mié</label>
                    <label><input type="checkbox" value="4" checked class="dia-cb"> Jue</label>
                    <label><input type="checkbox" value="5" checked class="dia-cb"> Vie</label>
                    <label><input type="checkbox" value="6" class="dia-cb"> Sáb</label>
                    <label><input type="checkbox" value="7" class="dia-cb"> Dom</label>
                </div>
            </div>
            
            <div class="form-group">
                <label>Tipo:*</label>
                <select id="masivo_tipo" onchange="toggleHorasMasivo()">
                    <option value="TRABAJO">Trabajo (jornada continua)</option>
                    <option value="PARTIDA_M">🌅 Jornada partida — Tramo mañana</option>
                    <option value="PARTIDA_T">🌆 Jornada partida — Tramo tarde</option>
                    <option value="VACACIONES">Vacaciones</option>
                    <option value="MEDICO">Médico</option>
                    <option value="LIBRE">Libre</option>
                    <option value="FESTIVO">Festivo</option>
                </select>
            </div>
            
            <div id="horasMasivoDiv">
                <div class="form-row">
                    <div class="form-group">
                        <label>Inicio:</label>
                        <input type="time" id="masivo_inicio_hora" value="09:00">
                    </div>
                    <div class="form-group">
                        <label>Fin:</label>
                        <input type="time" id="masivo_fin_hora" value="17:00">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Observaciones:</label>
                <textarea id="masivo_obs" rows="2"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">💾 Guardar</button>
                <button type="button" class="btn-secondary" onclick="cerrarModalMasivo()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<?php include 'horario_cuadrantes_scripts.php'; ?>