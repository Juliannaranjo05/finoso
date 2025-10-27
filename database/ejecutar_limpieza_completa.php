<?php
/**
 * LIMPIEZA COMPLETA DE BASE DE DATOS
 * Elimina todos los campos y tablas innecesarias identificadas
 */

require_once '../admin/conexion.php';

echo "<h1>🧹 Limpieza Completa de Base de Datos</h1>";
echo "<p>Eliminando campos y tablas innecesarias...</p>";
echo "<hr>";

$resultados = [];
$errores = [];

// 1. Eliminar tabla auditoria_pagos
echo "<h2>1️⃣ Eliminando tabla auditoria_pagos...</h2>";
try {
    $result = $conn->query("SHOW TABLES LIKE 'auditoria_pagos'");
    if ($result->num_rows > 0) {
        if ($conn->query("DROP TABLE auditoria_pagos")) {
            echo "<p style='color: green;'>✅ Tabla auditoria_pagos eliminada</p>";
            $resultados[] = "Tabla auditoria_pagos eliminada";
        } else {
            throw new Exception($conn->error);
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Tabla auditoria_pagos no existe</p>";
        $resultados[] = "Tabla auditoria_pagos ya estaba eliminada";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    $errores[] = "Error eliminando auditoria_pagos: " . $e->getMessage();
}

echo "<hr>";

// 2. Eliminar FK orden_ibfk_2
echo "<h2>2️⃣ Eliminando Foreign Key orden_ibfk_2...</h2>";
try {
    // Verificar si existe la FK
    $check = $conn->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND TABLE_NAME = 'orden' 
          AND CONSTRAINT_NAME = 'orden_ibfk_2'
    ");
    
    if ($check->num_rows > 0) {
        if ($conn->query("ALTER TABLE orden DROP FOREIGN KEY orden_ibfk_2")) {
            echo "<p style='color: green;'>✅ Foreign Key orden_ibfk_2 eliminada</p>";
            $resultados[] = "FK orden_ibfk_2 eliminada";
        } else {
            throw new Exception($conn->error);
        }
    } else {
        echo "<p style='color: orange;'>⚠️ FK orden_ibfk_2 no existe</p>";
        $resultados[] = "FK orden_ibfk_2 ya estaba eliminada";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    $errores[] = "Error eliminando FK: " . $e->getMessage();
}

echo "<hr>";

// 3. Eliminar índice codigo_descuento_id
echo "<h2>3️⃣ Eliminando índice codigo_descuento_id...</h2>";
try {
    $check = $conn->query("SHOW INDEX FROM orden WHERE Key_name = 'codigo_descuento_id'");
    
    if ($check->num_rows > 0) {
        if ($conn->query("ALTER TABLE orden DROP KEY codigo_descuento_id")) {
            echo "<p style='color: green;'>✅ Índice codigo_descuento_id eliminado</p>";
            $resultados[] = "Índice codigo_descuento_id eliminado";
        } else {
            throw new Exception($conn->error);
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Índice codigo_descuento_id no existe</p>";
        $resultados[] = "Índice codigo_descuento_id ya estaba eliminado";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    $errores[] = "Error eliminando índice: " . $e->getMessage();
}

echo "<hr>";

// 4. Eliminar columnas de orden
echo "<h2>4️⃣ Eliminando columnas innecesarias de orden...</h2>";
$columnas_eliminar = ['codigo_descuento_id', 'intentos_pago', 'fecha_ultima_subida'];

foreach ($columnas_eliminar as $columna) {
    try {
        // Verificar si existe la columna
        $check = $conn->query("SHOW COLUMNS FROM orden LIKE '$columna'");
        
        if ($check->num_rows > 0) {
            if ($conn->query("ALTER TABLE orden DROP COLUMN $columna")) {
                echo "<p style='color: green;'>✅ Columna orden.$columna eliminada</p>";
                $resultados[] = "Columna orden.$columna eliminada";
            } else {
                throw new Exception($conn->error);
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Columna orden.$columna no existe</p>";
            $resultados[] = "Columna orden.$columna ya estaba eliminada";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error eliminando $columna: " . $e->getMessage() . "</p>";
        $errores[] = "Error eliminando orden.$columna: " . $e->getMessage();
    }
}

echo "<hr>";

// 5. Eliminar columna cantidad de orden_detalle
echo "<h2>5️⃣ Eliminando columna cantidad de orden_detalle...</h2>";
try {
    $check = $conn->query("SHOW COLUMNS FROM orden_detalle LIKE 'cantidad'");
    
    if ($check->num_rows > 0) {
        if ($conn->query("ALTER TABLE orden_detalle DROP COLUMN cantidad")) {
            echo "<p style='color: green;'>✅ Columna orden_detalle.cantidad eliminada</p>";
            $resultados[] = "Columna orden_detalle.cantidad eliminada";
        } else {
            throw new Exception($conn->error);
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Columna orden_detalle.cantidad no existe</p>";
        $resultados[] = "Columna orden_detalle.cantidad ya estaba eliminada";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    $errores[] = "Error eliminando orden_detalle.cantidad: " . $e->getMessage();
}

echo "<hr>";

// 6. Modificar monto_pagado (DEFAULT NULL)
echo "<h2>6️⃣ Actualizando orden.monto_pagado...</h2>";
try {
    if ($conn->query("
        ALTER TABLE orden 
        MODIFY COLUMN monto_pagado decimal(10,2) DEFAULT NULL 
        COMMENT 'Monto real que pagó el cliente (puede ser menor al total esperado)'
    ")) {
        echo "<p style='color: green;'>✅ Campo orden.monto_pagado actualizado (DEFAULT NULL)</p>";
        $resultados[] = "Campo orden.monto_pagado actualizado";
    } else {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    $errores[] = "Error actualizando monto_pagado: " . $e->getMessage();
}

echo "<hr>";

// Resumen final
echo "<h2>📊 Resumen de Operaciones</h2>";

if (!empty($resultados)) {
    echo "<h3 style='color: green;'>✅ Operaciones Exitosas (" . count($resultados) . "):</h3>";
    echo "<ul>";
    foreach ($resultados as $resultado) {
        echo "<li>$resultado</li>";
    }
    echo "</ul>";
}

if (!empty($errores)) {
    echo "<h3 style='color: red;'>❌ Errores (" . count($errores) . "):</h3>";
    echo "<ul>";
    foreach ($errores as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 ¡Limpieza completada sin errores!</p>";
}

echo "<hr>";
echo "<h3>📝 Próximos Pasos:</h3>";
echo "<ol>";
echo "<li>Actualizar <code>finoso.sql</code> eliminando la tabla <code>descuento_aplicado_reloj</code></li>";
echo "<li>Exportar estructura limpia: <a href='exportar_estructura_sql.php'>Exportar SQL</a></li>";
echo "<li>Comparar con finoso.sql para verificar sincronización</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='index.html'>← Volver al Panel de Herramientas</a></p>";

$conn->close();
?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background: #f5f5f5;
    }
    h1 {
        color: #333;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
    }
    h2 {
        color: #667eea;
        border-bottom: 2px solid #667eea;
        padding-bottom: 10px;
    }
    h3 {
        color: #333;
    }
    code {
        background: #fff;
        padding: 2px 6px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-family: monospace;
    }
    a {
        color: #667eea;
        text-decoration: none;
        font-weight: bold;
    }
    a:hover {
        text-decoration: underline;
    }
    ul {
        line-height: 1.8;
    }
</style>

/**
 * LIMPIEZA COMPLETA DE BASE DE DATOS
 * Elimina todos los campos y tablas innecesarias identificadas
 */

require_once '../admin/conexion.php';

echo "<h1>🧹 Limpieza Completa de Base de Datos</h1>";
echo "<p>Eliminando campos y tablas innecesarias...</p>";
echo "<hr>";

$resultados = [];
$errores = [];

// 1. Eliminar tabla auditoria_pagos
echo "<h2>1️⃣ Eliminando tabla auditoria_pagos...</h2>";
try {
    $result = $conn->query("SHOW TABLES LIKE 'auditoria_pagos'");
    if ($result->num_rows > 0) {
        if ($conn->query("DROP TABLE auditoria_pagos")) {
            echo "<p style='color: green;'>✅ Tabla auditoria_pagos eliminada</p>";
            $resultados[] = "Tabla auditoria_pagos eliminada";
        } else {
            throw new Exception($conn->error);
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Tabla auditoria_pagos no existe</p>";
        $resultados[] = "Tabla auditoria_pagos ya estaba eliminada";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    $errores[] = "Error eliminando auditoria_pagos: " . $e->getMessage();
}

echo "<hr>";

// 2. Eliminar FK orden_ibfk_2
echo "<h2>2️⃣ Eliminando Foreign Key orden_ibfk_2...</h2>";
try {
    // Verificar si existe la FK
    $check = $conn->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND TABLE_NAME = 'orden' 
          AND CONSTRAINT_NAME = 'orden_ibfk_2'
    ");
    
    if ($check->num_rows > 0) {
        if ($conn->query("ALTER TABLE orden DROP FOREIGN KEY orden_ibfk_2")) {
            echo "<p style='color: green;'>✅ Foreign Key orden_ibfk_2 eliminada</p>";
            $resultados[] = "FK orden_ibfk_2 eliminada";
        } else {
            throw new Exception($conn->error);
        }
    } else {
        echo "<p style='color: orange;'>⚠️ FK orden_ibfk_2 no existe</p>";
        $resultados[] = "FK orden_ibfk_2 ya estaba eliminada";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    $errores[] = "Error eliminando FK: " . $e->getMessage();
}

echo "<hr>";

// 3. Eliminar índice codigo_descuento_id
echo "<h2>3️⃣ Eliminando índice codigo_descuento_id...</h2>";
try {
    $check = $conn->query("SHOW INDEX FROM orden WHERE Key_name = 'codigo_descuento_id'");
    
    if ($check->num_rows > 0) {
        if ($conn->query("ALTER TABLE orden DROP KEY codigo_descuento_id")) {
            echo "<p style='color: green;'>✅ Índice codigo_descuento_id eliminado</p>";
            $resultados[] = "Índice codigo_descuento_id eliminado";
        } else {
            throw new Exception($conn->error);
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Índice codigo_descuento_id no existe</p>";
        $resultados[] = "Índice codigo_descuento_id ya estaba eliminado";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    $errores[] = "Error eliminando índice: " . $e->getMessage();
}

echo "<hr>";

// 4. Eliminar columnas de orden
echo "<h2>4️⃣ Eliminando columnas innecesarias de orden...</h2>";
$columnas_eliminar = ['codigo_descuento_id', 'intentos_pago', 'fecha_ultima_subida'];

foreach ($columnas_eliminar as $columna) {
    try {
        // Verificar si existe la columna
        $check = $conn->query("SHOW COLUMNS FROM orden LIKE '$columna'");
        
        if ($check->num_rows > 0) {
            if ($conn->query("ALTER TABLE orden DROP COLUMN $columna")) {
                echo "<p style='color: green;'>✅ Columna orden.$columna eliminada</p>";
                $resultados[] = "Columna orden.$columna eliminada";
            } else {
                throw new Exception($conn->error);
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Columna orden.$columna no existe</p>";
            $resultados[] = "Columna orden.$columna ya estaba eliminada";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error eliminando $columna: " . $e->getMessage() . "</p>";
        $errores[] = "Error eliminando orden.$columna: " . $e->getMessage();
    }
}

echo "<hr>";

// 5. Eliminar columna cantidad de orden_detalle
echo "<h2>5️⃣ Eliminando columna cantidad de orden_detalle...</h2>";
try {
    $check = $conn->query("SHOW COLUMNS FROM orden_detalle LIKE 'cantidad'");
    
    if ($check->num_rows > 0) {
        if ($conn->query("ALTER TABLE orden_detalle DROP COLUMN cantidad")) {
            echo "<p style='color: green;'>✅ Columna orden_detalle.cantidad eliminada</p>";
            $resultados[] = "Columna orden_detalle.cantidad eliminada";
        } else {
            throw new Exception($conn->error);
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Columna orden_detalle.cantidad no existe</p>";
        $resultados[] = "Columna orden_detalle.cantidad ya estaba eliminada";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    $errores[] = "Error eliminando orden_detalle.cantidad: " . $e->getMessage();
}

echo "<hr>";

// 6. Modificar monto_pagado (DEFAULT NULL)
echo "<h2>6️⃣ Actualizando orden.monto_pagado...</h2>";
try {
    if ($conn->query("
        ALTER TABLE orden 
        MODIFY COLUMN monto_pagado decimal(10,2) DEFAULT NULL 
        COMMENT 'Monto real que pagó el cliente (puede ser menor al total esperado)'
    ")) {
        echo "<p style='color: green;'>✅ Campo orden.monto_pagado actualizado (DEFAULT NULL)</p>";
        $resultados[] = "Campo orden.monto_pagado actualizado";
    } else {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    $errores[] = "Error actualizando monto_pagado: " . $e->getMessage();
}

echo "<hr>";

// Resumen final
echo "<h2>📊 Resumen de Operaciones</h2>";

if (!empty($resultados)) {
    echo "<h3 style='color: green;'>✅ Operaciones Exitosas (" . count($resultados) . "):</h3>";
    echo "<ul>";
    foreach ($resultados as $resultado) {
        echo "<li>$resultado</li>";
    }
    echo "</ul>";
}

if (!empty($errores)) {
    echo "<h3 style='color: red;'>❌ Errores (" . count($errores) . "):</h3>";
    echo "<ul>";
    foreach ($errores as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 ¡Limpieza completada sin errores!</p>";
}

echo "<hr>";
echo "<h3>📝 Próximos Pasos:</h3>";
echo "<ol>";
echo "<li>Actualizar <code>finoso.sql</code> eliminando la tabla <code>descuento_aplicado_reloj</code></li>";
echo "<li>Exportar estructura limpia: <a href='exportar_estructura_sql.php'>Exportar SQL</a></li>";
echo "<li>Comparar con finoso.sql para verificar sincronización</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='index.html'>← Volver al Panel de Herramientas</a></p>";

$conn->close();
?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background: #f5f5f5;
    }
    h1 {
        color: #333;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
    }
    h2 {
        color: #667eea;
        border-bottom: 2px solid #667eea;
        padding-bottom: 10px;
    }
    h3 {
        color: #333;
    }
    code {
        background: #fff;
        padding: 2px 6px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-family: monospace;
    }
    a {
        color: #667eea;
        text-decoration: none;
        font-weight: bold;
    }
    a:hover {
        text-decoration: underline;
    }
    ul {
        line-height: 1.8;
    }
</style>

