<?php
session_start();
header('Content-Type: application/json');
require 'conexion.php';

if (!isset($_SESSION['nombre'])) {
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesión']);
    exit;
}

if (!isset($_POST['id_reloj'])) {
    echo json_encode(['success' => false, 'message' => 'Falta el ID del reloj']);
    exit;
}

// Obtener el ID del usuario
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
$id_reloj = $_POST['id_reloj'];

// Eliminar el reloj del carrito del usuario
$stmt = $conn->prepare("DELETE FROM carrito WHERE id_usuario = ? AND id_reloj = ?");
$stmt->bind_param("ii", $id_usuario, $id_reloj);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Reloj eliminado del carrito']);
} else {
    echo json_encode(['success' => false, 'message' => 'No se encontró el reloj en el carrito']);
}
?>