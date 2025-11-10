# 🚀 GUÍA COMPLETA: IMPLEMENTACIÓN DE FILTROS AVANZADOS

## 📋 ÍNDICE
1. [Preparar Datos](#paso-1-preparar-datos)
2. [Verificar Datos](#paso-2-verificar-datos)
3. [Implementar Backend](#paso-3-implementar-backend)
4. [Implementar Frontend](#paso-4-implementar-frontend)
5. [Probar Filtros](#paso-5-probar-filtros)

---

## 🎯 PASO 1: PREPARAR DATOS

### **Opción A: Datos Manuales (Recomendado para empezar)**

Para agregar datos específicos a los **primeros 4 relojes**:

```
📍 URL: https://finoso.store/admin/agregar_datos_filtros_relojes.php
```

**Datos que agregará:**
- ⚙️ **Reloj #1:** Cuarzo | Silicona | 45g | 10ATM | Deportivo
- 🎩 **Reloj #2:** Automático | Cuero | 85g | 5ATM | Elegante
- 💪 **Reloj #3:** Cuarzo cronógrafo | Acero | 165g | 10ATM | Deportivo
- ✨ **Reloj #4:** Cuarzo | Acero dorado | 120g | 3ATM | Elegante

**Pasos:**
1. Abre el link
2. Revisa los datos actuales
3. Clic en "✅ EJECUTAR ACTUALIZACIÓN"
4. ¡Listo! 4 relojes con datos completos

---

### **Opción B: Generador Automático (Para muchos relojes)**

Para agregar datos aleatorios a **TODOS los relojes**:

```
📍 URL: https://finoso.store/admin/generar_datos_relojes_automatico.php
```

**Configuración:**
- **Porcentaje:** 70% (algunos sin datos = más realista) o 100% (todos con datos)
- **Sobrescribir:** ☑️ Marcado si quieres reemplazar datos existentes

**Características:**
- 🎲 Genera combinaciones realistas automáticamente
- 📊 8 tipos de movimiento diferentes
- 🎨 12 variaciones de pulsera
- ⚖️ Pesos entre 35g y 210g
- 💧 Resistencias de 3ATM hasta 30ATM
- 🔗 Eslabones según tipo de pulsera (inteligente)

**Pasos:**
1. Abre el link
2. Selecciona porcentaje (70% o 100%)
3. Marca "Sobrescribir" si ya tienes datos
4. Clic en "🤖 GENERAR DATOS AUTOMÁTICAMENTE"
5. ¡Listo! Todos los relojes con datos variados

---

## ✅ PASO 2: VERIFICAR DATOS

Después de agregar datos, **verifica que se guardaron correctamente**:

```
📍 URL: https://finoso.store/admin/verificar_campos_filtros.php
```

**Deberías ver algo como:**
```
✅ Con Movimiento: 4/4 (100%) - LISTO
✅ Con Pulsera: 4/4 (100%) - LISTO
✅ Con Peso: 4/4 (100%) - LISTO
✅ Con Resistencia H₂O: 3/4 (75%) - LISTO
```

**Si ves 70% o más en al menos 3 campos:** ✅ **LISTO PARA IMPLEMENTAR FILTROS**

---

## 🔧 PASO 3: IMPLEMENTAR BACKEND

### **Archivo: `catalogo/php/mostrar_relojes.php`**

**Línea 57** - Agregar data-attributes:

**BUSCAR:**
```php
$cardsHTML .= '<div class="contenedor-card" data-marca="' . htmlspecialchars($row['marca']) . '" data-precio="' . $row['precio'] . '">';
```

**REEMPLAZAR POR:**
```php
$cardsHTML .= '<div class="contenedor-card" 
    data-marca="' . htmlspecialchars($row['marca']) . '" 
    data-precio="' . $row['precio'] . '"
    data-movimiento="' . htmlspecialchars($row['movimiento']) . '"
    data-pulsera="' . htmlspecialchars($row['pulsera']) . '"
    data-resistencia="' . htmlspecialchars($row['resistencia_agua']) . '"
    data-peso="' . htmlspecialchars($row['peso']) . '"
    data-bisel="' . htmlspecialchars($row['tipo_bisel']) . '">';
```

**✅ Guarda el archivo**

---

## 🎨 PASO 4: IMPLEMENTAR FRONTEND

### **4.1. Modificar HTML**

**Archivo:** `catalogo/catalogo.html`

**Línea 74** - Después del filtro de marca, agregar:

```html
<!-- Filtro por Movimiento -->
<div class="filtro-group">
    <select id="filtro-movimiento" class="filtro-select">
        <option value="">Todos los movimientos</option>
    </select>
</div>

<!-- Filtro por Pulsera -->
<div class="filtro-group">
    <select id="filtro-pulsera" class="filtro-select">
        <option value="">Todas las pulseras</option>
    </select>
</div>

<!-- Filtro por Resistencia al Agua -->
<div class="filtro-group">
    <select id="filtro-resistencia" class="filtro-select">
        <option value="">Resistencia al agua</option>
    </select>
</div>

<!-- Botón Limpiar Filtros -->
<div class="filtro-group">
    <button id="limpiar-filtros" class="btn-limpiar">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
            <path d="M21 3v5h-5"/>
            <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
            <path d="M3 21v-5h5"/>
        </svg>
        Limpiar Filtros
    </button>
</div>
```

**✅ Guarda el archivo**

---

### **4.2. Modificar JavaScript**

**Archivo:** `catalogo/js/mostrar-reloj.js`

#### **A. Agregar funciones para cargar opciones (después de línea 40)**

```javascript
// Función para cargar movimientos
function cargarMovimientos() {
    const tarjetas = document.querySelectorAll('.contenedor-card');
    const movimientos = new Set();
    
    tarjetas.forEach(tarjeta => {
        const movimiento = tarjeta.getAttribute('data-movimiento');
        if (movimiento && movimiento.trim() !== '') {
            movimientos.add(movimiento);
        }
    });
    
    const select = document.getElementById('filtro-movimiento');
    const sortedMovimientos = Array.from(movimientos).sort();
    
    sortedMovimientos.forEach(mov => {
        const option = document.createElement('option');
        option.value = mov;
        option.textContent = `⚙️ ${mov}`;
        select.appendChild(option);
    });
}

// Función para cargar pulseras
function cargarPulseras() {
    const tarjetas = document.querySelectorAll('.contenedor-card');
    const pulseras = new Set();
    
    tarjetas.forEach(tarjeta => {
        const pulsera = tarjeta.getAttribute('data-pulsera');
        if (pulsera && pulsera.trim() !== '') {
            pulseras.add(pulsera);
        }
    });
    
    const select = document.getElementById('filtro-pulsera');
    const sortedPulseras = Array.from(pulseras).sort();
    
    sortedPulseras.forEach(pul => {
        const option = document.createElement('option');
        option.value = pul;
        option.textContent = `📿 ${pul}`;
        select.appendChild(option);
    });
}

// Función para cargar resistencias al agua
function cargarResistencias() {
    const tarjetas = document.querySelectorAll('.contenedor-card');
    const resistencias = new Set();
    
    tarjetas.forEach(tarjeta => {
        const resistencia = tarjeta.getAttribute('data-resistencia');
        if (resistencia && resistencia.trim() !== '') {
            resistencias.add(resistencia);
        }
    });
    
    const select = document.getElementById('filtro-resistencia');
    const sortedResistencias = Array.from(resistencias).sort();
    
    sortedResistencias.forEach(res => {
        const option = document.createElement('option');
        option.value = res;
        option.textContent = `💧 ${res}`;
        select.appendChild(option);
    });
}
```

#### **B. Modificar función aplicarFiltros() (línea 78)**

**BUSCAR:**
```javascript
function aplicarFiltros() {
    const filtroPrecio = document.getElementById('filtro-precio').value;
    const filtroMarca = document.getElementById('filtro-marca').value;
```

**REEMPLAZAR POR:**
```javascript
function aplicarFiltros() {
    const filtroPrecio = document.getElementById('filtro-precio').value;
    const filtroMarca = document.getElementById('filtro-marca').value;
    const filtroMovimiento = document.getElementById('filtro-movimiento').value;
    const filtroPulsera = document.getElementById('filtro-pulsera').value;
    const filtroResistencia = document.getElementById('filtro-resistencia').value;
```

**BUSCAR (en la misma función, línea ~96):**
```javascript
// Si hay algún filtro activo, excluir vendidos
if ((filtroPrecio || filtroMarca) && esVendido) {
```

**REEMPLAZAR POR:**
```javascript
// Si hay algún filtro activo, excluir vendidos
if ((filtroPrecio || filtroMarca || filtroMovimiento || filtroPulsera || filtroResistencia) && esVendido) {
```

**AGREGAR AL FINAL de la función (antes del cierre de llave, línea ~130):**
```javascript
    // Filtrar por movimiento
    if (filtroMovimiento && mostrar) {
        const movimiento = tarjeta.getAttribute('data-movimiento');
        if (movimiento !== filtroMovimiento) {
            mostrar = false;
        }
    }
    
    // Filtrar por pulsera
    if (filtroPulsera && mostrar) {
        const pulsera = tarjeta.getAttribute('data-pulsera');
        if (pulsera !== filtroPulsera) {
            mostrar = false;
        }
    }
    
    // Filtrar por resistencia al agua
    if (filtroResistencia && mostrar) {
        const resistencia = tarjeta.getAttribute('data-resistencia');
        if (resistencia !== filtroResistencia) {
            mostrar = false;
        }
    }
```

#### **C. Agregar función limpiarFiltros() (al final del archivo)**

```javascript
// Función para limpiar todos los filtros
function limpiarFiltros() {
    document.getElementById('filtro-precio').value = '';
    document.getElementById('filtro-marca').value = '';
    document.getElementById('filtro-movimiento').value = '';
    document.getElementById('filtro-pulsera').value = '';
    document.getElementById('filtro-resistencia').value = '';
    aplicarFiltros();
}
```

#### **D. Agregar event listeners (línea ~270, en DOMContentLoaded)**

**BUSCAR:**
```javascript
document.getElementById('filtro-precio').addEventListener('change', aplicarFiltros);
document.getElementById('filtro-marca').addEventListener('change', aplicarFiltros);
```

**AGREGAR DESPUÉS:**
```javascript
document.getElementById('filtro-movimiento').addEventListener('change', aplicarFiltros);
document.getElementById('filtro-pulsera').addEventListener('change', aplicarFiltros);
document.getElementById('filtro-resistencia').addEventListener('change', aplicarFiltros);

// Botón limpiar filtros
const btnLimpiar = document.getElementById('limpiar-filtros');
if (btnLimpiar) {
    btnLimpiar.addEventListener('click', limpiarFiltros);
}
```

#### **E. Llamar funciones de carga (línea ~70, en cargarRelojes success)**

**BUSCAR:**
```javascript
cargarMarcas();
```

**AGREGAR DESPUÉS:**
```javascript
cargarMovimientos();
cargarPulseras();
cargarResistencias();
```

**✅ Guarda el archivo**

---

### **4.3. Agregar Estilos CSS**

**Archivo:** `catalogo/css/catalogo.css`

**Línea 1053** - Después de `.filtro-select:focus`, agregar:

```css
/* Botón limpiar filtros */
.btn-limpiar {
    background: rgba(255, 207, 102, 0.1);
    color: #FFCF66;
    border: 1px solid rgba(255, 207, 102, 0.5);
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.btn-limpiar svg {
    width: 16px;
    height: 16px;
}

.btn-limpiar:hover {
    background: rgba(255, 207, 102, 0.2);
    border-color: #FFCF66;
    box-shadow: 0 0 10px rgba(255, 207, 102, 0.3);
    transform: translateY(-2px);
}

.btn-limpiar:active {
    transform: translateY(0);
}

/* Responsive para más filtros */
@media (max-width: 1200px) {
    .filtros-row {
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    }
}

@media (max-width: 768px) {
    .filtros-row {
        grid-template-columns: 1fr;
    }
    
    .btn-limpiar {
        width: 100%;
        justify-content: center;
    }
}
```

**✅ Guarda el archivo**

---

## 🧪 PASO 5: PROBAR FILTROS

### **Test 1: Verificar que aparecen los filtros**
```
1. Abre: https://finoso.store/catalogo/catalogo.html
2. Verifica que ves 5 filtros + botón "Limpiar Filtros"
3. Abre cada filtro y verifica que tiene opciones
```

### **Test 2: Filtro individual**
```
1. Selecciona "Movimiento: Cuarzo"
2. Verifica que solo aparecen relojes de cuarzo
3. Clic en "Limpiar Filtros"
4. Verifica que aparecen todos los relojes
```

### **Test 3: Filtros combinados**
```
1. Selecciona "Pulsera: Acero"
2. Selecciona "Precio: 100K-500K"
3. Verifica que solo aparecen relojes que cumplen AMBAS condiciones
4. Agrega un tercer filtro "Movimiento: Cuarzo"
5. Verifica que se filtran correctamente
```

### **Test 4: Sin resultados**
```
1. Selecciona combinación imposible (ej: todos los filtros al máximo)
2. Verifica que aparece mensaje "No hay relojes con esos filtros"
3. Botón "Limpiar Filtros" debe funcionar
```

### **Test 5: Consola del navegador**
```
1. Abre consola (F12)
2. Aplica un filtro
3. Deberías ver logs como:
   🔍 Aplicando filtros: { precio: "", marca: "", movimiento: "Cuarzo" }
   ✅ Relojes visibles: 2
```

### **Test 6: Responsive (móvil)**
```
1. Abre DevTools (F12) > Toggle device toolbar
2. Selecciona iPhone 12 Pro
3. Verifica que filtros se apilan verticalmente
4. Verifica que botón "Limpiar" es táctil
```

---

## ✅ CHECKLIST COMPLETO

### **Datos:**
- [ ] Ejecutar script de datos (manual o automático)
- [ ] Verificar en `verificar_campos_filtros.php`
- [ ] Al menos 70% de datos en 3 campos

### **Backend:**
- [ ] Modificar `mostrar_relojes.php` (línea 57)
- [ ] Agregar 6 data-attributes
- [ ] Probar que se generan correctamente

### **Frontend HTML:**
- [ ] Agregar 3 selectores nuevos en `catalogo.html`
- [ ] Agregar botón "Limpiar Filtros"
- [ ] Verificar que IDs coinciden con JavaScript

### **JavaScript:**
- [ ] Agregar 3 funciones: `cargarMovimientos()`, `cargarPulseras()`, `cargarResistencias()`
- [ ] Modificar `aplicarFiltros()` con 3 nuevos filtros
- [ ] Agregar función `limpiarFiltros()`
- [ ] Agregar 4 event listeners
- [ ] Llamar funciones de carga en `cargarRelojes()`

### **CSS:**
- [ ] Agregar estilos para `.btn-limpiar`
- [ ] Agregar responsive adicional
- [ ] Verificar que se ve bien en móvil

### **Pruebas:**
- [ ] Test 1: Filtros aparecen
- [ ] Test 2: Filtro individual funciona
- [ ] Test 3: Filtros combinados funcionan
- [ ] Test 4: Mensaje sin resultados
- [ ] Test 5: Logs en consola
- [ ] Test 6: Responsive móvil

---

## 📊 RESULTADO ESPERADO

### **ANTES:**
```
[Precio ▼]  [Marca ▼]

Usuario debe revisar todos los relojes manualmente
```

### **DESPUÉS:**
```
[Precio ▼]  [Marca ▼]  [⚙️ Movimiento ▼]
[📿 Pulsera ▼]  [💧 Resistencia H₂O ▼]  [🔄 Limpiar]

Usuario encuentra exactamente lo que busca en segundos
```

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### **Problema: Los filtros no aparecen**
```
✅ Verifica que agregaste el HTML en catalogo.html
✅ Verifica que los IDs coinciden: filtro-movimiento, filtro-pulsera, filtro-resistencia
✅ Abre consola (F12) y busca errores
```

### **Problema: Los filtros están vacíos (sin opciones)**
```
✅ Verifica que ejecutaste el script de datos
✅ Verifica en phpMyAdmin que los campos tienen datos
✅ Verifica que modificaste mostrar_relojes.php con data-attributes
✅ Abre consola y busca: "Tarjetas encontradas: X"
```

### **Problema: Al filtrar no pasa nada**
```
✅ Verifica que agregaste los event listeners en JavaScript
✅ Abre consola y busca: "Aplicando filtros: {...}"
✅ Verifica que la función aplicarFiltros() tiene los nuevos filtros
```

### **Problema: Botón "Limpiar" no funciona**
```
✅ Verifica que agregaste la función limpiarFiltros()
✅ Verifica que agregaste el event listener para el botón
✅ Verifica que el ID del botón es: limpiar-filtros
```

---

## 🎉 ¡LISTO!

Si completaste todos los pasos, ahora tienes un **sistema de filtros avanzados** funcionando:

✅ 5 filtros diferentes (Precio, Marca, Movimiento, Pulsera, Resistencia)
✅ Búsqueda combinada
✅ Botón para limpiar
✅ Emojis visuales
✅ Responsive

**Siguiente paso:** Probar en producción o agregar más filtros (Peso, Bisel, etc.)

---

**Tiempo total:** ~2 horas  
**Dificultad:** Media  
**Resultado:** ⭐⭐⭐⭐⭐

