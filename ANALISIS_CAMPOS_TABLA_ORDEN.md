# Análisis de Campos de la Tabla `orden`

## 📊 Análisis de 4 Registros de Ejemplo

### Orden #42 (pendiente_verificacion)
- 🟡 `id_usuario`: NULL (⚠️ compra anónima o sin capturar)
- ❌ `nombre_archivo_comprobante`: NULL (debería ser "comprobante_1761538407...")
- ✅ `comprobante_verificado`: 0 (correcto)

### Orden #43 (pendiente_verificacion, comprobante verificado)
- ✅ `id_usuario`: 1
- ❌ `nombre_archivo_comprobante`: NULL (debería tener valor)
- ✅ `comprobante_verificado`: 1 (ya verificado, esperando aprobación)

### Orden #44 (pagado/aprobado)
- ✅ `id_usuario`: 1
- ✅ `nombre_archivo_comprobante`: "krea-edit__11_..." (correcto)
- ✅ `fecha_aprobacion`: 2025-10-26 23:24:24
- ✅ `comprobante_verificado`: 1

### Orden #45 (rechazado)
- 🟡 `id_usuario`: NULL (compra anónima)
- ✅ `nombre_archivo_comprobante`: "Lucid_Origin_Logo..."
- ✅ `fecha_aprobacion`: 2025-10-26 23:25:32 (fecha de rechazo)
- ✅ `observaciones`: "El comprobante pertenece a otra transacción"
- ✅ `comprobante_verificado`: 0

---

## ❌ Problemas Identificados

### 1. **`nombre_archivo_comprobante` queda NULL**

**Problema:** En `informacion-carrito/php/subir_comprobante-carrito.php` y `informacion-favoritos/php/subir_comprobante-carrito.php`:

```php
$sql_orden = "INSERT INTO orden (
    id_usuario, total, estado, metodo_pago, costo_envio,
    nombre, cedula, celular, departamento, ciudad, direccion, barrio, referencias,
    comprobante_pago, correo, token_verificacion  // ❌ Falta nombre_archivo_comprobante
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
```

**Consecuencia:**
- El campo `comprobante_pago` tiene la ruta completa
- El campo `nombre_archivo_comprobante` queda NULL
- Hay inconsistencia en los datos

**Solución:**
Agregar `nombre_archivo_comprobante` al INSERT:

```php
$sql_orden = "INSERT INTO orden (
    id_usuario, total, estado, metodo_pago, costo_envio,
    nombre, cedula, celular, departamento, ciudad, direccion, barrio, referencias,
    comprobante_pago, nombre_archivo_comprobante, correo, token_verificacion
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
```

---

## 🗑️ Campos que NO SE USAN (Candidatos a Eliminar)

### 1. **`codigo_seguimiento`** - Siempre 0
- No se usa en ningún archivo PHP
- Redundante con `token_verificacion`
- **Recomendación:** ❌ ELIMINAR

### 2. **`hash_comprobante`** - Siempre NULL
- Se creó para detectar comprobantes duplicados
- Nunca se implementó
- **Recomendación:** ✅ MANTENER pero IMPLEMENTAR (es útil para seguridad)

---

## ⚠️ Campos que DEBERÍAN USARSE pero NO

### 1. **`monto_pagado`** - Siempre 0.00

**Para qué sirve:**
- Almacenar el monto que el usuario **dice haber pagado**
- Comparar con el total esperado
- Detectar errores o intentos de fraude

**Ejemplo de uso:**
```php
// Al subir comprobante, pedir al usuario que ingrese el monto pagado
$monto_pagado = floatval($_POST['monto_pagado']);

// Al verificar comprobante, comparar
if (abs($monto_pagado - $total) > 1000) {
    // Advertir al admin: "El monto declarado ($X) no coincide con el total ($Y)"
}
```

**Recomendación:** ⭐ IMPLEMENTAR - Mejora validación y reduce fraudes

### 2. **`hash_comprobante`** - Siempre NULL

**Para qué sirve:**
- Generar un hash único del archivo del comprobante
- Detectar si el mismo comprobante se sube 2 veces
- Prevenir fraude

**Ejemplo de uso:**
```php
// Al subir comprobante
$hash_comprobante = hash_file('sha256', $archivo['tmp_name']);

// Verificar si ya existe
$stmt = $conn->prepare("SELECT COUNT(*) FROM orden WHERE hash_comprobante = ?");
$stmt->bind_param("s", $hash_comprobante);
// ...
if ($ya_existe > 0) {
    die("Este comprobante ya fue utilizado en otra orden");
}
```

**Recomendación:** ⭐ IMPLEMENTAR - Importante para seguridad

---

## ✅ Campos que SÍ se usan correctamente

### Flujo normal de una orden:

1. **Creación (pendiente_verificacion):**
   - `comprobante_pago` ✅
   - `token_verificacion` ✅
   - `comprobante_verificado` = 0 ✅

2. **Admin verifica comprobante:**
   - `comprobante_verificado` = 1 ✅
   - Aún en `pendiente_verificacion` ✅

3. **Admin aprueba orden:**
   - `estado` → 'pagado' ✅
   - `fecha_aprobacion` ✅
   - `codigo_descuento` (si tiene sesión) ✅

4. **Admin marca como enviado:**
   - `estado` → 'enviado' ✅
   - `guia_envio` ✅
   - `transportadora` ✅
   - `fecha_entrega_estimada` ✅

5. **Orden entregada:**
   - `estado` → 'entregado' ✅

### Si se rechaza:

- `estado` → 'rechazado' ✅
- `fecha_aprobacion` (fecha de rechazo) ✅
- `observaciones` (razón) ✅
- `recordatorio_enviado` / `fecha_recordatorio` ✅

---

## 🔧 Resumen de Recomendaciones

### 🔴 Urgente (Bugs):
1. ❌ Corregir INSERT para incluir `nombre_archivo_comprobante`

### 🟡 Mejoras de Seguridad:
2. ⭐ Implementar `hash_comprobante` para detectar comprobantes duplicados
3. ⭐ Implementar `monto_pagado` para mejorar validación

### 🟢 Limpieza:
4. 🗑️ Eliminar `codigo_seguimiento` (nunca se usa y es redundante)

### ✅ Mantener:
- Todos los demás campos están bien y se usan correctamente

---

## 📋 Campos de la Tabla `orden` (Evaluación)

| Campo | ¿Se usa? | ¿Correcto? | Notas |
|-------|----------|------------|-------|
| `id_orden` | ✅ | ✅ | PK |
| `id_usuario` | ✅ | ✅ | NULL si es anónimo |
| `nombre` | ✅ | ✅ | |
| `correo` | ✅ | ✅ | |
| `cedula` | ✅ | ✅ | |
| `celular` | ✅ | ✅ | |
| `departamento` | ✅ | ✅ | |
| `ciudad` | ✅ | ✅ | |
| `direccion` | ✅ | ✅ | |
| `barrio` | ✅ | ✅ | |
| `metodo_pago` | ✅ | ✅ | |
| `costo_envio` | ✅ | ✅ | |
| `fecha` | ✅ | ✅ | |
| `total` | ✅ | ✅ | |
| `estado` | ✅ | ✅ | |
| `fecha_envio` | ✅ | ✅ | Solo cuando se envía |
| `comprobante_pago` | ✅ | ✅ | Ruta completa |
| `nombre_archivo_comprobante` | ✅ | ❌ | **BUG: Queda NULL** |
| `token_verificacion` | ✅ | ✅ | |
| `fecha_aprobacion` | ✅ | ✅ | |
| `observaciones` | ✅ | ✅ | Solo en rechazos |
| `comprobante_verificado` | ✅ | ✅ | |
| `hash_comprobante` | ❌ | ⚠️ | **No implementado, debería** |
| `guia_envio` | ✅ | ✅ | Solo cuando se envía |
| `transportadora` | ✅ | ✅ | Solo cuando se envía |
| `fecha_entrega_estimada` | ✅ | ✅ | Solo cuando se envía |
| `codigo_descuento` | ✅ | ✅ | Al aprobar (con sesión) |
| `codigo_seguimiento` | ❌ | 🗑️ | **Nunca se usa, eliminar** |
| `monto_pagado` | ❌ | ⚠️ | **No implementado, debería** |
| `recordatorio_enviado` | ✅ | ✅ | Órdenes rechazadas |
| `fecha_recordatorio` | ✅ | ✅ | Órdenes rechazadas |

---

**Fecha**: 27 de octubre de 2025  
**Estado**: 📊 Análisis completo


## 📊 Análisis de 4 Registros de Ejemplo

### Orden #42 (pendiente_verificacion)
- 🟡 `id_usuario`: NULL (⚠️ compra anónima o sin capturar)
- ❌ `nombre_archivo_comprobante`: NULL (debería ser "comprobante_1761538407...")
- ✅ `comprobante_verificado`: 0 (correcto)

### Orden #43 (pendiente_verificacion, comprobante verificado)
- ✅ `id_usuario`: 1
- ❌ `nombre_archivo_comprobante`: NULL (debería tener valor)
- ✅ `comprobante_verificado`: 1 (ya verificado, esperando aprobación)

### Orden #44 (pagado/aprobado)
- ✅ `id_usuario`: 1
- ✅ `nombre_archivo_comprobante`: "krea-edit__11_..." (correcto)
- ✅ `fecha_aprobacion`: 2025-10-26 23:24:24
- ✅ `comprobante_verificado`: 1

### Orden #45 (rechazado)
- 🟡 `id_usuario`: NULL (compra anónima)
- ✅ `nombre_archivo_comprobante`: "Lucid_Origin_Logo..."
- ✅ `fecha_aprobacion`: 2025-10-26 23:25:32 (fecha de rechazo)
- ✅ `observaciones`: "El comprobante pertenece a otra transacción"
- ✅ `comprobante_verificado`: 0

---

## ❌ Problemas Identificados

### 1. **`nombre_archivo_comprobante` queda NULL**

**Problema:** En `informacion-carrito/php/subir_comprobante-carrito.php` y `informacion-favoritos/php/subir_comprobante-carrito.php`:

```php
$sql_orden = "INSERT INTO orden (
    id_usuario, total, estado, metodo_pago, costo_envio,
    nombre, cedula, celular, departamento, ciudad, direccion, barrio, referencias,
    comprobante_pago, correo, token_verificacion  // ❌ Falta nombre_archivo_comprobante
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
```

**Consecuencia:**
- El campo `comprobante_pago` tiene la ruta completa
- El campo `nombre_archivo_comprobante` queda NULL
- Hay inconsistencia en los datos

**Solución:**
Agregar `nombre_archivo_comprobante` al INSERT:

```php
$sql_orden = "INSERT INTO orden (
    id_usuario, total, estado, metodo_pago, costo_envio,
    nombre, cedula, celular, departamento, ciudad, direccion, barrio, referencias,
    comprobante_pago, nombre_archivo_comprobante, correo, token_verificacion
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
```

---

## 🗑️ Campos que NO SE USAN (Candidatos a Eliminar)

### 1. **`codigo_seguimiento`** - Siempre 0
- No se usa en ningún archivo PHP
- Redundante con `token_verificacion`
- **Recomendación:** ❌ ELIMINAR

### 2. **`hash_comprobante`** - Siempre NULL
- Se creó para detectar comprobantes duplicados
- Nunca se implementó
- **Recomendación:** ✅ MANTENER pero IMPLEMENTAR (es útil para seguridad)

---

## ⚠️ Campos que DEBERÍAN USARSE pero NO

### 1. **`monto_pagado`** - Siempre 0.00

**Para qué sirve:**
- Almacenar el monto que el usuario **dice haber pagado**
- Comparar con el total esperado
- Detectar errores o intentos de fraude

**Ejemplo de uso:**
```php
// Al subir comprobante, pedir al usuario que ingrese el monto pagado
$monto_pagado = floatval($_POST['monto_pagado']);

// Al verificar comprobante, comparar
if (abs($monto_pagado - $total) > 1000) {
    // Advertir al admin: "El monto declarado ($X) no coincide con el total ($Y)"
}
```

**Recomendación:** ⭐ IMPLEMENTAR - Mejora validación y reduce fraudes

### 2. **`hash_comprobante`** - Siempre NULL

**Para qué sirve:**
- Generar un hash único del archivo del comprobante
- Detectar si el mismo comprobante se sube 2 veces
- Prevenir fraude

**Ejemplo de uso:**
```php
// Al subir comprobante
$hash_comprobante = hash_file('sha256', $archivo['tmp_name']);

// Verificar si ya existe
$stmt = $conn->prepare("SELECT COUNT(*) FROM orden WHERE hash_comprobante = ?");
$stmt->bind_param("s", $hash_comprobante);
// ...
if ($ya_existe > 0) {
    die("Este comprobante ya fue utilizado en otra orden");
}
```

**Recomendación:** ⭐ IMPLEMENTAR - Importante para seguridad

---

## ✅ Campos que SÍ se usan correctamente

### Flujo normal de una orden:

1. **Creación (pendiente_verificacion):**
   - `comprobante_pago` ✅
   - `token_verificacion` ✅
   - `comprobante_verificado` = 0 ✅

2. **Admin verifica comprobante:**
   - `comprobante_verificado` = 1 ✅
   - Aún en `pendiente_verificacion` ✅

3. **Admin aprueba orden:**
   - `estado` → 'pagado' ✅
   - `fecha_aprobacion` ✅
   - `codigo_descuento` (si tiene sesión) ✅

4. **Admin marca como enviado:**
   - `estado` → 'enviado' ✅
   - `guia_envio` ✅
   - `transportadora` ✅
   - `fecha_entrega_estimada` ✅

5. **Orden entregada:**
   - `estado` → 'entregado' ✅

### Si se rechaza:

- `estado` → 'rechazado' ✅
- `fecha_aprobacion` (fecha de rechazo) ✅
- `observaciones` (razón) ✅
- `recordatorio_enviado` / `fecha_recordatorio` ✅

---

## 🔧 Resumen de Recomendaciones

### 🔴 Urgente (Bugs):
1. ❌ Corregir INSERT para incluir `nombre_archivo_comprobante`

### 🟡 Mejoras de Seguridad:
2. ⭐ Implementar `hash_comprobante` para detectar comprobantes duplicados
3. ⭐ Implementar `monto_pagado` para mejorar validación

### 🟢 Limpieza:
4. 🗑️ Eliminar `codigo_seguimiento` (nunca se usa y es redundante)

### ✅ Mantener:
- Todos los demás campos están bien y se usan correctamente

---

## 📋 Campos de la Tabla `orden` (Evaluación)

| Campo | ¿Se usa? | ¿Correcto? | Notas |
|-------|----------|------------|-------|
| `id_orden` | ✅ | ✅ | PK |
| `id_usuario` | ✅ | ✅ | NULL si es anónimo |
| `nombre` | ✅ | ✅ | |
| `correo` | ✅ | ✅ | |
| `cedula` | ✅ | ✅ | |
| `celular` | ✅ | ✅ | |
| `departamento` | ✅ | ✅ | |
| `ciudad` | ✅ | ✅ | |
| `direccion` | ✅ | ✅ | |
| `barrio` | ✅ | ✅ | |
| `metodo_pago` | ✅ | ✅ | |
| `costo_envio` | ✅ | ✅ | |
| `fecha` | ✅ | ✅ | |
| `total` | ✅ | ✅ | |
| `estado` | ✅ | ✅ | |
| `fecha_envio` | ✅ | ✅ | Solo cuando se envía |
| `comprobante_pago` | ✅ | ✅ | Ruta completa |
| `nombre_archivo_comprobante` | ✅ | ❌ | **BUG: Queda NULL** |
| `token_verificacion` | ✅ | ✅ | |
| `fecha_aprobacion` | ✅ | ✅ | |
| `observaciones` | ✅ | ✅ | Solo en rechazos |
| `comprobante_verificado` | ✅ | ✅ | |
| `hash_comprobante` | ❌ | ⚠️ | **No implementado, debería** |
| `guia_envio` | ✅ | ✅ | Solo cuando se envía |
| `transportadora` | ✅ | ✅ | Solo cuando se envía |
| `fecha_entrega_estimada` | ✅ | ✅ | Solo cuando se envía |
| `codigo_descuento` | ✅ | ✅ | Al aprobar (con sesión) |
| `codigo_seguimiento` | ❌ | 🗑️ | **Nunca se usa, eliminar** |
| `monto_pagado` | ❌ | ⚠️ | **No implementado, debería** |
| `recordatorio_enviado` | ✅ | ✅ | Órdenes rechazadas |
| `fecha_recordatorio` | ✅ | ✅ | Órdenes rechazadas |

---

**Fecha**: 27 de octubre de 2025  
**Estado**: 📊 Análisis completo

