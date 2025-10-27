<?php
/**
 * COMPARADOR DE ESTRUCTURA DE BASE DE DATOS
 * Muestra la estructura actual de la BD para compararla con finoso.sql
 */

require_once '../admin/conexion.php';

// Obtener el nombre de la base de datos actual
$db_name_result = $conn->query("SELECT DATABASE()");
$db_name = $db_name_result->fetch_row()[0];

echo "<h1>🔍 Estructura de Base de Datos: <code>$db_name</code></h1>";
echo "<p>Compara esta estructura con <code>finoso.sql</code> para detectar diferencias</p>";
echo "<hr>";

// Obtener todas las tablas
$tables_result = $conn->query("SHOW TABLES");
$tables = [];

while ($row = $tables_result->fetch_row()) {
    $tables[] = $row[0];
}

sort($tables);

echo "<h2>📊 Resumen: " . count($tables) . " tablas encontradas</h2>";
echo "<ul>";
foreach ($tables as $table) {
    echo "<li><a href='#tabla-$table'>$table</a></li>";
}
echo "</ul>";
echo "<hr>";

// Para cada tabla, obtener su estructura completa
foreach ($tables as $table) {
    echo "<div id='tabla-$table' style='margin-bottom: 40px; border: 2px solid #ddd; padding: 20px; background: #f9f9f9;'>";
    echo "<h2>📋 Tabla: <code>$table</code></h2>";
    
    // 1. COLUMNAS
    echo "<h3>Columnas:</h3>";
    $columns_result = $conn->query("SHOW FULL COLUMNS FROM `$table`");
    
    echo "<table border='1' cellpadding='8' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #333; color: white;'>
            <th>Campo</th>
            <th>Tipo</th>
            <th>Null</th>
            <th>Key</th>
            <th>Default</th>
            <th>Extra</th>
            <th>Comment</th>
          </tr>";
    
    while ($col = $columns_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($col['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Comment']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. ÍNDICES
    echo "<h3>Índices:</h3>";
    $indexes_result = $conn->query("SHOW INDEXES FROM `$table`");
    
    $indexes = [];
    while ($idx = $indexes_result->fetch_assoc()) {
        $key_name = $idx['Key_name'];
        if (!isset($indexes[$key_name])) {
            $indexes[$key_name] = [
                'columns' => [],
                'unique' => $idx['Non_unique'] == 0 ? 'YES' : 'NO',
                'type' => $idx['Index_type']
            ];
        }
        $indexes[$key_name]['columns'][] = $idx['Column_name'];
    }
    
    if (!empty($indexes)) {
        echo "<table border='1' cellpadding='8' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #333; color: white;'>
                <th>Nombre del Índice</th>
                <th>Columnas</th>
                <th>Único</th>
                <th>Tipo</th>
              </tr>";
        
        foreach ($indexes as $name => $info) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($name) . "</strong></td>";
            echo "<td>" . implode(', ', $info['columns']) . "</td>";
            echo "<td>" . $info['unique'] . "</td>";
            echo "<td>" . $info['type'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p><em>No hay índices</em></p>";
    }
    
    // 3. FOREIGN KEYS
    echo "<h3>Foreign Keys:</h3>";
    $fk_query = "
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME,
            UPDATE_RULE,
            DELETE_RULE
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '$db_name'
          AND TABLE_NAME = '$table'
          AND REFERENCED_TABLE_NAME IS NOT NULL
    ";
    
    $fk_result = $conn->query($fk_query);
    
    if ($fk_result && $fk_result->num_rows > 0) {
        echo "<table border='1' cellpadding='8' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #333; color: white;'>
                <th>Constraint</th>
                <th>Columna Local</th>
                <th>Tabla Referenciada</th>
                <th>Columna Referenciada</th>
                <th>ON UPDATE</th>
                <th>ON DELETE</th>
              </tr>";
        
        while ($fk = $fk_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($fk['CONSTRAINT_NAME']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($fk['COLUMN_NAME']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($fk['REFERENCED_TABLE_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($fk['REFERENCED_COLUMN_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($fk['UPDATE_RULE']) . "</td>";
            echo "<td>" . htmlspecialchars($fk['DELETE_RULE']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p><em>No hay foreign keys</em></p>";
    }
    
    // 4. CREAR STATEMENT (para copiar)
    echo "<h3>CREATE TABLE Statement:</h3>";
    $create_result = $conn->query("SHOW CREATE TABLE `$table`");
    $create_row = $create_result->fetch_assoc();
    
    echo "<textarea style='width: 100%; height: 200px; font-family: monospace; font-size: 12px;'>";
    echo htmlspecialchars($create_row['Create Table']);
    echo "</textarea>";
    
    // 5. CONTAR REGISTROS
    $count_result = $conn->query("SELECT COUNT(*) as total FROM `$table`");
    $count = $count_result->fetch_assoc()['total'];
    echo "<p><strong>📊 Registros actuales:</strong> $count</p>";
    
    echo "</div>";
    echo "<hr>";
}

echo "<h2>✅ Análisis Completado</h2>";
echo "<p>Copia esta información y compárala con <code>finoso.sql</code></p>";
echo "<p><a href='#'>↑ Volver arriba</a></p>";

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
    }
    h2 {
        color: #0066cc;
        margin-top: 20px;
    }
    h3 {
        color: #006600;
        margin-top: 15px;
    }
    code {
        background: #fff;
        padding: 2px 6px;
        border: 1px solid #ddd;
        border-radius: 3px;
    }
    table {
        background: white;
        margin: 10px 0;
    }
    tr:nth-child(even) {
        background: #f2f2f2;
    }
    textarea {
        border: 1px solid #ccc;
        padding: 10px;
        border-radius: 5px;
    }
    a {
        color: #0066cc;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
</style>

/**
 * COMPARADOR DE ESTRUCTURA DE BASE DE DATOS
 * Muestra la estructura actual de la BD para compararla con finoso.sql
 */

require_once '../admin/conexion.php';

// Obtener el nombre de la base de datos actual
$db_name_result = $conn->query("SELECT DATABASE()");
$db_name = $db_name_result->fetch_row()[0];

echo "<h1>🔍 Estructura de Base de Datos: <code>$db_name</code></h1>";
echo "<p>Compara esta estructura con <code>finoso.sql</code> para detectar diferencias</p>";
echo "<hr>";

// Obtener todas las tablas
$tables_result = $conn->query("SHOW TABLES");
$tables = [];

while ($row = $tables_result->fetch_row()) {
    $tables[] = $row[0];
}

sort($tables);

echo "<h2>📊 Resumen: " . count($tables) . " tablas encontradas</h2>";
echo "<ul>";
foreach ($tables as $table) {
    echo "<li><a href='#tabla-$table'>$table</a></li>";
}
echo "</ul>";
echo "<hr>";

// Para cada tabla, obtener su estructura completa
foreach ($tables as $table) {
    echo "<div id='tabla-$table' style='margin-bottom: 40px; border: 2px solid #ddd; padding: 20px; background: #f9f9f9;'>";
    echo "<h2>📋 Tabla: <code>$table</code></h2>";
    
    // 1. COLUMNAS
    echo "<h3>Columnas:</h3>";
    $columns_result = $conn->query("SHOW FULL COLUMNS FROM `$table`");
    
    echo "<table border='1' cellpadding='8' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #333; color: white;'>
            <th>Campo</th>
            <th>Tipo</th>
            <th>Null</th>
            <th>Key</th>
            <th>Default</th>
            <th>Extra</th>
            <th>Comment</th>
          </tr>";
    
    while ($col = $columns_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($col['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Comment']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. ÍNDICES
    echo "<h3>Índices:</h3>";
    $indexes_result = $conn->query("SHOW INDEXES FROM `$table`");
    
    $indexes = [];
    while ($idx = $indexes_result->fetch_assoc()) {
        $key_name = $idx['Key_name'];
        if (!isset($indexes[$key_name])) {
            $indexes[$key_name] = [
                'columns' => [],
                'unique' => $idx['Non_unique'] == 0 ? 'YES' : 'NO',
                'type' => $idx['Index_type']
            ];
        }
        $indexes[$key_name]['columns'][] = $idx['Column_name'];
    }
    
    if (!empty($indexes)) {
        echo "<table border='1' cellpadding='8' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #333; color: white;'>
                <th>Nombre del Índice</th>
                <th>Columnas</th>
                <th>Único</th>
                <th>Tipo</th>
              </tr>";
        
        foreach ($indexes as $name => $info) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($name) . "</strong></td>";
            echo "<td>" . implode(', ', $info['columns']) . "</td>";
            echo "<td>" . $info['unique'] . "</td>";
            echo "<td>" . $info['type'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p><em>No hay índices</em></p>";
    }
    
    // 3. FOREIGN KEYS
    echo "<h3>Foreign Keys:</h3>";
    $fk_query = "
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME,
            UPDATE_RULE,
            DELETE_RULE
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '$db_name'
          AND TABLE_NAME = '$table'
          AND REFERENCED_TABLE_NAME IS NOT NULL
    ";
    
    $fk_result = $conn->query($fk_query);
    
    if ($fk_result && $fk_result->num_rows > 0) {
        echo "<table border='1' cellpadding='8' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #333; color: white;'>
                <th>Constraint</th>
                <th>Columna Local</th>
                <th>Tabla Referenciada</th>
                <th>Columna Referenciada</th>
                <th>ON UPDATE</th>
                <th>ON DELETE</th>
              </tr>";
        
        while ($fk = $fk_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($fk['CONSTRAINT_NAME']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($fk['COLUMN_NAME']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($fk['REFERENCED_TABLE_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($fk['REFERENCED_COLUMN_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($fk['UPDATE_RULE']) . "</td>";
            echo "<td>" . htmlspecialchars($fk['DELETE_RULE']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p><em>No hay foreign keys</em></p>";
    }
    
    // 4. CREAR STATEMENT (para copiar)
    echo "<h3>CREATE TABLE Statement:</h3>";
    $create_result = $conn->query("SHOW CREATE TABLE `$table`");
    $create_row = $create_result->fetch_assoc();
    
    echo "<textarea style='width: 100%; height: 200px; font-family: monospace; font-size: 12px;'>";
    echo htmlspecialchars($create_row['Create Table']);
    echo "</textarea>";
    
    // 5. CONTAR REGISTROS
    $count_result = $conn->query("SELECT COUNT(*) as total FROM `$table`");
    $count = $count_result->fetch_assoc()['total'];
    echo "<p><strong>📊 Registros actuales:</strong> $count</p>";
    
    echo "</div>";
    echo "<hr>";
}

echo "<h2>✅ Análisis Completado</h2>";
echo "<p>Copia esta información y compárala con <code>finoso.sql</code></p>";
echo "<p><a href='#'>↑ Volver arriba</a></p>";

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
    }
    h2 {
        color: #0066cc;
        margin-top: 20px;
    }
    h3 {
        color: #006600;
        margin-top: 15px;
    }
    code {
        background: #fff;
        padding: 2px 6px;
        border: 1px solid #ddd;
        border-radius: 3px;
    }
    table {
        background: white;
        margin: 10px 0;
    }
    tr:nth-child(even) {
        background: #f2f2f2;
    }
    textarea {
        border: 1px solid #ccc;
        padding: 10px;
        border-radius: 5px;
    }
    a {
        color: #0066cc;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
</style>

