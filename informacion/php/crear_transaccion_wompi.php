<?php
// Crear una transacción con Wompi
session_start();

$id_usuario = $_SESSION['id_usuario'] ?? null;
$correo = $_SESSION['correo'] ?? null;

// Si no hay sesión activa, crear un usuario temporal
if (!$id_usuario) {
    $id_usuario = 0; // Usuario invitado
}

include 'wompi_config.php';
include 'conexion.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Leer el cuerpo del POST
$input = json_decode(file_get_contents('php://input'), true);
$id_reloj = intval($input['id_reloj'] ?? 0);
$costo_envio = intval($input['costo_envio'] ?? 0);

// Validación básica
if (!$id_reloj) {
    echo json_encode(['error' => 'ID de reloj no proporcionado']);
    exit;
}

if ($costo_envio <= 0) {
    echo json_encode(['error' => 'Costo de envío no válido']);
    exit;
}

// Buscar reloj en la base de datos
$sql = "SELECT * FROM reloj WHERE id_reloj = $id_reloj AND vendido = 0";
$resultado = mysqli_query($conn, $sql);

if (!$resultado || mysqli_num_rows($resultado) === 0) {
    echo json_encode(['error' => 'Reloj no encontrado o ya vendido']);
    exit;
}

$reloj = mysqli_fetch_assoc($resultado);

try {
    // Calcular precios
    $precio_original = floatval($reloj['precio']);
    $descuento = floatval($reloj['descuento']);
    
    // Aplicar descuento si existe
    $precio_con_descuento = ($descuento > 0) ? $precio_original * (1 - $descuento) : $precio_original;
    $precio_final = intval($precio_con_descuento);
    $total_con_envio = $precio_final + $costo_envio;

    // Generar referencia única para la transacción
    $referencia = 'FINOSO_' . time() . '_' . $id_reloj;
    
    // Usar VPOS directo (más confiable que la API de transacciones)
    $amount_in_cents = $total_con_envio * 100;
    
    // Simplificado - solo datos básicos del producto
    $product_name = $reloj['nombre'];
    $product_brand = $reloj['marca'];
    
    // Verificar que la llave pública esté definida
    if (empty(WOMPI_PUBLIC_KEY)) {
        echo json_encode(['error' => 'Llave pública vacía o no definida en wompi_config.php']);
        exit;
    }
    
    error_log("DEBUG - PUBLIC KEY: " . WOMPI_PUBLIC_KEY);
    error_log("DEBUG - PUBLIC KEY LENGTH: " . strlen(WOMPI_PUBLIC_KEY));
    
    // Generar firma de integridad para producción
    // Formato correcto: referencia + amount_in_cents + currency + events_secret
    $signature_string = $referencia . $amount_in_cents . 'COP' . WOMPI_EVENTS_SECRET;
    $signature = hash('sha256', $signature_string);
    
    // Debug de la firma
    error_log("DEBUG - Signature String: " . $signature_string);
    error_log("DEBUG - Generated Signature: " . $signature);
    
    // Usar checkout directo de Wompi (/p/) con firma de integridad
    $vpos_url = 'https://checkout.wompi.co/p/?' . http_build_query([
        'public-key' => WOMPI_PUBLIC_KEY,
        'currency' => 'COP',
        'amount-in-cents' => $amount_in_cents,
        'reference' => $referencia,
        'signature:integrity' => $signature,
        'redirect-url' => WOMPI_REDIRECT_URL
    ]);
    
    error_log("=== WOMPI CHECKOUT DIRECTO ===");
    error_log("Product: " . $product_brand . " " . $product_name);
    error_log("Amount: $" . number_format($total_con_envio) . " COP");
    error_log("Checkout URL: " . $vpos_url);
    
    error_log("=== WOMPI VPOS URL DEBUG ===");
    error_log("VPOS URL: " . $vpos_url);
    error_log("Amount in cents: " . $amount_in_cents);
    error_log("Reference: " . $referencia);

    // Guardar datos del formulario en sesión para usar después del pago
    $_SESSION['wompi_transaction_data'] = [
        'id_usuario' => $id_usuario,
        'id_reloj' => $id_reloj,
        'precio_reloj' => $precio_final,
        'costo_envio' => $costo_envio,
        'total' => $total_con_envio,
        'referencia' => $referencia,
        'datos_cliente' => [
            'nombre' => $input['nombre'] ?? '',
            'cedula' => $input['cedula'] ?? '',
            'celular' => $input['celular'] ?? '',
            'departamento' => $input['departamento'] ?? '',
            'ciudad' => $input['ciudad'] ?? '',
            'direccion' => $input['direccion'] ?? '',
            'barrio' => $input['barrio'] ?? '',
            'referencias' => $input['referencias'] ?? '',
            'metodo_pago' => 'wompi'
        ]
    ];

    // Respuesta para el frontend
    echo json_encode([
        'success' => true,
        'reference' => $referencia,
        'amount' => $total_con_envio,
        'currency' => 'COP',
        'vpos_url' => $vpos_url,
        'public_key' => WOMPI_PUBLIC_KEY
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error al crear transacción',
        'message' => $e->getMessage()
    ]);
}
?>




