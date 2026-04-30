<?php
/*
 * nominas_admin.php — Vista de gestión de nóminas (admin)
 * Incluida por panel.php (seccion=empleados&vista=nominas). Solo admin.
 * Permite seleccionar empleado, mes y año, y subir un PDF de nómina.
 * Los PDFs se guardan en uploads/nominas/ y se registran en la tabla DOCUMENTOS.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    die("Acceso denegado");
}

$empresa = $_SESSION['empresa_activa'];
$alertaHtml = '';

// ============================================================
// PROCESAR SUBIDA DE DOCUMENTO
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_nomina'])) {
    $idEmpleado  = $_POST['id_usuario'];
    $titulo      = trim($_POST['titulo']);
    $mes         = (int)$_POST['mes'];
    $anio        = (int)$_POST['anio'];
    $archivo     = $_FILES['documento'];

    $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

    if ($ext !== 'pdf') {
        $alertaHtml = '<div class="nominas-alert error">❌ Solo se permiten archivos PDF.</div>';
    } elseif ($archivo['size'] > 10 * 1024 * 1024) {
        $alertaHtml = '<div class="nominas-alert error">❌ El archivo no puede superar los 10MB.</div>';
    } else {
        $directorio = __DIR__ . '/../uploads/nominas/';
        if (!is_dir($directorio)) mkdir($directorio, 0777, true);

        $nombreFinal = 'nomina_' . $idEmpleado . '_' . $mes . '_' . $anio . '_' . time() . '.pdf';
        $rutaDestino = $directorio . $nombreFinal;
        $rutaBD      = 'uploads/nominas/' . $nombreFinal;

        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            $stmt = $pdo->prepare("INSERT INTO documentos (id_usuario, id_empresa, titulo, mes, anio, ruta_archivo) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$idEmpleado, $empresa, $titulo, $mes, $anio, $rutaBD]);

            // Notificar al empleado
            $msgNotif = " Tienes un nuevo documento disponible: $titulo";
            $pdo->prepare("INSERT INTO NOTIFICACIONES (id_usuario, mensaje, leida) VALUES (?, ?, 0)")
                ->execute([$idEmpleado, $msgNotif]);

            $alertaHtml = '<div class="nominas-alert success">✅ Documento subido y empleado notificado correctamente.</div>';
        } else {
            $alertaHtml = '<div class="nominas-alert error">❌ Error al guardar el archivo en el servidor.</div>';
        }
    }
}

// ============================================================
// PROCESAR BORRADO
// ============================================================
if (isset($_GET['borrar'])) {
    $idDoc = (int)$_GET['borrar'];
    $stmt  = $pdo->prepare("SELECT ruta_archivo FROM documentos WHERE id_documento = ? AND id_empresa = ?");
    $stmt->execute([$idDoc, $empresa]);
    $doc = $stmt->fetch();

    if ($doc) {
        $rutaFisica = __DIR__ . '/../' . $doc['ruta_archivo'];
        if (file_exists($rutaFisica)) unlink($rutaFisica);
        $pdo->prepare("DELETE FROM documentos WHERE id_documento = ?")->execute([$idDoc]);
        $alertaHtml = '<div class="nominas-alert success">✅ Documento eliminado correctamente.</div>';
    }
}

// ============================================================
// OBTENER DATOS
// ============================================================
$stmtEmpleados = $pdo->prepare("
    SELECT U.id_usuario, U.nombre, U.apellidos, U.foto_perfil
    FROM USUARIO U
    JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
    WHERE EU.id_empresa = ? AND EU.activo = 1
    ORDER BY U.nombre, U.apellidos
");
$stmtEmpleados->execute([$empresa]);
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

$stmtDocs = $pdo->prepare("
    SELECT D.*, U.nombre, U.apellidos, U.foto_perfil
    FROM documentos D
    JOIN USUARIO U ON D.id_usuario = U.id_usuario
    WHERE D.id_empresa = ?
    ORDER BY D.anio DESC, D.mes DESC, D.fecha_subida DESC
");
$stmtDocs->execute([$empresa]);
$documentos = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

$meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
          'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
?>

<link rel="stylesheet" href="css/nominas.css">

<div class="nominas-wrapper">

    <!-- CABECERA -->
    <div class="nominas-header-banner">
        <div>
            <h2> Gestión de Nóminas y Documentos</h2>
            <p>Sube y gestiona los documentos de los empleados de tu empresa</p>
        </div>
        <div style="font-size:40px;opacity:0.3;">📄</div>
    </div>

    <?= $alertaHtml ?>

    <!-- ========================================================
         FORMULARIO DE SUBIDA
    ========================================================= -->
    <div class="upload-card">
        <div class="upload-card-header">
            <span style="font-size:20px;"></span>
            <h3>Subir Nuevo Documento</h3>
        </div>
        <div class="upload-card-body">
            <form method="POST" enctype="multipart/form-data" id="formSubida">

                <!-- Fila 1: datos del documento -->
                <div class="upload-form-grid">
                    <div class="form-field">
                        <label>Empleado *</label>
                        <select name="id_usuario" required>
                            <option value="">Seleccionar empleado...</option>
                            <?php foreach ($empleados as $emp): ?>
                                <option value="<?= $emp['id_usuario'] ?>">
                                    <?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field">
                        <label>Título del Documento *</label>
                        <input type="text" name="titulo" placeholder="Ej: Nómina Abril 2026" required>
                    </div>

                    <div class="form-field">
                        <label>Mes *</label>
                        <select name="mes" required>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $i ?>" <?= date('n') == $i ? 'selected' : '' ?>>
                                    <?= $meses[$i] ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-field">
                        <label>Año *</label>
                        <input type="number" name="anio" value="<?= date('Y') ?>" min="2020" max="2035" required>
                    </div>
                </div>

                <!-- Fila 2: archivo + botón -->
                <div class="upload-form-grid segunda-fila">
                    <div class="form-field">
                        <label for="inputDocumento">Archivo PDF *</label>
                        <!-- Input oculto con display:none — NO usa position:absolute -->
                        <input type="file"
                               id="inputDocumento"
                               name="documento"
                               accept="application/pdf"
                               required
                               style="display:none;"
                               onchange="actualizarNombreArchivo(this)">
                        <!-- Label visual que activa el input por id -->
                        <label for="inputDocumento" class="file-input-label" id="fileLabel">
                            <span class="file-icon">📎</span>
                            <span id="fileLabelText">Haz clic para seleccionar un PDF...</span>
                        </label>
                    </div>

                    <div class="form-field">
                        <label>&nbsp;</label>
                        <button type="submit" name="subir_nomina" class="btn-upload">
                             Subir Documento
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- ========================================================
         HISTORIAL DE DOCUMENTOS SUBIDOS
    ========================================================= -->
    <div class="historial-section">
        <h3> Historial de Documentos <span style="color:var(--text-muted);font-weight:400;font-size:14px;">(<?= count($documentos) ?> documentos)</span></h3>

        <div class="historial-table-wrapper">
            <?php if (empty($documentos)): ?>
                <div class="empty-historial">
                    <div class="empty-icon">📂</div>
                    <p>No hay documentos subidos todavía</p>
                </div>
            <?php else: ?>
                <table class="historial-table">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Documento</th>
                            <th>Período</th>
                            <th>Fecha subida</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): ?>
                            <tr>
                                <td>
                                    <div class="empleado-nombre-tabla">
                                        <img src="<?= htmlspecialchars($doc['foto_perfil'] ?? 'secciones/uploads/perfil_default.jpg') ?>"
                                             alt="foto"
                                             onerror="this.src='secciones/uploads/perfil_default.jpg'">
                                        <span><?= htmlspecialchars($doc['nombre'] . ' ' . $doc['apellidos']) ?></span>
                                    </div>
                                </td>
                                <td><strong><?= htmlspecialchars($doc['titulo']) ?></strong></td>
                                <td>
                                    <span class="periodo-badge">
                                        <?= $meses[$doc['mes']] ?> <?= $doc['anio'] ?>
                                    </span>
                                </td>
                                <td style="color:var(--text-muted);">
                                    <?= date('d/m/Y H:i', strtotime($doc['fecha_subida'])) ?>
                                </td>
                                <td style="white-space:nowrap;">
                                    <a href="<?= htmlspecialchars($doc['ruta_archivo']) ?>"
                                       target="_blank"
                                       class="btn-tabla btn-tabla-ver">
                                         Ver
                                    </a>
                                    <a href="panel.php?seccion=empleados&vista=nominas&borrar=<?= $doc['id_documento'] ?>"
                                       class="btn-tabla btn-tabla-borrar"
                                       onclick="return confirm('¿Seguro que quieres borrar este documento?')">
                                         Borrar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
function actualizarNombreArchivo(input) {
    const label     = document.getElementById('fileLabel');
    const labelText = document.getElementById('fileLabelText');
    if (input.files && input.files[0]) {
        const nombre = input.files[0].name;
        const tamano = (input.files[0].size / 1024 / 1024).toFixed(2);
        labelText.textContent = ` ${nombre} (${tamano} MB)`;
        label.classList.add('has-file');
    } else {
        labelText.textContent = 'Haz clic para seleccionar un PDF...';
        label.classList.remove('has-file');
    }
}
</script>