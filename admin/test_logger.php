<?php
/**
 * TEST DEL LOGGER
 * Prueba el sistema de logs para ver si escribe correctamente
 */

require_once 'php/logger.php';

echo "<h1>🧪 Test del Sistema de Logs</h1>";
echo "<p>Escribiendo logs de prueba...</p>";

log_separador('TEST INICIO');
escribir_log('Este es un log de prueba INFO', 'INFO');
escribir_log('Este es un log de prueba SUCCESS ✓✓✓', 'SUCCESS');
escribir_log('Este es un log de prueba WARNING ⚠', 'WARNING');
escribir_log('Este es un log de prueba ERROR ✗✗✗', 'ERROR');
escribir_log('Este es un log de prueba DEBUG', 'DEBUG');
log_separador('TEST FIN');

echo "<h2 style='color: green;'>✅ Logs escritos correctamente</h2>";
echo "<p><strong>Archivo:</strong> admin/logs/codigos_descuento.log</p>";

$log_file = __DIR__ . '/logs/codigos_descuento.log';
if (file_exists($log_file)) {
    echo "<p><strong>Tamaño del archivo:</strong> " . filesize($log_file) . " bytes</p>";
    echo "<h3>📄 Contenido:</h3>";
    echo "<pre style='background: #000; color: #0f0; padding: 20px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars(file_get_contents($log_file));
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ Error: No se pudo crear el archivo de logs</p>";
}

echo "<hr>";
echo "<h3>🔗 Enlaces útiles:</h3>";
echo "<p><a href='ver_logs_debug.php' style='padding: 10px 20px; background: #FFCF66; color: #000; text-decoration: none; border-radius: 5px; font-weight: bold;'>Ver Logs (Visor)</a></p>";
echo "<p><a href='panel.php' style='padding: 10px 20px; background: #ccc; color: #000; text-decoration: none; border-radius: 5px; font-weight: bold;'>Volver al Panel</a></p>";
?>

