# Análisis Completo de Campos de la Tabla `orden`

## 📋 Estructura Real de la Tabla

### Campos según phpMyAdmin:

1. `id_orden` - PK
2. `id_usuario` - FK a tabla usuario
3. `nombre` - Nombre del cliente
4. `correo` - Email del cliente
5. `cedula` - Documento de identidad
6. `celular` - Teléfono
7. `departamento` - Departamento de envío
8. `ciudad` - Ciudad de envío
9. `direccion` - Dirección completa
10. `barrio` - Barrio
11. `referencias` - Referencias de ubicación
12. `metodo_pago` - Método (nequi, wompi, etc.)
13. `costo_envio` - Costo del envío
14. `fecha` - Fecha de creación de la orden
15. `total` - Total a pagar
16. `estado` - Estado de la orden
17. `codigo_descuento_id` - FK a código de descuento aplicado
18. `comprobante_pago` - Ruta del archivo
19. `nombre_archivo_comprobante` - Nombre del archivo
20. `token_verificacion` - Token único para verificar
21. `fecha_aprobacion` - Fecha de aprobación/rechazo
22. `motivo_rechazo` - Razón del rechazo
23. `comprobante_verificado` - 0=No, 1=Sí
24. `transportadora` - Empresa de transporte
25. `guia_envio` - Número de guía
26. `fecha_envio` - Fecha de envío
27. `fecha_entrega_estimada` - Fecha estimada
28. `fecha_entrega` - Fecha real de entrega
29. `recordatorio_enviado` - Si se envió recordatorio WhatsApp
30. `monto_pagado` - Monto real que pagó el cliente
31. `intentos_pago` - Número de intentos de pago/resubida
32. `fecha_ultima_subida` - Última vez que se actualizó comprobante

---

## 📊 Análisis de los 4 Registros

### Orden #42 - Estado: `pendiente_verificacion` (Comprobante sin verificar)

| Campo | Valor | ¿Correcto? |
|-------|-------|------------|
| id_orden | 42 | ✅ |
| id_usuario | NULL | ⚠️ Compra anónima o sin capturar |
| nombre | Julian | ✅ |
| correo | juliannaranjo58@gmail.com | ✅ |
| cedula | 1138274130 | ✅ |
| celular | 3173897119 | ✅ |
| departamento | Bolívar | ✅ |
| ciudad | Cartagena | ✅ |
| direccion | Cll 42 a 2w 74 | ✅ |
| barrio | El saman | ✅ |
| referencias | NULL | ✅ Sin referencias |
| metodo_pago | nequi | ✅ |
| costo_envio | 22000.00 | ✅ |
| fecha | 2025-10-26 23:13:27 | ✅ |
| total | 282000.00 | ✅ |
| estado | pendiente_verificacion | ✅ |
| codigo_descuento_id | NULL | ✅ Aún no se aprueba |
| comprobante_pago | comprobante_1761538407_68fef1672b70e.png | ✅ |
| nombre_archivo_comprobante | NULL | ❌ **BUG: Debería tener valor** |
| token_verificacion | 7c1c8b5d1c54484f... | ✅ |
| fecha_aprobacion | NULL | ✅ Aún no aprobada |
| motivo_rechazo | NULL | ✅ |
| comprobante_verificado | 0 | ✅ Sin verificar |
| transportadora | NULL | ✅ No enviada |
| guia_envio | NULL | ✅ No enviada |
| fecha_envio | NULL | ✅ No enviada |
| fecha_entrega_estimada | NULL | ✅ No enviada |
| fecha_entrega | NULL | ✅ No entregada |
| recordatorio_enviado | 0 | ✅ |
| monto_pagado | 0.00 | ❌ **NO SE USA** |
| intentos_pago | 0 | ⚠️ **NO SE USA** |
| fecha_ultima_subida | NULL | ⚠️ **NO SE USA** |

---

### Orden #43 - Estado: `pendiente_verificacion` (Comprobante verificado, esperando aprobación)

| Campo | Valor | ¿Correcto? |
|-------|-------|------------|
| id_orden | 43 | ✅ |
| id_usuario | 1 | ✅ |
| nombre | Julian | ✅ |
| correo | juliannaranjo58@gmail.com | ✅ |
| cedula | 1138274130 | ✅ |
| celular | 3173897119 | ✅ |
| departamento | Cauca | ✅ |
| ciudad | Popayán | ✅ |
| direccion | Cll 42 a 2w 74 | ✅ |
| barrio | El saman | ✅ |
| referencias | NULL | ✅ |
| metodo_pago | nequi | ✅ |
| costo_envio | 20000.00 | ✅ |
| fecha | 2025-10-26 23:15:07 | ✅ |
| total | 280000.00 | ✅ |
| estado | pendiente_verificacion | ✅ |
| codigo_descuento_id | NULL | ✅ Aún no aprobada |
| comprobante_pago | comprobante_1761538507_68fef1cb8bfdc.png | ✅ |
| nombre_archivo_comprobante | NULL | ❌ **BUG: Debería tener valor** |
| token_verificacion | 9e69ef71e99e473b... | ✅ |
| fecha_aprobacion | NULL | ✅ Aún no aprobada |
| motivo_rechazo | NULL | ✅ |
| comprobante_verificado | 1 | ✅ Ya verificado |
| transportadora | NULL | ✅ No enviada |
| guia_envio | NULL | ✅ No enviada |
| fecha_envio | NULL | ✅ No enviada |
| fecha_entrega_estimada | NULL | ✅ No enviada |
| fecha_entrega | NULL | ✅ No entregada |
| recordatorio_enviado | 0 | ✅ |
| monto_pagado | 0.00 | ❌ **NO SE USA** |
| intentos_pago | 0 | ⚠️ **NO SE USA** |
| fecha_ultima_subida | NULL | ⚠️ **NO SE USA** |

---

### Orden #44 - Estado: `pagado` (Orden aprobada)

| Campo | Valor | ¿Correcto? |
|-------|-------|------------|
| id_orden | 44 | ✅ |
| id_usuario | 1 | ✅ |
| nombre | Julian | ✅ |
| correo | juliannaranjo58@gmail.com | ✅ |
| cedula | 1138274130 | ✅ |
| celular | 3173897119 | ✅ |
| departamento | Antioquia | ✅ |
| ciudad | Bello | ✅ |
| direccion | Cll 42 a 2w 74 | ✅ |
| barrio | El saman | ✅ |
| referencias | NULL | ✅ |
| metodo_pago | nequi | ✅ |
| costo_envio | 10000.00 | ✅ |
| fecha | 2025-10-26 23:23:51 | ✅ |
| total | 135000.00 | ✅ |
| estado | pagado | ✅ Aprobada |
| codigo_descuento_id | NULL | ⚠️ Debería tener código (si tenía sesión) |
| comprobante_pago | comprobante_1761539031_68fef3d779fa2.png | ✅ |
| nombre_archivo_comprobante | krea-edit__11_-removebg-preview.png | ✅ |
| token_verificacion | c41b1ee32561ad5e... | ✅ |
| fecha_aprobacion | 2025-10-26 23:24:24 | ✅ |
| motivo_rechazo | NULL | ✅ No rechazada |
| comprobante_verificado | 1 | ✅ |
| transportadora | NULL | ✅ Aún no enviada |
| guia_envio | NULL | ✅ Aún no enviada |
| fecha_envio | NULL | ✅ Aún no enviada |
| fecha_entrega_estimada | NULL | ✅ Aún no enviada |
| fecha_entrega | NULL | ✅ No entregada |
| recordatorio_enviado | 0 | ✅ |
| monto_pagado | 0.00 | ❌ **NO SE USA** |
| intentos_pago | 0 | ⚠️ **NO SE USA** |
| fecha_ultima_subida | NULL | ⚠️ **NO SE USA** |

---

### Orden #45 - Estado: `rechazado`

| Campo | Valor | ¿Correcto? |
|-------|-------|------------|
| id_orden | 45 | ✅ |
| id_usuario | NULL | ✅ Compra anónima |
| nombre | Julian | ✅ |
| correo | juliannaranjo58@gmail.com | ✅ |
| cedula | 1138274130 | ✅ |
| celular | 3173897119 | ✅ |
| departamento | Antioquia | ✅ |
| ciudad | Bello | ✅ |
| direccion | Cll 42 a 2w 74 | ✅ |
| barrio | El saman | ✅ |
| referencias | NULL | ✅ |
| metodo_pago | nequi | ✅ |
| costo_envio | 10000.00 | ✅ |
| fecha | 2025-10-26 23:25:07 | ✅ |
| total | 145000.00 | ✅ |
| estado | rechazado | ✅ |
| codigo_descuento_id | NULL | ✅ No se genera código |
| comprobante_pago | comprobante_1761539107_68fef4236fbe3.jpg | ✅ |
| nombre_archivo_comprobante | Lucid_Origin_Logo_text_... | ✅ |
| token_verificacion | b946ad7081707d07... | ✅ |
| fecha_aprobacion | 2025-10-26 23:25:32 | ✅ (fecha de rechazo) |
| motivo_rechazo | El comprobante pertenece a otra transacción | ✅ |
| comprobante_verificado | 0 | ✅ |
| transportadora | NULL | ✅ No se envía |
| guia_envio | NULL | ✅ No se envía |
| fecha_envio | NULL | ✅ No se envía |
| fecha_entrega_estimada | NULL | ✅ No se envía |
| fecha_entrega | NULL | ✅ No entregada |
| recordatorio_enviado | 0 | ⚠️ Debería ser 1 si se envió WhatsApp |
| monto_pagado | 0.00 | ❌ **NO SE USA** |
| intentos_pago | 0 | ⚠️ **NO SE USA** |
| fecha_ultima_subida | NULL | ⚠️ **NO SE USA** |

---

## ❌ PROBLEMAS ENCONTRADOS

### 1. **`nombre_archivo_comprobante` = NULL** (Órdenes #42 y #43)

**Causa:** No se estaba incluyendo en el INSERT de carrito y favoritos  
**Impacto:** Campo NULL cuando debería tener el nombre del archivo  
**Estado:** ✅ **YA CORREGIDO** en los archivos

### 2. **`codigo_descuento_id` = NULL** (Orden #44 aprobada con sesión)

**Esperado:** Si la orden #44 tenía `id_usuario = 1` (con sesión), al aprobarla debería haberse generado un código de descuento  
**Problema:** El campo está NULL  
**Posible causa:** Error en el flujo de aprobación o código no se generó

### 3. **`recordatorio_enviado` = 0** (Orden #45 rechazada)

**Esperado:** Si se implementó el sistema de recordatorios por WhatsApp, debería ser 1  
**Problema:** Está en 0  
**Posible causa:** El recordatorio no se envió o el sistema no está activo

---

## 🗑️ CAMPOS QUE NUNCA SE USAN

### 1. **`monto_pagado`** - Siempre 0.00

**Descripción en BD:** "Monto real que pagó el cliente (puede ser menor al total esperado)"

**Estado actual:** ❌ Nunca se captura ni se usa

**¿Útil?** ⭐ **SÍ, MUY ÚTIL**

**Propósito:**
- Capturar el monto que el cliente dice haber pagado
- Comparar con el total esperado
- Detectar discrepancias (ej: cliente pagó $130.000 pero el total era $135.000)
- Admin puede ver si hay diferencia antes de aprobar

**Recomendación:** 🔥 **IMPLEMENTAR**

```php
// En el formulario de subida de comprobante, agregar campo:
<input type="number" name="monto_pagado" placeholder="Monto que pagaste" required>

// En subir_comprobante-carrito.php:
$monto_pagado = floatval($_POST['monto_pagado']);

// En el INSERT:
INSERT INTO orden (..., monto_pagado, ...) VALUES (..., ?, ...)

// En el panel admin, mostrar advertencia si hay diferencia:
if (abs($monto_pagado - $total) > 1000) {
    echo "⚠️ ADVERTENCIA: Monto declarado ($monto_pagado) difiere del total ($total)";
}
```

---

### 2. **`intentos_pago`** - Siempre 0

**Descripción en BD:** "Número de intentos de pago/resubida de comprobante"

**Estado actual:** ❌ Nunca se usa

**¿Útil?** ⚠️ **PUEDE SER ÚTIL**

**Propósito:**
- Contar cuántas veces el usuario ha subido/actualizado el comprobante
- Detectar usuarios que suben múltiples comprobantes (posible fraude)
- Estadísticas

**Recomendación:** 💡 **IMPLEMENTAR SI SE PERMITE RESUBIR COMPROBANTES**

Si permites que un usuario corrija/reemplace un comprobante rechazado, este campo sería útil.

---

### 3. **`fecha_ultima_subida`** - Siempre NULL

**Descripción en BD:** "Última vez que se subió o actualizó el comprobante"

**Estado actual:** ❌ Nunca se usa

**¿Útil?** ⚠️ **PUEDE SER ÚTIL** (junto con `intentos_pago`)

**Propósito:**
- Saber cuándo fue la última actualización del comprobante
- Útil si se permite resubir comprobantes
- Auditoría

**Recomendación:** 💡 **IMPLEMENTAR SI SE PERMITE RESUBIR COMPROBANTES**

---

## ✅ CAMPOS QUE SÍ SE USAN CORRECTAMENTE

| Campo | ¿Se usa? | Observaciones |
|-------|----------|---------------|
| id_orden, id_usuario, nombre, correo, cedula, celular | ✅ | Datos básicos |
| departamento, ciudad, direccion, barrio, referencias | ✅ | Datos de envío |
| metodo_pago, costo_envio, fecha, total | ✅ | Datos de pago |
| estado | ✅ | Flujo correcto |
| codigo_descuento_id | ⚠️ | Se usa, pero puede fallar |
| comprobante_pago | ✅ | Ruta del archivo |
| nombre_archivo_comprobante | ✅ | **Ahora sí (corregido)** |
| token_verificacion | ✅ | Token único |
| fecha_aprobacion | ✅ | Al aprobar/rechazar |
| motivo_rechazo | ✅ | Solo en rechazos |
| comprobante_verificado | ✅ | 0/1 correcto |
| transportadora, guia_envio, fecha_envio | ✅ | Solo al enviar |
| fecha_entrega_estimada, fecha_entrega | ✅ | Solo al enviar |
| recordatorio_enviado | ✅ | Sistema de recordatorios |

---

## 🔧 RESUMEN DE RECOMENDACIONES

### 🔴 Urgente (Ya corregido):
1. ✅ **DONE** - Corregir `nombre_archivo_comprobante` NULL

### 🟡 Mejoras de Funcionalidad:
2. ⭐ **IMPLEMENTAR** - `monto_pagado` para mejorar validación
3. 💡 **CONSIDERAR** - `intentos_pago` y `fecha_ultima_subida` si se permite resubir comprobantes

### 🟢 Revisar:
4. ⚠️ **VERIFICAR** - ¿Por qué `codigo_descuento_id` está NULL en orden #44 aprobada con sesión?
5. ⚠️ **VERIFICAR** - ¿El sistema de recordatorios está activo? (orden #45 rechazada sin recordatorio)

---

**Fecha:** 27 de octubre de 2025  
**Estado:** 📊 Análisis actualizado con estructura real


## 📋 Estructura Real de la Tabla

### Campos según phpMyAdmin:

1. `id_orden` - PK
2. `id_usuario` - FK a tabla usuario
3. `nombre` - Nombre del cliente
4. `correo` - Email del cliente
5. `cedula` - Documento de identidad
6. `celular` - Teléfono
7. `departamento` - Departamento de envío
8. `ciudad` - Ciudad de envío
9. `direccion` - Dirección completa
10. `barrio` - Barrio
11. `referencias` - Referencias de ubicación
12. `metodo_pago` - Método (nequi, wompi, etc.)
13. `costo_envio` - Costo del envío
14. `fecha` - Fecha de creación de la orden
15. `total` - Total a pagar
16. `estado` - Estado de la orden
17. `codigo_descuento_id` - FK a código de descuento aplicado
18. `comprobante_pago` - Ruta del archivo
19. `nombre_archivo_comprobante` - Nombre del archivo
20. `token_verificacion` - Token único para verificar
21. `fecha_aprobacion` - Fecha de aprobación/rechazo
22. `motivo_rechazo` - Razón del rechazo
23. `comprobante_verificado` - 0=No, 1=Sí
24. `transportadora` - Empresa de transporte
25. `guia_envio` - Número de guía
26. `fecha_envio` - Fecha de envío
27. `fecha_entrega_estimada` - Fecha estimada
28. `fecha_entrega` - Fecha real de entrega
29. `recordatorio_enviado` - Si se envió recordatorio WhatsApp
30. `monto_pagado` - Monto real que pagó el cliente
31. `intentos_pago` - Número de intentos de pago/resubida
32. `fecha_ultima_subida` - Última vez que se actualizó comprobante

---

## 📊 Análisis de los 4 Registros

### Orden #42 - Estado: `pendiente_verificacion` (Comprobante sin verificar)

| Campo | Valor | ¿Correcto? |
|-------|-------|------------|
| id_orden | 42 | ✅ |
| id_usuario | NULL | ⚠️ Compra anónima o sin capturar |
| nombre | Julian | ✅ |
| correo | juliannaranjo58@gmail.com | ✅ |
| cedula | 1138274130 | ✅ |
| celular | 3173897119 | ✅ |
| departamento | Bolívar | ✅ |
| ciudad | Cartagena | ✅ |
| direccion | Cll 42 a 2w 74 | ✅ |
| barrio | El saman | ✅ |
| referencias | NULL | ✅ Sin referencias |
| metodo_pago | nequi | ✅ |
| costo_envio | 22000.00 | ✅ |
| fecha | 2025-10-26 23:13:27 | ✅ |
| total | 282000.00 | ✅ |
| estado | pendiente_verificacion | ✅ |
| codigo_descuento_id | NULL | ✅ Aún no se aprueba |
| comprobante_pago | comprobante_1761538407_68fef1672b70e.png | ✅ |
| nombre_archivo_comprobante | NULL | ❌ **BUG: Debería tener valor** |
| token_verificacion | 7c1c8b5d1c54484f... | ✅ |
| fecha_aprobacion | NULL | ✅ Aún no aprobada |
| motivo_rechazo | NULL | ✅ |
| comprobante_verificado | 0 | ✅ Sin verificar |
| transportadora | NULL | ✅ No enviada |
| guia_envio | NULL | ✅ No enviada |
| fecha_envio | NULL | ✅ No enviada |
| fecha_entrega_estimada | NULL | ✅ No enviada |
| fecha_entrega | NULL | ✅ No entregada |
| recordatorio_enviado | 0 | ✅ |
| monto_pagado | 0.00 | ❌ **NO SE USA** |
| intentos_pago | 0 | ⚠️ **NO SE USA** |
| fecha_ultima_subida | NULL | ⚠️ **NO SE USA** |

---

### Orden #43 - Estado: `pendiente_verificacion` (Comprobante verificado, esperando aprobación)

| Campo | Valor | ¿Correcto? |
|-------|-------|------------|
| id_orden | 43 | ✅ |
| id_usuario | 1 | ✅ |
| nombre | Julian | ✅ |
| correo | juliannaranjo58@gmail.com | ✅ |
| cedula | 1138274130 | ✅ |
| celular | 3173897119 | ✅ |
| departamento | Cauca | ✅ |
| ciudad | Popayán | ✅ |
| direccion | Cll 42 a 2w 74 | ✅ |
| barrio | El saman | ✅ |
| referencias | NULL | ✅ |
| metodo_pago | nequi | ✅ |
| costo_envio | 20000.00 | ✅ |
| fecha | 2025-10-26 23:15:07 | ✅ |
| total | 280000.00 | ✅ |
| estado | pendiente_verificacion | ✅ |
| codigo_descuento_id | NULL | ✅ Aún no aprobada |
| comprobante_pago | comprobante_1761538507_68fef1cb8bfdc.png | ✅ |
| nombre_archivo_comprobante | NULL | ❌ **BUG: Debería tener valor** |
| token_verificacion | 9e69ef71e99e473b... | ✅ |
| fecha_aprobacion | NULL | ✅ Aún no aprobada |
| motivo_rechazo | NULL | ✅ |
| comprobante_verificado | 1 | ✅ Ya verificado |
| transportadora | NULL | ✅ No enviada |
| guia_envio | NULL | ✅ No enviada |
| fecha_envio | NULL | ✅ No enviada |
| fecha_entrega_estimada | NULL | ✅ No enviada |
| fecha_entrega | NULL | ✅ No entregada |
| recordatorio_enviado | 0 | ✅ |
| monto_pagado | 0.00 | ❌ **NO SE USA** |
| intentos_pago | 0 | ⚠️ **NO SE USA** |
| fecha_ultima_subida | NULL | ⚠️ **NO SE USA** |

---

### Orden #44 - Estado: `pagado` (Orden aprobada)

| Campo | Valor | ¿Correcto? |
|-------|-------|------------|
| id_orden | 44 | ✅ |
| id_usuario | 1 | ✅ |
| nombre | Julian | ✅ |
| correo | juliannaranjo58@gmail.com | ✅ |
| cedula | 1138274130 | ✅ |
| celular | 3173897119 | ✅ |
| departamento | Antioquia | ✅ |
| ciudad | Bello | ✅ |
| direccion | Cll 42 a 2w 74 | ✅ |
| barrio | El saman | ✅ |
| referencias | NULL | ✅ |
| metodo_pago | nequi | ✅ |
| costo_envio | 10000.00 | ✅ |
| fecha | 2025-10-26 23:23:51 | ✅ |
| total | 135000.00 | ✅ |
| estado | pagado | ✅ Aprobada |
| codigo_descuento_id | NULL | ⚠️ Debería tener código (si tenía sesión) |
| comprobante_pago | comprobante_1761539031_68fef3d779fa2.png | ✅ |
| nombre_archivo_comprobante | krea-edit__11_-removebg-preview.png | ✅ |
| token_verificacion | c41b1ee32561ad5e... | ✅ |
| fecha_aprobacion | 2025-10-26 23:24:24 | ✅ |
| motivo_rechazo | NULL | ✅ No rechazada |
| comprobante_verificado | 1 | ✅ |
| transportadora | NULL | ✅ Aún no enviada |
| guia_envio | NULL | ✅ Aún no enviada |
| fecha_envio | NULL | ✅ Aún no enviada |
| fecha_entrega_estimada | NULL | ✅ Aún no enviada |
| fecha_entrega | NULL | ✅ No entregada |
| recordatorio_enviado | 0 | ✅ |
| monto_pagado | 0.00 | ❌ **NO SE USA** |
| intentos_pago | 0 | ⚠️ **NO SE USA** |
| fecha_ultima_subida | NULL | ⚠️ **NO SE USA** |

---

### Orden #45 - Estado: `rechazado`

| Campo | Valor | ¿Correcto? |
|-------|-------|------------|
| id_orden | 45 | ✅ |
| id_usuario | NULL | ✅ Compra anónima |
| nombre | Julian | ✅ |
| correo | juliannaranjo58@gmail.com | ✅ |
| cedula | 1138274130 | ✅ |
| celular | 3173897119 | ✅ |
| departamento | Antioquia | ✅ |
| ciudad | Bello | ✅ |
| direccion | Cll 42 a 2w 74 | ✅ |
| barrio | El saman | ✅ |
| referencias | NULL | ✅ |
| metodo_pago | nequi | ✅ |
| costo_envio | 10000.00 | ✅ |
| fecha | 2025-10-26 23:25:07 | ✅ |
| total | 145000.00 | ✅ |
| estado | rechazado | ✅ |
| codigo_descuento_id | NULL | ✅ No se genera código |
| comprobante_pago | comprobante_1761539107_68fef4236fbe3.jpg | ✅ |
| nombre_archivo_comprobante | Lucid_Origin_Logo_text_... | ✅ |
| token_verificacion | b946ad7081707d07... | ✅ |
| fecha_aprobacion | 2025-10-26 23:25:32 | ✅ (fecha de rechazo) |
| motivo_rechazo | El comprobante pertenece a otra transacción | ✅ |
| comprobante_verificado | 0 | ✅ |
| transportadora | NULL | ✅ No se envía |
| guia_envio | NULL | ✅ No se envía |
| fecha_envio | NULL | ✅ No se envía |
| fecha_entrega_estimada | NULL | ✅ No se envía |
| fecha_entrega | NULL | ✅ No entregada |
| recordatorio_enviado | 0 | ⚠️ Debería ser 1 si se envió WhatsApp |
| monto_pagado | 0.00 | ❌ **NO SE USA** |
| intentos_pago | 0 | ⚠️ **NO SE USA** |
| fecha_ultima_subida | NULL | ⚠️ **NO SE USA** |

---

## ❌ PROBLEMAS ENCONTRADOS

### 1. **`nombre_archivo_comprobante` = NULL** (Órdenes #42 y #43)

**Causa:** No se estaba incluyendo en el INSERT de carrito y favoritos  
**Impacto:** Campo NULL cuando debería tener el nombre del archivo  
**Estado:** ✅ **YA CORREGIDO** en los archivos

### 2. **`codigo_descuento_id` = NULL** (Orden #44 aprobada con sesión)

**Esperado:** Si la orden #44 tenía `id_usuario = 1` (con sesión), al aprobarla debería haberse generado un código de descuento  
**Problema:** El campo está NULL  
**Posible causa:** Error en el flujo de aprobación o código no se generó

### 3. **`recordatorio_enviado` = 0** (Orden #45 rechazada)

**Esperado:** Si se implementó el sistema de recordatorios por WhatsApp, debería ser 1  
**Problema:** Está en 0  
**Posible causa:** El recordatorio no se envió o el sistema no está activo

---

## 🗑️ CAMPOS QUE NUNCA SE USAN

### 1. **`monto_pagado`** - Siempre 0.00

**Descripción en BD:** "Monto real que pagó el cliente (puede ser menor al total esperado)"

**Estado actual:** ❌ Nunca se captura ni se usa

**¿Útil?** ⭐ **SÍ, MUY ÚTIL**

**Propósito:**
- Capturar el monto que el cliente dice haber pagado
- Comparar con el total esperado
- Detectar discrepancias (ej: cliente pagó $130.000 pero el total era $135.000)
- Admin puede ver si hay diferencia antes de aprobar

**Recomendación:** 🔥 **IMPLEMENTAR**

```php
// En el formulario de subida de comprobante, agregar campo:
<input type="number" name="monto_pagado" placeholder="Monto que pagaste" required>

// En subir_comprobante-carrito.php:
$monto_pagado = floatval($_POST['monto_pagado']);

// En el INSERT:
INSERT INTO orden (..., monto_pagado, ...) VALUES (..., ?, ...)

// En el panel admin, mostrar advertencia si hay diferencia:
if (abs($monto_pagado - $total) > 1000) {
    echo "⚠️ ADVERTENCIA: Monto declarado ($monto_pagado) difiere del total ($total)";
}
```

---

### 2. **`intentos_pago`** - Siempre 0

**Descripción en BD:** "Número de intentos de pago/resubida de comprobante"

**Estado actual:** ❌ Nunca se usa

**¿Útil?** ⚠️ **PUEDE SER ÚTIL**

**Propósito:**
- Contar cuántas veces el usuario ha subido/actualizado el comprobante
- Detectar usuarios que suben múltiples comprobantes (posible fraude)
- Estadísticas

**Recomendación:** 💡 **IMPLEMENTAR SI SE PERMITE RESUBIR COMPROBANTES**

Si permites que un usuario corrija/reemplace un comprobante rechazado, este campo sería útil.

---

### 3. **`fecha_ultima_subida`** - Siempre NULL

**Descripción en BD:** "Última vez que se subió o actualizó el comprobante"

**Estado actual:** ❌ Nunca se usa

**¿Útil?** ⚠️ **PUEDE SER ÚTIL** (junto con `intentos_pago`)

**Propósito:**
- Saber cuándo fue la última actualización del comprobante
- Útil si se permite resubir comprobantes
- Auditoría

**Recomendación:** 💡 **IMPLEMENTAR SI SE PERMITE RESUBIR COMPROBANTES**

---

## ✅ CAMPOS QUE SÍ SE USAN CORRECTAMENTE

| Campo | ¿Se usa? | Observaciones |
|-------|----------|---------------|
| id_orden, id_usuario, nombre, correo, cedula, celular | ✅ | Datos básicos |
| departamento, ciudad, direccion, barrio, referencias | ✅ | Datos de envío |
| metodo_pago, costo_envio, fecha, total | ✅ | Datos de pago |
| estado | ✅ | Flujo correcto |
| codigo_descuento_id | ⚠️ | Se usa, pero puede fallar |
| comprobante_pago | ✅ | Ruta del archivo |
| nombre_archivo_comprobante | ✅ | **Ahora sí (corregido)** |
| token_verificacion | ✅ | Token único |
| fecha_aprobacion | ✅ | Al aprobar/rechazar |
| motivo_rechazo | ✅ | Solo en rechazos |
| comprobante_verificado | ✅ | 0/1 correcto |
| transportadora, guia_envio, fecha_envio | ✅ | Solo al enviar |
| fecha_entrega_estimada, fecha_entrega | ✅ | Solo al enviar |
| recordatorio_enviado | ✅ | Sistema de recordatorios |

---

## 🔧 RESUMEN DE RECOMENDACIONES

### 🔴 Urgente (Ya corregido):
1. ✅ **DONE** - Corregir `nombre_archivo_comprobante` NULL

### 🟡 Mejoras de Funcionalidad:
2. ⭐ **IMPLEMENTAR** - `monto_pagado` para mejorar validación
3. 💡 **CONSIDERAR** - `intentos_pago` y `fecha_ultima_subida` si se permite resubir comprobantes

### 🟢 Revisar:
4. ⚠️ **VERIFICAR** - ¿Por qué `codigo_descuento_id` está NULL en orden #44 aprobada con sesión?
5. ⚠️ **VERIFICAR** - ¿El sistema de recordatorios está activo? (orden #45 rechazada sin recordatorio)

---

**Fecha:** 27 de octubre de 2025  
**Estado:** 📊 Análisis actualizado con estructura real

