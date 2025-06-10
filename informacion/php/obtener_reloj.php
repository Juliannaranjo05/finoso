<?php
include 'conexion.php';

if (!isset($_GET['id_reloj'])) {
    echo json_encode(['error' => 'ID no especificado']);
    exit;
}

$id = intval($_GET['id_reloj']); // Seguridad básica

$sql = "SELECT * FROM reloj WHERE id_reloj = $id";
$resultado = mysqli_query($conn, $sql);

if (mysqli_num_rows($resultado) === 0) {
    echo json_encode(['error' => 'Reloj no encontrado']);
    exit;
}

$reloj = mysqli_fetch_assoc($resultado);

// Devuelve los datos en formato JSON
echo json_encode($reloj);
?>