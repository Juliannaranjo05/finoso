# 🧪 PRUEBA RÁPIDA - VALIDACIÓN DE CARRITO

## ⚡ PRUEBA EN 5 MINUTOS

### **1️⃣ PREPARACIÓN (30 segundos)**
```
1. Resetear relojes:
   http://127.0.0.1/finoso/database/resetear_relojes_prueba.php

2. Iniciar sesión como cualquier usuario
```

---

### **2️⃣ AGREGAR AL CARRITO (1 minuto)**
```
1. Ve al catálogo
2. Agrega el Reloj #2 al carrito
3. Ve al carrito y verifica que esté ahí
4. NO finalices la compra todavía
5. Cierra sesión
```

---

### **3️⃣ MARCAR RELOJ COMO VENDIDO (1 minuto)**

**Opción A: Manualmente en phpMyAdmin**
```sql
UPDATE reloj 
SET vendido = 1, disponible = 0 
WHERE id_reloj = 2;
```

**Opción B: Hacer una compra real (más realista)**
```
1. Modo incógnito o sin sesión
2. Compra el Reloj #2
3. Admin aprueba la orden
4. Reloj se marca vendido automáticamente
```

---

### **4️⃣ PROBAR VALIDACIÓN (2 minutos)**
```
1. Vuelve a iniciar sesión como el usuario original
2. Abre el carrito (debería ver el Reloj #2)
3. Haz clic en "Finalizar Compra"
4. Observa:
   ✅ Botón cambia a "Validando..."
   ✅ Aparece alerta: "⚠️ Algunos relojes ya no están disponibles..."
   ✅ Lista el reloj eliminado
   ✅ Carrito se recarga automáticamente
   ✅ Reloj ya NO aparece en el carrito
```

---

### **5️⃣ VERIFICAR (30 segundos)**

**Verificar en phpMyAdmin:**
```sql
-- El reloj no debe estar en el carrito
SELECT * FROM carrito WHERE id_reloj = 2;
-- Resultado: 0 filas

-- El reloj está marcado como vendido
SELECT nombre, vendido FROM reloj WHERE id_reloj = 2;
-- Resultado: vendido = 1
```

---

## ✅ RESULTADOS ESPERADOS

### **SI TODO FUNCIONA:**
```
✅ Validación se ejecuta antes de redirigir
✅ Detecta reloj vendido
✅ Muestra mensaje claro
✅ Elimina reloj del carrito automáticamente
✅ Actualiza visual del carrito
✅ No permite continuar con reloj vendido
```

### **SI ALGO FALLA:**
```
❌ No aparece mensaje → Revisar consola (F12)
❌ No se elimina del carrito → Revisar php/validar_disponibilidad_carrito.php
❌ Redirige sin validar → Revisar js/carrito.js línea 219
```

---

## 🐛 DEBUG RÁPIDO

### **Abrir Consola del Navegador (F12):**
```javascript
// Debería aparecer al hacer clic en "Finalizar Compra":
Validando disponibilidad...

// Si hay relojes vendidos:
Relojes vendidos detectados: [{...}]

// Si hay error:
Error al validar disponibilidad: {...}
```

### **Ver Logs del Servidor:**
```
C:\xampp\apache\logs\error.log

Buscar:
[VALIDAR-CARRITO] Relojes vendidos eliminados: 1 para usuario X
```

---

## 🎯 CASOS DE PRUEBA ADICIONALES

### **Caso 1: Carrito con varios relojes, solo 1 vendido**
```
1. Agrega Reloj #1, #2, #3 al carrito
2. Marca solo Reloj #2 como vendido
3. Clic en "Finalizar Compra"
4. Esperado: Solo Reloj #2 eliminado, otros permanecen
```

### **Caso 2: Todos los relojes vendidos**
```
1. Agrega Reloj #1, #2 al carrito
2. Marca ambos como vendidos
3. Clic en "Finalizar Compra"
4. Esperado: Ambos eliminados, carrito vacío
```

### **Caso 3: Ningún reloj vendido (flujo normal)**
```
1. Agrega Reloj #1 al carrito
2. NO lo marques como vendido
3. Clic en "Finalizar Compra"
4. Esperado: Redirige a informacion-carrito.html normalmente
```

---

## 📸 CAPTURAS ESPERADAS

### **Mensaje de Alerta:**
```
┌─────────────────────────────────────────┐
│  ⚠️ Algunos relojes ya no están        │
│     disponibles:                        │
│                                          │
│  • Patk Phlppe Bicolor Dorado - Negro  │
│                                          │
│  Han sido eliminados de tu carrito      │
│  automáticamente.                       │
│                                          │
│             [ Aceptar ]                  │
└─────────────────────────────────────────┘
```

### **Botón Durante Validación:**
```
┌─────────────────┐
│  Validando...   │  (disabled)
└─────────────────┘
```

---

## ⏱️ TIEMPO ESTIMADO

```
Prueba completa: 5 minutos
Casos adicionales: +3 minutos por caso
Debug (si hay error): 5-10 minutos
```

---

**¡Listo para probar!** 🚀

