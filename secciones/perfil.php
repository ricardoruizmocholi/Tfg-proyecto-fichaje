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
<style>
    .perfil-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .perfil-card {
        background: white;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .perfil-header {
        display: flex;
        align-items: center;
        gap: 30px;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }

    .foto-perfil-container {
        position: relative;
    }

    .foto-perfil {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #dfb65fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    .cambiar-foto-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: #dfb65fff;
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        cursor: pointer;
        font-size: 18px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        transition: background 0.3s;
    }

    .cambiar-foto-btn:hover {
        background: #dfb65fff;
    }

    .usuario-info h2 {
        margin: 0 0 10px 0;
        color: #333;
    }

    .usuario-info p {
        margin: 5px 0;
        color: #666;
    }

    .datos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .dato-item {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #dfb65fff;
    }

    .dato-label {
        font-weight: bold;
        color: #555;
        font-size: 12px;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .dato-valor {
        font-size: 16px;
        color: #333;
    }

    .boton-accion {
        padding: 12px 24px;
        background: #dfb65fff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: background 0.3s;
        margin-right: 10px;
    }

    .boton-accion:hover {
        background: #5568d3;
    }

    .boton-accion.rojo {
        background: #dc3545;
    }

    .boton-accion.rojo:hover {
        background: #c82333;
    }

    /* Modal */
    .modal-perfil {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.6);
        animation: fadeIn 0.3s;
    }

    .modal-content-perfil {
        background: white;
        margin: 5% auto;
        padding: 30px;
        width: 90%;
        max-width: 500px;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        animation: slideDown 0.3s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideDown {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .close-modal {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        line-height: 20px;
    }

    .close-modal:hover {
        color: #000;
    }

    .form-group-perfil {
        margin-bottom: 20px;
    }

    .form-group-perfil label {
        display: block;
        margin-bottom: 8px;
        color: #555;
        font-weight: 600;
    }

    .form-group-perfil input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        box-sizing: border-box;
    }

    .form-group-perfil input:focus {
        outline: none;
        border-color: #667eea;
    }

    .mensaje-perfil {
        padding: 12px;
        border-radius: 5px;
        margin-top: 15px;
        font-size: 14px;
    }

    .mensaje-perfil.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .mensaje-perfil.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .input-file-custom {
        display: none;
    }
</style>
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
                <p>📧 <?= htmlspecialchars($datosUsuario['email']) ?></p>
                <p>🏢 Empresa: <strong><?= htmlspecialchars($usuario['empresas'][0]['nombre'] ?? 'N/A') ?></strong></p>
            </div>
        </div>

        <!-- Datos del usuario -->
        <div class="datos-grid">
            <div class="dato-item">
                <div class="dato-label">📄 NIF</div>
                <div class="dato-valor"><?= htmlspecialchars($datosUsuario['NIF'] ?? 'No registrado') ?></div>
            </div>

            <div class="dato-item">
                <div class="dato-label">🔢 Nº Afiliación</div>
                <div class="dato-valor"><?= htmlspecialchars($datosUsuario['Numero_Afiliciacion'] ?? 'No registrado') ?></div>
            </div>

            <div class="dato-item">
                <div class="dato-label">📅 Cuenta creada</div>
                <div class="dato-valor"><?= date('d/m/Y', strtotime($datosUsuario['created_at'] ?? 'now')) ?></div>
            </div>

            <div class="dato-item">
                <div class="dato-label">✅ Estado</div>
                <div class="dato-valor"><?= $datosUsuario['activo'] ? '🟢 Activo' : '🔴 Inactivo' ?></div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div style="margin-top: 20px;">
            <button class="boton-accion" onclick="abrirModalPassword()">
                🔒 Cambiar Contraseña
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