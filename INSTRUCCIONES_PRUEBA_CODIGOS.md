# 🚀 INSTRUCCIONES COMPLETAS - PRUEBA DE CÓDIGOS DE DESCUENTO

## 📝 PROBLEMA DETECTADO:
Los logs del sistema no están habilitados, por lo que no podíamos ver qué está pasando cuando se genera el código.

## ✅ SOLUCIÓN IMPLEMENTADA:
Se creó un **sistema de logs propio** dentro del proyecto que escribe directamente en un archivo.

---

## 🔧 PASOS PARA HACER LA PRUEBA COMPLETA:

### 1️⃣ **VERIFICAR QUE EL LOGGER FUNCIONA**

Abre en tu navegador:
```
https://finoso.store/admin/test_logger.php
```

**¿Qué deberías ver?**
- ✅ Mensaje "Logs escritos correctamente"
- 📄 Contenido del archivo de logs con líneas de prueba
- Símbolos: ✓ (éxito), ✗ (error), ⚠ (advertencia)

**Si no funciona:**
- Verifica que el directorio `admin/logs` existe
- Verifica permisos de escritura en esa carpeta

---

### 2️⃣ **CREAR LA TABLA EN LA BD**

Abre:
```
https://finoso.store/database/ejecutar_crear_usuario_codigo.php
```

**¿Qué deberías ver?**
- ✅ "Instalación Completada"
- Tabla `usuario_codigo_descuento` creada
- Todas las claves foráneas configuradas

**Solo necesitas hacer esto UNA VEZ**

---

### 3️⃣ **RESETEAR LOS RELOJES**

Abre:
```
https://finoso.store/database/resetear_relojes_prueba.php
```

**¿Qué hace?**
- Pone los relojes 1, 2, 3, 4 como disponibles
- `disponible = 1` y `vendido = 0`

**Ejecuta esto cada vez que quieras hacer una nueva prueba**

---

### 4️⃣ **ABRIR EL VISOR DE LOGS**

Abre en otra pestaña (déjala abierta):
```
https://finoso.store/admin/ver_logs_debug.php
```

**Características:**
- 🔄 Se auto-refresca cada 3 segundos
- 📊 Muestra estadísticas (Total, Éxitos, Errores, Advertencias)
- 🎨 Colores: Verde = éxito, Rojo = error, Amarillo = advertencia
- 🗑️ Botón para limpiar logs

---

### 5️⃣ **HACER LA COMPRA DE PRUEBA**

1. **Inicia sesión** en tu sitio con un usuario
2. **Ve al catálogo**: https://finoso.store/catalogo/catalogo.html
3. **Verifica que aparezcan los 4 relojes** disponibles
4. **Agrega uno al carrito**
5. **Completa la compra con Nequi**
6. **Paga y sube el comprobante**

---

### 6️⃣ **APROBAR LA ORDEN**

1. **Ve al panel admin**: https://finoso.store/admin/panel.php
2. **Busca la orden pendiente**
3. **Haz clic en "Aprobar"**

**EN ESTE MOMENTO se generará el código y se escribirán los logs**

---

### 7️⃣ **REVISAR LOS LOGS**

**Ve al visor de logs** (que dejaste abierto):
```
https://finoso.store/admin/ver_logs_debug.php
```

**🔍 BUSCA ESTAS LÍNEAS:**

#### ✅ **SI TODO FUNCIONA BIEN, VERÁS:**
```
========== INICIO GENERACIÓN DE CÓDIGO ==========
[INFO] [APROBAR] ID Orden: 123
[INFO] [APROBAR] Código generado: FINAB12CD
[SUCCESS] [APROBAR] ✓ Código insertado en codigo_descuento - ID: 15
[INFO] [APROBAR] ID Usuario de la orden: 5
[INFO] [APROBAR] Intentando asignar código al usuario...
[INFO] [APROBAR] Query preparada OK
[DEBUG] [APROBAR] Params: id_usuario=5, id_codigo=15, notas=...
[SUCCESS] [APROBAR] ✓✓✓ CÓDIGO ASIGNADO EXITOSAMENTE ✓✓✓
[SUCCESS] [APROBAR] ID Asignación: 8
[SUCCESS] [APROBAR] Código: FINAB12CD → Usuario: 5
========== FIN GENERACIÓN DE CÓDIGO ==========
```

**Estadísticas mostrarán:**
- Éxitos: 3 o más (en verde)
- Errores: 0 (en rojo)

---

#### ❌ **SI HAY ERROR, VERÁS:**
```
[ERROR] [APROBAR] ✗✗✗ ERROR al asignar código ✗✗✗
[ERROR] [APROBAR] Error MySQL: Table 'usuario_codigo_descuento' doesn't exist
[ERROR] [APROBAR] Error Número: 1146
```

**Solución:** Ejecuta el paso 2 de nuevo (crear tabla)

---

#### ⚠️ **SI NO HAY USUARIO, VERÁS:**
```
[INFO] [APROBAR] ID Usuario de la orden: NULL
[WARNING] [APROBAR] ⚠ Código NO asignado - Usuario inválido o NULL
```

**Solución:** La orden no tiene usuario asignado. Asegúrate de iniciar sesión antes de comprar.

---

### 8️⃣ **VERIFICAR EN EL PERFIL DEL USUARIO**

1. **Ve al perfil**: https://finoso.store/perfil/perfil.html
2. **Busca la sección**: "🎟️ Mis Códigos de Descuento"
3. **Deberías ver**:
   - El código generado (ej: FINAB12CD)
   - Estado: DISPONIBLE (verde)
   - Porcentaje: 10% OFF
   - Fecha de expiración
   - Botón "Copiar"

---

### 9️⃣ **VERIFICAR EN LA BASE DE DATOS** (opcional)

Abre phpMyAdmin:
```
http://127.0.0.1/phpmyadmin
```

**Verifica en la tabla `codigo_descuento`:**
```sql
SELECT * FROM codigo_descuento ORDER BY id_codigo DESC LIMIT 1;
```
Deberías ver el código recién creado.

**Verifica en la tabla `usuario_codigo_descuento`:**
```sql
SELECT * FROM usuario_codigo_descuento ORDER BY id_usuario_codigo DESC LIMIT 1;
```
Deberías ver la asignación del código al usuario.

---

## 🐛 SOLUCIÓN DE PROBLEMAS:

### Problema: "No se ven logs en el visor"
**Causas posibles:**
1. El directorio `admin/logs` no existe → Verificar con `test_logger.php`
2. No hay permisos de escritura → Dar permisos 777 a `admin/logs`
3. PHP no puede escribir archivos → Verificar configuración de PHP

**Solución:**
```bash
# Windows (CMD como administrador)
cd C:\xampp\htdocs\finoso\admin
mkdir logs
icacls logs /grant Everyone:F
```

---

### Problema: "Tabla no existe"
**Mensaje:** `Table 'usuario_codigo_descuento' doesn't exist`

**Solución:**
Ejecuta de nuevo: https://finoso.store/database/ejecutar_crear_usuario_codigo.php

---

### Problema: "ID Usuario es NULL"
**En logs:** `[WARNING] ID Usuario de la orden: NULL`

**Causa:** La orden se creó sin usuario (compra como invitado)

**Solución:**
1. Asegúrate de **iniciar sesión** antes de comprar
2. Verifica que la sesión esté activa
3. En `admin/panel.php`, verifica que la orden tenga `id_usuario`

---

### Problema: "No aparece el código en el perfil"
**Posibles causas:**
1. El código no se asignó (revisar logs)
2. Error en `obtener_codigos_usuario.php`
3. Usuario incorrecto

**Verificar:**
```sql
-- En phpMyAdmin:
SELECT * FROM usuario_codigo_descuento WHERE id_usuario = TU_ID_USUARIO;
```

Si no aparece nada, el código no se asignó. Revisa los logs.

---

## 📁 ARCHIVOS IMPORTANTES:

### Logs y Debug:
- `admin/logs/codigos_descuento.log` - Archivo de logs interno
- `admin/ver_logs_debug.php` - Visor de logs con auto-refresh
- `admin/test_logger.php` - Prueba del sistema de logs
- `admin/php/logger.php` - Sistema de logs personalizado

### Base de Datos:
- `database/ejecutar_crear_usuario_codigo.php` - Crear tabla
- `database/resetear_relojes_prueba.php` - Resetear relojes
- `finoso.sql` - Estructura completa de BD

### Backend:
- `admin/php/acciones.php` - Genera código al aprobar orden
- `perfil/php/obtener_codigos_usuario.php` - Obtiene códigos del usuario

### Frontend:
- `perfil/perfil.html` - Muestra códigos en el perfil
- `perfil/js/perfil.js` - Carga y muestra códigos

---

## 📋 CHECKLIST DE VERIFICACIÓN:

Antes de hacer la prueba, asegúrate de:

- [ ] ✅ El logger funciona (`test_logger.php` muestra logs)
- [ ] ✅ La tabla `usuario_codigo_descuento` existe
- [ ] ✅ Los relojes están disponibles (resetear si es necesario)
- [ ] ✅ El visor de logs está abierto
- [ ] ✅ Tienes un usuario con sesión iniciada
- [ ] ✅ El servidor Apache y MySQL están corriendo

Durante la prueba:

- [ ] ✅ La compra se completó correctamente
- [ ] ✅ La orden aparece en el admin
- [ ] ✅ Al aprobar, aparecen logs en el visor
- [ ] ✅ Los logs muestran "CÓDIGO ASIGNADO EXITOSAMENTE"
- [ ] ✅ El código aparece en el perfil del usuario
- [ ] ✅ Se puede copiar el código

---

## 🎯 ¿QUÉ ESPERAR?

### ✅ ESCENARIO EXITOSO:
1. Apruebas la orden
2. Logs muestran: `✓✓✓ CÓDIGO ASIGNADO EXITOSAMENTE ✓✓✓`
3. Código aparece en perfil del usuario
4. Estado: DISPONIBLE (verde)
5. Puedes copiar el código

### ❌ ESCENARIO CON ERROR:
1. Apruebas la orden
2. Logs muestran: `✗✗✗ ERROR al asignar código ✗✗✗`
3. Logs muestran el error MySQL específico
4. El código NO aparece en el perfil

### ⚠️ ESCENARIO SIN USUARIO:
1. Apruebas la orden
2. Logs muestran: `⚠ Código NO asignado - Usuario inválido o NULL`
3. El código se envía por email pero NO aparece en perfil
4. (Normal si la compra fue sin sesión)

---

## 🆘 SI NADA FUNCIONA:

1. **Limpia los logs:**
   - Ve a `ver_logs_debug.php`
   - Haz clic en "Limpiar Logs"

2. **Resetea los relojes:**
   - `resetear_relojes_prueba.php`

3. **Prueba el logger:**
   - `test_logger.php`
   - Si no funciona, el problema es de permisos

4. **Verifica la BD:**
   - Abre phpMyAdmin
   - Verifica que existe `usuario_codigo_descuento`

5. **Intenta de nuevo:**
   - Nueva compra
   - Nueva aprobación
   - Revisa logs

---

**¿Listo?** Empieza por el paso 1 y cuéntame qué ves en cada paso. 🚀

**Última actualización:** Octubre 2025

