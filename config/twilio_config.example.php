<?php
/**
 * CONFIGURACIÓN DE TWILIO WHATSAPP - ARCHIVO DE EJEMPLO
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo como 'twilio_config.php'
 * 2. Reemplaza los valores de ejemplo con tus credenciales reales
 * 3. NO subas 'twilio_config.php' a Git (ya está en .gitignore)
 * 
 * Para obtener tus credenciales:
 * 1. Ve a https://console.twilio.com/
 * 2. Copia tu Account SID y Auth Token
 * 3. Para Sandbox: https://console.twilio.com/us1/develop/sms/try-it-out/whatsapp-learn
 * 4. Conecta tu número de WhatsApp al sandbox
 */

// ⚠️ IMPORTANTE: Cambiar estos valores por los de tu cuenta Twilio
define('TWILIO_ACCOUNT_SID', 'TU_ACCOUNT_SID_AQUI');
define('TWILIO_AUTH_TOKEN', 'TU_AUTH_TOKEN_AQUI');

// Número de WhatsApp de Twilio (Sandbox o Producción)
// Sandbox format: whatsapp:+14155238886
// Producción format: whatsapp:+57XXXXXXXXXX
define('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886'); // Número del sandbox por defecto

// Número de WhatsApp del admin/negocio para recibir notificaciones
define('ADMIN_WHATSAPP', '57XXXXXXXXX'); // Tu número de negocio

// Configuración de entorno
define('TWILIO_ENVIRONMENT', 'sandbox'); // 'sandbox' o 'production'

// Logs
define('TWILIO_LOG_ENABLED', true);
define('TWILIO_LOG_FILE', __DIR__ . '/../logs/whatsapp_notifications.log');

// Verificar que las credenciales estén configuradas
function verificarConfiguracionTwilio() {
    if (TWILIO_ACCOUNT_SID === 'TU_ACCOUNT_SID_AQUI' || 
        TWILIO_AUTH_TOKEN === 'TU_AUTH_TOKEN_AQUI') {
        return false;
    }
    return true;
}
?>

