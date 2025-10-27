# 🎟️ Módulo de Códigos de Descuento - Instrucciones de Uso

## 📋 Características Implementadas

### ✅ **Funcionalidades Completas**

1. **Crear Códigos de Descuento**
   - Código personalizado (se convierte automáticamente a mayúsculas)
   - Porcentaje de descuento (1% - 100%)
   - Fecha de expiración

2. **Listar Códigos**
   - Vista en tabla con todos los códigos
   - Estados visuales: ✅ Activo, ⚠️ Expira Hoy, ❌ Expirado

3. **Editar Códigos**
   - Modificar código, porcentaje o fecha
   - Validación para evitar códigos duplicados

4. **Eliminar Códigos**
   - Eliminar códigos que ya no se necesiten
   - Confirmación antes de eliminar

5. **Verificar Códigos**
   - Endpoint para verificar si un código es válido
   - Útil para integración con el frontend

---

## 🚀 Cómo Usar el Módulo

### **Acceder al Módulo**
1. Inicia sesión como administrador
2. En el panel de administración, haz clic en la pestaña **"🎟️ Códigos de Descuento"**

### **Crear un Nuevo Código**
1. Haz clic en el botón **"➕ Crear Nuevo Código"**
2. Completa el formulario:
   - **Código**: Ejemplo `VERANO2025` (se convertirá a mayúsculas automáticamente)
   - **Porcentaje**: Ejemplo `20` (para 20% de descuento)
   - **Fecha de Expiración**: Selecciona la fecha límite
3. Haz clic en **"Guardar"**

### **Editar un Código Existente**
1. En la tabla de códigos, haz clic en el botón **"✏️"** (editar)
2. Modifica los campos que desees
3. Haz clic en **"Guardar"**

### **Eliminar un Código**
1. En la tabla de códigos, haz clic en el botón **"🗑️"** (eliminar)
2. Confirma la eliminación

---

## 🔧 Integración con el Frontend

### **Endpoint de Validación**
Para validar un código desde el frontend, usa:

```javascript
fetch('admin/php/gestionar_codigos.php?action=verificar&codigo=VERANO2025')
    .then(response => response.json())
    .then(data => {
        if (data.valido) {
            console.log('Descuento:', data.porcentaje + '%');
            // Aplicar descuento
        } else {
            console.log(data.mensaje); // "Código expirado" o "Código no encontrado"
        }
    });
```

### **Ejemplo de Aplicación de Descuento**
```javascript
const precioOriginal = 100000; // $100.000
const porcentajeDescuento = 20; // 20%
const precioFinal = precioOriginal * (1 - porcentajeDescuento / 100);
console.log('Precio final:', precioFinal); // $80.000
```

---

## 📊 Estados de los Códigos

| Estado | Icono | Descripción |
|--------|-------|-------------|
| **Activo** | ✅ | El código está vigente y puede ser usado |
| **Expira Hoy** | ⚠️ | El código expira hoy, último día de uso |
| **Expirado** | ❌ | El código ya no es válido |

---

## 🔒 Seguridad

- ✅ Solo administradores pueden acceder al módulo
- ✅ Validación de sesión en todos los endpoints
- ✅ Protección contra códigos duplicados
- ✅ Validación de porcentajes (1-100)
- ✅ Fechas de expiración validadas

---

## 📝 Notas Importantes

1. **Los códigos son case-insensitive** (se guardan en mayúsculas)
2. **Los porcentajes se guardan como decimales** en la BD (ej: 20% = 0.20)
3. **La expiración es al final del día** especificado
4. **No se pueden crear códigos duplicados**
5. **Los códigos expirados no se eliminan automáticamente** (para mantener historial)

---

## 🎯 Próximos Pasos Recomendados

Para integrar completamente el sistema de códigos de descuento:

1. **Agregar campo de código en el formulario de compra** (`informacion.html`)
2. **Crear validación en tiempo real** del código
3. **Actualizar el cálculo del total** aplicando el descuento
4. **Guardar el código usado** en la tabla `orden` (agregar columna `codigo_descuento`)
5. **Mostrar el descuento aplicado** en el panel de administración

---

## 📞 Soporte

Si encuentras algún problema o necesitas agregar funcionalidades adicionales, revisa:
- `admin/php/gestionar_codigos.php` - Backend (CRUD)
- `admin/js/panel.js` - Frontend (funciones JS)
- `admin/css/panel.css` - Estilos
- `admin/panel.php` - HTML del módulo



