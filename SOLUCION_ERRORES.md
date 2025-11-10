# 🔧 SOLUCIÓN DE ERRORES

## Errores Encontrados:

### 1. ❌ `Unknown column 'o.monto_pagado' in 'field list'`
**Causa:** La columna `monto_pagado` no existe en la tabla `orden`.

**Solución:** Ejecutar el script SQL.

### 2. ❌ `Cannot read properties of null (reading 'addEventListener')`
**Causa:** El código intenta acceder a elementos que no existen en la página de perfil.

**Solución:** Agregar verificaciones antes de usar `addEventListener`.

---

## 🚀 Pasos para Arreglar:

### PASO 1: Actualizar la Base de Datos ⚠️ **EJECUTAR PRIMERO**

Abre en tu navegador:
```
https://finoso.store/database/ejecutar_agregar_monto_pagado.php
```

Esto agregará automáticamente las columnas:
- `monto_pagado`
- `intentos_pago`
- `fecha_ultima_subida`

**Resultado esperado:**
```
✅ Consultas exitosas: 3
❌ Errores: 0
🎉 ¡Base de datos actualizada correctamente!
```

---

### PASO 2: Verificar que funcione

1. **Abrir perfil de usuario:**
   ```
   https://finoso.store/perfil/perfil.html
   ```

2. **Ya NO debe aparecer:**
   - ❌ Error de JavaScript en consola
   - ❌ Error de SQL "Unknown column"

3. **Debe funcionar:**
   - ✅ Historial de compras carga correctamente
   - ✅ Botones aparecen según estado
   - ✅ Si hay orden rechazada por monto, muestra diferencia

---

## 📋 Cambios Aplicados:

### Archivos Creados:
1. ✅ `database/ejecutar_agregar_monto_pagado.php` - Script auto-ejecutable

### Archivos Modificados:
1. ✅ `perfil/js/perfil.js` - Verificación de `cerrarSesionBtn`
2. ✅ `login/js/sesion.js` - Verificación de `iconoLogin`

---

## 🧪 Cómo Probar:

### Prueba 1: Rechazar orden por monto
1. Ir al panel de admin
2. Seleccionar una orden pendiente
3. Click "Rechazar"
4. Motivo: "El monto del comprobante no coincide"
5. **Ingresar monto pagado:** `15000` (ejemplo)
6. Click "Rechazar"

### Prueba 2: Ver en perfil de usuario
1. Ir a perfil del usuario
2. Ver la orden rechazada
3. Debe aparecer botón: **"💰 Completar Pago"**
4. Click en el botón
5. Debe mostrar:
   ```
   Total del pedido: $20.000
   Ya pagaste: $15.000
   Falta pagar: $5.000
   ```

---

## ✅ Checklist Final:

- [ ] Ejecuté `ejecutar_agregar_monto_pagado.php`
- [ ] Vi mensaje "🎉 ¡Base de datos actualizada correctamente!"
- [ ] Perfil carga sin errores de JavaScript
- [ ] Historial carga correctamente
- [ ] Puedo rechazar órdenes por monto
- [ ] Se guarda el monto pagado
- [ ] Usuario ve diferencia correcta

---

## 🆘 Si Sigue con Errores:

### Error: "Duplicate column name 'monto_pagado'"
**Significa:** La columna ya existe (ok)
**Solución:** Ignorar, ya está arreglado

### Error: "Access denied"
**Significa:** Problema de permisos de BD
**Solución:** Verificar usuario/contraseña en `admin/conexion.php`

### Error: JavaScript sigue apareciendo
**Solución:** 
1. Refrescar página con `Ctrl+F5` (limpiar caché)
2. Abrir consola (F12) y verificar qué línea exacta
3. Compartir el error completo

---

**Todo listo para usar el sistema de recuperación de órdenes!** ✨

