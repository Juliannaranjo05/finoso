<?php
/**
 * RESPUESTA DE WOMPI CARRITO - Solo Confirmación Visual
 * 
 * Este archivo NO crea la orden (eso lo hace el webhook).
 * Solo busca la orden ya creada y muestra confirmación al usuario.
 */

session_start();
include 'conexion.php';

error_log("[WOMPI-RESPONSE-CARRITO] 🔄 Usuario redirigido desde Wompi");

// Obtener parámetros de la URL
$transaction_id = $_GET['id'] ?? '';
$reference = $_GET['reference'] ?? '';
$status = $_GET['status'] ?? '';

error_log("[WOMPI-RESPONSE-CARRITO] Params - ID: $transaction_id, Ref: $reference, Status: $status");

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
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .container {
                background: white;
                border-radius: 20px;
                padding: 40px;
                max-width: 500px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                text-align: center;
            }
            .spinner {
                width: 60px;
                height: 60px;
                border: 5px solid #f3f3f3;
                border-top: 5px solid #667eea;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 30px;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            h1 { color: #333; margin-bottom: 20px; font-size: 28px; }
            p { color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 30px; }
            .btn {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 15px 40px;
                border: none;
                border-radius: 50px;
                font-size: 16px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                transition: transform 0.3s ease;
            }
            .btn:hover { transform: translateY(-2px); }
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
$sql = "SELECT o.*, 
        GROUP_CONCAT(r.nombre SEPARATOR ', ') as nombres_relojes,
        COUNT(DISTINCT od.id_reloj) as cantidad_relojes
        FROM orden o
        LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
        LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
        WHERE o.token_verificacion = ?
        GROUP BY o.id_orden
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $reference);
        $stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $orden = $result->fetch_assoc();
    $id_orden = $orden['id_orden'];
    $total = $orden['total'];
    $nombres_relojes = $orden['nombres_relojes'] ?? 'tus relojes';
    $cantidad_relojes = $orden['cantidad_relojes'] ?? 0;
    $token = $orden['token_verificacion'];
    
    error_log("[WOMPI-RESPONSE-CARRITO] ✅ Orden encontrada: #$id_orden con $cantidad_relojes relojes");

        // Limpiar datos de sesión
        unset($_SESSION['wompi_carrito_data']);

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
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .container {
                background: white;
                border-radius: 20px;
                padding: 40px;
                max-width: 600px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                text-align: center;
            }
            .success-icon {
                font-size: 80px;
                margin-bottom: 20px;
                animation: scaleIn 0.5s ease-out;
            }
            @keyframes scaleIn {
                0% { transform: scale(0); }
                50% { transform: scale(1.2); }
                100% { transform: scale(1); }
            }
            h1 {
                color: #333;
                margin-bottom: 15px;
                font-size: 32px;
            }
            .subtitle {
                color: #666;
                font-size: 18px;
                margin-bottom: 30px;
            }
            .order-details {
                background: #f8f9fa;
                border-radius: 15px;
                padding: 25px;
                margin: 30px 0;
                text-align: left;
            }
            .detail-row {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                border-bottom: 1px solid #e0e0e0;
            }
            .detail-row:last-child {
                border-bottom: none;
                font-weight: bold;
                font-size: 18px;
                color: #667eea;
            }
            .label { color: #666; }
            .value { color: #333; font-weight: 600; }
            .productos-list {
                background: #fff;
                padding: 15px;
                border-radius: 10px;
                margin-top: 10px;
                font-size: 14px;
                color: #555;
            }
            .alert-box {
                background: #d4edda;
                border: 1px solid #c3e6cb;
                border-radius: 10px;
                padding: 20px;
                margin: 20px 0;
                color: #155724;
            }
            .btn {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 15px 40px;
                border: none;
                border-radius: 50px;
                font-size: 16px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                margin: 10px;
                transition: transform 0.3s ease;
            }
            .btn:hover { transform: translateY(-2px); }
            .btn-secondary {
                background: #6c757d;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="success-icon">🛒</div>
            <h1>¡Pago Exitoso!</h1>
            <p class="subtitle">Tu compra ha sido procesada correctamente</p>
            
            <div class="order-details">
                <div class="detail-row">
                    <span class="label">📦 Orden:</span>
                    <span class="value">#<?php echo $id_orden; ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">🕐 Productos:</span>
                    <span class="value"><?php echo $cantidad_relojes; ?> reloj<?php echo $cantidad_relojes > 1 ? 'es' : ''; ?></span>
                </div>
                <div class="productos-list">
                    <?php echo htmlspecialchars($nombres_relojes); ?>
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
    // Orden no encontrada (el webhook aún no procesó)
    error_log("[WOMPI-RESPONSE-CARRITO] ⏳ Orden no encontrada aún, webhook puede estar procesando");
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
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .container {
                background: white;
                border-radius: 20px;
                padding: 40px;
                max-width: 500px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                text-align: center;
            }
            .spinner {
                width: 60px;
                height: 60px;
                border: 5px solid #f3f3f3;
                border-top: 5px solid #667eea;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 30px;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            h1 { color: #333; margin-bottom: 20px; font-size: 28px; }
            p { color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 15px; }
            .btn {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 15px 40px;
                border: none;
                border-radius: 50px;
                font-size: 16px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                margin-top: 20px;
                transition: transform 0.3s ease;
            }
            .btn:hover { transform: translateY(-2px); }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="spinner"></div>
            <h1>⏳ Procesando tu Pago...</h1>
            <p>Tu pago está siendo verificado por Wompi.</p>
            <p><strong>Recibirás un email de confirmación en los próximos minutos.</strong></p>
            <p style="font-size: 14px; color: #999;">Si tienes dudas, contacta a soporte con tu número de referencia.</p>
            <a href="https://finoso.store/" class="btn">Volver al Inicio</a>
        </div>
        <script>
            // Recargar después de 3 segundos por si el webhook ya procesó
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        </script>
    </body>
    </html>
    <?php
}

$stmt->close();
$conn->close();
?>
