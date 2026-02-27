<?php
// secciones/horario.php
if (!isset($_SESSION['usuario'])) {
    die("Acceso no autorizado");
}

$usuario = $_SESSION['usuario'];
$idUsuario = $usuario['id'];
$empresa = $_SESSION['empresa_activa'];
$esAdmin = $_SESSION['es_admin'] ?? 0;
?>

<div id="seccion-horarios">
    <?php if ($esAdmin): ?>
        <!-- VISTA ADMINISTRADOR -->
        <?php include __DIR__ . '/horario_admin.php'; ?>
    <?php else: ?>
        <!-- VISTA EMPLEADO -->
        <?php include __DIR__ . '/horario_empleado.php'; ?>
    <?php endif; ?>
</div>

<script>
    // Variables globales
    const ID_USUARIO = <?= $idUsuario ?>;
    const ID_EMPRESA = <?= $empresa ?>;
    const ES_ADMIN = <?= $esAdmin ?>;
</script>