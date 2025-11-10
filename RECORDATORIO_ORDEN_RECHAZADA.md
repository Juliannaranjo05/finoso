# 📱 RECORDATORIO WHATSAPP - ORDEN RECHAZADA

## ✅ IMPLEMENTACIÓN COMPLETA

Se ha implementado la **notificación automática** para clientes con órdenes rechazadas.

---

## 🎯 ¿CÓMO FUNCIONA?

### **Escenario:**
1. Cliente sube comprobante → Orden creada
2. Admin rechaza la orden (por comprobante inválido, monto incorrecto, etc.)
3. **Después de 24 horas**, el sistema envía WhatsApp automático

### **Mensaje que recibe el cliente:**
```
❌ Orden Rechazada - FINOSO

Hola Julian,

Tu orden #3 fue rechazada:
⌚ Technomarine Circones Negro
💰 $109.000

📋 Motivo:
El monto transferido no coincide con el total de la orden

🔄 ¿Quieres reintentar?
Puedes hacer una nueva compra o contactarnos para ayudarte.

💬 ¿Necesitas ayuda?
Responde este mensaje y te asistimos 😊

🛒 Volver a comprar:
https://finoso.com/catalogo/catalogo.html
```

---

## 📋 ARCHIVOS CREADOS

### **1. Template WhatsApp**
- **Archivo:** `includes/WhatsAppTemplates.php`
- **Método:** `WhatsAppTemplates::ordenRechazada()`
- ✅ Ya implementado

### **2. Script de Recordatorio**
- **Archivo:** `admin/php/recordatorio_orden_rechazada.php`
- **Función:** Busca órdenes rechazadas y envía WhatsApp
- ✅ Ya implementado

### **3. Actualización de BD**
- **Archivo:** `database/add_recordatorio_enviado.sql`
- **Función:** Agrega columna para trackear recordatorios enviados
- ⚠️ **PENDIENTE DE EJECUTAR**

### **4. Página de Prueba**
- **Archivo:** `admin/test_recordatorio_rechazada.html`
- **Función:** Probar envío manual antes de automatizar
- ✅ Ya implementado

---

## 🚀 PASOS PARA ACTIVAR

### **PASO 1: Actualizar la Base de Datos**

Ejecuta este script **UNA SOLA VEZ**:

```
https://finoso.store/database/ejecutar_add_recordatorio.php
```

Esto agregará la columna `recordatorio_enviado` a la tabla `orden`.

---

### **PASO 2: Probar Manualmente**

Antes de automatizar, prueba que funcione:

```
https://finoso.store/admin/test_recordatorio_rechazada.html
```

**Para probar necesitas:**
1. Tener al menos 1 orden con estado `rechazado`
2. Que la orden tenga más de 24 horas (puedes modificar el `fecha` en BD temporalmente)
3. Tu WhatsApp conectado al Sandbox de Twilio

**Cómo crear una orden rechazada de prueba:**
```sql
-- En phpMyAdmin, ejecuta:
UPDATE orden 
SET estado = 'rechazado', 
    motivo_rechazo = 'Monto incorrecto en el comprobante',
    fecha = DATE_SUB(NOW(), INTERVAL 25 HOUR)
WHERE id_orden = 3;
```

Luego ve a la página de prueba y clic en **"Enviar Recordatorios Ahora"**.

---

### **PASO 3: Automatizar con CRON** (Producción)

Una vez que funcione manualmente, configura un CRON job:

#### **En Linux/cPanel:**
```bash
# Ejecutar cada 12 horas
0 */12 * * * /usr/bin/php /path/to/finoso/admin/php/recordatorio_orden_rechazada.php
```

#### **En Windows (Task Scheduler):**
```
Programa: C:\xampp\php\php.exe
Argumentos: C:\xampp\htdocs\finoso\admin\php\recordatorio_orden_rechazada.php
Repetir: Cada 12 horas
```

---

## 📊 CARACTERÍSTICAS

### **Inteligencia del Sistema:**
- ✅ Solo envía 1 recordatorio por orden (no spam)
- ✅ Solo órdenes rechazadas hace > 24 horas
- ✅ Incluye el motivo del rechazo
- ✅ Link directo al catálogo
- ✅ Invita a responder para ayuda

### **Prevención de Spam:**
- Campo `recordatorio_enviado` en BD
- Una vez enviado = no vuelve a enviar
- Límite de 10 órdenes por ejecución

---

## 🧪 PRUEBA RÁPIDA

### **Simular orden rechazada:**
```sql
-- Ejecutar en phpMyAdmin:
UPDATE orden 
SET estado = 'rechazado', 
    motivo_rechazo = 'El monto transferido no coincide',
    fecha = DATE_SUB(NOW(), INTERVAL 25 HOUR),
    recordatorio_enviado = 0
WHERE id_orden = 3;
```

### **Enviar recordatorio:**
```
https://finoso.store/admin/test_recordatorio_rechazada.html
→ Clic en "Enviar Recordatorios Ahora"
```

### **Verificar en WhatsApp:**
Deberías recibir el mensaje de orden rechazada.

---

## 📈 MÉTRICAS ESPERADAS

**Tasa de recuperación típica:**
- 15-25% de clientes reintentan la compra
- 30-40% responden pidiendo ayuda
- 50% ignoran el mensaje

**Mejor práctica:**
- Responder rápido a los que contestan
- Ofrecer ayuda personalizada
- Facilitar el proceso de reintento

---

## 🔧 MANTENIMIENTO

### **Ver órdenes pendientes de recordatorio:**
```sql
SELECT 
    id_orden, 
    nombre, 
    estado, 
    fecha, 
    motivo_rechazo,
    recordatorio_enviado
FROM orden 
WHERE estado = 'rechazado' 
AND fecha < DATE_SUB(NOW(), INTERVAL 24 HOUR)
AND recordatorio_enviado = 0;
```

### **Resetear recordatorios (si necesitas reenviar):**
```sql
UPDATE orden 
SET recordatorio_enviado = 0 
WHERE estado = 'rechazado';
```

---

## ✅ CHECKLIST DE ACTIVACIÓN

- [ ] Ejecutar `ejecutar_add_recordatorio.php`
- [ ] Crear orden rechazada de prueba
- [ ] Probar en `test_recordatorio_rechazada.html`
- [ ] Verificar recepción en WhatsApp
- [ ] Configurar CRON job (producción)
- [ ] Monitorear logs primeros días

---

## 📞 SOPORTE

Si tienes problemas:
1. Revisa logs: `logs/whatsapp_notifications.log`
2. Verifica que Twilio esté configurado
3. Confirma que la columna `recordatorio_enviado` existe
4. Revisa que haya órdenes rechazadas > 24h

---

**¡Sistema listo para recuperar ventas perdidas!** 🚀💰

