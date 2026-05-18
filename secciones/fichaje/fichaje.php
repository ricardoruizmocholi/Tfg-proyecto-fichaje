<!--
  fichaje.php — Vista principal del reloj fichador
  Incluida por panel.php (seccion=fichaje / seccion=fichaje&vista=inicio). Admin y empleado.
  Muestra los botones de Entrada / Pausa / Reanudación / Salida según el turno activo.
  Verifica IP autorizada si la empresa tiene restricción habilitada.
  Incluye widget de meteorología (API OpenWeatherMap) para alertas de condiciones extremas.
-->
<link rel="stylesheet" href="css/fichaje.css">

<?php
// Variables de sesión necesarias para este archivo
$usuario   = $_SESSION['usuario'];
$empresa   = $_SESSION['empresa_activa'];
$idUsuario = $_SESSION['usuario']['id'];
$esAdmin   = $_SESSION['es_admin'] ?? 0;

ini_set('display_errors', 1);
error_reporting(E_ALL);
// ---------------------------------------------------------------
// Detectar si hoy tiene jornada partida asignada en HORARIOS
// ---------------------------------------------------------------
$hoy = date('Y-m-d');
$idEmpresaActual = $_SESSION['empresa_activa'];

$sqlHorarioHoy = "SELECT tipo_jornada, hora_inicio, hora_fin, orden_dia
                  FROM HORARIOS
                  WHERE id_usuario = :idu AND id_empresa = :ide AND fecha = :hoy
                  ORDER BY orden_dia ASC";
$stmtH = $pdo->prepare($sqlHorarioHoy);
$stmtH->execute(['idu' => $usuario['id'], 'ide' => $idEmpresaActual, 'hoy' => $hoy]);
$horariosHoy = $stmtH->fetchAll(PDO::FETCH_ASSOC);

$tienePartidaM = false;
$tienePartidaT = false;
$hiM = $hfM = $hiT = $hfT = null;

foreach ($horariosHoy as $h) {
    if ($h['tipo_jornada'] === 'PARTIDA_M') {
        $tienePartidaM = true;
        $hiM = $h['hora_inicio'];
        $hfM = $h['hora_fin'];
    }
    if ($h['tipo_jornada'] === 'PARTIDA_T') {
        $tienePartidaT = true;
        $hiT = $h['hora_inicio'];
        $hfT = $h['hora_fin'];
    }
}
$esPartida = $tienePartidaM || $tienePartidaT;

// ---------------------------------------------------------------
// Obtener fichajes de hoy (puede haber 2 en jornada partida)
// ---------------------------------------------------------------
$sqlF = "SELECT * FROM FICHAJE WHERE id_usuario = :idu AND fecha = CURDATE() ORDER BY id_fichaje ASC";
$stmtF = $pdo->prepare($sqlF);
$stmtF->execute(['idu' => $usuario['id']]);
$fichajesHoy = $stmtF->fetchAll(PDO::FETCH_ASSOC);

$fichajeHoy   = $fichajesHoy[0] ?? null; // primero del día (referencia para partida)
$fichajeTarde = $fichajesHoy[1] ?? null; // segundo (tarde en jornada partida)

// ---------------------------------------------------------------
// Lógica botones — Normal
// Puede haber más de un fichaje si el empleado fichó salida por
// error y volvió a fichar entrada. Buscamos el fichaje "activo":
// el que tiene entrada registrada pero SIN salida todavía.
// ---------------------------------------------------------------
$fichajeActivo = null;
foreach ($fichajesHoy as $f) {
    if ($f['hora_entrada'] !== null && $f['hora_salida'] === null) {
        $fichajeActivo = $f;
        break; // el primero abierto es el activo
    }
}

// ENTRADA: solo disponible si no hay ningún fichaje abierto ahora mismo
$mostrarEntrada  = ($fichajeActivo === null);
// Pausa / Reanudar / Salida: usan el fichaje que esté abierto
$mostrarPausa    = $fichajeActivo !== null && $fichajeActivo['hora_pausa'] === null;
$mostrarReanudar = $fichajeActivo !== null && $fichajeActivo['hora_pausa'] !== null && $fichajeActivo['hora_reanudacion'] === null;
$mostrarSalida   = $fichajeActivo !== null;

// ---------------------------------------------------------------
// Lógica botones — Partida
// ---------------------------------------------------------------
$mananaCerrado   = $fichajeHoy && $fichajeHoy['hora_salida'] !== null;
$pMostrarEntM    = !$fichajeHoy;                                                                 // mostrar "Entrada Mañana"
$pMostrarSalM    = $fichajeHoy && $fichajeHoy['hora_entrada'] !== null && $fichajeHoy['hora_salida'] === null; // mostrar "Salida Mañana"
$pMostrarEntT    = $mananaCerrado && (!$fichajeTarde || $fichajeTarde['hora_salida'] !== null);  // mostrar "Entrada Tarde"
$pMostrarSalT    = $fichajeTarde && $fichajeTarde['hora_entrada'] !== null && $fichajeTarde['hora_salida'] === null; // mostrar "Salida Tarde"
$jornadaCompleta = $mananaCerrado && $fichajeTarde && $fichajeTarde['hora_salida'] !== null;

// IP
$ip_usuario = $_SERVER['REMOTE_ADDR'];
require_once __DIR__ . '/../api/fichaje_ip_helper.php';
$acceso_permitido = fichaje_ip_permitida($pdo, (int)$_SESSION['empresa_activa']);
?>

<h2> Fichaje</h2>

<?php if ($esPartida): ?>
    <!-- ======================================
         JORNADA PARTIDA
    ======================================= -->
    <div style="background:#e8eaf6;border-left:4px solid #3f51b5;padding:15px;border-radius:5px;margin-bottom:15px;">
        <p><strong> Hoy tienes una <u>jornada partida</u></strong></p>
        <?php if ($tienePartidaM): ?>
            <p>1º Tramo : <strong><?= substr($hiM,0,5) ?> – <?= substr($hfM,0,5) ?></strong></p>
        <?php endif; ?>
        <?php if ($tienePartidaT): ?>
            <p> 2º Tramo : <strong><?= substr($hiT,0,5) ?> – <?= substr($hfT,0,5) ?></strong></p>
        <?php endif; ?>
    </div>
    <div class="contenedor-fichaje">
        
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;">
            <div style="background:#f0f4ff;padding:15px;border-radius:5px;">
                <p><strong>1º Tramo </strong></p>
                <?php if ($fichajeHoy): ?>
                    <p> Entrada: <strong><?= substr($fichajeHoy['hora_entrada'],0,5) ?? '-' ?></strong></p>
                    <p> Salida: <strong><?= $fichajeHoy['hora_salida'] ? substr($fichajeHoy['hora_salida'],0,5) : '—' ?></strong></p>
                <?php else: ?>
                    <p style="color:#888;">Sin fichar</p>
                <?php endif; ?>
            </div>
            <div style="background:#fce4ec;padding:15px;border-radius:5px;">
                <p><strong>2º Tramo </strong></p>
                <?php if ($fichajeTarde): ?>
                    <p> Entrada: <strong><?= substr($fichajeTarde['hora_entrada'],0,5) ?? '-' ?></strong></p>
                    <p> Salida: <strong><?= $fichajeTarde['hora_salida'] ? substr($fichajeTarde['hora_salida'],0,5) : '—' ?></strong></p>
                <?php else: ?>
                    <p style="color:#888;">Sin fichar</p>
                <?php endif; ?>
            </div>
        </div>
    
        <div id="weather-widget" style="margin-bottom: 20px;">
            <div class="weather-loading">Cargando clima local...</div>
        </div>
    </div>


    <div class="fichaje-panel">
        <?php if ($acceso_permitido): ?>
            <form action="secciones/fichaje/fichaje_accion.php" method="POST">
                <?php if ($jornadaCompleta): ?>
                    <p style="color:#28a745;font-weight:bold;padding:10px;"> Jornada partida completada</p>
                <?php else: ?>
                    <?php if ($pMostrarEntM): ?>
                        <button type="submit" name="accion" value="entrada" class="btn-fichaje btn-entrada"> Entrada 1º Tramo</button>
                    <?php endif; ?>
                    <?php if ($pMostrarSalM): ?>
                        <button type="submit" name="accion" value="salida" class="btn-fichaje btn-salida"> Salida 1º Tramo</button>
                    <?php endif; ?>
                    <?php if ($pMostrarEntT): ?>
                        <button type="submit" name="accion" value="entrada_tarde" class="btn-fichaje btn-reanudar" style="background:#3f51b5;"> Entrada 2º Tramo</button>
                    <?php endif; ?>
                    <?php if ($pMostrarSalT): ?>
                        <button type="submit" name="accion" value="salida_tarde" class="btn-fichaje btn-salida" style="background:#880e4f;">Salida 2º Tramo</button>
                    <?php endif; ?>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <div style="background:#fff3f3;color:#d32f2f;padding:20px;border-radius:8px;border:1px solid #ffcdd2;text-align:center;">
                <strong><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em" style="vertical-align:-0.125em"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/></svg> Acceso Restringido</strong>
                <p style="margin:10px 0 0;">Solo puedes fichar desde la red del despacho.</p>
                <small>Tu IP: <?= $ip_usuario ?></small>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- ======================================
         JORNADA NORMAL (código original)
    ======================================= -->
    <div class="contenedor-fichaje">

        <?php if($fichajeHoy): ?>
            <div class="contendor_horas" >
                <p><strong>Estado del fichaje de hoy:</strong></p>
                <p> Entrada: <strong><?= $fichajeHoy['hora_entrada'] ?? '-' ?></strong></p>
                <p> Pausa: <strong><?= $fichajeHoy['hora_pausa'] ?? '-' ?></strong></p>
                <p> Reanudación: <strong><?= $fichajeHoy['hora_reanudacion'] ?? '-' ?></strong></p>
                <p> Salida: <strong><?= $fichajeHoy['hora_salida'] ?? '-' ?></strong></p>
            </div>
        <?php else: ?>
            <div class="contendor_sin_horas">
                <p><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em" style="vertical-align:-0.125em"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg> No has fichado hoy todavía.</p>
            </div>
        <?php endif; ?>
    
        <div id="weather-widget" style="margin-bottom: 20px;">
            <div class="weather-loading">Cargando clima local...</div>
        </div>
    </div>

    <div class="fichaje-panel">
        <?php if ($acceso_permitido): ?>
            <form action="secciones/fichaje/fichaje_accion.php" method="POST">
                <?php if($mostrarEntrada): ?>
                    <button type="submit" name="accion" value="entrada" class="btn-fichaje btn-entrada"> Fichar Entrada</button>
                <?php endif; ?>
                <?php if($mostrarPausa): ?>
                    <button type="submit" name="accion" value="pausa" class="btn-fichaje btn-pausa"> Iniciar Pausa</button>
                <?php endif; ?>
                <?php if($mostrarReanudar): ?>
                    <button type="submit" name="accion" value="reanudar" class="btn-fichaje btn-reanudar"> Reanudar Trabajo</button>
                <?php endif; ?>
                <?php if($mostrarSalida): ?>
                    <button type="submit" name="accion" value="salida" class="btn-fichaje btn-salida"> Fichar Salida</button>
                    <?php if($fichajeHoy['hora_pausa'] !== null && $fichajeHoy['hora_reanudacion'] === null): ?>
                        <p style="color:#ff9800;margin-top:10px;"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em" style="vertical-align:-0.125em"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg> Estás en pausa...</p>
                    <?php endif; ?>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <div style="background:#fff3f3;color:#d32f2f;padding:20px;border-radius:8px;border:1px solid #ffcdd2;text-align:center;">
                <strong> Acceso Restringido</strong>
                <p style="margin:10px 0 0;">Solo puedes fichar desde la red del despacho.</p>
                <small>Tu IP: <?= $ip_usuario ?></small>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<hr style="margin:30px 0;">
<h3>Últimos fichajes</h3>
<?php
$sqlH = "SELECT * FROM FICHAJE WHERE id_usuario = :idu ORDER BY fecha DESC, id_fichaje DESC LIMIT 10";
$stmtHL = $pdo->prepare($sqlH);
$stmtHL->execute(['idu' => $usuario['id']]);
$historial = $stmtHL->fetchAll(PDO::FETCH_ASSOC);
if(count($historial) > 0):
?>
<div class="table-container">
    <table class="fichajes-table">
        <thead>
            <tr>
                <th>Fecha</th><th>Entrada</th><th>Pausa</th><th>Reanudación</th><th>Salida</th><th>Tramo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($historial as $f): 
                $tipo_f = $f['tipo'] ?? 'normal';
                $claseF = $f['hora_salida'] ? 'fila-completo' : 'fila-proceso';
                $tramoLabel = match($tipo_f) {
                    'partida_tarde'  => '2º Tramo',
                    default          => ' Continua'
                };
            ?>
            <tr class="<?= $claseF ?>">
                <td><?= date('d/m/Y', strtotime($f['fecha'])) ?></td>
                <td><?= $f['hora_entrada'] ? substr($f['hora_entrada'],0,5) : '-' ?></td>
                <td><?= $f['hora_pausa'] ? substr($f['hora_pausa'],0,5) : '-' ?></td>
                <td><?= $f['hora_reanudacion'] ? substr($f['hora_reanudacion'],0,5) : '-' ?></td>
                <td><?= $f['hora_salida'] ? substr($f['hora_salida'],0,5) : '-' ?></td>
                <td><?= $tramoLabel ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <p>No hay fichajes registrados.</p>
<?php endif; ?>

<style>
.alerta-amarilla { background:#fffbeb; border-left:4px solid #f59e0b; color:#92400e; }
.alerta-naranja  { background:#fff7ed; border-left:4px solid #ea580c; color:#9a3412; }
.alerta-roja     { background:#fff1f2; border-left:4px solid #dc2626; color:#991b1b; }
</style>

<div id="modal-alerta-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:32px 28px;max-width:420px;width:90%;text-align:center;box-shadow:0 8px 40px rgba(0,0,0,0.3);">
        <div id="modal-alerta-icono" style="font-size:3rem;line-height:1;margin-bottom:12px;"></div>
        <h3 id="modal-alerta-titulo" style="margin:0 0 10px;font-size:1.25rem;"></h3>
        <p id="modal-alerta-texto" style="margin:0 0 22px;color:#555;line-height:1.5;"></p>
        <button id="modal-alerta-cerrar" onclick="cerrarAlerta()" style="padding:10px 30px;border:none;border-radius:6px;cursor:pointer;font-size:1rem;font-weight:600;color:#fff;">Entendido</button>
    </div>
</div>

<div id="qa-test-buttons" style="position:fixed;bottom:18px;right:18px;z-index:9000;display:flex;flex-direction:column;gap:4px;opacity:0.5;font-size:0.72rem;">
    <span style="text-align:center;color:#555;letter-spacing:1px;font-weight:700;font-size:0.6rem;">TEST</span>
    <button onclick="mostrarAlerta('amarilla')" style="padding:3px 8px;border:1px solid #d97706;background:#fef3c7;border-radius:4px;cursor:pointer;">🟡 Alerta Amarilla</button>
    <button onclick="mostrarAlerta('naranja')"  style="padding:3px 8px;border:1px solid #ea580c;background:#fff7ed;border-radius:4px;cursor:pointer;">🟠 Alerta Naranja</button>
    <button onclick="mostrarAlerta('roja')"     style="padding:3px 8px;border:1px solid #dc2626;background:#fff1f2;border-radius:4px;cursor:pointer;">🔴 Alerta Roja</button>
</div>

<script>
    const ALERTAS = {
        amarilla: { icono: '🟡', titulo: 'Alerta Meteorológica Amarilla', texto: 'Se han detectado condiciones meteorológicas adversas en tu zona. Extrema la precaución durante los desplazamientos.', color: '#d97706' },
        naranja:  { icono: '🟠', titulo: 'Alerta Meteorológica Naranja',  texto: 'Condiciones meteorológicas de riesgo elevado en tu área. Actúa con mayor precaución y sigue las recomendaciones de las autoridades.', color: '#ea580c' },
        roja:     { icono: '🔴', titulo: 'Alerta Meteorológica Roja',     texto: 'ALERTA ROJA: Se recomienda evitar desplazamientos. El fichaje ha sido deshabilitado por seguridad mientras persistan estas condiciones extremas.', color: '#dc2626', bloqueaFichaje: true }
    };

    // Umbrales: amarilla ≥50 km/h o ≥15 mm/h | naranja ≥70 km/h o ≥30 mm/h | roja ≥90 km/h o ≥60 mm/h
    function evaluarAlertaMeteorologica(weatherData) {
        const viento = (weatherData.wind?.speed ?? 0) * 3.6; // m/s → km/h
        const lluvia = weatherData.rain?.['1h'] ?? 0;        // mm/h (ausente = sin lluvia)
        if (viento >= 90 || lluvia >= 60) return 'roja';
        if (viento >= 70 || lluvia >= 30) return 'naranja';
        if (viento >= 50 || lluvia >= 15) return 'amarilla';
        return null;
    }

    function bloquearFichaje(activar) {
        document.querySelectorAll('.btn-fichaje, form button[type="submit"]').forEach(btn => {
            btn.disabled = activar;
            btn.classList.toggle('btn-disabled', activar);
        });
    }

    function mostrarAlerta(nivel, esReal = false) {
        const alerta = ALERTAS[nivel];
        const overlay = document.getElementById('modal-alerta-overlay');
        document.getElementById('modal-alerta-icono').textContent = alerta.icono;
        document.getElementById('modal-alerta-titulo').textContent = alerta.titulo;
        document.getElementById('modal-alerta-texto').textContent  = alerta.texto;
        document.getElementById('modal-alerta-cerrar').style.background = alerta.color;
        overlay.style.display  = 'flex';
        overlay.dataset.nivel  = nivel;
        overlay.dataset.esReal = esReal ? '1' : '';
        if (alerta.bloqueaFichaje) {
            bloquearFichaje(true);
            sessionStorage.setItem('alertaRoja', 'activa');
            if (!sessionStorage.getItem('alertaRojaEmailEnviado')) {
                sessionStorage.setItem('alertaRojaEmailEnviado', 'true');
                fetch('secciones/api/send_alerta_roja.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ empresa_id: <?= (int)$empresa ?>, nivel: 'roja' })
                })
                .then(r => r.json())
                .then(d => { if (!d.success || d.errores?.length) console.error('Alerta roja email:', d); })
                .catch(e => console.error('Alerta roja email fetch:', e));
            }
        }
    }

    function cerrarAlerta() {
        document.getElementById('modal-alerta-overlay').style.display = 'none';
    }

    document.addEventListener("DOMContentLoaded", function() {
        if (sessionStorage.getItem('alertaRoja') === 'activa') bloquearFichaje(true);

        const apiKey = '69d2e88e83a50d651ac646d55dc998bb';

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(position => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                const url = `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric&lang=es`;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        const temp = Math.round(data.main.temp);
                        const desc = data.weather[0].description;
                        const icon = data.weather[0].icon;
                        const city = data.name;
                        const code = data.weather[0].id; // Código de condición
                        const wind = data.wind.speed * 3.6; // Convertir m/s a km/h

                        const nivelAlerta = evaluarAlertaMeteorologica(data);
                        if (nivelAlerta) {
                            mostrarAlerta(nivelAlerta, true);
                        } else if (sessionStorage.getItem('alertaRoja') === 'activa') {
                            bloquearFichaje(false);
                            sessionStorage.removeItem('alertaRoja');
                            sessionStorage.removeItem('alertaRojaEmailEnviado');
                        }

                        const alertaMsgs = {
                            amarilla: '🟡 ALERTA AMARILLA: Condiciones meteorológicas adversas en tu zona.',
                            naranja:  '🟠 ALERTA NARANJA: Condiciones de riesgo elevado. Actúa con precaución.',
                            roja:     '🚨 ALERTA ROJA: Evita desplazamientos. Fichaje deshabilitado por seguridad.'
                        };
                        const dangerHtml = nivelAlerta
                            ? `<div class="danger-msg alerta-${nivelAlerta}">${alertaMsgs[nivelAlerta]}</div>`
                            : '';

                        document.getElementById('weather-widget').innerHTML = `
                            <div class="weather-card ${nivelAlerta === 'roja' ? 'danger' : ''}">
                                <div style="width: 100%;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div class="weather-info">
                                            <span class="weather-city">${city} ${nivelAlerta === 'roja' ? '🚨' : ''}</span>
                                            <span class="weather-desc">${desc}</span>
                                            <span class="weather-temp">${temp}°C</span>
                                        </div>
                                        <div class="weather-icon-container">
                                            <img src="https://openweathermap.org/img/wn/${icon}@2x.png">
                                        </div>
                                    </div>
                                    ${dangerHtml}
                                </div>
                            </div>
                        `;
                    });
            });
        }
    });

</script>