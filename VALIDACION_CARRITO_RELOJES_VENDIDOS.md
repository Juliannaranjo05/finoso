# ✅ VALIDACIÓN DE DISPONIBILIDAD EN CARRITO

## 🎯 PROBLEMA RESUELTO

### **Antes:**
```
Usuario A → Agrega reloj al carrito
Usuario B → Compra ese reloj (se marca como vendido)
Usuario A → Inicia sesión
              → Reloj vendido sigue en su carrito ❌
              → Puede intentar finalizar compra ❌
              → Sistema no valida disponibilidad ❌
```

### **Ahora:**
```
Usuario A → Agrega reloj al carrito
Usuario B → Compra ese reloj (vendido = 1)
Usuario A → Inicia sesión
              → Reloj vendido sigue en su carrito
              → Clic en "Finalizar Compra" ✓
              → Sistema valida disponibilidad ✓
              → Detecta reloj vendido ✓
              → Elimina del carrito automáticamente ✓
              → Muestra mensaje al usuario ✓
              → Actualiza carrito visual ✓
```

---

## 🔧 IMPLEMENTACIÓN

### **1. Nuevo Endpoint PHP**

**Archivo:** `php/validar_disponibilidad_carrito.php`

```php
// Funcionalidad:
1. Obtiene todos los relojes del carrito del usuario
2. Verifica cada reloj:
   - ¿vendido = 1? → Reloj ya vendido
   - ¿disponible = 0? → Reloj no disponible
3. Si encuentra relojes no disponibles:
   - Los elimina automáticamente del carrito
   - Retorna lista de relojes eliminados
4. Si todo OK:
   - Permite continuar con la compra
```

**Respuesta JSON:**
```json
// Si hay relojes vendidos:
{
  "success": false,
  "relojes_vendidos": [
    {
      "id_reloj": 2,
      "nombre": "Patk Phlppe Bicolor Dorado - Negro",
      "vendido": 1,
      "disponible": 0
    }
  ],
  "mensaje": "Algunos relojes de tu carrito ya no están disponibles..."
}

// Si todo está OK:
{
  "success": true,
  "relojes_vendidos": [],
  "mensaje": "Todos los relojes están disponibles."
}
```

### **2. Modificación del Botón "Finalizar Compra"**

**Archivo:** `js/carrito.js`

**Antes:**
```javascript
button.addEventListener('click', () => {
    if (carrito_vacio) {
        alert('No hay productos');
        return;
    }
    window.location.href = 'informacion-carrito.html'; // Redirige directo
});
```

**Ahora:**
```javascript
button.addEventListener('click', async () => {
    if (carrito_vacio) {
        alert('No hay productos');
        return;
    }

    // 1. Deshabilitar botón
    boton.disabled = true;
    boton.textContent = 'Validando...';

    // 2. Validar disponibilidad
    const response = await fetch('validar_disponibilidad_carrito.php');
    const data = await response.json();

    // 3. Si hay relojes vendidos
    if (!data.success && data.relojes_vendidos.length > 0) {
        // Mostrar alerta con nombres
        alert('⚠️ Algunos relojes ya no están disponibles...');
        
        // Recargar carrito (actualiza visual)
        cargarCarrito();
        
        // Habilitar botón
        boton.disabled = false;
        return;
    }

    // 4. Todo OK, continuar
    window.location.href = 'informacion-carrito.html';
});
```

---

## 🎬 FLUJO COMPLETO

### **Escenario 1: Todos los relojes disponibles**

```
1. Usuario hace clic en "Finalizar Compra"
   ↓
2. Botón cambia a "Validando..." y se deshabilita
   ↓
3. JavaScript hace fetch a validar_disponibilidad_carrito.php
   ↓
4. PHP consulta BD:
   SELECT * FROM carrito c
   INNER JOIN reloj r ON c.id_reloj = r.id_reloj
   WHERE c.id_usuario = ? AND (r.vendido = 1 OR r.disponible = 0)
   ↓
5. No hay relojes vendidos
   ↓
6. Retorna: { "success": true }
   ↓
7. JavaScript redirige a informacion-carrito.html
   ↓
8. ✅ Usuario continúa con la compra normalmente
```

### **Escenario 2: Hay relojes vendidos**

```
1. Usuario hace clic en "Finalizar Compra"
   ↓
2. Botón cambia a "Validando..." y se deshabilita
   ↓
3. JavaScript hace fetch a validar_disponibilidad_carrito.php
   ↓
4. PHP consulta BD y encuentra reloj vendido
   ↓
5. PHP elimina reloj del carrito:
   DELETE FROM carrito WHERE id_carrito = ?
   ↓
6. Retorna: { 
      "success": false,
      "relojes_vendidos": [{"nombre": "Reloj X"}]
   }
   ↓
7. JavaScript muestra alerta:
   "⚠️ Algunos relojes ya no están disponibles:
   
   • Reloj X
   
   Han sido eliminados de tu carrito automáticamente."
   ↓
8. JavaScript recarga el carrito (cargarCarrito())
   ↓
9. Carrito visual se actualiza sin el reloj vendido
   ↓
10. Botón se habilita de nuevo
    ↓
11. Usuario puede revisar carrito actualizado
    ↓
12. Si quiere, puede intentar finalizar compra nuevamente
```

---

## 🧪 INSTRUCCIONES DE PRUEBA

### **Preparación:**

1. **Crea dos usuarios:**
   - Usuario A (usarás este)
   - Usuario B (o puedes hacer la compra sin sesión)

2. **Resetea relojes:**
   ```
   http://127.0.0.1/finoso/database/resetear_relojes_prueba.php
   ```

### **Prueba Completa:**

#### **PASO 1: Agregar reloj al carrito**
```
1. Inicia sesión como Usuario A
2. Ve al catálogo
3. Agrega Reloj #2 al carrito
4. NO finalices la compra todavía
5. Cierra sesión
```

#### **PASO 2: Otro usuario compra el reloj**
```
1. Abre modo incógnito (o cierra sesión)
2. Ve al catálogo
3. Compra el Reloj #2 (el que Usuario A tiene en carrito)
4. Completa la compra (como Usuario B o invitado)
5. Admin aprueba la orden
6. Reloj #2 ahora está marcado como vendido en BD
```

Verifica en phpMyAdmin:
```sql
SELECT id_reloj, nombre, vendido, disponible 
FROM reloj 
WHERE id_reloj = 2;

-- Debería mostrar: vendido = 1
```

#### **PASO 3: Usuario A intenta finalizar compra**
```
1. Vuelve como Usuario A (inicia sesión)
2. Abre el carrito
3. Deberías ver el Reloj #2 (aunque ya está vendido)
4. Haz clic en "Finalizar Compra"
5. El botón cambiará a "Validando..."
6. Aparecerá un alert:
   "⚠️ Algunos relojes ya no están disponibles:
   
   • Patk Phlppe Bicolor Dorado - Negro
   
   Han sido eliminados de tu carrito automáticamente."
7. Haz clic en "Aceptar"
8. El carrito se recargará automáticamente
9. El Reloj #2 ya NO aparecerá en el carrito
```

#### **PASO 4: Verificar en BD**
```sql
-- El reloj debe estar eliminado del carrito
SELECT * FROM carrito WHERE id_usuario = ID_USUARIO_A;

-- No debería aparecer el reloj #2
```

---

## 📊 CASOS DE USO

### **Caso 1: Usuario tiene 3 relojes, 1 vendido**
```
Carrito inicial:
- Reloj A ✓ disponible
- Reloj B ✗ vendido
- Reloj C ✓ disponible

Usuario clic "Finalizar Compra":
→ Sistema detecta Reloj B vendido
→ Elimina Reloj B del carrito
→ Muestra mensaje: "1 reloj eliminado"
→ Carrito queda con: Reloj A, Reloj C
→ Usuario puede continuar comprando A y C
```

### **Caso 2: Usuario tiene 2 relojes, ambos vendidos**
```
Carrito inicial:
- Reloj A ✗ vendido
- Reloj B ✗ vendido

Usuario clic "Finalizar Compra":
→ Sistema detecta ambos vendidos
→ Elimina ambos del carrito
→ Muestra mensaje: "2 relojes eliminados"
→ Carrito queda vacío
→ Muestra: "No hay productos en el carrito"
→ Botón "Finalizar Compra" se oculta
```

### **Caso 3: Todos disponibles**
```
Carrito inicial:
- Reloj A ✓ disponible
- Reloj B ✓ disponible

Usuario clic "Finalizar Compra":
→ Sistema valida ambos OK
→ Redirige a informacion-carrito.html
→ Usuario continúa normalmente
```

---

## 🔍 LOGS Y DEBUG

### **Ver en consola del navegador (F12):**
```javascript
// Al hacer clic en "Finalizar Compra"
console.log('Validando disponibilidad...');

// Si hay relojes vendidos
console.log('Relojes vendidos encontrados:', data.relojes_vendidos);

// Si hay error
console.error('Error al validar disponibilidad:', error);
```

### **Ver en logs del servidor:**
```bash
# Ubicación: C:\xampp\apache\logs\error.log

[VALIDAR-CARRITO] Relojes vendidos eliminados: 2 para usuario 5
```

---

## 🚨 VENTAJAS DEL SISTEMA

### **1. Seguridad** ✅
- Evita que usuarios compren relojes ya vendidos
- Validación en servidor (no manipulable desde cliente)

### **2. Experiencia de Usuario** ✅
- Mensaje claro de qué relojes fueron eliminados
- Eliminación automática (no manual)
- Carrito se actualiza al instante

### **3. Integridad de Datos** ✅
- Carrito siempre sincronizado con disponibilidad real
- No hay órdenes con relojes no disponibles

### **4. Prevención de Errores** ✅
- Catch temprano antes de llegar a informacion-carrito
- Evita procesos de pago inválidos

---

## 📝 CONSULTAS SQL ÚTILES

### **Ver relojes vendidos que están en carritos:**
```sql
SELECT 
    c.id_carrito,
    u.nombre AS usuario,
    r.nombre AS reloj,
    r.vendido,
    r.disponible,
    c.fecha_agregado
FROM carrito c
INNER JOIN usuario u ON c.id_usuario = u.id_usuario
INNER JOIN reloj r ON c.id_reloj = r.id_reloj
WHERE r.vendido = 1 OR r.disponible = 0;
```

### **Limpiar todos los carritos de relojes vendidos (manualmente):**
```sql
DELETE FROM carrito 
WHERE id_reloj IN (
    SELECT id_reloj 
    FROM reloj 
    WHERE vendido = 1 OR disponible = 0
);
```

### **Ver cuántos carritos tienen relojes vendidos:**
```sql
SELECT 
    COUNT(DISTINCT c.id_usuario) AS usuarios_afectados,
    COUNT(c.id_carrito) AS relojes_en_carrito_vendidos
FROM carrito c
INNER JOIN reloj r ON c.id_reloj = r.id_reloj
WHERE r.vendido = 1 OR r.disponible = 0;
```

---

## ✅ RESUMEN

### **Archivos Creados:**
```
✅ php/validar_disponibilidad_carrito.php  - Backend de validación
✅ VALIDACION_CARRITO_RELOJES_VENDIDOS.md  - Documentación
```

### **Archivos Modificados:**
```
✅ js/carrito.js  - Evento del botón "Finalizar Compra"
```

### **Funcionalidades:**
```
✅ Validación antes de finalizar compra
✅ Detección de relojes vendidos
✅ Eliminación automática del carrito
✅ Mensaje claro al usuario
✅ Actualización visual del carrito
✅ Prevención de compras inválidas
```

---

**Sistema implementado:** Octubre 2025  
**Estado:** ✅ Funcional y probado  
**Integración:** ✅ Compatible con sistema existente

