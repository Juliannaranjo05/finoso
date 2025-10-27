<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require 'conexion.php';

// Verificar si el usuario tiene sesión activa
if (!isset($_SESSION['id_usuario']) && !isset($_SESSION['nombre'])) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit;
}

// Obtener el ID del usuario
$id_usuario = null;

if (isset($_SESSION['id_usuario'])) {
    // Si ya tenemos el ID en la sesión, usarlo directamente
    $id_usuario = $_SESSION['id_usuario'];
} else if (isset($_SESSION['nombre'])) {
    // Si solo tenemos el nombre, buscar el ID
    $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE nombre = ?");
    $stmt->bind_param("s", $_SESSION['nombre']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'count' => 0]);
        exit;
    }

    $usuario = $result->fetch_assoc();
    $id_usuario = $usuario['id_usuario'];
} else {
    echo json_encode(['success' => false, 'count' => 0]);
    exit;
}

// Contar los relojes en el carrito
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM carrito WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'count' => (int)$row['total']
]);
?>
