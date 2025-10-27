# 📋 Lista Completa de Pruebas del Sistema FINOSO

## 🎯 Objetivo

Probar **TODAS** las funcionalidades del sistema antes de producción.

---

## ✅ **Checklist de Pruebas**

### **1. AUTENTICACIÓN Y USUARIOS** 👤

#### **1.1 Registro de Usuario**
- [ ] Registrar nuevo usuario con correo válido
- [ ] Verificar que llegue email de verificación
- [ ] Verificar cuenta con token del email
- [ ] Intentar registrar con correo duplicado (debe fallar)
- [ ] Intentar registrar con correo inválido (debe fallar)
- [ ] Verificar que contraseña se encripte (hash en BD)

#### **1.2 Login**
- [ ] Login con credenciales correctas
- [ ] Login con credenciales incorrectas (debe fallar)
- [ ] Login con cuenta no verificada (debe fallar o avisar)
- [ ] Verificar que sesión persista al navegar
- [ ] Cerrar sesión y verificar redirección

#### **1.3 Recuperación de Contraseña**
- [ ] Solicitar reset de contraseña
- [ ] Verificar que llegue email con token
- [ ] Cambiar contraseña con token válido
- [ ] Intentar usar token expirado (debe fallar)
- [ ] Login con nueva contraseña

#### **1.4 Roles**
- [ ] Crear usuario normal (rol: usuario)
- [ ] Crear usuario admin (rol: administrador)
- [ ] Verificar que admin pueda acceder al panel
- [ ] Verificar que usuario normal NO pueda acceder al panel

---

### **2. CATÁLOGO Y PRODUCTOS** 🛍️

#### **2.1 Visualización**
- [ ] Ver catálogo completo en `/catalogo/catalogo.html`
- [ ] Ver todos los relojes en `index.html`
- [ ] Verificar que imágenes carguen correctamente
- [ ] Verificar precios formateados (ej: $125.000)
- [ ] Verificar descuentos visibles (si aplica)
- [ ] Ver detalles de un reloj individual

#### **2.2 Filtros**
- [ ] Filtrar por marca (Richard Mille, Patek Philippe, etc.)
- [ ] Filtrar por rango de precio
- [ ] Filtrar por disponibilidad
- [ ] Filtrar por descuentos activos
- [ ] Combinar múltiples filtros
- [ ] Limpiar filtros y ver todos de nuevo

#### **2.3 Búsqueda**
- [ ] Buscar por nombre de reloj
- [ ] Buscar por marca
- [ ] Buscar término que no existe (sin resultados)
- [ ] Verificar que búsqueda sea case-insensitive

---

### **3. CARRITO** 🛒

#### **3.1 Agregar al Carrito (CON sesión)**
- [ ] Agregar 1 reloj al carrito
- [ ] Agregar 2 relojes diferentes
- [ ] Agregar 3 relojes diferentes
- [ ] Verificar contador del carrito en header
- [ ] Verificar que carrito persista al recargar página
- [ ] Verificar que carrito se guarde en BD (tabla `carrito`)

#### **3.2 Agregar al Carrito (SIN sesión - Anónimo)**
- [ ] Cerrar sesión
- [ ] Agregar 1 reloj al carrito
- [ ] Verificar que use `localStorage` (no BD)
- [ ] Recargar página y verificar persistencia
- [ ] Verificar que NO se guarde en tabla `carrito` de BD

#### **3.3 Ver Carrito**
- [ ] Abrir modal de carrito
- [ ] Ver productos agregados
- [ ] Ver precio individual de cada producto
- [ ] Ver precio con descuento (si aplica)
- [ ] Ver subtotal
- [ ] Ver costo de envío (si ya se calculó)
- [ ] Ver total general

#### **3.4 Modificar Carrito**
- [ ] Eliminar 1 producto del carrito
- [ ] Eliminar todos los productos
- [ ] Verificar que se actualice BD (si hay sesión)
- [ ] Verificar que se actualice `localStorage` (si no hay sesión)

#### **3.5 Validaciones**
- [ ] Intentar agregar reloj marcado como "vendido" (debe fallar)
- [ ] Intentar agregar reloj no disponible (debe fallar)
- [ ] Verificar que no se puedan agregar duplicados

---

### **4. FAVORITOS** ⭐

#### **4.1 Agregar a Favoritos (CON sesión)**
- [ ] Agregar 1 reloj a favoritos
- [ ] Agregar 2 relojes diferentes
- [ ] Verificar ícono de corazón lleno
- [ ] Verificar que persista al recargar
- [ ] Verificar que se guarde en tabla `carrito` o similar con flag especial

#### **4.2 Agregar a Favoritos (SIN sesión)**
- [ ] Cerrar sesión
- [ ] Agregar 1 reloj a favoritos
- [ ] Verificar que use `localStorage`
- [ ] Recargar y verificar persistencia
- [ ] Iniciar sesión y verificar que favoritos se mantengan

#### **4.3 Ver Favoritos**
- [ ] Ir a página de favoritos (`informacion-favoritos/`)
- [ ] Ver lista completa de favoritos
- [ ] Ver precios actualizados
- [ ] Ver descuentos activos

#### **4.4 Comprar desde Favoritos**
- [ ] Seleccionar productos de favoritos
- [ ] Ir a "Finalizar Compra"
- [ ] Completar formulario
- [ ] Pagar con Nequi
- [ ] Verificar que orden se cree correctamente

#### **4.5 Eliminar de Favoritos**
- [ ] Quitar 1 producto de favoritos
- [ ] Verificar actualización en BD o localStorage
- [ ] Verificar que ícono cambie a corazón vacío

---

### **5. CÓDIGOS DE DESCUENTO** 🎟️

#### **5.1 Aplicar Código (Individual - CON sesión)**
- [ ] Ir a detalle de un reloj
- [ ] Ingresar código de descuento válido
- [ ] Verificar que precio se actualice visualmente
- [ ] Verificar que descuento persista al recargar
- [ ] Verificar que `usuario_codigo_descuento.activo = 0`
- [ ] Verificar que `id_reloj` se guarde
- [ ] Intentar aplicar código ya usado (debe fallar)
- [ ] Intentar aplicar código de otro usuario (debe fallar)
- [ ] Intentar aplicar código expirado (debe fallar)

#### **5.2 Código NO Aplicable a Carrito/Favoritos**
- [ ] Agregar reloj al carrito
- [ ] Verificar que NO haya opción de código en carrito
- [ ] Comprar desde favoritos
- [ ] Verificar que NO haya opción de código

#### **5.3 Verificar en Perfil**
- [ ] Ir a perfil de usuario
- [ ] Ver sección "Mis Códigos"
- [ ] Ver códigos disponibles
- [ ] Ver códigos usados (sin botón copiar)
- [ ] Ver códigos expirados
- [ ] Copiar código disponible

#### **5.4 Generación de Códigos (Admin)**
- [ ] Admin aprueba orden de usuario CON sesión
- [ ] Verificar que se genere código automáticamente
- [ ] Verificar que se asigne a `usuario_codigo_descuento`
- [ ] Verificar que se envíe por correo
- [ ] Admin aprueba orden anónima (SIN sesión)
- [ ] Verificar que NO se genere código
- [ ] Verificar que email diga "registrate para códigos"

---

### **6. PROCESO DE COMPRA INDIVIDUAL** 🛍️

#### **6.1 Compra Individual CON Sesión**
- [ ] Ir a detalle de reloj
- [ ] Click en "Comprar Ahora"
- [ ] Verificar que cargue formulario con datos del usuario
- [ ] Seleccionar departamento
- [ ] Seleccionar ciudad
- [ ] Verificar que costo de envío se calcule automáticamente
- [ ] Completar dirección, barrio, referencias
- [ ] Ver resumen de compra con total correcto
- [ ] Seleccionar método de pago (Nequi)
- [ ] Subir comprobante de pago
- [ ] **NUEVO:** Ingresar "Monto pagado" (opcional)
- [ ] Verificar redirección a "Comprobante Recibido"
- [ ] Verificar que orden se cree en BD con `estado = pendiente_verificacion`
- [ ] Verificar que orden_detalle se cree correctamente
- [ ] Verificar que llegue email de confirmación
- [ ] Verificar token de verificación único

#### **6.2 Compra Individual SIN Sesión (Anónimo)**
- [ ] Cerrar sesión
- [ ] Ir a detalle de reloj
- [ ] Click en "Comprar Ahora"
- [ ] Completar formulario manualmente (nombre, correo, cédula, etc.)
- [ ] Seleccionar departamento y ciudad
- [ ] Completar datos de envío
- [ ] Subir comprobante
- [ ] Ingresar monto pagado
- [ ] Verificar que orden se cree con `id_usuario = NULL`
- [ ] Verificar que NO se genere código de descuento
- [ ] Verificar email con mensaje para registrarse

#### **6.3 Compra con Código Aplicado**
- [ ] Aplicar código a un reloj
- [ ] Comprar ese reloj
- [ ] Verificar que precio final sea con descuento
- [ ] Verificar que `usuario_codigo_descuento.id_orden` se actualice
- [ ] Verificar que código quede marcado como usado en perfil

#### **6.4 Validación de Formulario**
- [ ] Intentar enviar sin comprobante (debe fallar)
- [ ] Intentar enviar sin seleccionar ciudad (debe fallar)
- [ ] Intentar enviar con correo inválido (debe fallar)
- [ ] Intentar enviar con cédula vacía (debe fallar)

---

### **7. PROCESO DE COMPRA POR CARRITO** 🛒

#### **7.1 Compra Carrito CON Sesión**
- [ ] Agregar 2-3 relojes al carrito
- [ ] Click en "Finalizar Compra"
- [ ] Verificar que cargue página `informacion-carrito/informacion-carrito.php`
- [ ] Verificar que productos se muestren correctamente
- [ ] Verificar que precios individuales sean correctos
- [ ] Verificar que subtotal sea correcto
- [ ] Seleccionar departamento y ciudad
- [ ] Verificar cálculo de costo de envío
- [ ] Verificar total general correcto
- [ ] Completar formulario
- [ ] Subir comprobante Nequi
- [ ] Ingresar monto pagado
- [ ] Verificar que 1 orden se cree con múltiples productos en `orden_detalle`
- [ ] Verificar que email llegue con lista de productos
- [ ] Verificar que carrito se limpie después de compra

#### **7.2 Compra Carrito SIN Sesión**
- [ ] Cerrar sesión
- [ ] Agregar productos al carrito (localStorage)
- [ ] Finalizar compra
- [ ] Completar formulario manualmente
- [ ] Subir comprobante
- [ ] Verificar orden con `id_usuario = NULL`
- [ ] Verificar que NO se genere código
- [ ] Verificar email anónimo

#### **7.3 Validación de Relojes Vendidos**
- [ ] Agregar reloj al carrito
- [ ] Desde admin, marcar ese reloj como vendido
- [ ] Intentar finalizar compra
- [ ] Verificar que se detecte y se rechace

---

### **8. PROCESO DE COMPRA POR FAVORITOS** ⭐

#### **8.1 Compra Favoritos SIN Sesión**
- [ ] Cerrar sesión
- [ ] Agregar 2-3 relojes a favoritos (localStorage)
- [ ] Ir a página de favoritos
- [ ] Seleccionar productos
- [ ] Click "Finalizar Compra"
- [ ] Verificar que cargue `informacion-favoritos/informacion-favoritos.html`
- [ ] Completar formulario
- [ ] Subir comprobante
- [ ] Ingresar monto pagado
- [ ] Verificar que orden se cree correctamente
- [ ] Verificar productos en `orden_detalle`
- [ ] Verificar que NO se genere código
- [ ] Verificar email anónimo

#### **8.2 Compra Favoritos CON Sesión**
- [ ] Iniciar sesión
- [ ] Tener favoritos guardados
- [ ] Finalizar compra desde favoritos
- [ ] Verificar que datos de usuario se pre-carguen
- [ ] Completar compra
- [ ] Verificar que se genere código de descuento (si admin aprueba)

---

### **9. PANEL DE ADMINISTRACIÓN** 👨‍💼

#### **9.1 Acceso**
- [ ] Login con cuenta admin
- [ ] Verificar acceso a `/admin/panel.php`
- [ ] Verificar que usuario normal NO pueda acceder
- [ ] Verificar estadísticas en dashboard

#### **9.2 Ver Órdenes**
- [ ] Ver lista completa de órdenes
- [ ] Ver órdenes pendientes de verificación
- [ ] Ver órdenes en verificación
- [ ] Ver órdenes pagadas
- [ ] Ver órdenes rechazadas
- [ ] Ver órdenes enviadas
- [ ] Ver órdenes entregadas
- [ ] Verificar que órdenes con múltiples productos NO aparezcan duplicadas
- [ ] Verificar que se muestre "Producto(s): Reloj 1, Reloj 2" para multi-producto

#### **9.3 Verificar Comprobante**
- [ ] Hacer clic en "Ver Comprobante"
- [ ] Verificar que se abra imagen/PDF
- [ ] Hacer clic en "✓ Verificar Comprobante"
- [ ] Verificar que estado cambie a "verificacion" o similar
- [ ] Verificar que botón cambie a "✅ Aprobar Orden"

#### **9.4 Aprobar Orden (Usuario CON sesión)**
- [ ] Aprobar orden de usuario registrado
- [ ] Verificar que estado cambie a "pagado"
- [ ] Verificar que relojes se marquen como vendidos
- [ ] Verificar que se genere código de descuento
- [ ] Verificar que código se asigne en `usuario_codigo_descuento`
- [ ] Verificar que llegue email con código
- [ ] Verificar que llegue WhatsApp (si Twilio activo)
- [ ] Verificar que admin reciba notificación

#### **9.5 Aprobar Orden (Usuario SIN sesión - Anónimo)**
- [ ] Aprobar orden anónima
- [ ] Verificar que relojes se marquen como vendidos
- [ ] Verificar que NO se genere código
- [ ] Verificar que llegue email con CTA "Regístrate"
- [ ] Verificar que llegue WhatsApp (si aplica)

#### **9.6 Rechazar Orden**
- [ ] Hacer clic en "❌ Rechazar"
- [ ] Seleccionar motivo de rechazo
- [ ] **Si es "Monto incorrecto":** Ingresar monto real pagado
- [ ] Confirmar rechazo
- [ ] Verificar que estado cambie a "rechazado"
- [ ] Verificar que relojes NO se marquen como vendidos
- [ ] Verificar que llegue email con motivo de rechazo
- [ ] Verificar que llegue WhatsApp de recordatorio (si configurado)

#### **9.7 Marcar como Enviado**
- [ ] Seleccionar orden pagada
- [ ] Click en "🚚 Marcar Enviado"
- [ ] Seleccionar transportadora (SERVIENTREGA, etc.)
- [ ] Ingresar guía de seguimiento
- [ ] Confirmar
- [ ] Verificar que estado cambie a "enviado"
- [ ] Verificar que se guarde `guia_envio` y `transportadora`
- [ ] Verificar que llegue email con guía

#### **9.8 Marcar como Entregado**
- [ ] Seleccionar orden enviada
- [ ] Click en "✅ Marcar Entregado"
- [ ] Confirmar
- [ ] Verificar que estado cambie a "entregado"
- [ ] Verificar que se guarde `fecha_entrega`

#### **9.9 Revertir Aprobación**
- [ ] Aprobar una orden
- [ ] Click en "↩️ Revertir Aprobación"
- [ ] Verificar que vuelva a "pendiente_verificacion"
- [ ] Verificar que relojes vuelvan a disponibles

#### **9.10 Gestión de Productos**
- [ ] Agregar nuevo reloj
- [ ] Editar reloj existente
- [ ] Cambiar precio
- [ ] Cambiar descuento
- [ ] Marcar como no disponible
- [ ] Subir imágenes nuevas

#### **9.11 Gestión de Marcas**
- [ ] Ver lista de marcas
- [ ] Agregar nueva marca
- [ ] Editar marca existente
- [ ] Desactivar marca

#### **9.12 Gestión de Envíos**
- [ ] Ver lista de ciudades/precios
- [ ] Agregar nueva ciudad
- [ ] Editar precio de envío
- [ ] Editar días estimados
- [ ] Desactivar ciudad

---

### **10. PERFIL DE USUARIO** 👤

#### **10.1 Ver Perfil**
- [ ] Ir a `/perfil/perfil.php`
- [ ] Ver datos personales
- [ ] Ver estadísticas:
  - [ ] Total invertido (solo órdenes entregadas)
  - [ ] Relojes comprados (solo entregados)
  - [ ] Órdenes totales

#### **10.2 Historial de Compras**
- [ ] Ver lista de órdenes del usuario
- [ ] Ver detalles de cada orden
- [ ] Ver estado actual
- [ ] Ver productos comprados
- [ ] Ver total pagado

#### **10.3 Mis Códigos de Descuento**
- [ ] Ver códigos disponibles
- [ ] Ver códigos usados
- [ ] Ver códigos expirados
- [ ] Copiar código disponible
- [ ] Verificar que código usado NO tenga botón copiar
- [ ] Ver fecha de expiración

#### **10.4 Editar Perfil**
- [ ] Cambiar nombre
- [ ] Cambiar correo (si aplica)
- [ ] Cambiar contraseña
- [ ] Guardar cambios
- [ ] Verificar actualización en BD

---

### **11. EMAILS** 📧

#### **11.1 Email de Registro**
- [ ] Registrar nuevo usuario
- [ ] Verificar que llegue email
- [ ] Verificar diseño del email
- [ ] Click en enlace de verificación
- [ ] Verificar redirección

#### **11.2 Email de Orden Creada**
- [ ] Crear orden (individual, carrito o favoritos)
- [ ] Verificar que llegue email
- [ ] Verificar que contenga:
  - [ ] Nombre del cliente
  - [ ] Productos comprados
  - [ ] Total pagado
  - [ ] Token de verificación
  - [ ] Mensaje "Comprobante Recibido"
  - [ ] Próximos pasos

#### **11.3 Email de Orden Aprobada (CON sesión)**
- [ ] Admin aprueba orden
- [ ] Verificar que llegue email
- [ ] Verificar que contenga:
  - [ ] Código de descuento generado
  - [ ] Porcentaje de descuento
  - [ ] Fecha de expiración
  - [ ] Instrucciones de uso
  - [ ] Guía de envío (si aplica)

#### **11.4 Email de Orden Aprobada (SIN sesión)**
- [ ] Admin aprueba orden anónima
- [ ] Verificar que llegue email
- [ ] Verificar que contenga:
  - [ ] Agradecimiento
  - [ ] CTA para registrarse
  - [ ] Explicación de beneficios de cuenta

#### **11.5 Email de Orden Rechazada**
- [ ] Admin rechaza orden
- [ ] Verificar que llegue email
- [ ] Verificar que contenga:
  - [ ] Motivo de rechazo
  - [ ] Monto incorrecto (si aplica)
  - [ ] Instrucciones para resubir
  - [ ] Contacto de soporte

#### **11.6 Email de Orden Enviada**
- [ ] Admin marca como enviada
- [ ] Verificar que llegue email
- [ ] Verificar que contenga:
  - [ ] Transportadora
  - [ ] Guía de seguimiento
  - [ ] Enlace de tracking (si aplica)

#### **11.7 Email de Recuperación de Contraseña**
- [ ] Solicitar reset
- [ ] Verificar que llegue email
- [ ] Verificar enlace con token
- [ ] Cambiar contraseña
- [ ] Verificar que token expire después de usado

---

### **12. WHATSAPP (si Twilio activo)** 📱

#### **12.1 Notificación de Orden Creada (Cliente)**
- [ ] Crear orden con celular válido
- [ ] Verificar que llegue WhatsApp al cliente
- [ ] Verificar mensaje con:
  - [ ] Nombre del producto
  - [ ] Total a pagar
  - [ ] Token de verificación

#### **12.2 Notificación de Orden Aprobada (Cliente)**
- [ ] Admin aprueba orden
- [ ] Verificar WhatsApp al cliente
- [ ] Verificar mensaje con código de descuento (si aplica)

#### **12.3 Notificación al Admin**
- [ ] Cliente crea orden
- [ ] Verificar que admin reciba WhatsApp
- [ ] Verificar que contenga:
  - [ ] ID de orden
  - [ ] Cliente
  - [ ] Total
  - [ ] Productos

#### **12.4 Recordatorio de Orden Rechazada**
- [ ] Admin rechaza orden
- [ ] Esperar X minutos (según configuración)
- [ ] Verificar que llegue recordatorio WhatsApp
- [ ] Verificar que `recordatorio_enviado = 1` en BD

---

### **13. VALIDACIONES Y SEGURIDAD** 🔒

#### **13.1 Inyección SQL**
- [ ] Intentar inyección en campo correo
- [ ] Intentar inyección en campo nombre
- [ ] Intentar inyección en búsqueda
- [ ] Verificar que prepared statements protejan

#### **13.2 XSS (Cross-Site Scripting)**
- [ ] Intentar insertar `<script>alert('XSS')</script>` en comentario
- [ ] Intentar en campo nombre
- [ ] Verificar que se escape HTML

#### **13.3 CSRF (Cross-Site Request Forgery)**
- [ ] Verificar tokens CSRF en formularios críticos
- [ ] Intentar enviar formulario sin token
- [ ] Verificar que se rechace

#### **13.4 Validación de Archivos**
- [ ] Intentar subir archivo .exe como comprobante (debe fallar)
- [ ] Intentar subir archivo .php (debe fallar)
- [ ] Subir imagen válida (.jpg, .png)
- [ ] Subir PDF válido
- [ ] Intentar subir archivo > 5MB (debe fallar si hay límite)

#### **13.5 Validación de Sesiones**
- [ ] Intentar acceder a perfil sin sesión (debe redirigir a login)
- [ ] Intentar acceder a admin sin sesión (debe redirigir)
- [ ] Intentar acceder a admin con usuario normal (debe denegar)

#### **13.6 Validación de Tokens**
- [ ] Usar token de verificación caducado (debe fallar)
- [ ] Usar token ya usado (debe fallar)
- [ ] Modificar token manualmente (debe fallar)

---

### **14. RESPONSIVE Y MÓVIL** 📱

#### **14.1 Catálogo en Móvil**
- [ ] Abrir catálogo en móvil
- [ ] Verificar diseño responsive
- [ ] Verificar que imágenes se adapten
- [ ] Verificar que filtros funcionen
- [ ] Verificar menú hamburguesa

#### **14.2 Proceso de Compra en Móvil**
- [ ] Comprar desde móvil
- [ ] Completar formulario
- [ ] Subir comprobante desde cámara
- [ ] Verificar que todo funcione

#### **14.3 Carrito en Móvil**
- [ ] Abrir carrito modal en móvil
- [ ] Agregar productos
- [ ] Eliminar productos
- [ ] Verificar diseño

#### **14.4 Perfil en Móvil**
- [ ] Ver perfil en móvil
- [ ] Ver historial
- [ ] Ver códigos
- [ ] Verificar que todo sea legible

---

### **15. RENDIMIENTO** ⚡

#### **15.1 Tiempos de Carga**
- [ ] Medir tiempo de carga de index.html (< 3 segundos ideal)
- [ ] Medir tiempo de carga de catálogo (< 3 segundos)
- [ ] Medir tiempo de carga de panel admin (< 5 segundos)

#### **15.2 Imágenes**
- [ ] Verificar que imágenes estén optimizadas
- [ ] Verificar lazy loading (si aplica)
- [ ] Verificar tamaños de archivo razonables

#### **15.3 Base de Datos**
- [ ] Verificar índices en tablas críticas
- [ ] Hacer query de 100+ órdenes (debe ser rápido)
- [ ] Verificar que no haya N+1 queries

---

### **16. NAVEGADORES** 🌐

#### **16.1 Chrome**
- [ ] Probar todas las funcionalidades en Chrome

#### **16.2 Firefox**
- [ ] Probar todas las funcionalidades en Firefox

#### **16.3 Safari (macOS/iOS)**
- [ ] Probar en Safari (si tienes acceso)

#### **16.4 Edge**
- [ ] Probar en Edge

---

### **17. CASOS LÍMITE** ⚠️

#### **17.1 Carrito Vacío**
- [ ] Intentar finalizar compra con carrito vacío
- [ ] Verificar mensaje de error

#### **17.2 Stock Agotado**
- [ ] Intentar comprar reloj ya vendido
- [ ] Verificar que se detecte y rechace

#### **17.3 Códigos Expirados**
- [ ] Intentar usar código expirado
- [ ] Verificar mensaje de error

#### **17.4 Múltiples Sesiones**
- [ ] Abrir 2 pestañas con misma cuenta
- [ ] Agregar productos en ambas
- [ ] Verificar sincronización

#### **17.5 Compra Simultánea**
- [ ] 2 usuarios intentan comprar mismo reloj
- [ ] El primero en aprobar debería ganarlo
- [ ] El segundo debería ver "vendido"

---

### **18. LOGS Y DEBUG** 🐛

#### **18.1 Logs de WhatsApp**
- [ ] Verificar archivo `logs/whatsapp_notifications.log`
- [ ] Verificar que se registren envíos exitosos
- [ ] Verificar que se registren errores

#### **18.2 Logs de Email**
- [ ] Verificar que emails enviados se registren
- [ ] Verificar errores de PHPMailer

#### **18.3 Errores PHP**
- [ ] Revisar `logs/php_errors.log` (si existe)
- [ ] Verificar que no haya errores críticos

---

### **19. BASE DE DATOS** 💾

#### **19.1 Integridad Referencial**
- [ ] Intentar eliminar usuario con órdenes (debe proteger o hacer ON DELETE)
- [ ] Intentar eliminar reloj con órdenes (debe proteger)
- [ ] Verificar foreign keys funcionando

#### **19.2 Datos Consistentes**
- [ ] Verificar que precios en `orden_detalle` coincidan con `reloj`
- [ ] Verificar que totales en `orden` sean correctos
- [ ] Verificar que `monto_pagado` tenga sentido vs `total`

#### **19.3 Campos Críticos**
- [ ] Verificar que `orden.nombre_archivo_comprobante` NO sea NULL
- [ ] Verificar que `usuario_codigo_descuento.id_reloj` se guarde cuando se aplica
- [ ] Verificar que `orden.token_verificacion` sea único

---

### **20. DOCUMENTACIÓN** 📚

#### **20.1 Revisar Archivos MD**
- [ ] Leer `EMPIEZA_AQUI.md`
- [ ] Leer `SISTEMA_FAVORITOS_FINAL.md`
- [ ] Leer `RESUMEN_COMPLETO_SISTEMA_CODIGOS.md`
- [ ] Verificar que documentación esté actualizada

#### **20.2 Comentarios en Código**
- [ ] Verificar que funciones críticas tengan comentarios
- [ ] Verificar que SQL complejo tenga explicaciones

---

## 📊 **Resumen de Categorías**

| Categoría | Cantidad de Pruebas |
|-----------|---------------------|
| 👤 Autenticación | ~15 |
| 🛍️ Catálogo | ~12 |
| 🛒 Carrito | ~20 |
| ⭐ Favoritos | ~15 |
| 🎟️ Códigos | ~12 |
| 🛍️ Compra Individual | ~15 |
| 🛒 Compra Carrito | ~12 |
| ⭐ Compra Favoritos | ~8 |
| 👨‍💼 Admin Panel | ~35 |
| 👤 Perfil | ~12 |
| 📧 Emails | ~25 |
| 📱 WhatsApp | ~8 |
| 🔒 Seguridad | ~15 |
| 📱 Móvil | ~12 |
| ⚡ Rendimiento | ~8 |
| 🌐 Navegadores | ~4 |
| ⚠️ Casos Límite | ~8 |
| 🐛 Logs | ~6 |
| 💾 Base de Datos | ~8 |
| 📚 Documentación | ~5 |

**TOTAL: ~255 pruebas**

---

## 🎯 **Prioridad de Pruebas**

### **🔴 CRÍTICAS (hacer primero):**
1. Proceso de compra completo (individual, carrito, favoritos)
2. Admin: aprobar/rechazar órdenes
3. Códigos de descuento (aplicar, usar, generar)
4. Autenticación y roles
5. Validación de relojes vendidos

### **🟡 IMPORTANTES (hacer segundo):**
6. Carrito y favoritos
7. Emails de notificación
8. Perfil de usuario
9. Gestión de productos/marcas/envíos
10. Validaciones de seguridad

### **🟢 OPCIONALES (hacer después):**
11. WhatsApp (si Twilio activo)
12. Responsive en móvil
13. Rendimiento
14. Navegadores alternativos
15. Casos límite

---

## ✅ **Cómo Usar Esta Lista**

1. **Imprime** o ten esta lista a mano
2. **Ve marcando** cada checkbox mientras pruebas
3. **Anota errores** encontrados en cada sección
4. **Prioriza** las críticas primero
5. **Documenta** bugs para arreglar después

---

**¡Suerte con las pruebas!** 🚀

**Fecha:** 27 de octubre de 2025  
**Versión del Sistema:** Pre-producción  
**Estado:** ✅ Lista completa de pruebas

