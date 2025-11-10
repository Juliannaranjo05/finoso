# Limpieza de Base de Datos - Campos y Tablas Innecesarias

## 🧹 Resumen de Limpieza

Se eliminaron **4 elementos** innecesarios de la base de datos y el código.

---

## 1. ❌ Campo `cantidad` de tabla `orden_detalle`

### Antes:
```sql
CREATE TABLE `orden_detalle` (
  `id_orden_detalle` int(11) NOT NULL,
  `id_orden` int(11) DEFAULT NULL,
  `id_reloj` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,  ← ELIMINADO
  `precio_unitario` decimal(10,2) DEFAULT NULL
);
```

### Ahora:
```sql
CREATE TABLE `orden_detalle` (
  `id_orden_detalle` int(11) NOT NULL,
  `id_orden` int(11) DEFAULT NULL,
  `id_reloj` int(11) DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL
);
```

### ¿Por qué se eliminó?

- **NO se usa** en ningún archivo PHP
- Cada reloj vendido se registra como una fila independiente en `orden_detalle`
- Si una orden tiene 3 relojes → 3 registros en `orden_detalle`
- El campo `cantidad` siempre sería `1` o estaría vacío
- **NO existe** flujo de "comprar 2 unidades del mismo reloj"

**Conclusión:** Campo redundante e innecesario en el modelo actual.

---

## 2. ❌ Tabla `auditoria_pagos` (Completa)

### Estado Anterior:

**Tabla creada dinámicamente en:**
`informacion/php/subir_comprobante.php`

```php
$sql_auditoria = "CREATE TABLE IF NOT EXISTS auditoria_pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timestamp_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_cliente VARCHAR(45),
    user_agent TEXT,
    referer TEXT,
    monto_esperado DECIMAL(10,2),
    monto_recibido DECIMAL(10,2),
    hash_archivo VARCHAR(64),
    estado VARCHAR(20),
    token_verificacion VARCHAR(64),
    id_orden INT,
    observaciones TEXT
)";
$conn->query($sql_auditoria);

// Pero el INSERT estaba COMENTADO:
/*
$stmt_audit = $conn->prepare("INSERT INTO auditoria_pagos ...");
...
*/

// Y el UPDATE nunca funcionaba (sin $id_auditoria):
$stmt_audit_update = $conn->prepare("UPDATE auditoria_pagos ...");
```

### ¿Por qué existía?

Era un **experimento de auditoría de pagos** para registrar:
- IP del cliente
- User Agent (navegador)
- Monto esperado vs recibido
- Hash del comprobante
- Token de verificación

### ¿Por qué se eliminó?

1. **Código comentado** desde el inicio (nunca se activó)
2. **Tabla vacía** (se creaba pero nunca se insertaba nada)
3. **NO está en `finoso.sql`** (no es parte del esquema oficial)
4. **Sistema actual es suficiente:**
   - `orden.comprobante_pago` → guarda el archivo
   - `orden.token_verificacion` → token único
   - `orden.monto_pagado` → monto declarado
   - Logs de Apache/PHP → IPs y User Agents

### Archivos modificados:

#### ✅ `informacion/php/subir_comprobante.php`
**Eliminado:**
- Líneas 144-176: Creación de tabla y código comentado de INSERT
- Líneas 459-464: UPDATE a tabla inexistente

**Antes:**
```php
// 7. Registrar intento de pago en auditoría
$ip_cliente = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
// ... 30 líneas de código comentado
```

**Ahora:**
```php
// 6. Generar token de verificación único para esta transacción
$token_verificacion = bin2hex(random_bytes(32));

// Verificar archivo de comprobante
```

#### ✅ `admin/debug_bd.php`
**Eliminado:**
```php
$tablas = [
    'auditoria_pagos',  ← ELIMINADO
    'carrito',
    // ...
];
```

**Agregado:**
```php
$tablas = [
    'carrito',
    // ...
    'usuario_codigo_descuento'  ← AGREGADO (estaba faltando)
];
```

---

## 3. 🔧 Campo `cantidad` - Eliminación de la BD Actual

### Script SQL creado:
`database/eliminar_auditoria_pagos.sql`
```sql
DROP TABLE IF EXISTS auditoria_pagos;
```

### Script PHP ejecutor:
`database/ejecutar_eliminar_auditoria.php`

**Funcionalidad:**
1. Verifica si la tabla `auditoria_pagos` existe
2. Si existe → la elimina
3. Si no existe → informa que no hay nada que hacer
4. Muestra resumen de la operación

**Para ejecutar:**
```
https://finoso.store/database/ejecutar_eliminar_auditoria.php
```

---

## 📊 Comparación General

### Antes de la Limpieza:

**`finoso.sql`:**
- Tabla `orden`: 32 campos (3 sin uso)
- Tabla `orden_detalle`: 5 campos (1 sin uso)
- Tabla `auditoria_pagos`: NO en finoso.sql

**Código PHP:**
- `informacion/php/subir_comprobante.php`: ~40 líneas de código comentado
- `admin/debug_bd.php`: referencia a tabla fantasma

**Base de Datos Actual:**
- Tabla `auditoria_pagos`: vacía pero existe

### Después de la Limpieza:

**`finoso.sql`:**
- Tabla `orden`: 29 campos ✅ (todos en uso)
- Tabla `orden_detalle`: 4 campos ✅ (todos en uso)
- Tabla `auditoria_pagos`: ❌ Nunca se creará

**Código PHP:**
- `informacion/php/subir_comprobante.php`: Código limpio, sin experimentos comentados
- `admin/debug_bd.php`: Solo tablas reales + incluye `usuario_codigo_descuento`

**Base de Datos Actual:**
- Tabla `auditoria_pagos`: ⏳ Pendiente de eliminar con script

---

## ✅ Beneficios de la Limpieza

### 1. Esquema más Limpio
- Sin campos huérfanos
- Sin tablas experimentales
- Código más legible

### 2. Mejor Rendimiento
- Menos campos en consultas JOIN
- Menos índices innecesarios
- Queries más eficientes

### 3. Mantenimiento más Fácil
- Código sin confusión
- Desarrolladores futuros no se preguntarán "¿para qué es esto?"
- Documentación más clara

### 4. Auditoría Clara
- El sistema actual ya registra lo necesario:
  - `orden.comprobante_pago` → archivo del comprobante
  - `orden.token_verificacion` → identificador único
  - `orden.monto_pagado` → monto declarado por el cliente
  - `orden.fecha` → timestamp de la transacción

---

## 📝 Resumen de Archivos Modificados

### Esquema (`finoso.sql`):
1. ✅ Tabla `orden_detalle` → Campo `cantidad` eliminado

### PHP (2 archivos):
2. ✅ `informacion/php/subir_comprobante.php` → Código de `auditoria_pagos` eliminado
3. ✅ `admin/debug_bd.php` → Tabla `auditoria_pagos` eliminada de la lista

### Scripts de BD (2 nuevos):
4. 📄 `database/eliminar_auditoria_pagos.sql` → SQL para DROP TABLE
5. 📄 `database/ejecutar_eliminar_auditoria.php` → Ejecutor con validaciones

### Documentación (1 nuevo):
6. 📄 `LIMPIEZA_BD_CAMPOS_INNECESARIOS.md` → Este archivo

**Total: 6 archivos modificados/creados**

---

## 🧪 Pruebas Recomendadas

### Test 1: Verificar INSERT en orden_detalle
1. Realizar una compra (individual, carrito o favoritos)
2. Verificar que `orden_detalle` se inserte correctamente
3. Confirmar que **NO se use** el campo `cantidad` en ningún lado

### Test 2: Verificar que no haya errores
1. Realizar compra individual con Nequi
2. Verificar que NO haya errores de `$id_auditoria` indefinida
3. Confirmar que la orden se cree correctamente

### Test 3: Ejecutar script de limpieza
1. Acceder a `https://finoso.store/database/ejecutar_eliminar_auditoria.php`
2. Verificar que la tabla `auditoria_pagos` se elimine
3. Confirmar que `admin/debug_bd.php` funcione sin errores

---

## 🎯 Recomendación Final

**Para la BD de producción:**
- Usar el `finoso.sql` actualizado (ya está limpio)
- NO ejecutar script de eliminación (tabla no existirá)

**Para la BD de desarrollo actual:**
- Ejecutar `database/ejecutar_eliminar_auditoria.php`
- Esto elimina la tabla `auditoria_pagos` creada experimentalmente

---

**Fecha:** 27 de octubre de 2025  
**Estado:** ✅ Limpieza completada en código y esquema  
**Pendiente:** Ejecutar script de eliminación en BD de desarrollo


## 🧹 Resumen de Limpieza

Se eliminaron **4 elementos** innecesarios de la base de datos y el código.

---

## 1. ❌ Campo `cantidad` de tabla `orden_detalle`

### Antes:
```sql
CREATE TABLE `orden_detalle` (
  `id_orden_detalle` int(11) NOT NULL,
  `id_orden` int(11) DEFAULT NULL,
  `id_reloj` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,  ← ELIMINADO
  `precio_unitario` decimal(10,2) DEFAULT NULL
);
```

### Ahora:
```sql
CREATE TABLE `orden_detalle` (
  `id_orden_detalle` int(11) NOT NULL,
  `id_orden` int(11) DEFAULT NULL,
  `id_reloj` int(11) DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL
);
```

### ¿Por qué se eliminó?

- **NO se usa** en ningún archivo PHP
- Cada reloj vendido se registra como una fila independiente en `orden_detalle`
- Si una orden tiene 3 relojes → 3 registros en `orden_detalle`
- El campo `cantidad` siempre sería `1` o estaría vacío
- **NO existe** flujo de "comprar 2 unidades del mismo reloj"

**Conclusión:** Campo redundante e innecesario en el modelo actual.

---

## 2. ❌ Tabla `auditoria_pagos` (Completa)

### Estado Anterior:

**Tabla creada dinámicamente en:**
`informacion/php/subir_comprobante.php`

```php
$sql_auditoria = "CREATE TABLE IF NOT EXISTS auditoria_pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timestamp_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_cliente VARCHAR(45),
    user_agent TEXT,
    referer TEXT,
    monto_esperado DECIMAL(10,2),
    monto_recibido DECIMAL(10,2),
    hash_archivo VARCHAR(64),
    estado VARCHAR(20),
    token_verificacion VARCHAR(64),
    id_orden INT,
    observaciones TEXT
)";
$conn->query($sql_auditoria);

// Pero el INSERT estaba COMENTADO:
/*
$stmt_audit = $conn->prepare("INSERT INTO auditoria_pagos ...");
...
*/

// Y el UPDATE nunca funcionaba (sin $id_auditoria):
$stmt_audit_update = $conn->prepare("UPDATE auditoria_pagos ...");
```

### ¿Por qué existía?

Era un **experimento de auditoría de pagos** para registrar:
- IP del cliente
- User Agent (navegador)
- Monto esperado vs recibido
- Hash del comprobante
- Token de verificación

### ¿Por qué se eliminó?

1. **Código comentado** desde el inicio (nunca se activó)
2. **Tabla vacía** (se creaba pero nunca se insertaba nada)
3. **NO está en `finoso.sql`** (no es parte del esquema oficial)
4. **Sistema actual es suficiente:**
   - `orden.comprobante_pago` → guarda el archivo
   - `orden.token_verificacion` → token único
   - `orden.monto_pagado` → monto declarado
   - Logs de Apache/PHP → IPs y User Agents

### Archivos modificados:

#### ✅ `informacion/php/subir_comprobante.php`
**Eliminado:**
- Líneas 144-176: Creación de tabla y código comentado de INSERT
- Líneas 459-464: UPDATE a tabla inexistente

**Antes:**
```php
// 7. Registrar intento de pago en auditoría
$ip_cliente = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
// ... 30 líneas de código comentado
```

**Ahora:**
```php
// 6. Generar token de verificación único para esta transacción
$token_verificacion = bin2hex(random_bytes(32));

// Verificar archivo de comprobante
```

#### ✅ `admin/debug_bd.php`
**Eliminado:**
```php
$tablas = [
    'auditoria_pagos',  ← ELIMINADO
    'carrito',
    // ...
];
```

**Agregado:**
```php
$tablas = [
    'carrito',
    // ...
    'usuario_codigo_descuento'  ← AGREGADO (estaba faltando)
];
```

---

## 3. 🔧 Campo `cantidad` - Eliminación de la BD Actual

### Script SQL creado:
`database/eliminar_auditoria_pagos.sql`
```sql
DROP TABLE IF EXISTS auditoria_pagos;
```

### Script PHP ejecutor:
`database/ejecutar_eliminar_auditoria.php`

**Funcionalidad:**
1. Verifica si la tabla `auditoria_pagos` existe
2. Si existe → la elimina
3. Si no existe → informa que no hay nada que hacer
4. Muestra resumen de la operación

**Para ejecutar:**
```
https://finoso.store/database/ejecutar_eliminar_auditoria.php
```

---

## 📊 Comparación General

### Antes de la Limpieza:

**`finoso.sql`:**
- Tabla `orden`: 32 campos (3 sin uso)
- Tabla `orden_detalle`: 5 campos (1 sin uso)
- Tabla `auditoria_pagos`: NO en finoso.sql

**Código PHP:**
- `informacion/php/subir_comprobante.php`: ~40 líneas de código comentado
- `admin/debug_bd.php`: referencia a tabla fantasma

**Base de Datos Actual:**
- Tabla `auditoria_pagos`: vacía pero existe

### Después de la Limpieza:

**`finoso.sql`:**
- Tabla `orden`: 29 campos ✅ (todos en uso)
- Tabla `orden_detalle`: 4 campos ✅ (todos en uso)
- Tabla `auditoria_pagos`: ❌ Nunca se creará

**Código PHP:**
- `informacion/php/subir_comprobante.php`: Código limpio, sin experimentos comentados
- `admin/debug_bd.php`: Solo tablas reales + incluye `usuario_codigo_descuento`

**Base de Datos Actual:**
- Tabla `auditoria_pagos`: ⏳ Pendiente de eliminar con script

---

## ✅ Beneficios de la Limpieza

### 1. Esquema más Limpio
- Sin campos huérfanos
- Sin tablas experimentales
- Código más legible

### 2. Mejor Rendimiento
- Menos campos en consultas JOIN
- Menos índices innecesarios
- Queries más eficientes

### 3. Mantenimiento más Fácil
- Código sin confusión
- Desarrolladores futuros no se preguntarán "¿para qué es esto?"
- Documentación más clara

### 4. Auditoría Clara
- El sistema actual ya registra lo necesario:
  - `orden.comprobante_pago` → archivo del comprobante
  - `orden.token_verificacion` → identificador único
  - `orden.monto_pagado` → monto declarado por el cliente
  - `orden.fecha` → timestamp de la transacción

---

## 📝 Resumen de Archivos Modificados

### Esquema (`finoso.sql`):
1. ✅ Tabla `orden_detalle` → Campo `cantidad` eliminado

### PHP (2 archivos):
2. ✅ `informacion/php/subir_comprobante.php` → Código de `auditoria_pagos` eliminado
3. ✅ `admin/debug_bd.php` → Tabla `auditoria_pagos` eliminada de la lista

### Scripts de BD (2 nuevos):
4. 📄 `database/eliminar_auditoria_pagos.sql` → SQL para DROP TABLE
5. 📄 `database/ejecutar_eliminar_auditoria.php` → Ejecutor con validaciones

### Documentación (1 nuevo):
6. 📄 `LIMPIEZA_BD_CAMPOS_INNECESARIOS.md` → Este archivo

**Total: 6 archivos modificados/creados**

---

## 🧪 Pruebas Recomendadas

### Test 1: Verificar INSERT en orden_detalle
1. Realizar una compra (individual, carrito o favoritos)
2. Verificar que `orden_detalle` se inserte correctamente
3. Confirmar que **NO se use** el campo `cantidad` en ningún lado

### Test 2: Verificar que no haya errores
1. Realizar compra individual con Nequi
2. Verificar que NO haya errores de `$id_auditoria` indefinida
3. Confirmar que la orden se cree correctamente

### Test 3: Ejecutar script de limpieza
1. Acceder a `https://finoso.store/database/ejecutar_eliminar_auditoria.php`
2. Verificar que la tabla `auditoria_pagos` se elimine
3. Confirmar que `admin/debug_bd.php` funcione sin errores

---

## 🎯 Recomendación Final

**Para la BD de producción:**
- Usar el `finoso.sql` actualizado (ya está limpio)
- NO ejecutar script de eliminación (tabla no existirá)

**Para la BD de desarrollo actual:**
- Ejecutar `database/ejecutar_eliminar_auditoria.php`
- Esto elimina la tabla `auditoria_pagos` creada experimentalmente

---

**Fecha:** 27 de octubre de 2025  
**Estado:** ✅ Limpieza completada en código y esquema  
**Pendiente:** Ejecutar script de eliminación en BD de desarrollo

