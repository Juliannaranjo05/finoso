<?php
include 'conexion.php';
$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'];
$newPassword = password_hash($data['newPassword'], PASSWORD_DEFAULT);

$query = "SELECT correo FROM reset_tokens WHERE token = '$token' AND expira > NOW()";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    echo "Token inválido o expirado.";
    exit;
}

$row = mysqli_fetch_assoc($result);
$correo = $row['correo'];

// Actualizar contraseña
mysqli_query($conn, "UPDATE usuario SET contraseña = '$newPassword' WHERE correo = '$correo'");

// Borrar el token
mysqli_query($conn, "DELETE FROM reset_tokens WHERE token = '$token'");

echo "Contraseña actualizada con éxito.";
?>
