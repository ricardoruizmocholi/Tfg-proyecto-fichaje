<?php
// secciones/vacaciones_cuadrante.php
if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) die('No autorizado');

$anio   = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
$mesVer = isset($_GET['mes'])  ? (int)$_GET['mes']  : (int)date('n');
// Navegar mes anterior / siguiente
if ($mesVer < 1)  { $mesVer = 12; $anio--; }
if ($mesVer > 12) { $mesVer = 1;  $anio++; }

$mesesNombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                 'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

// Datos del mes
$primerDia  = new DateTime("{$anio}-{$mesVer}-01");
$diasDelMes = (int)$primerDia->format('t');

// Empleados activos con su contrato
$stmtEmps = $pdo->prepare("
    SELECT u.id_usuario, u.nombre, u.apellidos, u.foto_perfil,
           u.tipo_contrato, u.horas_contrato_mes,
           u.vacaciones_totales_anuales,
           COALESCE(va.dias_restantes, u.vacaciones_totales_anuales) AS dias_restantes,
           COALESCE(va.dias_utilizados, 0) AS dias_usados
    FROM usuario u
    JOIN empresa_usuario eu ON u.id_usuario = eu.id_usuario
    LEFT JOIN vacaciones_acumulado va ON va.id_usuario = u.id_usuario AND va.año = ?
    WHERE eu.id_empresa = ? AND eu.activo = 1 AND eu.admin = 0
    ORDER BY u.nombre, u.apellidos
");
$stmtEmps->execute([$anio, $empresa]);
$empleados = $stmtEmps->fetchAll(PDO::FETCH_ASSOC);
$totalEmpleados = count($empleados);

// Vacaciones aprobadas de este mes (de horarios)
$stmtVac = $pdo->prepare("
    SELECT h.id_usuario, h.fecha
    FROM horarios h
    JOIN empresa_usuario eu ON h.id_usuario = eu.id_usuario
    WHERE h.id_empresa = ? AND h.tipo_jornada = 'VACACIONES'
      AND MONTH(h.fecha) = ? AND YEAR(h.fecha) = ?
      AND eu.activo = 1
");
$stmtVac->execute([$empresa, $mesVer, $anio]);
$vacRows = $stmtVac->fetchAll(PDO::FETCH_ASSOC);

// Indexar: $vacaciones[id_usuario][fecha] = true
$vacaciones = [];
foreach ($vacRows as $v) {
    $vacaciones[$v['id_usuario']][$v['fecha']] = true;
}

// Solicitudes PENDIENTES de este mes
$stmtPend = $pdo->prepare("
    SELECT sh.id_usuario, dsh.fecha
    FROM detalle_solicitud_horario dsh
    JOIN solicitudes_horario sh ON dsh.id_solicitud = sh.id_solicitud
    WHERE sh.id_empresa = ? AND sh.estado = 'PENDIENTE'
      AND dsh.tipo_jornada = 'VACACIONES'
      AND MONTH(dsh.fecha) = ? AND YEAR(dsh.fecha) = ?
");
$stmtPend->execute([$empresa, $mesVer, $anio]);
$pendRows = $stmtPend->fetchAll(PDO::FETCH_ASSOC);

$pendientes = [];
foreach ($pendRows as $p) {
    $pendientes[$p['id_usuario']][$p['fecha']] = true;
}
// Contar cuántos empleados están de vacaciones cada día
$contadorDia = []; // fecha => count
for ($d = 1; $d <= $diasDelMes; $d++) {
    $fecha = sprintf('%04d-%02d-%02d', $anio, $mesVer, $d);
    $contadorDia[$fecha] = 0;
    foreach ($empleados as $emp) {
        if (!empty($vacaciones[$emp['id_usuario']][$fecha])) {
            $contadorDia[$fecha]++;
        }
    }
}

// Umbral de alerta: >33% de la plantilla de vacaciones el mismo día
$umbralAlerta = max(1, ceil($totalEmpleados * 0.33));
?>

<link rel="stylesheet" href="css/vacaciones.css">

<div class="vac-wrapper">

    <!-- ── Cabecera ─────────────────────────────────────── -->
    <div class="vac-header">
        <div>
            <h2> Cuadrante de Vacaciones</h2>
            <p>Planifica las vacaciones del equipo y asegura la cobertura diaria.</p>
        </div>
        <div class="vac-nav">
            <a href="panel.php?seccion=horario&vista=vacaciones&mes=<?= $mesVer-1 ?>&anio=<?= $mesVer==1?$anio-1:$anio ?>"
               class="vac-nav-btn">◀</a>
            <span class="vac-nav-label"><?= $mesesNombres[$mesVer] ?> <?= $anio ?></span>
            <a href="panel.php?seccion=horario&vista=vacaciones&mes=<?= $mesVer+1 ?>&anio=<?= $mesVer==12?$anio+1:$anio ?>"
               class="vac-nav-btn">▶</a>
        </div>
    </div>

    <!-- ── Leyenda ──────────────────────────────────────── -->
    <div class="vac-leyenda">
        <span><span class="vac-chip aprobada"></span> Aprobada</span>
        <span><span class="vac-chip pendiente"></span> Pendiente aprobación</span>
        <span><span class="vac-chip alerta"></span> ⚠️ Más del 33% del equipo</span>
        <span><span class="vac-chip finde"></span> Fin de semana</span>
        <span style="color:#6c757d;font-size:12px;">Umbral de alerta: <?= $umbralAlerta ?> empleados/día</span>
    </div>

    <!-- ── Stats rápidas ────────────────────────────────── -->
    <div class="vac-stats">
        <?php
        $sinDias    = count(array_filter($empleados, fn($e) => $e['dias_restantes'] <= 0));
        $enVacHoy   = $contadorDia[date('Y-m-d')] ?? 0;
        $diasAlerta = count(array_filter($contadorDia, fn($c) => $c >= $umbralAlerta));
        ?>
        <div class="vac-stat-item">
            <div class="vac-stat-num"><?= $totalEmpleados ?></div>
            <div class="vac-stat-lbl">Empleados</div>
        </div>
        <div class="vac-stat-item">
            <div class="vac-stat-num"><?= $enVacHoy ?></div>
            <div class="vac-stat-lbl">De vacaciones hoy</div>
        </div>
        <div class="vac-stat-item <?= $sinDias > 0 ? 'alerta' : '' ?>">
            <div class="vac-stat-num"><?= $sinDias ?></div>
            <div class="vac-stat-lbl">Sin días disponibles</div>
        </div>
        <div class="vac-stat-item <?= $diasAlerta > 0 ? 'alerta' : '' ?>">
            <div class="vac-stat-num"><?= $diasAlerta ?></div>
            <div class="vac-stat-lbl">Días con riesgo de cobertura</div>
        </div>
    </div>

    <!-- ── Cuadrante ────────────────────────────────────── -->
    <div class="vac-scroll">
        <table class="vac-table">
            <thead>
                <tr>
                    <th class="vac-th-empleado">Empleado</th>
                    <th class="vac-th-saldo">Días</th>
                    <?php for ($d = 1; $d <= $diasDelMes; $d++):
                        $fecha   = sprintf('%04d-%02d-%02d', $anio, $mesVer, $d);
                        $dow     = (int)(new DateTime($fecha))->format('N'); // 1=Lun 7=Dom
                        $esFinde = $dow >= 6;
                        $esHoy   = $fecha === date('Y-m-d');
                        $clsHead = $esFinde ? 'finde' : ($esHoy ? 'hoy' : '');
                        $numEnVac= $contadorDia[$fecha];
                        $esAlerta= $numEnVac >= $umbralAlerta;
                    ?>
                        <th class="vac-th-dia <?= $clsHead ?> <?= $esAlerta ? 'dia-alerta' : '' ?>">
                            <div><?= ['','L','M','X','J','V','S','D'][$dow] ?></div>
                            <div class="vac-th-num"><?= $d ?></div>
                            <?php if ($esAlerta): ?><div class="vac-alerta-ico">⚠️</div><?php endif; ?>
                        </th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empleados as $emp):
                    $idU = $emp['id_usuario'];
                    $pct = $emp['vacaciones_totales_anuales'] > 0
                        ? min(100, round(($emp['dias_usados'] / $emp['vacaciones_totales_anuales']) * 100))
                        : 0;
                    $clsRest = $emp['dias_restantes'] <= 0 ? 'cero'
                             : ($emp['dias_restantes'] <= 3 ? 'poco' : '');
                ?>
                    <tr>
                        <td class="vac-td-empleado">
                            <img src="<?= htmlspecialchars($emp['foto_perfil'] ?? 'secciones/uploads/perfil_default.jpg') ?>"
                                 onerror="this.src='secciones/uploads/perfil_default.jpg'"
                                 class="vac-avatar">
                            <div>
                                <div class="vac-nombre"><?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']) ?></div>
                                <div class="vac-contrato"><?= $emp['tipo_contrato'] === 'parcial' ? '½ jornada' : 'Completa' ?></div>
                            </div>
                        </td>

                        <td class="vac-td-saldo <?= $clsRest ?>">
                            <div class="vac-saldo-num"><?= number_format($emp['dias_restantes'], 1) ?></div>
                            <div class="vac-saldo-bar">
                                <div class="vac-saldo-fill <?= $clsRest ?>" style="width:<?= $pct ?>%"></div>
                            </div>
                            <div class="vac-saldo-sub">/<?= $emp['vacaciones_totales_anuales'] ?></div>
                        </td>

                        <?php for ($d = 1; $d <= $diasDelMes; $d++):
                            $fecha   = sprintf('%04d-%02d-%02d', $anio, $mesVer, $d);
                            $dow     = (int)(new DateTime($fecha))->format('N');
                            $esFinde = $dow >= 6;
                            $esVac   = !empty($vacaciones[$idU][$fecha]);
                            $esPend  = !empty($pendientes[$idU][$fecha]);
                            $esAlerta= $contadorDia[$fecha] >= $umbralAlerta;
                            $esHoy   = $fecha === date('Y-m-d');

                            $cls = 'vac-td-dia';
                            if ($esFinde)  $cls .= ' finde';
                            if ($esHoy)    $cls .= ' hoy';
                            if ($esAlerta && ($esVac || $esPend)) $cls .= ' dia-alerta';
                        ?>
                            <td class="<?= $cls ?>">
                                <?php if ($esVac): ?>
                                    <div class="vac-celda aprobada" title="Vacaciones aprobadas">✓</div>
                                <?php elseif ($esPend): ?>
                                    <div class="vac-celda pendiente" title="Pendiente de aprobación">?</div>
                                <?php elseif ($esFinde): ?>
                                    <div class="vac-celda finde"></div>
                                <?php else: ?>
                                    <div class="vac-celda vacia"></div>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ── Aviso cobertura ──────────────────────────────── -->
    <?php if ($diasAlerta > 0): ?>
    <div style="margin-top:16px;background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:14px 18px;font-size:13px;color:#856404;">
        ⚠️ <strong>Atención:</strong> Hay <?= $diasAlerta ?> día<?= $diasAlerta > 1 ? 's' : '' ?>
        en <?= $mesesNombres[$mesVer] ?> en los que más del 33% de la plantilla
        (≥ <?= $umbralAlerta ?> personas) coincide de vacaciones.
        Revisa las celdas marcadas antes de aprobar nuevas solicitudes.
    </div>
    <?php endif; ?>

</div>