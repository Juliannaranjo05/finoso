<?php
/**
 * Archivo de conexión a la base de datos para el panel de administración
 */

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "finoso";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar charset
$conn->set_charset("utf8");
?>
