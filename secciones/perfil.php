<?php 

$id_usuario = $usuario['id'];
//$sqlUsa
$sqlUsuario = "SELECT * FROM USUARIO WHERE id_usuario = :id_usuario";
$stmtUsuario = $pdo->prepare($sqlUsuario);
$stmtUsuario->execute(['id_usuario'=>$id_usuario]);
$datosUsuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

if(!$datosUsuario){
    die("Error al cargar datos del usuario.");
}

//Ruta de la imagen de perfil
$imagenPerfil = $datosUsuario['foto_perfil'] ?? 'secciones/uploads/perfil_default.jpg';

?>

<link rel="stylesheet" href="css/perfil.css">
<div class="perfil-container">
  

    <!-- Card principal con foto y datos básicos -->
    <div class="perfil-card">
        <div class="perfil-header">
            <div class="foto-perfil-container">
                <img src="<?= htmlspecialchars($imagenPerfil) ?>" alt="Foto de perfil" class="foto-perfil" id="fotoPerfil">
                <button class="cambiar-foto-btn" onclick="document.getElementById('inputFoto').click();" title="Cambiar foto">
                    📷
                </button>
                <form id="formFoto" enctype="multipart/form-data" style="display: none;">
                    <input type="file" id="inputFoto" name="foto" accept="image/*" class="input-file-custom">
                </form>
            </div>

            <div class="usuario-info">
                <h2><?= htmlspecialchars($datosUsuario['nombre'] . ' ' . $datosUsuario['apellidos']) ?></h2>
                <p> <?= htmlspecialchars($datosUsuario['email']) ?></p>
                <p> Empresa: <strong><?= htmlspecialchars($usuario['empresas'][0]['nombre'] ?? 'N/A') ?></strong></p>
            </div>
        </div>

        <!-- Datos del usuario -->
        <div class="datos-grid">
            <div class="dato-item">
                <div class="dato-label"> NIF</div>
                <div class="dato-valor"><?= htmlspecialchars($datosUsuario['NIF'] ?? 'No registrado') ?></div>
            </div>

            <div class="dato-item">
                <div class="dato-label"> Nº Afiliación</div>
                <div class="dato-valor"><?= htmlspecialchars($datosUsuario['Numero_Afiliciacion'] ?? 'No registrado') ?></div>
            </div>

            <div class="dato-item">
                <div class="dato-label"> Cuenta creada</div>
                <div class="dato-valor"><?= date('d/m/Y', strtotime($datosUsuario['created_at'] ?? 'now')) ?></div>
            </div>

            <div class="dato-item">
                <div class="dato-label"> Estado</div>
                <div class="dato-valor"><?= $datosUsuario['activo'] ? ' Activo' : ' Inactivo' ?></div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div style="margin-top: 20px;">
            <button class="boton-accion" onclick="abrirModalPassword()">
                 Cambiar Contraseña
            </button>
        </div>
    </div>
</div>

<!-- Modal para cambiar contraseña -->
<div id="modalPassword" class="modal-perfil">
    <div class="modal-content-perfil">
        <span class="close-modal" onclick="cerrarModalPassword()">&times;</span>
        <h3 style="margin-bottom: 20px; color: #333;">🔒 Cambiar Contraseña</h3>
        <p style="color: #666; margin-bottom: 20px;">Por seguridad, primero debes introducir tu contraseña actual.</p>
        
        <form id="formCambiarPassword">
            <div class="form-group-perfil">
                <label>🔑 Contraseña Actual:</label>
                <input type="password" id="passwordActual" required minlength="6" placeholder="••••••••">
            </div>

            <div class="form-group-perfil">
                <label>🆕 Nueva Contraseña:</label>
                <input type="password" id="passwordNueva1" required minlength="6" placeholder="••••••••">
                <small style="color: #888;">Mínimo 6 caracteres</small>
            </div>

            <div class="form-group-perfil">
                <label>✅ Repetir Nueva Contraseña:</label>
                <input type="password" id="passwordNueva2" required minlength="6" placeholder="••••••••">
            </div>

            <button type="submit" class="boton-accion" style="width: 100%;">
                Actualizar Contraseña
            </button>
        </form>

        <div id="mensajePassword"></div>
    </div>
</div>
<script>
    //abrir y cerrar modal de constraseña
    function abrirModalPassword(){
        document.getElementById('modalPassword').style.display = 'block';
        // focus es una función de javascript que pone el cursor en un input y sieve para mejorar la experiencia de usuario
        document.getElementById('passwordActual').focus();
    }
    function cerrarModalPassword(){
        document.getElementById('modalPassword').style.display = 'none';
        document.getElementById('formCambiarPassword').reset();
        document.getElementById('mensajePassword').innerHTML = '';
    }

    //Cerrar modal al hacer click fuera
    window.onclick = function(event){
        const modal = document.getElementById('modalPassword');
        if(event.target === modal){
            cerrarModalPassword();
        }
    }

    // Cabiar contraseña
    document.getElementById('formCambiarPassword').onsubmit = async function(e){
        e.preventDefault();

        const passwordActual = document.getElementById('passwordActual').value;
        const passwordNueva1 = document.getElementById('passwordNueva1').value;
        const passwordNueva2 = document.getElementById('passwordNueva2').value;
        const mensajeDiv = document.getElementById('mensajePassword');

        // Validaciones 
        if(passwordNueva1 !== passwordNueva2){
            mensajeDiv.innerHTML = '<div class="mensaje-perfil error">❌ Las nuevas contraseñas no coinciden.</div>';
            return;
        }

        if(passwordNueva1.length < 6){
            mensajeDiv.innerHTML = '<div class="mensaje-perfil error">❌ La nueva contraseña debe tener al menos 6 caracteres.</div>';
            return;
        }
        if(passwordActual === passwordNueva1){
            mensajeDiv.innerHTML = '<div class="mensaje-perfil error">❌ La nueva contraseña no puede ser igual a la actual.</div>';
            return;
        }

        mensajeDiv.innerHTML  = '<div class="mensaje-perfil" style="background: #e3f2fd; color: #0277bd;">⏳ Verificando...</div>';

        try {
            const response = await fetch('secciones/cambiar_password_perfil.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    password_actual: passwordActual,
                    password_nueva: passwordNueva1
                })

            });

            const data = await response.json();

            if(data.success){
                mensajeDiv.innerHTML = '<div class="mensaje-perfil success">✅ Contraseña actualizada correctamente.</div>';
                setTimeout(() => {
                    cerrarModalPassword();
                }, 2000);
              
            } else {
                mensajeDiv.innerHTML = `<div class="mensaje-perfil error">❌ ${data.message}</div>`;
            }
        } catch (error){
            mensajeDiv.innerHTML = '<div class="mensaje-perfil error">❌ Error de conexión. Inténtalo de nuevo.</div>';
        }
    };

    // Cambiar foto de perfil
    document.getElementById('inputFoto').onchange = async function(){
        const file = this.files[0];
        if(!file) return;

        //Validar tipo de archivo
        if(!file.type.startsWith('image/')) {
            alert('Por favor, selecciona un archivo de imagen válido.');
            return;
        }

        //Validar tamaño (maximo 5MB)
        if(file.size > 5 * 1024 * 1024){
            alert('El tamaño de la imagen no puede superar los 5MB.');
            return;
        }

        const formData = new FormData();
        formData.append('foto', file);

        try {
            const response = await fetch('secciones/cambiar_foto_perfil.php',{
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if(data.success){
                //Actualizar imagen en la página
                document.getElementById('fotoPerfil').src = data.nueva_foto + '?t=' + new Date().getTime();
                alert('✅ Foto de perfil actualizada correctamente.');
            } else {
                alert('❌ ' + data.message);
            }
        }catch(error) {
            alert('❌ Error al subir la imagen. Inténtalo de nuevo.');
        }
    };


</script>