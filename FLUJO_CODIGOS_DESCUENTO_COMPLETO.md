# 🎟️ FLUJO COMPLETO - CÓDIGOS DE DESCUENTO

## 📊 ESTRUCTURA DE BASE DE DATOS

### Tabla 1: `codigo_descuento` (Códigos Maestros)
```sql
CREATE TABLE codigo_descuento (
  id_codigo INT PRIMARY KEY AUTO_INCREMENT,
  codigo VARCHAR(50) UNIQUE,
  porcentaje DECIMAL(5,2),
  fecha_expiracion DATE
);
```
**Propósito:** Almacenar todos los códigos de descuento disponibles

### Tabla 2: `usuario_codigo_descuento` (Asignación a Usuarios)
```sql
CREATE TABLE usuario_codigo_descuento (
  id_usuario_codigo INT PRIMARY KEY AUTO_INCREMENT,
  id_usuario INT,
  id_codigo INT,
  fecha_asignado DATETIME,
  fecha_usado DATETIME,
  id_orden INT,
  veces_usado INT DEFAULT 0,
  activo BOOLEAN DEFAULT TRUE,
  notas TEXT
);
```
**Propósito:** Rastrear qué códigos tiene cada usuario y su historial de uso

---

## 🔄 FLUJO COMPLETO

### 1️⃣ **USUARIO HACE UNA COMPRA**

```
Usuario → Selecciona productos → Checkout → Paga
                                                ↓
                                           Se crea ORDEN
```

### 2️⃣ **ADMIN APRUEBA LA ORDEN**

**Archivo:** `admin/php/acciones.php`

```php
// 1. Se genera código único
$codigo_descuento = 'FIN' . strtoupper(substr(uniqid(), -6));
// Ejemplo: "FINAB12CD"

// 2. Se guarda en codigo_descuento
INSERT INTO codigo_descuento (codigo, porcentaje, fecha_expiracion)
VALUES ('FINAB12CD', 10.00, '2026-01-31');
// Retorna: id_codigo = 15

// 3. Se asigna al usuario
INSERT INTO usuario_codigo_descuento (id_usuario, id_codigo, notas)
VALUES (5, 15, 'Código de agradecimiento por tu compra #123 🎉');

// 4. Se envía por email y WhatsApp
```

**Resultado:**
- ✅ Código creado en BD
- ✅ Código asignado al usuario
- ✅ Usuario recibe código por email
- ✅ Código visible en su perfil

### 3️⃣ **USUARIO VE SU CÓDIGO EN EL PERFIL**

**URL:** `perfil/perfil.html`

```
Perfil → Sección "Mis Códigos de Descuento"
         ↓
    [FINAB12CD]  10% OFF  [Copiar]
    📅 Válido hasta 31/01/2026
```

**Estados visuales:**
- 🟢 **DISPONIBLE** - Puede usarse
- ⚪ **USADO** - Ya fue utilizado
- 🔴 **EXPIRADO** - Fecha pasada

### 4️⃣ **USUARIO USA EL CÓDIGO (PRÓXIMA COMPRA)**

**Cuando el usuario aplica el código en el checkout:**

```sql
-- 1. Verificar que el código existe y es válido
SELECT * FROM usuario_codigo_descuento ucd
INNER JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
WHERE ucd.id_usuario = ? 
  AND cd.codigo = ?
  AND ucd.activo = 1
  AND (cd.fecha_expiracion IS NULL OR cd.fecha_expiracion >= CURDATE());

-- 2. Aplicar descuento a la orden
UPDATE orden 
SET codigo_descuento_id = ?
WHERE id_orden = ?;

-- 3. Marcar código como usado
UPDATE usuario_codigo_descuento
SET fecha_usado = NOW(),
    veces_usado = veces_usado + 1,
    id_orden = ?
WHERE id_usuario = ? AND id_codigo = ?;
```

**Resultado:**
- ✅ Descuento aplicado a la compra
- ✅ Código marcado como "USADO"
- ✅ En el perfil aparece con estado gris
- ✅ Ya no se puede volver a usar

---

## 💡 DISEÑO: ¿POR QUÉ DOS TABLAS?

### ❌ **DISEÑO MALO (Una sola tabla):**
```
codigo_descuento:
- codigo: "BIENVENIDA10"
- id_usuario: 5
- usado: false
```

**Problema:** Si quieres dar el mismo código "BIENVENIDA10" a 100 usuarios, tienes que crear 100 registros duplicados.

### ✅ **DISEÑO CORRECTO (Dos tablas):**

**Tabla 1 (Maestra):**
```
codigo_descuento:
id: 1, codigo: "BIENVENIDA10", porcentaje: 10
```

**Tabla 2 (Asignaciones):**
```
usuario_codigo_descuento:
id: 1, id_usuario: 5, id_codigo: 1, usado: false
id: 2, id_usuario: 8, id_codigo: 1, usado: false
id: 3, id_usuario: 12, id_codigo: 1, usado: true
...100 registros más
```

**Ventajas:**
- ✅ No hay duplicación de datos
- ✅ Rastreo individual por usuario
- ✅ Estadísticas precisas (cuántos usuarios lo usaron)
- ✅ Normalización correcta (3NF)
- ✅ Escalable para miles de usuarios

---

## 📝 EJEMPLOS DE CONSULTAS ÚTILES

### Ver códigos de un usuario:
```sql
SELECT cd.codigo, cd.porcentaje, cd.fecha_expiracion,
       ucd.fecha_asignado, ucd.fecha_usado, ucd.veces_usado
FROM usuario_codigo_descuento ucd
INNER JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
WHERE ucd.id_usuario = 5;
```

### Códigos disponibles de un usuario:
```sql
SELECT cd.codigo, cd.porcentaje
FROM usuario_codigo_descuento ucd
INNER JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
WHERE ucd.id_usuario = 5
  AND ucd.activo = 1
  AND ucd.fecha_usado IS NULL
  AND (cd.fecha_expiracion IS NULL OR cd.fecha_expiracion >= CURDATE());
```

### Estadísticas de uso de un código:
```sql
SELECT cd.codigo,
       COUNT(*) as total_asignados,
       SUM(CASE WHEN ucd.fecha_usado IS NOT NULL THEN 1 ELSE 0 END) as total_usados,
       SUM(ucd.veces_usado) as usos_totales
FROM usuario_codigo_descuento ucd
INNER JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
WHERE cd.id_codigo = 1
GROUP BY cd.codigo;
```

### Usuarios que nunca han usado sus códigos:
```sql
SELECT u.nombre, u.correo, COUNT(*) as codigos_sin_usar
FROM usuario u
INNER JOIN usuario_codigo_descuento ucd ON u.id_usuario = ucd.id_usuario
WHERE ucd.fecha_usado IS NULL
  AND ucd.activo = 1
GROUP BY u.id_usuario, u.nombre, u.correo
HAVING codigos_sin_usar > 0;
```

---

## 🎯 CASOS DE USO

### Caso 1: Código de Bienvenida (Masivo)
```sql
-- Crear código maestro
INSERT INTO codigo_descuento (codigo, porcentaje, fecha_expiracion)
VALUES ('BIENVENIDA10', 10, '2026-12-31');

-- Asignar a todos los usuarios nuevos
INSERT INTO usuario_codigo_descuento (id_usuario, id_codigo, notas)
SELECT id_usuario, 1, 'Código de bienvenida por registrarte'
FROM usuario
WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 7 DAY);
```

### Caso 2: Código Personal por Compra (Único)
```sql
-- Cada usuario recibe su código único después de comprar
INSERT INTO codigo_descuento (codigo, porcentaje, fecha_expiracion)
VALUES ('FINAB12CD', 10, '2026-01-31');

INSERT INTO usuario_codigo_descuento (id_usuario, id_codigo, notas)
VALUES (5, LAST_INSERT_ID(), 'Gracias por tu compra #123');
```

### Caso 3: Código Exclusivo VIP (Limitado)
```sql
-- Solo para usuarios con más de 3 compras
INSERT INTO codigo_descuento (codigo, porcentaje, fecha_expiracion)
VALUES ('VIP20', 20, '2026-06-30');

INSERT INTO usuario_codigo_descuento (id_usuario, id_codigo, notas)
SELECT u.id_usuario, 10, 'Código VIP - ¡Eres cliente preferente!'
FROM usuario u
INNER JOIN orden o ON u.id_usuario = o.id_usuario
WHERE o.estado = 'entregado'
GROUP BY u.id_usuario
HAVING COUNT(o.id_orden) >= 3;
```

---

## 🔐 VALIDACIONES IMPORTANTES

### Antes de aplicar un código:
1. ✅ Verificar que el usuario tiene el código asignado
2. ✅ Verificar que no ha expirado
3. ✅ Verificar que no se ha usado (o permitir múltiples usos si aplica)
4. ✅ Verificar que está activo
5. ✅ Aplicar el descuento correcto
6. ✅ Marcar como usado después de confirmar el pago

---

## 📊 REPORTES Y ANÁLISIS

### Top 10 códigos más usados:
```sql
SELECT cd.codigo, cd.porcentaje,
       COUNT(DISTINCT ucd.id_usuario) as usuarios_totales,
       SUM(ucd.veces_usado) as usos_totales
FROM usuario_codigo_descuento ucd
INNER JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
WHERE ucd.fecha_usado IS NOT NULL
GROUP BY cd.id_codigo, cd.codigo, cd.porcentaje
ORDER BY usos_totales DESC
LIMIT 10;
```

### Códigos próximos a expirar:
```sql
SELECT cd.codigo, cd.fecha_expiracion,
       DATEDIFF(cd.fecha_expiracion, CURDATE()) as dias_restantes,
       COUNT(*) as usuarios_sin_usar
FROM codigo_descuento cd
INNER JOIN usuario_codigo_descuento ucd ON cd.id_codigo = ucd.id_codigo
WHERE cd.fecha_expiracion BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
  AND ucd.fecha_usado IS NULL
GROUP BY cd.id_codigo, cd.codigo, cd.fecha_expiracion
ORDER BY dias_restantes ASC;
```

---

## ✅ ESTADO ACTUAL DEL SISTEMA

### ✓ Implementado:
1. Tabla `codigo_descuento` - Códigos maestros
2. Tabla `usuario_codigo_descuento` - Asignaciones
3. Generación automática de códigos al aprobar orden
4. Asignación automática al usuario
5. Vista en perfil de usuario
6. Botón copiar código
7. Estados visuales (disponible/usado/expirado)
8. Envío por email y WhatsApp

### 🔜 Pendiente (Futuro):
1. Sistema de aplicar códigos en el checkout
2. Marcar códigos como usados al aplicarlos
3. Notificaciones de expiración
4. Panel admin para asignar códigos masivamente
5. Estadísticas en dashboard admin

---

**Última actualización:** Octubre 2025  
**Sistema:** FINOSO - Gestión de Códigos de Descuento

