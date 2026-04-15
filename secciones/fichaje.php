<link rel="stylesheet" href="css/fichaje.css">

<?php
// ---------------------------------------------------------------
// Detectar si hoy tiene jornada partida asignada en HORARIOS
// ---------------------------------------------------------------
$hoy = date('Y-m-d');
$idEmpresaActual = $_SESSION['empresa_activa'];

$sqlHorarioHoy = "SELECT tipo_jornada, hora_inicio, hora_fin, orden_dia
                  FROM HORARIOS
                  WHERE id_usuario = :idu AND id_empresa = :ide AND fecha = :hoy
                  ORDER BY orden_dia ASC";
$stmtH = $pdo->prepare($sqlHorarioHoy);
$stmtH->execute(['idu' => $usuario['id'], 'ide' => $idEmpresaActual, 'hoy' => $hoy]);
$horariosHoy = $stmtH->fetchAll(PDO::FETCH_ASSOC);

$tienePartidaM = false;
$tienePartidaT = false;
$hiM = $hfM = $hiT = $hfT = null;

foreach ($horariosHoy as $h) {
    if ($h['tipo_jornada'] === 'PARTIDA_M') {
        $tienePartidaM = true;
        $hiM = $h['hora_inicio'];
        $hfM = $h['hora_fin'];
    }
    if ($h['tipo_jornada'] === 'PARTIDA_T') {
        $tienePartidaT = true;
        $hiT = $h['hora_inicio'];
        $hfT = $h['hora_fin'];
    }
}
$esPartida = $tienePartidaM || $tienePartidaT;

// ---------------------------------------------------------------
// Obtener fichajes de hoy (puede haber 2 en jornada partida)
// ---------------------------------------------------------------
$sqlF = "SELECT * FROM FICHAJE WHERE id_usuario = :idu AND fecha = CURDATE() ORDER BY id_fichaje ASC";
$stmtF = $pdo->prepare($sqlF);
$stmtF->execute(['idu' => $usuario['id']]);
$fichajesHoy = $stmtF->fetchAll(PDO::FETCH_ASSOC);

$fichajeHoy   = $fichajesHoy[0] ?? null; // mañana o normal
$fichajeTarde = $fichajesHoy[1] ?? null; // tarde

// ---------------------------------------------------------------
// Lógica botones — Normal
// ---------------------------------------------------------------
$mostrarEntrada  = !$fichajeHoy || $fichajeHoy['hora_salida'] !== null;
$mostrarPausa    = $fichajeHoy && $fichajeHoy['hora_entrada'] !== null && $fichajeHoy['hora_pausa'] === null && $fichajeHoy['hora_salida'] === null;
$mostrarReanudar = $fichajeHoy && $fichajeHoy['hora_pausa'] !== null && $fichajeHoy['hora_reanudacion'] === null && $fichajeHoy['hora_salida'] === null;
$mostrarSalida   = $fichajeHoy && $fichajeHoy['hora_entrada'] !== null && $fichajeHoy['hora_salida'] === null;

// ---------------------------------------------------------------
// Lógica botones — Partida
// ---------------------------------------------------------------
$mananaCerrado   = $fichajeHoy && $fichajeHoy['hora_salida'] !== null;
$pMostrarEntM    = !$fichajeHoy;                                                                 // mostrar "Entrada Mañana"
$pMostrarSalM    = $fichajeHoy && $fichajeHoy['hora_entrada'] !== null && $fichajeHoy['hora_salida'] === null; // mostrar "Salida Mañana"
$pMostrarEntT    = $mananaCerrado && (!$fichajeTarde || $fichajeTarde['hora_salida'] !== null);  // mostrar "Entrada Tarde"
$pMostrarSalT    = $fichajeTarde && $fichajeTarde['hora_entrada'] !== null && $fichajeTarde['hora_salida'] === null; // mostrar "Salida Tarde"
$jornadaCompleta = $mananaCerrado && $fichajeTarde && $fichajeTarde['hora_salida'] !== null;

// IP
require_once __DIR__ . '/api/fichaje_ip_helper.php';
$acceso_permitido = fichaje_ip_permitida($pdo, (int)$_SESSION['empresa_activa']);
?>

<h2> Fichaje</h2>

<?php if ($esPartida): ?>
    <!-- ======================================
         JORNADA PARTIDA
    ======================================= -->
    <div style="background:#e8eaf6;border-left:4px solid #3f51b5;padding:15px;border-radius:5px;margin-bottom:15px;">
        <p><strong> Hoy tienes una <u>jornada partida</u></strong></p>
        <?php if ($tienePartidaM): ?>
            <p> Primer Tramo: <strong><?= substr($hiM,0,5) ?> – <?= substr($hfM,0,5) ?></strong></p>
        <?php endif; ?>
        <?php if ($tienePartidaT): ?>
            <p> Segundo Tramo: <strong><?= substr($hiT,0,5) ?> – <?= substr($hfT,0,5) ?></strong></p>
        <?php endif; ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;">
        <div style="background:#f0f4ff;padding:15px;border-radius:5px;">
            <p><strong> Primer tramo</strong></p>
            <?php if ($fichajeHoy): ?>
                <p> Entrada: <strong><?= substr($fichajeHoy['hora_entrada'],0,5) ?? '-' ?></strong></p>
                <p> Salida: <strong><?= $fichajeHoy['hora_salida'] ? substr($fichajeHoy['hora_salida'],0,5) : '—' ?></strong></p>
            <?php else: ?>
                <p style="color:#888;">Sin fichar</p>
            <?php endif; ?>
        </div>
        <div style="background:#fce4ec;padding:15px;border-radius:5px;">
            <p><strong> Segundo Tramo</strong></p>
            <?php if ($fichajeTarde): ?>
                <p> Entrada: <strong><?= substr($fichajeTarde['hora_entrada'],0,5) ?? '-' ?></strong></p>
                <p> Salida: <strong><?= $fichajeTarde['hora_salida'] ? substr($fichajeTarde['hora_salida'],0,5) : '—' ?></strong></p>
            <?php else: ?>
                <p style="color:#888;">Sin fichar</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="fichaje-panel">
        <?php if ($acceso_permitido): ?>
            <form action="secciones/fichaje_accion.php" method="POST">
                <?php if ($jornadaCompleta): ?>
                    <p style="color:#28a745;font-weight:bold;padding:10px;">✅ Jornada partida completada</p>
                <?php else: ?>
                    <?php if ($pMostrarEntM): ?>
                        <button type="submit" name="accion" value="entrada" class="btn-fichaje btn-entrada">🟢 Entrada Mañana</button>
                    <?php endif; ?>
                    <?php if ($pMostrarSalM): ?>
                        <button type="submit" name="accion" value="salida" class="btn-fichaje btn-salida">🔴 Salida Mañana</button>
                    <?php endif; ?>
                    <?php if ($pMostrarEntT): ?>
                        <button type="submit" name="accion" value="entrada_tarde" class="btn-fichaje btn-reanudar" style="background:#3f51b5;">🟣 Entrada Tarde</button>
                    <?php endif; ?>
                    <?php if ($pMostrarSalT): ?>
                        <button type="submit" name="accion" value="salida_tarde" class="btn-fichaje btn-salida" style="background:#880e4f;">🟤 Salida Tarde</button>
                    <?php endif; ?>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <div style="background:#fff3f3;color:#d32f2f;padding:20px;border-radius:8px;border:1px solid #ffcdd2;text-align:center;">
                <strong>🚫 Acceso Restringido</strong>
                <p style="margin:10px 0 0;">Solo puedes fichar desde la red del despacho.</p>
                <small>Tu IP: <?= $ip_usuario ?></small>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- ======================================
         JORNADA NORMAL (código original)
    ======================================= -->
    <?php if($fichajeHoy): ?>
        <div style="background:#cacacaa8;padding:15px;border-radius:5px;margin-bottom:20px;">
            <p><strong>Estado del fichaje de hoy:</strong></p>
            <p> Entrada: <strong><?= $fichajeHoy['hora_entrada'] ?? '-' ?></strong></p>
            <p> Pausa: <strong><?= $fichajeHoy['hora_pausa'] ?? '-' ?></strong></p>
            <p> Reanudación: <strong><?= $fichajeHoy['hora_reanudacion'] ?? '-' ?></strong></p>
            <p> Salida: <strong><?= $fichajeHoy['hora_salida'] ?? '-' ?></strong></p>
        </div>
    <?php else: ?>
        <div style="background:#fff3cd;padding:15px;border-radius:5px;margin-bottom:20px;">
            <p> No has fichado hoy todavía.</p>
        </div>
    <?php endif; ?>

    <div class="fichaje-panel">
        <?php if ($acceso_permitido): ?>
            <form action="secciones/fichaje_accion.php" method="POST">
                <?php if($mostrarEntrada): ?>
                    <button type="submit" name="accion" value="entrada" class="btn-fichaje btn-entrada">🟢 Fichar Entrada</button>
                <?php endif; ?>
                <?php if($mostrarPausa): ?>
                    <button type="submit" name="accion" value="pausa" class="btn-fichaje btn-pausa">⏸️ Iniciar Pausa</button>
                <?php endif; ?>
                <?php if($mostrarReanudar): ?>
                    <button type="submit" name="accion" value="reanudar" class="btn-fichaje btn-reanudar">▶️ Reanudar Trabajo</button>
                <?php endif; ?>
                <?php if($mostrarSalida): ?>
                    <button type="submit" name="accion" value="salida" class="btn-fichaje btn-salida">🔴 Fichar Salida</button>
                    <?php if($fichajeHoy['hora_pausa'] !== null && $fichajeHoy['hora_reanudacion'] === null): ?>
                        <p style="color:#ff9800;margin-top:10px;">⚠️ Estás en pausa...</p>
                    <?php endif; ?>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <div style="background:#fff3f3;color:#d32f2f;padding:20px;border-radius:8px;border:1px solid #ffcdd2;text-align:center;">
                <strong> Acceso Restringido</strong>
                <p style="margin:10px 0 0;">Solo puedes fichar desde la red del despacho.</p>
                <small>Tu IP: <?= $ip_usuario ?></small>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<hr style="margin:30px 0;">
<h3> Últimos fichajes</h3>
<?php
$sqlH = "SELECT * FROM FICHAJE WHERE id_usuario = :idu ORDER BY fecha DESC, id_fichaje DESC LIMIT 10";
$stmtHL = $pdo->prepare($sqlH);
$stmtHL->execute(['idu' => $usuario['id']]);
$historial = $stmtHL->fetchAll(PDO::FETCH_ASSOC);
if(count($historial) > 0):
?>
<div class="table-container">
    <table class="fichajes-table">
        <thead>
            <tr>
                <th>Fecha</th><th>Entrada</th><th>Pausa</th><th>Reanudación</th><th>Salida</th><th>Tramo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($historial as $f): 
                $tipo_f = $f['tipo'] ?? 'normal';
                $claseF = $f['hora_salida'] ? 'fila-completo' : 'fila-proceso';
                $tramoLabel = match($tipo_f) {
                    'partida_tarde'  => ' Tarde',
                    default          => ' Completa'
                };
            ?>
            <tr class="<?= $claseF ?>">
                <td><?= date('d/m/Y', strtotime($f['fecha'])) ?></td>
                <td><?= $f['hora_entrada'] ? substr($f['hora_entrada'],0,5) : '-' ?></td>
                <td><?= $f['hora_pausa'] ? substr($f['hora_pausa'],0,5) : '-' ?></td>
                <td><?= $f['hora_reanudacion'] ? substr($f['hora_reanudacion'],0,5) : '-' ?></td>
                <td><?= $f['hora_salida'] ? substr($f['hora_salida'],0,5) : '-' ?></td>
                <td><?= $tramoLabel ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <p>No hay fichajes registrados.</p>
<?php endif; ?>