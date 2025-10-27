# 📱 CONFIGURACIÓN DE WHATSAPP CON TWILIO

## 🚀 PASO 1: Crear cuenta en Twilio (GRATIS)

1. Ve a: https://www.twilio.com/try-twilio
2. Regístrate con tu email
3. Verifica tu número de teléfono
4. **IMPORTANTE:** Anota tu **Account SID** y **Auth Token**

## 📞 PASO 2: Configurar WhatsApp Sandbox (GRATIS)

1. En tu consola de Twilio, ve a:
   https://console.twilio.com/us1/develop/sms/try-it-out/whatsapp-learn

2. Verás un código como: **"join [código-único]"**
   Ejemplo: "join happy-mountain"

3. **Conecta tu WhatsApp:**
   - Envía un mensaje de WhatsApp al número: **+1 (415) 523-8886**
   - Mensaje: **"join [tu-código-aquí]"**
   - Ejemplo: "join happy-mountain"

4. Recibirás confirmación de Twilio: "You are all set!"

## ⚙️ PASO 3: Configurar el sistema

1. Edita el archivo: `config/twilio_config.php`

2. Reemplaza estos valores:
   ```php
   define('TWILIO_ACCOUNT_SID', 'ACxxxxxxxxxxxxxxxxxxxxx'); // Tu Account SID
   define('TWILIO_AUTH_TOKEN', 'tu_auth_token_aqui');      // Tu Auth Token
   ```

3. El número FROM ya está configurado para sandbox:
   ```php
   define('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886');
   ```

## 🧪 PASO 4: Probar el sistema

1. Ejecuta el archivo de prueba:
   ```
   http://localhost/finoso/test_whatsapp.php
   ```

2. Deberías recibir un mensaje de WhatsApp en tu número conectado

## 📋 PASO 5: Integración con el sistema

Las notificaciones ya están integradas en:

- ✅ **Compra exitosa:** `informacion/php/subir_comprobante.php`
- ✅ **Pago aprobado:** `admin/php/aprobar_pago.php` (crear)
- ✅ **Producto enviado:** `admin/php/marcar_enviado.php` (crear)
- ✅ **Producto entregado:** `admin/php/marcar_entregado.php` (crear)
- ✅ **Nueva orden admin:** Automático al recibir comprobante

## 🔄 PASO 6: Migrar a producción (CUANDO TODO FUNCIONE)

1. Compra un número de teléfono en Twilio (~$1 USD/mes)
2. Obtiene aprobación de WhatsApp Business
3. Cambia en config:
   ```php
   define('TWILIO_ENVIRONMENT', 'production');
   define('TWILIO_WHATSAPP_FROM', 'whatsapp:+57TUNUM');
   ```

## 💰 Costos

- **Sandbox (Pruebas):** GRATIS - Ilimitado
- **Producción:** $0.005 USD por mensaje
- **Estimado mensual:** $1-3 USD (20-40 órdenes)

## 🐛 Troubleshooting

### Error: "Credenciales no configuradas"
- Verifica que editaste `config/twilio_config.php`
- Asegúrate de reemplazar los valores de ejemplo

### No recibo mensajes
- Verifica que conectaste tu WhatsApp al sandbox
- Envía nuevamente "join [código]" a +1 (415) 523-8886

### Error 63009: From number not verified
- Estás en sandbox, solo puedes enviar a números que se unieron
- Conecta más números enviando "join [código]"

## 📞 Soporte

Si tienes problemas, revisa los logs en:
- `logs/whatsapp_notifications.log`

O contacta a Twilio Support:
- https://support.twilio.com/

