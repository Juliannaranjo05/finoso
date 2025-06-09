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

    $mail->setFrom('TU_CORREO@gmail.com', 'Finoso');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Restablece tu contraseña';
    $mail->Body = 'Haz clic aquí para restablecer tu contraseña: 
    <a href="http://127.0.0.1/finoso/login/olvidar-contraseña/reset_password.html?token=' . $token . '">Restablecer</a>';

    $mail->send();
    echo "Correo de recuperación enviado.";
} catch (Exception $e) {
    echo "Error al enviar correo: {$mail->ErrorInfo}";
}
?>