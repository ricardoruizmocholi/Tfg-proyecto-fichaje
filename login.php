<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Fichajes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #5568d3;
        }

        .forgot-link {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-link a:hover {
            text-decoration: underline;
        }

        /* Estilos del modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            width: 90%;
            max-width: 450px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: slideDown 0.3s;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .close-btn {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 20px;
        }

        .close-btn:hover,
        .close-btn:focus {
            color: #000;
        }

        .modal h3 {
            margin-bottom: 20px;
            color: #333;
            clear: both;
        }

        .message {
            padding: 10px;
            margin-top: 15px;
            border-radius: 5px;
            font-size: 14px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .loading {
            display: none;
            text-align: center;
            margin-top: 10px;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2> Iniciar Sesión</h2>
        <form action="login_procesar.php" method="POST">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required placeholder="tu@email.com">
            </div>

            <div class="form-group">
                <label>Contraseña:</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn">Entrar</button>
        </form>
        
        <div class="forgot-link">
            <a href="#" id="forgotPasswordLink">¿Olvidaste tu contraseña?</a>
        </div>
    </div>

    <!-- Modal 1: Introducir email -->
    <div id="modalEmail" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeEmail">&times;</span>
            <h3>🔑 Recuperar contraseña</h3>
            <p>Introduce tu email y te enviaremos un código de verificación.</p>
            <form id="formEmail">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" id="emailReset" required placeholder="tu@email.com">
                </div>
                <button type="submit" class="btn">Enviar código</button>
                <div class="loading" id="loadingEmail">
                    <div class="spinner"></div>
                    <p>Enviando código...</p>
                </div>
            </form>
            <div id="emailMessage"></div>
        </div>
    </div>

    <!-- Modal 2: Introducir código -->
    <div id="modalCode" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeCode">&times;</span>
            <h3>✉️ Verificar código</h3>
            <p>Introduce el código de 6 dígitos que hemos enviado a tu correo.</p>
            <form id="formCode">
                <div class="form-group">
                    <input type="text" id="codeInput" required maxlength="6" placeholder="123456" 
                           pattern="[0-9]{6}" style="text-align: center; font-size: 24px; letter-spacing: 5px;">
                </div>
                <button type="submit" class="btn">Verificar código</button>
                <div class="loading" id="loadingCode">
                    <div class="spinner"></div>
                    <p>Verificando...</p>
                </div>
            </form>
            <div id="codeMessage"></div>
        </div>
    </div>

    <!-- Modal 3: Cambiar contraseña -->
    <div id="modalChangePass" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeChangePass">&times;</span>
            <h3>🔒 Nueva contraseña</h3>
            <p>Introduce tu nueva contraseña (mínimo 6 caracteres).</p>
            <form id="formChangePass">
                <div class="form-group">
                    <label>Nueva contraseña:</label>
                    <input type="password" id="newPass1" required minlength="6" placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label>Repite la contraseña:</label>
                    <input type="password" id="newPass2" required minlength="6" placeholder="••••••••">
                </div>
                <button type="submit" class="btn">Cambiar contraseña</button>
                <div class="loading" id="loadingChange">
                    <div class="spinner"></div>
                    <p>Actualizando...</p>
                </div>
            </form>
            <div id="changePassMessage"></div>
        </div>
    </div>

    <script>
        const modalEmail = document.getElementById('modalEmail');
        const modalCode = document.getElementById('modalCode');
        const modalChangePass = document.getElementById('modalChangePass');

        const forgotLink = document.getElementById('forgotPasswordLink');

        const closeEmail = document.getElementById('closeEmail');
        const closeCode = document.getElementById('closeCode');
        const closeChangePass = document.getElementById('closeChangePass');

        // Abrir modal de email
        forgotLink.onclick = e => {
            e.preventDefault();
            modalEmail.style.display = 'block';
            document.getElementById('emailReset').focus();
        }

        // Cerrar modales
        closeEmail.onclick = () => {
            modalEmail.style.display = 'none';
            clearMessages();
        }
        closeCode.onclick = () => {
            modalCode.style.display = 'none';
            clearMessages();
        }
        closeChangePass.onclick = () => {
            modalChangePass.style.display = 'none';
            clearMessages();
        }

        // Cerrar al hacer clic fuera
        window.onclick = e => {
            if (e.target === modalEmail) {
                modalEmail.style.display = 'none';
                clearMessages();
            }
            if (e.target === modalCode) {
                modalCode.style.display = 'none';
                clearMessages();
            }
            if (e.target === modalChangePass) {
                modalChangePass.style.display = 'none';
                clearMessages();
            }
        };

        function clearMessages() {
            document.getElementById('emailMessage').innerHTML = '';
            document.getElementById('codeMessage').innerHTML = '';
            document.getElementById('changePassMessage').innerHTML = '';
        }

        // PASO 1: Enviar email para generar código
        document.getElementById('formEmail').onsubmit = async e => {
            e.preventDefault();
            const email = document.getElementById('emailReset').value;
            const msgDiv = document.getElementById('emailMessage');
            const loading = document.getElementById('loadingEmail');

            msgDiv.innerHTML = '';
            loading.style.display = 'block';

            try {
                const res = await fetch('recuperar_password.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({email})
                });
                const data = await res.json();

                loading.style.display = 'none';

                if(data.success) {
                    msgDiv.innerHTML = '<div class="message success">✅ Código enviado a tu correo.</div>';
                    setTimeout(() => {
                        modalEmail.style.display = 'none';
                        modalCode.style.display = 'block';
                        document.getElementById('codeInput').focus();
                    }, 1500);
                } else {
                    msgDiv.innerHTML = `<div class="message error">❌ ${data.message}</div>`;
                }
            } catch(error) {
                loading.style.display = 'none';

                console.error('Error completo:', error); // Ver el error en consola
    
                // Intenta obtener la respuesta como texto
                fetch('recuperar_password.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({email})
                })
                .then(res => res.text()) // ⬅️ Cambia a .text() para ver qué devuelve
                .then(text => console.log('Respuesta del servidor:', text))
                .catch(err => console.error('Error de red:', err));

                msgDiv.innerHTML = '<div class="message error">❌ Error de conexión. Inténtalo de nuevo.</div>';
            }
        };

        // PASO 2: Verificar código
        document.getElementById('formCode').onsubmit = async e => {
            e.preventDefault();
            const code = document.getElementById('codeInput').value;
            const msgDiv = document.getElementById('codeMessage');
            const loading = document.getElementById('loadingCode');

            if(code.length !== 6 || !/^\d+$/.test(code)) {
                msgDiv.innerHTML = '<div class="message error">❌ El código debe tener 6 dígitos.</div>';
                return;
            }

            msgDiv.innerHTML = '';
            loading.style.display = 'block';

            try {
                const res = await fetch('verificar_codigo.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({code})
                });
                const data = await res.json();

                loading.style.display = 'none';

                if(data.success) {
                    msgDiv.innerHTML = '<div class="message success">✅ Código verificado correctamente.</div>';
                    setTimeout(() => {
                        modalCode.style.display = 'none';
                        modalChangePass.style.display = 'block';
                        document.getElementById('newPass1').focus();
                    }, 1000);
                } else {
                    msgDiv.innerHTML = '<div class="message error">❌ Código incorrecto o expirado.</div>';
                }
            } catch(error) {
                loading.style.display = 'none';
                msgDiv.innerHTML = '<div class="message error">❌ Error de conexión. Inténtalo de nuevo.</div>';
            }
        };

        // PASO 3: Cambiar contraseña
        document.getElementById('formChangePass').onsubmit = async e => {
            e.preventDefault();
            const pass1 = document.getElementById('newPass1').value;
            const pass2 = document.getElementById('newPass2').value;
            const msgDiv = document.getElementById('changePassMessage');
            const loading = document.getElementById('loadingChange');

            if(pass1 !== pass2) {
                msgDiv.innerHTML = '<div class="message error">❌ Las contraseñas no coinciden.</div>';
                return;
            }

            if(pass1.length < 6) {
                msgDiv.innerHTML = '<div class="message error">❌ La contraseña debe tener al menos 6 caracteres.</div>';
                return;
            }

            msgDiv.innerHTML = '';
            loading.style.display = 'block';

            try {
                const res = await fetch('cambiar_password.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({password: pass1})
                });
                const data = await res.json();

                loading.style.display = 'none';

                if(data.success) {
                    msgDiv.innerHTML = '<div class="message success">✅ Contraseña cambiada con éxito. Redirigiendo...</div>';
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    msgDiv.innerHTML = `<div class="message error">❌ ${data.message}</div>`;
                }
            } catch(error) {
                loading.style.display = 'none';
                msgDiv.innerHTML = '<div class="message error">❌ Error de conexión. Inténtalo de nuevo.</div>';
            }
        };

        // Solo números en el campo de código
        document.getElementById('codeInput').oninput = function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        }
    </script>
</body>
</html>