<?php
/**
 * RESPUESTA DE WOMPI - Solo Confirmación Visual
 * 
 * Este archivo NO crea la orden (eso lo hace el webhook).
 * Solo busca la orden ya creada y muestra confirmación al usuario.
 */

session_start();
include 'conexion.php';
include 'wompi_config.php';

$LOG_FILE = __DIR__ . '/../../logs/wompi_flow.log';
if (!file_exists(dirname($LOG_FILE))) {
    @mkdir(dirname($LOG_FILE), 0775, true);
}

function wompi_response_log($message) {
    global $LOG_FILE;
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message\n", 3, $LOG_FILE);
}

wompi_response_log('[WOMPI-RESPONSE] Usuario redirigido desde Wompi');

// Obtener parámetros de la URL
$transaction_id = $_GET['id'] ?? '';
$reference = $_GET['reference'] ?? '';
$status = $_GET['status'] ?? '';
$retry = isset($_GET['retry']) ? max(0, intval($_GET['retry'])) : 0;

wompi_response_log("[WOMPI-RESPONSE] Params - ID: $transaction_id, Ref: $reference, Status: $status");

// Fallback: usar referencia guardada en sesión (generada antes del checkout)
if (empty($reference) && !empty($_SESSION['wompi_transaction_data']['referencia'])) {
    $reference = $_SESSION['wompi_transaction_data']['referencia'];
    wompi_response_log("[WOMPI-RESPONSE] Referencia recuperada desde sesión: $reference");
}

// Fallback adicional: si tenemos transaction_id, consultar a Wompi
if (empty($reference) && !empty($transaction_id)) {
    $transactionUrl = rtrim(getWompiBaseUrl(), '/') . '/transactions/' . urlencode($transaction_id);
    wompi_response_log("[WOMPI-RESPONSE] Buscando referencia via API: $transactionUrl");

    $ch = curl_init($transactionUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . WOMPI_PRIVATE_KEY,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $apiResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($apiResponse && $httpCode === 200) {
        $decoded = json_decode($apiResponse, true);
        $apiReference = $decoded['data']['reference'] ?? '';
        wompi_response_log("[WOMPI-RESPONSE] API reference: $apiReference");
        if (!empty($apiReference)) {
            $reference = $apiReference;
        }
    } else {
        wompi_response_log("[WOMPI-RESPONSE] Error consultando API de Wompi (HTTP $httpCode): $curlError");
    }
}

// Si no hay parámetros, mostrar mensaje genérico
if (empty($reference)) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Procesando Pago - FINOSO</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: radial-gradient(circle at top, #1b1b1b 0%, #000 55%, #050505 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                color: #f5f5f5;
            }
            .container {
                background: linear-gradient(160deg, #0c0c0c 0%, #161616 55%, #111 100%);
                border-radius: 18px;
                padding: 40px;
                max-width: 520px;
                width: 100%;
                box-shadow: 0 25px 70px rgba(0,0,0,0.55);
                text-align: center;
                border: 1px solid rgba(212,175,55,0.35);
            }
            .spinner {
                width: 60px;
                height: 60px;
                border: 5px solid rgba(212,175,55,0.2);
                border-top: 5px solid #d4af37;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 30px;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            h1 {
                color: #f7dfa6;
                margin-bottom: 20px;
                font-size: 28px;
                letter-spacing: 0.5px;
            }
            p {
                color: #d7d7d7;
                font-size: 16px;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            .btn {
                background: linear-gradient(135deg, #f1d27a 0%, #d4af37 100%);
                color: #000;
                padding: 15px 40px;
                border: none;
                border-radius: 50px;
                font-size: 16px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                font-weight: 600;
                box-shadow: 0 10px 25px rgba(212,175,55,0.25);
            }
            .btn:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(212,175,55,0.35); }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="spinner"></div>
            <h1>🎉 ¡Pago Procesado!</h1>
            <p>Tu pago ha sido procesado exitosamente. Estamos preparando tu orden...</p>
            <p><strong>Recibirás un email de confirmación en los próximos minutos.</strong></p>
            <a href="https://finoso.store/" class="btn">Volver al Inicio</a>
        </div>
        <script>
            // Redirigir a inicio después de 5 segundos
            setTimeout(() => {
                window.location.href = 'https://finoso.store/';
            }, 5000);
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Buscar la orden por reference (que es el token_verificacion en el webhook)
$sql = "SELECT o.*, r.nombre as nombre_reloj 
        FROM orden o
        LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
        LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
        WHERE o.token_verificacion = ? 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $reference);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $orden = $result->fetch_assoc();
    $id_orden = $orden['id_orden'];
    $total = $orden['total'];
    $nombre_reloj = $orden['nombre_reloj'] ?? 'tu reloj';
    $token = $orden['token_verificacion'];
    
    wompi_response_log("[WOMPI-RESPONSE] ✅ Orden encontrada: #$id_orden");
    
    // Limpiar datos de sesión
    unset($_SESSION['wompi_transaction_data']);
    
    // Mostrar página de éxito con datos de la orden
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>¡Pago Exitoso! - FINOSO</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: radial-gradient(circle at top, #1b1b1b 0%, #000 55%, #050505 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                color: #f5f5f5;
            }
            .container {
                background: linear-gradient(155deg, #0c0c0c 0%, #161616 60%, #0b0b0b 100%);
                border-radius: 18px;
                padding: 40px;
                max-width: 640px;
                width: 100%;
                box-shadow: 0 30px 80px rgba(0,0,0,0.6);
                text-align: center;
                border: 1px solid rgba(212,175,55,0.35);
            }
            .success-icon {
                font-size: 80px;
                margin-bottom: 20px;
                animation: scaleIn 0.6s ease-out;
                color: #f1d27a;
                text-shadow: 0 0 15px rgba(212,175,55,0.4);
            }
            @keyframes scaleIn {
                0% { transform: scale(0); opacity: 0; }
                50% { transform: scale(1.2); opacity: 0.9; }
                100% { transform: scale(1); opacity: 1; }
            }
            h1 {
                color: #f7dfa6;
                margin-bottom: 12px;
                font-size: 32px;
                letter-spacing: 0.5px;
            }
            .subtitle {
                color: #dcdcdc;
                font-size: 18px;
                margin-bottom: 30px;
            }
            .order-details {
                background: rgba(255, 215, 141, 0.05);
                border-radius: 15px;
                padding: 25px;
                margin: 30px 0;
                text-align: left;
                border: 1px solid rgba(212,175,55,0.25);
            }
            .detail-row {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                border-bottom: 1px solid rgba(212,175,55,0.15);
            }
            .detail-row:last-child {
                border-bottom: none;
                font-weight: 700;
                font-size: 18px;
                color: #f1d27a;
            }
            .label { color: #c7c7c7; }
            .value { color: #f5f5f5; font-weight: 600; }
            .alert-box {
                background: rgba(241, 210, 122, 0.1);
                border: 1px solid rgba(212,175,55,0.35);
                border-radius: 12px;
                padding: 22px;
                margin: 25px 0;
                color: #f1d27a;
                font-weight: 600;
                text-align: center;
            }
            .btn {
                background: linear-gradient(135deg, #f1d27a 0%, #d4af37 100%);
                color: #000;
                padding: 15px 42px;
                border: none;
                border-radius: 50px;
                font-size: 16px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                margin: 10px;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                font-weight: 600;
                box-shadow: 0 12px 28px rgba(212,175,55,0.3);
            }
            .btn:hover { transform: translateY(-2px); box-shadow: 0 18px 35px rgba(212,175,55,0.4); }
            .btn-secondary {
                background: rgba(245,245,245,0.12);
                color: #f5f5f5;
                border: 1px solid rgba(212,175,55,0.35);
                box-shadow: none;
            }
            .btn-secondary:hover {
                box-shadow: 0 15px 30px rgba(212,175,55,0.25);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="success-icon">🎉</div>
            <h1>¡Pago Exitoso!</h1>
            <p class="subtitle">Tu compra ha sido procesada correctamente</p>
            
            <div class="order-details">
                <div class="detail-row">
                    <span class="label">📦 Orden:</span>
                    <span class="value">#<?php echo $id_orden; ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">🕐 Producto:</span>
                    <span class="value"><?php echo htmlspecialchars($nombre_reloj); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">💳 Método:</span>
                    <span class="value">Wompi</span>
                </div>
                <div class="detail-row">
                    <span class="label">💰 Total:</span>
                    <span class="value">$<?php echo number_format($total, 0, ',', '.'); ?> COP</span>
                </div>
            </div>
            
            <div class="alert-box">
                <strong>📧 Revisa tu email</strong><br>
                Te hemos enviado un correo con los detalles de tu compra y tu código de descuento (si aplica).
            </div>
            
            <div>
                <a href="https://finoso.store/" class="btn">Volver al Inicio</a>
                <a href="https://finoso.store/perfil/perfil.html" class="btn btn-secondary">Ver Mi Perfil</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    
} else {
    $sessionFallback = $_SESSION['wompi_transaction_data'] ?? null;
    if ($sessionFallback) {
        wompi_response_log("[WOMPI-RESPONSE] ⚠️ Orden no encontrada pero hay datos en sesión. Mostrando fallback.");
        $fallbackIdOrden = $sessionFallback['id_orden'] ?? null;
        $fallbackTotal = $sessionFallback['total'] ?? 0;
        $fallbackNombre = $sessionFallback['datos_cliente']['nombre_reloj'] ?? null;
        if (!$fallbackNombre && isset($sessionFallback['id_reloj'])) {
            $fallbackNombre = 'Reloj #' . $sessionFallback['id_reloj'];
        }
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Pago recibido - FINOSO</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: radial-gradient(circle at top, #1b1b1b 0%, #000 55%, #050505 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                    color: #f5f5f5;
                }
                .container {
                    background: linear-gradient(155deg, #0c0c0c 0%, #161616 60%, #0b0b0b 100%);
                    border-radius: 18px;
                    padding: 40px;
                    max-width: 640px;
                    width: 100%;
                    box-shadow: 0 30px 80px rgba(0,0,0,0.6);
                    text-align: center;
                    border: 1px solid rgba(212,175,55,0.35);
                }
                .success-icon {
                    font-size: 70px;
                    margin-bottom: 20px;
                    color: #f1d27a;
                }
                h1 { color: #f7dfa6; margin-bottom: 12px; font-size: 30px; letter-spacing: 0.5px; }
                .subtitle { color: #dcdcdc; font-size: 17px; margin-bottom: 28px; }
                .order-details {
                    background: rgba(255, 215, 141, 0.05);
                    border-radius: 15px;
                    padding: 25px;
                    margin: 30px 0;
                    text-align: left;
                    border: 1px solid rgba(212,175,55,0.25);
                }
                .detail-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px 0;
                    border-bottom: 1px solid rgba(212,175,55,0.15);
                }
                .detail-row:last-child {
                    border-bottom: none;
                    font-weight: 700;
                    font-size: 18px;
                    color: #f1d27a;
                }
                .label { color: #c7c7c7; }
                .value { color: #f5f5f5; font-weight: 600; }
                .alert-box {
                    background: rgba(241, 210, 122, 0.1);
                    border: 1px solid rgba(212,175,55,0.35);
                    border-radius: 12px;
                    padding: 20px;
                    margin: 20px 0;
                    color: #f1d27a;
                    font-weight: 600;
                    text-align: center;
                }
                .btn {
                    background: linear-gradient(135deg, #f1d27a 0%, #d4af37 100%);
                    color: #000;
                    padding: 15px 42px;
                    border: none;
                    border-radius: 50px;
                    font-size: 16px;
                    cursor: pointer;
                    text-decoration: none;
                    display: inline-block;
                    margin: 10px;
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                    font-weight: 600;
                    box-shadow: 0 12px 28px rgba(212,175,55,0.3);
                }
                .btn:hover { transform: translateY(-2px); box-shadow: 0 18px 35px rgba(212,175,55,0.4); }
                .btn-secondary {
                    background: rgba(245,245,245,0.12);
                    color: #f5f5f5;
                    border: 1px solid rgba(212,175,55,0.35);
                    box-shadow: none;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="success-icon">🕒</div>
                <h1>Pago recibido, estamos validando tu orden</h1>
                <p class="subtitle">Tu pago fue confirmado por Wompi. Estamos registrando la orden en nuestra base de datos.</p>

                <div class="order-details">
                    <div class="detail-row">
                        <span class="label">📦 Orden (temporal):</span>
                        <span class="value"><?php echo $fallbackIdOrden !== null ? '#' . $fallbackIdOrden : 'En registro'; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">🕐 Producto:</span>
                        <span class="value"><?php echo htmlspecialchars($fallbackNombre ?? 'Reloj comprado'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">💳 Método:</span>
                        <span class="value">Wompi</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">💰 Total:</span>
                        <span class="value">$<?php echo number_format($fallbackTotal, 0, ',', '.'); ?> COP</span>
                    </div>
                </div>

                <div class="alert-box">
                    <strong>⏳ Estamos sincronizando tu orden.</strong><br>
                    Este proceso puede tardar unos segundos. Puedes actualizar la página o revisar tu perfil en un momento.
                </div>

                <div>
                    <a href="https://finoso.store/" class="btn">Volver al Inicio</a>
                    <a href="https://finoso.store/perfil/perfil.html" class="btn btn-secondary">Ver Mi Perfil</a>
                </div>
            </div>
        </body>
        </html>
        <?php
    } elseif ($retry < 3) {
        wompi_response_log("[WOMPI-RESPONSE] ⏳ Orden no encontrada aún, reintentando (retry={$retry}).");
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Procesando... - FINOSO</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: radial-gradient(circle at top, #1b1b1b 0%, #000 55%, #050505 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                    color: #f5f5f5;
                }
                .container {
                    background: linear-gradient(160deg, #0c0c0c 0%, #161616 55%, #111 100%);
                    border-radius: 18px;
                    padding: 40px;
                    max-width: 520px;
                    width: 100%;
                    box-shadow: 0 25px 70px rgba(0,0,0,0.55);
                    text-align: center;
                    border: 1px solid rgba(212,175,55,0.35);
                }
                .spinner {
                    width: 60px;
                    height: 60px;
                    border: 5px solid rgba(212,175,55,0.2);
                    border-top: 5px solid #d4af37;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin: 0 auto 30px;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                h1 { color: #f7dfa6; margin-bottom: 20px; font-size: 28px; letter-spacing: 0.5px; }
                p { color: #d7d7d7; font-size: 16px; line-height: 1.6; margin-bottom: 15px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="spinner"></div>
                <h1>⏳ Procesando tu Pago...</h1>
                <p>Estamos confirmando la información con Wompi.</p>
                <p>Esto puede tardar unos segundos.</p>
            </div>
            <script>
                setTimeout(() => {
                    const url = new URL(window.location.href);
                    const currentRetry = parseInt(url.searchParams.get('retry') || '0', 10) + 1;
                    url.searchParams.set('retry', currentRetry);
                    window.location.href = url.toString();
                }, 3000);
            </script>
        </body>
        </html>
        <?php
    } else {
        wompi_response_log("[WOMPI-RESPONSE] ❌ Orden no encontrada tras múltiples intentos.");
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Pago recibido - Confirmación manual</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: radial-gradient(circle at top, #1b1b1b 0%, #000 55%, #050505 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                    color: #f5f5f5;
                }
                .container {
                    background: linear-gradient(160deg, #0c0c0c 0%, #161616 55%, #111 100%);
                    border-radius: 18px;
                    padding: 40px;
                    max-width: 520px;
                    width: 100%;
                    box-shadow: 0 25px 70px rgba(0,0,0,0.55);
                    text-align: center;
                    border: 1px solid rgba(212,175,55,0.35);
                }
                h1 { color: #f7dfa6; margin-bottom: 20px; font-size: 28px; letter-spacing: 0.5px; }
                p { color: #d7d7d7; font-size: 16px; line-height: 1.6; margin-bottom: 15px; }
                .btn {
                    background: linear-gradient(135deg, #f1d27a 0%, #d4af37 100%);
                    color: #000;
                    padding: 15px 40px;
                    border: none;
                    border-radius: 50px;
                    font-size: 16px;
                    cursor: pointer;
                    text-decoration: none;
                    display: inline-block;
                    margin-top: 20px;
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                    font-weight: 600;
                    box-shadow: 0 10px 25px rgba(212,175,55,0.25);
                }
                .btn:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(212,175,55,0.35); }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Pago recibido</h1>
                <p>No pudimos confirmar automáticamente tu orden. Nuestro equipo la revisará manualmente.</p>
                <p>Por favor contáctanos con el comprobante de pago o revisa tu correo para más instrucciones.</p>
                <a href="https://finoso.store/" class="btn">Volver al Inicio</a>
            </div>
        </body>
        </html>
        <?php
    }
}

$stmt->close();
$conn->close();
?>




