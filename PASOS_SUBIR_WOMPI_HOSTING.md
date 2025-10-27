# 🚀 PASOS PARA SUBIR WOMPI AL HOSTING

**Fecha:** 27 de Octubre, 2025  
**Urgencia:** ALTA - El 404 impide que funcione Wompi

---

## ⚡ **RESUMEN DEL PROBLEMA:**

1. ❌ **Pago de Nequi aprobado** ($5.000) pero da 404
2. ❌ **No se creó la orden** en la base de datos
3. ❌ **Archivos desactualizados** en el hosting

**Causa:** `wompi_response.php` no existe o está desactualizado en el hosting.

---

## 📦 **ARCHIVOS CRÍTICOS A SUBIR (12 archivos):**

### **1. Webhook (EL MÁS IMPORTANTE):**
```
📁 informacion/php/wompi_webhook.php
```
**¿Por qué?** Este archivo recibe la notificación de Wompi y crea la orden automáticamente.

---

### **2. Páginas de Respuesta (Solucionan el 404):**
```
📁 informacion/php/wompi_response.php
📁 informacion-carrito/php/wompi_response_carrito.php
📁 informacion-favoritos/php/wompi_response_carrito.php
```
**¿Por qué?** Estas son las páginas a las que redirige Wompi después del pago. Ahora SOLO muestran confirmación (no duplican la orden).

---

### **3. Creación de Transacción (Fix del envío):**
```
📁 informacion/php/crear_transaccion_wompi.php
📁 informacion-carrito/php/crear_transaccion_wompi_carrito.php
📁 informacion-favoritos/php/crear_transaccion_wompi_carrito.php
```
**¿Por qué?** Corrigen el bug del costo de envío ($20 en lugar de $20.000).

---

### **4. JavaScript (Fix del envío):**
```
📁 informacion/js/validaciones-compra.js
📁 informacion-carrito/js/validaciones-compra.js
📁 informacion-favoritos/js/validaciones-compra.js
```
**¿Por qué?** Usan `data-precio` para enviar el costo correcto.

---

### **5. Emails:**
```
📁 informacion/php/enviar_correo_confirmacion.php
📁 informacion-carrito/php/enviar_correo_confirmacion.php
📁 informacion-favoritos/php/enviar_correo_confirmacion.php
```
**¿Por qué?** Credenciales SMTP corregidas.

---

## 🔧 **VERIFICAR ANTES DE SUBIR:**

### **1. Webhook en Dashboard de Wompi:**
```
URL: https://finoso.store/informacion/php/wompi_webhook.php
Eventos: ✅ transaction.approved
         ✅ transaction.updated
```

**¿Cómo verificar?**
1. Entra a: https://dashboard.wompi.com/
2. Ve a: Configuración → Webhooks
3. Verifica que la URL sea exactamente: `https://finoso.store/informacion/php/wompi_webhook.php`

---

## 📋 **PASOS PARA SUBIR:**

### **Paso 1: Backup (IMPORTANTE)**
```
1. Descarga estos archivos del hosting actual:
   - informacion/php/wompi_webhook.php (backup)
   - informacion/php/wompi_response.php (backup)
   
2. Guárdalos en una carpeta llamada "backup_wompi_27oct2025"
```

---

### **Paso 2: Subir Archivos por FTP/cPanel**
```
1. Abre tu cliente FTP (FileZilla, WinSCP, o cPanel File Manager)

2. Navega a: public_html/finoso/

3. Sube PRIMERO los 3 archivos más críticos:
   ✅ informacion/php/wompi_webhook.php
   ✅ informacion/php/wompi_response.php
   ✅ informacion/php/crear_transaccion_wompi.php

4. Luego sube los otros 9 archivos de la lista
```

---

### **Paso 3: Verificar Permisos**
```
Todos los archivos PHP deben tener permisos: 644
Todas las carpetas deben tener permisos: 755
```

---

### **Paso 4: Verificar Logs (IMPORTANTE)**
```
1. Verifica que la carpeta logs/ sea escribible (permisos 777)

2. Ruta: public_html/finoso/logs/

3. Si no existe, créala y dale permisos 777
```

---

## ✅ **PRUEBA DESPUÉS DE SUBIR:**

### **1. Compra de Prueba con Nequi ($5.000):**
```
1. Ve a: https://finoso.store/
2. Selecciona un reloj
3. Compra con Wompi → Nequi
4. Paga $5.000
5. Verifica:
   ✅ No da 404
   ✅ Muestra página de éxito elegante
   ✅ Se crea la orden en BD
   ✅ Llega email con código de descuento
   ✅ Llega WhatsApp (si está configurado)
   ✅ Aparece en admin como "pagado"
```

---

### **2. Verificar Logs:**
```
1. Descarga el archivo: logs/php_error_log

2. Busca estas líneas (deben aparecer):
   [WOMPI-WEBHOOK] 🎉 Transacción aprobada
   [WOMPI-WEBHOOK] 📦 Procesando orden
   [WOMPI-WEBHOOK] ✅ Código generado
   [WOMPI-WEBHOOK] ✅ Email enviado
   [WOMPI-WEBHOOK] ✅ WhatsApp enviado
   [WOMPI-WEBHOOK] 🎉 Orden procesada completamente
   
   [WOMPI-RESPONSE] 🔄 Usuario redirigido desde Wompi
   [WOMPI-RESPONSE] ✅ Orden encontrada: #X
```

---

## 🚨 **SI ALGO FALLA:**

### **Problema 1: Sigue dando 404**
```
Solución:
1. Verifica la ruta del archivo en FTP
2. Debe estar en: public_html/finoso/informacion/php/wompi_response.php
3. NO en: public_html/informacion/php/wompi_response.php
```

---

### **Problema 2: No se crea la orden**
```
Solución:
1. Verifica que el webhook esté configurado en Wompi Dashboard
2. Descarga logs/php_error_log
3. Busca errores de [WOMPI-WEBHOOK]
4. Verifica permisos de logs/ (777)
```

---

### **Problema 3: No llega email**
```
Solución:
1. Verifica logs/php_error_log
2. Busca: [CORREO-CONFIRMACION]
3. Si dice "SMTP Error", verifica las credenciales en enviar_correo_confirmacion.php
```

---

## 📊 **CHECKLIST FINAL:**

Antes de dar por terminado, verifica:

- [ ] Los 12 archivos críticos están subidos
- [ ] Webhook configurado en Wompi Dashboard
- [ ] Permisos correctos (644 para PHP, 755 para carpetas)
- [ ] Carpeta logs/ existe y tiene permisos 777
- [ ] Compra de prueba exitosa con Nequi
- [ ] No da 404
- [ ] Orden creada en BD
- [ ] Email recibido con código
- [ ] WhatsApp recibido (si aplica)
- [ ] Aparece en admin como "pagado"
- [ ] Logs muestran proceso completo

---

## 🎯 **TIEMPO ESTIMADO:**

- Subir archivos: **5 minutos**
- Verificar configuración: **3 minutos**
- Prueba completa: **5 minutos**

**Total: ~15 minutos**

---

## 💡 **NOTA IMPORTANTE:**

El flujo ahora funciona así:

1. **Usuario paga en Wompi** → Wompi aprueba
2. **Wompi llama al webhook** → `wompi_webhook.php` crea la orden completa
3. **Usuario es redirigido** → `wompi_response.php` SOLO muestra confirmación

**ANTES** (mal): `wompi_response.php` creaba la orden → Duplicación  
**AHORA** (bien): Solo el webhook crea la orden → Sin duplicación

---

**¡Listo para subir!** 🚀

---

**Última actualización:** 27/10/2025 - 11:30 PM

