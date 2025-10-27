<?php
// Crear una transacción con Wompi para carrito
session_start();

$id_usuario = $_SESSION['id_usuario'] ?? null;
$correo = $_SESSION['correo'] ?? null;

// Si no hay sesión activa, crear un usuario temporal
if (!$id_usuario) {
    $id_usuario = 0; // Usuario invitado
}

include '../../informacion/php/wompi_config.php';
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
$productos = $input['productos'] ?? [];
$costo_envio = intval($input['costo_envio'] ?? 0);

// Validación básica
if (empty($productos)) {
    echo json_encode(['error' => 'No hay productos en el carrito']);
    exit;
}

if ($costo_envio <= 0) {
    echo json_encode(['error' => 'Costo de envío no válido']);
    exit;
}

try {
    // Calcular totales
    $total_relojes = 0;
    $productos_validos = [];
    
    foreach ($productos as $producto) {
        $id_reloj = intval($producto['id_reloj']);
        
        // Verificar que el reloj existe y no está vendido
        $sql = "SELECT * FROM reloj WHERE id_reloj = $id_reloj AND vendido = 0";
        $resultado = mysqli_query($conn, $sql);
        
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            $reloj = mysqli_fetch_assoc($resultado);
            
            // Calcular precio con descuento
            $precio_original = floatval($reloj['precio']);
            $descuento = floatval($reloj['descuento']);
            $precio_con_descuento = ($descuento > 0) ? $precio_original * (1 - $descuento) : $precio_original;
            $precio_final = intval($precio_con_descuento);
            
            $total_relojes += $precio_final;
            $productos_validos[] = [
                'id_reloj' => $id_reloj,
                'precio' => $precio_final,
                'reloj' => $reloj
            ];
        }
    }
    
    if (empty($productos_validos)) {
        echo json_encode(['error' => 'No hay productos válidos en el carrito']);
        exit;
    }
    
    $total_con_envio = $total_relojes + $costo_envio;
    
    // Generar referencia única para la transacción
    $referencia = 'FINOSO_CARRITO_' . time() . '_' . count($productos_validos);
    
    // Usar VPOS directo para el carrito (más confiable)
    $amount_in_cents = $total_con_envio * 100;
    
    // Simplificado - solo datos básicos del carrito
    $carrito_items_count = count($productos_validos);
    
    // Generar firma de integridad para producción
    // Formato correcto: referencia + amount_in_cents + currency + events_secret
    $signature_string = $referencia . $amount_in_cents . 'COP' . WOMPI_EVENTS_SECRET;
    $signature = hash('sha256', $signature_string);
    
    // Debug de la firma
    error_log("DEBUG - Carrito Signature String: " . $signature_string);
    error_log("DEBUG - Carrito Generated Signature: " . $signature);
    
    // Usar checkout directo de Wompi (/p/) con firma de integridad
    $vpos_url = 'https://checkout.wompi.co/p/?' . http_build_query([
        'public-key' => WOMPI_PUBLIC_KEY,
        'currency' => 'COP',
        'amount-in-cents' => $amount_in_cents,
        'reference' => $referencia,
        'signature:integrity' => $signature,
        'redirect-url' => WOMPI_REDIRECT_URL_CARRITO
    ]);
    
    error_log("=== WOMPI CARRITO CHECKOUT DIRECTO ===");
    error_log("Carrito: " . $carrito_items_count . " reloj(es)");
    error_log("Amount: $" . number_format($total_con_envio) . " COP");
    error_log("Checkout URL: " . $vpos_url);
    
    error_log("=== WOMPI VPOS URL CARRITO DEBUG ===");
    error_log("VPOS URL: " . $vpos_url);
    error_log("Amount in cents: " . $amount_in_cents);
    error_log("Reference: " . $referencia);
    
    // Guardar datos del formulario en sesión para usar después del pago
    $_SESSION['wompi_carrito_data'] = [
        'id_usuario' => $id_usuario,
        'productos' => $productos_validos,
        'total_relojes' => $total_relojes,
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
