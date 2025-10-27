# 📱 GUÍA DE NOTIFICACIONES WHATSAPP - PANEL ADMIN

## 🎯 Archivos Creados

### 1. **aprobar_pago.php**
- **Función:** Aprobar el pago de una orden y notificar al cliente
- **Ubicación:** `admin/php/aprobar_pago.php`
- **Método:** POST
- **Parámetros:**
  ```javascript
  {
      id_orden: 123
  }
  ```
- **Respuesta:**
  ```json
  {
      "success": true,
      "message": "Pago aprobado correctamente",
      "whatsapp_enviado": true,
      "whatsapp_error": null
  }
  ```

### 2. **marcar_enviado.php**
- **Función:** Marcar orden como enviada y notificar al cliente con guía
- **Ubicación:** `admin/php/marcar_enviado.php`
- **Método:** POST
- **Parámetros:**
  ```javascript
  {
      id_orden: 123,
      transportadora: "SERVIENTREGA", // opcional
      guia: "ABC123XYZ456"
  }
  ```
- **Respuesta:**
  ```json
  {
      "success": true,
      "message": "Orden marcada como enviada",
      "transportadora": "SERVIENTREGA",
      "guia": "ABC123XYZ456",
      "fecha_estimada": "2025-10-26",
      "whatsapp_enviado": true,
      "whatsapp_error": null
  }
  ```

### 3. **marcar_entregado.php**
- **Función:** Marcar orden como entregada y solicitar feedback
- **Ubicación:** `admin/php/marcar_entregado.php`
- **Método:** POST
- **Parámetros:**
  ```javascript
  {
      id_orden: 123
  }
  ```
- **Respuesta:**
  ```json
  {
      "success": true,
      "message": "Orden marcada como entregada",
      "fecha_entrega": "2025-10-23 14:30:00",
      "whatsapp_enviado": true,
      "whatsapp_error": null
  }
  ```

### 4. **generar_reporte_mensual.php**
- **Función:** Generar y enviar reporte mensual al admin
- **Ubicación:** `admin/php/generar_reporte_mensual.php`
- **Método:** GET o CLI
- **Ejecución manual:** `http://localhost/finoso/admin/php/generar_reporte_mensual.php`
- **Ejecución CRON:** `php admin/php/generar_reporte_mensual.php`

---

## 🔧 INTEGRACIÓN EN EL PANEL DE ADMIN

### Opción 1: Botones en el panel (Recomendado)

```html
<!-- En admin/panel.html o donde muestras las órdenes -->
<div class="orden-item" data-orden-id="123">
    <h3>Orden #123 - Juan Pérez</h3>
    <p>Estado: <span class="estado-orden">pendiente</span></p>
    
    <!-- Botón para aprobar pago -->
    <button onclick="aprobarPago(123)" class="btn-aprobar">
        ✅ Aprobar Pago
    </button>
    
    <!-- Botón para marcar como enviado -->
    <button onclick="marcarEnviado(123)" class="btn-enviar">
        🚚 Marcar como Enviado
    </button>
    
    <!-- Botón para marcar como entregado -->
    <button onclick="marcarEntregado(123)" class="btn-entregado">
        🎁 Marcar como Entregado
    </button>
</div>
```

### JavaScript del Panel

```javascript
// admin/js/panel.js

/**
 * Aprobar pago de una orden
 */
async function aprobarPago(idOrden) {
    if (!confirm('¿Aprobar el pago de esta orden? Se enviará WhatsApp al cliente.')) {
        return;
    }
    
    try {
        const response = await fetch('php/aprobar_pago.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_orden=${idOrden}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Pago aprobado correctamente');
            if (result.whatsapp_enviado) {
                alert('📱 WhatsApp enviado al cliente');
            }
            // Recargar órdenes o actualizar UI
            location.reload();
        } else {
            alert('❌ Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error al aprobar pago');
    }
}

/**
 * Marcar orden como enviada
 */
async function marcarEnviado(idOrden) {
    const transportadora = prompt('Transportadora (ej. SERVIENTREGA):', 'SERVIENTREGA');
    if (!transportadora) return;
    
    const guia = prompt('Número de guía de seguimiento:', '');
    if (!guia) {
        alert('❌ El número de guía es obligatorio');
        return;
    }
    
    try {
        const response = await fetch('php/marcar_enviado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_orden=${idOrden}&transportadora=${transportadora}&guia=${guia}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(`✅ Orden marcada como enviada\n🚚 Guía: ${result.guia}`);
            if (result.whatsapp_enviado) {
                alert('📱 WhatsApp con guía enviado al cliente');
            }
            location.reload();
        } else {
            alert('❌ Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error al marcar como enviado');
    }
}

/**
 * Marcar orden como entregada
 */
async function marcarEntregado(idOrden) {
    if (!confirm('¿Confirmar que el producto fue entregado? Se enviará WhatsApp al cliente.')) {
        return;
    }
    
    try {
        const response = await fetch('php/marcar_entregado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_orden=${idOrden}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Orden marcada como entregada');
            if (result.whatsapp_enviado) {
                alert('📱 WhatsApp de confirmación enviado al cliente');
            }
            location.reload();
        } else {
            alert('❌ Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error al marcar como entregado');
    }
}

/**
 * Generar reporte mensual
 */
async function generarReporteMensual() {
    if (!confirm('¿Generar reporte mensual? Se enviará por WhatsApp al admin.')) {
        return;
    }
    
    try {
        const response = await fetch('php/generar_reporte_mensual.php');
        const result = await response.json();
        
        if (result.success) {
            alert(`✅ Reporte generado para ${result.mes} ${result.anio}`);
            if (result.whatsapp_enviado) {
                alert('📱 WhatsApp con reporte enviado');
            }
        } else {
            alert('❌ Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error al generar reporte');
    }
}
```

---

## 🔔 FLUJO COMPLETO DE NOTIFICACIONES

### Cliente hace compra:
1. ✅ **Sube comprobante** → `subir_comprobante.php`
   - Cliente recibe: "Compra Exitosa"
   - Admin recibe: "Nueva Orden"

2. ✅ **Admin aprueba pago** → `aprobar_pago.php`
   - Cliente recibe: "Pago Aprobado"

3. 🚚 **Admin marca como enviado** → `marcar_enviado.php`
   - Cliente recibe: "Producto Enviado" (con guía)

4. 🎁 **Admin marca como entregado** → `marcar_entregado.php`
   - Cliente recibe: "Producto Entregado" (solicitud de feedback)

---

## ⚙️ CONFIGURACIÓN DE CRON PARA REPORTE MENSUAL

### Linux/Mac (crontab)
```bash
# Ejecutar el día 1 de cada mes a las 8:00 AM
0 8 1 * * php /ruta/completa/finoso/admin/php/generar_reporte_mensual.php
```

### Windows (Task Scheduler)
1. Abrir "Programador de tareas"
2. Crear tarea básica
3. Nombre: "Reporte Mensual FINOSO"
4. Desencadenador: Mensual, día 1, 8:00 AM
5. Acción: Ejecutar programa
6. Programa: `C:\xampp\php\php.exe`
7. Argumentos: `C:\xampp\htdocs\finoso\admin\php\generar_reporte_mensual.php`

---

## 📊 MENSAJES QUE RECIBEN LOS CLIENTES

### 1. Compra Exitosa
```
¡Gracias por tu compra en FINOSO! 🎉

Hola Juan Pérez!

📦 Orden #123
⌚ Reloj: Patek Philippe Bicolor Dorado - Negro
💰 Total: $125.000

✅ Tu comprobante fue recibido correctamente
Lo verificaremos en las próximas 24-48 horas

📱 Te notificaremos cuando se apruebe tu pago

¿Dudas? Responde este mensaje
```

### 2. Pago Aprobado
```
✅ ¡Tu pago fue APROBADO! 🎊

Hola Juan Pérez!

📦 Orden #123
⌚ Patek Philippe Bicolor Dorado - Negro

🚚 Próximo paso: ENVÍO
📅 Tiempo estimado: 2-4 días hábiles

Te enviaremos la guía de seguimiento muy pronto

¡Gracias por confiar en FINOSO! ⌚✨
```

### 3. Producto Enviado
```
📦 ¡Tu reloj va en camino! 🚚

Hola Juan Pérez!

Orden #123
⌚ Patek Philippe Bicolor Dorado - Negro

📍 Transportadora: SERVIENTREGA
🔢 Guía: ABC123XYZ456
📅 Llegada estimada: 26 Oct 2025

Rastrea tu pedido aquí:
https://www.servientrega.com/rastreo/

¡Ya casi es tuyo! 🎁
```

### 4. Producto Entregado
```
🎉 ¡Entrega completada! ⌚

Hola Juan Pérez!

Tu Patek Philippe Bicolor Dorado - Negro fue entregado exitosamente
Orden #123

¿Cómo estuvo tu experiencia? 😊
Tu opinión nos ayuda a mejorar

📸 Comparte una foto con tu reloj
🌟 Etiquétanos en Instagram: @finoso.club

🔒 Garantía: 30 días
📱 Soporte: Responde este mensaje

¡Gracias por elegir FINOSO! 💛
```

---

## 📋 CHECKLIST DE IMPLEMENTACIÓN

- [x] Archivo `aprobar_pago.php` creado
- [x] Archivo `marcar_enviado.php` creado
- [x] Archivo `marcar_entregado.php` creado
- [x] Archivo `generar_reporte_mensual.php` creado
- [x] Templates de mensajes actualizados
- [ ] Botones agregados en panel.html
- [ ] Funciones JavaScript agregadas en panel.js
- [ ] CRON configurado para reporte mensual (opcional)
- [ ] Probar cada notificación en Sandbox

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### WhatsApp no llega
1. Verificar que el Sandbox esté activo (24h desde el último "join")
2. Verificar credenciales en `config/twilio_config.php`
3. Revisar logs en `logs/whatsapp_notifications.log`
4. Verificar que el número tenga formato correcto (573001234567)

### Error al aprobar/enviar/entregar
1. Verificar que la orden exista en la base de datos
2. Verificar que el usuario tenga un número de celular válido
3. Revisar logs de PHP (`error_log`)
4. Verificar conexión a base de datos

---

## 📞 SOPORTE

- Documentación Twilio: https://www.twilio.com/docs/whatsapp
- Logs del sistema: `logs/whatsapp_notifications.log`
- Archivo de prueba: `test_whatsapp.php`

