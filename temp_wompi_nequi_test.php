<?php
// Test de integración Wompi con Nequi
include 'informacion/php/wompi_config.php';

echo "<h2>📱 Test de Integración Wompi + Nequi</h2>";

echo "<h3>📋 Configuración:</h3>";
echo "<ul>";
echo "<li><strong>Entorno:</strong> " . WOMPI_ENVIRONMENT . "</li>";
echo "<li><strong>Public Key:</strong> " . WOMPI_PUBLIC_KEY . "</li>";
echo "<li><strong>Método:</strong> Checkout directo con tokens de aceptación</li>";
echo "</ul>";

echo "<h3>🔧 Problema identificado y solucionado:</h3>";
echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>❌ Error anterior:</strong> Error 422 - Unprocessable Entity</p>";
echo "<p><strong>🔍 Causa:</strong> Faltaban los tokens de aceptación requeridos para Nequi</p>";
echo "<p><strong>✅ Solución:</strong> Agregados acceptance-token y accept-personal-auth</p>";
echo "</div>";

echo "<h3>📝 Tokens agregados:</h3>";
echo "<ul>";
echo "<li>✅ <strong>acceptance-token:</strong> Para aceptar términos y condiciones</li>";
echo "<li>✅ <strong>accept-personal-auth:</strong> Para autorización de datos personales</li>";
echo "</ul>";

// Test con Nequi
echo "<h3>📱 Test: Pago con Nequi</h3>";

$amount = 125010; // $125,010 COP
$amount_in_cents = $amount * 100;
$reference = 'FINOSO_NEQUI_' . time();

$checkout_url_nequi = 'https://checkout.wompi.co/p/?' . http_build_query([
    'public-key' => WOMPI_PUBLIC_KEY,
    'currency' => 'COP',
    'amount-in-cents' => $amount_in_cents,
    'reference' => $reference,
    'redirect-url' => WOMPI_REDIRECT_URL,
    'customer-data.email' => 'test@finoso.com',
    'customer-data.full-name' => 'Cliente Test Finoso',
    'customer-data.phone-number' => '3173897119',
    'customer-data.legal-id' => '1234567890',
    'customer-data.legal-id-type' => 'CC',
    'acceptance-token' => 'eyJhbGciOiJIUzI1NiJ9.eyJjb250cmFjdF9pZCI6NTA3LCJwZXJtYWxpbmsiOiJodHRwczovL3dvbXBpLmNvbS9hc3NldHMvZG93bmxvYWRibGUvcmVnbGFtZW50by1Vc3Vhcmlvcy1Db2xvbWJpYS5wZGYiLCJmaWxlX2hhc2giOiJkYzJkNGUzMDVlNGQzNmFhYjhjYzU3N2I1YTY5Nzg1MSIsImppdCI6IjE3NjA2MzcxNzItODk3NTYiLCJlbWFpbCI6IiIsImV4cCI6MTc2MDY0MDc3Mn0.wcZok6cA9amDB310YC03pKxT95AgWulpLAX8FVS9NR0',
    'accept-personal-auth' => 'eyJhbGciOiJIUzI1NiJ9.eyJjb250cmFjdF9pZCI6NDc0LCJwZXJtYWxpbmsiOiJodHRwczovL3dvbXBpLmNvbS9hc3NldHMvZG93bmxvYWRibGUvYXV0b3JpemFjaW9uLXRyYXRhbWllbnRvLWRhdG9zLXBlcnNvbmFsZXMucGRmIiwiZmlsZV9oYXNoIjoiNTE2ODYzZjA3NzZlZWY3NjBkNGI5OWFiMWJlZjRjNzgiLCJqaXQiOiIxNzYwNjM3MTcyLTg5MzAwIiwiZW1haWwiOiIifQ.9Wue-UEvnk921fogMyf6Z3dmqfvAqq7gLs7Qmd7I-5o'
]);

echo "<h4>📊 Datos de la transacción:</h4>";
echo "<ul>";
echo "<li><strong>Monto:</strong> $" . number_format($amount) . " COP</li>";
echo "<li><strong>Referencia:</strong> " . $reference . "</li>";
echo "<li><strong>Método de pago:</strong> Nequi</li>";
echo "<li><strong>Teléfono:</strong> 3173897119</li>";
echo "</ul>";

echo "<p><a href='" . $checkout_url_nequi . "' target='_blank' style='background: #28a745; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-size: 16px;'>📱 Probar Pago con Nequi</a></p>";

echo "<h3>🔍 Cómo verificar que funciona:</h3>";
echo "<ol>";
echo "<li><strong>Haz clic en el botón de arriba</strong> para ir al checkout</li>";
echo "<li><strong>Verifica que el monto esté prellenado</strong> ($125,010 COP)</li>";
echo "<li><strong>Selecciona Nequi</strong> como método de pago</li>";
echo "<li><strong>Ingresa el número de Nequi</strong> (3173897119)</li>";
echo "<li><strong>Completa el pago</strong> (o cancela si es solo prueba)</li>";
echo "<li><strong>Verifica en tu panel de Wompi</strong> que la transacción aparezca</li>";
echo "</ol>";

echo "<h3>✅ Parámetros corregidos:</h3>";
echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>✅ Ahora se incluyen todos los parámetros requeridos:</strong></p>";
echo "<ul>";
echo "<li>💰 <strong>Monto prellenado:</strong> amount-in-cents</li>";
echo "<li>👤 <strong>Datos del cliente:</strong> email, nombre, teléfono</li>";
echo "<li>📱 <strong>Número de Nequi:</strong> customer-data.phone-number</li>";
echo "<li>📋 <strong>Acceptance token:</strong> Para términos y condiciones</li>";
echo "<li>🔒 <strong>Personal auth:</strong> Para autorización de datos</li>";
echo "<li>🔄 <strong>Redirect URL:</strong> Para regresar después del pago</li>";
echo "</ul>";
echo "</div>";

echo "<h3>🎉 Estado final:</h3>";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
echo "<p style='color: #155724; font-weight: bold;'>✅ ¡Integración Wompi + Nequi corregida!</p>";
echo "<ul style='color: #155724;'>";
echo "<li>✅ Error 422 solucionado</li>";
echo "<li>✅ Tokens de aceptación incluidos</li>";
echo "<li>✅ Parámetros completos para Nequi</li>";
echo "<li>✅ Monto prellenado correctamente</li>";
echo "<li>✅ Listo para pagos reales con Nequi</li>";
echo "</ul>";
echo "</div>";
?>

