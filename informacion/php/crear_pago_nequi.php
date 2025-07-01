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

// Obtener precio, descuento y más info desde la BD
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
$precio_reloj = floatval($row['precio']);
$descuento = floatval($row['descuento']); // Obtener descuento desde la BD

// Corregir precio si está en formato incorrecto
if ($precio_reloj < 1000 && $precio_reloj > 0) {
    $precio_reloj = $precio_reloj * 1000;
}

// Aplicar descuento solo si existe (descuento > 0)
if ($descuento > 0) {
    $precio_reloj = $precio_reloj * (1 - $descuento);  // Aplicar descuento
}

// Redondear el precio al múltiplo de 1000 más cercano
$resto = $precio_reloj % 1000; // Restante al dividir entre 1000

if ($resto >= 500) {
    $precio_reloj = ceil($precio_reloj / 1000) * 1000; // Redondeo hacia arriba al siguiente múltiplo de 1000
} else {
    $precio_reloj = floor($precio_reloj / 1000) * 1000; // Redondeo hacia abajo al múltiplo anterior de 1000
}

// Calcular el total con el costo de envío
$total = $precio_reloj + $costo_envio;

$marca = $row['marca'];
$img = 'http://127.0.0.1/finoso/' . ltrim($row['img'], '/');

// Respuesta con todos los datos necesarios
$response = [
    'success' => true,
    'precio_reloj' => $precio_reloj, // Este es el precio con descuento si aplica y redondeado
    'costo_envio' => $costo_envio,
    'total' => $total,
    'nombre_reloj' => $nombre_reloj,
    'marca' => $marca,
    'img' => $img,
    // Datos del formulario para usar después
    'datos_cliente' => [
        'metodo_pago' => $input['metodo_pago'],
        'nombre' => $input['nombre'],
        'cedula' => $input['cedula'],
        'celular' => $input['celular'],
        'correo' => $input['correo'] ?? null,
        'id_usuario' => $id_usuario, // INCLUIR EL ID_USUARIO AQUÍ
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
