<?php
/**
 * SCRIPT DE ACTUALIZACIÓN - usuario_codigo_descuento
 * Ejecutar UNA SOLA VEZ
 * 
 * Agrega campo id_reloj y elimina tabla temporal
 */

// Incluir conexión
require_once '../admin/conexion.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><style>
body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
table { border-collapse: collapse; width: 100%; margin-top: 20px; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
.highlight { background-color: #90EE90; }
</style></head><body>";

echo "<h2>🔧 Actualizando estructura de usuario_codigo_descuento...</h2>";

$success_count = 0;
$error_count = 0;

// PASO 1: Vaciar tabla usuario_codigo_descuento
echo "<h3>PASO 1: Vaciando tabla usuario_codigo_descuento</h3>";
try {
    $conn->query("TRUNCATE TABLE usuario_codigo_descuento");
    echo "<p class='success'>✅ Tabla vaciada correctamente</p>";
    $success_count++;
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    $error_count++;
}

// PASO 2: Verificar si el campo ya existe
echo "<h3>PASO 2: Verificando campo id_reloj</h3>";
$check_column = $conn->query("SHOW COLUMNS FROM usuario_codigo_descuento LIKE 'id_reloj'");
if ($check_column && $check_column->num_rows > 0) {
    echo "<p class='warning'>⚠️ El campo id_reloj ya existe, omitiendo...</p>";
    $success_count++;
} else {
    // PASO 2A: Agregar campo id_reloj
    echo "<p>Agregando campo id_reloj...</p>";
    try {
        $conn->query("ALTER TABLE usuario_codigo_descuento ADD COLUMN id_reloj INT(11) DEFAULT NULL AFTER id_codigo");
        echo "<p class='success'>✅ Campo id_reloj agregado correctamente</p>";
        $success_count++;
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error al agregar campo: " . $e->getMessage() . "</p>";
        $error_count++;
    }
}

// PASO 3: Verificar si la foreign key ya existe
echo "<h3>PASO 3: Verificando foreign key</h3>";
$check_fk = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                          WHERE TABLE_NAME = 'usuario_codigo_descuento' 
                          AND CONSTRAINT_NAME = 'fk_usuario_codigo_reloj'");
if ($check_fk && $check_fk->num_rows > 0) {
    echo "<p class='warning'>⚠️ La foreign key ya existe, omitiendo...</p>";
    $success_count++;
} else {
    // PASO 3A: Agregar foreign key
    echo "<p>Agregando foreign key...</p>";
    try {
        $conn->query("ALTER TABLE usuario_codigo_descuento 
                      ADD CONSTRAINT fk_usuario_codigo_reloj 
                      FOREIGN KEY (id_reloj) REFERENCES reloj(id_reloj) 
                      ON DELETE SET NULL");
        echo "<p class='success'>✅ Foreign key agregada correctamente</p>";
        $success_count++;
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error al agregar foreign key: " . $e->getMessage() . "</p>";
        $error_count++;
    }
}

// PASO 4: Eliminar tabla descuento_aplicado_reloj si existe
echo "<h3>PASO 4: Eliminando tabla temporal descuento_aplicado_reloj</h3>";
$check_table = $conn->query("SHOW TABLES LIKE 'descuento_aplicado_reloj'");
if ($check_table && $check_table->num_rows > 0) {
    try {
        $conn->query("DROP TABLE descuento_aplicado_reloj");
        echo "<p class='success'>✅ Tabla descuento_aplicado_reloj eliminada correctamente</p>";
        $success_count++;
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error al eliminar tabla: " . $e->getMessage() . "</p>";
        $error_count++;
    }
} else {
    echo "<p class='warning'>⚠️ La tabla descuento_aplicado_reloj no existe, omitiendo...</p>";
    $success_count++;
}

echo "<hr>";
echo "<h3>📊 Resumen:</h3>";
echo "<p>✅ Operaciones exitosas: <strong>$success_count</strong></p>";
echo "<p>⚠️ Advertencias: <strong>$error_count</strong></p>";

// Verificar que el campo se agregó correctamente
$result = $conn->query("SHOW COLUMNS FROM usuario_codigo_descuento LIKE 'id_reloj'");

if ($result && $result->num_rows > 0) {
    echo "<p style='color: green; font-size: 18px;'><strong>🎉 ¡Actualización completada exitosamente!</strong></p>";
    echo "<p>El campo <code>id_reloj</code> fue agregado correctamente a la tabla <code>usuario_codigo_descuento</code></p>";
    
    // Mostrar estructura actualizada
    echo "<h3>📋 Estructura actualizada:</h3>";
    $columns = $conn->query("SHOW COLUMNS FROM usuario_codigo_descuento");
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
    while ($col = $columns->fetch_assoc()) {
        $is_new = $col['Field'] === 'id_reloj' ? " style='background: #90EE90;'" : "";
        echo "<tr$is_new>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>✅ Ahora puedes usar el sistema de códigos correctamente</strong></p>";
    
    // Verificar si se eliminó la tabla temporal
    $check_tabla = $conn->query("SHOW TABLES LIKE 'descuento_aplicado_reloj'");
    if ($check_tabla && $check_tabla->num_rows === 0) {
        echo "<p class='success'>✅ La tabla temporal descuento_aplicado_reloj fue eliminada</p>";
    } else {
        echo "<p class='warning'>⚠️ La tabla descuento_aplicado_reloj aún existe</p>";
    }
} else {
    echo "<p class='error'>❌ Error: El campo id_reloj no se pudo agregar</p>";
    echo "<p>Por favor, ejecuta manualmente los siguientes comandos SQL:</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo "TRUNCATE TABLE usuario_codigo_descuento;\n";
    echo "ALTER TABLE usuario_codigo_descuento ADD COLUMN id_reloj INT(11) DEFAULT NULL AFTER id_codigo;\n";
    echo "ALTER TABLE usuario_codigo_descuento ADD CONSTRAINT fk_usuario_codigo_reloj FOREIGN KEY (id_reloj) REFERENCES reloj(id_reloj) ON DELETE SET NULL;\n";
    echo "DROP TABLE IF EXISTS descuento_aplicado_reloj;";
    echo "</pre>";
}

$conn->close();
echo "</body></html>";
?>


 * SCRIPT DE ACTUALIZACIÓN - usuario_codigo_descuento
 * Ejecutar UNA SOLA VEZ
 * 
 * Agrega campo id_reloj y elimina tabla temporal
 */

// Incluir conexión
require_once '../admin/conexion.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><style>
body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
table { border-collapse: collapse; width: 100%; margin-top: 20px; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
.highlight { background-color: #90EE90; }
</style></head><body>";

echo "<h2>🔧 Actualizando estructura de usuario_codigo_descuento...</h2>";

$success_count = 0;
$error_count = 0;

// PASO 1: Vaciar tabla usuario_codigo_descuento
echo "<h3>PASO 1: Vaciando tabla usuario_codigo_descuento</h3>";
try {
    $conn->query("TRUNCATE TABLE usuario_codigo_descuento");
    echo "<p class='success'>✅ Tabla vaciada correctamente</p>";
    $success_count++;
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    $error_count++;
}

// PASO 2: Verificar si el campo ya existe
echo "<h3>PASO 2: Verificando campo id_reloj</h3>";
$check_column = $conn->query("SHOW COLUMNS FROM usuario_codigo_descuento LIKE 'id_reloj'");
if ($check_column && $check_column->num_rows > 0) {
    echo "<p class='warning'>⚠️ El campo id_reloj ya existe, omitiendo...</p>";
    $success_count++;
} else {
    // PASO 2A: Agregar campo id_reloj
    echo "<p>Agregando campo id_reloj...</p>";
    try {
        $conn->query("ALTER TABLE usuario_codigo_descuento ADD COLUMN id_reloj INT(11) DEFAULT NULL AFTER id_codigo");
        echo "<p class='success'>✅ Campo id_reloj agregado correctamente</p>";
        $success_count++;
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error al agregar campo: " . $e->getMessage() . "</p>";
        $error_count++;
    }
}

// PASO 3: Verificar si la foreign key ya existe
echo "<h3>PASO 3: Verificando foreign key</h3>";
$check_fk = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                          WHERE TABLE_NAME = 'usuario_codigo_descuento' 
                          AND CONSTRAINT_NAME = 'fk_usuario_codigo_reloj'");
if ($check_fk && $check_fk->num_rows > 0) {
    echo "<p class='warning'>⚠️ La foreign key ya existe, omitiendo...</p>";
    $success_count++;
} else {
    // PASO 3A: Agregar foreign key
    echo "<p>Agregando foreign key...</p>";
    try {
        $conn->query("ALTER TABLE usuario_codigo_descuento 
                      ADD CONSTRAINT fk_usuario_codigo_reloj 
                      FOREIGN KEY (id_reloj) REFERENCES reloj(id_reloj) 
                      ON DELETE SET NULL");
        echo "<p class='success'>✅ Foreign key agregada correctamente</p>";
        $success_count++;
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error al agregar foreign key: " . $e->getMessage() . "</p>";
        $error_count++;
    }
}

// PASO 4: Eliminar tabla descuento_aplicado_reloj si existe
echo "<h3>PASO 4: Eliminando tabla temporal descuento_aplicado_reloj</h3>";
$check_table = $conn->query("SHOW TABLES LIKE 'descuento_aplicado_reloj'");
if ($check_table && $check_table->num_rows > 0) {
    try {
        $conn->query("DROP TABLE descuento_aplicado_reloj");
        echo "<p class='success'>✅ Tabla descuento_aplicado_reloj eliminada correctamente</p>";
        $success_count++;
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error al eliminar tabla: " . $e->getMessage() . "</p>";
        $error_count++;
    }
} else {
    echo "<p class='warning'>⚠️ La tabla descuento_aplicado_reloj no existe, omitiendo...</p>";
    $success_count++;
}

echo "<hr>";
echo "<h3>📊 Resumen:</h3>";
echo "<p>✅ Operaciones exitosas: <strong>$success_count</strong></p>";
echo "<p>⚠️ Advertencias: <strong>$error_count</strong></p>";

// Verificar que el campo se agregó correctamente
$result = $conn->query("SHOW COLUMNS FROM usuario_codigo_descuento LIKE 'id_reloj'");

if ($result && $result->num_rows > 0) {
    echo "<p style='color: green; font-size: 18px;'><strong>🎉 ¡Actualización completada exitosamente!</strong></p>";
    echo "<p>El campo <code>id_reloj</code> fue agregado correctamente a la tabla <code>usuario_codigo_descuento</code></p>";
    
    // Mostrar estructura actualizada
    echo "<h3>📋 Estructura actualizada:</h3>";
    $columns = $conn->query("SHOW COLUMNS FROM usuario_codigo_descuento");
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
    while ($col = $columns->fetch_assoc()) {
        $is_new = $col['Field'] === 'id_reloj' ? " style='background: #90EE90;'" : "";
        echo "<tr$is_new>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>✅ Ahora puedes usar el sistema de códigos correctamente</strong></p>";
    
    // Verificar si se eliminó la tabla temporal
    $check_tabla = $conn->query("SHOW TABLES LIKE 'descuento_aplicado_reloj'");
    if ($check_tabla && $check_tabla->num_rows === 0) {
        echo "<p class='success'>✅ La tabla temporal descuento_aplicado_reloj fue eliminada</p>";
    } else {
        echo "<p class='warning'>⚠️ La tabla descuento_aplicado_reloj aún existe</p>";
    }
} else {
    echo "<p class='error'>❌ Error: El campo id_reloj no se pudo agregar</p>";
    echo "<p>Por favor, ejecuta manualmente los siguientes comandos SQL:</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo "TRUNCATE TABLE usuario_codigo_descuento;\n";
    echo "ALTER TABLE usuario_codigo_descuento ADD COLUMN id_reloj INT(11) DEFAULT NULL AFTER id_codigo;\n";
    echo "ALTER TABLE usuario_codigo_descuento ADD CONSTRAINT fk_usuario_codigo_reloj FOREIGN KEY (id_reloj) REFERENCES reloj(id_reloj) ON DELETE SET NULL;\n";
    echo "DROP TABLE IF EXISTS descuento_aplicado_reloj;";
    echo "</pre>";
}

$conn->close();
echo "</body></html>";
?>

