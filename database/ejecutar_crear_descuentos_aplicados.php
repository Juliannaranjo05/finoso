<?php
/**
 * SCRIPT DE INSTALACIÓN - Tabla descuento_aplicado_reloj
 * Ejecutar una sola vez para crear la tabla
 */

require_once '../admin/conexion.php';

$errores = [];
$exitos = [];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Tabla - Descuentos Aplicados</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 800px;
            width: 100%;
        }
        h1 {
            color: #2d3748;
            text-align: center;
            margin-bottom: 30px;
        }
        .status-box {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .success {
            background: #f0fff4;
            border-color: #48bb78;
            color: #22543d;
        }
        .error {
            background: #fff5f5;
            border-color: #f56565;
            color: #742a2a;
        }
        .info {
            background: #f7fafc;
            border-color: #4299e1;
            color: #2c5282;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #FFCF66 0%, #FFB84D 100%);
            color: #2d3748;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 10px 5px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 207, 102, 0.4);
        }
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        code {
            background: #2d3748;
            color: #48bb78;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 Instalación: Tabla de Descuentos Aplicados</h1>

        <?php
        // Verificar si la tabla ya existe
        $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'descuento_aplicado_reloj'");
        $tabla_existe = mysqli_num_rows($check_table) > 0;

        if ($tabla_existe) {
            echo '<div class="status-box info">';
            echo '<strong>ℹ️ La tabla ya existe</strong><br>';
            echo 'La tabla <code>descuento_aplicado_reloj</code> ya está creada en la base de datos.';
            echo '</div>';
        } else {
            // Leer el archivo SQL
            $sql_file = __DIR__ . '/crear_descuentos_aplicados.sql';
            $sql = file_get_contents($sql_file);

            // Ejecutar la creación de la tabla
            if (mysqli_query($conn, $sql)) {
                array_push($exitos, '✅ Tabla <code>descuento_aplicado_reloj</code> creada exitosamente');
                
                echo '<div class="status-box success">';
                echo '<strong>✅ Instalación Exitosa</strong><br>';
                echo 'La tabla <code>descuento_aplicado_reloj</code> se creó correctamente.';
                echo '</div>';
            } else {
                array_push($errores, '❌ Error al crear tabla: ' . mysqli_error($conn));
                
                echo '<div class="status-box error">';
                echo '<strong>❌ Error en la Instalación</strong><br>';
                echo 'Error: ' . mysqli_error($conn);
                echo '</div>';
            }
        }

        // Mostrar estructura de la tabla
        echo '<div class="status-box info">';
        echo '<h3 style="margin-bottom: 10px;">📊 Estructura de la Tabla:</h3>';
        echo '<ul style="margin-left: 20px; line-height: 1.8;">';
        echo '<li><code>id_descuento_aplicado</code> - ID único</li>';
        echo '<li><code>id_usuario</code> - Usuario que aplicó el descuento</li>';
        echo '<li><code>id_reloj</code> - Reloj al que se aplicó</li>';
        echo '<li><code>id_codigo</code> - Código de descuento usado</li>';
        echo '<li><code>precio_original</code> - Precio antes del descuento</li>';
        echo '<li><code>porcentaje_descuento</code> - % de descuento (10.00)</li>';
        echo '<li><code>precio_con_descuento</code> - Precio final</li>';
        echo '<li><code>fecha_aplicado</code> - Cuándo se aplicó</li>';
        echo '<li><code>expira_en</code> - Expira en 24 horas</li>';
        echo '<li><code>usado_en_orden</code> - Orden donde se usó (NULL = pendiente)</li>';
        echo '</ul>';
        echo '</div>';

        echo '<div class="status-box info">';
        echo '<h3 style="margin-bottom: 10px;">💡 ¿Para qué sirve?</h3>';
        echo '<p style="line-height: 1.6;">Esta tabla guarda qué descuentos ha aplicado cada usuario a cada reloj.</p>';
        echo '<p style="line-height: 1.6; margin-top: 10px;"><strong>Ejemplo:</strong></p>';
        echo '<ul style="margin-left: 20px; line-height: 1.8; margin-top: 10px;">';
        echo '<li>Usuario Juan aplica código FIN123ABC al reloj #5</li>';
        echo '<li>Precio original: $125.000 → Precio con descuento: $112.500</li>';
        echo '<li>Si Juan vuelve a entrar al reloj #5, ve el precio con descuento automáticamente</li>';
        echo '<li>Si Juan completa la compra, se marca como "usado"</li>';
        echo '<li>Si pasan 24 horas sin comprar, el descuento expira</li>';
        echo '</ul>';
        echo '</div>';

        mysqli_close($conn);
        ?>

        <div class="actions">
            <a href="../informacion/informacion.html?id=1" class="btn">🛍️ Probar con un Reloj</a>
            <a href="../admin/panel.php" class="btn">← Volver al Admin</a>
        </div>
    </div>
</body>
</html>

