<?php
/**
 * RESETEAR RELOJES PARA PRUEBAS
 * Pone los relojes con IDs 1, 2, 3 y 4 como disponibles nuevamente
 */

require_once '../admin/conexion.php';

$relojes_ids = [1, 2, 3, 4];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resetear Relojes - Pruebas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
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
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            text-align: center;
            color: #718096;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .info-box {
            background: #f7fafc;
            border-left: 4px solid #4299e1;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .warning-box {
            background: #fffaf0;
            border-left: 4px solid #ed8936;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success-box {
            background: #f0fff4;
            border-left: 4px solid #48bb78;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .error-box {
            background: #fff5f5;
            border-left: 4px solid #f56565;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        th {
            background: linear-gradient(135deg, #FFCF66 0%, #FFB84D 100%);
            color: #2d3748;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:hover {
            background: #f7fafc;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-disponible {
            background: #c6f6d5;
            color: #22543d;
        }
        .badge-vendido {
            background: #fed7d7;
            color: #742a2a;
        }
        .badge-yes {
            background: #c6f6d5;
            color: #22543d;
        }
        .badge-no {
            background: #e2e8f0;
            color: #4a5568;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #FFCF66 0%, #FFB84D 100%);
            color: #2d3748;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            margin: 5px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 207, 102, 0.4);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #cbd5e0 0%, #a0aec0 100%);
        }
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        .step {
            display: flex;
            align-items: center;
            margin: 15px 0;
            padding: 10px;
            background: #f7fafc;
            border-radius: 8px;
        }
        .step-number {
            background: linear-gradient(135deg, #FFCF66 0%, #FFB84D 100%);
            color: #2d3748;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .step-content {
            flex: 1;
            color: #4a5568;
        }
        code {
            background: #2d3748;
            color: #48bb78;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Resetear Relojes para Pruebas</h1>
        <p class="subtitle">IDs: 1, 2, 3, 4</p>

        <?php
        // Mostrar estado ANTES
        echo '<h3 style="color: #2d3748; margin-top: 20px;">📊 Estado ANTES del Reset:</h3>';
        
        $ids_str = implode(',', $relojes_ids);
        $query_antes = "SELECT id_reloj, marca, nombre, disponible, vendido FROM reloj WHERE id_reloj IN ($ids_str) ORDER BY id_reloj";
        $result_antes = mysqli_query($conn, $query_antes);
        
        if ($result_antes && mysqli_num_rows($result_antes) > 0) {
            echo '<table>';
            echo '<tr><th>ID</th><th>Marca</th><th>Nombre</th><th>Disponible</th><th>Vendido</th></tr>';
            
            $relojes_antes = [];
            while ($row = mysqli_fetch_assoc($result_antes)) {
                $relojes_antes[] = $row;
                $disponible_badge = $row['disponible'] ? '<span class="badge badge-yes">SÍ</span>' : '<span class="badge badge-no">NO</span>';
                $vendido_badge = $row['vendido'] ? '<span class="badge badge-vendido">SÍ</span>' : '<span class="badge badge-no">NO</span>';
                
                echo '<tr>';
                echo '<td><strong>' . $row['id_reloj'] . '</strong></td>';
                echo '<td>' . htmlspecialchars($row['marca']) . '</td>';
                echo '<td>' . htmlspecialchars($row['nombre']) . '</td>';
                echo '<td>' . $disponible_badge . '</td>';
                echo '<td>' . $vendido_badge . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
            // Contar cuántos están vendidos
            $vendidos_count = 0;
            foreach ($relojes_antes as $reloj) {
                if ($reloj['vendido']) $vendidos_count++;
            }
            
            if ($vendidos_count > 0) {
                echo '<div class="warning-box">';
                echo '<strong>⚠️ Atención:</strong> Hay <strong>' . $vendidos_count . '</strong> reloj(es) marcado(s) como vendido(s).';
                echo '</div>';
            } else {
                echo '<div class="info-box">';
                echo '<strong>ℹ️ Información:</strong> Ningún reloj está marcado como vendido actualmente.';
                echo '</div>';
            }
            
            // EJECUTAR RESET
            echo '<h3 style="color: #2d3748; margin-top: 30px;">⚙️ Ejecutando Reset...</h3>';
            
            $query_reset = "UPDATE reloj SET disponible = 1, vendido = 0 WHERE id_reloj IN ($ids_str)";
            
            if (mysqli_query($conn, $query_reset)) {
                $affected = mysqli_affected_rows($conn);
                
                echo '<div class="success-box">';
                echo '<strong>✅ Reset Exitoso!</strong><br>';
                echo 'Se actualizaron <strong>' . $affected . '</strong> registro(s).';
                echo '</div>';
                
                // Mostrar estado DESPUÉS
                echo '<h3 style="color: #2d3748; margin-top: 30px;">📊 Estado DESPUÉS del Reset:</h3>';
                
                $result_despues = mysqli_query($conn, $query_antes);
                
                if ($result_despues && mysqli_num_rows($result_despues) > 0) {
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Marca</th><th>Nombre</th><th>Disponible</th><th>Vendido</th></tr>';
                    
                    while ($row = mysqli_fetch_assoc($result_despues)) {
                        $disponible_badge = $row['disponible'] ? '<span class="badge badge-yes">SÍ</span>' : '<span class="badge badge-no">NO</span>';
                        $vendido_badge = $row['vendido'] ? '<span class="badge badge-no">NO</span>' : '<span class="badge badge-no">NO</span>';
                        
                        echo '<tr>';
                        echo '<td><strong>' . $row['id_reloj'] . '</strong></td>';
                        echo '<td>' . htmlspecialchars($row['marca']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['nombre']) . '</td>';
                        echo '<td>' . $disponible_badge . '</td>';
                        echo '<td>' . $vendido_badge . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    
                    echo '<div class="success-box">';
                    echo '<strong>🎉 ¡Listo para nuevas pruebas!</strong><br>';
                    echo 'Los 4 relojes están ahora <code>disponible = 1</code> y <code>vendido = 0</code>';
                    echo '</div>';
                }
                
            } else {
                echo '<div class="error-box">';
                echo '<strong>❌ Error al ejecutar reset:</strong><br>';
                echo mysqli_error($conn);
                echo '</div>';
            }
            
        } else {
            echo '<div class="error-box">';
            echo '<strong>❌ Error:</strong> No se encontraron los relojes con los IDs especificados.';
            echo '</div>';
        }
        
        mysqli_close($conn);
        ?>

        <div style="margin-top: 30px; padding: 20px; background: #f7fafc; border-radius: 8px;">
            <h3 style="color: #2d3748; margin-bottom: 15px;">📝 Consulta SQL Ejecutada:</h3>
            <code style="display: block; padding: 10px; background: #2d3748; border-radius: 5px;">
                UPDATE reloj SET disponible = 1, vendido = 0 WHERE id_reloj IN (1, 2, 3, 4);
            </code>
        </div>

        <div style="margin-top: 20px; padding: 20px; background: #fffaf0; border-radius: 8px;">
            <h3 style="color: #744210; margin-bottom: 10px;">💡 Próximos Pasos:</h3>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-content">Ve al catálogo y verifica que los 4 relojes aparezcan</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-content">Haz una nueva compra de prueba con Nequi</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-content">Aprueba la orden desde el panel de admin</div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-content">Verifica los logs en <code>admin/ver_logs_codigos.php</code></div>
            </div>
            <div class="step">
                <div class="step-number">5</div>
                <div class="step-content">Revisa tu perfil para ver el código generado</div>
            </div>
        </div>

        <div class="actions">
            <button class="btn" onclick="location.reload()">🔄 Ejecutar Reset Nuevamente</button>
            <a href="../catalogo/catalogo.html" class="btn">🛍️ Ver Catálogo</a>
            <a href="../admin/ver_logs_codigos.php" class="btn">📋 Ver Logs</a>
            <a href="../admin/panel.php" class="btn btn-secondary">← Volver al Admin</a>
        </div>
    </div>
</body>
</html>

