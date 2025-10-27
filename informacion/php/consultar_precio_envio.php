<?php
include 'conexion.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$ciudad = $_GET['ciudad'] ?? '';
$departamento = $_GET['departamento'] ?? '';

// Log para debug
error_log("🔍 Buscando envío - Ciudad: '$ciudad' | Departamento: '$departamento'");

// Validar entrada
if (empty($ciudad) || empty($departamento)) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Faltan datos: ciudad o departamento"
    ]);
    exit;
}

// Buscar en la nueva tabla envios con LIKE para evitar problemas de UTF-8
$sql = "SELECT precio, dias_estimados, ciudad, departamento
        FROM envios 
        WHERE ciudad LIKE ? AND departamento LIKE ? AND activo = 1
        LIMIT 1";

$stmt = $conn->prepare($sql);
$ciudad_like = "%$ciudad%";
$departamento_like = "%$departamento%";
$stmt->bind_param("ss", $ciudad_like, $departamento_like);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    error_log("✅ Envío encontrado - Precio: {$data['precio']} | Días: {$data['dias_estimados']}");
    echo json_encode([
        "status" => "ok",
        "precio" => floatval($data['precio']),
        "dias_estimados" => intval($data['dias_estimados']),
        "ciudad_encontrada" => $data['ciudad'],
        "departamento_encontrado" => $data['departamento']
    ], JSON_INVALID_UTF8_SUBSTITUTE);
} else {
    error_log("❌ No se encontró envío para '$ciudad' en '$departamento'");
    
    // Buscar ciudades similares para debug
    $debug_sql = "SELECT ciudad FROM envios WHERE departamento LIKE ? LIMIT 5";
    $debug_stmt = $conn->prepare($debug_sql);
    $debug_stmt->bind_param("s", $departamento_like);
    $debug_stmt->execute();
    $debug_result = $debug_stmt->get_result();
    $ciudades_disponibles = [];
    while ($row = $debug_result->fetch_assoc()) {
        $ciudades_disponibles[] = $row['ciudad'];
    }
    
    echo json_encode([
        "status" => "no_disponible",
        "mensaje" => "No hay precio para esa ciudad todavía",
        "debug_ciudades_disponibles" => $ciudades_disponibles
    ], JSON_INVALID_UTF8_SUBSTITUTE);
}

$stmt->close();
$conn->close();
?>