# 🔄 SISTEMA DE RECUPERACIÓN DE ÓRDENES RECHAZADAS

## 📊 Flujo de Estados de Órdenes

### Estados Ordenados (de menor a mayor avance):

```
1. pendiente              → Orden creada, esperando procesamiento
2. pendiente_verificacion → Comprobante subido, esperando verificación admin
3. pagado                 → Pago verificado y aprobado
4. aprobado               → Admin aprobó la orden
5. enviado                → Pedido en camino (con guía de rastreo)
6. entregado              → ✅ COMPLETADO - Pedido entregado al cliente
7. rechazado              → ❌ Orden rechazada (puede recuperarse)
8. cancelado              → ⛔ Orden cancelada (final)
```

### Respuesta a tu pregunta:
✅ **Sí, `entregado` es más avanzado que `pagado`**

- **`pagado`** = El pago fue aprobado, pero el producto aún no se ha enviado
- **`entregado`** = Estado final exitoso - el producto ya llegó al cliente

---

## 🔧 Sistema de Recuperación para Órdenes Rechazadas

### Concepto
Cuando una orden es rechazada, el sistema analiza el **motivo del rechazo** y ofrece acciones específicas para que el cliente pueda recuperar su compra sin empezar desde cero.

### ⚙️ Cómo Funciona

#### 1. **Detección Automática del Problema**

El JavaScript analiza el `motivo_rechazo` de la orden y detecta keywords:

```javascript
const motivo = orden.motivo_rechazo.toLowerCase();

// Problema de MONTO
if (motivo.includes('monto') || motivo.includes('coincide') || 
    motivo.includes('diferencia') || motivo.includes('precio')) {
    // Mostrar botón "Completar Pago"
}

// Problema de COMPROBANTE
if (motivo.includes('comprobante') || motivo.includes('legible') || 
    motivo.includes('falso') || motivo.includes('inválido')) {
    // Mostrar botón "Subir Nuevo Comprobante"
}
```

#### 2. **Botones Inteligentes según Motivo**

| Motivo del Rechazo | Botón que Aparece | Acción |
|-------------------|-------------------|---------|
| "El monto del comprobante no coincide" | 💰 **Completar Pago** | Permite pagar la diferencia |
| "Monto incorrecto: diferencia de $10.000" | 💰 **Completar Pago** | Permite pagar la diferencia |
| "Comprobante no legible" | 📤 **Subir Nuevo Comprobante** | Permite resubir comprobante |
| "Comprobante borroso" | 📤 **Subir Nuevo Comprobante** | Permite resubir comprobante |
| "Comprobante falso" | 📤 **Subir Nuevo Comprobante** | Permite resubir comprobante |
| **Cualquier motivo** | 💬 **Contactar Soporte** | WhatsApp con info pre-cargada |

---

## 📱 Funcionalidades Implementadas

### 1. Botón "Completar Pago" 💰

**Cuándo aparece:** Si el motivo incluye palabras como "monto", "coincide", "diferencia", "precio"

**Qué hace:**
```javascript
function pagarDiferencia(idOrden, totalOriginal) {
    // Muestra confirmación explicando el problema
    const mensaje = `Tu orden fue rechazada porque el monto del comprobante 
                     no coincide con el total de $${totalOriginal}`;
    
    if (confirm(mensaje)) {
        // Redirige a página de pago específica para esta orden
        window.location.href = `../informacion/recuperar_pago.html?orden=${idOrden}`;
    }
}
```

**Flujo Propuesto:**
1. Cliente hace clic en "Completar Pago"
2. Se le muestra cuánto debe (diferencia)
3. Métodos de pago disponibles:
   - **Nequi** (pagar solo la diferencia)
   - **Wompi** (pagar solo la diferencia)
4. Sube nuevo comprobante con el monto completo
5. La orden vuelve a estado `pendiente_verificacion`

---

### 2. Botón "Subir Nuevo Comprobante" 📤

**Cuándo aparece:** Si el motivo incluye palabras como "comprobante", "legible", "falso", "inválido", "borroso"

**Qué hace:**
```javascript
function subirNuevoComprobante(idOrden) {
    // Muestra guía de lo que debe incluir el comprobante
    const mensaje = `Tu comprobante fue rechazado. 
                     Puedes subir un nuevo comprobante que sea:
                     ✓ Legible y claro
                     ✓ Con todos los datos visibles
                     ✓ Del monto correcto`;
    
    if (confirm(mensaje)) {
        // Redirige a página de resubir comprobante
        window.location.href = `../informacion/resubir_comprobante.html?orden=${idOrden}`;
    }
}
```

**Flujo Propuesto:**
1. Cliente hace clic en "Subir Nuevo Comprobante"
2. Se muestra formulario de carga de imagen
3. Validaciones:
   - Solo imágenes (JPG, PNG)
   - Tamaño máximo 5MB
   - Preview antes de subir
4. Al subir:
   - Reemplaza el comprobante anterior
   - La orden vuelve a `pendiente_verificacion`
   - Se notifica al admin

---

### 3. Botón "Contactar Soporte" 💬 (SIEMPRE disponible)

**Cuándo aparece:** En todas las órdenes rechazadas

**Qué hace:**
```javascript
function contactarSoporteRechazo(idOrden, nombreReloj, motivoRechazo) {
    const mensaje = `Hola! 👋
                     Mi orden #${idOrden} fue rechazada y necesito ayuda.
                     
                     📦 Reloj: ${nombreReloj}
                     ❌ Motivo: ${motivoRechazo}
                     
                     ¿Pueden ayudarme a resolver este problema?`;
    
    const url = `https://wa.me/+573173897119?text=${encodeURIComponent(mensaje)}`;
    window.open(url, '_blank');
}
```

**Ventaja:** El mensaje llega pre-cargado con:
- Número de orden
- Nombre del reloj
- Motivo del rechazo
- Cliente solo debe dar click y escribir detalles adicionales

---

## 🎨 Estilos de Botones

```css
/* Botón Completar Pago / Subir Comprobante */
.btn-recuperar {
    background: rgba(76, 175, 80, 0.1);
    border-color: #4CAF50;
    color: #4CAF50;
}

/* Botón WhatsApp */
.btn-whatsapp {
    background: rgba(37, 211, 102, 0.1);
    border-color: #25D366;
    color: #25D366;
}
```

---

## 🛠️ Implementación Backend (Pendiente)

### 1. `informacion/recuperar_pago.html`
Página para completar pago de diferencia:

```php
<?php
// recuperar_pago.php
$id_orden = $_GET['orden'];

// Obtener datos de la orden
$sql = "SELECT o.total, o.monto_pagado, r.nombre 
        FROM orden o
        JOIN orden_detalle od ON o.id_orden = od.id_orden
        JOIN reloj r ON od.id_reloj = r.id_reloj
        WHERE o.id_orden = ? AND o.estado = 'rechazado'";

// Calcular diferencia
$diferencia = $total - $monto_pagado;

// Mostrar formulario con:
// - Info de la orden
// - Monto faltante: $XXX
// - Métodos de pago (Nequi / Wompi)
// - Botón "Pagar Diferencia"
?>
```

### 2. `informacion/resubir_comprobante.html`
Página para resubir comprobante:

```php
<?php
// resubir_comprobante.php
$id_orden = $_GET['orden'];

// Verificar que la orden existe y está rechazada
$sql = "SELECT * FROM orden WHERE id_orden = ? AND estado = 'rechazado'";

// Mostrar formulario:
// - Drag & Drop de imagen
// - Preview del comprobante
// - Guía de lo que debe incluir
// - Botón "Subir Comprobante"

// Al subir:
// 1. Actualizar campo comprobante_pago
// 2. Cambiar estado a 'pendiente_verificacion'
// 3. Limpiar motivo_rechazo
// 4. Actualizar fecha_subida
// 5. Notificar admin (email/WhatsApp)
?>
```

---

## 🔄 Flujo Completo de Recuperación

### Ejemplo 1: Monto Incorrecto

```
1. Cliente paga $110.000 de un reloj de $135.000
2. Admin rechaza orden:
   - Estado → 'rechazado'
   - Motivo → "El monto del comprobante no coincide: falta $25.000"

3. Cliente ve en su perfil:
   ❌ ORDEN RECHAZADA
   💰 Botón "Completar Pago"
   💬 Botón "Contactar Soporte"

4. Cliente hace clic en "Completar Pago":
   → Redirige a recuperar_pago.html?orden=5
   → Ve: "Falta pagar: $25.000"
   → Paga diferencia por Nequi
   → Sube comprobante del pago adicional

5. Sistema:
   → Actualiza monto_pagado = $135.000
   → Cambia estado a 'pendiente_verificacion'
   → Notifica admin: "Orden #5 - Pago completado"

6. Admin verifica y aprueba
   → Estado → 'pagado' → 'enviado' → 'entregado'
```

### Ejemplo 2: Comprobante No Legible

```
1. Cliente sube comprobante borroso
2. Admin rechaza:
   - Estado → 'rechazado'
   - Motivo → "Comprobante no legible - Por favor sube una imagen más clara"

3. Cliente ve en su perfil:
   ❌ ORDEN RECHAZADA
   📤 Botón "Subir Nuevo Comprobante"
   💬 Botón "Contactar Soporte"

4. Cliente hace clic en "Subir Nuevo Comprobante":
   → Redirige a resubir_comprobante.html?orden=5
   → Ve guía: "Tu comprobante debe ser..."
   → Sube nueva foto clara

5. Sistema:
   → Reemplaza comprobante
   → Cambia estado a 'pendiente_verificacion'
   → Limpia motivo_rechazo
   → Notifica admin

6. Admin verifica y aprueba
```

---

## 📊 Tabla en BD (Sugerencia)

Agregar columna a la tabla `orden`:

```sql
ALTER TABLE orden ADD COLUMN monto_pagado DECIMAL(10,2) DEFAULT 0;
ALTER TABLE orden ADD COLUMN fecha_subida DATETIME DEFAULT NULL;
ALTER TABLE orden ADD COLUMN intentos_rechazo INT DEFAULT 0;
```

- **`monto_pagado`**: Monto real que pagó el cliente (puede ser menor al total)
- **`fecha_subida`**: Última vez que subió/resubió comprobante
- **`intentos_rechazo`**: Contador de rechazos (limitar a 3 intentos)

---

## ✅ Ventajas del Sistema

1. **Reduce abandono**: Cliente no pierde su orden, puede recuperarla
2. **Mejor UX**: Flujo claro de qué hacer ante rechazo
3. **Automatizado**: Botones inteligentes según motivo
4. **Eficiente**: Cliente paga solo diferencia (no todo de nuevo)
5. **Trazabilidad**: Historial de intentos y rechazos
6. **Soporte rápido**: WhatsApp con info pre-cargada

---

## 🚧 Próximos Pasos

1. ✅ **Botones inteligentes** - COMPLETADO
2. ✅ **Estilos diferenciados** - COMPLETADO
3. ⏳ **Crear `recuperar_pago.html`** - Pendiente
4. ⏳ **Crear `resubir_comprobante.html`** - Pendiente
5. ⏳ **Backend para procesar pago parcial** - Pendiente
6. ⏳ **Backend para resubir comprobante** - Pendiente
7. ⏳ **Notificaciones admin** cuando cliente recupera orden - Pendiente

---

## 💡 Alternativa Simplificada (Sin crear páginas)

Si no quieres crear las páginas `recuperar_pago.html` y `resubir_comprobante.html`, puedes usar solo WhatsApp:

```javascript
// En lugar de redirigir, abrir WhatsApp directamente
function pagarDiferencia(idOrden, totalOriginal) {
    const mensaje = `Hola! Necesito completar el pago de mi orden #${idOrden}.
                     
                     El total es $${totalOriginal} y ya hice un pago parcial.
                     ¿Cómo puedo pagar la diferencia?`;
    
    const url = `https://wa.me/+573173897119?text=${encodeURIComponent(mensaje)}`;
    window.open(url, '_blank');
}
```

**Ventaja:** Más rápido de implementar, soporte humano
**Desventaja:** Menos automatizado, requiere intervención manual

---

## 📱 Implementación Actual

Por ahora, el sistema está preparado para:
1. ✅ Detectar motivo de rechazo
2. ✅ Mostrar botones apropiados
3. ✅ WhatsApp siempre disponible
4. ⏳ `recuperar_pago.html` y `resubir_comprobante.html` redirigen pero aún no existen

**Puedes elegir:**
- Crear las páginas completas (más profesional)
- Usar solo WhatsApp (más rápido, soporte directo)

---

¿Quieres que implemente las páginas de recuperación completas o prefieres el flujo simplificado por WhatsApp?

