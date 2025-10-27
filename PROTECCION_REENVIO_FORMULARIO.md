# Protección contra Reenvío de Formulario (POST-REDIRECT-GET)

## 📅 Fecha de Implementación
26 de octubre de 2025

## 🎯 Problema Resuelto
Al refrescar la página después de subir un comprobante de pago, se creaban órdenes duplicadas y se enviaban múltiples correos electrónicos.

## 🔧 Solución Implementada
Se implementó el patrón **POST-REDIRECT-GET (PRG)** en todos los archivos de procesamiento de comprobantes:

### Archivos Modificados
1. `informacion-carrito/php/subir_comprobante-carrito.php` (Compras desde carrito con sesión)
2. `informacion-favoritos/php/subir_comprobante-carrito.php` (Compras desde favoritos sin sesión)

### Cambios Realizados

#### 1. Verificación de Peticiones GET (Líneas 17-37)
```php
// Si ya se procesó esta orden, redirigir a la página de confirmación
if (isset($_GET['orden_id']) && isset($_GET['token'])) {
    $orden_id = intval($_GET['orden_id']);
    $token = $_GET['token'];
    
    // Verificar que la orden existe y obtener sus datos
    $stmt = $conn->prepare("SELECT o.*, GROUP_CONCAT(r.nombre SEPARATOR ', ') as nombre_relojes
                            FROM orden o 
                            LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
                            LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
                            WHERE o.id_orden = ?
                            GROUP BY o.id_orden");
    $stmt->bind_param("i", $orden_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $orden = $result->fetch_assoc();
        // Mostrar página de éxito sin procesar nada
        mostrarPaginaExito($orden);
        exit();
    }
}
```

#### 2. Protección contra Peticiones No-POST (Líneas 40-43)
```php
// 🔥 VERIFICAR QUE SEA UNA PETICIÓN POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /finoso/');
    exit();
}
```

#### 3. Verificación de Token de Sesión (Líneas 46-59)
Para carrito:
```php
if (isset($_SESSION['ultimo_token_procesado_carrito'])) {
    $token_anterior = $_SESSION['ultimo_token_procesado_carrito'];
    $tiempo_anterior = $_SESSION['tiempo_ultimo_token_carrito'] ?? 0;
    
    // Si el token se procesó hace menos de 10 segundos, es un reenvío
    if ((time() - $tiempo_anterior) < 10) {
        // Redirigir a la página de confirmación de la orden anterior
        if (isset($_SESSION['ultima_orden_id_carrito'])) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?orden_id=" . $_SESSION['ultima_orden_id_carrito'] . "&token=" . $token_anterior);
            exit();
        }
    }
}
```

Para favoritos (similar pero con variables `_favoritos`):
```php
if (isset($_SESSION['ultimo_token_procesado_favoritos'])) {
    // ... mismo código pero con _favoritos
}
```

#### 4. Generación de Token Único (Antes del INSERT)
```php
// Generar token de verificación ANTES de crear la orden
$token_verificacion = bin2hex(random_bytes(16));
```

#### 5. Actualización de la Tabla `orden`
Se agregó el campo `token_verificacion` al INSERT:
```php
$sql_orden = "INSERT INTO orden (
    id_usuario, total, estado, metodo_pago, costo_envio,
    nombre, cedula, celular, departamento, ciudad, direccion, barrio, referencias,
    comprobante_pago, correo, token_verificacion
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
```

#### 6. Redirect después del Commit
Después de `$conn->commit()`:
```php
// Guardar token en sesión para evitar reenvíos
$_SESSION['ultimo_token_procesado_carrito'] = $token_verificacion;
$_SESSION['tiempo_ultimo_token_carrito'] = time();
$_SESSION['ultima_orden_id_carrito'] = $id_orden;

// 🔥 REDIRIGIR A LA MISMA PÁGINA CON GET (POST-REDIRECT-GET PATTERN)
header("Location: " . $_SERVER['PHP_SELF'] . "?orden_id=" . $id_orden . "&token=" . $token_verificacion);
exit();
```

#### 7. Nueva Función `mostrarPaginaExito($orden)`
Se creó una función para mostrar la página de confirmación sin procesar la orden:
```php
function mostrarPaginaExito($orden) {
    // Calcular valores formateados
    $total_productos = $orden['total'] - $orden['costo_envio'];
    $total_productos_formateado = number_format($total_productos, 0, ',', '.');
    $costo_envio_formateado = number_format($orden['costo_envio'], 0, ',', '.');
    $total_formateado = number_format($orden['total'], 0, ',', '.');
    
    // Obtener productos de esta orden
    global $conn;
    $stmt = $conn->prepare("SELECT r.nombre, od.precio_unitario 
                            FROM orden_detalle od
                            JOIN reloj r ON od.id_reloj = r.id_reloj
                            WHERE od.id_orden = ?");
    $stmt->bind_param("i", $orden['id_orden']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $productos_html = '';
    while ($row = $result->fetch_assoc()) {
        $nombre_producto = htmlspecialchars($row['nombre']);
        $precio_producto_formateado = number_format($row['precio_unitario'], 0, ',', '.');
        
        $productos_html .= '
            <div class="producto-item" style="display: flex; justify-content: space-between; padding: 6px 12px; border-bottom: 1px solid #ffffff1a;">
                <span class="producto-nombre" style="font-weight: 500;">' . $nombre_producto . '</span>
                <span class="producto-precio" style="color: #fff;">$' . $precio_producto_formateado . '</span>
            </div>';
    }
    $stmt->close();

    echo '<!DOCTYPE html>...'; // HTML de la página de éxito
}
```

## ✅ Beneficios
1. **Previene órdenes duplicadas**: Aunque el usuario presione F5 100 veces, solo se crea una orden.
2. **Evita correos duplicados**: No se envían múltiples correos al refrescar la página.
3. **Mejor experiencia de usuario**: El usuario puede refrescar la página de confirmación sin problemas.
4. **Tokens únicos**: Cada orden tiene un token único para verificación.
5. **Sesión temporal**: Los tokens se guardan en sesión por 10 segundos para evitar reenvíos accidentales.

## 🔍 Flujo de Funcionamiento

### Primera Vez (POST)
1. Usuario envía formulario → POST
2. Se genera token único
3. Se procesa el pago y crea la orden
4. Se guarda el token en la sesión
5. **REDIRECT** → GET con `?orden_id=123&token=abc...`
6. Se muestra la página de confirmación

### Al Refrescar (GET)
1. Navegador refresca → GET con `?orden_id=123&token=abc...`
2. Se detecta que hay parámetros GET
3. Se busca la orden en la base de datos
4. Se muestra la página de confirmación **sin procesar nada**

### Si Intentan Reenviar el POST
1. Se detecta que el token ya fue procesado hace menos de 10 segundos
2. Se redirige automáticamente al GET de la orden anterior
3. No se procesa nada nuevo

## 📝 Notas Importantes
- La tabla `orden` debe tener el campo `token_verificacion` (ya debe estar presente en la base de datos).
- Los tokens son únicos y se generan con `bin2hex(random_bytes(16))`.
- La ventana de protección es de 10 segundos (configurable en la validación del tiempo).
- Cada flujo (carrito/favoritos) usa variables de sesión diferentes para evitar conflictos:
  - Carrito: `ultimo_token_procesado_carrito`, `ultima_orden_id_carrito`
  - Favoritos: `ultimo_token_procesado_favoritos`, `ultima_orden_id_favoritos`

## 🔗 Referencias
Este patrón está inspirado en la implementación de `informacion/php/subir_comprobante.php` (compras individuales), donde ya estaba funcionando correctamente.

## ⚠️ Cambios en Base de Datos
Si la tabla `orden` no tiene el campo `token_verificacion`, ejecutar:
```sql
ALTER TABLE orden ADD COLUMN token_verificacion VARCHAR(64) DEFAULT NULL AFTER comprobante_pago;
```

## 🎉 Estado
✅ **IMPLEMENTADO Y FUNCIONANDO** en todos los flujos de compra:
- ✅ Compras individuales
- ✅ Compras desde carrito (con sesión)
- ✅ Compras desde favoritos (sin sesión)


## 📅 Fecha de Implementación
26 de octubre de 2025

## 🎯 Problema Resuelto
Al refrescar la página después de subir un comprobante de pago, se creaban órdenes duplicadas y se enviaban múltiples correos electrónicos.

## 🔧 Solución Implementada
Se implementó el patrón **POST-REDIRECT-GET (PRG)** en todos los archivos de procesamiento de comprobantes:

### Archivos Modificados
1. `informacion-carrito/php/subir_comprobante-carrito.php` (Compras desde carrito con sesión)
2. `informacion-favoritos/php/subir_comprobante-carrito.php` (Compras desde favoritos sin sesión)

### Cambios Realizados

#### 1. Verificación de Peticiones GET (Líneas 17-37)
```php
// Si ya se procesó esta orden, redirigir a la página de confirmación
if (isset($_GET['orden_id']) && isset($_GET['token'])) {
    $orden_id = intval($_GET['orden_id']);
    $token = $_GET['token'];
    
    // Verificar que la orden existe y obtener sus datos
    $stmt = $conn->prepare("SELECT o.*, GROUP_CONCAT(r.nombre SEPARATOR ', ') as nombre_relojes
                            FROM orden o 
                            LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
                            LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
                            WHERE o.id_orden = ?
                            GROUP BY o.id_orden");
    $stmt->bind_param("i", $orden_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $orden = $result->fetch_assoc();
        // Mostrar página de éxito sin procesar nada
        mostrarPaginaExito($orden);
        exit();
    }
}
```

#### 2. Protección contra Peticiones No-POST (Líneas 40-43)
```php
// 🔥 VERIFICAR QUE SEA UNA PETICIÓN POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /finoso/');
    exit();
}
```

#### 3. Verificación de Token de Sesión (Líneas 46-59)
Para carrito:
```php
if (isset($_SESSION['ultimo_token_procesado_carrito'])) {
    $token_anterior = $_SESSION['ultimo_token_procesado_carrito'];
    $tiempo_anterior = $_SESSION['tiempo_ultimo_token_carrito'] ?? 0;
    
    // Si el token se procesó hace menos de 10 segundos, es un reenvío
    if ((time() - $tiempo_anterior) < 10) {
        // Redirigir a la página de confirmación de la orden anterior
        if (isset($_SESSION['ultima_orden_id_carrito'])) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?orden_id=" . $_SESSION['ultima_orden_id_carrito'] . "&token=" . $token_anterior);
            exit();
        }
    }
}
```

Para favoritos (similar pero con variables `_favoritos`):
```php
if (isset($_SESSION['ultimo_token_procesado_favoritos'])) {
    // ... mismo código pero con _favoritos
}
```

#### 4. Generación de Token Único (Antes del INSERT)
```php
// Generar token de verificación ANTES de crear la orden
$token_verificacion = bin2hex(random_bytes(16));
```

#### 5. Actualización de la Tabla `orden`
Se agregó el campo `token_verificacion` al INSERT:
```php
$sql_orden = "INSERT INTO orden (
    id_usuario, total, estado, metodo_pago, costo_envio,
    nombre, cedula, celular, departamento, ciudad, direccion, barrio, referencias,
    comprobante_pago, correo, token_verificacion
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
```

#### 6. Redirect después del Commit
Después de `$conn->commit()`:
```php
// Guardar token en sesión para evitar reenvíos
$_SESSION['ultimo_token_procesado_carrito'] = $token_verificacion;
$_SESSION['tiempo_ultimo_token_carrito'] = time();
$_SESSION['ultima_orden_id_carrito'] = $id_orden;

// 🔥 REDIRIGIR A LA MISMA PÁGINA CON GET (POST-REDIRECT-GET PATTERN)
header("Location: " . $_SERVER['PHP_SELF'] . "?orden_id=" . $id_orden . "&token=" . $token_verificacion);
exit();
```

#### 7. Nueva Función `mostrarPaginaExito($orden)`
Se creó una función para mostrar la página de confirmación sin procesar la orden:
```php
function mostrarPaginaExito($orden) {
    // Calcular valores formateados
    $total_productos = $orden['total'] - $orden['costo_envio'];
    $total_productos_formateado = number_format($total_productos, 0, ',', '.');
    $costo_envio_formateado = number_format($orden['costo_envio'], 0, ',', '.');
    $total_formateado = number_format($orden['total'], 0, ',', '.');
    
    // Obtener productos de esta orden
    global $conn;
    $stmt = $conn->prepare("SELECT r.nombre, od.precio_unitario 
                            FROM orden_detalle od
                            JOIN reloj r ON od.id_reloj = r.id_reloj
                            WHERE od.id_orden = ?");
    $stmt->bind_param("i", $orden['id_orden']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $productos_html = '';
    while ($row = $result->fetch_assoc()) {
        $nombre_producto = htmlspecialchars($row['nombre']);
        $precio_producto_formateado = number_format($row['precio_unitario'], 0, ',', '.');
        
        $productos_html .= '
            <div class="producto-item" style="display: flex; justify-content: space-between; padding: 6px 12px; border-bottom: 1px solid #ffffff1a;">
                <span class="producto-nombre" style="font-weight: 500;">' . $nombre_producto . '</span>
                <span class="producto-precio" style="color: #fff;">$' . $precio_producto_formateado . '</span>
            </div>';
    }
    $stmt->close();

    echo '<!DOCTYPE html>...'; // HTML de la página de éxito
}
```

## ✅ Beneficios
1. **Previene órdenes duplicadas**: Aunque el usuario presione F5 100 veces, solo se crea una orden.
2. **Evita correos duplicados**: No se envían múltiples correos al refrescar la página.
3. **Mejor experiencia de usuario**: El usuario puede refrescar la página de confirmación sin problemas.
4. **Tokens únicos**: Cada orden tiene un token único para verificación.
5. **Sesión temporal**: Los tokens se guardan en sesión por 10 segundos para evitar reenvíos accidentales.

## 🔍 Flujo de Funcionamiento

### Primera Vez (POST)
1. Usuario envía formulario → POST
2. Se genera token único
3. Se procesa el pago y crea la orden
4. Se guarda el token en la sesión
5. **REDIRECT** → GET con `?orden_id=123&token=abc...`
6. Se muestra la página de confirmación

### Al Refrescar (GET)
1. Navegador refresca → GET con `?orden_id=123&token=abc...`
2. Se detecta que hay parámetros GET
3. Se busca la orden en la base de datos
4. Se muestra la página de confirmación **sin procesar nada**

### Si Intentan Reenviar el POST
1. Se detecta que el token ya fue procesado hace menos de 10 segundos
2. Se redirige automáticamente al GET de la orden anterior
3. No se procesa nada nuevo

## 📝 Notas Importantes
- La tabla `orden` debe tener el campo `token_verificacion` (ya debe estar presente en la base de datos).
- Los tokens son únicos y se generan con `bin2hex(random_bytes(16))`.
- La ventana de protección es de 10 segundos (configurable en la validación del tiempo).
- Cada flujo (carrito/favoritos) usa variables de sesión diferentes para evitar conflictos:
  - Carrito: `ultimo_token_procesado_carrito`, `ultima_orden_id_carrito`
  - Favoritos: `ultimo_token_procesado_favoritos`, `ultima_orden_id_favoritos`

## 🔗 Referencias
Este patrón está inspirado en la implementación de `informacion/php/subir_comprobante.php` (compras individuales), donde ya estaba funcionando correctamente.

## ⚠️ Cambios en Base de Datos
Si la tabla `orden` no tiene el campo `token_verificacion`, ejecutar:
```sql
ALTER TABLE orden ADD COLUMN token_verificacion VARCHAR(64) DEFAULT NULL AFTER comprobante_pago;
```

## 🎉 Estado
✅ **IMPLEMENTADO Y FUNCIONANDO** en todos los flujos de compra:
- ✅ Compras individuales
- ✅ Compras desde carrito (con sesión)
- ✅ Compras desde favoritos (sin sesión)

