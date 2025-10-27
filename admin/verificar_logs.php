<?php
/**
 * VERIFICACIÓN RÁPIDA DE LOGS
 * Muestra el estado actual del sistema de logs
 */

$log_file = __DIR__ . '/logs/codigos_descuento.log';

echo "<h1>🔍 Verificación del Sistema de Logs</h1>";
echo "<hr>";

// Verificar directorio
$log_dir = __DIR__ . '/logs';
echo "<h3>1. Directorio de logs:</h3>";
if (is_dir($log_dir)) {
    echo "✅ <strong>El directorio existe:</strong> " . $log_dir . "<br>";
    echo "✅ <strong>Permisos:</strong> " . substr(sprintf('%o', fileperms($log_dir)), -4) . "<br>";
} else {
    echo "❌ <strong>El directorio NO existe</strong><br>";
    echo "<p>Creando directorio...</p>";
    mkdir($log_dir, 0777, true);
    echo "✅ Directorio creado<br>";
}

echo "<hr>";

// Verificar archivo de log
echo "<h3>2. Archivo de logs:</h3>";
if (file_exists($log_file)) {
    $size = filesize($log_file);
    echo "✅ <strong>El archivo existe:</strong> " . $log_file . "<br>";
    echo "✅ <strong>Tamaño:</strong> " . $size . " bytes (" . round($size/1024, 2) . " KB)<br>";
    echo "✅ <strong>Última modificación:</strong> " . date('Y-m-d H:i:s', filemtime($log_file)) . "<br>";
    
    if ($size > 0) {
        echo "<h4>📄 Últimas 10 líneas:</h4>";
        $lines = file($log_file);
        $last_lines = array_slice($lines, -10);
        echo "<pre style='background: #000; color: #0f0; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
        foreach ($last_lines as $line) {
            echo htmlspecialchars($line);
        }
        echo "</pre>";
    } else {
        echo "<p style='color: orange;'>⚠️ El archivo está vacío (no se han registrado logs aún)</p>";
    }
} else {
    echo "⚠️ <strong>El archivo NO existe aún</strong> (se creará al aprobar la primera orden)<br>";
}

echo "<hr>";

// Verificar que el logger.php existe
echo "<h3>3. Sistema de logs:</h3>";
$logger_file = __DIR__ . '/php/logger.php';
if (file_exists($logger_file)) {
    echo "✅ <strong>logger.php existe:</strong> " . $logger_file . "<br>";
} else {
    echo "❌ <strong>logger.php NO existe</strong> - Esto es un problema grave<br>";
}

echo "<hr>";

// Prueba de escritura
echo "<h3>4. Prueba de escritura:</h3>";
require_once __DIR__ . '/php/logger.php';

escribir_log('TEST - Verificación del sistema de logs', 'INFO');
escribir_log('Si ves este mensaje, el logger funciona correctamente ✓', 'SUCCESS');

if (file_exists($log_file) && filesize($log_file) > 0) {
    echo "✅ <strong>Prueba exitosa</strong> - Se escribió en el log correctamente<br>";
} else {
    echo "❌ <strong>Error al escribir</strong> - Verifica permisos<br>";
}

echo "<hr>";

// Enlaces útiles
echo "<h3>🔗 Enlaces:</h3>";
echo "<p><a href='ver_logs_debug.php' style='padding: 10px 20px; background: #FFCF66; color: #000; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;'>📋 Ver Logs Completos</a></p>";
echo "<p><a href='test_logger.php' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;'>🧪 Test del Logger</a></p>";
echo "<p><a href='panel.php' style='padding: 10px 20px; background: #ccc; color: #000; text-decoration: none; border-radius: 5px; font-weight: bold;'>← Volver al Panel</a></p>";

echo "<hr>";
echo "<p style='text-align: center; color: #888;'><small>Sistema de Logs - Finoso</small></p>";
?>

