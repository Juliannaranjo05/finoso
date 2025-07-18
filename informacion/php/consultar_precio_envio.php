<?php
include 'conexion.php';

$ciudad = $_GET['ciudad'] ?? '';
$departamento = $_GET['departamento'] ?? '';
$origen = 'Medellín'; // O la ciudad fija desde donde sale el envío

// Validar entrada
if (empty($ciudad) || empty($departamento)) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Faltan datos: ciudad o departamento"
    ]);
    exit;
}

// Buscar en la tabla precios_envio
$sql = "SELECT precio_base, dias_estimados 
        FROM precios_envio 
        WHERE ciudad_origen = ? AND ciudad_destino = ? AND departamento_destino = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $origen, $ciudad, $departamento);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    echo json_encode([
        "status" => "ok",
        "precio" => $data['precio_base'],
        "dias_estimados" => $data['dias_estimados']
    ]);
} else {
    echo json_encode([
        "status" => "no_disponible",
        "mensaje" => "No hay precio para esa ciudad todavía"
    ]);
}