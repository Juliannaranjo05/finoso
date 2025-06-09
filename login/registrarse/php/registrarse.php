<?php
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

$nombre = $data['nombre'];
$contraseña = password_hash($data['contraseña'], PASSWORD_DEFAULT);
$correo = $data['correo'];
$fecha = date("Y-m-d H:i:s");
$token = bin2hex(random_bytes(16)); // token de 32 caracteres

// Verificar si ya existe
$check_query = "SELECT * FROM usuario WHERE nombre = '$nombre' OR correo = '$correo'";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) > 0) {
    echo "El usuario o correo ya existe.";
    exit;
}

// Insertar usuario con token y no verificado
$query = "INSERT INTO usuario (nombre, correo, contraseña, creado_en, verificado, token)
          VALUES ('$nombre', '$correo', '$contraseña', '$fecha', 0, '$token')";

if (mysqli_query($conn, $query)) {
    // Enviar email
    include 'enviar_correo.php';
    enviarCorreoVerificacion($correo, $token);
    echo "Registro exitoso. Revisa tu correo para verificar tu cuenta.";
} else {
    echo "Error al registrar.";
}

?>