<?php  
require __DIR__ . '/../../vendor/autoload.php';  
include 'conexion.php';  
header('Content-Type: application/json');   

// 🧩 Leer datos desde el frontend
$input = json_decode(file_get_contents('php://input'), true);
$id_reloj = intval($input['id_reloj'] ?? 0);
$id_usuario = isset($input['id_usuario']) ? intval($input['id_usuario']) : null;

// 🔎 Depuración inicial
error_log("📩 Datos recibidos desde frontend: " . json_encode($input, JSON_PRETTY_PRINT));

// ✅ Validar que el ID sea válido
if ($id_reloj <= 0) {
    echo json_encode(['error' => true, 'message' => 'ID de reloj inválido']);
    exit;
}

// 🕐 Obtener información del reloj
$stmt = $conn->prepare("SELECT nombre, precio, marca, img, descuento FROM reloj WHERE id_reloj = ?");
$stmt->bind_param("i", $id_reloj);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => true, 'message' => 'Reloj no encontrado']);
    exit;
}

$row = $result->fetch_assoc();
$nombre_reloj = $row['nombre'];
$precio_original = floatval($row['precio']);
$descuento_bd = floatval($row['descuento']);
$marca = $row['marca'];
$img = 'https://finoso.store/' . ltrim($row['img'], '/');

// 🔎 Depuración de precio antes de corrección
error_log("💰 Precio original leído de BD: {$precio_original}");

// 🧮 El precio ya viene correcto del frontend (125000 = $125.000)
// No necesitamos multiplicar nada

// 🚚 Calcular costo de envío según ubicación
$departamento = $input['departamento'] ?? '';
$ciudad = $input['ciudad'] ?? '';
$costo_envio = 0;

$stmt_envio = $conn->prepare("SELECT precio FROM envios WHERE departamento = ? AND ciudad = ? AND activo = 1 LIMIT 1");
$stmt_envio->bind_param("ss", $departamento, $ciudad);
$stmt_envio->execute();
$res_envio = $stmt_envio->get_result();

if ($res_envio->num_rows > 0) {
    $fila_envio = $res_envio->fetch_assoc();
    $costo_envio = floatval($fila_envio['precio']);
    error_log("🚛 Costo de envío encontrado en BD: {$costo_envio}");
} else {
    $costo_envio = 12000;
    error_log("🚛 No se encontró tarifa — usando valor por defecto: {$costo_envio}");
}

// 🧮 Total preliminar (sin descuento, ya que no se aplica aquí)
$total_estimado = $precio_original + $costo_envio;

// 🔎 Depuración de valores finales
error_log("📊 RESUMEN DE CÁLCULOS:");
error_log("   🕒 Nombre: {$nombre_reloj}");
error_log("   💸 Precio original: {$precio_original}");
error_log("   🚚 Envío: {$costo_envio}");
error_log("   💰 Total estimado: {$total_estimado}");

// 🧾 Preparar respuesta
$response = [
    'success' => true,
    'precio_original' => $precio_original,
    'precio_reloj' => $precio_original,
    'descuento_bd_disponible' => $descuento_bd,
    'costo_envio' => $costo_envio,
    'nombre_reloj' => $nombre_reloj,
    'marca' => $marca,
    'img' => $img,
    'total_estimado' => $total_estimado,
    'datos_cliente' => [
        'metodo_pago' => $input['metodo_pago'] ?? '',
        'nombre' => $input['nombre'] ?? '',
        'cedula' => $input['cedula'] ?? '',
        'celular' => $input['celular'] ?? '',
        'correo' => $input['correo'] ?? null,
        'id_usuario' => $id_usuario,
        'departamento' => $departamento,
        'ciudad' => $ciudad,
        'direccion' => $input['direccion'] ?? '',
        'barrio' => $input['barrio'] ?? '',
        'referencias' => $input['referencias'] ?? ''
    ]
];

// 🧠 Depuración final antes de enviar al frontend
error_log("📤 Respuesta enviada al frontend: " . json_encode($response, JSON_PRETTY_PRINT));

// 🔽 Enviar al frontend
echo json_encode($response);
?>