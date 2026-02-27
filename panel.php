<?php 

session_start();
//llamar a la conexion de base de datos
require_once 'config.php';

// si no hay sesion activa, redirigir al login
if(!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

//usuario en sesion
/*
$_SESSION['usuario'] = [
    "id" => $usuarioEncontrado['id_usuario'],
    "nombre" => $usuarioEncontrado['nombre'],
    "apellidos" => $usuarioEncontrado['apellidos'],
    "email" => $usuarioEncontrado['email'],
    "empresas" => $empresasAsociadas
];

*/
$usuario = $_SESSION['usuario'];
$empresa = $_SESSION['empresa_activa'] ?? null;
$idUsuario = $usuario['id'];
$esAdmin = $_SESSION['es_admin'] ?? null;

if(!$empresa) {
    die("Error no he encontrado enpresa vinculada al usuario.");
}

// Determinar que seccion carga
$seccion = $_GET['seccion'] ?? 'fichaje';
$vista = $_GET['vista'] ?? 'inicio';

//------------------------------------------------
// Carga CSS según empresa
//------------------------------------------------
$empresa = $_SESSION['empresa_activa']; 
$cssFile = "css/empresa_$empresa.css";

if (!file_exists($cssFile)) {
    $cssFile = "css/default.css"; // Por si hay una empresa sin css aún
}

$nombreEmpresa = "Empresa desconocida";

foreach ($_SESSION['usuario']['empresas'] as $emp) {
    if ($emp['id_empresa'] == $empresa) {
        $nombreEmpresa = $emp['nombre'];
        break;
    }
}



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="<?= $cssFile ?>">
</head>
<style>

</style>
<body>
    
    <header>
        <div class="logo"><?=$nombreEmpresa?></div>
        <div>
             <span style="color:white; margin-right:15px;">
                <?= $esAdmin ? ' Admin' : ' Empleado' ?> - 
                <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']) ?>
            </span>
            <div class="notification-container" style="display: inline-block; position: relative; margin-right: 15px;">
                <?php
                // Contar notificaciones no leídas para el badge
                $stmtNotif = $pdo->prepare("SELECT COUNT(*) FROM NOTIFICACIONES WHERE id_usuario = ? AND leida = 0");
                $stmtNotif->execute([$idUsuario]);
                $countNotif = $stmtNotif->fetchColumn();
                $badge = ($countNotif > 9) ? '9+' : $countNotif;
                ?>
                
                <a href="panel.php?seccion=notificaciones" style="text-decoration: none; font-size: 20px;">
                    🔔
                    <?php if ($countNotif > 0): ?>
                        <span class="notification-badge"><?= $badge ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <a href="logout.php" style="color:white; margin-left:20px;">Cerrar sesión</a>
        </div>
    </header>

    <?php if($esAdmin): ?>
        <div class="sidebar">
            <h3 class="sidebar-title">Administrador</h3>

            <!-- FICHAJE -->
            
                <a class="submenu-btn">
                    Fichaje
                    <svg class="arrow-icon" width="16" height="16" viewBox="0 0 20 20">
                        <path d="M 10 13.7 a 0.897 0.897 0 0 1 -0.636 -0.264 l -4.6 -4.6 a 0.9 0.9 0 1 1 1.272 -1.273 L 10 11.526 l 3.964 -3.963 a 0.9 0.9 0 0 1 1.272 1.273 l -4.6 4.6 A 0.897 0.897 0 0 1 10 13.7 Z"></path>
                    </svg>
                </a>
                <div class="submenu-content">
                    <a href="panel.php?seccion=fichaje&vista=inicio">Fichar</a>
                    <a href="panel.php?seccion=fichaje&vista=ver">Ver fichajes</a>
                    <a href="panel.php?seccion=fichaje&vista=modificar">Modificar fichajes</a>
                </div>

            <!-- HORARIO -->
            
                <a class="submenu-btn">
                    Horario
                    <svg class="arrow-icon" width="16" height="16" viewBox="0 0 20 20">
                        <path d="M 10 13.7 a 0.897 0.897 0 0 1 -0.636 -0.264 l -4.6 -4.6 a 0.9 0.9 0 1 1 1.272 -1.273 L 10 11.526 l 3.964 -3.963 a 0.9 0.9 0 0 1 1.272 1.273 l -4.6 4.6 A 0.897 0.897 0 0 1 10 13.7 Z"></path>
                    </svg>
                </a>
                <div class="submenu-content">
                    <a href="panel.php?seccion=horario&vista=peticiones">Peticiones</a>
                    <a href="panel.php?seccion=horario&vista=cuadrantes">Cuadrantes</a>
                </div>
            

            
           

            <!-- REPORTES -->
            
                <a class="submenu-btn">
                    Reportes
                    <svg class="arrow-icon" width="16" height="16" viewBox="0 0 20 20">
                        <path d="M 10 13.7 a 0.897 0.897 0 0 1 -0.636 -0.264 l -4.6 -4.6 a 0.9 0.9 0 1 1 1.272 -1.273 L 10 11.526 l 3.964 -3.963 a 0.9 0.9 0 0 1 1.272 1.273 l -4.6 4.6 A 0.897 0.897 0 0 1 10 13.7 Z"></path>
                    </svg>
                </a>
                <div class="submenu-content">
                    <a href="panel.php?seccion=reportes&vista=generar">Generar reportes</a>
                    <a href="panel.php?seccion=reportes&vista=historial">Historial reportes</a>
                </div>
            

            <!-- GESTIÓN EMPLEADOS -->
            
                <a class="submenu-btn">
                    Gestión empleados
                    <svg class="arrow-icon" width="16" height="16" viewBox="0 0 20 20">
                        <path d="M 10 13.7 a 0.897 0.897 0 0 1 -0.636 -0.264 l -4.6 -4.6 a 0.9 0.9 0 1 1 1.272 -1.273 L 10 11.526 l 3.964 -3.963 a 0.9 0.9 0 0 1 1.272 1.273 l -4.6 4.6 A 0.897 0.897 0 0 1 10 13.7 Z"></path>
                    </svg>
                </a>
                <div class="submenu-content">
                    <a href="panel.php?seccion=empleados&vista=lista">Ver empleados</a>
                    <a href="panel.php?seccion=empleados&vista=nuevo">Añadir empleados</a>
                </div>
           

            <a href="panel.php?seccion=perfil"> Perfil</a>
        </div>
    <?php else: ?>

        <div class="sidebar">
            <h3>Empleado</h3>
        <a href="panel.php?seccion=fichaje"> Fichaje</a>
        <a href="panel.php?seccion=horario"> Horario</a>
        <a href="panel.php?seccion=perfil"> Perfil</a>
        </div>
    <?php endif; ?>    
    

    <main>
        <?php
            /* --------------------------
   SECCIONES ADMIN + EMPLEADO
--------------------------- */

// Fichaje (ambos)

    /* --------------------------
    SOLO ADMIN
    --------------------------- */
    if ($esAdmin) {

        if ($seccion === "fichaje") {
            if ($vista === "ver") include "secciones/fichaje_ver.php";
            if ($vista === "modificar") include "secciones/fichaje_modificar.php";
            if ($vista === "inicio") include "secciones/fichaje.php"; // general
        }

        if ($seccion === "horario") {
            if ($vista === "peticiones") include "secciones/horario.php";
            if ($vista === "cuadrantes") include "secciones/horario_cuadrantes.php";
            if ($vista === "eventos") include "secciones/horario_eventos.php";
            if (!$vista) include "secciones/horario.php"; // general
        }

        if ($seccion === "reportes") {
            if ($vista === "generar") include "secciones/reportes.php";
            if ($vista === "historial") include "secciones/reportes_historial.php";
          if (!$vista)  include "secciones/reportes.php";
        }

        if ($seccion === "empleados") {
            if ($vista === "lista") include "secciones/empleados_lista.php";
            if ($vista === "nuevo") include "secciones/empleados_nuevo.php";
        }
    }

    /* --------------------------
    SOLO EMPLEADO
    --------------------------- */
    if (!$esAdmin) {
        if ($seccion === "fichaje") {
            include "secciones/fichaje.php";
        }
        if ($seccion === "horario") {
            include "secciones/horario.php";
        }
     
       
    }

    /* --------------------------
    AMBOS
    --------------------------- */
    if ($seccion === "documentos") {
        include "secciones/documentos.php";
    }

    if ($seccion === "perfil") {
        include "secciones/perfil.php";
    }
   
    if ($seccion === "notificaciones") {
        include "secciones/notificaciones.php";
    }

        ?>
    </main>
    <script>
        document.querySelectorAll(".submenu-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                const content = btn.nextElementSibling;

                // abrir/cerrar
                btn.classList.toggle("active");
                content.classList.toggle("open");

                // cerrar otros si quieres modo acordeón:
                document.querySelectorAll(".submenu-content").forEach(other => {
                    if (other !== content) {
                        other.classList.remove("open");
                        other.previousElementSibling.classList.remove("active");
                    }
                });
            });
        });


    function toggleNotificaciones() {
        const dropdown = document.getElementById('notif-dropdown');
        const isVisible = dropdown.style.display === 'block';
        
        dropdown.style.display = isVisible ? 'none' : 'block';
        
        if (!isVisible) {
            const list = document.getElementById('notif-list');
            list.innerHTML = '<div style="padding:15px; text-align:center;">Cargando...</div>';

            fetch('secciones/notificaciones_obtener.php')
                .then(res => res.json())
                .then(data => {
                    list.innerHTML = '';
                    if (data.length === 0) {
                        list.innerHTML = '<div class="notif-item">No tienes notificaciones pendientes</div>';
                    } else {
                        data.forEach(n => {
                            const div = document.createElement('div');
                            // Si era 0 (no leída) le ponemos clase unread
                            div.className = `notif-item ${n.leida == 0 ? 'unread' : ''}`;
                            
                            // Formatear fecha simple
                            const fecha = new Date(n.fecha_creacion).toLocaleString();
                            
                            div.innerHTML = `
                                <div style="margin-bottom: 4px;">${n.mensaje}</div>
                                <small style="color: #888; font-size: 11px;">${fecha}</small>
                            `;
                            list.appendChild(div);
                        });

                        // --- AQUÍ ESTÁ EL CAMBIO: AÑADIMOS EL BOTÓN VER TODAS ---
                        const footer = document.createElement('div');
                        footer.style = "padding: 10px; text-align: center; border-top: 1px solid #eee; background: #f9f9f9; border-radius: 0 0 8px 8px;";
                        footer.innerHTML = `<a href="panel.php?seccion=notificaciones" style="text-decoration:none; color:#007bff; font-weight:bold; font-size:12px;">VER TODAS LAS NOTIFICACIONES</a>`;
                        list.appendChild(footer);
                        }
                    // Resetear el badge visualmente después de leer
                    const badge = document.querySelector('.notification-badge');
                    if (badge) badge.style.display = 'none';
                });
        }
    }

</script>

</body>
</html>