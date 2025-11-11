# 🔓 Sistema de Recuperación de Pago Sin Sesión

**Fecha:** 27 de Octubre, 2025  
**Estado:** ✅ Implementado y Funcional

---

## 📋 Problema Resuelto

Las órdenes creadas **sin sesión** (`id_usuario = NULL`) no podían acceder a la página de recuperación de pago cuando eran rechazadas, porque el sistema requería autenticación obligatoria.

---

## ✅ Solución Implementada

Se implementó un sistema de **validación por token** que permite acceder a las páginas de recuperación tanto **con sesión** como **sin sesión**.

---

## 🔧 Archivos Modificados

### 1. **`informacion/php/obtener_orden_rechazada.php`**
- ✅ Eliminada validación obligatoria de sesión
- ✅ Agregada validación por token de verificación
- ✅ 3 métodos de validación soportados:
  1. **Por token** (sin sesión) ← Nuevo
  2. **Por ID de usuario** (con sesión)
  3. **Por email** (compra sin sesión, luego logueado)

```php
// Opción 1: Validar por token (funciona sin sesión)
if ($token && $orden['token_verificacion'] === $token) {
    $acceso_valido = true;
}
// Opción 2: Validar por sesión
elseif ($id_usuario_sesion && $orden['id_usuario'] == $id_usuario_sesion) {
    $acceso_valido = true;
}
// Opción 3: Validar por email en sesión
elseif (isset($_SESSION['correo']) && $orden['correo'] === $_SESSION['correo']) {
    $acceso_valido = true;
}
```

---

### 2. **`informacion/php/subir_comprobante_diferencia.php`**
- ✅ Eliminada validación obligatoria de sesión
- ✅ Agregada validación por token
- ✅ Mismos 3 métodos de validación que `obtener_orden_rechazada.php`
- ✅ Token incluido en la redirección a `pago_exitoso_wompi.html`

---

### 3. **`informacion/recuperar_pago.html`**
- ✅ JavaScript actualizado para leer y pasar el `token` de la URL
- ✅ Campo `<input type="hidden" name="token">` agregado al formulario
- ✅ Logs de consola para debugging
- ✅ Redirección a `index.html` en lugar de `perfil.html` para usuarios sin sesión

---

### 4. **`admin/php/acciones.php`**
- ✅ Token agregado al enlace del **email** de rechazo
- ✅ Token agregado al enlace de **WhatsApp**
- ✅ Query SQL actualizada para obtener `token_verificacion`

**Antes:**
```php
$urlRecuperacion = "https://finoso.store/informacion/recuperar_pago.html?orden={$id_orden}";
```

**Ahora:**
```php
$urlRecuperacion = "https://finoso.store/informacion/recuperar_pago.html?orden={$id_orden}&token={$token_orden}";
```

---

### 5. **`admin/php/recordatorio_orden_rechazada.php`**
- ✅ Token agregado al enlace de WhatsApp del recordatorio

---

## 🎯 Flujo Completo

### **Escenario: Usuario SIN sesión (compra anónima)**

1. **Usuario hace compra sin registrarse** (`id_usuario = NULL`)
2. **Admin rechaza la orden** por monto incorrecto
3. **Usuario recibe email/WhatsApp** con enlace:
   ```
   https://finoso.store/informacion/recuperar_pago.html?orden=12&token=abc123xyz...
   ```
4. **Usuario hace click** en el enlace
5. ✅ **Acceso concedido por token** (sin necesidad de login)
6. **Usuario ve:**
   - Detalles de la orden
   - Monto total
   - Monto ya pagado
   - Diferencia que falta
7. **Usuario sube comprobante** de la diferencia
8. ✅ **Comprobante procesado** (validado por token)
9. **Redirección a confirmación** con todos los detalles
10. ✅ **Orden actualizada** con nuevo monto

---

### **Escenario: Usuario CON sesión**

1. Usuario logueado accede desde perfil o email
2. ✅ Validación por sesión (método tradicional)
3. Puede pagar la diferencia normalmente

---

### **Escenario: Compra sin sesión, luego logueado**

1. Usuario compró sin registrarse
2. Luego se registró con el mismo email
3. ✅ Validación por email en sesión
4. Puede acceder a su orden histórica

---

## 📊 Logs de Debugging

El sistema genera logs detallados en `php_error_log`:

### ✅ **Acceso exitoso:**
```
[RECUPERAR-PAGO] Acceso concedido por token válido - Orden #12
[SUBIR-DIFERENCIA] Acceso concedido por token válido - Orden #12
[SUBIR-DIFERENCIA] Redirigiendo con token para acceso sin sesión
```

### ❌ **Acceso denegado:**
```
[RECUPERAR-PAGO] Acceso denegado - Orden #12, Token: Proporcionado, Sesión: No
[SUBIR-DIFERENCIA] Acceso denegado - Orden #12, Token proporcionado: No, Sesión: No
```

---

## 🔒 Seguridad

### **Validación del Token:**
- ✅ Se compara `$token` (de URL/POST) con `token_verificacion` (de BD)
- ✅ Validación estricta con `===`
- ✅ Token único generado por `bin2hex(random_bytes(32))`
- ✅ 64 caracteres hexadecimales (alta entropía)

### **Prevención de Accesos No Autorizados:**
- ❌ Sin token Y sin sesión → Acceso denegado
- ❌ Token incorrecto → Acceso denegado
- ❌ Token de otra orden → Acceso denegado

---

## 🧪 Casos de Prueba

| # | Escenario | ID Usuario | Token | Sesión | Resultado |
|---|-----------|------------|-------|--------|-----------|
| 1 | Compra sin sesión, acceso por email | `NULL` | ✅ Válido | ❌ No | ✅ Acceso concedido |
| 2 | Compra con sesión, acceso por perfil | `5` | ❌ No | ✅ Sí | ✅ Acceso concedido |
| 3 | Compra sin sesión, sin token | `NULL` | ❌ No | ❌ No | ❌ Acceso denegado |
| 4 | Token incorrecto | `NULL` | ❌ Inválido | ❌ No | ❌ Acceso denegado |
| 5 | Compra sin sesión, luego logueado | `NULL` → `5` | ❌ No | ✅ Email | ✅ Acceso concedido |

---

## ✨ Mejoras Implementadas

1. **Inclusión Universal:** Todos los usuarios pueden recuperar pagos rechazados
2. **Sin Fricción:** No se requiere crear cuenta para completar un pago
3. **Seguridad:** Token único previene accesos no autorizados
4. **Flexibilidad:** 3 métodos de validación soportados
5. **Debugging:** Logs extensivos para rastrear problemas
6. **UX Consistente:** Misma experiencia con/sin sesión

---

## 📝 Notas Técnicas

- El token se genera al crear la orden y **nunca cambia**
- El token se incluye en **todos** los correos relacionados con la orden
- El token se pasa por **GET** (visualización) y **POST** (envío de comprobante)
- No hay límite de tiempo para usar el token (mientras la orden esté rechazada)
- Si el usuario completa el pago y la orden es aprobada, el flujo de recuperación ya no es accesible (validación `estado = 'rechazado'`)

---

## 🚀 Listo para Producción

✅ **Código actualizado**  
✅ **Validaciones implementadas**  
✅ **Logs de debugging habilitados**  
✅ **Seguridad verificada**  
✅ **Compatible con todos los flujos de compra**

---

**Última actualización:** 27/10/2025

