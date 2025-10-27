# Corrección: Productos No Se Cargan en informacion-carrito

## 🐛 Problema Identificado

Después de usar **favoritos**, al iniciar sesión y acceder al **carrito normal** (`informacion-carrito`), los productos no se cargaban. La página mostraba "Cargando..." pero nunca se mostraban los relojes.

### Síntomas

- ✅ Página de "Finalizar Compra" se carga
- ❌ Productos no se muestran (solo "Cargando...")
- ❌ No se llama al endpoint `/php/mostrar_carrito.php`
- ❌ Consola no muestra logs de carga de carrito

## 🔍 Causa Raíz

El archivo `informacion-carrito/js/productos.js` detecta el origen de la compra mediante `sessionStorage.getItem('origen_compra')`:

```javascript
if (origenCompra === 'favoritos') {
    cargarDesdeFavoritos();  // Carga desde localStorage
} else {
    cargarDesdeCarrito();    // Carga desde BD
}
```

**El problema:** Cuando el usuario usaba **favoritos** primero, se guardaba en sessionStorage:

```javascript
sessionStorage.setItem('origen_compra', 'favoritos');
```

Luego, al iniciar sesión y acceder al **carrito normal**, el código seguía leyendo `origen_compra = 'favoritos'` y intentaba cargar desde localStorage (que estaba vacío), en lugar de cargar desde la BD del carrito.

## ✅ Solución Implementada

### 1. Limpiar sessionStorage al Entrar a informacion-carrito

Agregada limpieza automática del sessionStorage al inicio de `productos.js`:

```javascript
// 🔥 LIMPIAR sessionStorage de favoritos si estamos en informacion-carrito
// Esto evita que se cargue desde favoritos cuando se debe cargar desde carrito BD
console.log('🧹 Limpiando sessionStorage para carrito normal...');
sessionStorage.removeItem('origen_compra');
sessionStorage.removeItem('ids_relojes_compra');
console.log('✅ SessionStorage limpiado');
```

Esto garantiza que **siempre** se cargue desde el carrito de la BD cuando se accede a `informacion-carrito`.

### 2. Logs Mejorados

Agregados logs más descriptivos para identificar rápidamente de dónde se están cargando los productos:

```javascript
const origenCompra = sessionStorage.getItem('origen_compra');
console.log('🔍 Verificando origen de compra:', origenCompra ?? 'NULL (carrito por defecto)');

if (origenCompra === 'favoritos') {
    console.warn('⚠️ DETECTADO: origen_compra = favoritos (esto NO debería pasar en informacion-carrito)');
    cargarDesdeFavoritos();
} else {
    console.log("✅ Cargando productos desde CARRITO NORMAL (BD)");
    cargarDesdeCarrito();
}
```

### 3. Cache Busting Actualizado

Actualizado el cache busting en `informacion-carrito.html`:

```html
<!-- Antes (no funcionaba porque .html no procesa PHP) -->
<script src="js/productos.js?v=<?php echo time(); ?>"></script>

<!-- Ahora (timestamp manual) -->
<script src="js/productos.js?v=20251027a"></script>
```

## 📋 Flujo Correcto Ahora

### Escenario: Usuario usa favoritos, luego inicia sesión y usa carrito

1. **Usuario usa favoritos** (sin sesión):
   - Se guarda `sessionStorage.setItem('origen_compra', 'favoritos')`
   - Productos se guardan en `localStorage`

2. **Usuario inicia sesión** y va a `informacion-carrito`:
   - ✅ Al cargar la página, se **limpia automáticamente** el sessionStorage
   - ✅ `origen_compra` queda en `NULL`
   - ✅ El código carga desde **carrito BD** (correcto)

3. **Productos se muestran** correctamente desde la BD

## 🧪 Logs Esperados

Después de la corrección, en la consola del navegador deberías ver:

```
🧹 Limpiando sessionStorage para carrito normal...
✅ SessionStorage limpiado
✅ Sesión verificada, cargando productos del carrito BD...
🔍 DEBUG productos.js - Archivo cargado correctamente
🔍 Verificando origen de compra: NULL (carrito por defecto)
✅ Cargando productos desde CARRITO NORMAL (BD)
🔍 DEBUG productos.js - Iniciando fetch de carrito
🔍 DEBUG productos.js - Respuesta recibida: ...
🎨 Renderizando productos...
```

## 🎯 Resultado

- ✅ Productos se cargan correctamente desde el carrito BD
- ✅ No se mezclan datos de favoritos con carrito
- ✅ sessionStorage se limpia automáticamente
- ✅ Logs claros para debug futuro

## 📁 Archivos Modificados

1. `informacion-carrito/js/productos.js` - Limpieza de sessionStorage y logs mejorados
2. `informacion-carrito/informacion-carrito.html` - Cache busting actualizado

---

**Fecha**: 27 de octubre de 2025  
**Estado**: ✅ Implementado y probado


## 🐛 Problema Identificado

Después de usar **favoritos**, al iniciar sesión y acceder al **carrito normal** (`informacion-carrito`), los productos no se cargaban. La página mostraba "Cargando..." pero nunca se mostraban los relojes.

### Síntomas

- ✅ Página de "Finalizar Compra" se carga
- ❌ Productos no se muestran (solo "Cargando...")
- ❌ No se llama al endpoint `/php/mostrar_carrito.php`
- ❌ Consola no muestra logs de carga de carrito

## 🔍 Causa Raíz

El archivo `informacion-carrito/js/productos.js` detecta el origen de la compra mediante `sessionStorage.getItem('origen_compra')`:

```javascript
if (origenCompra === 'favoritos') {
    cargarDesdeFavoritos();  // Carga desde localStorage
} else {
    cargarDesdeCarrito();    // Carga desde BD
}
```

**El problema:** Cuando el usuario usaba **favoritos** primero, se guardaba en sessionStorage:

```javascript
sessionStorage.setItem('origen_compra', 'favoritos');
```

Luego, al iniciar sesión y acceder al **carrito normal**, el código seguía leyendo `origen_compra = 'favoritos'` y intentaba cargar desde localStorage (que estaba vacío), en lugar de cargar desde la BD del carrito.

## ✅ Solución Implementada

### 1. Limpiar sessionStorage al Entrar a informacion-carrito

Agregada limpieza automática del sessionStorage al inicio de `productos.js`:

```javascript
// 🔥 LIMPIAR sessionStorage de favoritos si estamos en informacion-carrito
// Esto evita que se cargue desde favoritos cuando se debe cargar desde carrito BD
console.log('🧹 Limpiando sessionStorage para carrito normal...');
sessionStorage.removeItem('origen_compra');
sessionStorage.removeItem('ids_relojes_compra');
console.log('✅ SessionStorage limpiado');
```

Esto garantiza que **siempre** se cargue desde el carrito de la BD cuando se accede a `informacion-carrito`.

### 2. Logs Mejorados

Agregados logs más descriptivos para identificar rápidamente de dónde se están cargando los productos:

```javascript
const origenCompra = sessionStorage.getItem('origen_compra');
console.log('🔍 Verificando origen de compra:', origenCompra ?? 'NULL (carrito por defecto)');

if (origenCompra === 'favoritos') {
    console.warn('⚠️ DETECTADO: origen_compra = favoritos (esto NO debería pasar en informacion-carrito)');
    cargarDesdeFavoritos();
} else {
    console.log("✅ Cargando productos desde CARRITO NORMAL (BD)");
    cargarDesdeCarrito();
}
```

### 3. Cache Busting Actualizado

Actualizado el cache busting en `informacion-carrito.html`:

```html
<!-- Antes (no funcionaba porque .html no procesa PHP) -->
<script src="js/productos.js?v=<?php echo time(); ?>"></script>

<!-- Ahora (timestamp manual) -->
<script src="js/productos.js?v=20251027a"></script>
```

## 📋 Flujo Correcto Ahora

### Escenario: Usuario usa favoritos, luego inicia sesión y usa carrito

1. **Usuario usa favoritos** (sin sesión):
   - Se guarda `sessionStorage.setItem('origen_compra', 'favoritos')`
   - Productos se guardan en `localStorage`

2. **Usuario inicia sesión** y va a `informacion-carrito`:
   - ✅ Al cargar la página, se **limpia automáticamente** el sessionStorage
   - ✅ `origen_compra` queda en `NULL`
   - ✅ El código carga desde **carrito BD** (correcto)

3. **Productos se muestran** correctamente desde la BD

## 🧪 Logs Esperados

Después de la corrección, en la consola del navegador deberías ver:

```
🧹 Limpiando sessionStorage para carrito normal...
✅ SessionStorage limpiado
✅ Sesión verificada, cargando productos del carrito BD...
🔍 DEBUG productos.js - Archivo cargado correctamente
🔍 Verificando origen de compra: NULL (carrito por defecto)
✅ Cargando productos desde CARRITO NORMAL (BD)
🔍 DEBUG productos.js - Iniciando fetch de carrito
🔍 DEBUG productos.js - Respuesta recibida: ...
🎨 Renderizando productos...
```

## 🎯 Resultado

- ✅ Productos se cargan correctamente desde el carrito BD
- ✅ No se mezclan datos de favoritos con carrito
- ✅ sessionStorage se limpia automáticamente
- ✅ Logs claros para debug futuro

## 📁 Archivos Modificados

1. `informacion-carrito/js/productos.js` - Limpieza de sessionStorage y logs mejorados
2. `informacion-carrito/informacion-carrito.html` - Cache busting actualizado

---

**Fecha**: 27 de octubre de 2025  
**Estado**: ✅ Implementado y probado

