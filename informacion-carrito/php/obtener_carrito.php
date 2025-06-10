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
    $descuento = (int) $row['descuento'];
    $precioFinal = $precioOriginal;

    if ($descuento > 0) {
        // Calcular el precio con descuento
        $precioConDescuento = $precioOriginal * (1 - $descuento);  // Resta el descuento al precio original
        $precioConDescuento = round($precioConDescuento, 0);  // Redondeamos el precio a la unidad más cercana
        $precioConDescuento = number_format($precioConDescuento, 0, ',', '.') . '.000';  // Formateamos con .000
    } else {
        // Si no hay descuento, el precio es el original
        $precioConDescuento = number_format($precioOriginal, 0, ',', '.') . '.000';
    }

    // Calcular la diferencia entre el precio original y el precio con descuento
    $diferenciaPrecio = $precioOriginal - $precioConDescuento;
    $diferenciaPrecio = number_format($diferenciaPrecio, 0, ',', '.') . '.000'; // Formatear la diferencia

    $producto = [
        'id' => $row['id'],
        'name' => $row['name'],
        'currentPrice' => $precioConDescuento,  // Precio con descuento
        'originalPrice' => $descuento > 0 ? '$' . number_format($precioOriginal, 0, ',', '.') . '.000' : null,  // Precio original si hay descuento
        'discountDifference' => $descuento > 0 ? '$' . $diferenciaPrecio : null,  // Diferencia si hay descuento
        'description' => $row['description'],
        'image' => '../' . $row['image']
    ];

    // Agregar el producto al array de productos
    $productos[] = $producto;
}

echo json_encode($productos, JSON_UNESCAPED_UNICODE);