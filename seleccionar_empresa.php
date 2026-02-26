
<link rel="stylesheet" href="css/selector.css">
<link href="fonts.googleapis.com" rel="stylesheet">

<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$empresas = $_SESSION['usuario']['empresas'];
?>

<div class="container">
    <h2>Selecciona una empresa</h2>

    <?php foreach ($empresas as $empresa): ?>
        <form action="usar_empresa.php" method="POST">
            <input type="hidden" name="id_empresa" value="<?= htmlspecialchars($empresa['id_empresa']) ?>">
      
            <button type="submit" class="company-card">
                <span class="company-name"><?= htmlspecialchars($empresa['nombre']) ?></span>                <!-- Añadimos un span para dar estilo al texto de administrador -->
                <?= $empresa['admin'] ? "<span class=\"admin-badge\">(Administrador)</span>" : "" ?>
            </button>
        </form>
    <?php endforeach; ?>
</div>
