<?php
// 🔍 DEPURACIÓN EXTENSIVA - Inicio del script
error_log("=== LISTAR_RELOJES.PHP INICIADO ===");
error_log("Timestamp: " . date('Y-m-d H:i:s'));
error_log("PHP Version: " . phpversion());
error_log("Memory limit: " . ini_get('memory_limit'));
error_log("Max execution time: " . ini_get('max_execution_time'));

// Verificar si hay errores de sintaxis
error_log("🔍 Verificando sintaxis del archivo...");
if (!defined('PHP_VERSION')) {
    error_log("❌ Error de sintaxis detectado");
    die("Error de sintaxis");
}
error_log("✅ Sintaxis OK");

// Configurar headers
error_log("📤 Configurando headers...");
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
error_log("✅ Headers configurados");

// Verificar si ya hay output
if (ob_get_level()) {
    error_log("⚠️ Output buffer activo, limpiando...");
    ob_clean();
}

// Función para limpiar caracteres UTF-8 mal codificados
function limpiarUTF8($string) {
    // Convertir de ISO-8859-1 a UTF-8 si es necesario
    if (!mb_check_encoding($string, 'UTF-8')) {
        $string = mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
    }
    
    // Limpiar caracteres de control
    $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
    
    // Normalizar espacios en blanco
    $string = preg_replace('/\s+/', ' ', trim($string));
    
    return $string;
}

error_log("🔄 Iniciando try-catch principal...");

try {
    error_log("📁 Cargando archivo de conexión...");
    $conexion_path = __DIR__ . '/../../php/conexion.php';
    error_log("📁 Ruta de conexión: " . $conexion_path);
    error_log("📁 Archivo existe: " . (file_exists($conexion_path) ? 'SÍ' : 'NO'));
    
    require_once $conexion_path;
    error_log("✅ Archivo de conexión cargado");

    error_log("🔗 Verificando conexión a base de datos...");
    error_log("🔗 Variable conn existe: " . (isset($conn) ? 'SÍ' : 'NO'));
    error_log("🔗 Variable conn es mysqli: " . ($conn instanceof mysqli ? 'SÍ' : 'NO'));
    
    if (!isset($conn) || !$conn) {
        throw new Exception("Variable \$conn no está disponible");
    }
    if (!($conn instanceof mysqli)) {
        throw new Exception("Variable \$conn no es una instancia de mysqli");
    }
    
    // Configurar charset UTF-8 en la conexión
    error_log("🔤 Configurando charset UTF-8...");
    if (!$conn->set_charset('utf8')) {
        error_log("⚠️ Error configurando charset: " . $conn->error);
    } else {
        error_log("✅ Charset UTF-8 configurado correctamente");
    }
    
    error_log("✅ Conexión a base de datos verificada");

    error_log("📊 Ejecutando consulta SQL...");
    $sql = "SELECT * FROM reloj ORDER BY precio DESC";
    error_log("SQL: " . $sql);
    
    $result = $conn->query($sql);
    error_log("📊 Resultado de consulta: " . ($result ? 'SUCCESS' : 'FAILED'));
    
    if (!$result) {
        throw new Exception("Error en consulta: " . $conn->error);
    }
    error_log("✅ Consulta ejecutada exitosamente");

    error_log("📋 Procesando resultados de la consulta...");
    error_log("📋 Número de filas: " . $result->num_rows);
    
    $relojes = [];
    $contador = 0;
    
    while ($row = $result->fetch_assoc()) {
        $contador++;
        error_log("📝 Procesando reloj #$contador: " . $row['nombre']);
        error_log("📝 Datos del reloj: " . print_r($row, true));
        
        try {
            // Limpiar todos los campos de texto con UTF-8
            $marca_limpia = limpiarUTF8($row['marca']);
            $nombre_limpio = limpiarUTF8($row['nombre']);
            $descripcion_limpia = limpiarUTF8($row['descripcion']);
            $img_limpia = limpiarUTF8($row['img']);
            
            error_log("🧹 Campos limpiados - Marca: '$marca_limpia', Nombre: '$nombre_limpio'");
            
            $reloj = [
                'id_reloj' => $row['id_reloj'],
                'marca' => $marca_limpia,
                'nombre' => $nombre_limpio,
                'descripcion' => $descripcion_limpia,
                'precio' => floatval($row['precio']),
                'precio_formateado' => '$' . number_format($row['precio'], 0, ',', '.'),
                'descuento' => $row['descuento'] ? floatval($row['descuento']) : null,
                'descuento_formateado' => $row['descuento'] ? ($row['descuento'] . '%') : 'Sin descuento',
                'img' => $img_limpia,
                'disponible' => (bool)$row['disponible'],
                'vendido' => (bool)$row['vendido'],
                'estado' => $row['vendido'] ? 'Vendido' : ($row['disponible'] ? 'Disponible' : 'No disponible'),
                'estado_color' => $row['vendido'] ? '#f44336' : ($row['disponible'] ? '#4CAF50' : '#FF9800')
            ];
            
            $relojes[] = $reloj;
            error_log("✅ Reloj #$contador procesado correctamente");
            
        } catch (Exception $e2) {
            error_log("❌ Error procesando reloj #$contador: " . $e2->getMessage());
        }
    }
    
    error_log("✅ Procesados $contador relojes");
    error_log("📊 Total en array: " . count($relojes));
    error_log("📊 Preparando respuesta JSON...");
    
    $respuesta = [
        'success' => true,
        'total' => count($relojes),
        'relojes' => $relojes
    ];
    
    error_log("📊 Respuesta preparada: " . print_r($respuesta, true));
    error_log("📤 Enviando respuesta JSON...");
    
    $json_output = json_encode($respuesta);
    error_log("📤 JSON generado (longitud): " . strlen($json_output));
    error_log("📤 JSON generado (primeros 200 chars): " . substr($json_output, 0, 200));
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("❌ Error en json_encode: " . json_last_error_msg());
        throw new Exception("Error generando JSON: " . json_last_error_msg());
    }
    
    // Limpiar cualquier output previo
    if (ob_get_level()) {
        ob_clean();
    }
    
    echo $json_output;
    error_log("✅ Respuesta JSON enviada exitosamente");
    
    // Forzar flush del output
    if (ob_get_level()) {
        ob_flush();
    }
    flush();
    
} catch (Exception $e) {
    error_log("❌ ERROR PRINCIPAL: " . $e->getMessage());
    error_log("📍 Archivo: " . $e->getFile() . " Línea: " . $e->getLine());
    error_log("📍 Stack trace: " . $e->getTraceAsString());
    
    // Limpiar cualquier output previo
    if (ob_get_level()) {
        ob_clean();
    }
    
    $error_response = json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    error_log("📤 Enviando respuesta de error: " . $error_response);
    echo $error_response;
}

error_log("=== LISTAR_RELOJES.PHP FINALIZADO ===");
error_log("Memory usage: " . memory_get_usage(true) . " bytes");
error_log("Memory peak: " . memory_get_peak_usage(true) . " bytes");
?>