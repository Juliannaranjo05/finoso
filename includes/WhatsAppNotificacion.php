<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/twilio_config.php';

use Twilio\Rest\Client;

class WhatsAppNotificacion {
    private $twilio;
    private $fromNumber;
    private $logEnabled;
    private $logFile;
    
    public function __construct() {
        // Verificar configuración
        if (!verificarConfiguracionTwilio()) {
            $this->log('ERROR: Credenciales de Twilio no configuradas', 'ERROR');
            throw new Exception('Credenciales de Twilio no configuradas. Edita config/twilio_config.php');
        }
        
        // Inicializar cliente Twilio
        $this->twilio = new Client(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
        $this->fromNumber = TWILIO_WHATSAPP_FROM;
        $this->logEnabled = TWILIO_LOG_ENABLED;
        $this->logFile = TWILIO_LOG_FILE;
        
        $this->log('WhatsAppNotificacion inicializada correctamente', 'INFO');
    }
    
    /**
     * Enviar mensaje de WhatsApp
     * 
     * @param string $to Número de teléfono destino (573001234567)
     * @param string $mensaje Texto del mensaje
     * @param string $tipo Tipo de notificación para logging
     * @return array Resultado del envío
     */
    public function enviarMensaje($to, $mensaje, $tipo = 'general') {
        try {
            // Formatear número de teléfono
            $toFormatted = $this->formatearNumero($to);
            
            $this->log("Intentando enviar mensaje tipo '{$tipo}' a {$toFormatted}", 'INFO');
            
            // Enviar mensaje
            $message = $this->twilio->messages->create(
                $toFormatted,
                [
                    'from' => $this->fromNumber,
                    'body' => $mensaje
                ]
            );
            
            $this->log("Mensaje enviado exitosamente. SID: {$message->sid}", 'SUCCESS');
            
            return [
                'success' => true,
                'sid' => $message->sid,
                'status' => $message->status,
                'to' => $toFormatted,
                'tipo' => $tipo
            ];
            
        } catch (Exception $e) {
            $this->log("Error al enviar mensaje: " . $e->getMessage(), 'ERROR');
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'to' => $to,
                'tipo' => $tipo
            ];
        }
    }
    
    /**
     * Formatear número de teléfono para WhatsApp
     * Entrada: 3001234567 o 573001234567
     * Salida: whatsapp:+573001234567
     */
    private function formatearNumero($numero) {
        // Limpiar el número
        $numero = preg_replace('/[^0-9]/', '', $numero);
        
        // Agregar código de país si no lo tiene
        if (substr($numero, 0, 2) !== '57') {
            $numero = '57' . $numero;
        }
        
        // Formato WhatsApp
        return 'whatsapp:+' . $numero;
    }
    
    /**
     * Logging de actividad
     */
    private function log($mensaje, $nivel = 'INFO') {
        if (!$this->logEnabled) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$nivel}] {$mensaje}\n";
        
        // Crear directorio de logs si no existe
        $logDir = dirname($this->logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        // Escribir en archivo
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        
        // También mostrar en consola en desarrollo
        if (TWILIO_ENVIRONMENT === 'sandbox') {
            error_log($logMessage);
        }
    }
    
    /**
     * Obtener el último estado del mensaje
     */
    public function obtenerEstadoMensaje($messageSid) {
        try {
            $message = $this->twilio->messages($messageSid)->fetch();
            
            return [
                'success' => true,
                'status' => $message->status,
                'dateCreated' => $message->dateCreated->format('Y-m-d H:i:s'),
                'dateSent' => $message->dateSent ? $message->dateSent->format('Y-m-d H:i:s') : null,
                'errorCode' => $message->errorCode,
                'errorMessage' => $message->errorMessage
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>

