<?php
include 'conexion.php'; // Asegúrate de que $conn sea un objeto mysqli

$ids = [5, 1, 6];

// Preparamos los marcadores (?, ?, ?, ?)
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = "SELECT id_reloj, img FROM reloj WHERE id_reloj IN ($placeholders)";
$stmt = $conn->prepare($sql);

// Como mysqli no permite pasar arrays directamente, usamos ... (argument unpacking)
$stmt->bind_param(str_repeat('i', count($ids)), ...$ids);

$stmt->execute();
$result = $stmt->get_result();

$datos = [];
while ($fila = $result->fetch_assoc()) {
    $datos[] = $fila;
}

echo json_encode($datos);
?>