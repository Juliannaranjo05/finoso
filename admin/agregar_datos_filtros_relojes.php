<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Datos para Filtros - Relojes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #fff;
            padding: 30px;
            min-height: 100vh;
        }
        .container {
            max-width: 1400px;
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
        .warning {
            background: rgba(255, 152, 0, 0.2);
            border: 2px solid #FF9800;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .warning h3 {
            color: #FF9800;
            margin-bottom: 10px;
        }
        .info {
            background: rgba(33, 150, 243, 0.2);
            border: 2px solid #2196F3;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .info h3 {
            color: #2196F3;
            margin-bottom: 15px;
        }
        .info ul {
            list-style: none;
            padding-left: 20px;
        }
        .info li {
            margin: 8px 0;
            color: rgba(255, 255, 255, 0.9);
        }
        .info li::before {
            content: "✓ ";
            color: #4CAF50;
            font-weight: bold;
            margin-right: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
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
        }
        tr:hover {
            background: rgba(255, 207, 102, 0.1);
        }
        .reloj-card {
            background: rgba(255, 207, 102, 0.05);
            border: 1px solid rgba(255, 207, 102, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
        }
        .reloj-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 207, 102, 0.3);
        }
        .reloj-nombre {
            color: #FFCF66;
            font-size: 1.2rem;
            font-weight: 600;
        }
        .reloj-id {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }
        .campos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        .campo {
            background: rgba(0, 0, 0, 0.3);
            padding: 10px;
            border-radius: 5px;
        }
        .campo-label {
            color: rgba(255, 207, 102, 0.8);
            font-size: 0.85rem;
            margin-bottom: 5px;
            display: block;
        }
        .campo-valor {
            color: #fff;
            font-size: 1rem;
            font-weight: 500;
        }
        .campo-vacio {
            color: rgba(255, 255, 255, 0.4);
            font-style: italic;
        }
        .btn {
            display: inline-block;
            background: #FFCF66;
            color: #090909;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 10px 5px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn:hover {
            background: #FFB800;
            box-shadow: 0 4px 15px rgba(255, 207, 102, 0.4);
            transform: translateY(-2px);
        }
        .btn-danger {
            background: #F44336;
            color: white;
        }
        .btn-danger:hover {
            background: #D32F2F;
            box-shadow: 0 4px 15px rgba(244, 67, 54, 0.4);
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .actions {
            text-align: center;
            margin: 30px 0;
        }
        .success {
            background: rgba(76, 175, 80, 0.2);
            border: 2px solid #4CAF50;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .success h3 {
            color: #4CAF50;
            margin-bottom: 10px;
        }
        .resultado {
            background: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #4CAF50;
        }
        .error {
            background: rgba(244, 67, 54, 0.2);
            border: 2px solid #F44336;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .error h3 {
            color: #F44336;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 AGREGAR DATOS PARA FILTROS - RELOJES</h1>
        <p class="subtitle">Script para llenar los campos necesarios para filtros avanzados</p>

        <?php
        require_once 'conexion.php';

        // Verificar si se envió el formulario
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ejecutar'])) {
            echo '<div class="success">';
            echo '<h3>✅ EJECUTANDO ACTUALIZACIÓN...</h3>';
            
            // Datos de prueba realistas para diferentes tipos de relojes
            $datosRelojes = [
                // Reloj 1 - Deportivo moderno
                1 => [
                    'movimiento' => 'Cuarzo',
                    'pulsera' => 'Silicona negro',
                    'peso' => '45g',
                    'resistencia_agua' => '10ATM (100m)',
                    'tipo_bisel' => 'Giratorio unidireccional',
                    'eslabones' => 'No aplica (pulsera flexible)'
                ],
                // Reloj 2 - Clásico elegante
                2 => [
                    'movimiento' => 'Automático',
                    'pulsera' => 'Cuero genuino marrón',
                    'peso' => '85g',
                    'resistencia_agua' => '5ATM (50m)',
                    'tipo_bisel' => 'Fijo pulido',
                    'eslabones' => 'No aplica (correa)'
                ],
                // Reloj 3 - Deportivo acero
                3 => [
                    'movimiento' => 'Cuarzo cronógrafo',
                    'pulsera' => 'Acero inoxidable',
                    'peso' => '165g',
                    'resistencia_agua' => '10ATM (100m)',
                    'tipo_bisel' => 'Giratorio bidireccional',
                    'eslabones' => 'Ajustables con herramienta'
                ],
                // Reloj 4 - Elegante dorado
                4 => [
                    'movimiento' => 'Cuarzo',
                    'pulsera' => 'Acero dorado',
                    'peso' => '120g',
                    'resistencia_agua' => '3ATM (30m)',
                    'tipo_bisel' => 'Fijo biselado',
                    'eslabones' => 'Ajustables con herramienta'
                ]
            ];

            $actualizados = 0;
            $errores = 0;

            foreach ($datosRelojes as $id => $datos) {
                $sql = "UPDATE reloj SET 
                        movimiento = ?,
                        pulsera = ?,
                        peso = ?,
                        resistencia_agua = ?,
                        tipo_bisel = ?,
                        eslabones = ?
                        WHERE id_reloj = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param(
                    "ssssssi",
                    $datos['movimiento'],
                    $datos['pulsera'],
                    $datos['peso'],
                    $datos['resistencia_agua'],
                    $datos['tipo_bisel'],
                    $datos['eslabones'],
                    $id
                );
                
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        echo '<div class="resultado">';
                        echo '✅ <strong>Reloj #' . $id . '</strong> actualizado correctamente<br>';
                        echo '<small style="color: rgba(255, 255, 255, 0.7);">Movimiento: ' . $datos['movimiento'] . ' | Pulsera: ' . $datos['pulsera'] . '</small>';
                        echo '</div>';
                        $actualizados++;
                    } else {
                        echo '<div class="resultado" style="border-color: #FF9800;">';
                        echo '⚠️ <strong>Reloj #' . $id . '</strong> no encontrado o sin cambios';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="resultado" style="border-color: #F44336;">';
                    echo '❌ <strong>Reloj #' . $id . '</strong> error: ' . $stmt->error;
                    echo '</div>';
                    $errores++;
                }
                $stmt->close();
            }

            echo '<hr style="border-color: rgba(255, 207, 102, 0.3); margin: 20px 0;">';
            echo '<p style="font-size: 1.2rem; margin: 15px 0;">';
            echo '<strong>Relojes actualizados:</strong> ' . $actualizados . ' de ' . count($datosRelojes);
            if ($errores > 0) {
                echo '<br><strong style="color: #F44336;">Errores:</strong> ' . $errores;
            }
            echo '</p>';
            echo '</div>';

            echo '<div class="actions">';
            echo '<a href="verificar_campos_filtros.php" class="btn">📊 Ver Estadísticas Actualizadas</a>';
            echo '<a href="agregar_datos_filtros_relojes.php" class="btn-secondary btn">🔄 Recargar Página</a>';
            echo '</div>';

        } else {
            // Mostrar estado actual y formulario
            $sql = "SELECT id_reloj, nombre, marca, movimiento, pulsera, peso, resistencia_agua, tipo_bisel, eslabones FROM reloj ORDER BY id_reloj";
            $result = $conn->query($sql);

            echo '<div class="warning">';
            echo '<h3>⚠️ ADVERTENCIA</h3>';
            echo '<p>Este script actualizará los campos de filtros en <strong>4 relojes</strong> (IDs: 1, 2, 3, 4).<br>';
            echo 'Los datos actuales serán <strong>reemplazados</strong> con valores de prueba realistas.</p>';
            echo '</div>';

            echo '<div class="info">';
            echo '<h3>📋 DATOS QUE SE AGREGARÁN:</h3>';
            echo '<ul>';
            echo '<li><strong>Movimiento:</strong> Cuarzo, Automático, Cuarzo cronógrafo</li>';
            echo '<li><strong>Pulsera:</strong> Silicona, Cuero genuino, Acero inoxidable, Acero dorado</li>';
            echo '<li><strong>Peso:</strong> 45g, 85g, 165g, 120g</li>';
            echo '<li><strong>Resistencia al Agua:</strong> 3ATM, 5ATM, 10ATM</li>';
            echo '<li><strong>Tipo de Bisel:</strong> Giratorio, Fijo pulido, Fijo biselado</li>';
            echo '<li><strong>Eslabones:</strong> Ajustables / No aplica</li>';
            echo '</ul>';
            echo '</div>';

            echo '<h2 style="color: #FFCF66; margin: 30px 0 20px 0;">📋 ESTADO ACTUAL DE LOS RELOJES:</h2>';

            while ($row = $result->fetch_assoc()) {
                echo '<div class="reloj-card">';
                echo '<div class="reloj-header">';
                echo '<div class="reloj-nombre">' . htmlspecialchars($row['nombre']) . '</div>';
                echo '<div class="reloj-id">ID: ' . $row['id_reloj'] . ' | ' . htmlspecialchars($row['marca']) . '</div>';
                echo '</div>';
                
                echo '<div class="campos-grid">';
                
                $campos = [
                    'Movimiento' => $row['movimiento'],
                    'Pulsera' => $row['pulsera'],
                    'Peso' => $row['peso'],
                    'Resistencia H₂O' => $row['resistencia_agua'],
                    'Tipo Bisel' => $row['tipo_bisel'],
                    'Eslabones' => $row['eslabones']
                ];
                
                foreach ($campos as $label => $valor) {
                    echo '<div class="campo">';
                    echo '<span class="campo-label">' . $label . ':</span><br>';
                    if (!empty($valor)) {
                        echo '<span class="campo-valor">' . htmlspecialchars($valor) . '</span>';
                    } else {
                        echo '<span class="campo-vacio">Sin datos</span>';
                    }
                    echo '</div>';
                }
                
                echo '</div>';
                echo '</div>';
            }

            echo '<form method="POST" onsubmit="return confirm(\'¿Estás seguro de actualizar los datos de los relojes?\');">';
            echo '<div class="actions">';
            echo '<button type="submit" name="ejecutar" value="1" class="btn">✅ EJECUTAR ACTUALIZACIÓN</button>';
            echo '<a href="panel.php" class="btn-secondary btn">← Volver al Panel</a>';
            echo '<a href="verificar_campos_filtros.php" class="btn-secondary btn">📊 Ver Estadísticas</a>';
            echo '</div>';
            echo '</form>';
        }

        $conn->close();
        ?>
    </div>
</body>
</html>

