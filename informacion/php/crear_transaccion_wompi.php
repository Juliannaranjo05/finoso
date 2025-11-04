<?php
// Crear una transacción con Wompi
session_start();

$id_usuario = $_SESSION['id_usuario'] ?? null;
$correoSesion = $_SESSION['correo'] ?? null;

// Si no hay sesión activa, crear un usuario temporal
if (!$id_usuario) {
    $id_usuario = 0; // Usuario invitado
}

include 'wompi_config.php';
include 'conexion.php';

$LOG_FILE = __DIR__ . '/../../logs/wompi_flow.log';
if (!file_exists(dirname($LOG_FILE))) {
    @mkdir(dirname($LOG_FILE), 0775, true);
}

function wompi_log($message) {
    global $LOG_FILE;
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message\n", 3, $LOG_FILE);
}

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

wompi_log('=== ➡️ crear_transaccion_wompi.php INICIO ===');
wompi_log(sprintf('Datos crudos POST: id_reloj=%d, costo_envio=%d, usuario=%s', $id_reloj, $costo_envio, $id_usuario ?: '0'));

$nombre = trim($input['nombre'] ?? '');
$cedula = trim($input['cedula'] ?? '');
$celular = trim($input['celular'] ?? '');
$departamento = trim($input['departamento'] ?? '');
$ciudad = trim($input['ciudad'] ?? '');
$direccion = trim($input['direccion'] ?? '');
$barrio = trim($input['barrio'] ?? '');
$referencias = trim($input['referencias'] ?? '');
$correoInput = trim($input['correo'] ?? '');
$correo = $correoSesion ?: $correoInput;
$transactionInProgress = false;

// Validación básica
if (!$id_reloj) {
    wompi_log('❌ ID de reloj no proporcionado');
    echo json_encode(['error' => 'ID de reloj no proporcionado']);
    exit;
}

if ($costo_envio <= 0) {
    wompi_log('❌ Costo de envío no válido');
    echo json_encode(['error' => 'Costo de envío no válido']);
    exit;
}

// Buscar reloj en la base de datos
$sql = "SELECT * FROM reloj WHERE id_reloj = $id_reloj AND vendido = 0";
$resultado = mysqli_query($conn, $sql);

if (!$resultado || mysqli_num_rows($resultado) === 0) {
    wompi_log(sprintf('❌ Reloj %d no encontrado o ya vendido', $id_reloj));
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
        wompi_log('❌ WOMPI_PUBLIC_KEY vacío');
        echo json_encode(['error' => 'Llave pública vacía o no definida en wompi_config.php']);
        exit;
    }

    // Guardar la orden en la base de datos antes de redirigir a Wompi
    mysqli_begin_transaction($conn);
    $transactionInProgress = true;

    $estado_inicial = 'pendiente';
    $metodo_pago = 'wompi';
    $total_decimal = round(floatval($total_con_envio), 2);
    $costo_envio_decimal = round(floatval($costo_envio), 2);
    $id_usuario_db = ($id_usuario && $id_usuario > 0) ? intval($id_usuario) : null;

    // Evitar referencias duplicadas (muy poco probable, pero seguro)
    $stmtCheck = $conn->prepare("SELECT id_orden FROM orden WHERE token_verificacion = ? LIMIT 1");
    $stmtCheck->bind_param("s", $referencia);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    if ($stmtCheck->num_rows > 0) {
        $stmtCheck->close();
        mysqli_rollback($conn);
        $transactionInProgress = false;
        wompi_log(sprintf('❌ Referencia duplicada detectada (%s)', $referencia));
        echo json_encode(['error' => 'Referencia de orden duplicada, intenta de nuevo.']);
        exit;
    }
    $stmtCheck->close();

    $stmtOrden = $conn->prepare("INSERT INTO orden (id_usuario, nombre, correo, cedula, celular, departamento, ciudad, direccion, barrio, referencias, total, metodo_pago, costo_envio, estado, token_verificacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmtOrden) {
        mysqli_rollback($conn);
        $transactionInProgress = false;
        wompi_log('❌ No fue posible preparar la inserción de la orden: ' . $conn->error);
        echo json_encode(['error' => 'No fue posible preparar la inserción de la orden.']);
        exit;
    }

    $stmtOrden->bind_param(
        'isssssssssdsdss',
        $id_usuario_db,
        $nombre,
        $correo,
        $cedula,
        $celular,
        $departamento,
        $ciudad,
        $direccion,
        $barrio,
        $referencias,
        $total_decimal,
        $metodo_pago,
        $costo_envio_decimal,
        $estado_inicial,
        $referencia
    );

    if (!$stmtOrden->execute()) {
        $stmtOrden->close();
        mysqli_rollback($conn);
        $transactionInProgress = false;
        wompi_log('❌ Error al guardar orden: ' . $stmtOrden->error);
        echo json_encode(['error' => 'No fue posible guardar la orden antes de redirigir a Wompi.']);
        exit;
    }

    $id_orden = $stmtOrden->insert_id;
    $stmtOrden->close();

    $precio_unitario = round(floatval($precio_final), 2);

    $stmtDetalle = $conn->prepare("INSERT INTO orden_detalle (id_orden, id_reloj, precio_unitario) VALUES (?, ?, ?)");
    if (!$stmtDetalle) {
        mysqli_rollback($conn);
        $transactionInProgress = false;
        wompi_log('❌ No fue posible preparar el detalle de la orden: ' . $conn->error);
        echo json_encode(['error' => 'No fue posible preparar el detalle de la orden.']);
        exit;
    }

    $stmtDetalle->bind_param('iid', $id_orden, $id_reloj, $precio_unitario);

    if (!$stmtDetalle->execute()) {
        $stmtDetalle->close();
        mysqli_rollback($conn);
        $transactionInProgress = false;
        wompi_log('❌ Error al guardar detalle: ' . $stmtDetalle->error);
        echo json_encode(['error' => 'No fue posible guardar el detalle de la orden.']);
        exit;
    }

    $stmtDetalle->close();

    mysqli_commit($conn);
    $transactionInProgress = false;

    wompi_log(sprintf('✅ Orden #%d creada (ref=%s, total=%s, costo_envio=%s, usuario=%s)', $id_orden, $referencia, number_format($total_decimal, 2, '.', ''), number_format($costo_envio_decimal, 2, '.', ''), $id_usuario_db !== null ? $id_usuario_db : 'NULL'));

    wompi_log(sprintf('Detalle guardado: reloj=%d, precio_unitario=%s', $id_reloj, number_format($precio_unitario, 2, '.', '')));

    // Generar firma de integridad para producción
    // Formato correcto: referencia + amount_in_cents + currency + events_secret
    $signature_string = $referencia . $amount_in_cents . 'COP' . WOMPI_EVENTS_SECRET;
    $signature = hash('sha256', $signature_string);
    
    // Debug de la firma
    wompi_log('Signature String: ' . $signature_string);
    wompi_log('Signature Hash: ' . $signature);
    
    // Usar checkout directo de Wompi (/p/) con firma de integridad
    $vpos_url = 'https://checkout.wompi.co/p/?' . http_build_query([
        'public-key' => WOMPI_PUBLIC_KEY,
        'currency' => 'COP',
        'amount-in-cents' => $amount_in_cents,
        'reference' => $referencia,
        'signature:integrity' => $signature,
        'redirect-url' => WOMPI_REDIRECT_URL
    ]);
    
    wompi_log('=== WOMPI CHECKOUT DIRECTO ===');
    wompi_log('Producto: ' . $product_brand . ' ' . $product_name);
    wompi_log('Total COP: ' . number_format($total_con_envio));
    wompi_log('VPOS URL: ' . $vpos_url);
    wompi_log('Amount in cents: ' . $amount_in_cents);
    wompi_log('Reference: ' . $referencia);
    
    // Guardar datos del formulario en sesión para usar después del pago
    $_SESSION['wompi_transaction_data'] = [
        'id_usuario' => $id_usuario,
        'id_orden' => $id_orden,
        'id_reloj' => $id_reloj,
        'precio_reloj' => $precio_final,
        'costo_envio' => $costo_envio,
        'total' => $total_con_envio,
        'referencia' => $referencia,
        'datos_cliente' => [
            'nombre' => $nombre,
            'cedula' => $cedula,
            'celular' => $celular,
            'departamento' => $departamento,
            'ciudad' => $ciudad,
            'direccion' => $direccion,
            'barrio' => $barrio,
            'referencias' => $referencias,
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
    if ($transactionInProgress) {
        mysqli_rollback($conn);
        $transactionInProgress = false;
    }
    wompi_log('❌ Excepción no controlada: ' . $e->getMessage());
    echo json_encode([
        'error' => 'Error al crear transacción',
        'message' => $e->getMessage()
    ]);
}
wompi_log('=== ⬅️ crear_transaccion_wompi.php FIN ===');
?>




