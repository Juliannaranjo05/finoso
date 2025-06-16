<?php
require __DIR__ . '/../../vendor/autoload.php';
include 'conexion.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

header('Content-Type: application/json');

// Leer el cuerpo del POST
$input = json_decode(file_get_contents('php://input'), true);
$id_reloj = intval($input['id_reloj'] ?? 0);
$costo_envio = intval($input['costo_envio'] ?? 0); // NUEVO: recibir costo de envío

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
$sql = "SELECT * FROM reloj WHERE id_reloj = $id_reloj";
$resultado = mysqli_query($conn, $sql);

if (!$resultado || mysqli_num_rows($resultado) === 0) {
    echo json_encode(['error' => 'Reloj no encontrado']);
    exit;
}

$reloj = mysqli_fetch_assoc($resultado);

try {
    MercadoPagoConfig::setAccessToken('APP_USR-8081700083482823-052513-4b51160e6045855a3b6372cc0c14e686-2456154307');    
    $client = new PreferenceClient();

    $precio_original = floatval($reloj['precio']); // Ejemplo: 135.00 (que representa $135.000 COP)
    $descuento = floatval($reloj['descuento']);    // Ejemplo: 0.11 (11%)

    // --- Paso 1: Convertir el precio abreviado a su valor real (multiplicar por 1000) ---
    $precio_real = $precio_original * 1000; // 135.00 → 135000.00

    // --- Paso 2: Aplicar descuento solo si existe ---
    $precio_con_descuento = ($descuento > 0) ? $precio_real * (1 - $descuento) : $precio_real;

    // --- Paso 3: Redondear a miles (120150 → 120000, 120550 → 121000) ---
    $precio_redondeado = round($precio_con_descuento / 1000) * 1000;

    // --- Paso 4: Para COP no se multiplica por 100, se envía el valor entero ---
    $precio_final = intval($precio_redondeado); // 120000 (SIN multiplicar por 100)

    // --- ENVÍO DINÁMICO: usar el valor recibido del frontend ---
    $total_con_envio = $precio_final + $costo_envio;

    // --- Depuración (registra todos los valores) ---
    error_log("Datos desde BD - Precio: {$reloj['precio']}, Descuento: {$reloj['descuento']}");
    error_log("Precio real (x1000): " . $precio_real);
    error_log("Precio con descuento: " . $precio_con_descuento);
    error_log("Precio redondeado: " . $precio_redondeado);
    error_log("Precio final para MercadoPago: " . $precio_final);
    error_log("Costo de envío recibido: " . $costo_envio);
    error_log("Total con envío: " . $total_con_envio);

    // --- Validación final ---
    if ($precio_final <= 0) {
        echo json_encode([
            'error' => 'El precio final debe ser mayor que cero',
            'debug' => [
                'precio_bd' => $reloj['precio'],
                'precio_real' => $precio_real,
                'precio_con_descuento' => $precio_con_descuento,
                'precio_redondeado' => $precio_redondeado,
                'precio_final' => $precio_final,
                'costo_envio' => $costo_envio,
                'total_con_envio' => $total_con_envio
            ]
        ]);
        exit;
    }

    $preference_data = [
        "items" => [
            [
                "title" => $reloj['nombre'],
                "quantity" => 1,
                "unit_price" => $precio_final, // Solo el precio del producto
                "currency_id" => "COP"
            ]
        ],
        "shipments" => [
            "cost" => $costo_envio,
            "mode" => "not_specified" // modo sin cálculo automático
        ],
        "back_urls" => [
            "success" => "https://finoso.store/feedback.php?status=success",
            "failure" => "https://finoso.store/feedback.php?status=failure",
            "pending" => "https://finoso.store/feedback.php?status=pending"
        ],
        "auto_return" => "approved",
        "external_reference" => "reloj_" . $id_reloj
    ];

    $preference = $client->create($preference_data);

    echo json_encode([
        'success' => true,
        'init_point' => $preference->init_point,
        'preference_id' => $preference->id,
        'precio_reloj' => $precio_final,
        'costo_envio' => $costo_envio,
        'total' => $total_con_envio
    ]);

} catch (\MercadoPago\Exceptions\MPApiException $e) {
    $apiResponse = $e->getApiResponse();
    $responseContent = $apiResponse->getContent() ?? [];
    
    echo json_encode([
        'error' => 'Error de API',
        'message' => $e->getMessage(),
        'status_code' => $apiResponse->getStatusCode(),
        'response' => $responseContent
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Excepción general',
        'message' => $e->getMessage()
    ]);
}
?>