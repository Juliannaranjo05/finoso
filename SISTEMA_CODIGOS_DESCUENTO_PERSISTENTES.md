# Sistema de Códigos de Descuento Persistentes

## 📅 Fecha de Implementación
27 de octubre de 2025

## 🎯 Problema Resuelto
Cuando un usuario aplicaba un código de descuento a un reloj individual:
- El descuento se aplicaba temporalmente en la pantalla
- Pero al recargar la página o cerrar y volver a entrar, el reloj volvía a su precio original
- El descuento no se mantenía guardado para ese usuario en ese reloj

## ✅ Solución Implementada

### 1. **Guardar el descuento en base de datos**
Se utiliza la tabla `descuento_aplicado_reloj` que guarda:
- `id_usuario`: Usuario que aplicó el código
- `id_reloj`: Reloj al que se aplicó
- `id_codigo`: Código de descuento usado
- `precio_original`: Precio antes del descuento
- `porcentaje_descuento`: Porcentaje aplicado
- `precio_con_descuento`: Precio final
- `expira_en`: Fecha de expiración (24 horas)
- `usado_en_orden`: ID de orden si ya se usó en una compra

### 2. **Flujo de aplicación del código**
Cuando el usuario presiona "Aplicar" (`informacion/php/aplicar_codigo_descuento.php`):
1. ✅ Valida que el código exista
2. ✅ Verifica que no haya expirado
3. ✅ **Requiere sesión activa** (`$id_usuario` no null)
4. ✅ Verifica que el código esté asignado a ese usuario
5. ✅ Verifica que no haya sido usado antes
6. ✅ Calcula el precio con descuento
7. ✅ **Guarda en BD** (`descuento_aplicado_reloj`)
8. ✅ Guarda en sesión para la orden

### 3. **Flujo de recuperación del descuento**
Cuando el usuario recarga la página (`informacion/php/obtener_descuento_aplicado.php`):
1. ✅ **Requiere sesión activa** (no funciona sin sesión)
2. ✅ Busca en `descuento_aplicado_reloj` por usuario + reloj
3. ✅ Verifica que no haya expirado (24 horas)
4. ✅ Verifica que no se haya usado en una orden
5. ✅ Retorna el descuento guardado

### 4. **Sincronización de carga**
**Problema anterior**: Dos scripts cargaban información en paralelo sin orden garantizado:
- `obtener-reloj.js` cargaba el reloj con su precio original
- Script en `informacion.html` cargaba el descuento aplicado
- Si `obtener-reloj.js` terminaba después, sobrescribía el precio con descuento

**Solución**:
```javascript
// En obtener-reloj.js (línea 289-296)
// ✅ CARGAR DESCUENTO APLICADO DESPUÉS DE RENDERIZAR EL RELOJ
setTimeout(() => {
    if (typeof cargarCodigoAplicado === 'function') {
        console.log('🔄 Verificando descuento aplicado...');
        cargarCodigoAplicado();
    }
}, 100);
```

Ahora el orden es:
1. Se carga el reloj con su precio original
2. Se renderiza el HTML
3. **Después de 100ms**, se verifica si hay descuento aplicado
4. Si lo hay, se actualiza el precio visualmente

## 🔒 Restricciones de Seguridad

### ✅ Funciona en:
- **Compras individuales CON sesión activa**

### ❌ NO funciona en:
- **Compras sin sesión** (usuarios anónimos)
  - El código requiere `$id_usuario` en BD
  - Retorna vacío si no hay sesión
- **Favoritos** (sin sesión)
  - Los favoritos están diseñados para usuarios sin sesión
  - No pueden aplicar códigos
- **Carrito**
  - En carrito no hay opción de aplicar código antes
  - El código se aplica al momento del checkout (si es necesario)

## 📋 Archivos Modificados

### 1. `informacion/js/obtener-reloj.js`
**Cambio**: Agregada llamada a `cargarCodigoAplicado()` después de renderizar el reloj (líneas 289-296)

**Antes**:
```javascript
            }
        })
        .catch(error => {
```

**Después**:
```javascript
            }
            
            // ✅ CARGAR DESCUENTO APLICADO DESPUÉS DE RENDERIZAR EL RELOJ
            setTimeout(() => {
                if (typeof cargarCodigoAplicado === 'function') {
                    console.log('🔄 Verificando descuento aplicado...');
                    cargarCodigoAplicado();
                }
            }, 100);
        })
        .catch(error => {
```

### 2. `informacion/informacion.html`
**Cambio**: Eliminada llamada duplicada en DOMContentLoaded (línea 553-554)

**Antes**:
```javascript
        // Cargar código al iniciar la página
        document.addEventListener('DOMContentLoaded', function() {
            cargarCodigoAplicado();
        });
```

**Después**:
```javascript
        // NOTA: cargarCodigoAplicado() ahora se llama desde obtener-reloj.js
        // después de que se cargue el reloj, para evitar problemas de sincronización
```

## 🎯 Resultado Final

### Antes ❌
1. Usuario aplica código → Precio cambia a $112.500
2. Usuario recarga página → Precio vuelve a $125.000
3. Usuario cierra y vuelve a entrar → Precio en $125.000

### Ahora ✅
1. Usuario aplica código → Precio cambia a $112.500
2. Usuario recarga página → **Precio se mantiene en $112.500**
3. Usuario cierra y vuelve a entrar → **Precio se mantiene en $112.500**
4. El descuento se mantiene por **24 horas** o hasta que se use en una compra

## 🔄 Expiración del Descuento

El descuento aplicado expira en estos casos:
1. **24 horas después** de aplicarlo (`expira_en`)
2. **Cuando se usa en una orden** (`usado_en_orden IS NOT NULL`)
3. Si el código mismo expira (`codigo_descuento.fecha_expiracion`)

## 📝 Tabla de Base de Datos

```sql
CREATE TABLE IF NOT EXISTS descuento_aplicado_reloj (
    id_descuento_aplicado INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_reloj INT NOT NULL,
    id_codigo INT NOT NULL,
    precio_original DECIMAL(10,2) NOT NULL,
    porcentaje_descuento INT NOT NULL,
    precio_con_descuento DECIMAL(10,2) NOT NULL,
    fecha_aplicado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expira_en DATETIME NULL,
    usado_en_orden INT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_reloj) REFERENCES reloj(id_reloj),
    FOREIGN KEY (id_codigo) REFERENCES codigo_descuento(id_codigo),
    FOREIGN KEY (usado_en_orden) REFERENCES orden(id_orden)
);
```

## 🧪 Cómo Probar

1. **Con sesión activa**:
   - Inicia sesión
   - Ve a un reloj individual
   - Aplica un código de descuento asignado a tu usuario
   - Precio cambia (ej: $125.000 → $112.500)
   - Recarga la página (F5)
   - ✅ El precio se mantiene en $112.500

2. **Sin sesión**:
   - Cierra sesión o usa ventana incógnita
   - Ve a un reloj individual
   - Intenta aplicar un código
   - ❌ Error: "Este código no está asignado a tu cuenta"

3. **Favoritos**:
   - Usa favoritos (con o sin sesión)
   - ❌ No hay opción de aplicar código

4. **Carrito**:
   - Agrega relojes al carrito
   - ❌ No hay opción de aplicar código antes del checkout

## 🎉 Estado
✅ **IMPLEMENTADO Y FUNCIONANDO**
- Códigos persistentes en compras individuales con sesión
- Protegido contra uso sin sesión
- Sincronización correcta de carga
- Expiración automática en 24 horas


## 📅 Fecha de Implementación
27 de octubre de 2025

## 🎯 Problema Resuelto
Cuando un usuario aplicaba un código de descuento a un reloj individual:
- El descuento se aplicaba temporalmente en la pantalla
- Pero al recargar la página o cerrar y volver a entrar, el reloj volvía a su precio original
- El descuento no se mantenía guardado para ese usuario en ese reloj

## ✅ Solución Implementada

### 1. **Guardar el descuento en base de datos**
Se utiliza la tabla `descuento_aplicado_reloj` que guarda:
- `id_usuario`: Usuario que aplicó el código
- `id_reloj`: Reloj al que se aplicó
- `id_codigo`: Código de descuento usado
- `precio_original`: Precio antes del descuento
- `porcentaje_descuento`: Porcentaje aplicado
- `precio_con_descuento`: Precio final
- `expira_en`: Fecha de expiración (24 horas)
- `usado_en_orden`: ID de orden si ya se usó en una compra

### 2. **Flujo de aplicación del código**
Cuando el usuario presiona "Aplicar" (`informacion/php/aplicar_codigo_descuento.php`):
1. ✅ Valida que el código exista
2. ✅ Verifica que no haya expirado
3. ✅ **Requiere sesión activa** (`$id_usuario` no null)
4. ✅ Verifica que el código esté asignado a ese usuario
5. ✅ Verifica que no haya sido usado antes
6. ✅ Calcula el precio con descuento
7. ✅ **Guarda en BD** (`descuento_aplicado_reloj`)
8. ✅ Guarda en sesión para la orden

### 3. **Flujo de recuperación del descuento**
Cuando el usuario recarga la página (`informacion/php/obtener_descuento_aplicado.php`):
1. ✅ **Requiere sesión activa** (no funciona sin sesión)
2. ✅ Busca en `descuento_aplicado_reloj` por usuario + reloj
3. ✅ Verifica que no haya expirado (24 horas)
4. ✅ Verifica que no se haya usado en una orden
5. ✅ Retorna el descuento guardado

### 4. **Sincronización de carga**
**Problema anterior**: Dos scripts cargaban información en paralelo sin orden garantizado:
- `obtener-reloj.js` cargaba el reloj con su precio original
- Script en `informacion.html` cargaba el descuento aplicado
- Si `obtener-reloj.js` terminaba después, sobrescribía el precio con descuento

**Solución**:
```javascript
// En obtener-reloj.js (línea 289-296)
// ✅ CARGAR DESCUENTO APLICADO DESPUÉS DE RENDERIZAR EL RELOJ
setTimeout(() => {
    if (typeof cargarCodigoAplicado === 'function') {
        console.log('🔄 Verificando descuento aplicado...');
        cargarCodigoAplicado();
    }
}, 100);
```

Ahora el orden es:
1. Se carga el reloj con su precio original
2. Se renderiza el HTML
3. **Después de 100ms**, se verifica si hay descuento aplicado
4. Si lo hay, se actualiza el precio visualmente

## 🔒 Restricciones de Seguridad

### ✅ Funciona en:
- **Compras individuales CON sesión activa**

### ❌ NO funciona en:
- **Compras sin sesión** (usuarios anónimos)
  - El código requiere `$id_usuario` en BD
  - Retorna vacío si no hay sesión
- **Favoritos** (sin sesión)
  - Los favoritos están diseñados para usuarios sin sesión
  - No pueden aplicar códigos
- **Carrito**
  - En carrito no hay opción de aplicar código antes
  - El código se aplica al momento del checkout (si es necesario)

## 📋 Archivos Modificados

### 1. `informacion/js/obtener-reloj.js`
**Cambio**: Agregada llamada a `cargarCodigoAplicado()` después de renderizar el reloj (líneas 289-296)

**Antes**:
```javascript
            }
        })
        .catch(error => {
```

**Después**:
```javascript
            }
            
            // ✅ CARGAR DESCUENTO APLICADO DESPUÉS DE RENDERIZAR EL RELOJ
            setTimeout(() => {
                if (typeof cargarCodigoAplicado === 'function') {
                    console.log('🔄 Verificando descuento aplicado...');
                    cargarCodigoAplicado();
                }
            }, 100);
        })
        .catch(error => {
```

### 2. `informacion/informacion.html`
**Cambio**: Eliminada llamada duplicada en DOMContentLoaded (línea 553-554)

**Antes**:
```javascript
        // Cargar código al iniciar la página
        document.addEventListener('DOMContentLoaded', function() {
            cargarCodigoAplicado();
        });
```

**Después**:
```javascript
        // NOTA: cargarCodigoAplicado() ahora se llama desde obtener-reloj.js
        // después de que se cargue el reloj, para evitar problemas de sincronización
```

## 🎯 Resultado Final

### Antes ❌
1. Usuario aplica código → Precio cambia a $112.500
2. Usuario recarga página → Precio vuelve a $125.000
3. Usuario cierra y vuelve a entrar → Precio en $125.000

### Ahora ✅
1. Usuario aplica código → Precio cambia a $112.500
2. Usuario recarga página → **Precio se mantiene en $112.500**
3. Usuario cierra y vuelve a entrar → **Precio se mantiene en $112.500**
4. El descuento se mantiene por **24 horas** o hasta que se use en una compra

## 🔄 Expiración del Descuento

El descuento aplicado expira en estos casos:
1. **24 horas después** de aplicarlo (`expira_en`)
2. **Cuando se usa en una orden** (`usado_en_orden IS NOT NULL`)
3. Si el código mismo expira (`codigo_descuento.fecha_expiracion`)

## 📝 Tabla de Base de Datos

```sql
CREATE TABLE IF NOT EXISTS descuento_aplicado_reloj (
    id_descuento_aplicado INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_reloj INT NOT NULL,
    id_codigo INT NOT NULL,
    precio_original DECIMAL(10,2) NOT NULL,
    porcentaje_descuento INT NOT NULL,
    precio_con_descuento DECIMAL(10,2) NOT NULL,
    fecha_aplicado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expira_en DATETIME NULL,
    usado_en_orden INT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_reloj) REFERENCES reloj(id_reloj),
    FOREIGN KEY (id_codigo) REFERENCES codigo_descuento(id_codigo),
    FOREIGN KEY (usado_en_orden) REFERENCES orden(id_orden)
);
```

## 🧪 Cómo Probar

1. **Con sesión activa**:
   - Inicia sesión
   - Ve a un reloj individual
   - Aplica un código de descuento asignado a tu usuario
   - Precio cambia (ej: $125.000 → $112.500)
   - Recarga la página (F5)
   - ✅ El precio se mantiene en $112.500

2. **Sin sesión**:
   - Cierra sesión o usa ventana incógnita
   - Ve a un reloj individual
   - Intenta aplicar un código
   - ❌ Error: "Este código no está asignado a tu cuenta"

3. **Favoritos**:
   - Usa favoritos (con o sin sesión)
   - ❌ No hay opción de aplicar código

4. **Carrito**:
   - Agrega relojes al carrito
   - ❌ No hay opción de aplicar código antes del checkout

## 🎉 Estado
✅ **IMPLEMENTADO Y FUNCIONANDO**
- Códigos persistentes en compras individuales con sesión
- Protegido contra uso sin sesión
- Sincronización correcta de carga
- Expiración automática en 24 horas

