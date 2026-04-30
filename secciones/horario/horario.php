<?php
/*
 * horario.php — Dispatcher del módulo Horario
 * Incluido por panel.php (seccion=horario&vista=peticiones). Admin y empleado.
 * Decide si mostrar horario_admin.php o horario_empleado.php según el rol en sesión.
 * Expone las variables globales JS: ID_USUARIO, ID_EMPRESA, ES_ADMIN.
 */
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