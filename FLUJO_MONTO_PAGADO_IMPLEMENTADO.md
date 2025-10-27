# 💰 SISTEMA DE MONTO PAGADO - IMPLEMENTADO

## 🎯 Problema Resuelto

**Pregunta del Usuario:**
> "Si el admin rechaza una orden porque el monto no coincide, ¿cómo saber cuánto falta pagar?"

**Respuesta del monto del Sistema:**
El admin ingresa el **monto que el cliente realmente pagó** al momento de rechazar, y el sistema calcula automáticamente la diferencia.

---

## 📊 Cambios en la Base de Datos

### SQL Ejecutado:
```sql
ALTER TABLE orden ADD COLUMN monto_pagado DECIMAL(10,2) DEFAULT 0 
COMMENT 'Monto real que pagó el cliente (puede ser menor al total)';

ALTER TABLE orden ADD COLUMN intentos_pago INT DEFAULT 0 
COMMENT 'Número de intentos de pago/resubida de comprobante';

ALTER TABLE orden ADD COLUMN fecha_ultima_subida DATETIME DEFAULT NULL 
COMMENT 'Última vez que se subió o actualizó el comprobante';
```

**Archivo:** `database/agregar_monto_pagado.sql`

---

## 🖥️ Flujo en el Panel de Admin

### 1. **Modal de Rechazo Mejorado**

Cuando el admin hace clic en "Rechazar Orden", aparece un modal con:

**Campos:**
- ✅ **Motivo de rechazo** (select)
- ✅ **Campo "Monto Pagado"** (input number) - **Solo aparece si selecciona "El monto del comprobante no coincide"**

#### Interfaz Visual:

```
┌─────────────────────────────────────────┐
│ Rechazar orden                       ×  │
├─────────────────────────────────────────┤
│                                         │
│ Motivo:                                 │
│ [El monto del comprobante no coincide ▼]│
│                                         │
│ 💰 Monto que el cliente pagó           │
│ ┌─────────────────────────────────────┐│
│ │ 15000                                ││
│ └─────────────────────────────────────┘│
│ 💡 Ingresa el monto que realmente pagó │
│    el cliente según el comprobante      │
│                                         │
│                    [Cancelar] [Rechazar]│
└─────────────────────────────────────────┘
```

### 2. **JavaScript - Mostrar/Ocultar Campo**

```javascript
select.onchange = () => {
    const motivo = select.value;
    
    // Mostrar campo "Monto Pagado" si es problema de monto
    if (motivo.includes('monto') || motivo.includes('coincide')) {
        montoPagadoWrapper.style.display = 'block';
    } else {
        montoPagadoWrapper.style.display = 'none';
        montoPagadoInput.value = '';
    }
};
```

### 3. **Validación antes de Enviar**

```javascript
// Validar monto pagado si es problema de monto
let montoPagado = 0;
if (baseMotivo.includes('monto') || baseMotivo.includes('coincide')) {
    montoPagado = parseFloat(montoPagadoInput.value) || 0;
    if (montoPagado <= 0) {
        alert('⚠️ Debes ingresar el monto que el cliente pagó realmente.');
        return;
    }
}
```

### 4. **Envío al Servidor**

```javascript
let body = `action=rechazar&id_orden=${idOrden}&motivo=${encodeURIComponent(motivo)}`;
if (montoPagado > 0) {
    body += `&monto_pagado=${montoPagado}`;
}
```

---

## 🔧 Backend - PHP

### Archivo: `admin/php/acciones.php`

```php
case 'rechazar':
    $motivo = $_POST['motivo'] ?? '';
    $monto_pagado = isset($_POST['monto_pagado']) ? floatval($_POST['monto_pagado']) : 0;
    
    if (empty($motivo)) {
        throw new Exception('Debe proporcionar un motivo para el rechazo');
    }

    // Rechazar orden y guardar monto_pagado si aplica
    if ($monto_pagado > 0) {
        // Si es problema de monto, guardar cuánto pagó realmente
        $stmt = $conn->prepare("UPDATE orden 
                               SET estado = 'rechazado', 
                                   motivo_rechazo = ?, 
                                   monto_pagado = ?, 
                                   fecha_aprobacion = NOW() 
                               WHERE id_orden = ?");
        $stmt->bind_param("sdi", $motivo, $monto_pagado, $id_orden);
    } else {
        // Rechazo normal sin problema de monto
        $stmt = $conn->prepare("UPDATE orden 
                               SET estado = 'rechazado', 
                                   motivo_rechazo = ?, 
                                   fecha_aprobacion = NOW() 
                               WHERE id_orden = ?");
        $stmt->bind_param("si", $motivo, $id_orden);
    }
```

---

## 👤 Vista del Usuario (Perfil)

### 1. **Obtener Historial con monto_pagado**

**Archivo:** `login/php/obtener_historial_usuario.php`

```php
$sql = "SELECT 
            o.id_orden,
            o.total,
            o.estado,
            o.motivo_rechazo,
            o.monto_pagado,  // ← NUEVO CAMPO
            r.nombre as nombre_reloj,
            ...
        FROM orden o ...";
```

### 2. **Botón "Completar Pago" con Diferencia**

**Archivo:** `perfil/js/perfil.js`

```javascript
// Generar botón con monto_pagado
onclick="pagarDiferencia(${orden.id_orden}, ${orden.total}, ${orden.monto_pagado || 0})"

// Función que calcula la diferencia
function pagarDiferencia(idOrden, totalOriginal, montoPagado) {
    const diferencia = totalOriginal - (montoPagado || 0);
    
    let mensaje = `📋 Orden Rechazada #${idOrden}\n\n`;
    mensaje += `💰 Total del pedido: $${formatearPrecio(totalOriginal)}\n`;
    
    if (montoPagado > 0) {
        mensaje += `✅ Ya pagaste: $${formatearPrecio(montoPagado)}\n`;
        mensaje += `❌ Falta pagar: $${formatearPrecio(diferencia)}\n\n`;
        mensaje += `¿Quieres completar el pago de los $${formatearPrecio(diferencia)} faltantes?`;
    }
    ...
}
```

### 3. **Dialog que Ve el Usuario**

```
┌────────────────────────────────────────┐
│ 📋 Orden Rechazada #5                  │
│                                        │
│ 💰 Total del pedido: $20.000           │
│ ✅ Ya pagaste: $15.000                 │
│ ❌ Falta pagar: $5.000                 │
│                                        │
│ ¿Quieres completar el pago de los     │
│ $5.000 faltantes?                      │
│                                        │
│            [Cancelar] [Aceptar]        │
└────────────────────────────────────────┘
```

---

## 🔄 Flujo Completo - Ejemplo Real

### Escenario: Cliente paga $15.000 de un pedido de $20.000

```
PASO 1: Cliente hace compra
├─ Reloj: $18.000
├─ Envío: $2.000
└─ Total: $20.000

PASO 2: Cliente paga pero solo $15.000
└─ Sube comprobante de Nequi por $15.000

PASO 3: Admin revisa comprobante
├─ Ve que el comprobante es de $15.000
├─ Hace clic en "Rechazar Orden"
├─ Selecciona motivo: "El monto del comprobante no coincide"
├─ Aparece campo: "Monto que el cliente pagó"
├─ Ingresa: 15000
└─ Click "Rechazar"

PASO 4: Sistema guarda en BD
├─ estado = 'rechazado'
├─ motivo_rechazo = 'El monto del comprobante no coincide'
└─ monto_pagado = 15000 ← NUEVO

PASO 5: Cliente ve en su perfil
Orden #5 - ❌ RECHAZADO

⚠️ Motivo: El monto del comprobante no coincide

[💰 Completar Pago]  [💬 Contactar Soporte]

PASO 6: Cliente hace clic en "Completar Pago"
Ve dialog:
├─ Total del pedido: $20.000
├─ Ya pagaste: $15.000
└─ Falta pagar: $5.000

PASO 7: Cliente acepta
├─ Redirige a: informacion/recuperar_pago.html?orden=5
└─ (Esta página aún está pendiente de crear)

PASO 8: En recuperar_pago.html (próximo paso)
├─ Muestra: "Falta pagar: $5.000"
├─ Métodos: Nequi / Wompi
├─ Cliente paga los $5.000
├─ Sube comprobante
├─ Sistema actualiza:
│   ├─ monto_pagado = 20000 (15000 + 5000)
│   └─ estado = 'pendiente_verificacion'
└─ Admin verifica y aprueba
```

---

## 📊 Datos en la Base de Datos

### Antes del Rechazo:
```sql
id_orden | total  | estado               | motivo_rechazo | monto_pagado
---------|--------|----------------------|----------------|-------------
5        | 20000  | pendiente_verificacion| NULL          | 0
```

### Después del Rechazo (por Admin):
```sql
id_orden | total  | estado     | motivo_rechazo                      | monto_pagado
---------|--------|------------|-------------------------------------|-------------
5        | 20000  | rechazado  | El monto del comprobante no coincide| 15000
```

### Después de Completar Pago:
```sql
id_orden | total  | estado                | motivo_rechazo | monto_pagado
---------|--------|---------------------------|----------------|-------------
5        | 20000  | pendiente_verificacion   | NULL           | 20000
```

---

## ✅ Ventajas del Sistema

1. **Transparencia Total**
   - Cliente sabe exactamente cuánto falta pagar
   - No hay confusión

2. **Facilita Recuperación**
   - Cliente solo paga la diferencia (no todo de nuevo)
   - Proceso más rápido

3. **Menos Soporte**
   - Sistema automático calcula diferencia
   - Menos consultas al soporte

4. **Trazabilidad**
   - Historial completo de pagos
   - Base de datos registra todo

5. **Flexibilidad**
   - Si `monto_pagado` > 0 → Muestra diferencia exacta
   - Si `monto_pagado` = 0 → Mensaje genérico

---

## 🚧 Próximos Pasos (Opcionales)

### Opción A: Crear páginas completas
1. ✅ **`informacion/recuperar_pago.html`** - Formulario para pagar diferencia
2. ✅ **`informacion/resubir_comprobante.html`** - Formulario para resubir comprobante
3. ✅ **Backend para procesar pago parcial**
4. ✅ **Backend para actualizar comprobante**

### Opción B: Mantener WhatsApp como respaldo
- Botón "Completar Pago" sigue redirigiendo
- Pero "Contactar Soporte" siempre funciona por WhatsApp
- Si las páginas no existen, el usuario contacta soporte
- Soporte humano guía el proceso

---

## 📝 Resumen de Archivos Modificados

### Base de Datos:
- ✅ `database/agregar_monto_pagado.sql` (EJECUTAR)

### Admin Panel:
- ✅ `admin/panel.php` - Modal con campo monto_pagado
- ✅ `admin/js/panel.js` - Lógica para mostrar/validar campo
- ✅ `admin/php/acciones.php` - Guardar monto_pagado en BD

### Usuario:
- ✅ `login/php/obtener_historial_usuario.php` - Incluir monto_pagado en query
- ✅ `perfil/js/perfil.js` - Calcular y mostrar diferencia

---

## 🎯 Estado Actual

### ✅ COMPLETADO:
- [x] Campo en BD (`monto_pagado`)
- [x] Modal de admin con campo
- [x] JavaScript validación
- [x] Backend guarda monto_pagado
- [x] Frontend obtiene monto_pagado
- [x] Botón calcula y muestra diferencia

### ⏳ PENDIENTE:
- [ ] Crear `informacion/recuperar_pago.html`
- [ ] Crear `informacion/resubir_comprobante.html`
- [ ] Backend para procesar pago parcial
- [ ] Backend para actualizar comprobante

**Puedes elegir crear las páginas o usar WhatsApp como alternativa.**

---

**Implementación Completada:** ✅ Octubre 2025

