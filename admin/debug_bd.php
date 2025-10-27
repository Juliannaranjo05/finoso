<?php
/**
 * DEBUG - VISOR COMPLETO DE BASE DE DATOS
 * Muestra todas las tablas y sus registros para depuración
 */

include 'conexion.php';

// Obtener todas las tablas
$tablas = [
    'carrito',
    'codigo_descuento',
    'comentarios',
    'envios',
    'marca',
    'orden',
    'orden_detalle',
    'reloj',
    'reset_tokens',
    'usuario',
    'usuario_codigo_descuento'
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Debug Base de Datos - FINOSO</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .table-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .record-count {
            background: #667eea;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 0.9rem;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .empty-table {
            text-align: center;
            padding: 30px;
            color: #999;
            font-style: italic;
        }

        .error {
            background: #ff4444;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        /* Colores especiales para estados */
        .estado-pendiente { background: #fff3cd; color: #856404; padding: 3px 8px; border-radius: 4px; font-weight: 600; }
        .estado-pagado { background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-weight: 600; }
        .estado-aprobado { background: #d1ecf1; color: #0c5460; padding: 3px 8px; border-radius: 4px; font-weight: 600; }
        .estado-enviado { background: #cce5ff; color: #004085; padding: 3px 8px; border-radius: 4px; font-weight: 600; }
        .estado-entregado { background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-weight: 600; }
        .estado-rechazado { background: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 4px; font-weight: 600; }

        .vendido-si { background: #28a745; color: white; padding: 3px 8px; border-radius: 4px; font-weight: 600; }
        .vendido-no { background: #6c757d; color: white; padding: 3px 8px; border-radius: 4px; font-weight: 600; }

        .top-menu {
            background: white;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .top-menu a {
            color: #667eea;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 8px;
            background: #f0f0f0;
            font-weight: 600;
            transition: all 0.3s;
        }

        .top-menu a:hover {
            background: #667eea;
            color: white;
        }

        /* Columna ID destacada */
        td:first-child, th:first-child {
            font-weight: bold;
            color: #667eea;
        }

        /* Scroll horizontal para tablas grandes */
        .table-scroll {
            overflow-x: auto;
        }

        /* Valores NULL */
        .null-value {
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug Base de Datos - FINOSO</h1>
        
        <div class="top-menu">
            <?php foreach ($tablas as $tabla): ?>
                <a href="#<?php echo $tabla; ?>"><?php echo $tabla; ?></a>
            <?php endforeach; ?>
        </div>

        <?php foreach ($tablas as $tabla): ?>
            <div class="table-container" id="<?php echo $tabla; ?>">
                <div class="table-title">
                    <span>📊 <?php echo strtoupper($tabla); ?></span>
                    <?php
                    // Contar registros
                    $count_sql = "SELECT COUNT(*) as total FROM `$tabla`";
                    $count_result = $conn->query($count_sql);
                    $count_row = $count_result->fetch_assoc();
                    ?>
                    <span class="record-count"><?php echo $count_row['total']; ?> registros</span>
                </div>

                <div class="table-scroll">
                    <?php
                    $sql = "SELECT * FROM `$tabla` ORDER BY 1 DESC LIMIT 100";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        echo '<table>';
                        
                        // Cabecera
                        $first_row = true;
                        while ($row = $result->fetch_assoc()) {
                            if ($first_row) {
                                echo '<tr>';
                                foreach (array_keys($row) as $column) {
                                    echo '<th>' . htmlspecialchars($column) . '</th>';
                                }
                                echo '</tr>';
                                $first_row = false;
                            }

                            echo '<tr>';
                            foreach ($row as $key => $value) {
                                $cell_value = $value;
                                $cell_class = '';

                                // Formatear valores especiales
                                if ($value === null) {
                                    $cell_value = '<span class="null-value">NULL</span>';
                                } else {
                                    // Estados de orden
                                    if ($key === 'estado' && $tabla === 'orden') {
                                        $estado_class = 'estado-' . str_replace('_', '-', $value);
                                        $cell_value = '<span class="' . $estado_class . '">' . htmlspecialchars($value) . '</span>';
                                    }
                                    // Campo vendido
                                    elseif ($key === 'vendido' && $tabla === 'reloj') {
                                        $vendido_class = $value == 1 ? 'vendido-si' : 'vendido-no';
                                        $vendido_text = $value == 1 ? 'SÍ' : 'NO';
                                        $cell_value = '<span class="' . $vendido_class . '">' . $vendido_text . '</span>';
                                    }
                                    // Precios
                                    elseif (in_array($key, ['precio', 'total', 'costo_envio', 'precio_unitario', 'subtotal'])) {
                                        $cell_value = '$' . number_format($value, 0, ',', '.');
                                    }
                                    // Fechas
                                    elseif (in_array($key, ['fecha', 'fecha_orden', 'fecha_envio', 'fecha_entrega', 'fecha_aprobacion'])) {
                                        $cell_value = date('Y-m-d H:i', strtotime($value));
                                    }
                                    // Textos largos
                                    elseif (strlen($value) > 100) {
                                        $cell_value = htmlspecialchars(substr($value, 0, 100)) . '...';
                                    }
                                    // Imágenes (mostrar solo nombre)
                                    elseif (strpos($key, 'imagen') !== false || strpos($key, 'comprobante') !== false) {
                                        if (strlen($value) > 50) {
                                            $cell_value = '🖼️ [Imagen/Base64]';
                                        } else {
                                            $cell_value = htmlspecialchars($value);
                                        }
                                    }
                                    else {
                                        $cell_value = htmlspecialchars($value);
                                    }
                                }

                                echo '<td>' . $cell_value . '</td>';
                            }
                            echo '</tr>';
                        }
                        
                        echo '</table>';
                    } else {
                        echo '<div class="empty-table">📭 Esta tabla está vacía</div>';
                    }
                    ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php $conn->close(); ?>
    </div>
</body>
</html>

