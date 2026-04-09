<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    die("Acceso denegado");
}

$empresa = $_SESSION['empresa_activa'];
$mensaje = '';

// Procesar la subida del documento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_nomina'])) {
    $idEmpleado = $_POST['id_usuario'];
    $titulo = trim($_POST['titulo']);
    $mes = $_POST['mes'];
    $anio = $_POST['anio'];
    $archivo = $_FILES['documento'];

    // Validar que sea PDF
    $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        $mensaje = "<div class='alert alert-danger'>Solo se permiten archivos PDF.</div>";
    } else {
        // Crear carpeta si no existe
        $directorio = __DIR__ . '/../uploads/nominas/';
        if (!is_dir($directorio)) mkdir($directorio, 0777, true);

        // Generar nombre único para no sobrescribir
        $nombreFinal = 'nomina_' . $idEmpleado . '_' . $mes . '_' . $anio . '_' . time() . '.pdf';
        $rutaDestino = $directorio . $nombreFinal;
        $rutaBD = 'uploads/nominas/' . $nombreFinal;

        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            $stmt = $pdo->prepare("INSERT INTO documentos (id_usuario, id_empresa, titulo, mes, anio, ruta_archivo) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$idEmpleado, $empresa, $titulo, $mes, $anio, $rutaBD]);
            
            // Opcional: Avisar al empleado con el sistema de notificaciones que ya tienes
            $msgNotif = "Tienes una nueva nómina/documento disponible: $titulo";
            $pdo->prepare("INSERT INTO NOTIFICACIONES (id_usuario, mensaje, leida) VALUES (?, ?, 0)")->execute([$idEmpleado, $msgNotif]);

            $mensaje = "<div class='alert alert-success'>Documento subido y empleado notificado.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al guardar el archivo en el servidor.</div>";
        }
    }
}

// Procesar el borrado
if (isset($_GET['borrar'])) {
    $idDoc = $_GET['borrar'];
    $stmt = $pdo->prepare("SELECT ruta_archivo FROM documentos WHERE id_documento = ? AND id_empresa = ?");
    $stmt->execute([$idDoc, $empresa]);
    $doc = $stmt->fetch();
    
    if ($doc) {
        $rutaFisica = __DIR__ . '/../' . $doc['ruta_archivo'];
        if (file_exists($rutaFisica)) unlink($rutaFisica); // Borrar archivo físico
        $pdo->prepare("DELETE FROM documentos WHERE id_documento = ?")->execute([$idDoc]);
        $mensaje = "<div class='alert alert-success'>Documento eliminado.</div>";
    }
}

// Obtener empleados para el desplegable
$empleados = $pdo->prepare("SELECT U.id_usuario, U.nombre, U.apellidos FROM USUARIO U JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario WHERE EU.id_empresa = ? AND EU.activo = 1 ORDER BY U.nombre");
$empleados->execute([$empresa]);

// Obtener historial de documentos subidos
$documentos = $pdo->prepare("SELECT D.*, U.nombre, U.apellidos FROM documentos D JOIN USUARIO U ON D.id_usuario = U.id_usuario WHERE D.id_empresa = ? ORDER BY D.anio DESC, D.mes DESC");
$documentos->execute([$empresa]);
$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
?>

<div class="container mt-4">
    <h2>📁 Gestión de Nóminas y Documentos</h2>
    <?= $mensaje ?>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">Subir Nuevo Documento</div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-4">
                    <label>Empleado</label>
                    <select name="id_usuario" class="form-select" required>
                        <option value="">Seleccionar empleado...</option>
                        <?php while($emp = $empleados->fetch()): ?>
                            <option value="<?= $emp['id_usuario'] ?>"><?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Título del Documento</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ej: Nómina Abril 2026" required>
                </div>
                <div class="col-md-2">
                    <label>Mes</label>
                    <select name="mes" class="form-select" required>
                        <?php for($i=1; $i<=12; $i++): ?>
                            <option value="<?= $i ?>" <?= date('n') == $i ? 'selected' : '' ?>><?= $meses[$i] ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Año</label>
                    <input type="number" name="anio" class="form-control" value="<?= date('Y') ?>" required>
                </div>
                <div class="col-md-9">
                    <label>Archivo PDF</label>
                    <input type="file" name="documento" class="form-control" accept="application/pdf" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" name="subir_nomina" class="btn btn-success w-100">Subir Documento</button>
                </div>
            </form>
        </div>
    </div>

    <h4>Historial de Documentos</h4>
    <table class="table table-hover table-white shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>Empleado</th>
                <th>Documento</th>
                <th>Periodo</th>
                <th>Fecha Subida</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($doc = $documentos->fetch()): ?>
            <tr>
                <td><?= htmlspecialchars($doc['nombre'] . ' ' . $doc['apellidos']) ?></td>
                <td><?= htmlspecialchars($doc['titulo']) ?></td>
                <td><?= $meses[$doc['mes']] ?> <?= $doc['anio'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($doc['fecha_subida'])) ?></td>
                <td>
                    <a href="<?= htmlspecialchars($doc['ruta_archivo']) ?>" target="_blank" class="btn btn-sm btn-info text-white">Ver</a>
                    <a href="?seccion=nominas_admin&borrar=<?= $doc['id_documento'] ?>" onclick="return confirm('¿Seguro que quieres borrar este documento?');" class="btn btn-sm btn-danger">Borrar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>