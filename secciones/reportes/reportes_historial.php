<?php
/*
 * reportes_historial.php — Vista del historial de reportes (admin)
 * Incluida por panel.php (seccion=reportes&vista=historial). Solo admin.
 * Lista todos los reportes generados de la empresa con fecha, empleado y tipo.
 * Permite ver/descargar el PDF (reporte_descarga.php) y eliminar reportes (reporte_eliminar.php).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';

// Verificación de seguridad
if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    die('<div class="alert-error">Acceso denegado.</div>');
}

$empresa = $_SESSION['empresa_activa'];
$idEmpleado = $_GET['id_usuario'] ?? null;

// Obtener empleados
$stmtEmpleados = $pdo->prepare("
    SELECT U.id_usuario, U.nombre, U.apellidos, U.foto_perfil
    FROM USUARIO U
    JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
    WHERE EU.id_empresa = ? AND EU.activo = 1
    ORDER BY U.nombre, U.apellidos
");
$stmtEmpleados->execute([$empresa]);
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

$reportes = [];
if ($idEmpleado) {
    $stmtReportes = $pdo->prepare("
        SELECT R.*, UG.nombre as gen_nombre, UG.apellidos as gen_apellidos
        FROM REPORTES R
        LEFT JOIN USUARIO UG ON R.generado_por = UG.id_usuario
        WHERE R.id_empresa = ? AND R.id_usuario = ?
        ORDER BY R.anio DESC, R.mes DESC, R.fecha_generacion DESC
    ");
    $stmtReportes->execute([$empresa, $idEmpleado]);
    $reportes = $stmtReportes->fetchAll(PDO::FETCH_ASSOC);
}

$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
?>
<link rel="stylesheet" href="css/reportes.css">
<style>
    
</style>

<div class="historial-wrapper">
    <div class="section-header">
        <h2><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em" style="vertical-align:-0.125em"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776"/></svg> Historial de Documentos</h2>
        <p>Gestiona y supervisa los reportes oficiales del personal.</p>
    </div>

    <div class="selector-card">
        <label style="font-weight: 600;">Empleado:</label>
        <form method="GET">
            <input type="hidden" name="seccion" value="reportes">
            <input type="hidden" name="vista" value="historial">
            <select name="id_usuario" onchange="this.form.submit()">
                <option value="">Seleccione un trabajador...</option>
                <?php foreach ($empleados as $emp): ?>
                    <option value="<?= $emp['id_usuario'] ?>" <?= ($idEmpleado == $emp['id_usuario']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($idEmpleado): ?>
        <div class="report-grid">
            <?php foreach ($reportes as $rep): ?>
                <div class="report-card" id="reporte-<?= $rep['id_reporte'] ?>">
                    <button class="btn-delete-float" 
                            onclick="eliminarReporte(<?= $rep['id_reporte'] ?>)" 
                            title="Eliminar este reporte permanentemente">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    <div class="card-body">
                        <div class="report-icon">
                            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="report-date"><?= $meses[$rep['mes']] ?> <?= $rep['anio'] ?></div>
                        <div class="report-meta">
                            <strong>Tipo:</strong> <?= ucfirst(str_replace('_', ' ', $rep['tipo_reporte'])) ?><br>
                            <strong>Generado:</strong> <?= date('d/m/Y', strtotime($rep['fecha_generacion'])) ?><br>
                            <strong>Autor:</strong> <?= htmlspecialchars($rep['gen_nombre']) ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="secciones/reportes/reporte_descarga.php?id=<?= $rep['id_reporte'] ?>&preview=1" target="_blank" class="btn-pro btn-view"> Ver</a>
                        <a href="secciones/reportes/reporte_descarga.php?id=<?= $rep['id_reporte'] ?>" class="btn-pro btn-download"> Bajar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if(empty($reportes)): ?>
            <div class="empty-state"><h3>No hay reportes para este usuario</h3></div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function eliminarReporte(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar este reporte? Esta acción borrará el archivo físico y no se puede deshacer.')) {
        return;
    }

    const card = document.getElementById('reporte-' + id);
    
    fetch('secciones/reportes/reporte_eliminar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_reporte: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Animación profesional de salida
            card.classList.add('fade-out');
            setTimeout(() => card.remove(), 400);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al intentar eliminar el reporte.');
    });
}
</script>