<?php
include 'conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de Cuenta</title>
    <link rel="icon" href="http://127.0.0.1/finoso/img/finoso_logo.png" type="image/x-icon">
    <style>
        body {
            background-color: #090909;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .gif-container {
            text-align: center;
        }
        .gif-container img {
            width: 150px; /* Puedes ajustar el tamaño */
        }
        .mensaje {
            color: white;
            margin-top: 20px;
            font-size: 18px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <?php
    if (isset($_GET['token'])) {
        $token = $_GET['token'];
        $query = "SELECT * FROM usuario WHERE token = '$token' AND verificado = 0";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $update = "UPDATE usuario SET verificado = 1, token = NULL WHERE token = '$token'";
            mysqli_query($conn, $update);
            ?>
            <div class="gif-container">
                <img src="http://127.0.0.1/finoso/img/check.gif" alt="Verificado">
                <div class="mensaje">¡Tu cuenta ha sido verificada!</div>
            </div>
            <?php
        } else {
            echo "<div class='mensaje'>Token inválido o la cuenta ya fue verificada.</div>";
        }
    }
    ?>
</body>
</html>