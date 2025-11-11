<?php 
require __DIR__ . '/../../vendor/autoload.php'; 
include 'conexion.php'; 
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$productos = $input['productos'] ?? [];
$costo_envio = floatval($input['costo_envio'] ?? 0);
$id_usuario = isset($input['id_usuario']) ? intval($input['id_usuario']) : null;

if (!is_array($productos) || count($productos) === 0) {
    echo json_encode(['error' => true, 'message' => 'Carrito vacío o mal formado']);
    exit;
}

$subtotal = 0;
error_log("🔄 INICIO - Subtotal inicial: $subtotal");

$productos_validos = [];
$errores = [];

foreach ($productos as $producto) {
    $id = intval($producto['id'] ?? 0);
    $cantidad = intval($producto['cantidad'] ?? 1);

    if ($id <= 0) {
        $errores[] = "ID inválido en producto";
        continue;
    }

    $stmt = $conn->prepare("SELECT nombre, precio, marca, img, descuento FROM reloj WHERE id_reloj = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $errores[] = "Producto con ID $id no encontrado";
        continue;
    }

    $row = $result->fetch_assoc();

    error_log("🗄️ ID $id - Precio BD: '{$row['precio']}', Descuento BD: '{$row['descuento']}'");

    // ✅ Procesar precio: usar directamente el valor de la BD
    $precio = floatval($row['precio']);
    
    error_log("🔍 DEBUG crear_pago_nequi.php - ID: $id");
    error_log("🔍 DEBUG crear_pago_nequi.php - Precio BD (raw): " . $row['precio']);
    error_log("🔍 DEBUG crear_pago_nequi.php - Precio BD (floatval): $precio");

    error_log("💰 ID $id - Precio procesado: $precio");

    // ✅ Aplicar descuento si existe
    $descuento = floatval($row['descuento'] ?? 0);
    if ($descuento > 0) {
        $precio_antes_descuento = $precio;
        $precio *= (1 - $descuento);
        error_log("🏷️ ID $id - Descuento {$descuento}% aplicado: $precio_antes_descuento → $precio");
    }

    // ✅ No redondear, usar precio exacto
    error_log("💰 ID $id - Precio final: $precio");

    // ✅ Sumar al subtotal
    $subtotal_antes = $subtotal;
    $subtotal += $precio * $cantidad;
    error_log("🔍 DEBUG crear_pago_nequi.php - Sumando: $precio × $cantidad = " . ($precio * $cantidad));
    error_log("🔍 DEBUG crear_pago_nequi.php - Subtotal antes: $subtotal_antes");
    error_log("🔍 DEBUG crear_pago_nequi.php - Subtotal después: $subtotal");
    error_log("➕ ID $id - Subtotal: $subtotal_antes + ($precio × $cantidad) = $subtotal");

    $productos_validos[] = [
        'id' => $id,
        'nombre' => $row['nombre'],
        'marca' => $row['marca'],
        'img' => 'https://finoso.store/' . ltrim($row['img'], '/'),
        'precio' => $precio,
        'cantidad' => $cantidad
    ];
}

error_log("🏁 FINAL - Subtotal: $subtotal, Costo envío: $costo_envio");

// Total final con envío
$total = $subtotal + $costo_envio;
error_log("🔍 DEBUG crear_pago_nequi.php - Subtotal final: $subtotal");
error_log("🔍 DEBUG crear_pago_nequi.php - Costo envío: $costo_envio");
error_log("🔍 DEBUG crear_pago_nequi.php - Total final: $total");

// Estructura de respuesta
$response = [
    'success' => true,
    'productos' => $productos_validos,
    'subtotal' => $subtotal,
    'costo_envio' => $costo_envio,
    'total' => $total,
    'errores' => $errores,
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

echo json_encode($response);
?>