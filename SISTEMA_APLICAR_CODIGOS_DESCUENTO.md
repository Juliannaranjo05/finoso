# 🎟️ SISTEMA COMPLETO DE CÓDIGOS DE DESCUENTO

## ✅ FUNCIONALIDADES IMPLEMENTADAS

### 1. **Aplicar Código de Descuento**
- Usuario ingresa código en el formulario de compra
- Sistema valida que el código:
  - ✅ Existe en la base de datos
  - ✅ No ha expirado
  - ✅ Está asignado al usuario (si hay sesión activa)
  - ✅ No ha sido usado previamente
- Aplica el descuento al precio del reloj
- Guarda el código en la sesión para usarlo en la orden

### 2. **Persistencia del Descuento**
- El descuento se guarda en `localStorage`
- Si el usuario recarga la página o sale y vuelve, el descuento permanece aplicado
- Persiste durante 24 horas
- Input del código se deshabilita una vez aplicado
- Botón cambia a "Aplicado ✓"

### 3. **Marcar Código como Usado**
- Cuando se completa la compra (sube comprobante), el código se marca automáticamente como usado
- Actualiza `usuario_codigo_descuento`:
  - ✅ `fecha_usado` = fecha actual
  - ✅ `id_orden` = ID de la orden creada
  - ✅ `veces_usado` = incrementa en 1
  - ✅ `activo` = 0 (desactivado)
- El código NO se puede volver a usar

### 4. **Visualización en el Perfil**
- Códigos usados aparecen con estado "USADO" (gris)
- Códigos disponibles aparecen con estado "DISPONIBLE" (verde)
- Muestra la fecha de uso y la orden asociada

---

## 📁 ARCHIVOS CREADOS/MODIFICADOS

### **Nuevos Archivos:**

#### 1. `informacion/php/aplicar_codigo_descuento.php`
Backend para validar y aplicar códigos
```php
- Valida código
- Verifica pertenencia al usuario
- Verifica que no esté expirado/usado
- Guarda en sesión PHP
- Retorna respuesta JSON
```

### **Archivos Modificados:**

#### 2. `informacion/informacion.html`
Frontend con lógica de códigos
```javascript
- Función aplicarDescuento() mejorada
- Función cargarCodigoAplicado() nueva
- Persistencia en localStorage
- Feedback visual mejorado
- Auto-carga al iniciar página
```

#### 3. `informacion/php/subir_comprobante.php`
Marca código como usado al crear orden
```php
- Lee código de la sesión
- Actualiza usuario_codigo_descuento
- Marca como usado con fecha y orden
- Limpia sesión después
- Logs detallados
```

---

## 🔄 FLUJO COMPLETO DEL SISTEMA

### **PASO 1: Usuario recibe código**
```
Orden aprobada → Código generado (FINXXXXXX) → Asignado al usuario → Email enviado
```

### **PASO 2: Usuario aplica código**
```
1. Usuario va a comprar un reloj
2. En el formulario ingresa el código
3. Hace clic en "Aplicar"
4. Sistema valida:
   - ¿Existe? ✓
   - ¿No expiró? ✓
   - ¿Le pertenece? ✓
   - ¿No está usado? ✓
5. Descuento aplicado al precio
6. Mensaje verde: "Descuento aplicado: -$XXX (10% OFF)"
7. Guarda en localStorage + sesión PHP
```

### **PASO 3: Usuario completa la compra**
```
1. Llena el formulario de envío
2. Sube comprobante de pago
3. Sistema crea la orden
4. Lee el código de la sesión
5. Marca el código como usado en BD:
   - fecha_usado = NOW()
   - id_orden = nuevo ID
   - veces_usado = 1
   - activo = 0
6. Limpia la sesión
```

### **PASO 4: Código marcado como usado**
```
1. En el perfil del usuario, el código aparece como "USADO"
2. Si intenta usar el mismo código de nuevo: "Ya utilizaste este código anteriormente"
3. El código queda asociado a la orden donde se usó
```

### **PASO 5: Persistencia**
```
Si el usuario:
- Recarga la página → Código sigue aplicado ✓
- Cierra y vuelve a abrir → Código sigue aplicado ✓ (24 horas)
- Completa la compra → Código marcado como usado ✓
```

---

## 🧪 INSTRUCCIONES DE PRUEBA

### **PRUEBA COMPLETA (END-TO-END):**

#### **1. Preparación:**
```
1. Resetea relojes:
   https://finoso.store/database/resetear_relojes_prueba.php

2. Limpia localStorage del navegador:
   F12 → Console → localStorage.clear()

3. Inicia sesión como usuario
```

#### **2. Genera un código:**
```
1. Compra un reloj
2. Admin aprueba la orden
3. Verifica que se generó el código en:
   - phpMyAdmin: usuario_codigo_descuento
   - Perfil: Mis Códigos de Descuento
4. Copia el código (ejemplo: FINAB12CD)
```

#### **3. Aplica el código en una nueva compra:**
```
1. Ve al catálogo
2. Selecciona otro reloj
3. En el formulario de compra, ingresa el código
4. Clic en "Aplicar"
5. Verifica:
   ✓ Mensaje verde: "Descuento aplicado: -$XXX (10% OFF)"
   ✓ Precio del reloj se redujo
   ✓ Input del código se deshabilitó
   ✓ Botón cambió a "Aplicado ✓"
```

#### **4. Prueba la persistencia:**
```
1. Recarga la página (F5)
2. Verifica:
   ✓ El descuento sigue aplicado
   ✓ El mensaje verde sigue visible
   ✓ El input sigue deshabilitado
3. Cierra la pestaña y vuelve a abrir
4. Verifica:
   ✓ El descuento persiste
```

#### **5. Completa la compra:**
```
1. Llena el formulario de envío
2. Sube comprobante de Nequi
3. Espera la confirmación
```

#### **6. Verifica en la BD:**
```sql
-- Ver el código usado
SELECT * FROM usuario_codigo_descuento 
WHERE id_usuario = TU_ID_USUARIO 
ORDER BY id_usuario_codigo DESC 
LIMIT 1;
```

**Deberías ver:**
- ✅ `fecha_usado`: con fecha y hora
- ✅ `id_orden`: con el ID de la orden nueva
- ✅ `veces_usado`: 1
- ✅ `activo`: 0

#### **7. Verifica en el perfil:**
```
1. Ve a tu perfil: perfil/perfil.html
2. Sección "Mis Códigos de Descuento"
3. El código debe aparecer como "USADO" (gris)
4. Debe mostrar la orden asociada
```

#### **8. Intenta usar el mismo código de nuevo:**
```
1. Ve a comprar otro reloj
2. Ingresa el mismo código
3. Clic en "Aplicar"
4. Debería mostrar:
   "Ya utilizaste este código anteriormente."
```

---

## 📊 TABLAS DE BASE DE DATOS

### **`codigo_descuento`** (Códigos maestros)
```
- id_codigo
- codigo (FINXXXXXX)
- porcentaje (10.00)
- fecha_expiracion (2026-01-31)
```

### **`usuario_codigo_descuento`** (Asignación y uso)
```
- id_usuario_codigo
- id_usuario → quien tiene el código
- id_codigo → referencia al código maestro
- fecha_asignado → cuándo se le dio
- fecha_usado → cuándo lo usó (NULL = no usado)
- id_orden → en qué orden lo usó (NULL = no usado)
- veces_usado → contador (0 o 1)
- activo → 1=disponible, 0=usado
- notas → "Código de agradecimiento por tu compra #XX"
```

### **`orden`** (Órdenes de compra)
```
- id_orden
- id_usuario
- total
- metodo_pago
- estado
- ... (otros campos)
```

---

## 🔍 LOGS Y DEBUG

### **Ver logs del sistema:**
```
https://finoso.store/admin/ver_logs_debug.php
```

### **Buscar en logs:**
```
[APLICAR-CODIGO] → Logs de aplicación de código
[NEQUI-INDIVIDUAL] → Logs de marcado como usado
[SUCCESS] → Operaciones exitosas
[ERROR] → Errores
[WARNING] → Advertencias
```

### **Ejemplo de logs exitosos:**
```
[INFO] [APLICAR-CODIGO] Intento de aplicación - Código: FINAB12CD
[DEBUG] [APLICAR-CODIGO] Búsqueda de código - Encontrado: SÍ
[SUCCESS] [APLICAR-CODIGO] ✓ Código aplicado exitosamente - 10% de descuento
[INFO] [NEQUI-INDIVIDUAL] Código aplicado detectado en sesión: FINAB12CD
[INFO] [NEQUI-INDIVIDUAL] ✓ Código marcado como usado
```

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### **Problema: "Este código no está asignado a tu cuenta"**
**Causa:** El código no está en `usuario_codigo_descuento` para ese usuario

**Solución:**
```sql
-- Asignar código al usuario
INSERT INTO usuario_codigo_descuento (id_usuario, id_codigo, notas) 
VALUES (TU_ID_USUARIO, ID_DEL_CODIGO, 'Código de prueba');
```

### **Problema: "Ya utilizaste este código anteriormente"**
**Causa:** El código tiene `fecha_usado` o `activo = 0`

**Solución:**
```sql
-- Reactivar código (solo para pruebas)
UPDATE usuario_codigo_descuento 
SET fecha_usado = NULL, 
    id_orden = NULL, 
    veces_usado = 0, 
    activo = 1 
WHERE id_usuario = TU_ID_USUARIO 
  AND id_codigo = ID_DEL_CODIGO;
```

### **Problema: El descuento no se aplica al precio**
**Causa:** Error en JavaScript o precio no detectado

**Solución:**
1. Abre la consola del navegador (F12)
2. Busca errores en rojo
3. Verifica que exista `.precio-descuentos` o `.precio-normal` en el HTML

### **Problema: El código no se marca como usado después de comprar**
**Causa:** No hay código en la sesión PHP

**Solución:**
1. Revisa logs: `ver_logs_debug.php`
2. Busca: `[NEQUI-INDIVIDUAL] Código aplicado detectado`
3. Si no aparece, el código no está en `$_SESSION`
4. Asegúrate de aplicar el código ANTES de subir el comprobante

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

- [x] Endpoint PHP para validar códigos
- [x] Validaciones (existe, no expiró, pertenece al usuario, no usado)
- [x] Guardado en sesión PHP
- [x] Frontend con aplicación de descuento
- [x] Persistencia en localStorage
- [x] Auto-carga al recargar página
- [x] Marcado como usado al crear orden
- [x] Actualización de BD (fecha_usado, id_orden, etc.)
- [x] Visualización de estados en perfil
- [x] Logs detallados para debug
- [x] Mensajes de error claros
- [x] Feedback visual (mensajes, botones)
- [x] Protección contra reutilización

---

## 🚀 PRÓXIMAS MEJORAS (OPCIONAL)

1. **Aplicar código en el carrito (compra múltiple)**
   - Extender a `informacion-carrito`
   - Aplicar descuento al total del carrito

2. **Códigos de uso múltiple**
   - Permitir X usos por código
   - Contador de usos restantes

3. **Descuentos acumulables**
   - Permitir múltiples códigos en una compra
   - Calcular descuentos combinados

4. **Notificaciones**
   - Email cuando el código está por expirar
   - WhatsApp con recordatorio

5. **Panel admin mejorado**
   - Ver estadísticas de uso de códigos
   - Asignar códigos masivamente
   - Generar reportes

---

**Sistema implementado:** Octubre 2025  
**Estado:** ✅ Funcional y listo para producción  
**Documentación:** Completa

