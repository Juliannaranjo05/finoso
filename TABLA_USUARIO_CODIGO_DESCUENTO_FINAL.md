# Tabla `usuario_codigo_descuento` - Implementación Final

## 📅 Fecha de Actualización
27 de octubre de 2025

## ✅ Cambios Realizados

### 1. **Nuevo Campo Agregado**

```sql
ALTER TABLE usuario_codigo_descuento 
ADD COLUMN id_reloj INT(11) DEFAULT NULL AFTER id_codigo;
```

### 2. **Estructura Final**

```sql
CREATE TABLE `usuario_codigo_descuento` (
  `id_usuario_codigo` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_codigo` int(11) NOT NULL,
  `id_reloj` int(11) DEFAULT NULL,          -- ✅ NUEVO
  `fecha_asignado` datetime DEFAULT CURRENT_TIMESTAMP(),
  `fecha_usado` datetime DEFAULT NULL,
  `id_orden` int(11) DEFAULT NULL,
  `veces_usado` int(11) DEFAULT 0,          -- No se usa, pero se mantiene
  `activo` tinyint(1) DEFAULT 1,
  `notas` text DEFAULT NULL,
  PRIMARY KEY (`id_usuario_codigo`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_codigo` (`id_codigo`),
  KEY `id_reloj` (`id_reloj`),              -- ✅ NUEVO
  KEY `id_orden` (`id_orden`),
  CONSTRAINT `fk_reloj` FOREIGN KEY (`id_reloj`) REFERENCES `reloj` (`id_reloj`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 🔄 Flujo de Uso

### **1. Usuario recibe código** (Admin)
```sql
INSERT INTO usuario_codigo_descuento 
(id_usuario, id_codigo, notas) 
VALUES (7, 18, 'Código de agradecimiento por tu compra #28 🎉')

-- Estado: activo=1, id_reloj=NULL, fecha_usado=NULL
```

### **2. Usuario aplica código en un reloj**
```sql
UPDATE usuario_codigo_descuento 
SET activo = 0,
    fecha_usado = NOW(),
    id_reloj = 123
WHERE id_usuario = 7 AND id_codigo = 18

-- Estado: activo=0, id_reloj=123, fecha_usado=2025-10-27
```

### **3. Usuario recarga página**
```sql
SELECT ucd.*, cd.codigo, cd.porcentaje
FROM usuario_codigo_descuento ucd
JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
WHERE ucd.id_usuario = 7 
  AND ucd.id_reloj = 123
  AND ucd.activo = 0
  AND ucd.id_orden IS NULL

-- Recupera: código=FIN0873E0, porcentaje=10
-- Frontend muestra: $112,500 (10% OFF)
```

### **4. Usuario completa compra**
```sql
UPDATE usuario_codigo_descuento 
SET id_orden = 28
WHERE id_usuario = 7 AND id_codigo = 18

-- Estado: activo=0, id_reloj=123, id_orden=28
```

### **5. Usuario ve su perfil**
```sql
SELECT ucd.*, cd.*
FROM usuario_codigo_descuento ucd
JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
WHERE ucd.id_usuario = 7

-- Determina estado:
-- fecha_usado != NULL && activo = 0 → "USADO" ❌ No copiar
```

## 📊 Estados Posibles

| activo | fecha_usado | id_reloj | id_orden | Estado | Botón Copiar |
|--------|-------------|----------|----------|--------|--------------|
| 1 | NULL | NULL | NULL | **Disponible** | ✅ Sí |
| 0 | 2025-10-27 | 123 | NULL | **Aplicado (sin comprar)** | ❌ No |
| 0 | 2025-10-27 | 123 | 28 | **Usado (comprado)** | ❌ No |

## 🎯 Validaciones

### **Al intentar aplicar**:

| Escenario | Validación | Resultado |
|-----------|------------|-----------|
| Código no existe | `WHERE codigo = ?` | ❌ "Código no existe" |
| Código expirado | `fecha_expiracion < NOW()` | ❌ "Código expirado" |
| No asignado al usuario | `id_usuario != ?` | ❌ "No asignado a tu cuenta" |
| Ya usado (activo=0) | `activo = 0` | ❌ "Ya utilizaste este código" |
| Ya usado (fecha_usado) | `fecha_usado != NULL` | ❌ "Ya utilizaste este código" |
| Todo OK | - | ✅ Se aplica y marca activo=0 |

## 🗑️ Tabla Eliminada

Se eliminó la tabla temporal `descuento_aplicado_reloj` que se había creado por error.

## 📝 Scripts de Actualización

### **Ejecutar UNA SOLA VEZ**:

```bash
http://localhost/finoso/database/ejecutar_actualizar_usuario_codigo.php
```

Este script:
1. ✅ Vacía la tabla `usuario_codigo_descuento`
2. ✅ Agrega el campo `id_reloj`
3. ✅ Agrega la foreign key
4. ✅ Elimina la tabla `descuento_aplicado_reloj`

## ✅ Archivos Actualizados

1. **`finoso.sql`** - Estructura actualizada con `id_reloj`
2. **`database/actualizar_usuario_codigo_descuento.sql`** - Script SQL
3. **`database/ejecutar_actualizar_usuario_codigo.php`** - Ejecutor PHP
4. **`informacion/php/aplicar_codigo_descuento.php`** - Usa solo `usuario_codigo_descuento`
5. **`informacion/php/obtener_descuento_aplicado.php`** - Usa solo `usuario_codigo_descuento`
6. **`informacion/php/subir_comprobante.php`** - Eliminada referencia a tabla temporal
7. **`informacion/php/wompi_webhook.php`** - Eliminada referencia a tabla temporal

## 🎉 Estado Final

✅ **TODO FUNCIONA CON `usuario_codigo_descuento`**
- Se agregó el campo `id_reloj` necesario
- Se eliminó la tabla temporal incorrecta
- Todo el flujo usa una sola tabla
- Persistencia 100% en base de datos
- Sin localStorage


## 📅 Fecha de Actualización
27 de octubre de 2025

## ✅ Cambios Realizados

### 1. **Nuevo Campo Agregado**

```sql
ALTER TABLE usuario_codigo_descuento 
ADD COLUMN id_reloj INT(11) DEFAULT NULL AFTER id_codigo;
```

### 2. **Estructura Final**

```sql
CREATE TABLE `usuario_codigo_descuento` (
  `id_usuario_codigo` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_codigo` int(11) NOT NULL,
  `id_reloj` int(11) DEFAULT NULL,          -- ✅ NUEVO
  `fecha_asignado` datetime DEFAULT CURRENT_TIMESTAMP(),
  `fecha_usado` datetime DEFAULT NULL,
  `id_orden` int(11) DEFAULT NULL,
  `veces_usado` int(11) DEFAULT 0,          -- No se usa, pero se mantiene
  `activo` tinyint(1) DEFAULT 1,
  `notas` text DEFAULT NULL,
  PRIMARY KEY (`id_usuario_codigo`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_codigo` (`id_codigo`),
  KEY `id_reloj` (`id_reloj`),              -- ✅ NUEVO
  KEY `id_orden` (`id_orden`),
  CONSTRAINT `fk_reloj` FOREIGN KEY (`id_reloj`) REFERENCES `reloj` (`id_reloj`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 🔄 Flujo de Uso

### **1. Usuario recibe código** (Admin)
```sql
INSERT INTO usuario_codigo_descuento 
(id_usuario, id_codigo, notas) 
VALUES (7, 18, 'Código de agradecimiento por tu compra #28 🎉')

-- Estado: activo=1, id_reloj=NULL, fecha_usado=NULL
```

### **2. Usuario aplica código en un reloj**
```sql
UPDATE usuario_codigo_descuento 
SET activo = 0,
    fecha_usado = NOW(),
    id_reloj = 123
WHERE id_usuario = 7 AND id_codigo = 18

-- Estado: activo=0, id_reloj=123, fecha_usado=2025-10-27
```

### **3. Usuario recarga página**
```sql
SELECT ucd.*, cd.codigo, cd.porcentaje
FROM usuario_codigo_descuento ucd
JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
WHERE ucd.id_usuario = 7 
  AND ucd.id_reloj = 123
  AND ucd.activo = 0
  AND ucd.id_orden IS NULL

-- Recupera: código=FIN0873E0, porcentaje=10
-- Frontend muestra: $112,500 (10% OFF)
```

### **4. Usuario completa compra**
```sql
UPDATE usuario_codigo_descuento 
SET id_orden = 28
WHERE id_usuario = 7 AND id_codigo = 18

-- Estado: activo=0, id_reloj=123, id_orden=28
```

### **5. Usuario ve su perfil**
```sql
SELECT ucd.*, cd.*
FROM usuario_codigo_descuento ucd
JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
WHERE ucd.id_usuario = 7

-- Determina estado:
-- fecha_usado != NULL && activo = 0 → "USADO" ❌ No copiar
```

## 📊 Estados Posibles

| activo | fecha_usado | id_reloj | id_orden | Estado | Botón Copiar |
|--------|-------------|----------|----------|--------|--------------|
| 1 | NULL | NULL | NULL | **Disponible** | ✅ Sí |
| 0 | 2025-10-27 | 123 | NULL | **Aplicado (sin comprar)** | ❌ No |
| 0 | 2025-10-27 | 123 | 28 | **Usado (comprado)** | ❌ No |

## 🎯 Validaciones

### **Al intentar aplicar**:

| Escenario | Validación | Resultado |
|-----------|------------|-----------|
| Código no existe | `WHERE codigo = ?` | ❌ "Código no existe" |
| Código expirado | `fecha_expiracion < NOW()` | ❌ "Código expirado" |
| No asignado al usuario | `id_usuario != ?` | ❌ "No asignado a tu cuenta" |
| Ya usado (activo=0) | `activo = 0` | ❌ "Ya utilizaste este código" |
| Ya usado (fecha_usado) | `fecha_usado != NULL` | ❌ "Ya utilizaste este código" |
| Todo OK | - | ✅ Se aplica y marca activo=0 |

## 🗑️ Tabla Eliminada

Se eliminó la tabla temporal `descuento_aplicado_reloj` que se había creado por error.

## 📝 Scripts de Actualización

### **Ejecutar UNA SOLA VEZ**:

```bash
http://localhost/finoso/database/ejecutar_actualizar_usuario_codigo.php
```

Este script:
1. ✅ Vacía la tabla `usuario_codigo_descuento`
2. ✅ Agrega el campo `id_reloj`
3. ✅ Agrega la foreign key
4. ✅ Elimina la tabla `descuento_aplicado_reloj`

## ✅ Archivos Actualizados

1. **`finoso.sql`** - Estructura actualizada con `id_reloj`
2. **`database/actualizar_usuario_codigo_descuento.sql`** - Script SQL
3. **`database/ejecutar_actualizar_usuario_codigo.php`** - Ejecutor PHP
4. **`informacion/php/aplicar_codigo_descuento.php`** - Usa solo `usuario_codigo_descuento`
5. **`informacion/php/obtener_descuento_aplicado.php`** - Usa solo `usuario_codigo_descuento`
6. **`informacion/php/subir_comprobante.php`** - Eliminada referencia a tabla temporal
7. **`informacion/php/wompi_webhook.php`** - Eliminada referencia a tabla temporal

## 🎉 Estado Final

✅ **TODO FUNCIONA CON `usuario_codigo_descuento`**
- Se agregó el campo `id_reloj` necesario
- Se eliminó la tabla temporal incorrecta
- Todo el flujo usa una sola tabla
- Persistencia 100% en base de datos
- Sin localStorage

