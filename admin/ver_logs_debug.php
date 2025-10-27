<?php
/**
 * VISOR DE LOGS DEBUG - SISTEMA PERSONALIZADO
 * Lee el archivo de logs interno del proyecto
 */

$log_file = __DIR__ . '/logs/codigos_descuento.log';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Logs - Códigos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            background: #1a1a1a;
            color: #0f0;
            padding: 20px;
        }
        .container {
            max-width: 1600px;
            margin: 0 auto;
            background: #000;
            border: 2px solid #FFCF66;
            border-radius: 10px;
            padding: 20px;
        }
        h1 {
            color: #FFCF66;
            text-align: center;
            margin-bottom: 10px;
            font-size: 24px;
        }
        .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .info {
            background: rgba(255, 207, 102, 0.1);
            border: 1px solid #FFCF66;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #FFCF66;
            font-size: 13px;
        }
        .warning {
            background: rgba(244, 67, 54, 0.1);
            border: 1px solid #F44336;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #F44336;
            text-align: center;
        }
        .log-container {
            background: #000;
            border: 1px solid #333;
            padding: 15px;
            max-height: 70vh;
            overflow-y: auto;
            font-size: 12px;
            line-height: 1.8;
            user-select: text;
            cursor: text;
        }
        .log-line {
            margin-bottom: 3px;
            padding: 6px 10px;
            border-left: 3px solid transparent;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .log-line.HEADER {
            background: rgba(255, 207, 102, 0.15);
            border-left-color: #FFCF66;
            font-weight: bold;
            color: #FFCF66;
            font-size: 14px;
        }
        .log-line.SUCCESS {
            background: rgba(76, 175, 80, 0.1);
            border-left-color: #4CAF50;
            color: #4CAF50;
        }
        .log-line.ERROR {
            background: rgba(244, 67, 54, 0.1);
            border-left-color: #F44336;
            color: #F44336;
            font-weight: bold;
        }
        .log-line.WARNING {
            background: rgba(255, 193, 7, 0.1);
            border-left-color: #FFC107;
            color: #FFC107;
        }
        .log-line.INFO {
            color: #03A9F4;
        }
        .log-line.DEBUG {
            color: #9E9E9E;
            font-size: 11px;
        }
        .timestamp {
            color: #666;
            margin-right: 10px;
        }
        .tipo {
            font-weight: bold;
            margin-right: 10px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #FFCF66 0%, #FFB84D 100%);
            color: #000;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 207, 102, 0.4);
        }
        .btn-danger {
            background: linear-gradient(135deg, #F44336 0%, #D32F2F 100%);
            color: white;
        }
        .actions {
            text-align: center;
            margin-bottom: 20px;
        }
        .no-logs {
            text-align: center;
            padding: 40px;
            color: #888;
            font-size: 14px;
        }
        code {
            background: #2d3748;
            color: #FFCF66;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .stats {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .stat-box {
            flex: 1;
            min-width: 150px;
            background: rgba(255, 207, 102, 0.05);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #FFCF66;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 DEBUG LOGS - CÓDIGOS DE DESCUENTO</h1>
        <p class="subtitle">Sistema de logs interno del proyecto</p>

        <?php if (file_exists($log_file)): ?>
            <?php
            $lineas = file($log_file, FILE_IGNORE_NEW_LINES);
            $lineas = array_reverse($lineas); // Más recientes primero
            
            // Estadísticas
            $total_lineas = count($lineas);
            $success_count = 0;
            $error_count = 0;
            $warning_count = 0;
            
            foreach ($lineas as $linea) {
                if (strpos($linea, '[SUCCESS]') !== false) $success_count++;
                if (strpos($linea, '[ERROR]') !== false) $error_count++;
                if (strpos($linea, '[WARNING]') !== false) $warning_count++;
            }
            ?>
            
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $total_lineas; ?></div>
                    <div class="stat-label">Total Líneas</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="color: #4CAF50;"><?php echo $success_count; ?></div>
                    <div class="stat-label">Éxitos</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="color: #F44336;"><?php echo $error_count; ?></div>
                    <div class="stat-label">Errores</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="color: #FFC107;"><?php echo $warning_count; ?></div>
                    <div class="stat-label">Advertencias</div>
                </div>
            </div>
            
            <div class="info">
                <strong>📁 Archivo:</strong> <?php echo $log_file; ?><br>
                <strong>📏 Tamaño:</strong> <?php echo round(filesize($log_file) / 1024, 2); ?> KB<br>
                <strong>🕐 Modificado:</strong> <?php echo date('Y-m-d H:i:s', filemtime($log_file)); ?>
            </div>
            
            <div class="actions">
                <button class="btn" onclick="location.reload()">🔄 Recargar</button>
                <button class="btn" onclick="copiarLogs()">📋 Copiar Todo</button>
                <button class="btn" onclick="descargarLogs()">💾 Descargar TXT</button>
                <form method="post" style="display: inline;">
                    <button type="submit" name="limpiar" class="btn btn-danger" onclick="return confirm('¿Seguro que quieres limpiar todos los logs?')">🗑️ Limpiar Logs</button>
                </form>
                <a href="panel.php" class="btn">← Volver</a>
            </div>
            
            <!-- Textarea oculto para copiar -->
            <textarea id="logsCopy" style="position: absolute; left: -9999px; top: -9999px;"><?php
                if (!empty($lineas)) {
                    foreach (array_reverse($lineas) as $linea) { // Orden original
                        echo htmlspecialchars($linea) . "\n";
                    }
                }
            ?></textarea>
            
            <?php
            if (isset($_POST['limpiar'])) {
                file_put_contents($log_file, '');
                echo '<script>location.href = location.href.split("?")[0];</script>';
            }
            ?>
            
            <div class="log-container" id="logContainer">
                <?php if (empty($lineas)): ?>
                    <div class="no-logs">
                        📝 No hay logs registrados aún.<br><br>
                        Realiza una acción (aprobar orden) para ver logs aquí.
                    </div>
                <?php else: ?>
                    <?php foreach ($lineas as $linea): ?>
                        <?php
                        $linea = trim($linea);
                        if (empty($linea)) continue;
                        
                        // Extraer componentes
                        $tipo = 'INFO';
                        if (preg_match('/\[(SUCCESS|ERROR|WARNING|INFO|DEBUG|HEADER|SEPARATOR)\]/', $linea, $matches)) {
                            $tipo = $matches[1];
                        }
                        
                        // Formatear
                        $linea_html = htmlspecialchars($linea);
                        $linea_html = str_replace('✓', '<span style="color: #4CAF50;">✓</span>', $linea_html);
                        $linea_html = str_replace('✗', '<span style="color: #F44336;">✗</span>', $linea_html);
                        $linea_html = str_replace('⚠', '<span style="color: #FFC107;">⚠</span>', $linea_html);
                        
                        echo '<div class="log-line ' . $tipo . '">' . $linea_html . '</div>';
                        ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
        <?php else: ?>
            <div class="warning">
                <h2 style="margin-bottom: 15px;">⚠️ Archivo de logs no encontrado</h2>
                <p>El archivo de logs aún no se ha creado.</p>
                <p style="margin-top: 10px;"><strong>Ruta esperada:</strong><br><code><?php echo $log_file; ?></code></p>
                <p style="margin-top: 15px;">El archivo se creará automáticamente cuando apruebes la primera orden.</p>
            </div>
            
            <div class="actions">
                <button class="btn" onclick="location.reload()">🔄 Recargar</button>
                <a href="panel.php" class="btn">← Volver</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Función para copiar logs al portapapeles
        function copiarLogs() {
            const textarea = document.getElementById('logsCopy');
            textarea.select();
            textarea.setSelectionRange(0, 99999); // Para móviles
            
            try {
                document.execCommand('copy');
                
                // Feedback visual
                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.innerHTML = '✅ Copiado!';
                btn.style.background = 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)';
                btn.style.color = 'white';
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                    btn.style.color = '';
                }, 2000);
            } catch (err) {
                alert('No se pudo copiar. Intenta manualmente seleccionando el texto.');
            }
        }
        
        // Función para descargar logs como archivo .txt
        function descargarLogs() {
            const textarea = document.getElementById('logsCopy');
            const contenido = textarea.value;
            const fecha = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
            const nombreArchivo = 'logs_codigos_descuento_' + fecha + '.txt';
            
            // Crear blob y descarga
            const blob = new Blob([contenido], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = nombreArchivo;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            // Feedback visual
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '✅ Descargado!';
            btn.style.background = 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)';
            btn.style.color = 'white';
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '';
                btn.style.color = '';
            }, 2000);
        }
        
        // Auto-refresh cada 3 segundos (comentado para que no interrumpa al copiar)
        // setTimeout(function() {
        //     location.reload();
        // }, 3000);
        
        // Botón manual de refresh
        console.log('Logs cargados. Haz clic en "Recargar" para actualizar.');
    </script>
</body>
</html>

