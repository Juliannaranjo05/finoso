<?php 
session_start(); 
require 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

// Verificar si hay sesión activa
if (!isset($_SESSION['nombre'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
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

$id_reloj = $data['id_reloj'] ?? null;

if (!$id_reloj) {
    echo json_encode(['success' => false, 'message' => 'ID del reloj requerido']);
    exit;
}

// Verificar si ya está en el carrito
$check = $conn->prepare("SELECT 1 FROM carrito WHERE id_usuario = ? AND id_reloj = ?");
$check->bind_param("ii", $id_usuario, $id_reloj);
$check->execute();
$resultCheck = $check->get_result();

if ($resultCheck->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Este reloj ya está en el carrito']);
    exit;
}

// Insertar si no existe
$stmt = $conn->prepare("INSERT INTO carrito (id_usuario, id_reloj) VALUES (?, ?)");
$stmt->bind_param("ii", $id_usuario, $id_reloj);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Reloj agregado al carrito']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar en el carrito']);
}
?>