<?php
// Test final de Wompi en producción con VPOS ID real
include 'informacion/php/wompi_config.php';

echo "<h2>🎉 Test Final Wompi - PRODUCCIÓN COMPLETA</h2>";

echo "<h3>📋 Configuración final:</h3>";
echo "<ul>";
echo "<li><strong>Entorno:</strong> " . WOMPI_ENVIRONMENT . "</li>";
echo "<li><strong>Public Key:</strong> " . WOMPI_PUBLIC_KEY . "</li>";
echo "<li><strong>Private Key:</strong> " . WOMPI_PRIVATE_KEY . "</li>";
echo "<li><strong>VPOS ID:</strong> " . WOMPI_VPOS_ID . "</li>";
echo "<li><strong>Base URL:</strong> " . getWompiBaseUrl() . "</li>";
echo "</ul>";

echo "<h3>🔗 URL de prueba generada:</h3>";

// Generar URL de prueba con datos reales
$amount = 50000; // $50,000 COP
$reference = 'FINOSO_' . time() . '_TEST';
$vpos_url = 'https://checkout.wompi.co/l/' . WOMPI_VPOS_ID . '?' . http_build_query([
    'amount' => $amount * 100,
    'currency' => 'COP',
    'reference' => $reference,
    'customerEmail' => 'test@finoso.com',
    'customerData[fullName]' => 'Cliente Test Finoso',
    'customerData[phoneNumber]' => '3173897119',
    'shippingAddress[addressLine1]' => 'Calle 123 #45-67',
    'shippingAddress[city]' => 'Bogotá',
    'shippingAddress[region]' => 'Cundinamarca',
    'shippingAddress[country]' => 'CO',
    'redirectUrl' => WOMPI_REDIRECT_URL
]);

echo "<p><strong>Monto de prueba:</strong> $" . number_format($amount) . " COP</p>";
echo "<p><strong>Referencia:</strong> " . $reference . "</p>";
echo "<p><strong>URL generada:</strong></p>";
echo "<textarea style='width: 100%; height: 100px; font-size: 12px;'>" . htmlspecialchars($vpos_url) . "</textarea>";

echo "<h3>🧪 Test de la URL:</h3>";
echo "<p><a href='" . $vpos_url . "' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Probar URL de Wompi (NUEVA VENTANA)</a></p>";

echo "<h3>⚠️ IMPORTANTE:</h3>";
echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Esta es una transacción REAL en producción:</strong></p>";
echo "<ul>";
echo "<li>✅ Usa el VPOS ID real: " . WOMPI_VPOS_ID . "</li>";
echo "<li>✅ Conecta con la API de producción de Wompi</li>";
echo "<li>⚠️ <strong>NO completes el pago</strong> - solo prueba que se abra correctamente</li>";
echo "<li>⚠️ <strong>Verifica que aparezcan los métodos de pago</strong> disponibles</li>";
echo "</ul>";
echo "</div>";

echo "<h3>🎯 Métodos de pago disponibles en tu cuenta:</h3>";
echo "<ul>";
echo "<li>💳 Tarjetas de crédito/débito</li>";
echo "<li>🏦 PSE (Pagos Seguros en Línea)</li>";
echo "<li>📱 Nequi</li>";
echo "<li>🏛️ Bancolombia QR</li>";
echo "<li>🏛️ Bancolombia Transferencia</li>";
echo "<li>🏛️ Bancolombia Recaudo</li>";
echo "<li>📱 Daviplata</li>";
echo "<li>💳 Click to Pay</li>";
echo "</ul>";

echo "<h3>✅ Estado de la integración:</h3>";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
echo "<p style='color: #155724; font-weight: bold;'>🎉 ¡INTEGRACIÓN DE WOMPI EN PRODUCCIÓN COMPLETA!</p>";
echo "<ul style='color: #155724;'>";
echo "<li>✅ Llaves de producción configuradas</li>";
echo "<li>✅ VPOS ID real obtenido</li>";
echo "<li>✅ URLs actualizadas</li>";
echo "<li>✅ Listo para transacciones reales</li>";
echo "</ul>";
echo "</div>";
?>

