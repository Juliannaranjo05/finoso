<?php
/**
 * ARCHIVO DE PRUEBA - NOTIFICACIONES WHATSAPP
 * 
 * Este archivo te permite probar el envío de mensajes de WhatsApp
 * antes de integrar con el sistema completo
 * 
 * INSTRUCCIONES:
 * 1. Configura config/twilio_config.php con tus credenciales
 * 2. Conecta tu WhatsApp al sandbox de Twilio
 * 3. Ejecuta este archivo: http://localhost/finoso/test_whatsapp.php
 */

require_once 'includes/WhatsAppNotificacion.php';
require_once 'includes/WhatsAppTemplates.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test WhatsApp Notifications - FINOSO</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #FFCF66;
            border-bottom: 3px solid #FFCF66;
            padding-bottom: 10px;
        }
        .status {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-weight: bold;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        button {
            background: #FFCF66;
            color: #000;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px 5px;
            transition: all 0.3s;
        }
        button:hover {
            background: #FFB800;
            transform: translateY(-2px);
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .pre-code {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 10px 0;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test WhatsApp Notifications - FINOSO</h1>
        
        <?php
        // Verificar configuración
        if (!verificarConfiguracionTwilio()) {
            echo '<div class="status error">';
            echo '❌ ERROR: Credenciales de Twilio no configuradas<br><br>';
            echo 'Por favor edita el archivo: <code>config/twilio_config.php</code><br>';
            echo 'Y reemplaza TU_ACCOUNT_SID_AQUI y TU_AUTH_TOKEN_AQUI con tus credenciales reales';
            echo '</div>';
            echo '<a href="INSTRUCCIONES_WHATSAPP_TWILIO.md" target="_blank">📖 Ver instrucciones completas</a>';
            exit;
        }
        
        echo '<div class="status success">';
        echo '✅ Credenciales de Twilio configuradas correctamente';
        echo '</div>';
        
        // Procesar envío de prueba
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_prueba'])) {
            $telefono = $_POST['telefono'];
            $tipoMensaje = $_POST['tipo_mensaje'];
            
            try {
                $whatsapp = new WhatsAppNotificacion();
                
                // Datos de ejemplo para el mensaje
                $datosEjemplo = [
                    'orden_id' => '12345',
                    'nombre_reloj' => 'Patek Philippe Bicolor Dorado - Negro',
                    'total' => 125000,
                    'nombre_cliente' => 'Juan Pérez',
                    'telefono' => $telefono,
                    'email' => 'cliente@example.com',
                    'transportadora' => 'SERVIENTREGA',
                    'guia' => '987654321',
                    'fecha_estimada' => date('d M Y', strtotime('+3 days')),
                    'metodo_pago' => 'Nequi'
                ];
                
                // Generar mensaje según el tipo
                switch ($tipoMensaje) {
                    case 'compra_exitosa':
                        $mensaje = WhatsAppTemplates::compraExitosa($datosEjemplo);
                        break;
                    case 'pago_aprobado':
                        $mensaje = WhatsAppTemplates::pagoAprobado($datosEjemplo);
                        break;
                    case 'producto_enviado':
                        $mensaje = WhatsAppTemplates::productoEnviado($datosEjemplo);
                        break;
                    case 'producto_entregado':
                        $mensaje = WhatsAppTemplates::productoEntregado($datosEjemplo);
                        break;
                    case 'nueva_orden_admin':
                        $mensaje = WhatsAppTemplates::nuevaOrdenAdmin($datosEjemplo);
                        break;
                    default:
                        $mensaje = "🧪 Mensaje de prueba FINOSO\n\nEste es un mensaje de prueba del sistema de notificaciones.\n\n✅ Si recibes esto, todo funciona correctamente!";
                }
                
                // Enviar mensaje
                $resultado = $whatsapp->enviarMensaje($telefono, $mensaje, $tipoMensaje);
                
                if ($resultado['success']) {
                    echo '<div class="status success">';
                    echo '✅ ¡Mensaje enviado exitosamente!<br><br>';
                    echo '<strong>Detalles:</strong><br>';
                    echo 'SID: ' . $resultado['sid'] . '<br>';
                    echo 'Estado: ' . $resultado['status'] . '<br>';
                    echo 'A: ' . $resultado['to'] . '<br>';
                    echo '<br>📱 Revisa tu WhatsApp!';
                    echo '</div>';
                    
                    echo '<div class="info">Mensaje enviado:</div>';
                    echo '<div class="pre-code">' . nl2br(htmlspecialchars($mensaje)) . '</div>';
                } else {
                    echo '<div class="status error">';
                    echo '❌ Error al enviar mensaje:<br>';
                    echo htmlspecialchars($resultado['error']);
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="status error">';
                echo '❌ Error: ' . htmlspecialchars($e->getMessage());
                echo '</div>';
            }
        }
        ?>
        
        <h2>📝 Enviar mensaje de prueba</h2>
        
        <div class="info">
            <strong>⚠️ IMPORTANTE:</strong> Antes de enviar, asegúrate de:<br>
            1. Haber conectado tu WhatsApp al sandbox de Twilio<br>
            2. Enviado "join [código]" al número +1 (415) 523-8886<br>
            <br>
            <a href="INSTRUCCIONES_WHATSAPP_TWILIO.md" target="_blank">📖 Ver instrucciones completas</a>
        </div>
        
        <form method="POST">
            <label>Tu número de WhatsApp:</label>
            <input type="text" name="telefono" placeholder="3001234567 o 573001234567" required>
            <small>Escribe tu número sin espacios ni símbolos</small>
            
            <label>Tipo de mensaje:</label>
            <select name="tipo_mensaje" style="width: 100%; padding: 10px; margin: 10px 0; border: 2px solid #ddd; border-radius: 5px;">
                <option value="simple">Mensaje Simple de Prueba</option>
                <option value="compra_exitosa">1. Compra Exitosa</option>
                <option value="pago_aprobado">2. Pago Aprobado</option>
                <option value="producto_enviado">3. Producto Enviado</option>
                <option value="producto_entregado">4. Producto Entregado</option>
                <option value="nueva_orden_admin">5. Nueva Orden (Admin)</option>
            </select>
            
            <br><br>
            <button type="submit" name="enviar_prueba">🚀 Enviar Mensaje de Prueba</button>
        </form>
        
        <br><hr><br>
        
        <h2>📊 Información del Sistema</h2>
        <div class="pre-code">
            <strong>Entorno:</strong> <?php echo TWILIO_ENVIRONMENT; ?><br>
            <strong>Número FROM:</strong> <?php echo TWILIO_WHATSAPP_FROM; ?><br>
            <strong>Admin WhatsApp:</strong> <?php echo ADMIN_WHATSAPP; ?><br>
            <strong>Logs habilitados:</strong> <?php echo TWILIO_LOG_ENABLED ? 'Sí' : 'No'; ?><br>
            <strong>Archivo de logs:</strong> <?php echo TWILIO_LOG_FILE; ?>
        </div>
    </div>
</body>
</html>

