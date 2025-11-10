# Sincronización BD Actual ↔️ finoso.sql

## 🎯 Objetivo

Asegurar que la **base de datos de desarrollo** (montada actualmente) y el archivo **`finoso.sql`** (esquema de producción) tengan la **misma estructura**.

---

## 🛠️ Herramientas Creadas

Se crearon **3 herramientas** para facilitarte la comparación y sincronización:

### 1. **`database/comparar_estructura_bd.php`**
**📋 Función:** Muestra la estructura completa de tu BD actual

**Qué muestra:**
- ✅ Todas las tablas
- ✅ Todas las columnas (tipo, null, default, extra, comentarios)
- ✅ Índices (primary, unique, foreign keys)
- ✅ Foreign Keys (ON UPDATE, ON DELETE)
- ✅ CREATE TABLE statement completo
- ✅ Cantidad de registros por tabla

**Cómo usarlo:**
```
https://finoso.store/database/comparar_estructura_bd.php
```

**Salida:** Página HTML detallada con toda la estructura

---

### 2. **`database/exportar_estructura_sql.php`**
**📥 Función:** Exporta la estructura actual como archivo SQL

**Qué hace:**
- ✅ Genera archivo SQL con **solo estructura** (sin datos)
- ✅ Incluye `DROP TABLE IF EXISTS`
- ✅ Incluye `CREATE TABLE` completos
- ✅ Formato igual a `finoso.sql` para fácil comparación
- ✅ Nombre de archivo con timestamp: `estructura_actual_2025-10-27_14-30-15.sql`

**Cómo usarlo:**
```
https://finoso.store/database/exportar_estructura_sql.php
```

**Salida:** Archivo SQL descargable + Preview en pantalla

---

### 3. **`database/index.html`**
**🎨 Función:** Panel central con acceso a todas las herramientas

**Cómo acceder:**
```
https://finoso.store/database/
```

**Incluye:**
- Links a todas las herramientas
- Explicación de cada una
- Flujo recomendado
- Advertencias de seguridad

---

## 📝 Flujo de Trabajo Recomendado

### **Paso 1: Ver Estructura Actual** 🔍

Accede a:
```
https://finoso.store/database/comparar_estructura_bd.php
```

**Qué revisar:**
- ¿Cuántas tablas tienes?
- ¿Qué campos tiene cada tabla?
- ¿Hay tablas experimentales? (como `auditoria_pagos`)

**Toma nota** de cualquier tabla o campo sospechoso.

---

### **Paso 2: Exportar Estructura SQL** 📥

Accede a:
```
https://finoso.store/database/exportar_estructura_sql.php
```

1. Click en **"⬇️ Descargar estructura_actual_[timestamp].sql"**
2. Guarda el archivo en la misma carpeta que `finoso.sql`

---

### **Paso 3: Comparar con finoso.sql** 🔄

#### **Opción A: VS Code (Recomendado)**

1. Abre VS Code en tu proyecto
2. Abre ambos archivos:
   - `finoso.sql`
   - `estructura_actual_2025-10-27_XX-XX-XX.sql`
3. Click derecho en `finoso.sql` → **"Select for Compare"**
4. Click derecho en `estructura_actual_...sql` → **"Compare with Selected"**

**VS Code mostrará:**
- 🔴 Líneas en **rojo** → Solo en archivo actual (sobran)
- 🟢 Líneas en **verde** → Solo en finoso.sql (faltan)
- ⚪ Líneas iguales → Correcto

#### **Opción B: DiffChecker Online**

1. Ve a: https://www.diffchecker.com/
2. Copia el contenido de `finoso.sql` en el panel izquierdo
3. Copia el contenido de `estructura_actual_...sql` en el panel derecho
4. Click en **"Find Difference"**

#### **Opción C: WinMerge (Windows)**

1. Descarga: https://winmerge.org/
2. Instala y abre WinMerge
3. File → Open → Selecciona ambos archivos
4. Compara visualmente

---

### **Paso 4: Identificar Diferencias** 🕵️

Busca estos tipos de diferencias:

#### **A) Tablas completas:**

**🔴 Tabla en BD actual pero NO en finoso.sql:**
```sql
-- Ejemplo: auditoria_pagos existe en BD pero NO en finoso.sql
```
**Acción:** ¿Es experimental? → Eliminarla

**🟢 Tabla en finoso.sql pero NO en BD actual:**
```sql
-- Ejemplo: usuario_codigo_descuento existe en finoso.sql pero NO en BD
```
**Acción:** Ejecutar script para crearla

---

#### **B) Campos de tablas:**

**🔴 Campo en BD pero NO en finoso.sql:**
```sql
-- Ejemplo en tabla `orden`:
`codigo_descuento_id` int(11) DEFAULT NULL,  -- ← Solo en BD, NO en finoso.sql
```
**Acción:** Decidir si eliminar o agregar a finoso.sql

**🟢 Campo en finoso.sql pero NO en BD:**
```sql
-- Ejemplo en tabla `orden`:
`monto_pagado` decimal(10,2) DEFAULT NULL,  -- ← Solo en finoso.sql
```
**Acción:** Crear script SQL para agregarlo

---

#### **C) Tipos de dato diferentes:**

**BD actual:**
```sql
`total` decimal(10,2) DEFAULT 0,
```

**finoso.sql:**
```sql
`total` decimal(10,2) DEFAULT NULL,
```

**Acción:** Decidir cuál es el correcto y actualizar

---

#### **D) Índices/Foreign Keys diferentes:**

**BD actual:**
```sql
ALTER TABLE `orden`
  ADD CONSTRAINT `orden_ibfk_2` FOREIGN KEY (`codigo_descuento_id`) REFERENCES `codigo_descuento` (`id_codigo`);
```

**finoso.sql:**
```sql
-- NO tiene esta FK
```

**Acción:** Eliminar FK innecesaria

---

### **Paso 5: Crear Scripts de Sincronización** 🔧

Para cada diferencia encontrada, crea un script SQL:

#### **Ejemplo: Eliminar campo `cantidad` de `orden_detalle`**

**Archivo:** `database/eliminar_campo_cantidad.sql`
```sql
ALTER TABLE orden_detalle DROP COLUMN cantidad;
```

**Ejecutor:** `database/ejecutar_eliminar_cantidad.php`
```php
<?php
require_once '../admin/conexion.php';

echo "<h2>Eliminando campo cantidad...</h2>";

if ($conn->query("ALTER TABLE orden_detalle DROP COLUMN cantidad")) {
    echo "<p style='color: green;'>✅ Campo eliminado</p>";
} else {
    echo "<p style='color: red;'>❌ Error: " . $conn->error . "</p>";
}

$conn->close();
?>
```

#### **Ejemplo: Agregar campo `monto_pagado` a `orden`**

**Archivo:** `database/agregar_monto_pagado.sql`
```sql
ALTER TABLE orden 
ADD COLUMN monto_pagado decimal(10,2) DEFAULT NULL 
COMMENT 'Monto real que pagó el cliente' 
AFTER comprobante_verificado;
```

**Ejecutor:** Ya existe: `database/ejecutar_agregar_monto_pagado.php`

---

### **Paso 6: Ejecutar Scripts** ▶️

1. **BACKUP PRIMERO:**
   ```
   http://127.0.0.1/phpmyadmin/
   → Exportar → Método: Rápido → Formato: SQL → Continuar
   ```

2. **Ejecutar cada script:**
   ```
   https://finoso.store/database/ejecutar_[nombre_script].php
   ```

3. **Verificar resultado:**
   - Volver a `comparar_estructura_bd.php`
   - Confirmar que el cambio se aplicó

---

### **Paso 7: Actualizar finoso.sql** 📝

Una vez que tu BD actual esté **correcta y limpia**:

1. Exporta la estructura final:
   ```
   https://finoso.store/database/exportar_estructura_sql.php
   ```

2. Descarga el archivo generado

3. **Reemplaza** el contenido de `finoso.sql` con el nuevo

4. **Commit** a Git:
   ```bash
   git add finoso.sql
   git commit -m "Sincronización estructura BD - eliminados campos innecesarios"
   git push
   ```

---

## 📊 Ejemplo Práctico

### **Situación Actual:**

**BD de desarrollo tiene:**
- Tabla `auditoria_pagos` (vacía, experimental)
- Campo `orden.codigo_descuento_id` (no se usa)
- Campo `orden_detalle.cantidad` (siempre 1)

**finoso.sql tiene:**
- NO tiene `auditoria_pagos`
- NO tiene `orden.codigo_descuento_id`
- NO tiene `orden_detalle.cantidad`
- Tiene `orden.monto_pagado` (falta en BD)

---

### **Acciones a realizar:**

#### **1. Eliminar de BD actual:**
```sql
DROP TABLE IF EXISTS auditoria_pagos;
ALTER TABLE orden DROP COLUMN codigo_descuento_id;
ALTER TABLE orden_detalle DROP COLUMN cantidad;
```

Scripts:
- ✅ `ejecutar_eliminar_auditoria.php` (ya creado)
- ⏳ `ejecutar_eliminar_codigo_descuento_id.php` (crear)
- ⏳ `ejecutar_eliminar_cantidad.php` (crear)

#### **2. Agregar a BD actual:**
```sql
ALTER TABLE orden 
ADD COLUMN monto_pagado decimal(10,2) DEFAULT NULL 
AFTER comprobante_verificado;
```

Script:
- ✅ `ejecutar_agregar_monto_pagado.php` (ya existe)

---

### **Resultado Final:**

✅ BD actual = finoso.sql (misma estructura)  
✅ Sin campos innecesarios  
✅ Sin tablas experimentales  
✅ Todos los campos necesarios presentes

---

## 🎯 Checklist de Sincronización

Usa este checklist para asegurar que todo esté correcto:

### **Tablas Oficiales (deben estar en AMBOS):**
- [ ] `carrito`
- [ ] `codigo_descuento`
- [ ] `comentarios`
- [ ] `envios`
- [ ] `marca`
- [ ] `orden`
- [ ] `orden_detalle`
- [ ] `reloj`
- [ ] `reset_tokens`
- [ ] `usuario`
- [ ] `usuario_codigo_descuento`

### **Tablas Experimentales (NO deben estar):**
- [ ] ❌ `auditoria_pagos` (eliminada)
- [ ] ❌ `descuento_aplicado_reloj` (eliminada)

### **Campos Eliminados:**
- [ ] ❌ `orden.codigo_descuento_id`
- [ ] ❌ `orden.intentos_pago`
- [ ] ❌ `orden.fecha_ultima_subida`
- [ ] ❌ `orden_detalle.cantidad`

### **Campos Agregados:**
- [ ] ✅ `orden.monto_pagado`
- [ ] ✅ `usuario_codigo_descuento.id_reloj`

---

## ⚠️ Advertencias Importantes

### **NO ejecutes directamente en producción:**
- Los scripts están diseñados para **desarrollo**
- Siempre haz **BACKUP** antes de cualquier cambio
- Prueba primero en ambiente local

### **Si algo sale mal:**
1. **Restaura el backup**
2. Revisa los logs de error
3. Consulta la documentación
4. Ajusta el script y vuelve a intentar

---

## 📁 Archivos Creados

### **Herramientas de Comparación:**
- ✅ `database/comparar_estructura_bd.php` → Ver estructura HTML
- ✅ `database/exportar_estructura_sql.php` → Exportar SQL
- ✅ `database/index.html` → Panel principal

### **Scripts de Limpieza:**
- ✅ `database/ejecutar_eliminar_auditoria.php` → DROP auditoria_pagos
- ✅ `database/ejecutar_actualizar_usuario_codigo.php` → ADD id_reloj
- ✅ `database/ejecutar_fix_id_orden.php` → Fix id_orden NULL

### **Documentación:**
- ✅ `SINCRONIZACION_BD_FINOSO_SQL.md` → Este archivo

---

## 🚀 Empezar Ahora

**Paso 1:**
```
https://finoso.store/database/
```

**Paso 2:**
Click en "👁️ Ver Estructura Actual"

**Paso 3:**
Compara con `finoso.sql`

**Paso 4:**
Ejecuta los scripts necesarios

---

**Fecha:** 27 de octubre de 2025  
**Estado:** ✅ Herramientas listas para usar


## 🎯 Objetivo

Asegurar que la **base de datos de desarrollo** (montada actualmente) y el archivo **`finoso.sql`** (esquema de producción) tengan la **misma estructura**.

---

## 🛠️ Herramientas Creadas

Se crearon **3 herramientas** para facilitarte la comparación y sincronización:

### 1. **`database/comparar_estructura_bd.php`**
**📋 Función:** Muestra la estructura completa de tu BD actual

**Qué muestra:**
- ✅ Todas las tablas
- ✅ Todas las columnas (tipo, null, default, extra, comentarios)
- ✅ Índices (primary, unique, foreign keys)
- ✅ Foreign Keys (ON UPDATE, ON DELETE)
- ✅ CREATE TABLE statement completo
- ✅ Cantidad de registros por tabla

**Cómo usarlo:**
```
https://finoso.store/database/comparar_estructura_bd.php
```

**Salida:** Página HTML detallada con toda la estructura

---

### 2. **`database/exportar_estructura_sql.php`**
**📥 Función:** Exporta la estructura actual como archivo SQL

**Qué hace:**
- ✅ Genera archivo SQL con **solo estructura** (sin datos)
- ✅ Incluye `DROP TABLE IF EXISTS`
- ✅ Incluye `CREATE TABLE` completos
- ✅ Formato igual a `finoso.sql` para fácil comparación
- ✅ Nombre de archivo con timestamp: `estructura_actual_2025-10-27_14-30-15.sql`

**Cómo usarlo:**
```
https://finoso.store/database/exportar_estructura_sql.php
```

**Salida:** Archivo SQL descargable + Preview en pantalla

---

### 3. **`database/index.html`**
**🎨 Función:** Panel central con acceso a todas las herramientas

**Cómo acceder:**
```
https://finoso.store/database/
```

**Incluye:**
- Links a todas las herramientas
- Explicación de cada una
- Flujo recomendado
- Advertencias de seguridad

---

## 📝 Flujo de Trabajo Recomendado

### **Paso 1: Ver Estructura Actual** 🔍

Accede a:
```
https://finoso.store/database/comparar_estructura_bd.php
```

**Qué revisar:**
- ¿Cuántas tablas tienes?
- ¿Qué campos tiene cada tabla?
- ¿Hay tablas experimentales? (como `auditoria_pagos`)

**Toma nota** de cualquier tabla o campo sospechoso.

---

### **Paso 2: Exportar Estructura SQL** 📥

Accede a:
```
https://finoso.store/database/exportar_estructura_sql.php
```

1. Click en **"⬇️ Descargar estructura_actual_[timestamp].sql"**
2. Guarda el archivo en la misma carpeta que `finoso.sql`

---

### **Paso 3: Comparar con finoso.sql** 🔄

#### **Opción A: VS Code (Recomendado)**

1. Abre VS Code en tu proyecto
2. Abre ambos archivos:
   - `finoso.sql`
   - `estructura_actual_2025-10-27_XX-XX-XX.sql`
3. Click derecho en `finoso.sql` → **"Select for Compare"**
4. Click derecho en `estructura_actual_...sql` → **"Compare with Selected"**

**VS Code mostrará:**
- 🔴 Líneas en **rojo** → Solo en archivo actual (sobran)
- 🟢 Líneas en **verde** → Solo en finoso.sql (faltan)
- ⚪ Líneas iguales → Correcto

#### **Opción B: DiffChecker Online**

1. Ve a: https://www.diffchecker.com/
2. Copia el contenido de `finoso.sql` en el panel izquierdo
3. Copia el contenido de `estructura_actual_...sql` en el panel derecho
4. Click en **"Find Difference"**

#### **Opción C: WinMerge (Windows)**

1. Descarga: https://winmerge.org/
2. Instala y abre WinMerge
3. File → Open → Selecciona ambos archivos
4. Compara visualmente

---

### **Paso 4: Identificar Diferencias** 🕵️

Busca estos tipos de diferencias:

#### **A) Tablas completas:**

**🔴 Tabla en BD actual pero NO en finoso.sql:**
```sql
-- Ejemplo: auditoria_pagos existe en BD pero NO en finoso.sql
```
**Acción:** ¿Es experimental? → Eliminarla

**🟢 Tabla en finoso.sql pero NO en BD actual:**
```sql
-- Ejemplo: usuario_codigo_descuento existe en finoso.sql pero NO en BD
```
**Acción:** Ejecutar script para crearla

---

#### **B) Campos de tablas:**

**🔴 Campo en BD pero NO en finoso.sql:**
```sql
-- Ejemplo en tabla `orden`:
`codigo_descuento_id` int(11) DEFAULT NULL,  -- ← Solo en BD, NO en finoso.sql
```
**Acción:** Decidir si eliminar o agregar a finoso.sql

**🟢 Campo en finoso.sql pero NO en BD:**
```sql
-- Ejemplo en tabla `orden`:
`monto_pagado` decimal(10,2) DEFAULT NULL,  -- ← Solo en finoso.sql
```
**Acción:** Crear script SQL para agregarlo

---

#### **C) Tipos de dato diferentes:**

**BD actual:**
```sql
`total` decimal(10,2) DEFAULT 0,
```

**finoso.sql:**
```sql
`total` decimal(10,2) DEFAULT NULL,
```

**Acción:** Decidir cuál es el correcto y actualizar

---

#### **D) Índices/Foreign Keys diferentes:**

**BD actual:**
```sql
ALTER TABLE `orden`
  ADD CONSTRAINT `orden_ibfk_2` FOREIGN KEY (`codigo_descuento_id`) REFERENCES `codigo_descuento` (`id_codigo`);
```

**finoso.sql:**
```sql
-- NO tiene esta FK
```

**Acción:** Eliminar FK innecesaria

---

### **Paso 5: Crear Scripts de Sincronización** 🔧

Para cada diferencia encontrada, crea un script SQL:

#### **Ejemplo: Eliminar campo `cantidad` de `orden_detalle`**

**Archivo:** `database/eliminar_campo_cantidad.sql`
```sql
ALTER TABLE orden_detalle DROP COLUMN cantidad;
```

**Ejecutor:** `database/ejecutar_eliminar_cantidad.php`
```php
<?php
require_once '../admin/conexion.php';

echo "<h2>Eliminando campo cantidad...</h2>";

if ($conn->query("ALTER TABLE orden_detalle DROP COLUMN cantidad")) {
    echo "<p style='color: green;'>✅ Campo eliminado</p>";
} else {
    echo "<p style='color: red;'>❌ Error: " . $conn->error . "</p>";
}

$conn->close();
?>
```

#### **Ejemplo: Agregar campo `monto_pagado` a `orden`**

**Archivo:** `database/agregar_monto_pagado.sql`
```sql
ALTER TABLE orden 
ADD COLUMN monto_pagado decimal(10,2) DEFAULT NULL 
COMMENT 'Monto real que pagó el cliente' 
AFTER comprobante_verificado;
```

**Ejecutor:** Ya existe: `database/ejecutar_agregar_monto_pagado.php`

---

### **Paso 6: Ejecutar Scripts** ▶️

1. **BACKUP PRIMERO:**
   ```
   http://127.0.0.1/phpmyadmin/
   → Exportar → Método: Rápido → Formato: SQL → Continuar
   ```

2. **Ejecutar cada script:**
   ```
   https://finoso.store/database/ejecutar_[nombre_script].php
   ```

3. **Verificar resultado:**
   - Volver a `comparar_estructura_bd.php`
   - Confirmar que el cambio se aplicó

---

### **Paso 7: Actualizar finoso.sql** 📝

Una vez que tu BD actual esté **correcta y limpia**:

1. Exporta la estructura final:
   ```
   https://finoso.store/database/exportar_estructura_sql.php
   ```

2. Descarga el archivo generado

3. **Reemplaza** el contenido de `finoso.sql` con el nuevo

4. **Commit** a Git:
   ```bash
   git add finoso.sql
   git commit -m "Sincronización estructura BD - eliminados campos innecesarios"
   git push
   ```

---

## 📊 Ejemplo Práctico

### **Situación Actual:**

**BD de desarrollo tiene:**
- Tabla `auditoria_pagos` (vacía, experimental)
- Campo `orden.codigo_descuento_id` (no se usa)
- Campo `orden_detalle.cantidad` (siempre 1)

**finoso.sql tiene:**
- NO tiene `auditoria_pagos`
- NO tiene `orden.codigo_descuento_id`
- NO tiene `orden_detalle.cantidad`
- Tiene `orden.monto_pagado` (falta en BD)

---

### **Acciones a realizar:**

#### **1. Eliminar de BD actual:**
```sql
DROP TABLE IF EXISTS auditoria_pagos;
ALTER TABLE orden DROP COLUMN codigo_descuento_id;
ALTER TABLE orden_detalle DROP COLUMN cantidad;
```

Scripts:
- ✅ `ejecutar_eliminar_auditoria.php` (ya creado)
- ⏳ `ejecutar_eliminar_codigo_descuento_id.php` (crear)
- ⏳ `ejecutar_eliminar_cantidad.php` (crear)

#### **2. Agregar a BD actual:**
```sql
ALTER TABLE orden 
ADD COLUMN monto_pagado decimal(10,2) DEFAULT NULL 
AFTER comprobante_verificado;
```

Script:
- ✅ `ejecutar_agregar_monto_pagado.php` (ya existe)

---

### **Resultado Final:**

✅ BD actual = finoso.sql (misma estructura)  
✅ Sin campos innecesarios  
✅ Sin tablas experimentales  
✅ Todos los campos necesarios presentes

---

## 🎯 Checklist de Sincronización

Usa este checklist para asegurar que todo esté correcto:

### **Tablas Oficiales (deben estar en AMBOS):**
- [ ] `carrito`
- [ ] `codigo_descuento`
- [ ] `comentarios`
- [ ] `envios`
- [ ] `marca`
- [ ] `orden`
- [ ] `orden_detalle`
- [ ] `reloj`
- [ ] `reset_tokens`
- [ ] `usuario`
- [ ] `usuario_codigo_descuento`

### **Tablas Experimentales (NO deben estar):**
- [ ] ❌ `auditoria_pagos` (eliminada)
- [ ] ❌ `descuento_aplicado_reloj` (eliminada)

### **Campos Eliminados:**
- [ ] ❌ `orden.codigo_descuento_id`
- [ ] ❌ `orden.intentos_pago`
- [ ] ❌ `orden.fecha_ultima_subida`
- [ ] ❌ `orden_detalle.cantidad`

### **Campos Agregados:**
- [ ] ✅ `orden.monto_pagado`
- [ ] ✅ `usuario_codigo_descuento.id_reloj`

---

## ⚠️ Advertencias Importantes

### **NO ejecutes directamente en producción:**
- Los scripts están diseñados para **desarrollo**
- Siempre haz **BACKUP** antes de cualquier cambio
- Prueba primero en ambiente local

### **Si algo sale mal:**
1. **Restaura el backup**
2. Revisa los logs de error
3. Consulta la documentación
4. Ajusta el script y vuelve a intentar

---

## 📁 Archivos Creados

### **Herramientas de Comparación:**
- ✅ `database/comparar_estructura_bd.php` → Ver estructura HTML
- ✅ `database/exportar_estructura_sql.php` → Exportar SQL
- ✅ `database/index.html` → Panel principal

### **Scripts de Limpieza:**
- ✅ `database/ejecutar_eliminar_auditoria.php` → DROP auditoria_pagos
- ✅ `database/ejecutar_actualizar_usuario_codigo.php` → ADD id_reloj
- ✅ `database/ejecutar_fix_id_orden.php` → Fix id_orden NULL

### **Documentación:**
- ✅ `SINCRONIZACION_BD_FINOSO_SQL.md` → Este archivo

---

## 🚀 Empezar Ahora

**Paso 1:**
```
https://finoso.store/database/
```

**Paso 2:**
Click en "👁️ Ver Estructura Actual"

**Paso 3:**
Compara con `finoso.sql`

**Paso 4:**
Ejecuta los scripts necesarios

---

**Fecha:** 27 de octubre de 2025  
**Estado:** ✅ Herramientas listas para usar

