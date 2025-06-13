<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['nombre'])) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE nombre = ?");
$stmt->bind_param("s", $_SESSION['nombre']);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode([]);
    exit;
}

$usuario = $res->fetch_assoc();
$id_usuario = $usuario['id_usuario'];

$sql = "SELECT r.id_reloj AS id, r.nombre AS name, r.precio, r.descuento,
               r.descripcion AS description, r.img AS image
        FROM carrito c
        JOIN reloj r ON c.id_reloj = r.id_reloj
        WHERE c.id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$productos = [];

while ($row = $result->fetch_assoc()) {
    $precioOriginal = (int) $row['precio'];
    $descuento = (float) $row['descuento'];
    $precioFinal = $descuento > 0 ? round($precioOriginal - ($precioOriginal * $descuento)) : $precioOriginal;

    if ($descuento > 0) {
        $precioFinal = round($precioOriginal * (1 - $descuento)); // precio con descuento
    }

    $producto = [
        'id' => $row['id'],
        'name' => $row['name'],
        'currentPrice' => '$' . $precioFinal . '.000', // valor numérico limpio
        'originalPrice' => $descuento > 0 ?  '$' . $precioOriginal . '.000': null, // solo si hay descuento
        'description' => $row['description'],
        'image' => '../' . $row['image'],
        'descuento' => $descuento // opcional si lo quieres usar en JS también
    ];

    $productos[] = $producto;
}

echo json_encode($productos, JSON_UNESCAPED_UNICODE);