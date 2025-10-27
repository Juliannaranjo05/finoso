<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de Campos para Filtros Avanzados</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #fff;
            padding: 30px;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(0, 0, 0, 0.6);
            border: 2px solid #FFCF66;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }
        h1 {
            color: #FFCF66;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2rem;
            text-shadow: 0 0 20px rgba(255, 207, 102, 0.5);
        }
        .subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 30px;
            font-size: 0.95rem;
        }
        .section {
            background: rgba(255, 207, 102, 0.05);
            border-left: 4px solid #FFCF66;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .section h2 {
            color: #FFCF66;
            margin-top: 0;
            font-size: 1.3rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            background: rgba(0, 0, 0, 0.3);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 207, 102, 0.2);
        }
        th {
            background: rgba(255, 207, 102, 0.2);
            color: #FFCF66;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        tr:hover {
            background: rgba(255, 207, 102, 0.1);
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status.lleno {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        .status.vacio {
            background: rgba(244, 67, 54, 0.2);
            color: #F44336;
            border: 1px solid #F44336;
        }
        .status.parcial {
            background: rgba(255, 152, 0, 0.2);
            color: #FF9800;
            border: 1px solid #FF9800;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .summary-card {
            background: rgba(255, 207, 102, 0.1);
            border: 1px solid rgba(255, 207, 102, 0.3);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        .summary-card h3 {
            color: #FFCF66;
            margin: 0 0 10px 0;
            font-size: 2rem;
        }
        .summary-card p {
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
            font-size: 0.9rem;
        }
        .value-list {
            max-height: 200px;
            overflow-y: auto;
            background: rgba(0, 0, 0, 0.3);
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .value-item {
            padding: 5px 10px;
            margin: 3px 0;
            background: rgba(255, 207, 102, 0.1);
            border-left: 3px solid #FFCF66;
            border-radius: 3px;
            font-size: 0.9rem;
        }
        .empty {
            color: rgba(255, 255, 255, 0.5);
            font-style: italic;
            text-align: center;
            padding: 20px;
        }
        .btn {
            display: inline-block;
            background: #FFCF66;
            color: #090909;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 10px 5px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #FFB800;
            box-shadow: 0 4px 15px rgba(255, 207, 102, 0.4);
            transform: translateY(-2px);
        }
        .actions {
            text-align: center;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 VERIFICACIÓN DE CAMPOS PARA FILTROS AVANZADOS</h1>
        <p class="subtitle">Análisis de datos disponibles en la tabla `reloj` para implementar filtros</p>

        <?php
        require_once 'conexion.php';

        // Consulta para obtener estadísticas
        $sql = "SELECT 
                    COUNT(*) as total_relojes,
                    SUM(CASE WHEN movimiento IS NOT NULL AND movimiento != '' THEN 1 ELSE 0 END) as con_movimiento,
                    SUM(CASE WHEN pulsera IS NOT NULL AND pulsera != '' THEN 1 ELSE 0 END) as con_pulsera,
                    SUM(CASE WHEN peso IS NOT NULL AND peso != '' THEN 1 ELSE 0 END) as con_peso,
                    SUM(CASE WHEN tipo_bisel IS NOT NULL AND tipo_bisel != '' THEN 1 ELSE 0 END) as con_tipo_bisel,
                    SUM(CASE WHEN resistencia_agua IS NOT NULL AND resistencia_agua != '' THEN 1 ELSE 0 END) as con_resistencia_agua,
                    SUM(CASE WHEN eslabones IS NOT NULL AND eslabones != '' THEN 1 ELSE 0 END) as con_eslabones
                FROM reloj";
        $result = $conn->query($sql);
        $stats = $result->fetch_assoc();

        $total = $stats['total_relojes'];
        ?>

        <!-- Resumen General -->
        <div class="summary">
            <div class="summary-card">
                <h3><?php echo $total; ?></h3>
                <p>Total de Relojes</p>
            </div>
            <div class="summary-card">
                <h3><?php echo $stats['con_movimiento']; ?></h3>
                <p>Con Movimiento</p>
            </div>
            <div class="summary-card">
                <h3><?php echo $stats['con_pulsera']; ?></h3>
                <p>Con Tipo Pulsera</p>
            </div>
            <div class="summary-card">
                <h3><?php echo $stats['con_peso']; ?></h3>
                <p>Con Peso</p>
            </div>
        </div>

        <?php
        // Función para obtener estado
        function obtenerEstado($cantidad, $total) {
            $porcentaje = ($cantidad / $total) * 100;
            if ($porcentaje >= 80) return 'lleno';
            if ($porcentaje >= 30) return 'parcial';
            return 'vacio';
        }

        // Función para obtener valores únicos
        function obtenerValoresUnicos($conn, $campo) {
            $sql = "SELECT DISTINCT $campo FROM reloj WHERE $campo IS NOT NULL AND $campo != '' ORDER BY $campo";
            $result = $conn->query($sql);
            $valores = [];
            while ($row = $result->fetch_assoc()) {
                $valores[] = $row[$campo];
            }
            return $valores;
        }
        ?>

        <!-- MOVIMIENTO -->
        <div class="section">
            <h2>⚙️ MOVIMIENTO</h2>
            <table>
                <tr>
                    <th>Total Relojes</th>
                    <th>Con Datos</th>
                    <th>Sin Datos</th>
                    <th>Porcentaje</th>
                    <th>Estado</th>
                </tr>
                <tr>
                    <td><?php echo $total; ?></td>
                    <td><?php echo $stats['con_movimiento']; ?></td>
                    <td><?php echo $total - $stats['con_movimiento']; ?></td>
                    <td><?php echo round(($stats['con_movimiento'] / $total) * 100, 1); ?>%</td>
                    <td><span class="status <?php echo obtenerEstado($stats['con_movimiento'], $total); ?>">
                        <?php 
                        $estado = obtenerEstado($stats['con_movimiento'], $total);
                        echo $estado == 'lleno' ? 'LISTO' : ($estado == 'parcial' ? 'INCOMPLETO' : 'VACÍO');
                        ?>
                    </span></td>
                </tr>
            </table>
            <h3>Valores disponibles:</h3>
            <?php
            $movimientos = obtenerValoresUnicos($conn, 'movimiento');
            if (count($movimientos) > 0) {
                echo '<div class="value-list">';
                foreach ($movimientos as $mov) {
                    echo '<div class="value-item">' . htmlspecialchars($mov) . '</div>';
                }
                echo '</div>';
            } else {
                echo '<p class="empty">No hay datos disponibles</p>';
            }
            ?>
        </div>

        <!-- PULSERA -->
        <div class="section">
            <h2>📿 TIPO DE PULSERA</h2>
            <table>
                <tr>
                    <th>Total Relojes</th>
                    <th>Con Datos</th>
                    <th>Sin Datos</th>
                    <th>Porcentaje</th>
                    <th>Estado</th>
                </tr>
                <tr>
                    <td><?php echo $total; ?></td>
                    <td><?php echo $stats['con_pulsera']; ?></td>
                    <td><?php echo $total - $stats['con_pulsera']; ?></td>
                    <td><?php echo round(($stats['con_pulsera'] / $total) * 100, 1); ?>%</td>
                    <td><span class="status <?php echo obtenerEstado($stats['con_pulsera'], $total); ?>">
                        <?php 
                        $estado = obtenerEstado($stats['con_pulsera'], $total);
                        echo $estado == 'lleno' ? 'LISTO' : ($estado == 'parcial' ? 'INCOMPLETO' : 'VACÍO');
                        ?>
                    </span></td>
                </tr>
            </table>
            <h3>Valores disponibles:</h3>
            <?php
            $pulseras = obtenerValoresUnicos($conn, 'pulsera');
            if (count($pulseras) > 0) {
                echo '<div class="value-list">';
                foreach ($pulseras as $pul) {
                    echo '<div class="value-item">' . htmlspecialchars($pul) . '</div>';
                }
                echo '</div>';
            } else {
                echo '<p class="empty">No hay datos disponibles</p>';
            }
            ?>
        </div>

        <!-- PESO -->
        <div class="section">
            <h2>⚖️ PESO</h2>
            <table>
                <tr>
                    <th>Total Relojes</th>
                    <th>Con Datos</th>
                    <th>Sin Datos</th>
                    <th>Porcentaje</th>
                    <th>Estado</th>
                </tr>
                <tr>
                    <td><?php echo $total; ?></td>
                    <td><?php echo $stats['con_peso']; ?></td>
                    <td><?php echo $total - $stats['con_peso']; ?></td>
                    <td><?php echo round(($stats['con_peso'] / $total) * 100, 1); ?>%</td>
                    <td><span class="status <?php echo obtenerEstado($stats['con_peso'], $total); ?>">
                        <?php 
                        $estado = obtenerEstado($stats['con_peso'], $total);
                        echo $estado == 'lleno' ? 'LISTO' : ($estado == 'parcial' ? 'INCOMPLETO' : 'VACÍO');
                        ?>
                    </span></td>
                </tr>
            </table>
            <h3>Valores disponibles:</h3>
            <?php
            $pesos = obtenerValoresUnicos($conn, 'peso');
            if (count($pesos) > 0) {
                echo '<div class="value-list">';
                foreach ($pesos as $p) {
                    echo '<div class="value-item">' . htmlspecialchars($p) . '</div>';
                }
                echo '</div>';
            } else {
                echo '<p class="empty">No hay datos disponibles</p>';
            }
            ?>
        </div>

        <!-- CAMPOS ADICIONALES (BONUS) -->
        <div class="section">
            <h2>🎁 CAMPOS ADICIONALES DISPONIBLES</h2>
            <table>
                <tr>
                    <th>Campo</th>
                    <th>Con Datos</th>
                    <th>Porcentaje</th>
                    <th>Estado</th>
                </tr>
                <tr>
                    <td>Tipo de Bisel</td>
                    <td><?php echo $stats['con_tipo_bisel']; ?> / <?php echo $total; ?></td>
                    <td><?php echo round(($stats['con_tipo_bisel'] / $total) * 100, 1); ?>%</td>
                    <td><span class="status <?php echo obtenerEstado($stats['con_tipo_bisel'], $total); ?>">
                        <?php 
                        $estado = obtenerEstado($stats['con_tipo_bisel'], $total);
                        echo $estado == 'lleno' ? 'LISTO' : ($estado == 'parcial' ? 'INCOMPLETO' : 'VACÍO');
                        ?>
                    </span></td>
                </tr>
                <tr>
                    <td>Resistencia al Agua</td>
                    <td><?php echo $stats['con_resistencia_agua']; ?> / <?php echo $total; ?></td>
                    <td><?php echo round(($stats['con_resistencia_agua'] / $total) * 100, 1); ?>%</td>
                    <td><span class="status <?php echo obtenerEstado($stats['con_resistencia_agua'], $total); ?>">
                        <?php 
                        $estado = obtenerEstado($stats['con_resistencia_agua'], $total);
                        echo $estado == 'lleno' ? 'LISTO' : ($estado == 'parcial' ? 'INCOMPLETO' : 'VACÍO');
                        ?>
                    </span></td>
                </tr>
                <tr>
                    <td>Eslabones</td>
                    <td><?php echo $stats['con_eslabones']; ?> / <?php echo $total; ?></td>
                    <td><?php echo round(($stats['con_eslabones'] / $total) * 100, 1); ?>%</td>
                    <td><span class="status <?php echo obtenerEstado($stats['con_eslabones'], $total); ?>">
                        <?php 
                        $estado = obtenerEstado($stats['con_eslabones'], $total);
                        echo $estado == 'lleno' ? 'LISTO' : ($estado == 'parcial' ? 'INCOMPLETO' : 'VACÍO');
                        ?>
                    </span></td>
                </tr>
            </table>
        </div>

        <?php
        // Recomendación
        $campos_listos = 0;
        if (obtenerEstado($stats['con_movimiento'], $total) == 'lleno') $campos_listos++;
        if (obtenerEstado($stats['con_pulsera'], $total) == 'lleno') $campos_listos++;
        if (obtenerEstado($stats['con_peso'], $total) == 'lleno') $campos_listos++;
        ?>

        <div class="section" style="background: <?php echo $campos_listos >= 2 ? 'rgba(76, 175, 80, 0.1)' : 'rgba(255, 152, 0, 0.1)'; ?>; border-color: <?php echo $campos_listos >= 2 ? '#4CAF50' : '#FF9800'; ?>;">
            <h2>📊 RECOMENDACIÓN</h2>
            <p style="font-size: 1.1rem; line-height: 1.8;">
                <?php
                if ($campos_listos >= 2) {
                    echo '✅ <strong>Listo para implementar filtros avanzados!</strong><br>';
                    echo 'Tienes suficientes datos en al menos ' . $campos_listos . ' campos principales para crear filtros útiles.';
                } elseif ($campos_listos >= 1) {
                    echo '⚠️ <strong>Implementación parcial recomendada</strong><br>';
                    echo 'Puedes implementar filtros para los campos con datos completos y agregar los demás más adelante.';
                } else {
                    echo '❌ <strong>Completar datos primero</strong><br>';
                    echo 'Es necesario agregar más información en la base de datos antes de implementar filtros.';
                }
                ?>
            </p>
        </div>

        <div class="actions">
            <a href="panel.php" class="btn">← Volver al Panel</a>
            <?php if ($campos_listos >= 1): ?>
            <a href="../PROPUESTA_FILTROS_AVANZADOS.md" class="btn" style="background: #4CAF50;">📄 Ver Propuesta de Implementación</a>
            <?php endif; ?>
        </div>

        <?php $conn->close(); ?>
    </div>
</body>
</html>

