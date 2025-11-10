<?php
/**
 * SCRIPT DE INSTALACIÓN - TABLA USUARIO_CODIGO_DESCUENTO
 * Ejecuta las queries para crear la tabla de relación usuarios-códigos
 * 
 * IMPORTANTE: Ejecutar solo UNA VEZ
 * URL: https://finoso.store/database/ejecutar_crear_usuario_codigo.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../admin/conexion.php';

// HTML de inicio
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Tabla Códigos Usuario</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2b2b2b 100%);
            color: #fff;
            padding: 40px 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid #FFCF66;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        h1 {
            color: #FFCF66;
            margin-bottom: 30px;
            text-align: center;
            font-size: 28px;
            letter-spacing: 2px;
        }
        .step {
            background: rgba(255, 255, 255, 0.03);
            border-left: 4px solid #FFCF66;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .step-title {
            color: #FFCF66;
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .step-content {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            padding-left: 30px;
        }
        .success {
            color: #4CAF50;
            background: rgba(76, 175, 80, 0.1);
            border-color: #4CAF50;
        }
        .error {
            color: #F44336;
            background: rgba(244, 67, 54, 0.1);
            border-color: #F44336;
        }
        .warning {
            color: #FFC107;
            background: rgba(255, 193, 7, 0.1);
            border-color: #FFC107;
        }
        .icon {
            font-size: 20px;
        }
        .code {
            background: rgba(0, 0, 0, 0.3);
            padding: 10px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin-top: 10px;
            overflow-x: auto;
        }
        .summary {
            margin-top: 30px;
            padding: 20px;
            background: rgba(255, 207, 102, 0.1);
            border: 2px solid #FFCF66;
            border-radius: 12px;
            text-align: center;
        }
        .summary h2 {
            color: #FFCF66;
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #FFCF66 0%, #FFB84D 100%);
            color: #1a1a1a;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 207, 102, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎟️ Instalación - Módulo de Códigos de Descuento</h1>
        
<?php

$errores = 0;
$exitosos = 0;

// PASO 1: Verificar si la tabla ya existe
echo '<div class="step">';
echo '<div class="step-title"><span class="icon">🔍</span> PASO 1: Verificando tabla existente...</div>';
echo '<div class="step-content">';

$check_table = "SHOW TABLES LIKE 'usuario_codigo_descuento'";
$result = $conn->query($check_table);

if ($result->num_rows > 0) {
    echo '<div class="warning">⚠️ La tabla <strong>usuario_codigo_descuento</strong> ya existe. Se omitirá la creación.</div>';
    $tabla_existe = true;
} else {
    echo '<div class="success">✓ La tabla no existe. Procediendo a crearla...</div>';
    $tabla_existe = false;
}

echo '</div></div>';

// PASO 2: Crear tabla (solo si no existe)
if (!$tabla_existe) {
    echo '<div class="step">';
    echo '<div class="step-title"><span class="icon">📦</span> PASO 2: Creando tabla usuario_codigo_descuento...</div>';
    echo '<div class="step-content">';
    
    $create_table = "
    CREATE TABLE `usuario_codigo_descuento` (
      `id_usuario_codigo` int(11) NOT NULL AUTO_INCREMENT,
      `id_usuario` int(11) NOT NULL,
      `id_codigo` int(11) NOT NULL,
      `fecha_asignado` datetime DEFAULT current_timestamp(),
      `fecha_usado` datetime DEFAULT NULL,
      `id_orden` int(11) DEFAULT NULL,
      `veces_usado` int(11) DEFAULT 0,
      `activo` tinyint(1) DEFAULT 1,
      `notas` text DEFAULT NULL,
      PRIMARY KEY (`id_usuario_codigo`),
      UNIQUE KEY `unico_usuario_codigo` (`id_usuario`,`id_codigo`),
      KEY `id_usuario` (`id_usuario`),
      KEY `id_codigo` (`id_codigo`),
      KEY `id_orden` (`id_orden`),
      KEY `idx_activo` (`activo`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($create_table)) {
        echo '<div class="success">✓ Tabla creada exitosamente</div>';
        $exitosos++;
    } else {
        echo '<div class="error">✗ Error al crear tabla: ' . $conn->error . '</div>';
        $errores++;
    }
    
    echo '</div></div>';
} else {
    echo '<div class="step warning">';
    echo '<div class="step-title"><span class="icon">⏭️</span> PASO 2: Omitido (tabla ya existe)</div>';
    echo '</div>';
}

// PASO 3: Agregar Foreign Keys (solo si la tabla es nueva)
if (!$tabla_existe) {
    echo '<div class="step">';
    echo '<div class="step-title"><span class="icon">🔗</span> PASO 3: Agregando relaciones (Foreign Keys)...</div>';
    echo '<div class="step-content">';
    
    $foreign_keys = [
        "ALTER TABLE `usuario_codigo_descuento` 
         ADD CONSTRAINT `usuario_codigo_ibfk_1` 
         FOREIGN KEY (`id_usuario`) 
         REFERENCES `usuario` (`id_usuario`) 
         ON DELETE CASCADE",
        
        "ALTER TABLE `usuario_codigo_descuento` 
         ADD CONSTRAINT `usuario_codigo_ibfk_2` 
         FOREIGN KEY (`id_codigo`) 
         REFERENCES `codigo_descuento` (`id_codigo`) 
         ON DELETE CASCADE",
        
        "ALTER TABLE `usuario_codigo_descuento` 
         ADD CONSTRAINT `usuario_codigo_ibfk_3` 
         FOREIGN KEY (`id_orden`) 
         REFERENCES `orden` (`id_orden`) 
         ON DELETE SET NULL"
    ];
    
    foreach ($foreign_keys as $index => $fk) {
        $fk_num = $index + 1;
        if ($conn->query($fk)) {
            echo "<div class='success'>✓ Foreign Key {$fk_num}/3 agregada</div>";
            $exitosos++;
        } else {
            echo "<div class='error'>✗ Error en Foreign Key {$fk_num}: " . $conn->error . "</div>";
            $errores++;
        }
    }
    
    echo '</div></div>';
} else {
    echo '<div class="step warning">';
    echo '<div class="step-title"><span class="icon">⏭️</span> PASO 3: Omitido (tabla ya existe)</div>';
    echo '</div>';
}

// PASO 4: Verificar estructura final
echo '<div class="step">';
echo '<div class="step-title"><span class="icon">✅</span> PASO 4: Verificando estructura final...</div>';
echo '<div class="step-content">';

$describe = "DESCRIBE usuario_codigo_descuento";
$result = $conn->query($describe);

if ($result && $result->num_rows > 0) {
    echo '<div class="success">✓ Tabla verificada correctamente. Columnas encontradas: ' . $result->num_rows . '</div>';
    echo '<div class="code">';
    echo "Estructura de la tabla:<br><br>";
    echo "<table style='width:100%; color: #FFCF66; border-collapse: collapse;'>";
    echo "<tr style='border-bottom: 1px solid rgba(255,207,102,0.3);'><th style='text-align:left; padding:5px;'>Campo</th><th style='text-align:left; padding:5px;'>Tipo</th><th style='text-align:left; padding:5px;'>Null</th><th style='text-align:left; padding:5px;'>Key</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr style='border-bottom: 1px solid rgba(255,207,102,0.1);'>";
        echo "<td style='padding:5px;'>{$row['Field']}</td>";
        echo "<td style='padding:5px;'>{$row['Type']}</td>";
        echo "<td style='padding:5px;'>{$row['Null']}</td>";
        echo "<td style='padding:5px;'>{$row['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo '</div>';
    $exitosos++;
} else {
    echo '<div class="error">✗ Error al verificar la tabla</div>';
    $errores++;
}

echo '</div></div>';

// PASO 5: Crear datos de prueba (opcional)
echo '<div class="step">';
echo '<div class="step-title"><span class="icon">🧪</span> PASO 5: Ejemplo de uso</div>';
echo '<div class="step-content">';
echo '<p>Para asignar un código a un usuario, usa esta query SQL:</p>';
echo '<div class="code">';
echo "-- Asignar código de descuento a un usuario<br>";
echo "INSERT INTO usuario_codigo_descuento (id_usuario, id_codigo, notas)<br>";
echo "VALUES (1, 1, 'Código de bienvenida 🎉');<br><br>";
echo "-- O asignar a múltiples usuarios<br>";
echo "INSERT INTO usuario_codigo_descuento (id_usuario, id_codigo, notas)<br>";
echo "SELECT id_usuario, 1, 'Promoción especial'<br>";
echo "FROM usuario<br>";
echo "WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY);";
echo '</div>';
echo '</div></div>';

// Resumen final
echo '<div class="summary">';
echo '<h2>📊 Resumen de Instalación</h2>';

if ($errores === 0) {
    echo '<div style="font-size: 48px; margin: 20px 0;">✅</div>';
    echo '<p style="font-size: 18px; color: #4CAF50; margin-bottom: 10px;"><strong>¡Instalación Completada Exitosamente!</strong></p>';
    echo '<p style="color: rgba(255,255,255,0.8);">Operaciones exitosas: <strong>' . $exitosos . '</strong></p>';
    echo '<p style="color: rgba(255,255,255,0.8); margin-top: 20px;">El módulo de códigos de descuento está listo para usar.</p>';
    echo '<a href="../perfil/perfil.html" class="btn">Ir al Perfil de Usuario</a>';
} else {
    echo '<div style="font-size: 48px; margin: 20px 0;">⚠️</div>';
    echo '<p style="font-size: 18px; color: #FFC107; margin-bottom: 10px;"><strong>Instalación con Advertencias</strong></p>';
    echo '<p style="color: rgba(255,255,255,0.8);">Exitosas: <strong>' . $exitosos . '</strong> | Errores: <strong style="color: #F44336;">' . $errores . '</strong></p>';
    echo '<p style="color: rgba(255,255,255,0.8); margin-top: 20px;">Revisa los errores arriba antes de continuar.</p>';
}

echo '</div>';

$conn->close();
?>

        <div style="margin-top: 30px; padding: 20px; background: rgba(0,0,0,0.2); border-radius: 10px; font-size: 12px; color: rgba(255,255,255,0.5); text-align: center;">
            <p>⚠️ <strong>IMPORTANTE:</strong> Este script solo debe ejecutarse UNA VEZ.</p>
            <p style="margin-top: 10px;">Si necesitas volver a ejecutarlo, primero elimina la tabla manualmente desde phpMyAdmin.</p>
            <p style="margin-top: 15px; color: #FFCF66;">FINOSO - Sistema de Gestión © 2025</p>
        </div>
    </div>
</body>
</html>

