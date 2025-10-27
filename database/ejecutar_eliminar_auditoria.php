<?php
/**
 * EJECUTOR - Eliminar tabla auditoria_pagos
 * Esta tabla fue creada experimentalmente pero nunca se usó
 */

require_once '../admin/conexion.php';

echo "<h2>🗑️ Eliminando tabla auditoria_pagos...</h2>";

try {
    // Verificar si la tabla existe
    $result = $conn->query("SHOW TABLES LIKE 'auditoria_pagos'");
    
    if ($result->num_rows > 0) {
        echo "<p>✅ Tabla encontrada, procediendo a eliminar...</p>";
        
        // Eliminar la tabla
        if ($conn->query("DROP TABLE auditoria_pagos")) {
            echo "<p style='color: green;'>✅ Tabla auditoria_pagos eliminada exitosamente</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al eliminar: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ La tabla auditoria_pagos no existe en la base de datos</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>📊 Resumen:</h3>";
echo "<ul>";
echo "<li>La tabla <code>auditoria_pagos</code> era experimental</li>";
echo "<li>El código que la usaba estaba comentado en <code>informacion/php/subir_comprobante.php</code></li>";
echo "<li>Nunca tuvo registros reales</li>";
echo "<li>Ya fue eliminada del código PHP</li>";
echo "</ul>";

$conn->close();
?>

/**
 * EJECUTOR - Eliminar tabla auditoria_pagos
 * Esta tabla fue creada experimentalmente pero nunca se usó
 */

require_once '../admin/conexion.php';

echo "<h2>🗑️ Eliminando tabla auditoria_pagos...</h2>";

try {
    // Verificar si la tabla existe
    $result = $conn->query("SHOW TABLES LIKE 'auditoria_pagos'");
    
    if ($result->num_rows > 0) {
        echo "<p>✅ Tabla encontrada, procediendo a eliminar...</p>";
        
        // Eliminar la tabla
        if ($conn->query("DROP TABLE auditoria_pagos")) {
            echo "<p style='color: green;'>✅ Tabla auditoria_pagos eliminada exitosamente</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al eliminar: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ La tabla auditoria_pagos no existe en la base de datos</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>📊 Resumen:</h3>";
echo "<ul>";
echo "<li>La tabla <code>auditoria_pagos</code> era experimental</li>";
echo "<li>El código que la usaba estaba comentado en <code>informacion/php/subir_comprobante.php</code></li>";
echo "<li>Nunca tuvo registros reales</li>";
echo "<li>Ya fue eliminada del código PHP</li>";
echo "</ul>";

$conn->close();
?>

