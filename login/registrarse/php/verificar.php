<?php
include 'conexion.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $query = "SELECT * FROM usuario WHERE token = '$token' AND verificado = 0";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $update = "UPDATE usuario SET verificado = 1, token = NULL WHERE token = '$token'";
        mysqli_query($conn, $update);
        echo "Tu cuenta ha sido verificada exitosamente.";
    } else {
        echo "Token inválido o la cuenta ya fue verificada.";
    }
}
?>