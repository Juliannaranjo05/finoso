<?php
$servername = "localhost";
$username = "root"; // o el usuario de tu base de datos
$password = "";     // o la contraseña correspondiente
$database = "finoso";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Configurar charset UTF-8
$conn->set_charset("utf8mb4");
?>