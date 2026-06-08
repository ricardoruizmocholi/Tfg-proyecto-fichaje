<?php
session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$empresaId = isset($input['empresa_id']) ? (int)$input['empresa_id'] : 0;
$nivel     = $input['nivel'] ?? '';

if ($empresaId <= 0 || $nivel !== 'roja') {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

// Obtener emails de todos los empleados activos de la empresa
$stmt = $pdo->prepare("
    SELECT U.email, U.nombre, U.apellidos
    FROM USUARIO U
    JOIN EMPRESA_USUARIO EU ON EU.id_usuario = U.id_usuario
    WHERE EU.id_empresa = :empresa_id
      AND EU.activo = 1
      AND U.activo = 1
");
$stmt->execute(['empresa_id' => $empresaId]);
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($empleados)) {
    echo json_encode(['success' => true, 'enviados' => 0, 'errores' => []]);
    exit;
}

require_once '../../PHPMailer/src/Exception.php';
require_once '../../PHPMailer/src/PHPMailer.php';
require_once '../../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$enviados = 0;
$errores  = [];

foreach ($empleados as $emp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ];

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($emp['email'], $emp['nombre'] . ' ' . $emp['apellidos']);

        $nombre = htmlspecialchars($emp['nombre']);
        $mail->isHTML(true);
        $mail->Subject = '⚠️ ALERTA METEOROLÓGICA ROJA — Evite desplazamientos';
        $mail->Body = "
        <html>
        <body style='font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;margin:0;'>
          <div style='max-width:600px;margin:0 auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.12);'>
            <div style='background:#dc2626;padding:28px 30px;text-align:center;'>
              <p style='font-size:2.5rem;margin:0;'>🚨</p>
              <h1 style='color:#fff;margin:10px 0 4px;font-size:1.4rem;letter-spacing:.5px;'>ALERTA METEOROLÓGICA ROJA</h1>
              <p style='color:#fecaca;margin:0;font-size:.9rem;'>Aviso emitido por el Sistema de Fichajes</p>
            </div>
            <div style='padding:30px;'>
              <p style='margin-top:0;'>Hola, <strong>{$nombre}</strong>:</p>
              <p>Se han detectado <strong>condiciones meteorológicas de máximo riesgo</strong> en tu área de trabajo.</p>
              <div style='background:#fff1f2;border-left:4px solid #dc2626;padding:16px 20px;border-radius:0 6px 6px 0;margin:20px 0;'>
                <p style='margin:0 0 8px;font-weight:700;color:#dc2626;'>¿Qué significa esto para ti?</p>
                <ul style='margin:0;padding-left:18px;color:#374151;line-height:1.7;'>
                  <li>El fichaje ha sido <strong>bloqueado temporalmente</strong> por tu seguridad.</li>
                  <li>Evita cualquier desplazamiento innecesario hasta nuevo aviso.</li>
                  <li>Permanece en un lugar seguro y sigue las recomendaciones de las autoridades.</li>
                </ul>
              </div>
              <p style='color:#6b7280;font-size:.9rem;'>El sistema de fichaje se reactivará automáticamente cuando las condiciones meteorológicas mejoren y cese la alerta roja.</p>
              <p style='color:#6b7280;font-size:.85rem;margin-bottom:0;'>Este es un mensaje automático del Sistema de Fichajes. No respondas a este correo.</p>
            </div>
            <div style='background:#f9fafb;border-top:1px solid #e5e7eb;padding:14px 30px;text-align:center;'>
              <p style='margin:0;color:#9ca3af;font-size:.8rem;'>Sistema de Fichajes &mdash; Aviso de seguridad meteorológica</p>
            </div>
          </div>
        </body>
        </html>";

        $mail->send();
        $enviados++;
    } catch (Exception $e) {
        $errores[] = $emp['email'] . ': ' . $mail->ErrorInfo;
    }
}

echo json_encode([
    'success'  => $enviados > 0 || empty($errores),
    'enviados' => $enviados,
    'errores'  => $errores,
]);
