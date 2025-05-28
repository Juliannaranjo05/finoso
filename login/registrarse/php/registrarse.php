<?php
// Incluye la conexión
include 'conexion.php'; // o el nombre correcto de tu archivo de conexión

// Verifica si llegaron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // encriptar contraseña
    $email = $_POST['email'];
    $fecha = date("Y-m-d H:i:s");

    // Validar si el usuario o correo ya existe (opcional pero recomendado)
    $check_query = "SELECT * FROM usuario WHERE nombre = '$nombre' OR email = '$email'";
    $result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($result) > 0) {
        echo "El usuario o email ya existe.";
    } else {
        $query = "INSERT INTO usuario (nombre, email, password, fecha_registro)
                  VALUES ('$nombre', '$email', '$password', '$fecha')";

        if (mysqli_query($conn, $query)) {
            echo "Usuario registrado exitosamente.";
            // Aquí puedes redirigir con header:
            // header("Location: ../login.html");
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }

    mysqli_close($conn);
}
?>