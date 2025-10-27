<?php
/**
 * EXPORTADOR DE ESTRUCTURA SQL
 * Genera un archivo SQL con SOLO la estructura (sin datos) de la BD actual
 * Para comparar con finoso.sql
 */

require_once '../admin/conexion.php';

// Configuración
$output_file = 'estructura_actual_' . date('Y-m-d_H-i-s') . '.sql';

// Obtener nombre de la BD
$db_name_result = $conn->query("SELECT DATABASE()");
$db_name = $db_name_result->fetch_row()[0];

// Obtener todas las tablas
$tables_result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $tables_result->fetch_row()) {
    $tables[] = $row[0];
}

sort($tables);

// Iniciar el archivo SQL
$sql_content = "-- Estructura de Base de Datos: $db_name\n";
$sql_content .= "-- Generado: " . date('Y-m-d H:i:s') . "\n";
$sql_content .= "-- Tablas: " . count($tables) . "\n\n";

$sql_content .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$sql_content .= "START TRANSACTION;\n";
$sql_content .= "SET time_zone = \"+00:00\";\n\n";

$sql_content .= "-- --------------------------------------------------------\n\n";

// Para cada tabla, obtener su CREATE TABLE
foreach ($tables as $table) {
    $sql_content .= "--\n";
    $sql_content .= "-- Estructura de tabla para la tabla `$table`\n";
    $sql_content .= "--\n\n";
    
    // DROP TABLE IF EXISTS
    $sql_content .= "DROP TABLE IF EXISTS `$table`;\n";
    
    // CREATE TABLE
    $create_result = $conn->query("SHOW CREATE TABLE `$table`");
    $create_row = $create_result->fetch_assoc();
    $sql_content .= $create_row['Create Table'] . ";\n\n";
    
    $sql_content .= "-- --------------------------------------------------------\n\n";
}

// Guardar el archivo
$file_path = __DIR__ . '/' . $output_file;
file_put_contents($file_path, $sql_content);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estructura SQL Exportada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        code {
            background: #fff;
            padding: 2px 6px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-family: monospace;
        }
        a.button {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        a.button:hover {
            background: #0056b3;
        }
        .instructions {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin: 20px 0;
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        ol {
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <h1>✅ Estructura SQL Exportada</h1>
    
    <div class="success">
        <h2>🎉 Archivo generado exitosamente</h2>
        <p><strong>Archivo:</strong> <code><?php echo $output_file; ?></code></p>
        <p><strong>Ubicación:</strong> <code><?php echo $file_path; ?></code></p>
        <p><strong>Base de Datos:</strong> <code><?php echo $db_name; ?></code></p>
        <p><strong>Tablas exportadas:</strong> <?php echo count($tables); ?></p>
    </div>
    
    <div class="info">
        <h3>📋 Tablas incluidas:</h3>
        <ul>
            <?php foreach ($tables as $table): ?>
                <li><code><?php echo $table; ?></code></li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <div class="instructions">
        <h2>📝 Cómo Comparar con finoso.sql</h2>
        <ol>
            <li>
                <strong>Descarga el archivo generado:</strong><br>
                <a href="<?php echo $output_file; ?>" download class="button">⬇️ Descargar <?php echo $output_file; ?></a>
            </li>
            
            <li>
                <strong>Abre ambos archivos en un comparador de texto:</strong>
                <ul>
                    <li><strong>VS Code:</strong> Click derecho → "Select for Compare" en uno, luego "Compare with Selected" en el otro</li>
                    <li><strong>Online:</strong> <a href="https://www.diffchecker.com/" target="_blank">DiffChecker.com</a></li>
                    <li><strong>WinMerge:</strong> Descarga gratuita para Windows</li>
                </ul>
            </li>
            
            <li>
                <strong>Busca diferencias:</strong>
                <ul>
                    <li>🔴 <strong>Campos que están en BD pero NO en finoso.sql</strong> → Sobran, eliminarlos</li>
                    <li>🟢 <strong>Campos que están en finoso.sql pero NO en BD</strong> → Faltan, agregarlos</li>
                    <li>🟡 <strong>Tipos de dato diferentes</strong> → Corregir</li>
                    <li>🔵 <strong>Índices/Foreign Keys diferentes</strong> → Sincronizar</li>
                </ul>
            </li>
            
            <li>
                <strong>Actualiza finoso.sql o la BD según corresponda</strong>
            </li>
        </ol>
    </div>
    
    <div class="info">
        <h3>🔧 Herramientas Útiles:</h3>
        <a href="comparar_estructura_bd.php" class="button">👁️ Ver Estructura Detallada</a>
        <a href="../admin/debug_bd.php" class="button">🐛 Debug BD</a>
        <a href="." class="button">📂 Ver Archivos Database</a>
    </div>
    
    <div class="instructions">
        <h2>⚠️ Notas Importantes:</h2>
        <ul>
            <li>Este archivo <strong>NO contiene datos</strong>, solo estructura (CREATE TABLE)</li>
            <li>Las tablas que aparecen con <code>DROP TABLE IF EXISTS</code></li>
            <li>Incluye todas las columnas, índices, foreign keys y comentarios</li>
            <li>El archivo se guarda en <code>database/</code></li>
            <li><strong>NO ejecutes</strong> este SQL en producción sin revisarlo</li>
        </ul>
    </div>
    
    <?php
    // Mostrar un preview del archivo
    $preview = substr($sql_content, 0, 2000);
    ?>
    
    <div class="instructions">
        <h2>👀 Preview del archivo:</h2>
        <textarea style="width: 100%; height: 300px; font-family: monospace; font-size: 12px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"><?php echo htmlspecialchars($preview); ?></textarea>
        <p><em>... (continuación en el archivo descargado)</em></p>
    </div>
</body>
</html>

<?php
$conn->close();
?>

/**
 * EXPORTADOR DE ESTRUCTURA SQL
 * Genera un archivo SQL con SOLO la estructura (sin datos) de la BD actual
 * Para comparar con finoso.sql
 */

require_once '../admin/conexion.php';

// Configuración
$output_file = 'estructura_actual_' . date('Y-m-d_H-i-s') . '.sql';

// Obtener nombre de la BD
$db_name_result = $conn->query("SELECT DATABASE()");
$db_name = $db_name_result->fetch_row()[0];

// Obtener todas las tablas
$tables_result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $tables_result->fetch_row()) {
    $tables[] = $row[0];
}

sort($tables);

// Iniciar el archivo SQL
$sql_content = "-- Estructura de Base de Datos: $db_name\n";
$sql_content .= "-- Generado: " . date('Y-m-d H:i:s') . "\n";
$sql_content .= "-- Tablas: " . count($tables) . "\n\n";

$sql_content .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$sql_content .= "START TRANSACTION;\n";
$sql_content .= "SET time_zone = \"+00:00\";\n\n";

$sql_content .= "-- --------------------------------------------------------\n\n";

// Para cada tabla, obtener su CREATE TABLE
foreach ($tables as $table) {
    $sql_content .= "--\n";
    $sql_content .= "-- Estructura de tabla para la tabla `$table`\n";
    $sql_content .= "--\n\n";
    
    // DROP TABLE IF EXISTS
    $sql_content .= "DROP TABLE IF EXISTS `$table`;\n";
    
    // CREATE TABLE
    $create_result = $conn->query("SHOW CREATE TABLE `$table`");
    $create_row = $create_result->fetch_assoc();
    $sql_content .= $create_row['Create Table'] . ";\n\n";
    
    $sql_content .= "-- --------------------------------------------------------\n\n";
}

// Guardar el archivo
$file_path = __DIR__ . '/' . $output_file;
file_put_contents($file_path, $sql_content);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estructura SQL Exportada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        code {
            background: #fff;
            padding: 2px 6px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-family: monospace;
        }
        a.button {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        a.button:hover {
            background: #0056b3;
        }
        .instructions {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin: 20px 0;
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        ol {
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <h1>✅ Estructura SQL Exportada</h1>
    
    <div class="success">
        <h2>🎉 Archivo generado exitosamente</h2>
        <p><strong>Archivo:</strong> <code><?php echo $output_file; ?></code></p>
        <p><strong>Ubicación:</strong> <code><?php echo $file_path; ?></code></p>
        <p><strong>Base de Datos:</strong> <code><?php echo $db_name; ?></code></p>
        <p><strong>Tablas exportadas:</strong> <?php echo count($tables); ?></p>
    </div>
    
    <div class="info">
        <h3>📋 Tablas incluidas:</h3>
        <ul>
            <?php foreach ($tables as $table): ?>
                <li><code><?php echo $table; ?></code></li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <div class="instructions">
        <h2>📝 Cómo Comparar con finoso.sql</h2>
        <ol>
            <li>
                <strong>Descarga el archivo generado:</strong><br>
                <a href="<?php echo $output_file; ?>" download class="button">⬇️ Descargar <?php echo $output_file; ?></a>
            </li>
            
            <li>
                <strong>Abre ambos archivos en un comparador de texto:</strong>
                <ul>
                    <li><strong>VS Code:</strong> Click derecho → "Select for Compare" en uno, luego "Compare with Selected" en el otro</li>
                    <li><strong>Online:</strong> <a href="https://www.diffchecker.com/" target="_blank">DiffChecker.com</a></li>
                    <li><strong>WinMerge:</strong> Descarga gratuita para Windows</li>
                </ul>
            </li>
            
            <li>
                <strong>Busca diferencias:</strong>
                <ul>
                    <li>🔴 <strong>Campos que están en BD pero NO en finoso.sql</strong> → Sobran, eliminarlos</li>
                    <li>🟢 <strong>Campos que están en finoso.sql pero NO en BD</strong> → Faltan, agregarlos</li>
                    <li>🟡 <strong>Tipos de dato diferentes</strong> → Corregir</li>
                    <li>🔵 <strong>Índices/Foreign Keys diferentes</strong> → Sincronizar</li>
                </ul>
            </li>
            
            <li>
                <strong>Actualiza finoso.sql o la BD según corresponda</strong>
            </li>
        </ol>
    </div>
    
    <div class="info">
        <h3>🔧 Herramientas Útiles:</h3>
        <a href="comparar_estructura_bd.php" class="button">👁️ Ver Estructura Detallada</a>
        <a href="../admin/debug_bd.php" class="button">🐛 Debug BD</a>
        <a href="." class="button">📂 Ver Archivos Database</a>
    </div>
    
    <div class="instructions">
        <h2>⚠️ Notas Importantes:</h2>
        <ul>
            <li>Este archivo <strong>NO contiene datos</strong>, solo estructura (CREATE TABLE)</li>
            <li>Las tablas que aparecen con <code>DROP TABLE IF EXISTS</code></li>
            <li>Incluye todas las columnas, índices, foreign keys y comentarios</li>
            <li>El archivo se guarda en <code>database/</code></li>
            <li><strong>NO ejecutes</strong> este SQL en producción sin revisarlo</li>
        </ul>
    </div>
    
    <?php
    // Mostrar un preview del archivo
    $preview = substr($sql_content, 0, 2000);
    ?>
    
    <div class="instructions">
        <h2>👀 Preview del archivo:</h2>
        <textarea style="width: 100%; height: 300px; font-family: monospace; font-size: 12px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"><?php echo htmlspecialchars($preview); ?></textarea>
        <p><em>... (continuación en el archivo descargado)</em></p>
    </div>
</body>
</html>

<?php
$conn->close();
?>

