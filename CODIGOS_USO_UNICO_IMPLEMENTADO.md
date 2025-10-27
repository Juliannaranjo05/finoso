# Códigos de Descuento - Uso Único Implementado

## 📅 Fecha de Implementación
27 de octubre de 2025

## 🎯 Problema Resuelto
Cuando un usuario aplicaba y usaba un código de descuento en una compra:
- El código se marcaba parcialmente (solo `id_orden` se actualizaba)
- Los campos `activo`, `fecha_usado` no se actualizaban correctamente
- El código seguía apareciendo como "Disponible" en el perfil
- El usuario podía copiar y reutilizar el código
- El campo `veces_usado` era innecesario si solo se puede usar una vez

## ✅ Cambios Implementados

### 1. **Simplificación de la tabla `usuario_codigo_descuento`**
Se eliminó el uso del campo `veces_usado` ya que los códigos solo se pueden usar **una vez**.

Los campos relevantes ahora son:
- `activo`: 1 = disponible, 0 = usado
- `fecha_usado`: NULL = no usado, fecha = usado
- `id_orden`: NULL = no usado, ID = orden donde se usó

### 2. **Actualización en Nequi Individual** (`informacion/php/subir_comprobante.php`)

**Antes**:
```php
UPDATE usuario_codigo_descuento 
SET fecha_usado = NOW(), 
    id_orden = ?, 
    veces_usado = veces_usado + 1,  // ❌ Innecesario
    activo = 0
WHERE id_usuario = ? AND id_codigo = ?
```

**Ahora**:
```php
UPDATE usuario_codigo_descuento 
SET fecha_usado = NOW(), 
    id_orden = ?,
    activo = 0
WHERE id_usuario = ? 
  AND id_codigo = ? 
  AND activo = 1
```

También se agregó:
```php
UPDATE descuento_aplicado_reloj 
SET usado_en_orden = ?
WHERE id_usuario = ? AND id_codigo = ?
```

### 3. **Nuevo: Actualización en Wompi** (`informacion/php/wompi_webhook.php`)

Se agregó la lógica para marcar el código como usado cuando se aprueba un pago por Wompi:

```php
// 🎟️ MARCAR CÓDIGO DE DESCUENTO COMO USADO
$stmt_codigo_check = $conn->prepare("
    SELECT dar.id_usuario, dar.id_codigo 
    FROM descuento_aplicado_reloj dar
    JOIN orden_detalle od ON dar.id_reloj = od.id_reloj
    WHERE od.id_orden = ? 
      AND dar.usado_en_orden IS NULL
    LIMIT 1
");

// Si encuentra un código, marcarlo como usado
UPDATE usuario_codigo_descuento 
SET fecha_usado = NOW(), 
    id_orden = ?,
    activo = 0
WHERE id_usuario = ? AND id_codigo = ?

UPDATE descuento_aplicado_reloj 
SET usado_en_orden = ?
WHERE id_usuario = ? AND id_codigo = ?
```

### 4. **Lógica de Estado Simplificada** (`perfil/php/obtener_codigos_usuario.php`)

**Antes**: La lógica era confusa con múltiples verificaciones que se sobrescribían.

**Ahora**: Lógica clara y priorizada:
```php
// 1. Verificar si fue usado (fecha_usado existe O activo = 0)
if ($row['fecha_usado'] || !$row['activo']) {
    $estado = 'usado';
}
// 2. Verificar si expiró (solo si no fue usado)
else if ($row['fecha_expiracion']) {
    if ($row['fecha_expiracion'] < $fecha_actual) {
        $estado = 'expirado';
    }
}
// 3. Si ninguno de los anteriores
else {
    $estado = 'disponible';
}
```

**Eliminado de la consulta**: `veces_usado`

### 5. **Interfaz del Perfil** (`perfil/js/perfil.js`)

**Eliminado**: Mostrar "Veces usado"
```javascript
// ❌ ELIMINADO
${codigo.veces_usado > 0 ? `
<div class="codigo-detalle-row">
    <span class="codigo-detalle-label">Veces usado:</span>
    <span class="codigo-detalle-valor">${codigo.veces_usado}</span>
</div>
` : ''}
```

**Mantenido**: El botón "Copiar" solo se muestra si el código NO está usado ni expirado:
```javascript
const botonCopiar = codigo.estado !== 'expirado' && codigo.estado !== 'usado' 
    ? `<button class="codigo-copiar" data-codigo="${codigo.codigo}">
            <svg>...</svg>
            Copiar
        </button>`
    : '';
```

## 📋 Archivos Modificados

### 1. `informacion/php/subir_comprobante.php`
- **Línea 459-466**: Eliminado `veces_usado` del UPDATE
- **Línea 487-498**: Agregado UPDATE a `descuento_aplicado_reloj`

### 2. `informacion/php/wompi_webhook.php`
- **Línea 102-148**: Agregada lógica completa para marcar código como usado en pagos Wompi

### 3. `perfil/php/obtener_codigos_usuario.php`
- **Línea 31**: Eliminado `ucd.veces_usado` de la consulta SELECT
- **Línea 67-89**: Simplificada lógica de determinación de estado
- **Línea 99**: Eliminado `veces_usado` del array de retorno

### 4. `perfil/js/perfil.js`
- **Línea 471-476**: Eliminado bloque que mostraba "Veces usado"

## 🔄 Flujo Completo

### **Aplicar código**:
1. Usuario aplica código en página individual → Guarda en `descuento_aplicado_reloj`
2. Código queda vinculado al reloj con el usuario

### **Usar código en Nequi**:
1. Usuario completa compra con Nequi → Orden se crea
2. Se actualiza `usuario_codigo_descuento`: 
   - `fecha_usado = NOW()`
   - `id_orden = #`
   - `activo = 0`
3. Se actualiza `descuento_aplicado_reloj`:
   - `usado_en_orden = #`

### **Usar código en Wompi**:
1. Usuario paga con Wompi → Webhook recibe confirmación
2. Si estado = 'APPROVED':
   - Busca si hay código aplicado en `descuento_aplicado_reloj`
   - Actualiza `usuario_codigo_descuento` (igual que Nequi)
   - Actualiza `descuento_aplicado_reloj` (igual que Nequi)

### **Ver en perfil**:
1. PHP determina estado:
   - Si `fecha_usado != NULL` O `activo = 0` → **Usado** ❌
   - Si no usado y expiró → **Expirado** ⏰
   - Si no usado y no expiró → **Disponible** ✅
2. JavaScript muestra:
   - Badge: "Usado", "Expirado" o "Disponible"
   - Botón "Copiar": Solo si estado = "Disponible"
   - Fecha de uso: Solo si fue usado

## ✅ Resultado Final

### Antes ❌
- Código con `id_orden = 28` pero `activo = 1` y `fecha_usado = NULL`
- Aparece como "Disponible" en el perfil
- Usuario puede copiar y reutilizar el código

### Ahora ✅
- Cuando se usa el código:
  - `activo = 0`
  - `fecha_usado = 2025-10-27 XX:XX:XX`
  - `id_orden = 28`
- Aparece como "**Usado**" en el perfil con badge rojo
- **NO** se muestra botón "Copiar"
- Muestra fecha de uso: "Usado el: 27 de octubre de 2025"
- Campo `veces_usado` ya no se usa ni muestra

## 🎯 Estados Posibles del Código

| Estado | Condición | Badge | Botón Copiar | Color |
|--------|-----------|-------|--------------|-------|
| **Disponible** | `activo = 1` y no expirado | "Disponible" | ✅ Sí | Verde |
| **Usado** | `fecha_usado != NULL` o `activo = 0` | "Usado" | ❌ No | Rojo |
| **Expirado** | `fecha_expiracion < HOY` y no usado | "Expirado" | ❌ No | Gris |

## 🎉 Estado
✅ **IMPLEMENTADO Y FUNCIONANDO**
- Códigos se marcan correctamente como usados
- Funciona en Nequi y Wompi
- Campo `veces_usado` ya no se usa
- Interfaz del perfil muestra correctamente el estado
- Botón "Copiar" solo para códigos disponibles


## 📅 Fecha de Implementación
27 de octubre de 2025

## 🎯 Problema Resuelto
Cuando un usuario aplicaba y usaba un código de descuento en una compra:
- El código se marcaba parcialmente (solo `id_orden` se actualizaba)
- Los campos `activo`, `fecha_usado` no se actualizaban correctamente
- El código seguía apareciendo como "Disponible" en el perfil
- El usuario podía copiar y reutilizar el código
- El campo `veces_usado` era innecesario si solo se puede usar una vez

## ✅ Cambios Implementados

### 1. **Simplificación de la tabla `usuario_codigo_descuento`**
Se eliminó el uso del campo `veces_usado` ya que los códigos solo se pueden usar **una vez**.

Los campos relevantes ahora son:
- `activo`: 1 = disponible, 0 = usado
- `fecha_usado`: NULL = no usado, fecha = usado
- `id_orden`: NULL = no usado, ID = orden donde se usó

### 2. **Actualización en Nequi Individual** (`informacion/php/subir_comprobante.php`)

**Antes**:
```php
UPDATE usuario_codigo_descuento 
SET fecha_usado = NOW(), 
    id_orden = ?, 
    veces_usado = veces_usado + 1,  // ❌ Innecesario
    activo = 0
WHERE id_usuario = ? AND id_codigo = ?
```

**Ahora**:
```php
UPDATE usuario_codigo_descuento 
SET fecha_usado = NOW(), 
    id_orden = ?,
    activo = 0
WHERE id_usuario = ? 
  AND id_codigo = ? 
  AND activo = 1
```

También se agregó:
```php
UPDATE descuento_aplicado_reloj 
SET usado_en_orden = ?
WHERE id_usuario = ? AND id_codigo = ?
```

### 3. **Nuevo: Actualización en Wompi** (`informacion/php/wompi_webhook.php`)

Se agregó la lógica para marcar el código como usado cuando se aprueba un pago por Wompi:

```php
// 🎟️ MARCAR CÓDIGO DE DESCUENTO COMO USADO
$stmt_codigo_check = $conn->prepare("
    SELECT dar.id_usuario, dar.id_codigo 
    FROM descuento_aplicado_reloj dar
    JOIN orden_detalle od ON dar.id_reloj = od.id_reloj
    WHERE od.id_orden = ? 
      AND dar.usado_en_orden IS NULL
    LIMIT 1
");

// Si encuentra un código, marcarlo como usado
UPDATE usuario_codigo_descuento 
SET fecha_usado = NOW(), 
    id_orden = ?,
    activo = 0
WHERE id_usuario = ? AND id_codigo = ?

UPDATE descuento_aplicado_reloj 
SET usado_en_orden = ?
WHERE id_usuario = ? AND id_codigo = ?
```

### 4. **Lógica de Estado Simplificada** (`perfil/php/obtener_codigos_usuario.php`)

**Antes**: La lógica era confusa con múltiples verificaciones que se sobrescribían.

**Ahora**: Lógica clara y priorizada:
```php
// 1. Verificar si fue usado (fecha_usado existe O activo = 0)
if ($row['fecha_usado'] || !$row['activo']) {
    $estado = 'usado';
}
// 2. Verificar si expiró (solo si no fue usado)
else if ($row['fecha_expiracion']) {
    if ($row['fecha_expiracion'] < $fecha_actual) {
        $estado = 'expirado';
    }
}
// 3. Si ninguno de los anteriores
else {
    $estado = 'disponible';
}
```

**Eliminado de la consulta**: `veces_usado`

### 5. **Interfaz del Perfil** (`perfil/js/perfil.js`)

**Eliminado**: Mostrar "Veces usado"
```javascript
// ❌ ELIMINADO
${codigo.veces_usado > 0 ? `
<div class="codigo-detalle-row">
    <span class="codigo-detalle-label">Veces usado:</span>
    <span class="codigo-detalle-valor">${codigo.veces_usado}</span>
</div>
` : ''}
```

**Mantenido**: El botón "Copiar" solo se muestra si el código NO está usado ni expirado:
```javascript
const botonCopiar = codigo.estado !== 'expirado' && codigo.estado !== 'usado' 
    ? `<button class="codigo-copiar" data-codigo="${codigo.codigo}">
            <svg>...</svg>
            Copiar
        </button>`
    : '';
```

## 📋 Archivos Modificados

### 1. `informacion/php/subir_comprobante.php`
- **Línea 459-466**: Eliminado `veces_usado` del UPDATE
- **Línea 487-498**: Agregado UPDATE a `descuento_aplicado_reloj`

### 2. `informacion/php/wompi_webhook.php`
- **Línea 102-148**: Agregada lógica completa para marcar código como usado en pagos Wompi

### 3. `perfil/php/obtener_codigos_usuario.php`
- **Línea 31**: Eliminado `ucd.veces_usado` de la consulta SELECT
- **Línea 67-89**: Simplificada lógica de determinación de estado
- **Línea 99**: Eliminado `veces_usado` del array de retorno

### 4. `perfil/js/perfil.js`
- **Línea 471-476**: Eliminado bloque que mostraba "Veces usado"

## 🔄 Flujo Completo

### **Aplicar código**:
1. Usuario aplica código en página individual → Guarda en `descuento_aplicado_reloj`
2. Código queda vinculado al reloj con el usuario

### **Usar código en Nequi**:
1. Usuario completa compra con Nequi → Orden se crea
2. Se actualiza `usuario_codigo_descuento`: 
   - `fecha_usado = NOW()`
   - `id_orden = #`
   - `activo = 0`
3. Se actualiza `descuento_aplicado_reloj`:
   - `usado_en_orden = #`

### **Usar código en Wompi**:
1. Usuario paga con Wompi → Webhook recibe confirmación
2. Si estado = 'APPROVED':
   - Busca si hay código aplicado en `descuento_aplicado_reloj`
   - Actualiza `usuario_codigo_descuento` (igual que Nequi)
   - Actualiza `descuento_aplicado_reloj` (igual que Nequi)

### **Ver en perfil**:
1. PHP determina estado:
   - Si `fecha_usado != NULL` O `activo = 0` → **Usado** ❌
   - Si no usado y expiró → **Expirado** ⏰
   - Si no usado y no expiró → **Disponible** ✅
2. JavaScript muestra:
   - Badge: "Usado", "Expirado" o "Disponible"
   - Botón "Copiar": Solo si estado = "Disponible"
   - Fecha de uso: Solo si fue usado

## ✅ Resultado Final

### Antes ❌
- Código con `id_orden = 28` pero `activo = 1` y `fecha_usado = NULL`
- Aparece como "Disponible" en el perfil
- Usuario puede copiar y reutilizar el código

### Ahora ✅
- Cuando se usa el código:
  - `activo = 0`
  - `fecha_usado = 2025-10-27 XX:XX:XX`
  - `id_orden = 28`
- Aparece como "**Usado**" en el perfil con badge rojo
- **NO** se muestra botón "Copiar"
- Muestra fecha de uso: "Usado el: 27 de octubre de 2025"
- Campo `veces_usado` ya no se usa ni muestra

## 🎯 Estados Posibles del Código

| Estado | Condición | Badge | Botón Copiar | Color |
|--------|-----------|-------|--------------|-------|
| **Disponible** | `activo = 1` y no expirado | "Disponible" | ✅ Sí | Verde |
| **Usado** | `fecha_usado != NULL` o `activo = 0` | "Usado" | ❌ No | Rojo |
| **Expirado** | `fecha_expiracion < HOY` y no usado | "Expirado" | ❌ No | Gris |

## 🎉 Estado
✅ **IMPLEMENTADO Y FUNCIONANDO**
- Códigos se marcan correctamente como usados
- Funciona en Nequi y Wompi
- Campo `veces_usado` ya no se usa
- Interfaz del perfil muestra correctamente el estado
- Botón "Copiar" solo para códigos disponibles

