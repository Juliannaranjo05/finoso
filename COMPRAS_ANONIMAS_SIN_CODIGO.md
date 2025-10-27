# 🛒 COMPRAS ANÓNIMAS - SIN CÓDIGO DE DESCUENTO

## 📋 PROBLEMA IDENTIFICADO

Cuando un usuario compraba desde **favoritos** (sin sesión/anónimo), el sistema intentaba generar un código de descuento pero:

❌ **NO había `id_usuario`** para guardar en `usuario_codigo_descuento`  
❌ **Se generaba código inútil** en `codigo_descuento` que nunca se podía usar  
❌ **Se enviaba email con código** que el usuario anónimo no podía redimir  

**PROBLEMA ADICIONAL ENCONTRADO:**
- ⚠️ El archivo `informacion-carrito/php/subir_comprobante-carrito.php` SIEMPRE generaba código primero, luego intentaba asignarlo
- ⚠️ El archivo `admin/php/acciones.php` SIEMPRE generaba código primero, incluso para órdenes anónimas
- ⚠️ Esto causaba códigos huérfanos en la BD que nunca se podían usar

---

## ✅ SOLUCIÓN IMPLEMENTADA (ACTUALIZADA - TODOS LOS FLUJOS)

### **1. Detección de Compra Anónima**

```php
// Verificar si es compra anónima (desde favoritos sin sesión)
$es_compra_anonima = !isset($id_usuario) || empty($id_usuario) || $id_usuario <= 0;
```

### **2. Dos Flujos Diferentes**

#### **🟢 Usuario Registrado (CON sesión):**
1. ✅ Genera código de descuento único
2. ✅ Guarda en tabla `codigo_descuento`
3. ✅ Asigna al usuario en `usuario_codigo_descuento`
4. ✅ Envía email CON código de descuento

#### **🟡 Usuario Anónimo (SIN sesión - Favoritos):**
1. ✅ **NO** genera código de descuento
2. ✅ **NO** inserta nada en la BD de códigos
3. ✅ Envía email de agradecimiento **SIN código**
4. ✅ Incluye CTA para invitar a registrarse

---

## 📧 DIFERENCIAS EN LOS CORREOS

### **Usuario Registrado:**
```
Subject: ¡Gracias por tu compra en Finoso! Aquí tienes un obsequio especial

Contenido:
- Productos comprados
- ✨ CÓDIGO DE DESCUENTO: FINxxxxxx
- Válido hasta: dd/mm/yyyy
- Porcentaje: 10%
```

### **Usuario Anónimo:**
```
Subject: ¡Gracias por tu compra en Finoso!

Contenido:
- Productos comprados
- ✨ ¿Quieres descuentos exclusivos en tu próxima compra? ✨
- Regístrate en nuestra página y recibe códigos de descuento especiales
- (Sin código de descuento)
```

---

## 🗂️ ARCHIVOS MODIFICADOS (ACTUALIZADO)

### **ARCHIVOS CORREGIDOS:**

1. ✅ **`informacion-favoritos/php/subir_comprobante-carrito.php`** - Nequi favoritos (anónimos)
2. ✅ **`informacion-carrito/php/subir_comprobante-carrito.php`** - Nequi con sesión
3. ✅ **`admin/php/acciones.php`** - Aprobación de órdenes por admin
4. ✅ **`informacion-favoritos/js/validaciones-compra.js`** - Limpieza de localStorage

---

### **1. `informacion-favoritos/js/validaciones-compra.js`**

**Limpieza de localStorage al inicio:**
```javascript
// ⭐ LIMPIAR localStorage al inicio para evitar datos residuales (como id_usuario de compras con sesión)
console.log("🧹 Limpiando localStorage de compras anteriores...");
localStorage.removeItem("nequi_datos_pago");
localStorage.removeItem("wompi_carrito_data");
```
**Propósito:** Evita que datos de compras anteriores con sesión contaminen compras anónimas.

---

### **2. `informacion-favoritos/pago_nequi-carrito.html`**

**Filtrado explícito de id_usuario:**
```javascript
// ⭐ NO enviar id_usuario si es null o vacío (compras anónimas desde favoritos)
document.getElementById("id_usuario_hidden").value = (datosOrden.id_usuario && datosOrden.id_usuario !== "null") ? datosOrden.id_usuario : "";
```
**Propósito:** Asegura que NO se envíe `id_usuario` al PHP si es inválido.

---

### **3. `informacion-favoritos/php/subir_comprobante-carrito.php`** (Nequi - Favoritos)

**Logging detallado al recibir comprobante:**
```php
// ⭐ DEBUG CRÍTICO: Rastrear origen del id_usuario
error_log('=== INICIO DEBUG COMPRA ANÓNIMA ===');
error_log('[NEQUI-CARRITO] id_usuario_sesion (SESSION): ' . ($id_usuario_sesion === null ? 'NULL' : "'" . $id_usuario_sesion . "'"));
error_log('[NEQUI-CARRITO] id_usuario_post (POST): ' . ($id_usuario_post === null ? 'NULL' : "'" . $id_usuario_post . "'"));
error_log('[NEQUI-CARRITO] id_usuario FINAL: ' . ($id_usuario === null ? 'NULL' : "'" . $id_usuario . "'"));
error_log('[NEQUI-CARRITO] Correo: ' . ($_POST['correo'] ?? 'NO DISPONIBLE'));
error_log('=================================');
```
**Propósito:** Permite rastrear exactamente de dónde viene el `id_usuario` para detectar filtraciones.

**Detección de compra anónima y flujo condicional:**
```php
// Verificar si es compra anónima (desde favoritos sin sesión)
$es_compra_anonima = !isset($id_usuario) || empty($id_usuario) || $id_usuario <= 0;

if ($es_compra_anonima) {
    // NO genera código
    error_log('[COMPROBANTE] ⚠ COMPRA ANÓNIMA DETECTADA - NO se generará código de descuento');
} else {
    // Genera código y asigna
    $codigo_descuento = 'FIN' . strtoupper(substr(uniqid(), -6));
    // ... (insertar en BD y asignar)
}
```

**Envío de correo diferenciado:**
```php
if ($es_compra_anonima) {
    enviarCorreoAgradecimientoAnonimo($correo);
} else {
    enviarCorreoCompraExitosa($correo, $codigo_descuento, $porcentaje, $fecha_expiracion);
}
```

---

### **4. `informacion-carrito/php/subir_comprobante-carrito.php`** (Nequi - Con sesión)

**Cambios implementados:**

1. **Detección de compra anónima** (por seguridad, aunque no debería recibir anónimos):
   ```php
   // Verificar si es compra anónima (sin sesión válida)
   $es_compra_anonima = !isset($id_usuario) || empty($id_usuario) || $id_usuario <= 0;
   ```

2. **Flujo condicional de generación de código:**
   ```php
   if ($es_compra_anonima) {
       // NO genera código
       $codigo_descuento = null;
       $porcentaje = null;
       $fecha_expiracion = null;
   } else {
       // Genera código normalmente
       $codigo_descuento = 'FIN' . strtoupper(substr(uniqid(), -6));
       // ... (insertar en BD y asignar)
   }
   ```

3. **Nueva función `enviarCorreoAgradecimientoAnonimo()`** agregada (igual que en favoritos)

4. **Envío condicional de correo:**
   ```php
   if ($es_compra_anonima) {
       enviarCorreoAgradecimientoAnonimo($correo);
   } else {
       enviarCorreoCompraExitosa($correo, $codigo_descuento, $porcentaje, $fecha_expiracion);
   }
   ```

---

### **5. `admin/php/acciones.php`** (Aprobación de órdenes)

**Cambios implementados:**

1. **Detección de compra anónima al aprobar orden:**
   ```php
   // Verificar si es compra anónima (desde favoritos sin sesión)
   $id_usuario_orden = isset($ordenInfo['id_usuario']) ? $ordenInfo['id_usuario'] : null;
   $es_compra_anonima = !$id_usuario_orden || $id_usuario_orden <= 0;
   
   escribir_log('[APROBAR] ID Usuario de la orden: ' . ($id_usuario_orden ? $id_usuario_orden : 'NULL'), 'INFO');
   escribir_log('[APROBAR] Es compra anónima: ' . ($es_compra_anonima ? 'SÍ' : 'NO'), 'INFO');
   ```

2. **Flujo condicional completo:**
   ```php
   if ($es_compra_anonima) {
       // NO generar código
       // Email de agradecimiento sin código + CTA para registrarse
       $mail->Subject = '¡Gracias por tu compra en Finoso! Pedido confirmado';
       // ... email sin código
   } else {
       // Generar código normal
       $codigo_descuento = 'FIN' . strtoupper(substr(uniqid(), -6));
       // ... insertar en BD y asignar
       $mail->Subject = '¡Gracias por tu compra en Finoso! Confirmación de pedido';
       // ... email con código
   }
   ```

3. **Email diferenciado:** El admin ahora envía emails diferentes según si la orden es de usuario registrado o anónimo

---

## 🎯 BENEFICIOS

### **Para el sistema:**
- ✅ No se generan códigos inútiles en la BD
- ✅ No hay intentos de guardar en `usuario_codigo_descuento` sin `id_usuario`
- ✅ Logs más claros (`COMPRA ANÓNIMA DETECTADA`)

### **Para el usuario anónimo:**
- ✅ Recibe email de agradecimiento profesional
- ✅ Se le invita a registrarse para obtener beneficios
- ✅ No recibe códigos que no puede usar

### **Para el negocio:**
- ✅ Incentiva a usuarios anónimos a crear cuenta
- ✅ CTA claro: "Regístrate y obtén descuentos"
- ✅ Convierte compradores anónimos en usuarios registrados

---

## 📊 FLUJO VISUAL

```
COMPRA FINALIZADA
       |
       v
¿Hay id_usuario válido?
       |
       ├─ SÍ → Usuario Registrado
       |        ├─ Genera código (FINxxxxxx)
       |        ├─ Guarda en codigo_descuento
       |        ├─ Asigna en usuario_codigo_descuento
       |        └─ Email CON código ✅
       |
       └─ NO → Usuario Anónimo (Favoritos)
                ├─ NO genera código
                ├─ NO guarda en BD
                └─ Email SIN código + CTA registrarse ✅
```

---

## 🔍 LOGS DE EJEMPLO

### **Compra Anónima:**
```
=== INICIO DEBUG COMPRA ANÓNIMA ===
[NEQUI-CARRITO] id_usuario_sesion (SESSION): NULL
[NEQUI-CARRITO] id_usuario_post (POST): ''
[NEQUI-CARRITO] id_usuario FINAL: NULL
[NEQUI-CARRITO] Correo: anonimo@email.com
=================================
[COMPROBANTE] ID Usuario: NO SET
[COMPROBANTE] ⚠ COMPRA ANÓNIMA DETECTADA - NO se generará código de descuento
[COMPROBANTE] Se enviará correo de agradecimiento simple sin código
✅ Correo de agradecimiento (anónimo) enviado a: anonimo@email.com
```

### **Compra Registrada:**
```
=== INICIO DEBUG COMPRA ANÓNIMA ===
[NEQUI-CARRITO] id_usuario_sesion (SESSION): '42'
[NEQUI-CARRITO] id_usuario_post (POST): NULL
[NEQUI-CARRITO] id_usuario FINAL: '42'
[NEQUI-CARRITO] Correo: cliente@email.com
=================================
[COMPROBANTE] ID Usuario: 42
[COMPROBANTE] ✓ Usuario registrado detectado - Generando código de descuento
[COMPROBANTE] Código generado: FINA1B2C3
[COMPROBANTE] ✓ Código insertado en codigo_descuento - ID: 15
[COMPROBANTE] ✓✓✓ CÓDIGO ASIGNADO EXITOSAMENTE ✓✓✓
✅ Correo enviado a: cliente@email.com
```

---

## ✅ TESTING

### **Caso 1: Compra desde Favoritos (Anónimo)**
1. Ir a catálogo SIN iniciar sesión
2. Agregar relojes a favoritos (localStorage)
3. "Cumplir mis Deseos" → `informacion-favoritos`
4. Completar compra con Nequi
5. ✅ **Resultado esperado**: Email SIN código + invitación a registrarse

### **Caso 2: Compra con Sesión Activa**
1. Iniciar sesión
2. Agregar relojes al carrito (BD)
3. Completar compra con Nequi
4. ✅ **Resultado esperado**: Email CON código de descuento

---

## 🚀 ESTADO

✅ **IMPLEMENTADO COMPLETAMENTE EN TODOS LOS FLUJOS**

### **Protección en todos los puntos de entrada:**
- ✅ **Nequi - Favoritos** (`informacion-favoritos/php/subir_comprobante-carrito.php`)
- ✅ **Nequi - Con sesión** (`informacion-carrito/php/subir_comprobante-carrito.php`)
- ✅ **Admin - Aprobación** (`admin/php/acciones.php`)
- ✅ **Wompi - Favoritos** (`informacion-favoritos/php/wompi_response_carrito.php`) - ✅ Ya NO generaba códigos
- ✅ **Wompi - Con sesión** (`informacion-carrito/php/wompi_response_carrito.php`) - ⚠️ NO genera códigos (se generan al aprobar)

### **Características implementadas:**
- ✅ Detección de compra anónima en todos los flujos
- ✅ **Limpieza de localStorage al inicio** (previene contaminación)
- ✅ **Filtrado explícito de id_usuario** en formularios
- ✅ **Logging detallado** para rastrear origen de datos
- ✅ NO genera código para anónimos en ningún punto
- ✅ Email diferenciado según tipo de usuario
- ✅ CTA para convertir anónimos en registrados
- ✅ **CERO códigos huérfanos** en la base de datos

---

## 📝 NOTAS ADICIONALES

- Los códigos **SOLO** se generan para usuarios con sesión activa (`id_usuario` válido)
- Los códigos **SOLO** se pueden usar si el usuario está registrado
- El sistema invita activamente a los anónimos a registrarse (CTA en emails)
- **CERO desperdicio** de códigos en la base de datos
- La tabla `usuario_codigo_descuento` mantiene **integridad referencial perfecta**
- **TODOS** los flujos de pago están protegidos (Nequi, Wompi, Admin)

### **🔐 Protecciones Implementadas:**

1. **Limpieza proactiva** - `localStorage` se limpia al cargar `informacion-favoritos`
2. **Validación en frontend** - No se envía `id_usuario` si es inválido
3. **Detección en backend** - Verificación ANTES de generar código en todos los archivos
4. **Logging completo** - Rastreo de cada compra para debugging
5. **Protección multi-capa** - 3 archivos PHP protegidos (favoritos, carrito, admin)
6. **Verificación temprana** - Se verifica `id_usuario` ANTES de crear el código

### **❓ ¿Por qué el usuario recibió código si era anónimo?**

Posibles causas (ahora resueltas):
- ✅ **Datos residuales en localStorage** de una compra anterior con sesión → **SOLUCIONADO** con limpieza al inicio
- ✅ **Sesión activa no cerrada** después de una compra con usuario registrado → **DETECTADO** con logging mejorado
- ✅ **Filtro insuficiente** al enviar datos del formulario → **REFORZADO** con validación explícita

---

🎉 **¡Sistema optimizado para manejar ambos tipos de compra correctamente!**


## 📋 PROBLEMA IDENTIFICADO

Cuando un usuario compraba desde **favoritos** (sin sesión/anónimo), el sistema intentaba generar un código de descuento pero:

❌ **NO había `id_usuario`** para guardar en `usuario_codigo_descuento`  
❌ **Se generaba código inútil** en `codigo_descuento` que nunca se podía usar  
❌ **Se enviaba email con código** que el usuario anónimo no podía redimir  

**PROBLEMA ADICIONAL ENCONTRADO:**
- ⚠️ El archivo `informacion-carrito/php/subir_comprobante-carrito.php` SIEMPRE generaba código primero, luego intentaba asignarlo
- ⚠️ El archivo `admin/php/acciones.php` SIEMPRE generaba código primero, incluso para órdenes anónimas
- ⚠️ Esto causaba códigos huérfanos en la BD que nunca se podían usar

---

## ✅ SOLUCIÓN IMPLEMENTADA (ACTUALIZADA - TODOS LOS FLUJOS)

### **1. Detección de Compra Anónima**

```php
// Verificar si es compra anónima (desde favoritos sin sesión)
$es_compra_anonima = !isset($id_usuario) || empty($id_usuario) || $id_usuario <= 0;
```

### **2. Dos Flujos Diferentes**

#### **🟢 Usuario Registrado (CON sesión):**
1. ✅ Genera código de descuento único
2. ✅ Guarda en tabla `codigo_descuento`
3. ✅ Asigna al usuario en `usuario_codigo_descuento`
4. ✅ Envía email CON código de descuento

#### **🟡 Usuario Anónimo (SIN sesión - Favoritos):**
1. ✅ **NO** genera código de descuento
2. ✅ **NO** inserta nada en la BD de códigos
3. ✅ Envía email de agradecimiento **SIN código**
4. ✅ Incluye CTA para invitar a registrarse

---

## 📧 DIFERENCIAS EN LOS CORREOS

### **Usuario Registrado:**
```
Subject: ¡Gracias por tu compra en Finoso! Aquí tienes un obsequio especial

Contenido:
- Productos comprados
- ✨ CÓDIGO DE DESCUENTO: FINxxxxxx
- Válido hasta: dd/mm/yyyy
- Porcentaje: 10%
```

### **Usuario Anónimo:**
```
Subject: ¡Gracias por tu compra en Finoso!

Contenido:
- Productos comprados
- ✨ ¿Quieres descuentos exclusivos en tu próxima compra? ✨
- Regístrate en nuestra página y recibe códigos de descuento especiales
- (Sin código de descuento)
```

---

## 🗂️ ARCHIVOS MODIFICADOS (ACTUALIZADO)

### **ARCHIVOS CORREGIDOS:**

1. ✅ **`informacion-favoritos/php/subir_comprobante-carrito.php`** - Nequi favoritos (anónimos)
2. ✅ **`informacion-carrito/php/subir_comprobante-carrito.php`** - Nequi con sesión
3. ✅ **`admin/php/acciones.php`** - Aprobación de órdenes por admin
4. ✅ **`informacion-favoritos/js/validaciones-compra.js`** - Limpieza de localStorage

---

### **1. `informacion-favoritos/js/validaciones-compra.js`**

**Limpieza de localStorage al inicio:**
```javascript
// ⭐ LIMPIAR localStorage al inicio para evitar datos residuales (como id_usuario de compras con sesión)
console.log("🧹 Limpiando localStorage de compras anteriores...");
localStorage.removeItem("nequi_datos_pago");
localStorage.removeItem("wompi_carrito_data");
```
**Propósito:** Evita que datos de compras anteriores con sesión contaminen compras anónimas.

---

### **2. `informacion-favoritos/pago_nequi-carrito.html`**

**Filtrado explícito de id_usuario:**
```javascript
// ⭐ NO enviar id_usuario si es null o vacío (compras anónimas desde favoritos)
document.getElementById("id_usuario_hidden").value = (datosOrden.id_usuario && datosOrden.id_usuario !== "null") ? datosOrden.id_usuario : "";
```
**Propósito:** Asegura que NO se envíe `id_usuario` al PHP si es inválido.

---

### **3. `informacion-favoritos/php/subir_comprobante-carrito.php`** (Nequi - Favoritos)

**Logging detallado al recibir comprobante:**
```php
// ⭐ DEBUG CRÍTICO: Rastrear origen del id_usuario
error_log('=== INICIO DEBUG COMPRA ANÓNIMA ===');
error_log('[NEQUI-CARRITO] id_usuario_sesion (SESSION): ' . ($id_usuario_sesion === null ? 'NULL' : "'" . $id_usuario_sesion . "'"));
error_log('[NEQUI-CARRITO] id_usuario_post (POST): ' . ($id_usuario_post === null ? 'NULL' : "'" . $id_usuario_post . "'"));
error_log('[NEQUI-CARRITO] id_usuario FINAL: ' . ($id_usuario === null ? 'NULL' : "'" . $id_usuario . "'"));
error_log('[NEQUI-CARRITO] Correo: ' . ($_POST['correo'] ?? 'NO DISPONIBLE'));
error_log('=================================');
```
**Propósito:** Permite rastrear exactamente de dónde viene el `id_usuario` para detectar filtraciones.

**Detección de compra anónima y flujo condicional:**
```php
// Verificar si es compra anónima (desde favoritos sin sesión)
$es_compra_anonima = !isset($id_usuario) || empty($id_usuario) || $id_usuario <= 0;

if ($es_compra_anonima) {
    // NO genera código
    error_log('[COMPROBANTE] ⚠ COMPRA ANÓNIMA DETECTADA - NO se generará código de descuento');
} else {
    // Genera código y asigna
    $codigo_descuento = 'FIN' . strtoupper(substr(uniqid(), -6));
    // ... (insertar en BD y asignar)
}
```

**Envío de correo diferenciado:**
```php
if ($es_compra_anonima) {
    enviarCorreoAgradecimientoAnonimo($correo);
} else {
    enviarCorreoCompraExitosa($correo, $codigo_descuento, $porcentaje, $fecha_expiracion);
}
```

---

### **4. `informacion-carrito/php/subir_comprobante-carrito.php`** (Nequi - Con sesión)

**Cambios implementados:**

1. **Detección de compra anónima** (por seguridad, aunque no debería recibir anónimos):
   ```php
   // Verificar si es compra anónima (sin sesión válida)
   $es_compra_anonima = !isset($id_usuario) || empty($id_usuario) || $id_usuario <= 0;
   ```

2. **Flujo condicional de generación de código:**
   ```php
   if ($es_compra_anonima) {
       // NO genera código
       $codigo_descuento = null;
       $porcentaje = null;
       $fecha_expiracion = null;
   } else {
       // Genera código normalmente
       $codigo_descuento = 'FIN' . strtoupper(substr(uniqid(), -6));
       // ... (insertar en BD y asignar)
   }
   ```

3. **Nueva función `enviarCorreoAgradecimientoAnonimo()`** agregada (igual que en favoritos)

4. **Envío condicional de correo:**
   ```php
   if ($es_compra_anonima) {
       enviarCorreoAgradecimientoAnonimo($correo);
   } else {
       enviarCorreoCompraExitosa($correo, $codigo_descuento, $porcentaje, $fecha_expiracion);
   }
   ```

---

### **5. `admin/php/acciones.php`** (Aprobación de órdenes)

**Cambios implementados:**

1. **Detección de compra anónima al aprobar orden:**
   ```php
   // Verificar si es compra anónima (desde favoritos sin sesión)
   $id_usuario_orden = isset($ordenInfo['id_usuario']) ? $ordenInfo['id_usuario'] : null;
   $es_compra_anonima = !$id_usuario_orden || $id_usuario_orden <= 0;
   
   escribir_log('[APROBAR] ID Usuario de la orden: ' . ($id_usuario_orden ? $id_usuario_orden : 'NULL'), 'INFO');
   escribir_log('[APROBAR] Es compra anónima: ' . ($es_compra_anonima ? 'SÍ' : 'NO'), 'INFO');
   ```

2. **Flujo condicional completo:**
   ```php
   if ($es_compra_anonima) {
       // NO generar código
       // Email de agradecimiento sin código + CTA para registrarse
       $mail->Subject = '¡Gracias por tu compra en Finoso! Pedido confirmado';
       // ... email sin código
   } else {
       // Generar código normal
       $codigo_descuento = 'FIN' . strtoupper(substr(uniqid(), -6));
       // ... insertar en BD y asignar
       $mail->Subject = '¡Gracias por tu compra en Finoso! Confirmación de pedido';
       // ... email con código
   }
   ```

3. **Email diferenciado:** El admin ahora envía emails diferentes según si la orden es de usuario registrado o anónimo

---

## 🎯 BENEFICIOS

### **Para el sistema:**
- ✅ No se generan códigos inútiles en la BD
- ✅ No hay intentos de guardar en `usuario_codigo_descuento` sin `id_usuario`
- ✅ Logs más claros (`COMPRA ANÓNIMA DETECTADA`)

### **Para el usuario anónimo:**
- ✅ Recibe email de agradecimiento profesional
- ✅ Se le invita a registrarse para obtener beneficios
- ✅ No recibe códigos que no puede usar

### **Para el negocio:**
- ✅ Incentiva a usuarios anónimos a crear cuenta
- ✅ CTA claro: "Regístrate y obtén descuentos"
- ✅ Convierte compradores anónimos en usuarios registrados

---

## 📊 FLUJO VISUAL

```
COMPRA FINALIZADA
       |
       v
¿Hay id_usuario válido?
       |
       ├─ SÍ → Usuario Registrado
       |        ├─ Genera código (FINxxxxxx)
       |        ├─ Guarda en codigo_descuento
       |        ├─ Asigna en usuario_codigo_descuento
       |        └─ Email CON código ✅
       |
       └─ NO → Usuario Anónimo (Favoritos)
                ├─ NO genera código
                ├─ NO guarda en BD
                └─ Email SIN código + CTA registrarse ✅
```

---

## 🔍 LOGS DE EJEMPLO

### **Compra Anónima:**
```
=== INICIO DEBUG COMPRA ANÓNIMA ===
[NEQUI-CARRITO] id_usuario_sesion (SESSION): NULL
[NEQUI-CARRITO] id_usuario_post (POST): ''
[NEQUI-CARRITO] id_usuario FINAL: NULL
[NEQUI-CARRITO] Correo: anonimo@email.com
=================================
[COMPROBANTE] ID Usuario: NO SET
[COMPROBANTE] ⚠ COMPRA ANÓNIMA DETECTADA - NO se generará código de descuento
[COMPROBANTE] Se enviará correo de agradecimiento simple sin código
✅ Correo de agradecimiento (anónimo) enviado a: anonimo@email.com
```

### **Compra Registrada:**
```
=== INICIO DEBUG COMPRA ANÓNIMA ===
[NEQUI-CARRITO] id_usuario_sesion (SESSION): '42'
[NEQUI-CARRITO] id_usuario_post (POST): NULL
[NEQUI-CARRITO] id_usuario FINAL: '42'
[NEQUI-CARRITO] Correo: cliente@email.com
=================================
[COMPROBANTE] ID Usuario: 42
[COMPROBANTE] ✓ Usuario registrado detectado - Generando código de descuento
[COMPROBANTE] Código generado: FINA1B2C3
[COMPROBANTE] ✓ Código insertado en codigo_descuento - ID: 15
[COMPROBANTE] ✓✓✓ CÓDIGO ASIGNADO EXITOSAMENTE ✓✓✓
✅ Correo enviado a: cliente@email.com
```

---

## ✅ TESTING

### **Caso 1: Compra desde Favoritos (Anónimo)**
1. Ir a catálogo SIN iniciar sesión
2. Agregar relojes a favoritos (localStorage)
3. "Cumplir mis Deseos" → `informacion-favoritos`
4. Completar compra con Nequi
5. ✅ **Resultado esperado**: Email SIN código + invitación a registrarse

### **Caso 2: Compra con Sesión Activa**
1. Iniciar sesión
2. Agregar relojes al carrito (BD)
3. Completar compra con Nequi
4. ✅ **Resultado esperado**: Email CON código de descuento

---

## 🚀 ESTADO

✅ **IMPLEMENTADO COMPLETAMENTE EN TODOS LOS FLUJOS**

### **Protección en todos los puntos de entrada:**
- ✅ **Nequi - Favoritos** (`informacion-favoritos/php/subir_comprobante-carrito.php`)
- ✅ **Nequi - Con sesión** (`informacion-carrito/php/subir_comprobante-carrito.php`)
- ✅ **Admin - Aprobación** (`admin/php/acciones.php`)
- ✅ **Wompi - Favoritos** (`informacion-favoritos/php/wompi_response_carrito.php`) - ✅ Ya NO generaba códigos
- ✅ **Wompi - Con sesión** (`informacion-carrito/php/wompi_response_carrito.php`) - ⚠️ NO genera códigos (se generan al aprobar)

### **Características implementadas:**
- ✅ Detección de compra anónima en todos los flujos
- ✅ **Limpieza de localStorage al inicio** (previene contaminación)
- ✅ **Filtrado explícito de id_usuario** en formularios
- ✅ **Logging detallado** para rastrear origen de datos
- ✅ NO genera código para anónimos en ningún punto
- ✅ Email diferenciado según tipo de usuario
- ✅ CTA para convertir anónimos en registrados
- ✅ **CERO códigos huérfanos** en la base de datos

---

## 📝 NOTAS ADICIONALES

- Los códigos **SOLO** se generan para usuarios con sesión activa (`id_usuario` válido)
- Los códigos **SOLO** se pueden usar si el usuario está registrado
- El sistema invita activamente a los anónimos a registrarse (CTA en emails)
- **CERO desperdicio** de códigos en la base de datos
- La tabla `usuario_codigo_descuento` mantiene **integridad referencial perfecta**
- **TODOS** los flujos de pago están protegidos (Nequi, Wompi, Admin)

### **🔐 Protecciones Implementadas:**

1. **Limpieza proactiva** - `localStorage` se limpia al cargar `informacion-favoritos`
2. **Validación en frontend** - No se envía `id_usuario` si es inválido
3. **Detección en backend** - Verificación ANTES de generar código en todos los archivos
4. **Logging completo** - Rastreo de cada compra para debugging
5. **Protección multi-capa** - 3 archivos PHP protegidos (favoritos, carrito, admin)
6. **Verificación temprana** - Se verifica `id_usuario` ANTES de crear el código

### **❓ ¿Por qué el usuario recibió código si era anónimo?**

Posibles causas (ahora resueltas):
- ✅ **Datos residuales en localStorage** de una compra anterior con sesión → **SOLUCIONADO** con limpieza al inicio
- ✅ **Sesión activa no cerrada** después de una compra con usuario registrado → **DETECTADO** con logging mejorado
- ✅ **Filtro insuficiente** al enviar datos del formulario → **REFORZADO** con validación explícita

---

🎉 **¡Sistema optimizado para manejar ambos tipos de compra correctamente!**

