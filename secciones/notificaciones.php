<?php
if (!isset($_SESSION['usuario'])) {
    die("Acceso no autorizado");
}

$idUsuario = $_SESSION['usuario']['id'];

// Limpieza automática opcional: borrar de más de 30 días
$pdo->prepare("DELETE FROM NOTIFICACIONES WHERE id_usuario = ? AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 30 DAY)")->execute([$idUsuario]);

// Obtener todas las notificaciones
$stmt = $pdo->prepare("SELECT * FROM NOTIFICACIONES WHERE id_usuario = ? ORDER BY fecha_creacion DESC");
$stmt->execute([$idUsuario]);
$notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Marcar como leídas
$pdo->prepare("UPDATE NOTIFICACIONES SET leida = 1 WHERE id_usuario = ? AND leida = 0")->execute([$idUsuario]);
?>

<link rel="stylesheet" href="css/notificaciones.css">

<div class="notif-page-container">
    <div class="notif-header-banner">
        <div class="header-content">
            <h2>🔔 Mis Notificaciones</h2>
            <p>Gestiona tus avisos y actualizaciones del sistema</p>
        </div>
        <button class="btn-clear-all" onclick="eliminarTodas()">
            <span class="icon">🗑️</span> Limpiar todo
        </button>
    </div>

    <div class="notif-list-container">
        <?php if (empty($notificaciones)): ?>
            <div class="notif-empty-state">
                <div class="empty-icon">📂</div>
                <p>No tienes notificaciones pendientes</p>
            </div>
        <?php else: ?>
            <div class="notif-grid" id="contenedor-notificaciones">
                <?php foreach ($notificaciones as $n): 
                    // Lógica de iconos y colores
                    $icono = 'ℹ️';
                    $claseTipo = '';
                    $msg = $n['mensaje'];

                    if (strpos($msg, 'ACEPTADA') !== false || strpos($msg, 'APROBADA') !== false) {
                        $icono = '✅'; $claseTipo = 'notif-success';
                    } elseif (strpos($msg, 'RECHAZADA') !== false) {
                        $icono = '❌'; $claseTipo = 'notif-danger';
                    } elseif (strpos($msg, 'modificado') !== false || strpos($msg, 'añadido') !== false) {
                        $icono = '📝'; $claseTipo = 'notif-info';
                    }
                ?>
                    <div class="notif-card <?= $claseTipo ?>" id="notif-<?= $n['id_notificacion'] ?>">
                        <div class="notif-icon-circle"><?= $icono ?></div>
                        <div class="notif-content">
                            <p class="notif-message"><?= htmlspecialchars($msg) ?></p>
                            <span class="notif-date">
                                🕒 <?= date('d/m/Y - H:i', strtotime($n['fecha_creacion'])) ?>
                            </span>
                        </div>
                        <button class="btn-delete-single" onclick="eliminarNotificacion(<?= $n['id_notificacion'] ?>)" title="Eliminar">
                            &times;
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function eliminarNotificacion(id) {
    if(!confirm('¿Eliminar esta notificación?')) return;
    
    fetch('secciones/notificaciones_operaciones.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `accion=eliminar&id=${id}`
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            const card = document.getElementById('notif-' + id);
            card.style.opacity = '0';
            card.style.transform = 'translateX(20px)';
            setTimeout(() => {
                card.remove();
                // Si no quedan más, recargar para mostrar estado vacío
                if (document.querySelectorAll('.notif-card').length === 0) {
                    location.reload();
                }
            }, 300);
        }
    });
}

function eliminarTodas() {
    if(!confirm('¿Quieres borrar TODAS las notificaciones?')) return;
    
    fetch('secciones/notificaciones_operaciones.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `accion=eliminar_todas`
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            location.reload();
        }
    });
}
</script>