<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/phpmailer/phpmailer/src/Exception.php';
require '../../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../../vendor/phpmailer/phpmailer/src/SMTP.php';

function enviarCorreoVerificacion($emailDestino, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'davidpascuas708@gmail.com'; // tu correo
        $mail->Password = 'qinc wznz hvmv zqwu';  // tu contraseña o App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('tucorreo@gmail.com', 'Finoso');
        $mail->addAddress($emailDestino);
        $mail->isHTML(true);
        $mail->Subject = 'Verifica tu cuenta';
        $mail->Body    = 'Haz clic en el siguiente enlace para verificar tu cuenta:<br><br>
        <a href="http://localhost/finoso/login/registrarse/php/verificar.php?token=' . $token . '">Verificar cuenta</a>';

        $mail->send();
    } catch (Exception $e) {
        echo "Error al enviar correo: {$mail->ErrorInfo}";
    }
}
