<?php  
require __DIR__ . '/../../vendor/autoload.php';  
include 'conexion.php';  
header('Content-Type: application/json');   

// Leer los datos enviados desde JS
$input = json_decode(file_get_contents('php://input'), true);
$id_reloj = intval($input['id_reloj'] ?? 0);
$costo_envio = floatval($input['costo_envio'] ?? 0);
$id_usuario = isset($input['id_usuario']) ? intval($input['id_usuario']) : null;

// Validar que el ID sea válido
if ($id_reloj <= 0) {
    echo json_encode(['error' => true, 'message' => 'ID de reloj inválido']);
    exit;
}

// Obtener información del reloj (SIN aplicar descuento aquí)
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

// Corregir precio si está en formato incorrecto
if ($precio_original < 1000 && $precio_original > 0) {
    $precio_original = $precio_original * 1000;
}

// 🔥 NO APLICAR DESCUENTO AQUÍ - solo enviar precio original
$marca = $row['marca'];
$img = 'http://127.0.0.1/finoso/' . ltrim($row['img'], '/');

// Respuesta con precio original
$response = [
    'success' => true,
    'precio_original' => $precio_original,
    'precio_reloj' => $precio_original, // Para compatibilidad
    'descuento_bd_disponible' => $descuento_bd,
    'costo_envio' => $costo_envio,
    'nombre_reloj' => $nombre_reloj,
    'marca' => $marca,
    'img' => $img,
    'datos_cliente' => [
        'metodo_pago' => $input['metodo_pago'],
        'nombre' => $input['nombre'],
        'cedula' => $input['cedula'],
        'celular' => $input['celular'],
        'correo' => $input['correo'] ?? null,
        'id_usuario' => $id_usuario,
        'departamento' => $input['departamento'],
        'ciudad' => $input['ciudad'],
        'direccion' => $input['direccion'],
        'barrio' => $input['barrio'],
        'referencias' => $input['referencias']
    ]
];

// Enviar la respuesta al frontend
echo json_encode($response);
?>
