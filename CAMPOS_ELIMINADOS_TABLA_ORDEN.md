# Campos Eliminados de la Tabla `orden`

## 🗑️ Resumen

Se eliminaron **3 campos** de la tabla `orden` en `finoso.sql` que **NO se usan** en ninguna parte del sistema.

---

## 1. ❌ `codigo_descuento_id`

### Definición anterior:
```sql
`codigo_descuento_id` int(11) DEFAULT NULL,
```

### ¿Por qué se eliminó?

- **NO se usa** en ningún archivo PHP del proyecto
- El sistema de códigos de descuento funciona completamente con la tabla `usuario_codigo_descuento`
- Los códigos se asignan a usuarios, no a órdenes directamente
- La relación orden-código se hace mediante `usuario_codigo_descuento.id_orden`

### Sistema actual (correcto):

**Tabla `usuario_codigo_descuento`:**
```
id_usuario_codigo | id_usuario | id_codigo | id_reloj | id_orden
1                 | 5          | 19        | 3        | 42
```

**Flujo:**
1. Orden #30 aprobada → genera código → `usuario_codigo_descuento` (activo=1, id_orden=NULL)
2. Usuario aplica código → `activo=0`, `id_reloj=3`, `id_orden=NULL`
3. Usuario compra → `id_orden=42` (vincula con la compra)

**Conclusión:** El campo `codigo_descuento_id` en `orden` era redundante.

### Elementos eliminados:
```sql
-- Columna
`codigo_descuento_id` int(11) DEFAULT NULL,

-- Índice
ADD KEY `codigo_descuento_id` (`codigo_descuento_id`);

-- Foreign key
ADD CONSTRAINT `orden_ibfk_2` FOREIGN KEY (`codigo_descuento_id`) REFERENCES `codigo_descuento` (`id_codigo`);
```

---

## 2. ❌ `intentos_pago`

### Definición anterior:
```sql
`intentos_pago` int(11) DEFAULT 0 COMMENT 'Número de intentos de pago/resubida de comprobante',
```

### ¿Por qué se eliminó?

- **NO existe** flujo de resubida de comprobantes en el sistema
- Siempre estaba en `0`
- Nunca se incrementaba ni se leía

### Flujo actual:

1. Cliente sube comprobante
2. Admin **verifica** o **rechaza**
3. Si se rechaza → se envía recordatorio por WhatsApp
4. **NO se permite** volver a subir otro comprobante para la misma orden
5. El cliente debe crear una **nueva orden**

**Conclusión:** Sin sistema de resubida, este campo no tiene función.

---

## 3. ❌ `fecha_ultima_subida`

### Definición anterior:
```sql
`fecha_ultima_subida` datetime DEFAULT NULL COMMENT 'Última vez que se subió o actualizó el comprobante',
```

### ¿Por qué se eliminó?

- **NO existe** flujo de actualización de comprobantes
- Siempre estaba en `NULL`
- Nunca se actualizaba

### Flujo actual:

- `fecha` (campo existente) ya marca cuándo se creó la orden
- NO hay sistema de actualización de comprobantes
- Si un comprobante se rechaza, el cliente crea una nueva orden con nueva `fecha`

**Conclusión:** Redundante con el campo `fecha` existente.

---

## ✅ Campos que SÍ se MANTIENEN

### 1. **`monto_pagado`** ✅ Actualizado

**Antes:**
```sql
`monto_pagado` decimal(10,2) DEFAULT 0 COMMENT 'Monto real que pagó el cliente',
```

**Ahora:**
```sql
`monto_pagado` decimal(10,2) DEFAULT NULL COMMENT 'Monto real que pagó el cliente (puede ser menor al total esperado)',
```

**Usos:**
1. **Cliente** puede ingresarlo al subir comprobante (opcional)
2. **Admin** puede ingresarlo al rechazar por "monto incorrecto"

**Beneficio:**
- Facilita detección de discrepancias
- Mejora comunicación cliente-admin
- Útil para estadísticas

---

### 2. **`recordatorio_enviado`** ✅ Mantenido

```sql
`recordatorio_enviado` tinyint(1) DEFAULT 0 COMMENT 'Indica si se envió recordatorio WhatsApp para orden rechazada',
```

**Uso actual:** Sistema de recordatorios WhatsApp

**Archivo:** `admin/php/recordatorio_orden_rechazada.php`

```php
// Buscar órdenes sin recordatorio
WHERE recordatorio_enviado = 0

// Marcar como enviado
UPDATE orden SET recordatorio_enviado = 1
```

**Estado:** ✅ Funcional (esperando reactivación de Twilio)

---

### 3. **`motivo_rechazo`** ✅ Mantenido

```sql
`motivo_rechazo` text DEFAULT NULL,
```

**Uso:** Almacenar la razón por la que se rechazó una orden

**Archivo:** `admin/php/acciones.php`

```php
UPDATE orden 
SET estado = 'rechazado', 
    motivo_rechazo = ?
WHERE id_orden = ?
```

**Mensajes comunes:**
- "El comprobante pertenece a otra transacción"
- "Monto incorrecto en comprobante"
- "Comprobante no legible o adulterado"

---

## 📊 Comparación Antes/Después

### Tabla `orden` - Campos de Validación

| Campo | Antes | Ahora | ¿Por qué? |
|-------|-------|-------|-----------|
| `monto_pagado` | DEFAULT 0 | DEFAULT NULL ✅ | Más semántico (NULL = no ingresado) |
| `intentos_pago` | DEFAULT 0 | ❌ ELIMINADO | No hay sistema de resubida |
| `fecha_ultima_subida` | DEFAULT NULL | ❌ ELIMINADO | No hay sistema de actualización |
| `codigo_descuento_id` | FK a codigo_descuento | ❌ ELIMINADO | No se usa, redundante |
| `recordatorio_enviado` | DEFAULT 0 ✅ | DEFAULT 0 ✅ | Sistema WhatsApp activo |
| `motivo_rechazo` | text ✅ | text ✅ | Usado al rechazar |

---

## 🎯 Resultado Final

### Antes (campos innecesarios):
- 32 campos en la tabla `orden`
- 3 campos sin uso
- 1 FK redundante

### Ahora (optimizado):
- 29 campos en la tabla `orden`
- Todos los campos tienen función definida
- Esquema más limpio y mantenible

---

## 📁 Archivo Actualizado

- ✅ `finoso.sql` - Esquema de producción limpio y optimizado

**Nota:** La BD actual en desarrollo puede tener estos campos, pero al crear la BD de producción desde `finoso.sql`, no se incluirán.

---

**Fecha:** 27 de octubre de 2025  
**Estado:** ✅ Limpieza completada


## 🗑️ Resumen

Se eliminaron **3 campos** de la tabla `orden` en `finoso.sql` que **NO se usan** en ninguna parte del sistema.

---

## 1. ❌ `codigo_descuento_id`

### Definición anterior:
```sql
`codigo_descuento_id` int(11) DEFAULT NULL,
```

### ¿Por qué se eliminó?

- **NO se usa** en ningún archivo PHP del proyecto
- El sistema de códigos de descuento funciona completamente con la tabla `usuario_codigo_descuento`
- Los códigos se asignan a usuarios, no a órdenes directamente
- La relación orden-código se hace mediante `usuario_codigo_descuento.id_orden`

### Sistema actual (correcto):

**Tabla `usuario_codigo_descuento`:**
```
id_usuario_codigo | id_usuario | id_codigo | id_reloj | id_orden
1                 | 5          | 19        | 3        | 42
```

**Flujo:**
1. Orden #30 aprobada → genera código → `usuario_codigo_descuento` (activo=1, id_orden=NULL)
2. Usuario aplica código → `activo=0`, `id_reloj=3`, `id_orden=NULL`
3. Usuario compra → `id_orden=42` (vincula con la compra)

**Conclusión:** El campo `codigo_descuento_id` en `orden` era redundante.

### Elementos eliminados:
```sql
-- Columna
`codigo_descuento_id` int(11) DEFAULT NULL,

-- Índice
ADD KEY `codigo_descuento_id` (`codigo_descuento_id`);

-- Foreign key
ADD CONSTRAINT `orden_ibfk_2` FOREIGN KEY (`codigo_descuento_id`) REFERENCES `codigo_descuento` (`id_codigo`);
```

---

## 2. ❌ `intentos_pago`

### Definición anterior:
```sql
`intentos_pago` int(11) DEFAULT 0 COMMENT 'Número de intentos de pago/resubida de comprobante',
```

### ¿Por qué se eliminó?

- **NO existe** flujo de resubida de comprobantes en el sistema
- Siempre estaba en `0`
- Nunca se incrementaba ni se leía

### Flujo actual:

1. Cliente sube comprobante
2. Admin **verifica** o **rechaza**
3. Si se rechaza → se envía recordatorio por WhatsApp
4. **NO se permite** volver a subir otro comprobante para la misma orden
5. El cliente debe crear una **nueva orden**

**Conclusión:** Sin sistema de resubida, este campo no tiene función.

---

## 3. ❌ `fecha_ultima_subida`

### Definición anterior:
```sql
`fecha_ultima_subida` datetime DEFAULT NULL COMMENT 'Última vez que se subió o actualizó el comprobante',
```

### ¿Por qué se eliminó?

- **NO existe** flujo de actualización de comprobantes
- Siempre estaba en `NULL`
- Nunca se actualizaba

### Flujo actual:

- `fecha` (campo existente) ya marca cuándo se creó la orden
- NO hay sistema de actualización de comprobantes
- Si un comprobante se rechaza, el cliente crea una nueva orden con nueva `fecha`

**Conclusión:** Redundante con el campo `fecha` existente.

---

## ✅ Campos que SÍ se MANTIENEN

### 1. **`monto_pagado`** ✅ Actualizado

**Antes:**
```sql
`monto_pagado` decimal(10,2) DEFAULT 0 COMMENT 'Monto real que pagó el cliente',
```

**Ahora:**
```sql
`monto_pagado` decimal(10,2) DEFAULT NULL COMMENT 'Monto real que pagó el cliente (puede ser menor al total esperado)',
```

**Usos:**
1. **Cliente** puede ingresarlo al subir comprobante (opcional)
2. **Admin** puede ingresarlo al rechazar por "monto incorrecto"

**Beneficio:**
- Facilita detección de discrepancias
- Mejora comunicación cliente-admin
- Útil para estadísticas

---

### 2. **`recordatorio_enviado`** ✅ Mantenido

```sql
`recordatorio_enviado` tinyint(1) DEFAULT 0 COMMENT 'Indica si se envió recordatorio WhatsApp para orden rechazada',
```

**Uso actual:** Sistema de recordatorios WhatsApp

**Archivo:** `admin/php/recordatorio_orden_rechazada.php`

```php
// Buscar órdenes sin recordatorio
WHERE recordatorio_enviado = 0

// Marcar como enviado
UPDATE orden SET recordatorio_enviado = 1
```

**Estado:** ✅ Funcional (esperando reactivación de Twilio)

---

### 3. **`motivo_rechazo`** ✅ Mantenido

```sql
`motivo_rechazo` text DEFAULT NULL,
```

**Uso:** Almacenar la razón por la que se rechazó una orden

**Archivo:** `admin/php/acciones.php`

```php
UPDATE orden 
SET estado = 'rechazado', 
    motivo_rechazo = ?
WHERE id_orden = ?
```

**Mensajes comunes:**
- "El comprobante pertenece a otra transacción"
- "Monto incorrecto en comprobante"
- "Comprobante no legible o adulterado"

---

## 📊 Comparación Antes/Después

### Tabla `orden` - Campos de Validación

| Campo | Antes | Ahora | ¿Por qué? |
|-------|-------|-------|-----------|
| `monto_pagado` | DEFAULT 0 | DEFAULT NULL ✅ | Más semántico (NULL = no ingresado) |
| `intentos_pago` | DEFAULT 0 | ❌ ELIMINADO | No hay sistema de resubida |
| `fecha_ultima_subida` | DEFAULT NULL | ❌ ELIMINADO | No hay sistema de actualización |
| `codigo_descuento_id` | FK a codigo_descuento | ❌ ELIMINADO | No se usa, redundante |
| `recordatorio_enviado` | DEFAULT 0 ✅ | DEFAULT 0 ✅ | Sistema WhatsApp activo |
| `motivo_rechazo` | text ✅ | text ✅ | Usado al rechazar |

---

## 🎯 Resultado Final

### Antes (campos innecesarios):
- 32 campos en la tabla `orden`
- 3 campos sin uso
- 1 FK redundante

### Ahora (optimizado):
- 29 campos en la tabla `orden`
- Todos los campos tienen función definida
- Esquema más limpio y mantenible

---

## 📁 Archivo Actualizado

- ✅ `finoso.sql` - Esquema de producción limpio y optimizado

**Nota:** La BD actual en desarrollo puede tener estos campos, pero al crear la BD de producción desde `finoso.sql`, no se incluirán.

---

**Fecha:** 27 de octubre de 2025  
**Estado:** ✅ Limpieza completada

