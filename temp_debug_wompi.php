<?php
// Debug de la configuración de Wompi
include 'informacion/php/wompi_config.php';

echo "<h2>🔍 Debug de Configuración Wompi</h2>";

echo "<h3>📋 Verificación de constantes:</h3>";

// Verificar WOMPI_PUBLIC_KEY
if (defined('WOMPI_PUBLIC_KEY')) {
    echo "<p style='color: green;'>✅ <strong>WOMPI_PUBLIC_KEY definida</strong></p>";
    echo "<p><strong>Valor:</strong> " . WOMPI_PUBLIC_KEY . "</p>";
    echo "<p><strong>Longitud:</strong> " . strlen(WOMPI_PUBLIC_KEY) . " caracteres</p>";
    echo "<p><strong>Comienza con 'pub_':</strong> " . (strpos(WOMPI_PUBLIC_KEY, 'pub_') === 0 ? '✅ Sí' : '❌ No') . "</p>";
} else {
    echo "<p style='color: red;'>❌ <strong>WOMPI_PUBLIC_KEY NO definida</strong></p>";
}

// Verificar WOMPI_PRIVATE_KEY
if (defined('WOMPI_PRIVATE_KEY')) {
    echo "<p style='color: green;'>✅ <strong>WOMPI_PRIVATE_KEY definida</strong></p>";
    echo "<p><strong>Valor:</strong> " . WOMPI_PRIVATE_KEY . "</p>";
    echo "<p><strong>Longitud:</strong> " . strlen(WOMPI_PRIVATE_KEY) . " caracteres</p>";
    echo "<p><strong>Comienza con 'prv_':</strong> " . (strpos(WOMPI_PRIVATE_KEY, 'prv_') === 0 ? '✅ Sí' : '❌ No') . "</p>";
} else {
    echo "<p style='color: red;'>❌ <strong>WOMPI_PRIVATE_KEY NO definida</strong></p>";
}

// Verificar WOMPI_ENVIRONMENT
if (defined('WOMPI_ENVIRONMENT')) {
    echo "<p style='color: green;'>✅ <strong>WOMPI_ENVIRONMENT definida</strong></p>";
    echo "<p><strong>Valor:</strong> " . WOMPI_ENVIRONMENT . "</p>";
} else {
    echo "<p style='color: red;'>❌ <strong>WOMPI_ENVIRONMENT NO definida</strong></p>";
}

echo "<h3>🧪 Test de URL generada:</h3>";

$amount = 125010;
$amount_in_cents = $amount * 100;
$reference = 'DEBUG_TEST_' . time();

$checkout_url = 'https://checkout.wompi.co/p/?' . http_build_query([
    'public-key' => WOMPI_PUBLIC_KEY,
    'currency' => 'COP',
    'amount-in-cents' => $amount_in_cents,
    'reference' => $reference,
    'redirect-url' => WOMPI_REDIRECT_URL
]);

echo "<h4>📊 URL generada:</h4>";
echo "<textarea style='width: 100%; height: 100px; font-size: 12px;'>" . htmlspecialchars($checkout_url) . "</textarea>";

echo "<h4>🔍 Análisis de la URL:</h4>";
$url_parts = parse_url($checkout_url);
parse_str($url_parts['query'], $query_params);

echo "<ul>";
foreach ($query_params as $key => $value) {
    $status = ($key === 'public-key' && !empty($value)) ? '✅' : '❓';
    echo "<li>{$status} <strong>{$key}:</strong> " . htmlspecialchars($value) . "</li>";
}
echo "</ul>";

echo "<h3>🧪 Test de la URL:</h3>";
echo "<p><a href='" . $checkout_url . "' target='_blank' style='background: #28a745; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-size: 16px;'>🔍 Probar URL Debug</a></p>";

echo "<h3>📝 Logs de error:</h3>";
echo "<p>Revisa el archivo de logs de PHP para ver:</p>";
echo "<ul>";
echo "<li>DEBUG - PUBLIC KEY: [valor]</li>";
echo "<li>DEBUG - PUBLIC KEY LENGTH: [número]</li>";
echo "</ul>";

echo "<h3>🔧 Posibles soluciones:</h3>";
echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Si la llave está vacía o mal formateada:</strong></p>";
echo "<ol>";
echo "<li>Verifica que no haya espacios extra en wompi_config.php</li>";
echo "<li>Asegúrate de que la llave comience con 'pub_prod_'</li>";
echo "<li>Verifica que no haya caracteres especiales</li>";
echo "</ol>";
echo "</div>";
?>

