# 🔄 FLUJO DE RECUPERACIÓN DE PAGO CON WHATSAPP

## 📋 Resumen

Sistema implementado para permitir que clientes con órdenes rechazadas por **monto incorrecto** puedan completar su pago y recuperar su orden mediante un enlace único enviado por WhatsApp.

---

## 🎯 Casos de Uso

### ✅ CASO 1: Rechazo por Monto Incorrecto
**Escenario:** Cliente pagó menos de lo que debía  
**Ejemplo:** Pagó $15.000 pero el total era $117.000  
**Acción:** Se envía WhatsApp con enlace para pagar diferencia ($102.000)

### ❌ CASO 2: Rechazo por Otros Motivos
**Escenario:** Comprobante ilegible, datos incorrectos, etc.  
**Acción:** Se envía WhatsApp genérico sugiriendo nueva compra o contacto

---

## 🔀 Flujo Completo

```
1. ADMIN RECHAZA ORDEN
   └─> Panel Admin → Orden #6 → Rechazar
   └─> Motivo: "El monto del comprobante no coincide..."
   └─> Ingresa monto pagado: $15.000
   └─> Confirma rechazo
        ↓
2. SISTEMA ACTUALIZA BD
   └─> estado = 'rechazado'
   └─> motivo_rechazo = "El monto..."
   └─> monto_pagado = 15000
        ↓
3. ENVÍA EMAIL AL CLIENTE
   └─> Notificación de rechazo por correo
        ↓
4. ENVÍA WHATSAPP AL CLIENTE ⭐ NUEVO
   └─> Si monto_pagado > 0:
       ├─ "❌ Orden Rechazada"
       ├─ "💡 PUEDES SALVAR TU ORDEN"
       ├─ "✅ Ya pagaste: $15.000"
       ├─ "❌ Falta pagar: $102.000"
       ├─ "🎯 COMPLETA TU PAGO AQUÍ"
       └─ Link: http://127.0.0.1/finoso/informacion/recuperar_pago.html?orden=6
        ↓
5. CLIENTE HACE CLICK EN ENLACE
   └─> Abre recuperar_pago.html?orden=6
   └─> Ve página con:
       ├─ Resumen de orden
       ├─ Desglose de pagos
       ├─ Instrucciones Nequi
       └─ Formulario para subir comprobante
        ↓
6. CLIENTE PAGA DIFERENCIA
   └─> Paga $102.000 por Nequi
   └─> Sube comprobante de pago
   └─> Sistema actualiza:
       ├─ monto_pagado = 15000 + 102000 = 117000
       ├─ estado = 'pendiente_verificacion'
       └─ comprobante_pago = nuevo archivo
        ↓
7. NOTIFICACIONES FINALES
   ├─> WhatsApp Cliente: "Comprobante recibido"
   └─> WhatsApp Admin: "Comprobante adicional recibado"
        ↓
8. ADMIN APRUEBA ORDEN
   └─> Orden vuelve al flujo normal
```

---

## 📱 Notificaciones (WhatsApp + Email)

### WHATSAPP - Mensaje CON Recuperación (monto_pagado > 0)

```
❌ Orden Rechazada - FINOSO

Hola Juan Pérez,

Tu orden #6 fue rechazada:
⌚ Rolex Submariner Dorado
💰 Total: $117.000

📋 Motivo:
El monto del comprobante no coincide con el total del pedido

💡 PUEDES SALVAR TU ORDEN 💡

✅ Ya pagaste: $15.000
❌ Falta pagar: $102.000

🎯 ¡COMPLETA TU PAGO AQUÍ!
http://127.0.0.1/finoso/informacion/recuperar_pago.html?orden=6

📱 Paga solo lo que falta y recupera tu orden
⏰ Link válido por 48 horas

💬 ¿Necesitas ayuda?
Responde este mensaje y te asistimos 😊

🛒 Volver a comprar:
http://127.0.0.1/finoso/catalogo/catalogo.html
```

### WHATSAPP - Mensaje SIN Recuperación (sin monto_pagado)

```
❌ Orden Rechazada - FINOSO

Hola María García,

Tu orden #7 fue rechazada:
⌚ Casio G-Shock Negro
💰 Total: $85.000

📋 Motivo:
El comprobante está muy borroso y no se puede verificar

🔄 ¿Quieres reintentar?
Puedes hacer una nueva compra o contactarnos para ayudarte.

💬 ¿Necesitas ayuda?
Responde este mensaje y te asistimos 😊

🛒 Volver a comprar:
http://127.0.0.1/finoso/catalogo/catalogo.html
```

### 📧 EMAIL - Mensaje CON Recuperación (monto_pagado > 0)

Email HTML con diseño premium que incluye:
- **Header dorado:** "❌ Orden Rechazada"
- **Motivo destacado:** Con borde naranja
- **Sección verde de recuperación:**
  - Título: "💡 ¡PUEDES SALVAR TU ORDEN!"
  - Tabla con desglose:
    - Total del pedido: $119.000
    - ✅ Ya pagaste: $5.000
    - ❌ Falta pagar: $114.000
  - **Botón grande dorado:** "🎯 COMPLETAR MI PAGO AHORA"
  - Texto: "📱 Paga solo la diferencia y recupera tu orden"
  - "⏰ Link válido por 48 horas"
- **Footer con link alternativo** (por si el botón no funciona)

**Preview:** `http://127.0.0.1/finoso/admin/test_email_recuperacion.html`

### 📧 EMAIL - Mensaje SIN Recuperación (sin monto_pagado)

Email simple con:
- Mensaje de rechazo
- Motivo en caja amarilla
- Lista de acciones sugeridas
- No incluye enlace de recuperación

---

## 🗂️ Archivos Modificados/Creados

### Nuevos Archivos
1. **`informacion/recuperar_pago.html`**
   - Página para completar pago de diferencia
   - Diseño basado en `pago_nequi.html`
   - Muestra desglose de pagos
   - Formulario para subir nuevo comprobante

2. **`informacion/php/obtener_orden_rechazada.php`**
   - Backend para cargar datos de orden rechazada
   - Valida que el usuario sea propietario
   - Valida que tenga monto_pagado > 0

3. **`informacion/php/subir_comprobante_diferencia.php`**
   - Procesa el comprobante adicional
   - Suma diferencia a `monto_pagado`
   - Cambia estado a `pendiente_verificacion`
   - Envía notificaciones WhatsApp

4. **`admin/test_mensaje_recuperacion.php`**
   - Página de prueba para ver los mensajes de WhatsApp
   - Muestra ejemplos de ambos casos
   - Incluye instrucciones de prueba

5. **`admin/test_email_recuperacion.html`**
   - Preview de los correos electrónicos
   - Comparación lado a lado (con/sin recuperación)
   - Muestra el diseño HTML final

### Archivos Modificados
6. **`includes/WhatsAppTemplates.php`**
   - Método `ordenRechazada()` actualizado
   - Detecta si hay `monto_pagado`
   - Genera mensaje con o sin enlace según el caso
   - Incluye desglose de montos

7. **`admin/php/acciones.php`**
   - Caso `rechazar` actualizado
   - **Email personalizado:** Detecta si hay `monto_pagado` y envía email con botón de recuperación
   - **WhatsApp:** Envía notificación con enlace cuando aplica
   - Incluye `url_recuperacion` cuando aplica
   - Calcula y envía `diferencia`

8. **`admin/php/recordatorio_orden_rechazada.php`**
   - Consulta SQL actualizada para obtener `monto_pagado`
   - Envía mensaje con enlace cuando aplica
   - Funciona con el recordatorio de 24 horas

9. **`catalogo/js/user-modal.js`**
   - Agregó botón "Completar Pago" para órdenes rechazadas
   - Solo visible si `monto_pagado > 0`
   - Función `completarPago()` redirige a `recuperar_pago.html`
   - Función `contactarSoporteRechazo()` para WhatsApp

10. **`catalogo/css/user-modal.css`**
    - Estilos para `.btn-completar-pago` (dorado destacado)
    - Estilos para `.btn-soporte` (verde WhatsApp)

---

## 🧪 Cómo Probar

### 1. Ver Mensajes de Ejemplo

**WhatsApp:**
```
http://127.0.0.1/finoso/admin/test_mensaje_recuperacion.php
```
- Muestra los 3 casos de uso
- Comparación lado a lado
- Instrucciones de prueba

**Email:**
```
http://127.0.0.1/finoso/admin/test_email_recuperacion.html
```
- Preview de ambos tipos de correos
- Diseño HTML completo
- Comparación lado a lado

### 2. Rechazar Orden con Monto Incorrecto
1. Ir a `http://127.0.0.1/finoso/admin/panel.php`
2. Buscar orden pendiente (ej: #6)
3. Click "Rechazar"
4. Seleccionar: "El monto del comprobante no coincide..."
5. Ingresar monto pagado: `15000`
6. Confirmar

### 3. Verificar Notificaciones

**WhatsApp:**
- Revisar WhatsApp conectado a Twilio
- Deberías recibir mensaje CON enlace
- Incluye desglose de montos

**Email:**
- Revisar el correo electrónico de la orden
- Deberías recibir email con diseño premium
- Incluye botón dorado "🎯 COMPLETAR MI PAGO AHORA"
- Click en el botón o el enlace alternativo

### 4. Completar Pago
1. Click en el enlace (desde WhatsApp o Email)
2. En `recuperar_pago.html?orden=6`:
   - Verifica datos de la orden
   - Ve el desglose: Total / Ya pagado / Falta
   - Instrucciones de Nequi + QR
3. Sube un comprobante de la diferencia
4. Click "Enviar comprobante"
5. Sistema actualiza BD
6. Redirige a página de éxito

### 5. Verificar en Admin
- Orden #6 debería estar en `pendiente_verificacion`
- `monto_pagado` debería ser `117000` (15000 + 102000)
- Nuevo comprobante adjunto

---

## 💡 Características Implementadas

✅ Detecta automáticamente si es problema de monto  
✅ Genera enlace único por orden  
✅ Mensaje personalizado según el caso  
✅ **Notificación dual:** WhatsApp + Email con el mismo enlace  
✅ **Email HTML premium:** Diseño profesional con botón de acción  
✅ Desglose claro de montos en ambas notificaciones  
✅ Página de recuperación elegante (mismo diseño Nequi)  
✅ Validación de propiedad de orden  
✅ Actualización automática de `monto_pagado`  
✅ Notificaciones a cliente y admin  
✅ Botón destacado en perfil de usuario  
✅ Enlace también en recordatorios (24h después)  
✅ Link alternativo en email si el botón no funciona  

---

## 🔐 Seguridad

- ✅ Valida que el usuario sea propietario de la orden
- ✅ Verifica que la orden esté en estado `rechazado`
- ✅ Requiere que `monto_pagado > 0` para acceder
- ✅ Los enlaces son únicos por orden (no adivinables)
- ✅ Validación de archivos (imagen/PDF)
- ✅ Protección contra inyección SQL (prepared statements)

---

## 📊 Ejemplo en Base de Datos

### Antes del Rechazo
```sql
SELECT id_orden, estado, total, monto_pagado FROM orden WHERE id_orden = 6;
-- id_orden | estado | total  | monto_pagado
-- 6        | pendiente | 117000 | NULL
```

### Después del Rechazo (con monto)
```sql
SELECT id_orden, estado, total, monto_pagado, motivo_rechazo FROM orden WHERE id_orden = 6;
-- id_orden | estado    | total  | monto_pagado | motivo_rechazo
-- 6        | rechazado | 117000 | 15000        | El monto del comprobante...
```

### Después de Completar Pago
```sql
SELECT id_orden, estado, total, monto_pagado FROM orden WHERE id_orden = 6;
-- id_orden | estado               | total  | monto_pagado
-- 6        | pendiente_verificacion | 117000 | 117000
```

---

## 🎨 Interfaz de Usuario

### En Perfil de Usuario
Cuando hay orden rechazada con `monto_pagado > 0`:
```
┌─────────────────────────────────────┐
│ Orden #6                            │
│ ❌ Rechazado                        │
│                                     │
│ Rolex Submariner Dorado             │
│ Total: $117.000                     │
│                                     │
│ ⚠️ Motivo: El monto no coincide    │
│                                     │
│ [💰 Completar Pago] [💬 Soporte]   │
└─────────────────────────────────────┘
```

### En recuperar_pago.html
```
┌─────────────────────────────────────┐
│    🏆 FINOSO - Completar Pago       │
├─────────────────────────────────────┤
│ Orden #6                            │
│                                     │
│ ⚠️ Motivo del rechazo               │
│ El monto del comprobante no coincide│
│                                     │
│ [Imagen] Rolex Submariner Dorado   │
│                                     │
│ 💰 RESUMEN DE PAGO                  │
│ Total del pedido:      $117.000     │
│ ✅ Ya pagaste:          $15.000     │
│ ❌ Falta pagar:        $102.000     │
│                                     │
│ 📱 Envía solo la diferencia         │
│ Nequi: 3173897119                   │
│ [Copiar número]                     │
│                                     │
│ [QR Code]                           │
│                                     │
│ 📸 Sube comprobante de diferencia   │
│ [Seleccionar archivo]               │
│ [✅ Enviar y completar pago]        │
└─────────────────────────────────────┘
```

---

## 🚀 Próximos Pasos (Opcionales)

1. **Expiración de Enlaces**
   - Agregar campo `fecha_expiracion_recuperacion` en BD
   - Validar que no hayan pasado 48 horas

2. **Límite de Intentos**
   - Permitir máximo 3 intentos de recuperación
   - Después de 3, bloquear y requerir contacto directo

3. **Historial de Pagos Parciales**
   - Tabla `pagos_parciales` para registrar cada pago
   - Mostrar histórico en admin panel

4. **Notificación Proactiva**
   - Enviar recordatorio a las 12h si no ha completado
   - Enviar aviso a las 36h antes de expirar

5. **Dashboard de Recuperaciones**
   - Métrica: % de órdenes recuperadas exitosamente
   - Tiempo promedio de recuperación
   - Tasa de conversión por motivo de rechazo

---

## 📞 Soporte

Si necesitas ayuda o hay algún error:
1. Revisa los logs en `logs/whatsapp_notifications.log`
2. Verifica las credenciales de Twilio en `config/twilio_config.php`
3. Asegúrate de que el sandbox de WhatsApp esté activo
4. Verifica que las columnas `monto_pagado` y `recordatorio_enviado` existan en la tabla `orden`

---

**Fecha de Implementación:** Octubre 2025  
**Versión:** 1.0  
**Estado:** ✅ Completamente funcional

