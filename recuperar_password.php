<?php
session_start();
header('Content-Type: application/json');

require 'config.php';

// Incluir PHPMailer
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email no válido']);
    exit;
}

$email = trim($data['email']);

// Verificar que el email existe en la base de datos
$stmt = $pdo->prepare("SELECT id_usuario, nombre FROM USUARIO WHERE email = ? AND activo = 1");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Email no registrado']);
    exit;
}

// Generar código aleatorio de 6 dígitos
$code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

// Guardar en sesión
$_SESSION['reset_code'] = $code;
$_SESSION['reset_email'] = $email;
$_SESSION['reset_code_time'] = time();

// Configurar PHPMailer
$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'ricardoruizm99@gmail.com';
    $mail->Password   = 'acqu mnfq gfxc bngd';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';
    
    // Opciones SSL para desarrollo local
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // Remitente y destinatario
    $mail->setFrom('ricardoruizm99@gmail.com', 'Sistema de Fichajes');
    $mail->addAddress($email, $user['nombre']);

    // Contenido del email
    $mail->isHTML(true);
    $mail->Subject = 'Codigo de recuperacion de contrasena';
    $mail->Body    = "
        <html>
        <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
            <div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #667eea; text-align: center;'>Recuperacion de Contrasena</h2>
                <p>Hola <strong>{$user['nombre']}</strong>,</p>
                <p>Has solicitado recuperar tu contrasena. Usa el siguiente codigo de verificacion:</p>
                <div style='background: #f0f0f0; padding: 20px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #667eea; margin: 20px 0; border-radius: 5px;'>
                    {$code}
                </div>
                <p style='color: #666;'><strong>Este codigo expirara en 15 minutos.</strong></p>
                <p style='color: #999; font-size: 12px; margin-top: 30px;'>Si no solicitaste este codigo, ignora este mensaje.</p>
            </div>
        </body>
        </html>
    ";
    $mail->AltBody = "Tu codigo de recuperacion es: {$code}. Este codigo expira en 15 minutos.";

    $mail->send();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Error PHPMailer: {$mail->ErrorInfo}");
    
    echo json_encode([
        'success' => false, 
        'message' => "Error al enviar el correo. Intentalo mas tarde."
    ]);
}
?>