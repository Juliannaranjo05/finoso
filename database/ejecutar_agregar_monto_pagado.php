<?php
/**
 * EJECUTAR SCRIPT PARA AGREGAR COLUMNAS A LA TABLA ORDEN
 * Ejecutar este archivo UNA SOLA VEZ desde el navegador
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexión a la base de datos
include '../admin/conexion.php';

echo "<h2>🔧 Agregando columnas a la tabla 'orden'...</h2>";

// Definir las consultas directamente aquí
$queries = [
    [
        'name' => 'monto_pagado',
        'sql' => "ALTER TABLE orden ADD COLUMN monto_pagado DECIMAL(10,2) DEFAULT 0 COMMENT 'Monto real que pagó el cliente (puede ser menor al total)'"
    ],
    [
        'name' => 'intentos_pago',
        'sql' => "ALTER TABLE orden ADD COLUMN intentos_pago INT DEFAULT 0 COMMENT 'Número de intentos de pago/resubida de comprobante'"
    ],
    [
        'name' => 'fecha_ultima_subida',
        'sql' => "ALTER TABLE orden ADD COLUMN fecha_ultima_subida DATETIME DEFAULT NULL COMMENT 'Última vez que se subió o actualizó el comprobante'"
    ]
];

$success = 0;
$errors = 0;

foreach ($queries as $queryInfo) {
    $name = $queryInfo['name'];
    $sql = $queryInfo['sql'];
    
    echo "<p>📝 Agregando columna: <strong>$name</strong></p>";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✅ Columna <strong>$name</strong> agregada exitosamente</p>";
        $success++;
    } else {
        // Si el error es "Duplicate column", está bien (ya existe)
        if (strpos($conn->error, 'Duplicate column') !== false) {
            echo "<p style='color: orange;'>⚠️ Columna <strong>$name</strong> ya existe (ok)</p>";
            $success++;
        } else {
            echo "<p style='color: red;'>❌ Error al agregar <strong>$name</strong>: " . htmlspecialchars($conn->error) . "</p>";
            $errors++;
        }
    }
}

echo "<hr>";
echo "<h3>📊 Resumen:</h3>";
echo "<p>✅ Columnas agregadas: <strong>$success</strong></p>";
echo "<p>❌ Errores: <strong>$errors</strong></p>";

if ($success === 3 && $errors === 0) {
    echo "<h2 style='color: green;'>🎉 ¡Base de datos actualizada correctamente!</h2>";
    echo "<p><strong>Las 3 columnas fueron agregadas exitosamente:</strong></p>";
    echo "<ul>";
    echo "<li>✅ <code>monto_pagado</code> - Para guardar el monto que el cliente realmente pagó</li>";
    echo "<li>✅ <code>intentos_pago</code> - Para contar intentos de pago</li>";
    echo "<li>✅ <code>fecha_ultima_subida</code> - Para registrar última actualización</li>";
    echo "</ul>";
    echo "<p><strong>Ya puedes usar el sistema de recuperación de órdenes.</strong></p>";
} else if ($success > 0 && $errors === 0) {
    echo "<h2 style='color: green;'>✅ Base de datos actualizada</h2>";
    echo "<p>Algunas columnas ya existían, pero todo está correcto.</p>";
} else {
    echo "<h2 style='color: red;'>⚠️ Algunos errores ocurrieron</h2>";
    echo "<p>Revisa los mensajes de error arriba.</p>";
}

// Verificar que las columnas existen
echo "<hr>";
echo "<h3>🔍 Verificación de columnas:</h3>";
$check_query = "SHOW COLUMNS FROM orden WHERE Field IN ('monto_pagado', 'intentos_pago', 'fecha_ultima_subida')";
$result = $conn->query($check_query);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr style='background: #f0f0f0;'><th style='padding: 8px;'>Campo</th><th style='padding: 8px;'>Tipo</th><th style='padding: 8px;'>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding: 8px;'><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($row['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p style='color: green;'>✅ <strong>Las columnas están presentes en la tabla</strong></p>";
} else {
    echo "<p style='color: red;'>❌ No se encontraron las columnas en la tabla</p>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Base de Datos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        code {
            background: #e0e0e0;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <hr>
    <p><a href="../admin/panel.php">← Volver al Panel de Admin</a></p>
    <p><a href="../perfil/perfil.html">← Ir al Perfil de Usuario</a></p>
</body>
</html>

