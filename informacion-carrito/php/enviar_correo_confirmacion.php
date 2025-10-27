<?php
/**
 * ENVIAR CORREO DE CONFIRMACIÓN AL CLIENTE
 * Cuando sube el comprobante Nequi
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_log('[CORREO-CONFIRMACION] 📦 Archivo enviar_correo_confirmacion.php cargado');

function enviarCorreoConfirmacionOrden($correo, $nombre, $id_orden, $nombre_reloj, $total, $token_verificacion) {
    error_log('[CORREO-CONFIRMACION] 🔵 Función llamada');
    error_log('[CORREO-CONFIRMACION] 📧 Destinatario: ' . $correo);
    error_log('[CORREO-CONFIRMACION] 👤 Nombre: ' . $nombre);
    error_log('[CORREO-CONFIRMACION] 📋 Orden: #' . $id_orden);
    error_log('[CORREO-CONFIRMACION] 🎯 Producto: ' . $nombre_reloj);
    error_log('[CORREO-CONFIRMACION] 💰 Total: $' . number_format($total, 0, ',', '.'));
    error_log('[CORREO-CONFIRMACION] 🔑 Token: ' . $token_verificacion);
    
    try {
        error_log('[CORREO-CONFIRMACION] 🔧 Iniciando PHPMailer...');
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 2; // Habilitar debug detallado
        $mail->Debugoutput = function($str, $level) {
            error_log("[CORREO-CONFIRMACION-SMTP] $str");
        };
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'davidpascuas708@gmail.com';
        $mail->Password = 'qinc wznz hvmv zqwu';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        error_log('[CORREO-CONFIRMACION] ⚙️ Configuración SMTP completada');
        
        $mail->setFrom('davidpascuas708@gmail.com', 'FINOSO');
        $mail->addAddress($correo, $nombre);
        
        $mail->isHTML(true);
        $mail->Subject = '✅ Comprobante Recibido - Orden #' . $id_orden . ' - FINOSO';
        
        $total_formateado = '$' . number_format($total, 0, ',', '.');
        
        $mail->Body = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; margin: 0; padding: 0; background-color: #0a0a0a; }
        .container { max-width: 600px; margin: 0 auto; background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%); }
        .header { background: linear-gradient(135deg, #d4af37 0%, #aa8a2e 100%); padding: 40px 20px; text-align: center; }
        .logo { font-size: 42px; font-weight: 800; color: #000; letter-spacing: 3px; margin: 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
        .content { padding: 40px 30px; color: #e0e0e0; }
        .success-icon { text-align: center; font-size: 64px; margin-bottom: 20px; }
        h1 { color: #d4af37; font-size: 28px; margin-bottom: 10px; text-align: center; }
        .subtitle { text-align: center; color: #b0b0b0; font-size: 16px; margin-bottom: 30px; }
        .info-box { background: #1a1a1a; border-left: 4px solid #d4af37; padding: 20px; margin: 25px 0; border-radius: 4px; }
        .info-box h2 { color: #d4af37; font-size: 18px; margin: 0 0 15px 0; }
        .info-item { margin: 12px 0; padding: 8px 0; border-bottom: 1px solid #2a2a2a; }
        .info-item:last-child { border-bottom: none; }
        .label { color: #888; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; }
        .value { color: #fff; font-size: 16px; font-weight: 600; margin-top: 5px; }
        .token-box { background: linear-gradient(135deg, #d4af37 0%, #aa8a2e 100%); color: #000; padding: 15px; border-radius: 8px; text-align: center; margin: 25px 0; font-size: 24px; font-weight: 700; letter-spacing: 2px; }
        .status-badge { display: inline-block; background: #ff9800; color: #000; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; margin: 20px 0; }
        .steps { background: #1a1a1a; padding: 25px; border-radius: 8px; margin: 25px 0; }
        .steps h3 { color: #d4af37; font-size: 18px; margin: 0 0 20px 0; }
        .step { display: flex; align-items: flex-start; margin: 15px 0; }
        .step-number { background: #d4af37; color: #000; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; margin-right: 15px; }
        .step-text { color: #c0c0c0; line-height: 1.6; }
        .footer { background: #0d0d0d; padding: 30px; text-align: center; color: #666; font-size: 13px; border-top: 1px solid #2a2a2a; }
        .footer a { color: #d4af37; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">FINOSO</div>
        </div>
        <div class="content">
            <div class="success-icon">✅</div>
            <h1>¡Comprobante Recibido!</h1>
            <p class="subtitle">Tu orden ha sido registrada exitosamente</p>
            
            <div class="info-box">
                <h2>📋 Información de tu Orden</h2>
                <div class="info-item">
                    <div class="label">Número de Orden</div>
                    <div class="value">#' . $id_orden . '</div>
                </div>
                <div class="info-item">
                    <div class="label">Producto</div>
                    <div class="value">' . htmlspecialchars($nombre_reloj) . '</div>
                </div>
                <div class="info-item">
                    <div class="label">Total</div>
                    <div class="value">' . $total_formateado . ' COP</div>
                </div>
                <div class="info-item">
                    <div class="label">Método de Pago</div>
                    <div class="value">Nequi</div>
                </div>
            </div>
            
            <div style="text-align: center;">
                <div class="status-badge">⏳ Pendiente de Verificación</div>
            </div>
            
            <div class="token-box">
                Token: ' . $token_verificacion . '
            </div>
            <p style="text-align: center; color: #888; font-size: 13px; margin-top: 10px;">
                Guarda este token para consultar el estado de tu orden
            </p>
            
            <div class="steps">
                <h3>📋 Próximos Pasos</h3>
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-text">Tu comprobante será verificado en las próximas <strong>3 horas</strong>.</div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-text">Si la verificación es correcta, recibirás la confirmación del pedido por correo.</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-text">Si no se valida o hay inconsistencias en el monto o datos, te notificaremos por correo con los pasos a seguir.</div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-text">Conserva tu comprobante y el token de verificación para cualquier revisión.</div>
                </div>
            </div>
            
            <p style="color: #888; text-align: center; margin-top: 30px;">
                ¿Tienes preguntas? Contáctanos en <a href="mailto:finosoweb@gmail.com" style="color: #d4af37;">finosoweb@gmail.com</a>
            </p>
        </div>
        <div class="footer">
            <p><strong>FINOSO</strong> - Relojes de Lujo</p>
            <p>Este es un correo automático, por favor no responder directamente.</p>
        </div>
    </div>
</body>
</html>';
        
        $mail->AltBody = "¡Comprobante Recibido!\n\n"
            . "Orden #" . $id_orden . "\n"
            . "Producto: " . $nombre_reloj . "\n"
            . "Total: " . $total_formateado . " COP\n"
            . "Token: " . $token_verificacion . "\n\n"
            . "Estado: Pendiente de Verificación\n\n"
            . "Tu comprobante será verificado en las próximas 3 horas.\n\n"
            . "FINOSO - Relojes de Lujo";
        
        error_log('[CORREO-CONFIRMACION] 📤 Intentando enviar correo...');
        $mail->send();
        error_log('[CORREO-CONFIRMACION] ✅ ✓ Correo enviado exitosamente a: ' . $correo);
        return true;
    } catch (Exception $e) {
        error_log('[CORREO-CONFIRMACION] ❌ ✗ Error al enviar correo: ' . $e->getMessage());
        error_log('[CORREO-CONFIRMACION] ❌ ErrorInfo: ' . $mail->ErrorInfo);
        return false;
    }
}
?>

