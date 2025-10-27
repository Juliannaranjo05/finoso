# 🎟️ MÓDULO DE CÓDIGOS DE DESCUENTO PARA USUARIOS

## 📋 Descripción General

Sistema completo de gestión de códigos de descuento personales para usuarios en FINOSO. Los usuarios pueden ver, gestionar y usar sus códigos de descuento exclusivos desde su perfil.

---

## ✅ IMPLEMENTACIÓN COMPLETADA

### 1. **Base de Datos** ✓

**Tabla creada:** `usuario_codigo_descuento` (agregada en `finoso.sql`)

```sql
CREATE TABLE `usuario_codigo_descuento` (
  `id_usuario_codigo` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_codigo` int(11) NOT NULL,
  `fecha_asignado` datetime DEFAULT current_timestamp(),
  `fecha_usado` datetime DEFAULT NULL,
  `id_orden` int(11) DEFAULT NULL,
  `veces_usado` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `notas` text DEFAULT NULL,
  PRIMARY KEY (`id_usuario_codigo`),
  UNIQUE KEY `unico_usuario_codigo` (`id_usuario`,`id_codigo`)
);
```

**Relaciones:**
- `id_usuario` → `usuario.id_usuario` (CASCADE)
- `id_codigo` → `codigo_descuento.id_codigo` (CASCADE)
- `id_orden` → `orden.id_orden` (SET NULL)

### 2. **Frontend - Interfaz de Usuario** ✓

**Ubicación:** Sección de perfil de usuario (`perfil/perfil.html`)

**Características:**
- 🎯 Sección dedicada "Mis Códigos de Descuento"
- 📊 Grid responsive de tarjetas de códigos
- 🏷️ Estados visuales: Disponible, Usado, Expirado
- 📋 Botón "Copiar" para códigos disponibles
- ⏰ Alertas de expiración próxima (3 días o menos)
- 💡 Notas personalizadas por código
- 📈 Estadísticas de uso

**Estilos implementados:**
- Tarjetas con gradientes y bordes dorados
- Animaciones hover
- Estados diferenciados por color
- Diseño responsive para móviles
- Spinner de carga

### 3. **Backend - API PHP** ✓

**Archivo:** `perfil/php/obtener_codigos_usuario.php`

**Funcionalidad:**
- Obtiene códigos asignados al usuario autenticado
- Calcula días restantes para expiración
- Determina estado automático (disponible/usado/expirado)
- Ordena por relevancia (disponibles primero)
- Retorna estadísticas de uso

**Respuesta JSON:**
```json
{
  "success": true,
  "codigos": [
    {
      "codigo": "FINOSO10",
      "porcentaje": 10,
      "fecha_expiracion": "2025-12-31",
      "dias_para_expirar": 45,
      "estado": "disponible",
      "veces_usado": 0,
      "notas": "Código de bienvenida"
    }
  ],
  "stats": {
    "total": 5,
    "disponibles": 3,
    "usados": 1,
    "expirados": 1
  }
}
```

### 4. **JavaScript - Lógica del Cliente** ✓

**Archivo:** `perfil/js/perfil.js`

**Funciones principales:**
- `cargarCodigosDescuento()` - Carga códigos del usuario
- `crearCardCodigo(codigo)` - Genera HTML de cada tarjeta
- `copiarCodigo(codigo, boton)` - Copia al portapapeles con feedback visual

**Características:**
- Carga automática al entrar al perfil
- Manejo de estados de carga
- Feedback visual al copiar
- Manejo de errores

---

## 🎨 ESTADOS DE CÓDIGO

### 1. **Disponible** 🟢
- Color: Verde (#4CAF50)
- Puede ser usado
- Botón "Copiar" activo
- Muestra fecha de expiración si aplica

### 2. **Usado** ⚪
- Color: Gris (#9E9E9E)
- Ya fue usado en una compra
- Muestra fecha de uso
- Sin botón copiar

### 3. **Expirado** 🔴
- Color: Rojo (#F44336)
- Fecha de expiración pasada
- Opacidad reducida
- Sin botón copiar

### 4. **Urgente** ⚠️
- Disponible pero expira en ≤3 días
- Alerta roja visible
- Botón copiar activo

---

## 👤 EXPERIENCIA DE USUARIO

### Flujo Normal:
1. Usuario inicia sesión
2. Va a su perfil
3. Ve sección "Mis Códigos de Descuento" después de estadísticas
4. Visualiza sus códigos en tarjetas organizadas
5. Copia código con un clic
6. Usa código al finalizar compra

### Estados Visuales:
- **Sin códigos:** Mensaje amigable con ícono
- **Cargando:** Spinner con texto "Cargando tus códigos..."
- **Con códigos:** Grid de tarjetas ordenadas por prioridad
- **Error:** Mensaje de error con opción de reintentar

---

## 🔧 CÓMO ASIGNAR CÓDIGOS (ADMIN)

### Método 1: Asignación Manual (SQL)
```sql
INSERT INTO usuario_codigo_descuento (id_usuario, id_codigo, notas) 
VALUES (
    1, -- ID del usuario
    5, -- ID del código de descuento
    'Código exclusivo por compra frecuente' -- Opcional
);
```

### Método 2: Asignación por Registro
```sql
-- Dar código de bienvenida a todos los nuevos usuarios
INSERT INTO usuario_codigo_descuento (id_usuario, id_codigo, notas)
SELECT id_usuario, 10, 'Código de bienvenida 🎉'
FROM usuario 
WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 7 DAY);
```

### Método 3: Asignación por Compra
```sql
-- Dar código después de X compras
INSERT INTO usuario_codigo_descuento (id_usuario, id_codigo, notas)
SELECT u.id_usuario, 15, '¡Gracias por tu fidelidad! 💎'
FROM usuario u
INNER JOIN orden o ON u.id_usuario = o.id_usuario
WHERE o.estado = 'entregado'
GROUP BY u.id_usuario
HAVING COUNT(o.id_orden) >= 3;
```

---

## 📊 CONSULTAS ÚTILES

### Ver códigos de un usuario:
```sql
SELECT u.nombre, cd.codigo, cd.porcentaje, ucd.fecha_asignado, ucd.veces_usado
FROM usuario_codigo_descuento ucd
INNER JOIN usuario u ON ucd.id_usuario = u.id_usuario
INNER JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
WHERE u.id_usuario = 1;
```

### Códigos disponibles por usuario:
```sql
SELECT u.nombre, COUNT(*) as codigos_disponibles
FROM usuario_codigo_descuento ucd
INNER JOIN usuario u ON ucd.id_usuario = u.id_usuario
INNER JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
WHERE ucd.activo = 1 
  AND ucd.fecha_usado IS NULL
  AND (cd.fecha_expiracion IS NULL OR cd.fecha_expiracion >= CURDATE())
GROUP BY u.id_usuario, u.nombre;
```

### Códigos más usados:
```sql
SELECT cd.codigo, cd.porcentaje, SUM(ucd.veces_usado) as total_usos
FROM usuario_codigo_descuento ucd
INNER JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
GROUP BY cd.id_codigo, cd.codigo, cd.porcentaje
ORDER BY total_usos DESC;
```

---

## 🎯 PRÓXIMAS MEJORAS SUGERIDAS

### Fase 2 (Futuro):
1. **Panel Admin Web:**
   - Interfaz para asignar códigos masivamente
   - Ver estadísticas de uso por usuario
   - Crear campañas de códigos

2. **Notificaciones:**
   - Email/WhatsApp cuando reciben nuevo código
   - Alerta 3 días antes de expirar
   - Recordatorio de códigos sin usar

3. **Gamificación:**
   - Códigos por logros
   - Códigos por referidos
   - Códigos especiales en cumpleaños

4. **Histórico:**
   - Ver todos los códigos usados
   - Detalles de compras con cada código
   - Ahorro total generado

---

## 📱 RESPONSIVE DESIGN

✅ **Móvil (< 768px):**
- Grid de 1 columna
- Tarjetas full-width
- Botones táctiles grandes

✅ **Tablet (768px - 1024px):**
- Grid de 2 columnas
- Tarjetas medianas

✅ **Desktop (> 1024px):**
- Grid de 3+ columnas (auto-fit)
- Hover effects completos

---

## 🔒 SEGURIDAD

✅ **Implementado:**
- Verificación de sesión PHP
- Consultas preparadas (prepared statements)
- Solo códigos del usuario autenticado
- Validación de permisos

---

## 📝 NOTAS TÉCNICAS

**Archivos Modificados/Creados:**
1. `finoso.sql` - Estructura de BD
2. `perfil/perfil.html` - Interfaz HTML
3. `perfil/css/perfil.css` - Estilos
4. `perfil/js/perfil.js` - Lógica JavaScript
5. `perfil/php/obtener_codigos_usuario.php` - Backend

**Dependencias:**
- Sesiones PHP activas
- jQuery NO requerido (vanilla JS)
- Bootstrap NO requerido (CSS custom)

**Navegadores Soportados:**
- Chrome/Edge (últimas 2 versiones)
- Firefox (últimas 2 versiones)
- Safari (últimas 2 versiones)
- Móviles modernos

---

## ✨ CARACTERÍSTICAS DESTACADAS

1. **UX Intuitiva:** Interface clara y fácil de usar
2. **Feedback Visual:** Animaciones y confirmaciones inmediatas
3. **Accesibilidad:** Estados claros con colores y textos
4. **Performance:** Carga rápida y eficiente
5. **Escalable:** Fácil agregar nuevas funcionalidades

---

**Estado del Módulo:** ✅ **COMPLETAMENTE FUNCIONAL**

**Última Actualización:** Octubre 2025

**Desarrollado para:** FINOSO - Sistema de Gestión de Relojes

