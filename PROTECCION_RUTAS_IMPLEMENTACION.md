# 🔒 Sistema de Protección de Rutas - FINOSO

## 🎯 Objetivo

Controlar el acceso a las páginas según si el usuario tiene sesión activa o no.

**NOTA:** Este sistema usa el archivo **existente** `login/php/verificar_sesion.php` para mantener consistencia con todo el proyecto.

---

## 📁 Archivos del Sistema

### **1. `login/php/verificar_sesion.php`** (YA EXISTE)
API que devuelve el estado de la sesión en JSON
```json
{
  "logged_in": true,
  "id_usuario": 1,
  "nombre": "Julian",
  "correo": "correo@ejemplo.com"
}
```

### **2. `js/proteccion_rutas.js`** (NUEVO)
JavaScript con funciones para proteger rutas:
- `protegerRutaConSesion()` → Requiere sesión activa
- `protegerRutaSinSesion()` → Requiere NO tener sesión

---

## 🔧 Cómo Aplicar las Protecciones

### **Páginas que REQUIEREN SESIÓN (usuario logueado):**

Agregar al final del HTML, justo antes de `</body>`:

```html
<!-- Protección: requiere sesión -->
<script src="/finoso/js/proteccion_rutas.js"></script>
<script>
    protegerRutaConSesion();
</script>
</body>
```

**Aplicar en:**
- ✅ `perfil/perfil.php`
- ✅ `informacion-carrito/informacion-carrito.html`
- ✅ `informacion-carrito/informacion-carrito.php`

---

### **Páginas que REQUIEREN NO TENER SESIÓN (login/registro):**

Agregar al final del HTML, justo antes de `</body>`:

```html
<!-- Protección: no debe haber sesión -->
<script src="/finoso/js/proteccion_rutas.js"></script>
<script>
    protegerRutaSinSesion();
</script>
</body>
```

**Aplicar en:**
- ✅ `login/login.html`
- ✅ `login/registrarse/registrarse.html`
- ✅ `login/olvidar-contraseña/reset_password.html`

---

### **Páginas SIN PROTECCIÓN (acceso libre):**

**NO agregar** protección en:
- ❌ `index.html` (inicio público)
- ❌ `catalogo/catalogo.html` (catálogo público)
- ❌ `informacion/informacion.html` (ver producto individual)
- ❌ `informacion-favoritos/informacion-favoritos.html` (favoritos anónimos)

---

## 🧪 Cómo Probar

### **Test 1: Protección de Login (SIN sesión)**

1. **SIN sesión:**
   - Ve a: `http://127.0.0.1/finoso/login/login.html`
   - ✅ Debería permitir acceso (mostrar login)

2. **CON sesión:**
   - Inicia sesión
   - Intenta acceder a: `http://127.0.0.1/finoso/login/login.html`
   - ✅ Debería redirigir automáticamente a `/finoso/index.html`

---

### **Test 2: Protección de Perfil (CON sesión)**

1. **SIN sesión:**
   - Cierra sesión o usa navegación privada
   - Intenta acceder a: `http://127.0.0.1/finoso/perfil/perfil.php`
   - ✅ Debería redirigir a `/finoso/login/login.html`

2. **CON sesión:**
   - Inicia sesión
   - Ve a: `http://127.0.0.1/finoso/perfil/perfil.php`
   - ✅ Debería permitir acceso (mostrar perfil)

---

## 📊 Resumen de Protecciones

| Página | Protección | Redirige a |
|--------|-----------|------------|
| `login/login.html` | `protegerRutaSinSesion()` | `/index.html` |
| `login/registrarse/registrarse.html` | `protegerRutaSinSesion()` | `/index.html` |
| `perfil/perfil.php` | `protegerRutaConSesion()` | `/login/login.html` |
| `informacion-carrito/*` | `protegerRutaConSesion()` | `/login/login.html` |
| `informacion-favoritos/*` | ❌ Sin protección | - |
| `index.html` | ❌ Sin protección | - |
| `catalogo/*` | ❌ Sin protección | - |
| `informacion/*` | ❌ Sin protección | - |

---

**Fecha:** 27 de octubre de 2025  
**Estado:** ✅ Sistema actualizado para usar verificar_sesion.php existente

