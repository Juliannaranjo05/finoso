<?php
include 'conexion.php'; // Asegúrate de que $conn sea un objeto mysqli

// Obtener los 4 relojes disponibles (no vendidos) ordenados por precio descendente (más caro primero)
$sql = "SELECT id_reloj, img, precio, marca, nombre FROM reloj WHERE vendido = 0 AND disponible = 1 ORDER BY precio DESC LIMIT 4";
$result = $conn->query($sql);

$datos = [];
if ($result && $result->num_rows > 0) {
    while ($fila = $result->fetch_assoc()) {
        $datos[] = $fila;
    }
}

// Si no hay suficientes relojes disponibles, completar con relojes vendidos (para mostrar algo)
if (count($datos) < 4) {
    $faltantes = 4 - count($datos);
    $sql_complemento = "SELECT id_reloj, img, precio, marca, nombre FROM reloj WHERE vendido = 1 AND disponible = 1 ORDER BY precio DESC LIMIT $faltantes";
    $result_complemento = $conn->query($sql_complemento);
    
    if ($result_complemento && $result_complemento->num_rows > 0) {
        while ($fila = $result_complemento->fetch_assoc()) {
            $datos[] = $fila;
        }
    }
}

echo json_encode($datos);
?>