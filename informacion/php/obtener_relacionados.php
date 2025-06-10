<?php
include 'conexion.php';

if (!isset($_GET['id_reloj'])) {
    echo json_encode(['error' => 'ID no especificado']);
    exit;
}

$id_reloj = intval($_GET['id_reloj']);

// Primero obtenemos la marca del reloj principal
$sqlMarca = "SELECT marca FROM reloj WHERE id_reloj = $id_reloj";
$resultMarca = mysqli_query($conn, $sqlMarca);

if (!$resultMarca || mysqli_num_rows($resultMarca) === 0) {
    echo json_encode(['error' => 'Reloj no encontrado']);
    exit;
}

$marca = mysqli_fetch_assoc($resultMarca)['marca'];

// Luego buscamos otros relojes con la misma marca, excluyendo el reloj principal
$sqlRelacionados = "SELECT * FROM reloj WHERE marca = '$marca' AND id_reloj != $id_reloj LIMIT 4"; // Puedes ajustar el límite
$resultRelacionados = mysqli_query($conn, $sqlRelacionados);

$relacionados = [];
while ($row = mysqli_fetch_assoc($resultRelacionados)) {
    $relacionados[] = $row;
}

echo json_encode($relacionados);
?>