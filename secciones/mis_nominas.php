<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['usuario'])) die("Acceso denegado");

$idUsuario = $_SESSION['usuario']['id'];
$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

// Obtener documentos del usuario
$stmt = $pdo->prepare("SELECT * FROM documentos WHERE id_usuario = ? ORDER BY anio DESC, mes DESC, fecha_subida DESC");
$stmt->execute([$idUsuario]);
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2> Mis Nóminas y Documentos</h2>
    <p class="text-muted">Aquí puedes consultar y descargar los documentos de la empresa.</p>

    <?php if (empty($documentos)): ?>
        <div class="alert alert-info">Aún no tienes documentos disponibles.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($documentos as $doc): ?>
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm h-100 border-left-primary">
                        <div class="card-body">
                            <h5 class="card-title text-primary">
                                 <?= htmlspecialchars($doc['titulo']) ?>
                            </h5>
                            <p class="card-text mb-1">
                                <strong>Periodo:</strong> <?= $meses[$doc['mes']] ?> <?= $doc['anio'] ?>
                            </p>
                            <p class="card-text text-muted small">
                                Subido el: <?= date('d/m/Y', strtotime($doc['fecha_subida'])) ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0 text-end">
                            <a href="<?= htmlspecialchars($doc['ruta_archivo']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                 Ver PDF
                            </a>
                            <a href="<?= htmlspecialchars($doc['ruta_archivo']) ?>" download class="btn btn-primary btn-sm">
                                 Descargar
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.border-left-primary { border-left: 4px solid #4e73df !important; }
</style>