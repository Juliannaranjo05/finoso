<?php
session_start();
header('Content-Type: application/json');

require 'conexion.php';

if (!isset($_SESSION['nombre'])) {
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesión']);
    exit;
}

// Obtener el ID del usuario basándose en el nombre de la sesión
$stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE nombre = ?");
$stmt->bind_param("s", $_SESSION['nombre']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit;
}

$usuario = $result->fetch_assoc();
$id_usuario = $usuario['id_usuario'];

// Ahora obtener los relojes del carrito
$sql = "SELECT r.id_reloj, r.nombre, r.precio, r.descuento, r.img
        FROM carrito c
        INNER JOIN reloj r ON c.id_reloj = r.id_reloj
        WHERE c.id_usuario = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$relojes = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $precioOriginal = $row['precio'];
    $descuento = $row['descuento'];

    // Asegura que el descuento esté en decimal. Si es porcentaje (20), descomenta esta línea:
    // $descuento = $descuento / 100;

    // Calcular precio con descuento si aplica
    $precio_final = $descuento ? round($precioOriginal * (1 - $descuento)) : $precioOriginal;

    // Sumar al total numérico
    $total += $precio_final;

    $relojes[] = [
        'id_reloj' => $row['id_reloj'],
        'nombre' => $row['nombre'],
        'precio' => $precioOriginal, // Sin formato para usar en JS
        'descuento' => $descuento,
        'img' => $row['img'],
        'precio_final' => $precio_final // También sin formato para usar bien en JS
    ];
}

// Retorna el total sin formato para usarlo como número en JS
echo json_encode([
    'success' => true,
    'relojes' => $relojes,
    'total' => $total
]);
?>