<?php
/**
 * LOGGER PERSONALIZADO PARA DEBUG
 * Escribe logs en un archivo dentro del proyecto
 */

function escribir_log($mensaje, $tipo = 'INFO') {
    $log_dir = __DIR__ . '/../logs';
    
    // Crear directorio si no existe
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    $log_file = $log_dir . '/codigos_descuento.log';
    
    $fecha = date('Y-m-d H:i:s');
    $linea = "[{$fecha}] [{$tipo}] {$mensaje}" . PHP_EOL;
    
    // Escribir en el archivo
    file_put_contents($log_file, $linea, FILE_APPEND);
    
    // También usar error_log por si acaso
    error_log($mensaje);
}

function log_separador($titulo = '') {
    $separador = str_repeat('=', 60);
    if ($titulo) {
        escribir_log($separador . ' ' . $titulo . ' ' . $separador, 'HEADER');
    } else {
        escribir_log($separador, 'SEPARATOR');
    }
}
?>

