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
    $precioOriginal = (float) $row['precio']; // Cambiado a float
    $descuento = (float) $row['descuento'];
    
    if ($descuento > 0) {
        $precioFinal = round($precioOriginal * (1 - $descuento), 2); // Mantenemos 2 decimales
    } else {
        $precioFinal = $precioOriginal;
    }

    $producto = [
        'id' => $row['id'],
        'name' => $row['name'],
        'currentPrice' => $precioFinal,
        'originalPrice' => $descuento > 0 ? $precioOriginal : null,
        'description' => $row['description'],
        'image' => '../' . $row['image'],
        'descuento' => $descuento
    ];

    $productos[] = $producto;
}

echo json_encode($productos, JSON_UNESCAPED_UNICODE);