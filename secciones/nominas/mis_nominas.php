<?php
/*
 * mis_nominas.php — Vista de nóminas del empleado
 * Incluida por panel.php (seccion=documentos). Admin y empleado.
 * Muestra la lista de documentos/nóminas subidos para el usuario en sesión.
 * Permite descargar cada PDF directamente.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['usuario'])) die("Acceso denegado");

$idUsuario = $_SESSION['usuario']['id'];

$meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
          'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

// Obtener documentos del usuario agrupados por año
$stmt = $pdo->prepare("
    SELECT * FROM documentos
    WHERE id_usuario = ?
    ORDER BY anio DESC, mes DESC, fecha_subida DESC
");
$stmt->execute([$idUsuario]);
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por año para los separadores
$porAnio = [];
foreach ($documentos as $doc) {
    $porAnio[$doc['anio']][] = $doc;
}
?>

<link rel="stylesheet" href="css/nominas.css">

<div class="nominas-wrapper">

    <!-- CABECERA -->
    <div class="nominas-header-banner">
        <div>
            <h2> Mis Nóminas y Documentos</h2>
            <p>Consulta y descarga los documentos que la empresa ha compartido contigo</p>
        </div>
        <div style="font-size:40px;opacity:0.3;"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg></div>
    </div>

    <?php if (empty($documentos)): ?>
        <!-- ESTADO VACÍO -->
        <div class="nominas-empty">
            <div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776"/></svg></div>
            <h3>No tienes documentos disponibles</h3>
            <p>Cuando tu empresa suba una nómina o documento, aparecerá aquí.</p>
        </div>

    <?php else: ?>

        <?php foreach ($porAnio as $anio => $docsAnio): ?>

            <!-- Separador de año -->
            <div style="display:flex;align-items:center;gap:12px;margin:0 0 18px 0;">
                <span style="font-size:18px;font-weight:700;color:var(--text-main);"> <?= $anio ?></span>
                <span style="flex:1;height:1px;background:var(--border);"></span>
                <span style="font-size:13px;color:var(--text-muted);font-weight:600;">
                    <?= count($docsAnio) ?> documento<?= count($docsAnio) > 1 ? 's' : '' ?>
                </span>
            </div>

            <!-- GRID DE TARJETAS (mismo diseño que historial de reportes) -->
            <div class="mis-nominas-grid" style="margin-bottom:32px;">
                <?php foreach ($docsAnio as $doc): ?>

                    <div class="nomina-card">

                        <!-- Cabecera con degradado -->
                        <div class="nomina-card-header">
                            <div>
                                <div class="nomina-tipo"> Documento</div>
                                <div class="nomina-titulo"><?= htmlspecialchars($doc['titulo']) ?></div>
                            </div>
                            <div class="nomina-periodo-badge">
                                <?= $meses[$doc['mes']] ?> <?= $doc['anio'] ?>
                            </div>
                        </div>

                        <!-- Cuerpo con metadatos -->
                        <div class="nomina-card-body">
                            <div class="nomina-meta-row">
                                <span class="meta-label"> Período</span>
                                <span class="meta-value"><?= $meses[$doc['mes']] ?> <?= $doc['anio'] ?></span>
                            </div>
                            <div class="nomina-meta-row">
                                <span class="meta-label"> Disponible desde</span>
                                <span class="meta-value"><?= date('d/m/Y', strtotime($doc['fecha_subida'])) ?></span>
                            </div>
                            <div class="nomina-meta-row">
                                <span class="meta-label"> Tipo</span>
                                <span class="meta-value">PDF</span>
                            </div>
                        </div>

                        <!-- Footer con botones de acción -->
                        <div class="nomina-card-footer">
                            <a href="<?= htmlspecialchars($doc['ruta_archivo']) ?>"
                               target="_blank"
                               class="btn-nomina btn-nomina-ver">
                                 Ver
                            </a>
                            <a href="<?= htmlspecialchars($doc['ruta_archivo']) ?>"
                               download
                               class="btn-nomina btn-nomina-descargar">
                                 Descargar
                            </a>
                        </div>

                    </div>

                <?php endforeach; ?>
            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>