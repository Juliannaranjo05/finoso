<?php
include 'conexion.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../vendor/phpmailer/phpmailer/src/Exception.php';
require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../vendor/phpmailer/phpmailer/src/SMTP.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];

// Verificar si existe el usuario
$query = "SELECT * FROM usuario WHERE correo = '$email'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    echo "El correo no está registrado.";
    exit;
}

// Generar token de reseteo
$token = bin2hex(random_bytes(16));
$expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Guardar token en tabla nueva (puedes crearla en tu DB)
mysqli_query($conn, "INSERT INTO reset_tokens (correo, token, expira) VALUES ('$email', '$token', '$expira')");

// Enviar correo
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'davidpascuas708@gmail.com';
    $mail->Password = 'qinc wznz hvmv zqwu';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('davidpascuas708@gmail.com', 'FINOSO - Relojes de Lujo');
    $mail->addAddress($email);
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = '🔐 Restablece tu contraseña - FINOSO';
    
    $mail->Body = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: \'Playfair Display\', Georgia, serif; background-color: #0a0a0a;">
        <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #0a0a0a;">
            <tr>
                <td align="center" style="padding: 40px 20px;">
                    <!-- Contenedor principal -->
                    <table role="presentation" style="max-width: 600px; width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #1a1a1a 0%, #0a0a0a 100%); border: 2px solid #FFCF66; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 40px rgba(255, 207, 102, 0.2);">
                        
                        <!-- Header con logo -->
                        <tr>
                            <td style="background: linear-gradient(135deg, #2a2a2a 0%, #1a1a1a 100%); padding: 40px 30px; text-align: center; border-bottom: 3px solid #FFCF66;">
                                <h1 style="margin: 0; color: #FFCF66; font-size: 48px; font-weight: 700; letter-spacing: 8px; text-shadow: 0 0 20px rgba(255, 207, 102, 0.5);">
                                    FINOSO
                                </h1>
                                <p style="margin: 10px 0 0; color: #FFCF66; font-size: 14px; letter-spacing: 2px; opacity: 0.8;">
                                    RELOJES DE LUJO
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Contenido -->
                        <tr>
                            <td style="padding: 50px 40px; text-align: center;">
                                <div style="margin-bottom: 30px;">
                                    <div style="width: 80px; height: 80px; margin: 0 auto 25px; background: linear-gradient(135deg, #FFCF66 0%, #d4a537 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 5px 20px rgba(255, 207, 102, 0.3);">
                                        <span style="font-size: 40px; color: #0a0a0a;">🔑</span>
                                    </div>
                                </div>
                                
                                <h2 style="margin: 0 0 20px; color: #FFCF66; font-size: 28px; font-weight: 600; letter-spacing: 1px;">
                                    Recupera tu contraseña
                                </h2>
                                
                                <p style="margin: 0 0 30px; color: #e0e0e0; font-size: 16px; line-height: 1.6;">
                                    Recibimos una solicitud para restablecer la contraseña de tu cuenta.<br>
                                    Haz clic en el botón de abajo para crear una nueva contraseña:
                                </p>
                                
                                <!-- Botón de restablecimiento -->
                                <table role="presentation" style="margin: 0 auto;">
                                    <tr>
                                        <td style="border-radius: 50px; background: linear-gradient(135deg, #FFCF66 0%, #d4a537 100%); box-shadow: 0 5px 25px rgba(255, 207, 102, 0.4);">
                                            <a href="https://finoso.store/login/olvidar-contraseña/reset_password.html?token=' . $token . '" 
                                               style="display: inline-block; padding: 18px 50px; color: #0a0a0a; text-decoration: none; font-size: 18px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;">
                                                Restablecer Contraseña
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                
                                <div style="margin: 35px 0 0; padding: 20px; background: rgba(255, 207, 102, 0.05); border-radius: 10px; border: 1px solid rgba(255, 207, 102, 0.2);">
                                    <p style="margin: 0 0 10px; color: #FFCF66; font-size: 13px; font-weight: 600;">
                                        ⏱️ Este enlace es válido por 1 hora
                                    </p>
                                    <p style="margin: 0; color: #999; font-size: 13px; line-height: 1.6;">
                                        Si no solicitaste este cambio, puedes ignorar este correo.<br>
                                        Tu contraseña actual permanecerá sin cambios.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Divisor decorativo -->
                        <tr>
                            <td style="padding: 0 40px;">
                                <div style="height: 1px; background: linear-gradient(90deg, transparent 0%, #FFCF66 50%, transparent 100%); opacity: 0.3;"></div>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style="padding: 30px 40px; text-align: center; background-color: #0a0a0a;">
                                <p style="margin: 0 0 10px; color: #FFCF66; font-size: 14px; font-weight: 600;">
                                    ¿Tienes preguntas?
                                </p>
                                <p style="margin: 0 0 20px; color: #999; font-size: 13px;">
                                    Contáctanos en: <a href="mailto:soporte@finoso.com" style="color: #FFCF66; text-decoration: none;">soporte@finoso.com</a>
                                </p>
                                
                                <div style="margin: 20px 0; padding-top: 20px; border-top: 1px solid rgba(255, 207, 102, 0.2);">
                                    <p style="margin: 0; color: #666; font-size: 12px; line-height: 1.5;">
                                        © 2025 FINOSO - Relojes de Lujo<br>
                                        Elegancia y precisión en cada detalle
                                    </p>
                                </div>
                            </td>
                        </tr>
                        
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
    ';
    
    // Versión texto plano (para clientes que no soportan HTML)
    $mail->AltBody = "Recupera tu contraseña - FINOSO\n\nRecibimos una solicitud para restablecer tu contraseña.\n\nPara crear una nueva contraseña, copia y pega este enlace en tu navegador:\n\nhttps://finoso.store/login/olvidar-contraseña/reset_password.html?token=" . $token . "\n\nEste enlace es válido por 1 hora.\n\nSi no solicitaste este cambio, puedes ignorar este correo de forma segura.\n\n© 2025 FINOSO - Relojes de Lujo";

    $mail->send();
    echo "Correo de recuperación enviado.";
} catch (Exception $e) {
    echo "Error al enviar correo: {$mail->ErrorInfo}";
}
?>