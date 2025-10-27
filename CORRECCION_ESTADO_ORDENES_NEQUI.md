# Corrección: Órdenes Llegaban como "Pagado" sin Verificación

## 🐛 Problema Identificado

Las órdenes pagadas con **Nequi** (carrito y favoritos) llegaban al panel de administración con estado **"Pagado"** directamente, sin pasar por el proceso de verificación del comprobante.

### Síntomas

- ❌ Órdenes con estado `pagado` sin verificar
- ❌ Comprobante dice "Sin verificar"
- ❌ Admin no puede **"Verificar Comprobante"**, solo puede **"Marcar Enviado"** o **"Revertir Aprobación"**
- ❌ Se saltaba el paso de revisión del comprobante

### Estados Esperados

El flujo correcto debería ser:

1. **Usuario sube comprobante** → `estado = 'pendiente_verificacion'`
2. **Admin verifica comprobante** → `estado = 'pagado'`
3. **Admin marca como enviado** → `estado = 'entregado'`

## 🔍 Causa Raíz

En los archivos de subida de comprobante para **carrito** y **favoritos**, el estado se estaba definiendo como `'pagado'` directamente:

### `informacion-carrito/php/subir_comprobante-carrito.php` (línea 272)

```php
$param_estado = 'pagado';  // ❌ INCORRECTO
$param_metodo = 'nequi';
```

### `informacion-favoritos/php/subir_comprobante-carrito.php` (línea 297)

```php
$param_estado = 'pagado';  // ❌ INCORRECTO
$param_metodo = 'nequi';
```

Esto causaba que al insertar la orden en la BD, se guardara con `estado = 'pagado'` sin pasar por la verificación.

### ¿Por qué solo afectaba a carrito y favoritos?

El archivo de **compra individual** (`informacion/php/subir_comprobante.php`) SÍ estaba correcto:

```php
INSERT INTO orden (..., estado, ...) VALUES (..., 'pendiente_verificacion', ...)  // ✅ CORRECTO
```

Por eso las compras individuales llegaban correctamente como "Verificación", pero las del carrito y favoritos llegaban como "Pagado".

## ✅ Solución Implementada

### 1. Corregido `informacion-carrito/php/subir_comprobante-carrito.php`

```php
// Antes:
$param_estado = 'pagado';  // ❌

// Ahora:
$param_estado = 'pendiente_verificacion';  // ✅ Estado inicial al subir comprobante
```

### 2. Corregido `informacion-favoritos/php/subir_comprobante-carrito.php`

```php
// Antes:
$param_estado = 'pagado';  // ❌

// Ahora:
$param_estado = 'pendiente_verificacion';  // ✅ Estado inicial al subir comprobante
```

## 📋 Flujo Correcto Ahora

### Todos los métodos de pago Nequi (individual, carrito, favoritos)

1. **Usuario sube comprobante**
   - Orden se crea con `estado = 'pendiente_verificacion'`
   - Aparece en "Órdenes Pendientes de Verificación"
   - Admin ve botones: **"✓ Verificar Comprobante"** y **"❌ Rechazar"**

2. **Admin verifica comprobante** (clic en "✓ Verificar Comprobante")
   - Estado cambia a `pagado`
   - Se genera código de descuento (si tiene sesión)
   - Se envía correo con confirmación
   - Aparece botón: **"🚚 Marcar Enviado"**

3. **Admin marca como enviado**
   - Estado cambia a `entregado`
   - Se envía correo de envío con guía
   - Orden se mueve a "Órdenes Entregadas"

## 🎯 Resultado

- ✅ Todas las órdenes Nequi llegan como `pendiente_verificacion`
- ✅ Admin debe verificar el comprobante antes de aprobar
- ✅ Flujo de verificación unificado para todos los métodos de pago
- ✅ No se generan códigos de descuento sin verificación

## 🧪 Prueba

1. **Realizar compra** desde carrito o favoritos con Nequi
2. **Subir comprobante**
3. **Ir al panel de admin**
4. **Verificar que la orden aparece** en "Órdenes Pendientes de Verificación"
5. **Verificar que el estado es** "Verificación" (no "Pagado")
6. **Verificar que aparece el botón** "✓ Verificar Comprobante"

## 📁 Archivos Modificados

1. `informacion-carrito/php/subir_comprobante-carrito.php` - Estado corregido a `pendiente_verificacion`
2. `informacion-favoritos/php/subir_comprobante-carrito.php` - Estado corregido a `pendiente_verificacion`

---

**Fecha**: 27 de octubre de 2025  
**Estado**: ✅ Implementado y probado


## 🐛 Problema Identificado

Las órdenes pagadas con **Nequi** (carrito y favoritos) llegaban al panel de administración con estado **"Pagado"** directamente, sin pasar por el proceso de verificación del comprobante.

### Síntomas

- ❌ Órdenes con estado `pagado` sin verificar
- ❌ Comprobante dice "Sin verificar"
- ❌ Admin no puede **"Verificar Comprobante"**, solo puede **"Marcar Enviado"** o **"Revertir Aprobación"**
- ❌ Se saltaba el paso de revisión del comprobante

### Estados Esperados

El flujo correcto debería ser:

1. **Usuario sube comprobante** → `estado = 'pendiente_verificacion'`
2. **Admin verifica comprobante** → `estado = 'pagado'`
3. **Admin marca como enviado** → `estado = 'entregado'`

## 🔍 Causa Raíz

En los archivos de subida de comprobante para **carrito** y **favoritos**, el estado se estaba definiendo como `'pagado'` directamente:

### `informacion-carrito/php/subir_comprobante-carrito.php` (línea 272)

```php
$param_estado = 'pagado';  // ❌ INCORRECTO
$param_metodo = 'nequi';
```

### `informacion-favoritos/php/subir_comprobante-carrito.php` (línea 297)

```php
$param_estado = 'pagado';  // ❌ INCORRECTO
$param_metodo = 'nequi';
```

Esto causaba que al insertar la orden en la BD, se guardara con `estado = 'pagado'` sin pasar por la verificación.

### ¿Por qué solo afectaba a carrito y favoritos?

El archivo de **compra individual** (`informacion/php/subir_comprobante.php`) SÍ estaba correcto:

```php
INSERT INTO orden (..., estado, ...) VALUES (..., 'pendiente_verificacion', ...)  // ✅ CORRECTO
```

Por eso las compras individuales llegaban correctamente como "Verificación", pero las del carrito y favoritos llegaban como "Pagado".

## ✅ Solución Implementada

### 1. Corregido `informacion-carrito/php/subir_comprobante-carrito.php`

```php
// Antes:
$param_estado = 'pagado';  // ❌

// Ahora:
$param_estado = 'pendiente_verificacion';  // ✅ Estado inicial al subir comprobante
```

### 2. Corregido `informacion-favoritos/php/subir_comprobante-carrito.php`

```php
// Antes:
$param_estado = 'pagado';  // ❌

// Ahora:
$param_estado = 'pendiente_verificacion';  // ✅ Estado inicial al subir comprobante
```

## 📋 Flujo Correcto Ahora

### Todos los métodos de pago Nequi (individual, carrito, favoritos)

1. **Usuario sube comprobante**
   - Orden se crea con `estado = 'pendiente_verificacion'`
   - Aparece en "Órdenes Pendientes de Verificación"
   - Admin ve botones: **"✓ Verificar Comprobante"** y **"❌ Rechazar"**

2. **Admin verifica comprobante** (clic en "✓ Verificar Comprobante")
   - Estado cambia a `pagado`
   - Se genera código de descuento (si tiene sesión)
   - Se envía correo con confirmación
   - Aparece botón: **"🚚 Marcar Enviado"**

3. **Admin marca como enviado**
   - Estado cambia a `entregado`
   - Se envía correo de envío con guía
   - Orden se mueve a "Órdenes Entregadas"

## 🎯 Resultado

- ✅ Todas las órdenes Nequi llegan como `pendiente_verificacion`
- ✅ Admin debe verificar el comprobante antes de aprobar
- ✅ Flujo de verificación unificado para todos los métodos de pago
- ✅ No se generan códigos de descuento sin verificación

## 🧪 Prueba

1. **Realizar compra** desde carrito o favoritos con Nequi
2. **Subir comprobante**
3. **Ir al panel de admin**
4. **Verificar que la orden aparece** en "Órdenes Pendientes de Verificación"
5. **Verificar que el estado es** "Verificación" (no "Pagado")
6. **Verificar que aparece el botón** "✓ Verificar Comprobante"

## 📁 Archivos Modificados

1. `informacion-carrito/php/subir_comprobante-carrito.php` - Estado corregido a `pendiente_verificacion`
2. `informacion-favoritos/php/subir_comprobante-carrito.php` - Estado corregido a `pendiente_verificacion`

---

**Fecha**: 27 de octubre de 2025  
**Estado**: ✅ Implementado y probado

