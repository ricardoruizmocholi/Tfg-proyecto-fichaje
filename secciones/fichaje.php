<link rel="stylesheet" href="css/fichaje.css">

<?php
// Buscar el fichaje actual del día
$sql = "SELECT * FROM FICHAJE 
        WHERE id_usuario = :id_usuario 
        AND fecha = CURDATE() 
        ORDER BY id_fichaje DESC 
        LIMIT 1";
// Preparar la consulta        
$stmt = $pdo->prepare($sql);
// Ejecutar la consulta con el ID del usuario en sesión
$stmt->execute(['id_usuario' => $usuario['id']]);

// $fichaje lista asociativa del fichaje de hoy si existe
$fichajeHoy = $stmt->fetch(PDO::FETCH_ASSOC);

// Determinar qué botones mostrar
$mostrarEntrada = !$fichajeHoy || $fichajeHoy['hora_salida'] !== null;
$mostrarPausa = $fichajeHoy && $fichajeHoy['hora_entrada'] !== null && $fichajeHoy['hora_pausa'] === null && $fichajeHoy['hora_salida'] === null;
$mostrarReanudar = $fichajeHoy && $fichajeHoy['hora_pausa'] !== null && $fichajeHoy['hora_reanudacion'] === null && $fichajeHoy['hora_salida'] === null;
$mostrarSalida = $fichajeHoy && $fichajeHoy['hora_entrada'] !== null && $fichajeHoy['hora_salida'] === null;

// Función para obtener la IP real del usuario
$ip_usuario = $_SERVER['REMOTE_ADDR'];
 
// 3. Definimos si tiene permiso:
$acceso_permitido = (
    $ip_usuario === IP_OFICINA ||            // Acceso por IP Pública (Servidor Externo)
    strpos($ip_usuario, RANGO_LOCAL) === 0 || // Acceso por Red Local (Servidor en NAS)
    $ip_usuario === '127.0.0.1' ||           // Tu PC de desarrollo
    $ip_usuario === '::1'                    // Tu PC de desarrollo (IPv6)
);
?>

<h2> Fichaje</h2>

<!-- Mostrar estado actual -->
<?php if($fichajeHoy): ?>
    <div style="background: #cacacaa8; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <p><strong>Estado del fichaje de hoy:</strong></p>
        <!-- Mostrar horas registradas -->
        <p>🕐 Entrada: <strong><?= $fichajeHoy['hora_entrada'] ?? '-' ?></strong></p>
        <p>⏸️ Pausa: <strong><?= $fichajeHoy['hora_pausa'] ?? '-' ?></strong></p>
        <p>▶️ Reanudación: <strong><?= $fichajeHoy['hora_reanudacion'] ?? '-' ?></strong></p>
        <p>🏁 Salida: <strong><?= $fichajeHoy['hora_salida'] ?? '-' ?></strong></p>
    </div>
<?php else: ?>
    <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <p>⚠️ No has fichado hoy todavía.</p>
    </div>
<?php endif; ?>

<div class="fichaje-panel">
    <?php if ($acceso_permitido): ?>
        <form action="secciones/fichaje_accion.php" method="POST">
            <?php if($mostrarEntrada): ?>
                <button type="submit" name="accion" value="entrada" class="boton">🟢 Fichar Entrada</button>
            <?php endif; ?>
            
            <?php if($mostrarPausa): ?>
                <button type="submit" name="accion" value="pausa" class="boton amarillo">⏸️ Iniciar Pausa</button>
            <?php endif; ?>
            
            <?php if($mostrarReanudar): ?>
                <button type="submit" name="accion" value="reanudar" class="boton">▶️ Reanudar Trabajo</button>
            <?php endif; ?>
            
            <?php if($mostrarSalida): ?>
                <button type="submit" name="accion" value="salida" class="boton rojo">🔴 Fichar Salida</button>
                <?php if($fichajeHoy && $fichajeHoy['hora_pausa'] !== null && $fichajeHoy['hora_reanudacion'] === null): ?>
                    <p style="color: #ff9800; margin-top: 10px;">⚠️ Atención: Estás en pausa...</p>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    <?php else: ?>
        <div style="background: #fff3f3; color: #d32f2f; padding: 20px; border-radius: 8px; border: 1px solid #ffcdd2; text-align: center;">
            <strong>🚫 Acceso Restringido</strong>
            <p style="margin: 10px 0 0;">Solo puedes fichar si estás conectado a la red del despacho.</p>
            <small style="opacity: 0.7;">Tu IP actual: <?= $ip_usuario ?></small>
        </div>
    <?php endif; ?>
</div>

<!-- Historial de fichajes recientes (opcional) -->
<hr style="margin: 30px 0;">
<h3> Últimos fichajes</h3>
<?php
$sqlHistorial = "SELECT * FROM FICHAJE 
                 WHERE id_usuario = :id_usuario 
                 ORDER BY fecha DESC, id_fichaje DESC 
                 LIMIT 7";
$stmtHistorial = $pdo->prepare($sqlHistorial);
$stmtHistorial->execute(['id_usuario' => $usuario['id']]);
$historial = $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);

if(count($historial) > 0):
?>
<div class="table-container">   
    <table class="fichajes-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Entrada</th>
                <th>Pausa</th>
                <th>Reanudación</th>
                <th>Salida</th>
                
            </tr>
        </thead>
        <tbody>
            <?php foreach($historial as $f): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($f['fecha'])) ?></td>
                    <td><?= $f['hora_entrada'] ?? '-' ?></td>
                    <td><?= $f['hora_pausa'] ?? '-' ?></td>
                    <td><?= $f['hora_reanudacion'] ?? '-' ?></td>
                    <td><?= $f['hora_salida'] ?? '-' ?></td>
                    
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <p>No hay fichajes registrados.</p>
<?php endif; ?>