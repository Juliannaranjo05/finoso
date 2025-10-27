<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generador Automático de Datos para Relojes</title>
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
        .info-box {
            background: rgba(33, 150, 243, 0.2);
            border: 2px solid #2196F3;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-box h3 {
            color: #2196F3;
            margin-bottom: 15px;
        }
        .config-section {
            background: rgba(255, 207, 102, 0.05);
            border: 1px solid rgba(255, 207, 102, 0.3);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .config-section h3 {
            color: #FFCF66;
            margin-bottom: 15px;
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            color: rgba(255, 207, 102, 0.9);
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            max-width: 300px;
            padding: 10px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 207, 102, 0.5);
            border-radius: 5px;
            color: #fff;
            font-size: 1rem;
        }
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin: 10px 0;
        }
        .checkbox-item {
            background: rgba(0, 0, 0, 0.3);
            padding: 10px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .checkbox-item label {
            cursor: pointer;
            margin: 0;
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
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.3);
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
        }
        .success h3 {
            color: #4CAF50;
            margin-bottom: 15px;
        }
        .resultado-item {
            background: rgba(0, 0, 0, 0.3);
            padding: 12px;
            margin: 8px 0;
            border-radius: 5px;
            border-left: 4px solid #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 GENERADOR AUTOMÁTICO DE DATOS</h1>
        <p class="subtitle">Completa automáticamente los campos de filtros para todos los relojes</p>

        <?php
        require_once 'conexion.php';

        // Plantillas de datos para generar aleatoriamente
        $plantillas = [
            'movimiento' => [
                'Cuarzo',
                'Automático',
                'Mecánico',
                'Cuarzo cronógrafo',
                'Automático con reserva de marcha',
                'Cuarzo digital',
                'Eco-Drive (solar)',
                'Kinetic (movimiento + batería)'
            ],
            'pulsera' => [
                'Acero inoxidable',
                'Acero dorado',
                'Acero bicolor (plateado-dorado)',
                'Cuero genuino negro',
                'Cuero genuino marrón',
                'Cuero genuino azul',
                'Silicona negro',
                'Silicona azul',
                'Caucho deportivo',
                'Malla milanesa',
                'NATO textil',
                'Titanio'
            ],
            'peso' => [
                '35g', '45g', '55g', '65g', '75g', '85g', '95g',
                '105g', '115g', '125g', '135g', '145g', '155g', '165g',
                '175g', '185g', '195g', '210g'
            ],
            'resistencia_agua' => [
                '3ATM (30m) - Salpicaduras',
                '5ATM (50m) - Natación superficial',
                '10ATM (100m) - Natación y snorkel',
                '20ATM (200m) - Buceo recreativo',
                '30ATM (300m) - Buceo profesional',
                'No resistente al agua'
            ],
            'tipo_bisel' => [
                'Fijo pulido',
                'Fijo mate',
                'Fijo biselado',
                'Giratorio unidireccional',
                'Giratorio bidireccional',
                'Taquímetro fijo',
                'Bisel táctil',
                'Sin bisel'
            ],
            'eslabones' => [
                'Ajustables con herramienta',
                'Ajustables con pasadores',
                'Sistema de cierre micro-ajustable',
                'No aplica (correa de cuero)',
                'No aplica (pulsera flexible)',
                'Ajuste rápido sin herramientas',
                'Eslabones fijos',
                'Sistema deslizante'
            ]
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar'])) {
            $porcentaje = intval($_POST['porcentaje'] ?? 100);
            $sobreescribir = isset($_POST['sobreescribir']);
            
            echo '<div class="success">';
            echo '<h3>✅ GENERANDO DATOS AUTOMÁTICAMENTE...</h3>';
            echo '<p>Porcentaje de relojes a actualizar: <strong>' . $porcentaje . '%</strong></p>';
            echo '<p>Sobrescribir datos existentes: <strong>' . ($sobreescribir ? 'SÍ' : 'NO') . '</strong></p>';
            echo '<hr style="border-color: rgba(76, 175, 80, 0.3); margin: 15px 0;">';
            
            // Obtener todos los relojes
            $sql = "SELECT id_reloj, nombre, movimiento, pulsera, peso, resistencia_agua, tipo_bisel, eslabones FROM reloj ORDER BY id_reloj";
            $result = $conn->query($sql);
            $relojes = $result->fetch_all(MYSQLI_ASSOC);
            
            // Calcular cuántos relojes actualizar
            $totalRelojes = count($relojes);
            $relojesActualizar = max(1, round($totalRelojes * ($porcentaje / 100)));
            
            // Seleccionar relojes aleatoriamente
            shuffle($relojes);
            $relojesSeleccionados = array_slice($relojes, 0, $relojesActualizar);
            
            $actualizados = 0;
            $saltados = 0;
            
            foreach ($relojesSeleccionados as $reloj) {
                // Verificar si tiene datos existentes
                $tieneDatos = !empty($reloj['movimiento']) || !empty($reloj['pulsera']) || 
                              !empty($reloj['peso']) || !empty($reloj['resistencia_agua']);
                
                if ($tieneDatos && !$sobreescribir) {
                    echo '<div class="resultado-item" style="border-color: #FF9800;">';
                    echo '⏭️ <strong>Reloj #' . $reloj['id_reloj'] . ':</strong> ' . htmlspecialchars($reloj['nombre']) . ' (ya tiene datos, omitido)';
                    echo '</div>';
                    $saltados++;
                    continue;
                }
                
                // Generar datos aleatorios
                $movimiento = $plantillas['movimiento'][array_rand($plantillas['movimiento'])];
                $pulsera = $plantillas['pulsera'][array_rand($plantillas['pulsera'])];
                $peso = $plantillas['peso'][array_rand($plantillas['peso'])];
                $resistencia = $plantillas['resistencia_agua'][array_rand($plantillas['resistencia_agua'])];
                $bisel = $plantillas['tipo_bisel'][array_rand($plantillas['tipo_bisel'])];
                
                // Eslabones depende del tipo de pulsera
                if (strpos(strtolower($pulsera), 'cuero') !== false || strpos(strtolower($pulsera), 'nato') !== false) {
                    $eslabones = 'No aplica (correa)';
                } elseif (strpos(strtolower($pulsera), 'silicona') !== false || strpos(strtolower($pulsera), 'caucho') !== false) {
                    $eslabones = 'No aplica (pulsera flexible)';
                } else {
                    $eslabones = $plantillas['eslabones'][array_rand(array_slice($plantillas['eslabones'], 0, 6))];
                }
                
                // Actualizar en BD
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
                    $movimiento,
                    $pulsera,
                    $peso,
                    $resistencia,
                    $bisel,
                    $eslabones,
                    $reloj['id_reloj']
                );
                
                if ($stmt->execute()) {
                    echo '<div class="resultado-item">';
                    echo '✅ <strong>Reloj #' . $reloj['id_reloj'] . ':</strong> ' . htmlspecialchars($reloj['nombre']) . '<br>';
                    echo '<small style="color: rgba(255, 255, 255, 0.7);">';
                    echo '⚙️ ' . $movimiento . ' | ';
                    echo '📿 ' . $pulsera . ' | ';
                    echo '⚖️ ' . $peso . ' | ';
                    echo '💧 ' . $resistencia;
                    echo '</small>';
                    echo '</div>';
                    $actualizados++;
                } else {
                    echo '<div class="resultado-item" style="border-color: #F44336;">';
                    echo '❌ Error al actualizar reloj #' . $reloj['id_reloj'];
                    echo '</div>';
                }
                $stmt->close();
            }
            
            echo '<hr style="border-color: rgba(76, 175, 80, 0.3); margin: 15px 0;">';
            echo '<p style="font-size: 1.2rem;"><strong>✅ Actualizados:</strong> ' . $actualizados . ' de ' . $totalRelojes . ' relojes</p>';
            if ($saltados > 0) {
                echo '<p style="font-size: 1.1rem; color: #FF9800;"><strong>⏭️ Omitidos:</strong> ' . $saltados . ' (ya tenían datos)</p>';
            }
            echo '</div>';
            
            echo '<div class="actions">';
            echo '<a href="verificar_campos_filtros.php" class="btn">📊 Ver Estadísticas</a>';
            echo '<a href="generar_datos_relojes_automatico.php" class="btn-secondary">🔄 Generar de Nuevo</a>';
            echo '</div>';
            
        } else {
            // Mostrar formulario de configuración
            ?>
            
            <div class="info-box">
                <h3>🎯 ACERCA DE ESTE GENERADOR</h3>
                <p>Este script genera datos de prueba <strong>realistas y variados</strong> para los campos de filtros:</p>
                <ul style="margin: 10px 0 0 20px; line-height: 1.8;">
                    <li>⚙️ <strong>Movimiento:</strong> 8 tipos diferentes (Cuarzo, Automático, Mecánico, etc.)</li>
                    <li>📿 <strong>Pulsera:</strong> 12 variaciones (Acero, Cuero, Silicona, etc.)</li>
                    <li>⚖️ <strong>Peso:</strong> Rangos realistas de 35g a 210g</li>
                    <li>💧 <strong>Resistencia al agua:</strong> 6 niveles (3ATM hasta 30ATM)</li>
                    <li>🔄 <strong>Tipo de bisel:</strong> 8 opciones diferentes</li>
                    <li>🔗 <strong>Eslabones:</strong> Se asigna según el tipo de pulsera</li>
                </ul>
            </div>

            <form method="POST">
                <div class="config-section">
                    <h3>⚙️ CONFIGURACIÓN</h3>
                    
                    <div class="form-group">
                        <label for="porcentaje">Porcentaje de relojes a actualizar:</label>
                        <select id="porcentaje" name="porcentaje">
                            <option value="50">50% - La mitad de los relojes</option>
                            <option value="70">70% - Mayoría de los relojes</option>
                            <option value="85">85% - Casi todos</option>
                            <option value="100" selected>100% - Todos los relojes</option>
                        </select>
                        <p style="color: rgba(255, 255, 255, 0.6); font-size: 0.9rem; margin-top: 5px;">
                            Con menos del 100%, algunos relojes quedarán sin datos (más realista).
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-item" style="max-width: 500px;">
                            <input type="checkbox" id="sobreescribir" name="sobreescribir" value="1">
                            <label for="sobreescribir">
                                <strong>Sobrescribir datos existentes</strong><br>
                                <small style="color: rgba(255, 255, 255, 0.6);">
                                    Si está desmarcado, solo actualizará relojes sin datos
                                </small>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" name="generar" value="1" class="btn">🤖 GENERAR DATOS AUTOMÁTICAMENTE</button>
                    <a href="agregar_datos_filtros_relojes.php" class="btn-secondary">📝 Modo Manual</a>
                    <a href="panel.php" class="btn-secondary">← Volver</a>
                </div>
            </form>

            <?php
        }
        $conn->close();
        ?>
    </div>
</body>
</html>

