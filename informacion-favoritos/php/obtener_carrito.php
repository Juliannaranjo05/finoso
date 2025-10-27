<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    
    error_log("🔍 DEBUG obtener_carrito.php - Producto: " . $row['name']);
    error_log("🔍 DEBUG obtener_carrito.php - Precio BD (raw): " . $row['precio']);
    error_log("🔍 DEBUG obtener_carrito.php - Precio BD (float): " . $precioOriginal);
    error_log("🔍 DEBUG obtener_carrito.php - Descuento: " . $descuento);
    
    if ($descuento > 0) {
        $precioFinal = $precioOriginal * (1 - $descuento); // Aplicar descuento antes de dividir
        error_log("🔍 DEBUG obtener_carrito.php - Precio con descuento: " . $precioFinal);
    } else {
        $precioFinal = $precioOriginal;
        error_log("🔍 DEBUG obtener_carrito.php - Precio sin descuento: " . $precioFinal);
    }
    
    // 🔥 DIVIDIR POR 1000 PARA CORREGIR EL FORMATO
    $precioFinal = $precioFinal / 1000;
    error_log("🔍 DEBUG obtener_carrito.php - Precio final después de /1000: " . $precioFinal);
    
    error_log("🔍 DEBUG obtener_carrito.php - Precio final enviado: " . $precioFinal);

    $producto = [
        'id' => $row['id'],
        'name' => $row['name'],
        'currentPrice' => $precioFinal,
        'originalPrice' => $descuento > 0 ? $precioOriginal / 1000 : null,
        'description' => $row['description'],
        'image' => '../' . $row['image'],
        'descuento' => $descuento
    ];

    $productos[] = $producto;
}

echo json_encode($productos, JSON_UNESCAPED_UNICODE);