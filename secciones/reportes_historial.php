<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

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

<style>
    :root {
        --primary-color: #4f46e5;
        --danger-color: #ef4444;
        --text-main: #111827;
        --text-muted: #6b7280;
        --radius: 12px;
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }

    .historial-wrapper { font-family: 'Inter', sans-serif; padding: 20px; }
    
    .selector-card {
        background: white; padding: 20px; border-radius: var(--radius);
        border: 1px solid #e5e7eb; margin-bottom: 30px; display: flex; align-items: center; gap: 15px;
    }

    .selector-card select {
        padding: 10px; border-radius: 8px; border: 1px solid #d1d5db; min-width: 280px;
    }

    .report-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;
    }

    /* CARD ESTILIZADA */
    .report-card {
        background: white; border-radius: var(--radius); border: 1px solid #e5e7eb;
        position: relative; overflow: hidden; transition: all 0.3s ease; display: flex; flex-direction: column;
    }

    .report-card:hover {
        transform: translateY(-5px); box-shadow: var(--shadow-md); border-color: var(--primary-color);
    }

    /* BOTÓN ELIMINAR (Invisible por defecto) */
    .btn-delete-float {
        position: absolute; top: 12px; right: 12px;
        width: 32px; height: 32px; border-radius: 50%;
        background: white; color: var(--danger-color);
        border: 1px solid #fee2e2; display: flex; align-items: center; justify-content: center;
        cursor: pointer; opacity: 0; transition: all 0.2s ease; z-index: 10;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .report-card:hover .btn-delete-float {
        opacity: 1;
    }

    .btn-delete-float:hover {
        background: var(--danger-color); color: white; transform: scale(1.1);
    }

    .card-body { padding: 24px; }
    
    .report-icon {
        width: 44px; height: 44px; background: #f5f3ff; color: var(--primary-color);
        border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;
    }

    .report-date { font-size: 18px; font-weight: 700; margin-bottom: 5px; }
    .report-meta { font-size: 13px; color: var(--text-muted); line-height: 1.6; }

    .card-footer {
        background: #f9fafb; padding: 15px 24px; border-top: 1px solid #e5e7eb;
        display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
    }

    .btn-pro {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        padding: 8px; border-radius: 8px; font-size: 13px; font-weight: 600;
        text-decoration: none; border: 1px solid #d1d5db; transition: 0.2s;
    }

    .btn-view { background: white; color: #374151; }
    .btn-download { background: var(--primary-color); color: white; border: none; }
    
    /* Animación de borrado */
    .fade-out { opacity: 0; transform: scale(0.9); transition: all 0.4s ease; }

    .empty-state {
        text-align: center; padding: 60px; background: #f9fafb; border: 2px dashed #d1d5db; border-radius: var(--radius);
    }
</style>

<div class="historial-wrapper">
    <div class="section-header">
        <h2>📂 Historial de Documentos</h2>
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
                        <a href="secciones/reporte_descarga.php?id=<?= $rep['id_reporte'] ?>&preview=1" target="_blank" class="btn-pro btn-view"> Ver</a>
                        <a href="secciones/reporte_descarga.php?id=<?= $rep['id_reporte'] ?>" class="btn-pro btn-download"> Bajar</a>
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
    
    fetch('secciones/reporte_eliminar.php', {
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