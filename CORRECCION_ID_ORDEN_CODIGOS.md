# Corrección: id_orden en Códigos Aplicados

## 🐛 Problema Identificado

Cuando un usuario aplicaba un código de descuento a un reloj, el código NO se cargaba automáticamente al refrescar la página, aunque en la BD estaba correctamente marcado como aplicado (`activo = 0`, `id_reloj` presente, `fecha_usado` presente).

### Causa Raíz

El código tenía `id_orden = 30` (la orden que lo generó como regalo), pero la query en `obtener_descuento_aplicado.php` buscaba códigos con `id_orden IS NULL`.

```sql
-- Query anterior (NO funcionaba)
WHERE ucd.activo = 0
  AND ucd.id_orden IS NULL  ← Fallaba aquí
```

### ¿Por qué tenía `id_orden`?

Cuando el admin aprueba una orden, se genera un código de descuento como regalo y se asigna con el `id_orden` de la orden que lo generó. Luego, cuando el usuario aplicaba ese código a un reloj, se marcaba como usado pero **NO se limpiaba el `id_orden`**.

## ✅ Solución Implementada

### 1. Modificado `aplicar_codigo_descuento.php`

Ahora cuando se aplica un código, se **limpia el `id_orden`** (se pone en `NULL`). Solo cuando se **compra** el reloj, se asigna el `id_orden` de la nueva compra.

```php
UPDATE usuario_codigo_descuento 
SET activo = 0,
    fecha_usado = NOW(),
    id_reloj = ?,
    id_orden = NULL  ← NUEVO: Limpiar id_orden al aplicar
WHERE id_usuario = ? 
  AND id_codigo = ?
```

### 2. Script de Corrección para BD

Creado `database/ejecutar_fix_id_orden.php` para arreglar registros existentes que tenían `id_orden` cuando deberían tener `NULL`.

```sql
UPDATE usuario_codigo_descuento 
SET id_orden = NULL
WHERE activo = 0 
  AND id_reloj IS NOT NULL
  AND fecha_usado IS NOT NULL
  AND id_orden IS NOT NULL;
```

### 3. Documentación en `finoso.sql`

Agregados comentarios explicando el flujo de estados:

```
-- Flujo de estados de un código de descuento:
-- 1. ASIGNADO (activo=1, id_reloj=NULL, fecha_usado=NULL, id_orden=NULL o ID de orden que lo generó)
-- 2. APLICADO (activo=0, id_reloj=X, fecha_usado=NOW(), id_orden=NULL) ← Se limpia id_orden al aplicar
-- 3. COMPRADO (activo=0, id_reloj=X, fecha_usado=fecha, id_orden=Y) ← Se asigna id_orden de la compra
```

## 📋 Flujo Correcto Ahora

### Escenario: Código de Regalo

1. **Orden #30 aprobada** → genera código de regalo
   - `id_orden = 30` (la orden que lo generó)
   - `activo = 1` (disponible)
   - `id_reloj = NULL`
   - `fecha_usado = NULL`

2. **Usuario aplica código al reloj #1**
   - `id_orden = NULL` ← **LIMPIADO**
   - `activo = 0` (usado)
   - `id_reloj = 1`
   - `fecha_usado = NOW()`

3. **Usuario compra el reloj #1** (orden #35)
   - `id_orden = 35` ← **Asignado a la nueva compra**
   - `activo = 0`
   - `id_reloj = 1`
   - `fecha_usado` (sin cambios)

### Query para Obtener Descuento Aplicado

```sql
SELECT ucd.*, cd.codigo, cd.porcentaje
FROM usuario_codigo_descuento ucd
JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
WHERE ucd.id_usuario = ? 
  AND ucd.id_reloj = ?
  AND ucd.activo = 0
  AND ucd.id_orden IS NULL  ← Ahora SÍ funciona porque id_orden se limpia al aplicar
```

## 🎯 Resultado

- ✅ Códigos aplicados se cargan correctamente al refrescar
- ✅ Precio con descuento se muestra automáticamente
- ✅ Input de código queda bloqueado con mensaje "Aplicado ✓"
- ✅ No se pueden aplicar más códigos al mismo reloj

## 🐛 Problema Adicional: Precio No Se Mostraba

Después de corregir `id_orden`, el descuento se cargaba correctamente desde BD, pero el precio visual no cambiaba.

### Causa

Cuando un reloj NO tiene descuento propio (`reloj.descuento = 0`), el HTML renderizado por `obtener-reloj.js` era:

```html
<p class="precio-normal">$135.000</p>
```

Pero `actualizarPrecioVisual()` buscaba el elemento `.precio-descuentos` que **no existía**.

### Solución

Modificada la función `actualizarPrecioVisual()` en `informacion.html` para que:

1. **Detecte** si existe `.precio-descuentos`
2. Si **NO existe**, lo **cree dinámicamente**:
   - Crea un `<p class="precio-descuentos">` con el precio final
   - Convierte `<p class="precio-normal">` en `<h4 class="precio-normal">` (tachado)
   - Inserta el nuevo elemento antes del precio tachado
3. Si **SÍ existe**, solo actualiza los valores

Ahora la estructura HTML se transforma dinámicamente de:

```html
<!-- Antes (sin descuento) -->
<p class="precio-normal">$135.000</p>
```

A:

```html
<!-- Después (con descuento del código aplicado) -->
<p class="precio-descuentos">$121.500</p>
<h4 class="precio-normal">$135.000</h4>
```

## 📁 Archivos Modificados

1. `informacion/php/aplicar_codigo_descuento.php` - Limpia `id_orden` al aplicar
2. `informacion/php/obtener_descuento_aplicado.php` - Logs de debug agregados
3. `database/ejecutar_fix_id_orden.php` - Script para corregir registros existentes
4. `database/fix_id_orden_aplicado.sql` - Query SQL para corrección manual
5. `finoso.sql` - Documentación del flujo de estados
6. `informacion/informacion.html` - Reestructuración dinámica del DOM para mostrar precios con descuento

## 🧪 Prueba

```bash
php database/ejecutar_fix_id_orden.php
```

Resultado esperado:
```
✅ Se actualizaron 1 registros
ID Orden: NULL
```

---

**Fecha**: 26 de octubre de 2025  
**Estado**: ✅ Implementado y probado


## 🐛 Problema Identificado

Cuando un usuario aplicaba un código de descuento a un reloj, el código NO se cargaba automáticamente al refrescar la página, aunque en la BD estaba correctamente marcado como aplicado (`activo = 0`, `id_reloj` presente, `fecha_usado` presente).

### Causa Raíz

El código tenía `id_orden = 30` (la orden que lo generó como regalo), pero la query en `obtener_descuento_aplicado.php` buscaba códigos con `id_orden IS NULL`.

```sql
-- Query anterior (NO funcionaba)
WHERE ucd.activo = 0
  AND ucd.id_orden IS NULL  ← Fallaba aquí
```

### ¿Por qué tenía `id_orden`?

Cuando el admin aprueba una orden, se genera un código de descuento como regalo y se asigna con el `id_orden` de la orden que lo generó. Luego, cuando el usuario aplicaba ese código a un reloj, se marcaba como usado pero **NO se limpiaba el `id_orden`**.

## ✅ Solución Implementada

### 1. Modificado `aplicar_codigo_descuento.php`

Ahora cuando se aplica un código, se **limpia el `id_orden`** (se pone en `NULL`). Solo cuando se **compra** el reloj, se asigna el `id_orden` de la nueva compra.

```php
UPDATE usuario_codigo_descuento 
SET activo = 0,
    fecha_usado = NOW(),
    id_reloj = ?,
    id_orden = NULL  ← NUEVO: Limpiar id_orden al aplicar
WHERE id_usuario = ? 
  AND id_codigo = ?
```

### 2. Script de Corrección para BD

Creado `database/ejecutar_fix_id_orden.php` para arreglar registros existentes que tenían `id_orden` cuando deberían tener `NULL`.

```sql
UPDATE usuario_codigo_descuento 
SET id_orden = NULL
WHERE activo = 0 
  AND id_reloj IS NOT NULL
  AND fecha_usado IS NOT NULL
  AND id_orden IS NOT NULL;
```

### 3. Documentación en `finoso.sql`

Agregados comentarios explicando el flujo de estados:

```
-- Flujo de estados de un código de descuento:
-- 1. ASIGNADO (activo=1, id_reloj=NULL, fecha_usado=NULL, id_orden=NULL o ID de orden que lo generó)
-- 2. APLICADO (activo=0, id_reloj=X, fecha_usado=NOW(), id_orden=NULL) ← Se limpia id_orden al aplicar
-- 3. COMPRADO (activo=0, id_reloj=X, fecha_usado=fecha, id_orden=Y) ← Se asigna id_orden de la compra
```

## 📋 Flujo Correcto Ahora

### Escenario: Código de Regalo

1. **Orden #30 aprobada** → genera código de regalo
   - `id_orden = 30` (la orden que lo generó)
   - `activo = 1` (disponible)
   - `id_reloj = NULL`
   - `fecha_usado = NULL`

2. **Usuario aplica código al reloj #1**
   - `id_orden = NULL` ← **LIMPIADO**
   - `activo = 0` (usado)
   - `id_reloj = 1`
   - `fecha_usado = NOW()`

3. **Usuario compra el reloj #1** (orden #35)
   - `id_orden = 35` ← **Asignado a la nueva compra**
   - `activo = 0`
   - `id_reloj = 1`
   - `fecha_usado` (sin cambios)

### Query para Obtener Descuento Aplicado

```sql
SELECT ucd.*, cd.codigo, cd.porcentaje
FROM usuario_codigo_descuento ucd
JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
WHERE ucd.id_usuario = ? 
  AND ucd.id_reloj = ?
  AND ucd.activo = 0
  AND ucd.id_orden IS NULL  ← Ahora SÍ funciona porque id_orden se limpia al aplicar
```

## 🎯 Resultado

- ✅ Códigos aplicados se cargan correctamente al refrescar
- ✅ Precio con descuento se muestra automáticamente
- ✅ Input de código queda bloqueado con mensaje "Aplicado ✓"
- ✅ No se pueden aplicar más códigos al mismo reloj

## 🐛 Problema Adicional: Precio No Se Mostraba

Después de corregir `id_orden`, el descuento se cargaba correctamente desde BD, pero el precio visual no cambiaba.

### Causa

Cuando un reloj NO tiene descuento propio (`reloj.descuento = 0`), el HTML renderizado por `obtener-reloj.js` era:

```html
<p class="precio-normal">$135.000</p>
```

Pero `actualizarPrecioVisual()` buscaba el elemento `.precio-descuentos` que **no existía**.

### Solución

Modificada la función `actualizarPrecioVisual()` en `informacion.html` para que:

1. **Detecte** si existe `.precio-descuentos`
2. Si **NO existe**, lo **cree dinámicamente**:
   - Crea un `<p class="precio-descuentos">` con el precio final
   - Convierte `<p class="precio-normal">` en `<h4 class="precio-normal">` (tachado)
   - Inserta el nuevo elemento antes del precio tachado
3. Si **SÍ existe**, solo actualiza los valores

Ahora la estructura HTML se transforma dinámicamente de:

```html
<!-- Antes (sin descuento) -->
<p class="precio-normal">$135.000</p>
```

A:

```html
<!-- Después (con descuento del código aplicado) -->
<p class="precio-descuentos">$121.500</p>
<h4 class="precio-normal">$135.000</h4>
```

## 📁 Archivos Modificados

1. `informacion/php/aplicar_codigo_descuento.php` - Limpia `id_orden` al aplicar
2. `informacion/php/obtener_descuento_aplicado.php` - Logs de debug agregados
3. `database/ejecutar_fix_id_orden.php` - Script para corregir registros existentes
4. `database/fix_id_orden_aplicado.sql` - Query SQL para corrección manual
5. `finoso.sql` - Documentación del flujo de estados
6. `informacion/informacion.html` - Reestructuración dinámica del DOM para mostrar precios con descuento

## 🧪 Prueba

```bash
php database/ejecutar_fix_id_orden.php
```

Resultado esperado:
```
✅ Se actualizaron 1 registros
ID Orden: NULL
```

---

**Fecha**: 26 de octubre de 2025  
**Estado**: ✅ Implementado y probado

