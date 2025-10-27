# Códigos de Descuento - Flujo Correcto Implementado

## 📅 Fecha de Corrección
27 de octubre de 2025

## 🎯 Problema Identificado

**Flujo INCORRECTO anterior**:
1. Usuario aplica código → Solo guarda en `descuento_aplicado_reloj` (temporal)
2. Usuario compra → Recién ahí marca `activo = 0` en `usuario_codigo_descuento`
3. Usuario recarga página → Código seguía en BD, se recuperaba correctamente
4. ❌ **Problema**: No usaba BD para persistencia, parecía localStorage

**El usuario señaló correctamente**:
> "cuando se aplica un descuento, cambia un campo en BD, activo pasa de 1 a 0, y se aplica con el id del reloj e id del usuario"

## ✅ Flujo CORRECTO Implementado

### **1. Al APLICAR el código** (`informacion/php/aplicar_codigo_descuento.php`)

```php
// Guardar el descuento aplicado
INSERT INTO descuento_aplicado_reloj 
(id_usuario, id_reloj, id_codigo, precio_original, porcentaje_descuento, precio_con_descuento, expira_en) 
VALUES (?, ?, ?, ?, ?, ?, ?)

// 🔥 MARCAR CÓDIGO COMO USADO INMEDIATAMENTE
UPDATE usuario_codigo_descuento 
SET activo = 0,
    fecha_usado = NOW()
WHERE id_usuario = ? AND id_codigo = ?
```

**Resultado**: El código queda **USADO** en BD desde el momento que se aplica.

### **2. Si intenta aplicar de nuevo** (mismo usuario)

La validación en `aplicar_codigo_descuento.php` (líneas 84-89):
```php
if (!$codigo_data['activo'] || $codigo_data['fecha_usado']) {
    $response['mensaje'] = 'Ya utilizaste este código anteriormente.';
    exit;
}
```

**Resultado**: ❌ Error "Ya utilizaste este código anteriormente"

### **3. Si intenta aplicar otro usuario**

La validación en `aplicar_codigo_descuento.php` (líneas 77-82):
```php
if (!$codigo_data['id_usuario_codigo']) {
    $response['mensaje'] = 'Este código no está asignado a tu cuenta.';
    exit;
}
```

**Resultado**: ❌ Error "Este código no está asignado a tu cuenta" (parece que no existe)

### **4. Al COMPLETAR la compra**

Ya no se marca como usado (porque ya lo está), solo se vincula con la orden:

**Nequi** (`informacion/php/subir_comprobante.php`):
```php
// Solo actualizar el id_orden para tener referencia
UPDATE usuario_codigo_descuento 
SET id_orden = ?
WHERE id_usuario = ? AND id_codigo = ?
```

**Wompi** (`informacion/php/wompi_webhook.php`):
```php
// Igual, solo actualizar id_orden
UPDATE usuario_codigo_descuento 
SET id_orden = ?
WHERE id_usuario = ? AND id_codigo = ?
```

### **5. Al RECARGAR la página**

`obtener_descuento_aplicado.php` busca en BD:
```php
SELECT dar.*, cd.codigo
FROM descuento_aplicado_reloj dar
JOIN codigo_descuento cd ON dar.id_codigo = cd.id_codigo
WHERE dar.id_usuario = ? 
  AND dar.id_reloj = ?
  AND (dar.expira_en IS NULL OR dar.expira_en > NOW())
  AND dar.usado_en_orden IS NULL
```

**Resultado**: ✅ Recupera el descuento desde BD (no localStorage)

### **6. En el PERFIL**

`obtener_codigos_usuario.php` consulta:
```php
SELECT ucd.*, cd.*
FROM usuario_codigo_descuento ucd
WHERE ucd.id_usuario = ?
```

Determina el estado:
```php
if ($row['fecha_usado'] || !$row['activo']) {
    $estado = 'usado';  // ✅ Aparece como USADO
}
```

**Frontend** (`perfil/js/perfil.js`):
```javascript
const botonCopiar = codigo.estado !== 'expirado' && codigo.estado !== 'usado' 
    ? `<button>Copiar</button>`  // Solo si disponible
    : '';  // ❌ No muestra botón si está usado
```

## 📊 Estados de la Base de Datos

### Tabla `usuario_codigo_descuento`:

| Momento | activo | fecha_usado | id_orden |
|---------|--------|-------------|----------|
| **Asignado** | 1 | NULL | NULL |
| **Aplicado** | 0 | 2025-10-27 | NULL |
| **Comprado** | 0 | 2025-10-27 | 28 |

### Tabla `descuento_aplicado_reloj`:

| Momento | id_usuario | id_reloj | id_codigo | usado_en_orden |
|---------|------------|----------|-----------|----------------|
| **Aplicado** | 7 | 123 | 18 | NULL |
| **Comprado** | 7 | 123 | 18 | 28 |

## 🎯 Diferencia Clave

### ANTES (Incorrecto):
```
Aplicar → descuento_aplicado_reloj (temporal)
          ↓
        Guardar en localStorage? ❌ NO
          ↓
Comprar → activo = 0 en usuario_codigo_descuento
```

### AHORA (Correcto):
```
Aplicar → descuento_aplicado_reloj (temporal)
          + activo = 0 en usuario_codigo_descuento ✅
          + fecha_usado = NOW()
          ↓
Recargar → Lee desde BD (descuento_aplicado_reloj) ✅
          ↓
Perfil → Muestra como "Usado" (activo = 0) ✅
          + NO muestra botón "Copiar"
          ↓
Comprar → Solo vincula id_orden
```

## ✅ Validaciones Completas

| Escenario | Resultado |
|-----------|-----------|
| Usuario aplica código por primera vez | ✅ Se aplica y marca como usado |
| Usuario recarga página | ✅ Descuento se mantiene (BD) |
| Usuario intenta aplicar de nuevo | ❌ "Ya utilizaste este código" |
| Usuario intenta aplicar en otro reloj | ❌ "Ya utilizaste este código" |
| Otro usuario intenta aplicar mismo código | ❌ "No está asignado a tu cuenta" |
| Usuario ve su perfil | ✅ Código aparece como "Usado" |
| Usuario intenta copiar código usado | ❌ No hay botón "Copiar" |

## 🎉 Estado Final

✅ **TODO SE GUARDA EN BASE DE DATOS**
- No se usa localStorage para nada
- El código se marca como usado INMEDIATAMENTE al aplicarlo
- La persistencia es 100% en BD (`descuento_aplicado_reloj` + `usuario_codigo_descuento`)
- El perfil muestra correctamente el estado del código
- No se puede reutilizar el código en ningún caso

## 📝 Archivos Modificados (Corrección Final)

### 1. `informacion/php/aplicar_codigo_descuento.php`
**Línea 142-158**: Agregado UPDATE que marca `activo = 0` y `fecha_usado = NOW()` **EN EL MOMENTO DE APLICAR**

### 2. `informacion/php/subir_comprobante.php`
**Línea 450-489**: Cambiado de "marcar como usado" a "vincular orden" (ya está usado)

### 3. `informacion/php/wompi_webhook.php`
**Línea 102-145**: Cambiado de "marcar como usado" a "vincular orden" (ya está usado)

## 🔐 Seguridad

- El código solo se puede aplicar UNA vez
- Solo el usuario al que está asignado puede usarlo
- Otros usuarios no pueden saber que el código existe
- Todo se valida en el servidor (PHP/BD)
- No hay manipulación posible desde el cliente


## 📅 Fecha de Corrección
27 de octubre de 2025

## 🎯 Problema Identificado

**Flujo INCORRECTO anterior**:
1. Usuario aplica código → Solo guarda en `descuento_aplicado_reloj` (temporal)
2. Usuario compra → Recién ahí marca `activo = 0` en `usuario_codigo_descuento`
3. Usuario recarga página → Código seguía en BD, se recuperaba correctamente
4. ❌ **Problema**: No usaba BD para persistencia, parecía localStorage

**El usuario señaló correctamente**:
> "cuando se aplica un descuento, cambia un campo en BD, activo pasa de 1 a 0, y se aplica con el id del reloj e id del usuario"

## ✅ Flujo CORRECTO Implementado

### **1. Al APLICAR el código** (`informacion/php/aplicar_codigo_descuento.php`)

```php
// Guardar el descuento aplicado
INSERT INTO descuento_aplicado_reloj 
(id_usuario, id_reloj, id_codigo, precio_original, porcentaje_descuento, precio_con_descuento, expira_en) 
VALUES (?, ?, ?, ?, ?, ?, ?)

// 🔥 MARCAR CÓDIGO COMO USADO INMEDIATAMENTE
UPDATE usuario_codigo_descuento 
SET activo = 0,
    fecha_usado = NOW()
WHERE id_usuario = ? AND id_codigo = ?
```

**Resultado**: El código queda **USADO** en BD desde el momento que se aplica.

### **2. Si intenta aplicar de nuevo** (mismo usuario)

La validación en `aplicar_codigo_descuento.php` (líneas 84-89):
```php
if (!$codigo_data['activo'] || $codigo_data['fecha_usado']) {
    $response['mensaje'] = 'Ya utilizaste este código anteriormente.';
    exit;
}
```

**Resultado**: ❌ Error "Ya utilizaste este código anteriormente"

### **3. Si intenta aplicar otro usuario**

La validación en `aplicar_codigo_descuento.php` (líneas 77-82):
```php
if (!$codigo_data['id_usuario_codigo']) {
    $response['mensaje'] = 'Este código no está asignado a tu cuenta.';
    exit;
}
```

**Resultado**: ❌ Error "Este código no está asignado a tu cuenta" (parece que no existe)

### **4. Al COMPLETAR la compra**

Ya no se marca como usado (porque ya lo está), solo se vincula con la orden:

**Nequi** (`informacion/php/subir_comprobante.php`):
```php
// Solo actualizar el id_orden para tener referencia
UPDATE usuario_codigo_descuento 
SET id_orden = ?
WHERE id_usuario = ? AND id_codigo = ?
```

**Wompi** (`informacion/php/wompi_webhook.php`):
```php
// Igual, solo actualizar id_orden
UPDATE usuario_codigo_descuento 
SET id_orden = ?
WHERE id_usuario = ? AND id_codigo = ?
```

### **5. Al RECARGAR la página**

`obtener_descuento_aplicado.php` busca en BD:
```php
SELECT dar.*, cd.codigo
FROM descuento_aplicado_reloj dar
JOIN codigo_descuento cd ON dar.id_codigo = cd.id_codigo
WHERE dar.id_usuario = ? 
  AND dar.id_reloj = ?
  AND (dar.expira_en IS NULL OR dar.expira_en > NOW())
  AND dar.usado_en_orden IS NULL
```

**Resultado**: ✅ Recupera el descuento desde BD (no localStorage)

### **6. En el PERFIL**

`obtener_codigos_usuario.php` consulta:
```php
SELECT ucd.*, cd.*
FROM usuario_codigo_descuento ucd
WHERE ucd.id_usuario = ?
```

Determina el estado:
```php
if ($row['fecha_usado'] || !$row['activo']) {
    $estado = 'usado';  // ✅ Aparece como USADO
}
```

**Frontend** (`perfil/js/perfil.js`):
```javascript
const botonCopiar = codigo.estado !== 'expirado' && codigo.estado !== 'usado' 
    ? `<button>Copiar</button>`  // Solo si disponible
    : '';  // ❌ No muestra botón si está usado
```

## 📊 Estados de la Base de Datos

### Tabla `usuario_codigo_descuento`:

| Momento | activo | fecha_usado | id_orden |
|---------|--------|-------------|----------|
| **Asignado** | 1 | NULL | NULL |
| **Aplicado** | 0 | 2025-10-27 | NULL |
| **Comprado** | 0 | 2025-10-27 | 28 |

### Tabla `descuento_aplicado_reloj`:

| Momento | id_usuario | id_reloj | id_codigo | usado_en_orden |
|---------|------------|----------|-----------|----------------|
| **Aplicado** | 7 | 123 | 18 | NULL |
| **Comprado** | 7 | 123 | 18 | 28 |

## 🎯 Diferencia Clave

### ANTES (Incorrecto):
```
Aplicar → descuento_aplicado_reloj (temporal)
          ↓
        Guardar en localStorage? ❌ NO
          ↓
Comprar → activo = 0 en usuario_codigo_descuento
```

### AHORA (Correcto):
```
Aplicar → descuento_aplicado_reloj (temporal)
          + activo = 0 en usuario_codigo_descuento ✅
          + fecha_usado = NOW()
          ↓
Recargar → Lee desde BD (descuento_aplicado_reloj) ✅
          ↓
Perfil → Muestra como "Usado" (activo = 0) ✅
          + NO muestra botón "Copiar"
          ↓
Comprar → Solo vincula id_orden
```

## ✅ Validaciones Completas

| Escenario | Resultado |
|-----------|-----------|
| Usuario aplica código por primera vez | ✅ Se aplica y marca como usado |
| Usuario recarga página | ✅ Descuento se mantiene (BD) |
| Usuario intenta aplicar de nuevo | ❌ "Ya utilizaste este código" |
| Usuario intenta aplicar en otro reloj | ❌ "Ya utilizaste este código" |
| Otro usuario intenta aplicar mismo código | ❌ "No está asignado a tu cuenta" |
| Usuario ve su perfil | ✅ Código aparece como "Usado" |
| Usuario intenta copiar código usado | ❌ No hay botón "Copiar" |

## 🎉 Estado Final

✅ **TODO SE GUARDA EN BASE DE DATOS**
- No se usa localStorage para nada
- El código se marca como usado INMEDIATAMENTE al aplicarlo
- La persistencia es 100% en BD (`descuento_aplicado_reloj` + `usuario_codigo_descuento`)
- El perfil muestra correctamente el estado del código
- No se puede reutilizar el código en ningún caso

## 📝 Archivos Modificados (Corrección Final)

### 1. `informacion/php/aplicar_codigo_descuento.php`
**Línea 142-158**: Agregado UPDATE que marca `activo = 0` y `fecha_usado = NOW()` **EN EL MOMENTO DE APLICAR**

### 2. `informacion/php/subir_comprobante.php`
**Línea 450-489**: Cambiado de "marcar como usado" a "vincular orden" (ya está usado)

### 3. `informacion/php/wompi_webhook.php`
**Línea 102-145**: Cambiado de "marcar como usado" a "vincular orden" (ya está usado)

## 🔐 Seguridad

- El código solo se puede aplicar UNA vez
- Solo el usuario al que está asignado puede usarlo
- Otros usuarios no pueden saber que el código existe
- Todo se valida en el servidor (PHP/BD)
- No hay manipulación posible desde el cliente

