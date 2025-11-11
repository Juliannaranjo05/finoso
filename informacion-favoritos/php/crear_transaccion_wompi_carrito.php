<?php
// Crear una transacción con Wompi para favoritos (carrito sin sesión)
session_start();

$id_usuario = $_SESSION['id_usuario'] ?? null;
$correoSesion = $_SESSION['correo'] ?? null;

// Si no hay sesión activa, se maneja como invitado (id_usuario = 0)
if (!$id_usuario) {
    $id_usuario = 0;
}

include '../../informacion/php/wompi_config.php';
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
$productos = $input['productos'] ?? [];
$costo_envio = intval($input['costo_envio'] ?? 0);

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

wompi_log('=== ➡️ crear_transaccion_wompi_carrito (favoritos) INICIO ===');
wompi_log(sprintf('Datos crudos favoritos: productos=%d, costo_envio=%d, usuario=%s', count($productos), $costo_envio, $id_usuario ?: '0'));

// Validación básica
if (empty($productos)) {
    wompi_log('❌ (Favoritos) No hay productos en el carrito');
    echo json_encode(['error' => 'No hay productos en el carrito']);
    exit;
}

if ($costo_envio <= 0) {
    wompi_log('❌ (Favoritos) Costo de envío no válido');
    echo json_encode(['error' => 'Costo de envío no válido']);
    exit;
}

$transactionInProgress = false;

try {
    // Calcular totales
    $total_relojes = 0;
    $productos_validos = [];
    
    foreach ($productos as $producto) {
        $id_reloj = intval($producto['id_reloj']);
        
        $sql = "SELECT * FROM reloj WHERE id_reloj = $id_reloj AND vendido = 0";
        $resultado = mysqli_query($conn, $sql);
        
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            $reloj = mysqli_fetch_assoc($resultado);
            
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
        wompi_log('❌ (Favoritos) No hay productos válidos');
        echo json_encode(['error' => 'No hay productos válidos en el carrito']);
        exit;
    }
    
    $total_con_envio = $total_relojes + $costo_envio;
    
    $referencia = 'FINOSO_FAVORITOS_' . time() . '_' . count($productos_validos);
    $amount_in_cents = $total_con_envio * 100;

    if (empty(WOMPI_PUBLIC_KEY)) {
        wompi_log('❌ (Favoritos) WOMPI_PUBLIC_KEY vacío');
        echo json_encode(['error' => 'Llave pública vacía o no definida en wompi_config.php']);
        exit;
    }

    mysqli_begin_transaction($conn);
    $transactionInProgress = true;

    $estado_inicial = 'pendiente';
    $metodo_pago = 'wompi';
    $total_decimal = round(floatval($total_con_envio), 2);
    $costo_envio_decimal = round(floatval($costo_envio), 2);
    $id_usuario_db = ($id_usuario && $id_usuario > 0) ? intval($id_usuario) : null;

    $stmtCheck = $conn->prepare("SELECT id_orden FROM orden WHERE token_verificacion = ? LIMIT 1");
    $stmtCheck->bind_param("s", $referencia);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    if ($stmtCheck->num_rows > 0) {
        $stmtCheck->close();
        mysqli_rollback($conn);
        $transactionInProgress = false;
        wompi_log(sprintf('❌ (Favoritos) Referencia duplicada detectada %s', $referencia));
        echo json_encode(['error' => 'Referencia de orden duplicada, intenta de nuevo.']);
        exit;
    }
    $stmtCheck->close();

    $stmtOrden = $conn->prepare("INSERT INTO orden (id_usuario, nombre, correo, cedula, celular, departamento, ciudad, direccion, barrio, referencias, total, metodo_pago, costo_envio, estado, token_verificacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmtOrden) {
        mysqli_rollback($conn);
        $transactionInProgress = false;
        wompi_log('❌ (Favoritos) No fue posible preparar la inserción de la orden: ' . $conn->error);
        echo json_encode(['error' => 'No fue posible preparar la orden antes de redirigir a Wompi.']);
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
        wompi_log('❌ (Favoritos) Error al guardar orden: ' . $stmtOrden->error);
        echo json_encode(['error' => 'No fue posible guardar la orden antes de redirigir a Wompi.']);
        exit;
    }

    $id_orden = $stmtOrden->insert_id;
    $stmtOrden->close();

    $stmtDetalle = $conn->prepare("INSERT INTO orden_detalle (id_orden, id_reloj, precio_unitario) VALUES (?, ?, ?)");
    if (!$stmtDetalle) {
        mysqli_rollback($conn);
        $transactionInProgress = false;
        wompi_log('❌ (Favoritos) No fue posible preparar detalle: ' . $conn->error);
        echo json_encode(['error' => 'No fue posible preparar el detalle de la orden.']);
        exit;
    }

    foreach ($productos_validos as $producto_valido) {
        $precio_unitario = round(floatval($producto_valido['precio']), 2);
        $stmtDetalle->bind_param('iid', $id_orden, $producto_valido['id_reloj'], $precio_unitario);
        if (!$stmtDetalle->execute()) {
            $stmtDetalle->close();
            mysqli_rollback($conn);
            $transactionInProgress = false;
            wompi_log('❌ (Favoritos) Error al guardar detalle: ' . $stmtDetalle->error);
            echo json_encode(['error' => 'No fue posible guardar los productos del carrito.']);
            exit;
        }
    }
    $stmtDetalle->close();

    mysqli_commit($conn);
    $transactionInProgress = false;

    wompi_log(sprintf('✅ (Favoritos) Orden #%d creada (ref=%s, total=%s, costo_envio=%s, usuario=%s)', $id_orden, $referencia, number_format($total_decimal, 2, '.', ''), number_format($costo_envio_decimal, 2, '.', ''), $id_usuario_db !== null ? $id_usuario_db : 'NULL'));
    wompi_log(sprintf('(Favoritos) Productos: %s', json_encode($productos_validos)));

    $signature_string = $referencia . $amount_in_cents . 'COP' . WOMPI_EVENTS_SECRET;
    $signature = hash('sha256', $signature_string);

    wompi_log('Signature String (favoritos): ' . $signature_string);
    wompi_log('Signature Hash (favoritos): ' . $signature);

    $vpos_url = 'https://checkout.wompi.co/p/?' . http_build_query([
        'public-key' => WOMPI_PUBLIC_KEY,
        'currency' => 'COP',
        'amount-in-cents' => $amount_in_cents,
        'reference' => $referencia,
        'signature:integrity' => $signature,
        'redirect-url' => WOMPI_REDIRECT_URL_CARRITO
    ]);

    wompi_log('=== WOMPI FAVORITOS CHECKOUT DIRECTO ===');
    wompi_log('Favoritos: ' . count($productos_validos) . ' reloj(es)');
    wompi_log('Total COP: ' . number_format($total_con_envio));
    wompi_log('VPOS URL: ' . $vpos_url);
    wompi_log('Amount in cents: ' . $amount_in_cents);
    wompi_log('Reference: ' . $referencia);

    $_SESSION['wompi_favoritos_data'] = [
        'id_usuario' => $id_usuario,
        'id_orden' => $id_orden,
        'productos' => $productos_validos,
        'total_relojes' => $total_relojes,
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
            'correo' => $correo,
            'metodo_pago' => 'wompi'
        ]
    ];
    session_write_close();

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
    }
    wompi_log('❌ Excepción (favoritos) no controlada: ' . $e->getMessage());
    echo json_encode([
        'error' => 'Error al crear transacción',
        'message' => $e->getMessage()
    ]);
}
wompi_log('=== ⬅️ crear_transaccion_wompi_carrito (favoritos) FIN ===');
?>
