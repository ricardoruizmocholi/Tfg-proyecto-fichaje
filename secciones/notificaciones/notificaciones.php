<?php
/*
 * notificaciones.php — Vista del panel de notificaciones
 * Incluida por panel.php (seccion=notificaciones). Admin y empleado.
 * Muestra todas las notificaciones del usuario (leídas y no leídas).
 * Permite marcar como leída y eliminar via fetch a notificaciones_operaciones.php.
 */
if (!isset($_SESSION['usuario'])) {
    die("Acceso no autorizado");
}

require_once __DIR__ . '/../../config.php';
$idUsuario = $_SESSION['usuario']['id'];
$empresa   = $_SESSION['empresa_activa'];

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
            <h2> Notificaciones</h2>
            <p>Gestiona tus avisos y actualizaciones del sistema</p>
        </div>
        <button class="btn-clear-all" onclick="eliminarTodas()">
           Limpiar todo
        </button>
    </div>
    <div class="notif-list-container">
        <?php if (empty($notificaciones)): ?>
            <div class="notif-empty-state">
                <div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776"/></svg></div>
                <p>No tienes notificaciones pendientes</p>
            </div>
        <?php else: ?>
            <div class="notif-grid" id="contenedor-notificaciones">
                <?php foreach ($notificaciones as $n): 
                    // Lógica de iconos y colores
                    $icono = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>';
                    $claseTipo = '';
                    $msg = $n['mensaje'];

                    if (strpos($msg, 'ACEPTADA') !== false || strpos($msg, 'APROBADA') !== false) {
                        $icono = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>'; $claseTipo = 'notif-success';
                    } elseif (strpos($msg, 'RECHAZADA') !== false) {
                        $icono = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>'; $claseTipo = 'notif-danger';
                    } elseif (strpos($msg, 'modificado') !== false || strpos($msg, 'añadido') !== false) {
                        $icono = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>'; $claseTipo = 'notif-info';
                    }
                ?>
                    <div class="notif-card <?= $claseTipo ?>" id="notif-<?= $n['id_notificacion'] ?>">
                        <div class="notif-icon-circle"><?= $icono ?></div>
                        <div class="notif-content">
                            <p class="notif-message"><?= htmlspecialchars($msg) ?></p>
                            <span class="notif-date">
                                 <?= date('d/m/Y - H:i', strtotime($n['fecha_creacion'])) ?>
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
    
    fetch('secciones/notificaciones/notificaciones_operaciones.php', {
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
    
    fetch('secciones/notificaciones/notificaciones_operaciones.php', {
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