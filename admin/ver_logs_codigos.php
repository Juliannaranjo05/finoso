<?php
/**
 * VISOR DE LOGS - CÓDIGOS DE DESCUENTO
 * Muestra los últimos logs relacionados con la generación y asignación de códigos
 */

// Rutas comunes de logs en XAMPP
$posibles_logs = [
    'C:/xampp/apache/logs/error.log',
    'C:/xampp/php/logs/php_error_log',
    '/var/log/apache2/error.log',
    '/opt/lampp/logs/error_log',
    ini_get('error_log')
];

$log_file = null;
foreach ($posibles_logs as $path) {
    if (file_exists($path)) {
        $log_file = $path;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs - Códigos de Descuento</title>
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
            max-width: 1400px;
            margin: 0 auto;
            background: #000;
            border: 2px solid #FFCF66;
            border-radius: 10px;
            padding: 20px;
        }
        h1 {
            color: #FFCF66;
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .info {
            background: rgba(255, 207, 102, 0.1);
            border: 1px solid #FFCF66;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #FFCF66;
        }
        .log-container {
            background: #000;
            border: 1px solid #333;
            padding: 15px;
            max-height: 600px;
            overflow-y: auto;
            font-size: 12px;
            line-height: 1.6;
        }
        .log-line {
            margin-bottom: 5px;
            padding: 5px;
            border-left: 3px solid transparent;
        }
        .log-line.inicio {
            background: rgba(255, 207, 102, 0.1);
            border-left-color: #FFCF66;
            font-weight: bold;
        }
        .log-line.exito {
            background: rgba(76, 175, 80, 0.1);
            border-left-color: #4CAF50;
            color: #4CAF50;
        }
        .log-line.error {
            background: rgba(244, 67, 54, 0.1);
            border-left-color: #F44336;
            color: #F44336;
        }
        .log-line.warning {
            background: rgba(255, 193, 7, 0.1);
            border-left-color: #FFC107;
            color: #FFC107;
        }
        .log-line.info {
            color: #03A9F4;
        }
        .timestamp {
            color: #888;
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
            margin: 10px 5px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 207, 102, 0.4);
        }
        .actions {
            text-align: center;
            margin-bottom: 20px;
        }
        .no-logs {
            text-align: center;
            padding: 40px;
            color: #888;
        }
        .filter {
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(255, 207, 102, 0.05);
            border-radius: 5px;
        }
        .filter label {
            color: #FFCF66;
            margin-right: 10px;
        }
        .filter select {
            background: #000;
            color: #0f0;
            border: 1px solid #FFCF66;
            padding: 5px 10px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 VISOR DE LOGS - CÓDIGOS DE DESCUENTO</h1>
        
        <?php if ($log_file && file_exists($log_file)): ?>
            <div class="info">
                <strong>Archivo de log:</strong> <?php echo $log_file; ?><br>
                <strong>Tamaño:</strong> <?php echo round(filesize($log_file) / 1024, 2); ?> KB<br>
                <strong>Última modificación:</strong> <?php echo date('Y-m-d H:i:s', filemtime($log_file)); ?>
            </div>
            
            <div class="actions">
                <button class="btn" onclick="location.reload()">🔄 Recargar</button>
                <a href="../panel.php" class="btn">← Volver al Panel</a>
            </div>
            
            <div class="filter">
                <label>Filtrar por:</label>
                <select id="filtro" onchange="filtrarLogs()">
                    <option value="todos">Todos los logs</option>
                    <option value="codigo" selected>Solo códigos</option>
                    <option value="exito">Solo éxitos</option>
                    <option value="error">Solo errores</option>
                </select>
                <label style="margin-left: 20px;">Últimas líneas:</label>
                <select id="lineas" onchange="location.href='?lineas='+this.value">
                    <option value="100" <?php echo (isset($_GET['lineas']) && $_GET['lineas'] == 100) ? 'selected' : ''; ?>>100</option>
                    <option value="200" <?php echo (isset($_GET['lineas']) && $_GET['lineas'] == 200) ? 'selected' : ''; ?>>200</option>
                    <option value="500" <?php echo (!isset($_GET['lineas']) || $_GET['lineas'] == 500) ? 'selected' : ''; ?>>500</option>
                    <option value="1000" <?php echo (isset($_GET['lineas']) && $_GET['lineas'] == 1000) ? 'selected' : ''; ?>>1000</option>
                </select>
            </div>
            
            <div class="log-container" id="logContainer">
                <?php
                $lineas = isset($_GET['lineas']) ? intval($_GET['lineas']) : 500;
                
                // Leer últimas líneas del log
                $file = new SplFileObject($log_file, 'r');
                $file->seek(PHP_INT_MAX);
                $last_line = $file->key();
                $lines = new LimitIterator($file, max(0, $last_line - $lineas), $last_line);
                
                $log_lines = array();
                foreach($lines as $line) {
                    $log_lines[] = $line;
                }
                $log_lines = array_reverse($log_lines);
                
                if (empty($log_lines)) {
                    echo '<div class="no-logs">No hay logs recientes</div>';
                } else {
                    foreach ($log_lines as $line) {
                        $line = trim($line);
                        if (empty($line)) continue;
                        
                        // Clasificar tipo de log
                        $clase = '';
                        if (strpos($line, '========== INICIO') !== false) {
                            $clase = 'inicio';
                        } elseif (strpos($line, '✓✓✓') !== false || strpos($line, 'EXITOSAMENTE') !== false) {
                            $clase = 'exito';
                        } elseif (strpos($line, '✗') !== false || strpos($line, 'ERROR') !== false || strpos($line, 'Error') !== false) {
                            $clase = 'error';
                        } elseif (strpos($line, '⚠') !== false || strpos($line, 'WARNING') !== false) {
                            $clase = 'warning';
                        } elseif (strpos($line, '[APROBAR]') !== false || strpos($line, '[COMPROBANTE]') !== false) {
                            $clase = 'info';
                        }
                        
                        // Extraer timestamp si existe
                        $timestamp = '';
                        if (preg_match('/^\[(.*?)\]/', $line, $matches)) {
                            $timestamp = '<span class="timestamp">[' . $matches[1] . ']</span>';
                            $line = substr($line, strlen($matches[0]));
                        }
                        
                        // Aplicar colores a símbolos
                        $line = str_replace('✓', '<span style="color: #4CAF50; font-weight: bold;">✓</span>', $line);
                        $line = str_replace('✗', '<span style="color: #F44336; font-weight: bold;">✗</span>', $line);
                        $line = str_replace('⚠', '<span style="color: #FFC107; font-weight: bold;">⚠</span>', $line);
                        
                        echo '<div class="log-line ' . $clase . '" data-tipo="' . $clase . '">';
                        echo $timestamp . htmlspecialchars_decode($line);
                        echo '</div>';
                    }
                }
                ?>
            </div>
            
        <?php else: ?>
            <div class="info" style="background: rgba(244, 67, 54, 0.1); border-color: #F44336; color: #F44336;">
                <strong>❌ No se encontró el archivo de logs</strong><br><br>
                Rutas buscadas:<br>
                <?php foreach ($posibles_logs as $path): ?>
                    - <?php echo $path; ?> <?php echo file_exists($path) ? '✓' : '✗'; ?><br>
                <?php endforeach; ?>
                <br>
                <strong>Solución:</strong><br>
                1. Verifica que error_log esté habilitado en php.ini<br>
                2. Configura la ruta correcta del log en php.ini<br>
                3. Reinicia Apache
            </div>
            
            <div class="actions">
                <a href="../panel.php" class="btn">← Volver al Panel</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function filtrarLogs() {
            const filtro = document.getElementById('filtro').value;
            const lineas = document.querySelectorAll('.log-line');
            
            lineas.forEach(linea => {
                const tipo = linea.getAttribute('data-tipo');
                const texto = linea.textContent;
                
                let mostrar = false;
                
                switch(filtro) {
                    case 'todos':
                        mostrar = true;
                        break;
                    case 'codigo':
                        mostrar = texto.includes('[APROBAR]') || texto.includes('[COMPROBANTE]') || 
                                  texto.includes('código') || texto.includes('CÓDIGO');
                        break;
                    case 'exito':
                        mostrar = tipo === 'exito';
                        break;
                    case 'error':
                        mostrar = tipo === 'error';
                        break;
                }
                
                linea.style.display = mostrar ? 'block' : 'none';
            });
        }
        
        // Auto-scroll al fondo
        const container = document.getElementById('logContainer');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
        
        // Aplicar filtro inicial
        filtrarLogs();
    </script>
</body>
</html>

