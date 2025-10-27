# Implementación Campo `monto_pagado` en Formularios de Cliente

## 📋 Objetivo

Permitir que el **cliente** ingrese el monto que pagó al subir su comprobante, facilitando la validación por parte del admin y mejorando la detección de discrepancias en los pagos.

---

## ✅ Implementación Completada

### 1. **Frontend - Formularios HTML**

Se agregó un campo opcional `monto_pagado` en todos los formularios de pago Nequi:

#### Archivos modificados:
- ✅ `informacion/pago_nequi.html` (Compras individuales)
- ✅ `informacion-carrito/pago_nequi-carrito.html` (Compras desde carrito)
- ✅ `informacion-favoritos/pago_nequi-carrito.html` (Compras desde favoritos)

#### Código agregado:
```html
<p style="margin: 20px 0 10px; color: #e0e0e0;">Monto que pagaste (opcional)</p>
<input type="number" name="monto_pagado" id="monto_pagado_input" 
       placeholder="Ej: 135000" 
       step="1000" 
       min="0"
       style="width: 100%; padding: 15px 20px; background: rgba(34, 34, 34, 0.8); color: #fff; border: 2px solid rgba(255, 207, 102, 0.3); border-radius: 15px; font-size: 1rem; margin-bottom: 15px;" />
<p style="margin: -10px 0 20px; color: #999; font-size: 0.9rem;">Si pagaste un monto diferente al total, indícalo aquí</p>
```

**Características:**
- Campo **opcional** (no required)
- Tipo `number` con `step="1000"` (incrementos de mil pesos)
- Placeholder con ejemplo: "Ej: 135000"
- Mensaje aclaratorio debajo del campo
- Estilos consistentes con el resto del formulario

---

### 2. **Backend - PHP**

Se captura y guarda el `monto_pagado` en la tabla `orden`:

#### Archivos modificados:
- ✅ `informacion/php/subir_comprobante.php`
- ✅ `informacion-carrito/php/subir_comprobante-carrito.php`
- ✅ `informacion-favoritos/php/subir_comprobante-carrito.php`

#### Código agregado:

```php
// Capturar monto pagado (opcional)
$monto_pagado = isset($_POST['monto_pagado']) && !empty($_POST['monto_pagado']) ? floatval($_POST['monto_pagado']) : null;
```

#### Actualización del INSERT:

**Antes:**
```sql
INSERT INTO orden (..., comprobante_pago, nombre_archivo_comprobante, correo, token_verificacion)
VALUES (..., ?, ?, ?, ?)
```

**Ahora:**
```sql
INSERT INTO orden (..., comprobante_pago, nombre_archivo_comprobante, correo, token_verificacion, monto_pagado)
VALUES (..., ?, ?, ?, ?, ?)
```

#### Actualización del bind_param:

```php
// Se agregó 'd' al final (double/float)
$stmt->bind_param("idssdssssssssssssd", ..., $monto_pagado);
```

**Manejo de NULL:**
- Si el campo está vacío → se guarda `NULL` en la BD
- Si el campo tiene valor → se guarda el monto como `decimal(10,2)`

---

### 3. **Base de Datos - `finoso.sql`**

Se actualizó el esquema de producción:

#### Campo `monto_pagado` actualizado:

**Antes:**
```sql
`monto_pagado` decimal(10,2) DEFAULT 0 COMMENT 'Monto real que pagó el cliente (puede ser menor al total)',
```

**Ahora:**
```sql
`monto_pagado` decimal(10,2) DEFAULT NULL COMMENT 'Monto real que pagó el cliente (puede ser menor al total esperado)',
```

**Cambios:**
- `DEFAULT 0` → `DEFAULT NULL` (más semántico)
- Comentario mejorado

---

### 4. **Campos Eliminados de `finoso.sql`**

Se eliminaron campos que **NO se usan** en el sistema:

#### ❌ `codigo_descuento_id`
- **Razón:** No se usa en ningún archivo PHP
- **Sistema actual:** Los códigos se manejan con `usuario_codigo_descuento`
- **Eliminados:**
  - Columna en tabla `orden`
  - Índice `KEY codigo_descuento_id`
  - Foreign key `orden_ibfk_2`

#### ❌ `intentos_pago`
- **Razón:** No existe flujo de resubida de comprobantes
- **Observación:** Se rechaza la orden y se notifica, pero no se permite resubir

#### ❌ `fecha_ultima_subida`
- **Razón:** No existe flujo de resubida de comprobantes
- **Observación:** Asociado a `intentos_pago`, sin funcionalidad actual

---

## 📊 Flujo Completo

### Escenario 1: Cliente paga el monto exacto

1. **Cliente:**
   - Total a pagar: **$135.000**
   - Paga: **$135.000**
   - Sube comprobante y **deja el campo vacío** (o ingresa 135000)

2. **Base de Datos:**
   - `total`: 135000
   - `monto_pagado`: NULL (o 135000)

3. **Admin:**
   - Ve el comprobante
   - No hay advertencias
   - Aprueba la orden

---

### Escenario 2: Cliente paga menos (error o pago parcial)

1. **Cliente:**
   - Total a pagar: **$135.000**
   - Paga: **$130.000** (se equivocó o no alcanzó)
   - Sube comprobante e ingresa: **130000**

2. **Base de Datos:**
   - `total`: 135000
   - `monto_pagado`: 130000

3. **Admin:**
   - Ve el comprobante
   - **Panel muestra:** ⚠️ "Monto declarado: $130.000 (diferencia de $5.000)"
   - Puede verificar en el comprobante
   - **Opciones:**
     - Rechazar con motivo "Monto incorrecto"
     - Aprobar si considera que puede cobrarse después

---

### Escenario 3: Cliente paga más (error)

1. **Cliente:**
   - Total a pagar: **$135.000**
   - Paga: **$140.000** (error al digitar o cobró de más)
   - Sube comprobante e ingresa: **140000**

2. **Base de Datos:**
   - `total`: 135000
   - `monto_pagado`: 140000

3. **Admin:**
   - Ve el comprobante
   - **Panel muestra:** ⚠️ "Monto declarado: $140.000 (excedente de $5.000)"
   - Puede verificar en el comprobante
   - Aprobar y gestionar devolución/crédito

---

## 🎯 Beneficios

### Para el Cliente:
✅ Transparencia al declarar el monto pagado  
✅ Facilita resolución de discrepancias  
✅ Campo opcional, no obligatorio

### Para el Admin:
✅ Validación más rápida de comprobantes  
✅ Detección automática de discrepancias  
✅ Información clara antes de aprobar  
✅ Reduce rechazos por malentendidos

### Para el Sistema:
✅ Datos más completos para auditoría  
✅ Estadísticas de pagos incorrectos  
✅ Mejora la trazabilidad de pagos

---

## 🔄 Integración con Sistema Existente

### Admin - Al Rechazar por Monto Incorrecto

El admin **ya puede** ingresar el `monto_pagado` al rechazar una orden:

**Archivo:** `admin/php/acciones.php`

```php
// Si el admin ingresa monto al rechazar:
UPDATE orden 
SET estado = 'rechazado', 
    motivo_rechazo = ?, 
    monto_pagado = ?  // ← Ya existente
WHERE id_orden = ?
```

**Ahora con la implementación del cliente:**
- Si el cliente **YA ingresó el monto** → Admin ve el valor y puede confirmarlo
- Si el cliente **NO ingresó el monto** → Admin puede ingresarlo manualmente al rechazar

---

## 📝 Resumen de Archivos Modificados

### Frontend (3 archivos):
1. `informacion/pago_nequi.html`
2. `informacion-carrito/pago_nequi-carrito.html`
3. `informacion-favoritos/pago_nequi-carrito.html`

### Backend (3 archivos):
4. `informacion/php/subir_comprobante.php`
5. `informacion-carrito/php/subir_comprobante-carrito.php`
6. `informacion-favoritos/php/subir_comprobante-carrito.php`

### Base de Datos (1 archivo):
7. `finoso.sql`

**Total: 7 archivos modificados**

---

## 🧪 Pruebas Recomendadas

### Test 1: Campo vacío (normal)
1. Realizar compra y subir comprobante
2. **No ingresar** monto en el campo
3. Verificar que la orden se cree con `monto_pagado = NULL`

### Test 2: Campo con valor correcto
1. Realizar compra por $135.000
2. Ingresar **135000** en el campo
3. Verificar que se guarde correctamente

### Test 3: Campo con valor menor
1. Realizar compra por $135.000
2. Ingresar **130000** en el campo
3. Verificar que el admin vea la discrepancia

### Test 4: Validación de formato
1. Intentar ingresar valores negativos (bloqueado por `min="0"`)
2. Intentar ingresar texto (bloqueado por `type="number"`)
3. Verificar que `step="1000"` funciona correctamente

---

**Fecha:** 27 de octubre de 2025  
**Estado:** ✅ Implementado completamente


## 📋 Objetivo

Permitir que el **cliente** ingrese el monto que pagó al subir su comprobante, facilitando la validación por parte del admin y mejorando la detección de discrepancias en los pagos.

---

## ✅ Implementación Completada

### 1. **Frontend - Formularios HTML**

Se agregó un campo opcional `monto_pagado` en todos los formularios de pago Nequi:

#### Archivos modificados:
- ✅ `informacion/pago_nequi.html` (Compras individuales)
- ✅ `informacion-carrito/pago_nequi-carrito.html` (Compras desde carrito)
- ✅ `informacion-favoritos/pago_nequi-carrito.html` (Compras desde favoritos)

#### Código agregado:
```html
<p style="margin: 20px 0 10px; color: #e0e0e0;">Monto que pagaste (opcional)</p>
<input type="number" name="monto_pagado" id="monto_pagado_input" 
       placeholder="Ej: 135000" 
       step="1000" 
       min="0"
       style="width: 100%; padding: 15px 20px; background: rgba(34, 34, 34, 0.8); color: #fff; border: 2px solid rgba(255, 207, 102, 0.3); border-radius: 15px; font-size: 1rem; margin-bottom: 15px;" />
<p style="margin: -10px 0 20px; color: #999; font-size: 0.9rem;">Si pagaste un monto diferente al total, indícalo aquí</p>
```

**Características:**
- Campo **opcional** (no required)
- Tipo `number` con `step="1000"` (incrementos de mil pesos)
- Placeholder con ejemplo: "Ej: 135000"
- Mensaje aclaratorio debajo del campo
- Estilos consistentes con el resto del formulario

---

### 2. **Backend - PHP**

Se captura y guarda el `monto_pagado` en la tabla `orden`:

#### Archivos modificados:
- ✅ `informacion/php/subir_comprobante.php`
- ✅ `informacion-carrito/php/subir_comprobante-carrito.php`
- ✅ `informacion-favoritos/php/subir_comprobante-carrito.php`

#### Código agregado:

```php
// Capturar monto pagado (opcional)
$monto_pagado = isset($_POST['monto_pagado']) && !empty($_POST['monto_pagado']) ? floatval($_POST['monto_pagado']) : null;
```

#### Actualización del INSERT:

**Antes:**
```sql
INSERT INTO orden (..., comprobante_pago, nombre_archivo_comprobante, correo, token_verificacion)
VALUES (..., ?, ?, ?, ?)
```

**Ahora:**
```sql
INSERT INTO orden (..., comprobante_pago, nombre_archivo_comprobante, correo, token_verificacion, monto_pagado)
VALUES (..., ?, ?, ?, ?, ?)
```

#### Actualización del bind_param:

```php
// Se agregó 'd' al final (double/float)
$stmt->bind_param("idssdssssssssssssd", ..., $monto_pagado);
```

**Manejo de NULL:**
- Si el campo está vacío → se guarda `NULL` en la BD
- Si el campo tiene valor → se guarda el monto como `decimal(10,2)`

---

### 3. **Base de Datos - `finoso.sql`**

Se actualizó el esquema de producción:

#### Campo `monto_pagado` actualizado:

**Antes:**
```sql
`monto_pagado` decimal(10,2) DEFAULT 0 COMMENT 'Monto real que pagó el cliente (puede ser menor al total)',
```

**Ahora:**
```sql
`monto_pagado` decimal(10,2) DEFAULT NULL COMMENT 'Monto real que pagó el cliente (puede ser menor al total esperado)',
```

**Cambios:**
- `DEFAULT 0` → `DEFAULT NULL` (más semántico)
- Comentario mejorado

---

### 4. **Campos Eliminados de `finoso.sql`**

Se eliminaron campos que **NO se usan** en el sistema:

#### ❌ `codigo_descuento_id`
- **Razón:** No se usa en ningún archivo PHP
- **Sistema actual:** Los códigos se manejan con `usuario_codigo_descuento`
- **Eliminados:**
  - Columna en tabla `orden`
  - Índice `KEY codigo_descuento_id`
  - Foreign key `orden_ibfk_2`

#### ❌ `intentos_pago`
- **Razón:** No existe flujo de resubida de comprobantes
- **Observación:** Se rechaza la orden y se notifica, pero no se permite resubir

#### ❌ `fecha_ultima_subida`
- **Razón:** No existe flujo de resubida de comprobantes
- **Observación:** Asociado a `intentos_pago`, sin funcionalidad actual

---

## 📊 Flujo Completo

### Escenario 1: Cliente paga el monto exacto

1. **Cliente:**
   - Total a pagar: **$135.000**
   - Paga: **$135.000**
   - Sube comprobante y **deja el campo vacío** (o ingresa 135000)

2. **Base de Datos:**
   - `total`: 135000
   - `monto_pagado`: NULL (o 135000)

3. **Admin:**
   - Ve el comprobante
   - No hay advertencias
   - Aprueba la orden

---

### Escenario 2: Cliente paga menos (error o pago parcial)

1. **Cliente:**
   - Total a pagar: **$135.000**
   - Paga: **$130.000** (se equivocó o no alcanzó)
   - Sube comprobante e ingresa: **130000**

2. **Base de Datos:**
   - `total`: 135000
   - `monto_pagado`: 130000

3. **Admin:**
   - Ve el comprobante
   - **Panel muestra:** ⚠️ "Monto declarado: $130.000 (diferencia de $5.000)"
   - Puede verificar en el comprobante
   - **Opciones:**
     - Rechazar con motivo "Monto incorrecto"
     - Aprobar si considera que puede cobrarse después

---

### Escenario 3: Cliente paga más (error)

1. **Cliente:**
   - Total a pagar: **$135.000**
   - Paga: **$140.000** (error al digitar o cobró de más)
   - Sube comprobante e ingresa: **140000**

2. **Base de Datos:**
   - `total`: 135000
   - `monto_pagado`: 140000

3. **Admin:**
   - Ve el comprobante
   - **Panel muestra:** ⚠️ "Monto declarado: $140.000 (excedente de $5.000)"
   - Puede verificar en el comprobante
   - Aprobar y gestionar devolución/crédito

---

## 🎯 Beneficios

### Para el Cliente:
✅ Transparencia al declarar el monto pagado  
✅ Facilita resolución de discrepancias  
✅ Campo opcional, no obligatorio

### Para el Admin:
✅ Validación más rápida de comprobantes  
✅ Detección automática de discrepancias  
✅ Información clara antes de aprobar  
✅ Reduce rechazos por malentendidos

### Para el Sistema:
✅ Datos más completos para auditoría  
✅ Estadísticas de pagos incorrectos  
✅ Mejora la trazabilidad de pagos

---

## 🔄 Integración con Sistema Existente

### Admin - Al Rechazar por Monto Incorrecto

El admin **ya puede** ingresar el `monto_pagado` al rechazar una orden:

**Archivo:** `admin/php/acciones.php`

```php
// Si el admin ingresa monto al rechazar:
UPDATE orden 
SET estado = 'rechazado', 
    motivo_rechazo = ?, 
    monto_pagado = ?  // ← Ya existente
WHERE id_orden = ?
```

**Ahora con la implementación del cliente:**
- Si el cliente **YA ingresó el monto** → Admin ve el valor y puede confirmarlo
- Si el cliente **NO ingresó el monto** → Admin puede ingresarlo manualmente al rechazar

---

## 📝 Resumen de Archivos Modificados

### Frontend (3 archivos):
1. `informacion/pago_nequi.html`
2. `informacion-carrito/pago_nequi-carrito.html`
3. `informacion-favoritos/pago_nequi-carrito.html`

### Backend (3 archivos):
4. `informacion/php/subir_comprobante.php`
5. `informacion-carrito/php/subir_comprobante-carrito.php`
6. `informacion-favoritos/php/subir_comprobante-carrito.php`

### Base de Datos (1 archivo):
7. `finoso.sql`

**Total: 7 archivos modificados**

---

## 🧪 Pruebas Recomendadas

### Test 1: Campo vacío (normal)
1. Realizar compra y subir comprobante
2. **No ingresar** monto en el campo
3. Verificar que la orden se cree con `monto_pagado = NULL`

### Test 2: Campo con valor correcto
1. Realizar compra por $135.000
2. Ingresar **135000** en el campo
3. Verificar que se guarde correctamente

### Test 3: Campo con valor menor
1. Realizar compra por $135.000
2. Ingresar **130000** en el campo
3. Verificar que el admin vea la discrepancia

### Test 4: Validación de formato
1. Intentar ingresar valores negativos (bloqueado por `min="0"`)
2. Intentar ingresar texto (bloqueado por `type="number"`)
3. Verificar que `step="1000"` funciona correctamente

---

**Fecha:** 27 de octubre de 2025  
**Estado:** ✅ Implementado completamente

