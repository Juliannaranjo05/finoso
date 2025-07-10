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

    // ✅ Procesar precio: formato 12.000 o 125.00
    $precio_str = $row['precio'];
    
    // Si el precio tiene formato 12.000 (punto como separador de miles)
    if (substr_count($precio_str, '.') == 1 && strlen($precio_str) - strrpos($precio_str, '.') == 4) {
        // Formato: 12.000 (miles con punto, sin decimales)
        $precio_limpio = str_replace('.', '', $precio_str) . '000'; // 12000 → 12000000
        $precio = floatval($precio_limpio);
    } 
    // Si el precio tiene formato 125.00 (punto como decimal)
    else if (substr_count($precio_str, '.') == 1 && strlen($precio_str) - strrpos($precio_str, '.') <= 3) {
        // Formato: 125.00 (decimales con punto)
        $precio = floatval($precio_str) * 1000; // 125.00 → 125000
    }
    // Si no hay punto, asumir que son miles
    else {
        $precio = floatval($precio_str) * 1000;
    }

    error_log("💰 ID $id - Precio procesado: $precio");

    // ✅ Aplicar descuento si existe
    $descuento = floatval($row['descuento'] ?? 0);
    if ($descuento > 0) {
        $precio_antes_descuento = $precio;
        $precio *= (1 - $descuento);
        error_log("🏷️ ID $id - Descuento {$descuento}% aplicado: $precio_antes_descuento → $precio");
    }

    // ✅ Redondear a múltiplos de 1000
    $precio_antes_redondeo = $precio;
    $resto = $precio % 1000;
    if ($resto >= 500) {
        $precio = ceil($precio / 1000) * 1000;
    } else {
        $precio = floor($precio / 1000) * 1000;
    }
    error_log("🔄 ID $id - Redondeo: $precio_antes_redondeo → $precio (resto: $resto)");

    // ✅ Sumar al subtotal
    $subtotal_antes = $subtotal;
    $subtotal += $precio * $cantidad;
    error_log("➕ ID $id - Subtotal: $subtotal_antes + ($precio × $cantidad) = $subtotal");

    $productos_validos[] = [
        'id' => $id,
        'nombre' => $row['nombre'],
        'marca' => $row['marca'],
        'img' => 'http://127.0.0.1/finoso/' . ltrim($row['img'], '/'),
        'precio' => $precio,
        'cantidad' => $cantidad
    ];
}

error_log("🏁 FINAL - Subtotal: $subtotal, Costo envío: $costo_envio");

// Total final con envío
$total = $subtotal + $costo_envio;

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