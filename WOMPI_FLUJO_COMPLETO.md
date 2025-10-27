# 💳 Flujo Completo de Pago con Wompi - FINOSO

**Fecha:** 27 de Octubre, 2025  
**Estado:** ✅ Implementado y Listo para Probar

---

## 🎯 **Diferencia entre Wompi y Nequi**

| Característica | **Nequi** | **Wompi** |
|----------------|-----------|-----------|
| **Tipo** | Manual | Automático |
| **Método** | Usuario paga → Sube comprobante | Usuario paga → Webhook automático |
| **Estado Inicial** | `pendiente_verificacion` | `pagado` (directo) |
| **Verificación** | Manual por admin | Automática por Wompi |
| **Tiempo** | 3-24 horas | Instantáneo |
| **Código Descuento** | Generado cuando admin aprueba | Generado automáticamente |
| **Email** | Al aprobar admin | Inmediato después del pago |
| **WhatsApp** | Al aprobar admin | Inmediato después del pago |

---

## 🔄 **Flujo Completo de Wompi**

### **1. Usuario Selecciona Producto**
```
Catálogo → Click en producto → "Comprar Ahora"
```

### **2. Usuario Completa Formulario**
- Nombre, email, cédula, celular
- Departamento y ciudad (costo de envío)
- Dirección de envío
- **Selecciona "Wompi" como método de pago**

### **3. Sistema Crea Pre-Orden**
```php
// orden se crea con:
- estado: 'pendiente' (temporal)
- metodo_pago: 'wompi'
- token_verificacion: (único, usado como reference en Wompi)
```

### **4. Redirección a Wompi**
```
Usuario es redirigido a: https://checkout.wompi.co/p/
Con los datos:
- public-key
- reference (token de la orden)
- amount-in-cents
- customer-data (email, nombre)
```

### **5. Usuario Paga en Wompi**
Los métodos disponibles se configuran desde el **Dashboard de Wompi** (no desde el código).

**Métodos recomendados a habilitar:**
- ✅ **Tarjeta de crédito/débito** (Visa, Mastercard, Amex) - Instantáneo
- ✅ **Nequi** (Transferencia desde la app) - Popular en Colombia
- 🤔 **PSE** (Transferencia bancaria) - Opcional, común en Colombia

**Métodos recomendados a deshabilitar:**
- ❌ Efectivo en Corresponsales - Muy lento (1-3 días)
- ❌ Crédito Addi - Genera intereses al cliente
- ❌ Baloto - Requiere punto físico

📄 **Ver:** `CONFIGURAR_METODOS_WOMPI_DASHBOARD.md` para instrucciones completas

### **6. Wompi Notifica Webhook** ⚡
```
POST https://tu-servidor.com/finoso/informacion/php/wompi_webhook.php

Event: transaction.approved
Data: {
  reference: "token_de_la_orden",
  status: "APPROVED",
  amount_in_cents: 15000000,
  ...
}
```

### **7. Webhook Procesa Automáticamente** 🤖

#### **A. Actualiza Estado de Orden**
```sql
UPDATE orden 
SET estado = 'pagado' 
WHERE token_verificacion = reference
```

#### **B. Marca Relojes como Vendidos**
```sql
UPDATE reloj r 
INNER JOIN orden_detalle od ON r.id_reloj = od.id_reloj 
SET r.vendido = 1 
WHERE od.id_orden = ?
```

#### **C. Genera Código de Descuento** (solo si `id_usuario` no es NULL)
```php
// Crea código: FIN + 6 caracteres aleatorios
$codigo = 'FIN' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
// Ejemplo: FINA3B7F2

// Inserta en codigo_descuento
INSERT INTO codigo_descuento (codigo, porcentaje, fecha_expiracion, activo)
VALUES ('FINA3B7F2', 10.00, '2025-11-27', 1)

// Asigna al usuario
INSERT INTO usuario_codigo_descuento 
(id_usuario, id_codigo, fecha_asignado, id_orden, veces_usado, activo, notas)
VALUES (5, 21, NOW(), 45, 0, 1, 'Código de agradecimiento por tu compra #45 🎉')
```

#### **D. Envía Email de Confirmación** 📧

**Si hay sesión (`id_usuario` no NULL):**
```
Asunto: ¡Pago Aprobado! Tu Código de Descuento - FINOSO

Contenido:
- ✅ Pago procesado exitosamente
- 📦 Detalles de la orden
- 🎁 Código de descuento destacado
- 📋 Próximos pasos
- 🔗 Botón "Ver Mi Perfil"
```

**Si NO hay sesión (anónimo):**
```
Asunto: ¡Pago Aprobado! - FINOSO

Contenido:
- ✅ Pago procesado exitosamente
- 📦 Detalles de la orden
- 💡 CTA para registrarse
- 📋 Próximos pasos
```

#### **E. Envía WhatsApp** 📱 (si Twilio está configurado)

**Al Cliente:**
```
🎉 ¡Hola {nombre}!

Tu pago ha sido procesado exitosamente.

📦 Orden: #{id}
🕐 Reloj: {nombre_reloj}
💰 Total: ${total}

{Si hay código}
🎁 Tu código de descuento: {codigo}
Úsalo en tu próxima compra

Gracias por confiar en FINOSO 🌟
```

**Al Admin:**
```
📦 NUEVA ORDEN - WOMPI

Orden: #{id}
Cliente: {nombre}
📞 {telefono}
📧 {email}
🕐 Producto: {nombre_reloj}
💰 Total: ${total}

✅ PAGO APROBADO POR WOMPI
Estado: PAGADO
```

### **8. Usuario Ve Confirmación** ✅
```
Redirección a: /informacion/php/wompi_response.php

Mensaje:
"¡Pago Exitoso! 🎉
Tu orden ha sido procesada.
Revisa tu email para más detalles."
```

---

## 🧪 **LISTO PARA PROBAR**

### ✅ **Lo que está Implementado:**

1. ✅ Webhook recibe notificación de Wompi
2. ✅ Orden marcada como `pagado` automáticamente
3. ✅ Relojes marcados como `vendidos`
4. ✅ **Código de descuento generado** (si hay sesión)
5. ✅ **Email enviado con código** (o CTA registro)
6. ✅ **WhatsApp enviado** al cliente y admin
7. ✅ Logs detallados en `php_error_log`

---

## 📋 **Cómo Probar:**

### **Paso 1: Reducir Precio del Reloj** 💰
```sql
-- Reducir a precio mínimo para prueba
UPDATE reloj 
SET precio = 5000 
WHERE id_reloj = 1;
```
*Wompi permite pagos desde $5.000 COP*

### **Paso 2: Hacer Compra CON Sesión** 👤
1. Iniciar sesión en FINOSO
2. Ir al producto
3. Click "Comprar Ahora"
4. Completar formulario
5. Seleccionar "Wompi"
6. Pagar con tarjeta de prueba (ver abajo)
7. ✅ Verificar que llegue **EMAIL CON CÓDIGO**
8. ✅ Verificar que llegue **WhatsApp**
9. ✅ Ir a perfil y ver **código en "Mis Códigos"**
10. ✅ En admin, orden debe estar **PAGADA**

### **Paso 3: Hacer Compra SIN Sesión** 🔓
1. Cerrar sesión (o navegador incógnito)
2. Comprar producto
3. Pagar con Wompi
4. ✅ Verificar que llegue **EMAIL SIN CÓDIGO** pero con **CTA "Regístrate"**
5. ✅ Verificar WhatsApp
6. ✅ En admin, orden **PAGADA** con `id_usuario = NULL`

---

## 💳 **Tarjetas de Prueba de Wompi**

### ✅ **Transacción Aprobada:**
```
Número: 4242 4242 4242 4242
CVV: 123
Fecha: Cualquier fecha futura
Nombre: Cualquier nombre
```

### ❌ **Transacción Rechazada:**
```
Número: 4111 1111 1111 1111
CVV: 123
Fecha: Cualquier fecha futura
```

### ⏳ **Transacción Pendiente:**
```
Número: 5555 5555 5555 4444
CVV: 123
Fecha: Cualquier fecha futura
```

---

## 📊 **Verificación en Logs**

Después de cada pago, revisa `logs/php_error_log`:

```
[WOMPI-WEBHOOK] 🎉 Transacción aprobada: {...}
[WOMPI-WEBHOOK] 📦 Procesando orden #45 - Usuario: 5
[WOMPI-WEBHOOK] ✅ Código generado: FINA3B7F2 para usuario #5
[WOMPI-WEBHOOK] ✅ Email enviado a: usuario@example.com
[WOMPI-WEBHOOK] ✅ WhatsApp enviado para orden #45
[WOMPI-WEBHOOK] 🎉 Orden #45 procesada completamente
```

Si es orden anónima:
```
[WOMPI-WEBHOOK] 📦 Procesando orden #46 - Usuario: NULL (anónimo)
[WOMPI-WEBHOOK] ℹ️ No se genera código (orden anónima)
[WOMPI-WEBHOOK] ✅ Email enviado a: anonimo@example.com
[WOMPI-WEBHOOK] ✅ WhatsApp enviado para orden #46
[WOMPI-WEBHOOK] 🎉 Orden #46 procesada completamente
```

---

## ⚠️ **Puntos Importantes**

### **1. Webhook URL**
Tu webhook debe ser accesible públicamente. En desarrollo:
- Usa **ngrok** o **localtunnel** para exponer `localhost`
- Configura la URL en el dashboard de Wompi

Ejemplo con ngrok:
```bash
ngrok http 80
# Te da: https://abc123.ngrok.io
# Webhook URL: https://abc123.ngrok.io/finoso/informacion/php/wompi_webhook.php
```

### **2. Firma del Webhook (Seguridad)**
El webhook verifica la firma con `WOMPI_EVENTS_SECRET`:
```php
$signature = $_SERVER['HTTP_SIGNATURE'] ?? '';
$expected_signature = hash_hmac('sha256', $input, WOMPI_EVENTS_SECRET);

if (!hash_equals($signature, $expected_signature)) {
    http_response_code(401);
    exit('Firma inválida');
}
```

### **3. Diferencia con Admin**
Con **Nequi:**
- Admin recibe orden en `pendiente_verificacion`
- Admin verifica comprobante manualmente
- Admin aprueba → genera código y envía email

Con **Wompi:**
- ✅ Todo es automático
- ✅ Orden llega directamente como `pagado`
- ✅ Código generado inmediatamente
- ✅ Admin solo debe **marcar como enviado** cuando despache

---

## 🎯 **Checklist de Prueba**

### **Compra CON Sesión:**
- [ ] Orden creada en BD con `estado = 'pagado'`
- [ ] Reloj marcado como `vendido = 1`
- [ ] Código generado en `codigo_descuento`
- [ ] Código asignado en `usuario_codigo_descuento`
- [ ] Email recibido con código destacado
- [ ] WhatsApp recibido (si Twilio activo)
- [ ] Código visible en perfil del usuario
- [ ] Logs completos en `php_error_log`

### **Compra SIN Sesión:**
- [ ] Orden creada con `id_usuario = NULL`
- [ ] Reloj marcado como vendido
- [ ] **NO** se genera código
- [ ] Email recibido con CTA "Regístrate"
- [ ] WhatsApp recibido (si Twilio activo)
- [ ] Logs indican "orden anónima"

---

## 🚀 **¿LISTO PARA PROBAR?**

✅ **Webhook actualizado con:**
- Generación automática de códigos
- Emails elegantes (con/sin código)
- WhatsApp al cliente y admin
- Logs detallados

✅ **Flujo completo implementado:**
- Pago → Wompi aprueba → Webhook procesa → Email + WhatsApp

✅ **Documentación completa:**
- Este archivo explica todo el flujo
- Tarjetas de prueba incluidas
- Checklist de verificación

---

**¡Reduce el precio de los relojes a $5.000 y empieza a probar con dinero real!** 🎉

**Nota:** Asegúrate de configurar un túnel (ngrok) para que Wompi pueda notificar tu webhook en localhost.

---

**Última actualización:** 27/10/2025  
**Estado:** ✅ Listo para Pruebas con Pagos Reales

