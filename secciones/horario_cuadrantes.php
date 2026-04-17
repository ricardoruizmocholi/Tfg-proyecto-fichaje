<link rel="stylesheet" href="css/horario_cuadrante.css">

<?php
$empleado_filtro     = $_GET['empleado']      ?? '';
$vista_periodo       = $_GET['periodo']       ?? 'semana';
$fecha_inicio_filtro = $_GET['fecha_inicio']  ?? date('Y-m-d');

$fecha_inicio = new DateTime($fecha_inicio_filtro);
$fecha_fin    = clone $fecha_inicio;

switch ($vista_periodo) {
    case 'semana':   $fecha_inicio->modify('monday this week'); $fecha_fin = clone $fecha_inicio; $fecha_fin->modify('+6 days');  break;
    case '2semanas': $fecha_inicio->modify('monday this week'); $fecha_fin = clone $fecha_inicio; $fecha_fin->modify('+13 days'); break;
    case '3semanas': $fecha_inicio->modify('monday this week'); $fecha_fin = clone $fecha_inicio; $fecha_fin->modify('+20 days'); break;
    case 'mes':      $fecha_inicio = new DateTime($fecha_inicio->format('Y-m-01')); $fecha_fin = new DateTime($fecha_inicio->format('Y-m-t')); break;
}

$fechas = [];
$fc = clone $fecha_inicio;
while ($fc <= $fecha_fin) { $fechas[] = $fc->format('Y-m-d'); $fc->modify('+1 day'); }

// Empleados
$sqlEmp = "SELECT U.id_usuario, U.nombre, U.apellidos
           FROM USUARIO U JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
           WHERE EU.id_empresa = ? AND EU.activo = 1";
$paramsEmp = [$empresa];
if ($empleado_filtro) { $sqlEmp .= " AND U.id_usuario = ?"; $paramsEmp[] = $empleado_filtro; }
$sqlEmp .= " ORDER BY U.nombre, U.apellidos";
$stmtEmp = $pdo->prepare($sqlEmp); $stmtEmp->execute($paramsEmp);
$empleados = $stmtEmp->fetchAll(PDO::FETCH_ASSOC);

// ── CONSULTA CON LEFT JOIN a tipos_jornada_custom ─────────────
$sqlH = "
    SELECT
        h.*,
        tjc.nombre_display  AS custom_nombre,
        tjc.color_hex       AS custom_color,
        tjc.es_productivo   AS custom_productivo
    FROM HORARIOS h
    LEFT JOIN tipos_jornada_custom tjc ON h.id_tipo_custom = tjc.id_tipo_custom
    WHERE h.id_empresa = :ide
      AND h.fecha BETWEEN :ini AND :fin
";
$paramsH = ['ide' => $empresa, 'ini' => $fecha_inicio->format('Y-m-d'), 'fin' => $fecha_fin->format('Y-m-d')];
if ($empleado_filtro) { $sqlH .= " AND h.id_usuario = :idu"; $paramsH['idu'] = $empleado_filtro; }
$sqlH .= " ORDER BY h.id_usuario, h.fecha, h.orden_dia ASC";
$stmtH = $pdo->prepare($sqlH); $stmtH->execute($paramsH);

$horarios = [];
while ($h = $stmtH->fetch(PDO::FETCH_ASSOC)) {
    $horarios[$h['id_usuario']][$h['fecha']][] = $h;
}

// Todos los empleados para el selector
$stmtTodos = $pdo->prepare("SELECT U.id_usuario, U.nombre, U.apellidos FROM USUARIO U JOIN EMPRESA_USUARIO EU ON U.id_usuario=EU.id_usuario WHERE EU.id_empresa=? AND EU.activo=1 ORDER BY U.nombre, U.apellidos");
$stmtTodos->execute([$empresa]);
$todosEmpleados = $stmtTodos->fetchAll(PDO::FETCH_ASSOC);

setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');

// ── Helpers híbridos ──────────────────────────────────────────
/**
 * Devuelve las clases CSS o el style inline para un evento.
 * Si tiene custom → usa color_hex inline. Si no → clase CSS genérica.
 */
function renderizarItemCuadrante(array $ev): string {
    $tieneCustom = !empty($ev['id_tipo_custom']) && !empty($ev['custom_nombre']);

    if ($tieneCustom) {
        $color = htmlspecialchars($ev['custom_color'] ?? '#667eea');
        $label = htmlspecialchars($ev['custom_nombre']);
        $textColor = colorTextoContraste($ev['custom_color'] ?? '#667eea');

        $style  = "background:{$color};color:{$textColor};border-left:3px solid {$color};";
        $titulo = $label;
    } else {
        $clase = match(strtoupper($ev['tipo_jornada'])) {
            'VACACIONES' => 'horario-vacaciones',
            'MEDICO'     => 'horario-medico',
            'LIBRE'      => 'horario-libre',
            'FESTIVO'    => 'horario-festivo',
            'PARTIDA_M'  => 'horario-partida-m',
            'PARTIDA_T'  => 'horario-partida-t',
            default      => 'horario-trabajo',
        };
        $style  = '';
        $titulo = etiquetaTipoEnum($ev['tipo_jornada']);
    }

    $horasHtml = '';
    if ($ev['hora_inicio'] && $ev['hora_fin']) {
        $horasHtml = '<div class="horario-horas">' . substr($ev['hora_inicio'],0,5) . ' - ' . substr($ev['hora_fin'],0,5) . '</div>';
    }

    $idH   = (int)$ev['id_horario'];
    $idU   = (int)$ev['id_usuario'];
    $fecha = htmlspecialchars($ev['fecha']);
    $idCus = (int)($ev['id_tipo_custom'] ?? 0);
    $clsBase = $tieneCustom ? 'horario-item horario-custom' : "horario-item {$clase}";

    return "<div class=\"{$clsBase}\"" .
           ($style ? " style=\"{$style}\"" : '') .
           " onclick=\"editarCelda(this)\"" .
           " data-id-horario=\"{$idH}\"" .
           " data-id-usuario=\"{$idU}\"" .
           " data-fecha=\"{$fecha}\"" .
           " data-id-custom=\"{$idCus}\"" .
           " title=\"{$titulo}\">" .
           "<span class=\"tipo-label\">{$titulo}</span>" .
           $horasHtml .
           "<button class=\"btn-eliminar\" onclick=\"eliminarHorario(event,{$idH})\">×</button>" .
           "</div>";
}

/** Calcula si el texto debe ser blanco o negro según el color de fondo */
function colorTextoContraste(string $hex): string {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex,0,2));
    $g = hexdec(substr($hex,2,2));
    $b = hexdec(substr($hex,4,2));
    $luminancia = (0.299*$r + 0.587*$g + 0.114*$b) / 255;
    return $luminancia > 0.5 ? '#333333' : '#ffffff';
}

function etiquetaTipoEnum(string $tipo): string {
    return match(strtoupper($tipo)) {
        'VACACIONES' => ' VACAC.',
        'MEDICO'     => ' MÉDICO',
        'LIBRE'      => ' LIBRE',
        'FESTIVO'    => ' FESTIVO',
        'PARTIDA_M'  => ' MAÑANA',
        'PARTIDA_T'  => ' TARDE',
        default      => ' TRABAJO',
    };
}
?>

<div class="section-header">
    <h2> Cuadrante de Horarios</h2>
    <p>Haz clic en + para añadir. Haz clic en un bloque para editar.</p>
</div>

<!-- Filtros -->
<div class="filter-container">
    <form method="GET" action="panel.php" class="filter-form">
        <input type="hidden" name="seccion" value="horario">
        <input type="hidden" name="vista"   value="cuadrantes">
        <div class="filter-group">
            <label>Empleado:</label>
            <select name="empleado" onchange="this.form.submit()">
                <option value="">Todos</option>
                <?php foreach ($todosEmpleados as $emp): ?>
                    <option value="<?= $emp['id_usuario'] ?>" <?= $empleado_filtro==$emp['id_usuario']?'selected':'' ?>>
                        <?= htmlspecialchars($emp['nombre'].' '.$emp['apellidos']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Período:</label>
            <select name="periodo" onchange="this.form.submit()">
                <option value="semana"   <?= $vista_periodo==='semana'   ?'selected':'' ?>>1 Semana</option>
                <option value="2semanas" <?= $vista_periodo==='2semanas' ?'selected':'' ?>>2 Semanas</option>
                <option value="3semanas" <?= $vista_periodo==='3semanas' ?'selected':'' ?>>3 Semanas</option>
                <option value="mes"      <?= $vista_periodo==='mes'      ?'selected':'' ?>>Mes completo</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Fecha inicio:</label>
            <input type="date" name="fecha_inicio" value="<?= $fecha_inicio_filtro ?>" onchange="this.form.submit()">
        </div>
        <button type="button" class="btn-success" onclick="abrirModalMasivo()"> Añadir en bloque</button>
    </form>
</div>

<div class="periodo-info">
    <strong> Período:</strong> <?= $fecha_inicio->format('d/m/Y') ?> - <?= $fecha_fin->format('d/m/Y') ?>
    (<?= count($fechas) ?> días)
</div>

<?php if (!$empleados): ?>
    <div class="no-results"><p>No hay empleados</p></div>
<?php else: ?>
<div class="cuadrante-container">
    <table class="cuadrante-table">
        <thead>
            <tr>
                <th class="columna-empleado">Empleado</th>
                <?php foreach ($fechas as $fecha):
                    $dia    = strftime('%a', strtotime($fecha));
                    $num    = date('d', strtotime($fecha));
                    $mes    = strftime('%b', strtotime($fecha));
                    $esFin  = date('N', strtotime($fecha)) >= 6;
                ?>
                    <th class="columna-dia <?= $esFin?'fin-semana':'' ?>">
                        <div><?= ucfirst($dia) ?></div>
                        <div style="font-size:11px;opacity:.9;"><?= $num ?> <?= $mes ?></div>
                    </th>
                <?php endforeach; ?>
                <th class="columna-total">Total</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($empleados as $emp):
            $totalH = 0;
        ?>
            <tr>
                <td class="celda-empleado">
                    <strong><?= htmlspecialchars($emp['nombre'].' '.$emp['apellidos']) ?></strong>
                </td>
                <?php foreach ($fechas as $fecha):
                    $evs    = $horarios[$emp['id_usuario']][$fecha] ?? [];
                    $esFin  = date('N', strtotime($fecha)) >= 6;
                    foreach ($evs as $ev) { $totalH += (float)($ev['horas_totales'] ?? 0); }
                ?>
                    <td class="td-horario <?= $esFin?'finde':'' ?>">
                        <div class="celda-contenido">
                            <?php foreach ($evs as $ev): ?>
                                <?= renderizarItemCuadrante($ev) ?>
                            <?php endforeach; ?>
                            <div class="btn-add-siempre"
                                 onclick="editarCelda(this)"
                                 data-id-usuario="<?= $emp['id_usuario'] ?>"
                                 data-fecha="<?= $fecha ?>"
                                 title="Añadir">+</div>
                        </div>
                    </td>
                <?php endforeach; ?>
                <td class="celda-total"><strong><?= number_format($totalH,2) ?>h</strong></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Modal editar individual -->
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
                <select id="edit_tipo">
                    <!-- rellenado dinámicamente por JS -->
                </select>
            </div>
            <div id="horasDiv">
                <div class="form-row">
                    <div class="form-group"><label>Inicio:</label><input type="time" id="edit_inicio"></div>
                    <div class="form-group"><label>Fin:</label><input type="time" id="edit_fin"></div>
                </div>
            </div>
            <div class="form-group">
                <label>Observaciones:</label>
                <textarea id="edit_obs" rows="2"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary"> Guardar</button>
                <button type="button" class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal masivo -->
<div id="modalMasivo" class="modal-horario">
    <div class="modal-content-horario">
        <div class="modal-header-horario">
            <span>Añadir Horarios en bloque</span>
            <span style="cursor:pointer" onclick="cerrarModalMasivo()">&times;</span>
        </div>
        
        <form id="formMasivo" onsubmit="return guardarMasivos(event)" class="modal-body-horario">
            <div class="form-group-horario">
                <label>Empleados:*</label>
                <select id="masivo_emps" multiple size="5" style="height:120px;" class="form-control-horario">
                    <option value="todos">TODOS</option>
                    <?php foreach ($todosEmpleados as $emp): ?>
                        <option value="<?= $emp['id_usuario'] ?>"><?= htmlspecialchars($emp['nombre'].' '.$emp['apellidos']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small>Ctrl/Cmd para seleccionar varios</small>
            </div>
            
            <div class="form-row">
                <div class="form-group-horario">
                    <label>Desde:*</label>
                    <input type="date" id="masivo_inicio" value="<?= date('Y-m-d') ?>" class="form-control-horario">
                </div>
                <div class="form-group-horario">
                    <label>Hasta:*</label>
                    <input type="date" id="masivo_fin" value="<?= date('Y-m-d', strtotime('+6 days')) ?>" class="form-control-horario">
                </div>
            </div>
            
            <div class="form-group-horario">
                <label>Días:</label>
                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 5px;">
                    <label style="font-weight:normal;"><input type="checkbox" value="1" checked class="dia-cb"> Lun</label>
                    <label style="font-weight:normal;"><input type="checkbox" value="2" checked class="dia-cb"> Mar</label>
                    <label style="font-weight:normal;"><input type="checkbox" value="3" checked class="dia-cb"> Mié</label>
                    <label style="font-weight:normal;"><input type="checkbox" value="4" checked class="dia-cb"> Jue</label>
                    <label style="font-weight:normal;"><input type="checkbox" value="5" checked class="dia-cb"> Vie</label>
                    <label style="font-weight:normal;"><input type="checkbox" value="6" class="dia-cb"> Sáb</label>
                    <label style="font-weight:normal;"><input type="checkbox" value="7" class="dia-cb"> Dom</label>
                </div>
            </div>
            
            <div class="form-group-horario">
                <label>Tipo:*</label>
                <select id="masivo_tipo" class="form-control-horario"><!-- JS -->
                                   </select>
            </div>
            
            <div id="horasMasivoDiv">
                <div class="form-row">
                    <div class="form-group-horario">
                        <label>Inicio:</label>
                        <input type="time" id="masivo_inicio_hora" value="09:00" class="form-control-horario">
                    </div>
                    <div class="form-group-horario">
                        <label>Fin:</label>
                        <input type="time" id="masivo_fin_hora" value="17:00" class="form-control-horario">
                    </div>
                </div>
            </div>
            
            <div class="form-group-horario">
                <label>Observaciones:</label>
                <textarea id="masivo_obs" rows="2" class="form-control-horario"></textarea>
            </div>
            
            <div class="modal-footer-horario" style="padding: 15px 0 0 0; background: transparent; border: none;">
                <button type="button" class="btn-cancel-horario" onclick="cerrarModalMasivo()">Cancelar</button>
                <button type="submit" class="btn-success-horario">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<?php include 'horario_cuadrantes_scripts.php'; ?>