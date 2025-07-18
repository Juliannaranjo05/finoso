<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

include 'conexion.php';
header('Content-Type: application/json');

// Leer datos del frontend
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id_reloj'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$nombre = $input['nombre'] ?? '';
$cedula = $input['cedula'] ?? '';
$celular = $input['celular'] ?? '';
$departamento = $input['departamento'] ?? '';
$ciudad = $input['ciudad'] ?? '';
$direccion = $input['direccion'] ?? '';
$barrio = $input['barrio'] ?? '';
$referencias = $input['referencias'] ?? '';
$metodo = $input['metodo_pago'] ?? 'mercado_pago';
$costo_envio = intval($input['costo_envio'] ?? 0);
$precio_reloj = intval($input['precio_reloj'] ?? 0);
$id_reloj = intval($input['id_reloj'] ?? 0);

// Validar precio total
$total = $precio_reloj + $costo_envio;

try {
    // Insertar en orden
    $stmt = $conn->prepare("INSERT INTO orden (nombre, cedula, celular, departamento, ciudad, direccion, barrio, referencias, total, metodo_pago, costo_envio, estado)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')");
    $stmt->bind_param("ssssssssdsd", $nombre, $cedula, $celular, $departamento, $ciudad, $direccion, $barrio, $referencias, $total, $metodo, $costo_envio);
    $stmt->execute();
    $id_orden = $conn->insert_id;

    // Insertar en orden_detalle
    $stmt2 = $conn->prepare("INSERT INTO orden_detalle (id_orden, id_reloj, precio_unitario) VALUES (?, ?, ?)");
    $stmt2->bind_param("iid", $id_orden, $id_reloj, $precio_reloj);
    $stmt2->execute();

    echo json_encode(['success' => true, 'id_orden' => $id_orden]);
} catch (Exception $e) {
    error_log("❌ Error al guardar orden: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en el servidor']);
}