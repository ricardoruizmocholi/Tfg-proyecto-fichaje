<link rel="stylesheet" href="css/estilos_fichaje.css">

<div class="fichaje-wrapper">
</div>

<?php
ini_set('default_charset', 'UTF-8');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
$pdo->exec("SET NAMES utf8mb4");

// Filtros
$modo              = $_GET['modo']         ?? 'dia';
$empleado_filtro   = $_GET['empleado']     ?? '';
$orden             = $_GET['orden']        ?? 'DESC';
$fecha_filtro      = $_GET['fecha']        ?? date('Y-m-d');
$mes_filtro        = $_GET['mes']          ?? date('Y-m');
$fecha_inicio_param = $_GET['fecha_inicio'] ?? '';
$fecha_fin_param    = $_GET['fecha_fin']   ?? '';

// Rango real
if ($modo === 'rango' && !empty($fecha_inicio_param)) {
    $fecha_inicio = $fecha_inicio_param;
    $fecha_fin    = $fecha_fin_param ?: date('Y-m-d');
} elseif ($modo === 'mes') {
    $fecha_inicio = date('Y-m-01', strtotime($mes_filtro . '-01'));
    $fecha_fin    = date('Y-m-t',  strtotime($mes_filtro . '-01'));
} else {
    $fecha_inicio = $fecha_filtro;
    $fecha_fin    = $fecha_filtro;
}

// ================================================================
// CONSULTA PRINCIPAL
// Trae TODOS los fichajes del rango, incluyendo los 2 de jornada
// partida para el mismo empleado/día.
// Ordenamos por usuario → fecha → id_fichaje para poder agrupar.
// ================================================================
$sql = "
    SELECT 
        U.id_usuario, U.nombre, U.apellidos,
        F.id_fichaje, F.fecha, F.hora_entrada, F.hora_pausa,
        F.hora_reanudacion, F.hora_salida, F.tipo AS tipo_fichaje,
        F.observaciones,
        H.hora_inicio AS horario_inicio, H.hora_fin AS horario_fin, H.tipo_jornada
    FROM USUARIO U
    JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
    LEFT JOIN FICHAJE F
        ON U.id_usuario = F.id_usuario
        AND F.fecha BETWEEN :inicio AND :fin
    LEFT JOIN HORARIOS H
        ON U.id_usuario = H.id_usuario
        AND H.id_empresa = EU.id_empresa
        AND H.fecha = F.fecha
    WHERE EU.id_empresa = :empresa
      AND EU.activo = 1
";

if ($empleado_filtro) {
    $sql .= " AND U.id_usuario = :id_empleado";
}

$sql .= " ORDER BY F.fecha $orden, U.nombre ASC, F.id_fichaje ASC";

$stmt = $pdo->prepare($sql);
$params = ['inicio' => $fecha_inicio, 'fin' => $fecha_fin, 'empresa' => $empresa];
if ($empleado_filtro) $params['id_empleado'] = $empleado_filtro;
$stmt->execute($params);
$rawRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ================================================================
// AGRUPAR: $registrosPorFecha[fecha][id_usuario] = array de filas
// Así podemos mostrar ambos tramos juntos cuando es jornada partida
// ================================================================
$registrosPorFecha = [];
foreach ($rawRows as $r) {
    $f   = $r['fecha'] ?: $fecha_inicio;
    $uid = $r['id_usuario'];
    if (!isset($registrosPorFecha[$f][$uid])) {
        $registrosPorFecha[$f][$uid] = [];
    }
    if ($r['id_fichaje']) { // puede venir null si no fichó
        $registrosPorFecha[$f][$uid][] = $r;
    }
}

// También necesitamos la lista de usuarios que no ficharon (LEFT JOIN devuelve fila con id_fichaje null)
$usuariosPorFecha = [];
foreach ($rawRows as $r) {
    $f   = $r['fecha'] ?: $fecha_inicio;
    $uid = $r['id_usuario'];
    if (!isset($usuariosPorFecha[$f][$uid])) {
        $usuariosPorFecha[$f][$uid] = $r; // guardamos datos del usuario aunque no tenga fichaje
    }
}

// Empleados para el select
$stmtEmp = $pdo->prepare("
    SELECT U.id_usuario, U.nombre, U.apellidos 
    FROM USUARIO U 
    JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario 
    WHERE EU.id_empresa = ? 
    ORDER BY U.nombre ASC
");
$stmtEmp->execute([$empresa]);
$empleados = $stmtEmp->fetchAll(PDO::FETCH_ASSOC);

// Helper: calcular segundos trabajados en un fichaje
function calcularSegundos($f) {
    // 1. Evitamos el warning comprobando si el array o las claves están vacías
    if (empty($f) || empty($f['hora_entrada']) || empty($f['hora_salida'])) return 0;
    
    $entrada = strtotime($f['hora_entrada']);
    $salida  = strtotime($f['hora_salida']);
    $pausa   = 0;
    
    // 2. Protegemos también las pausas por si vienen nulas
    if (!empty($f['hora_pausa']) && !empty($f['hora_reanudacion'])) {
        $pausa = strtotime($f['hora_reanudacion']) - strtotime($f['hora_pausa']);
    }
    
    return max(0, $salida - $entrada - $pausa);
}

// Helper: formatear segundos → "Xh Ym"
function formatearDuracion($seg) {
    if ($seg <= 0) return '-';
    $h = floor($seg / 3600);
    $m = floor(($seg % 3600) / 60);
    return $h > 0 ? "{$h}h {$m}m" : "{$m}m";
}
?>

<div class="fichaje-wrapper">
    <div class="section-header">
        <h2>📋 Registros de Fichaje</h2>
        <p>Filtrado inteligente por empleado y periodos</p>
    </div>

    <!-- FILTROS -->
    <div class="filter-card">
        <form method="GET" action="panel.php" class="new-filter-grid">
            <input type="hidden" name="seccion" value="fichaje">
            <input type="hidden" name="vista"   value="ver">
            
            <div class="f-group">
                <label>Ver por:</label>
                <select name="modo" id="modoFiltro" onchange="cambiarModo()" class="f-control">
                    <option value="dia"   <?= $modo=='dia'   ?'selected':'' ?>>Día específico</option>
                    <option value="mes"   <?= $modo=='mes'   ?'selected':'' ?>>Mes completo</option>
                    <option value="rango" <?= $modo=='rango' ?'selected':'' ?>>Rango personalizado</option>
                </select>
            </div>

            <div id="box-dia" class="f-group mode-box" style="<?= $modo!='dia'?'display:none':'' ?>">
                <label>Fecha</label>
                <input type="date" name="fecha" value="<?= $fecha_filtro ?>" class="f-control">
            </div>

            <div id="box-mes" class="f-group mode-box" style="<?= $modo!='mes'?'display:none':'' ?>">
                <label>Mes</label>
                <input type="month" name="mes" value="<?= $mes_filtro ?>" class="f-control">
            </div>

            <div id="box-rango" class="f-group mode-box" style="<?= $modo!='rango'?'display:none':'' ?>">
                <label>Periodo</label>
                <div class="range-inputs">
                    <input type="date" name="fecha_inicio" value="<?= $fecha_inicio_param ?>" class="f-control" placeholder="Desde">
                    <input type="date" name="fecha_fin"    value="<?= $fecha_fin_param ?>"    class="f-control" placeholder="Hasta">
                </div>
            </div>

            <div class="f-group">
                <label>Empleado</label>
                <select name="empleado" class="f-control">
                    <option value="">Todos</option>
                    <?php foreach ($empleados as $emp): ?>
                        <option value="<?= $emp['id_usuario'] ?>" <?= $empleado_filtro==$emp['id_usuario']?'selected':'' ?>>
                            <?= htmlspecialchars($emp['nombre'].' '.$emp['apellidos']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="f-group">
                <label>Orden</label>
                <select name="orden" class="f-control">
                    <option value="DESC" <?= $orden=='DESC'?'selected':'' ?>>Nuevos ↑</option>
                    <option value="ASC"  <?= $orden=='ASC' ?'selected':'' ?>>Antiguos ↓</option>
                </select>
            </div>

            <div class="f-actions">
                <button type="submit" class="btn-search">🔎 Filtrar</button>
                <a href="panel.php?seccion=fichaje&vista=ver" class="btn-reset">Limpiar</a>
            </div>
        </form>
    </div>

    <?php
    // Construir lista de fechas únicas en el orden correcto
    $fechasUnicas = array_keys($usuariosPorFecha);
    if ($orden === 'DESC') rsort($fechasUnicas); else sort($fechasUnicas);

    if (empty($fechasUnicas) || (count($fechasUnicas) === 1 && empty($registrosPorFecha[$fechasUnicas[0]]))):
    ?>
        <div class="no-data-card">
            <i class="fas fa-search"></i>
            <p>No hay registros para este criterio</p>
        </div>
    <?php else: ?>

        <?php foreach ($fechasUnicas as $fecha): 
            $usuariosEnFecha = $usuariosPorFecha[$fecha] ?? [];
            if (empty($usuariosEnFecha)) continue;
        ?>
            <div class="day-container">
                <div class="day-header">
                    📅 <?= date('d/m/Y', strtotime($fecha)) ?> — <?= ucfirst(strftime('%A', strtotime($fecha))) ?>
                </div>
                <div class="table-responsive">
                    <table class="f-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Jornada</th>
                                <th>1ª Entrada</th>
                                <th>1ª Salida</th>
                                <th>2ª Entrada</th>
                                <th>2ª Salida</th>
                                <th>Total horas</th>
                                <th>Estado</th>
                                <th>Obs.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuariosEnFecha as $uid => $datosUsuario):
                                $fichajesUsuario = $registrosPorFecha[$fecha][$uid] ?? [];

                                // ---- Datos del tramo 1 (mañana / normal) ----
                                $f1 = $fichajesUsuario[0] ?? null;
                                // ---- Datos del tramo 2 (tarde / partida) ----
                                $f2 = count($fichajesUsuario) > 1 ? $fichajesUsuario[1] : null;

                                $esPartida = $f2 !== null;

                                // Calcular segundos totales del día
                                $segTotales = calcularSegundos($f1 ?: []) + calcularSegundos($f2 ?: []);

                                // Estado global del día
                                if (!$f1) {
                                    $estadoLabel = '<span class="st-badge ausente">Sin fichar</span>';
                                } elseif ($esPartida) {
                                    $todosCerrados = $f1['hora_salida'] && $f2['hora_salida'];
                                    $estadoLabel = $todosCerrados
                                        ? '<span class="st-badge ok">Completada</span>'
                                        : '<span class="st-badge proceso">En curso</span>';
                                } elseif ($f1['hora_entrada'] && !$f1['hora_salida']) {
                                    $estadoLabel = '<span class="st-badge proceso">En curso</span>';
                                } else {
                                    $estadoLabel = '<span class="st-badge ok">Finalizado</span>';
                                }

                                // Etiqueta tipo jornada
                                $tipoJornada = $esPartida ? '🌅🌆 Partida' : '☀️ Continua';
                            ?>
                                <tr>
                                    <td class="name-td">
                                        <strong><?= htmlspecialchars($datosUsuario['nombre'].' '.$datosUsuario['apellidos']) ?></strong>
                                    </td>
                                    <td style="font-size:12px;color:#555;"><?= $tipoJornada ?></td>

                                    <!-- Tramo 1 -->
                                    <td><?= $f1 && $f1['hora_entrada'] ? substr($f1['hora_entrada'],0,5) : '--:--' ?></td>
                                    <td><?= $f1 && $f1['hora_salida']  ? substr($f1['hora_salida'],0,5)  : '--:--' ?></td>

                                    <!-- Tramo 2 (vacío si no hay partida) -->
                                    <td style="<?= !$esPartida ? 'color:#ccc;' : '' ?>">
                                        <?= $f2 ? substr($f2['hora_entrada'],0,5) : ($esPartida ? '--:--' : '—') ?>
                                    </td>
                                    <td style="<?= !$esPartida ? 'color:#ccc;' : '' ?>">
                                        <?= $f2 && $f2['hora_salida'] ? substr($f2['hora_salida'],0,5) : ($esPartida ? '--:--' : '—') ?>
                                    </td>

                                    <!-- Total horas del día -->
                                    <td>
                                        <?php if ($segTotales > 0): ?>
                                            <strong><?= formatearDuracion($segTotales) ?></strong>
                                        <?php else: ?>
                                            <span style="color:#ccc;">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <td><?= $estadoLabel ?></td>

                                    <td class="obs-td" style="font-size:12px;">
                                        <?php
                                        $obs = [];
                                        if ($f1 && $f1['observaciones']) $obs[] = $f1['observaciones'];
                                        if ($f2 && $f2['observaciones']) $obs[] = $f2['observaciones'];
                                        echo htmlspecialchars(implode(' / ', $obs));
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

<script>
function cambiarModo() {
    const modo = document.getElementById('modoFiltro').value;
    document.querySelectorAll('.mode-box').forEach(el => el.style.display = 'none');
    if(modo === 'dia')   document.getElementById('box-dia').style.display   = 'block';
    if(modo === 'mes')   document.getElementById('box-mes').style.display   = 'block';
    if(modo === 'rango') document.getElementById('box-rango').style.display = 'block';
}
</script>