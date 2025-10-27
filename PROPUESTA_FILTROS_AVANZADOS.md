# 🎯 PROPUESTA: FILTROS AVANZADOS EN CATÁLOGO

## 📊 ANÁLISIS INICIAL

### **✅ CAMPOS DISPONIBLES EN BD:**
```sql
reloj:
  ✓ movimiento (varchar 100)      - Ej: "Cuarzo", "Automático", "Mecánico"
  ✓ pulsera (varchar 100)          - Ej: "Acero inoxidable", "Cuero", "Silicona"
  ✓ peso (varchar 50)              - Ej: "150g", "200g", "Ligero"
  ✓ tipo_bisel (varchar 50)        - Ej: "Fijo", "Giratorio"
  ✓ resistencia_agua (varchar 50)  - Ej: "50m", "100m", "3ATM"
  ✓ eslabones (varchar 100)        - Ej: "Ajustables", "Fijos"
```

### **🎯 FILTROS A IMPLEMENTAR (PRIORITARIOS):**

**FASE 1 - Esenciales:**
1. ⚙️ **Movimiento** (más relevante para compradores)
2. 📿 **Tipo de Pulsera** (importante para preferencias)
3. 💧 **Resistencia al Agua** (criterio de compra común)

**FASE 2 - Adicionales:**
4. ⚖️ **Peso** (si hay suficientes datos)
5. 🔄 **Tipo de Bisel** (para usuarios avanzados)

---

## 🎨 DISEÑO PROPUESTO

### **Ubicación Visual:**

```
┌────────────────────────────────────────────────┐
│         CATÁLOGO - ESCOGE TU FLOW              │
├────────────────────────────────────────────────┤
│                                                 │
│  [Precio ▼] [Marca ▼] [Movimiento ▼]          │
│  [Pulsera ▼] [Resistencia H₂O ▼]              │
│                                                 │
├────────────────────────────────────────────────┤
│  ┌────┐ ┌────┐ ┌────┐ ┌────┐                 │
│  │    │ │    │ │    │ │    │  ← Relojes      │
│  └────┘ └────┘ └────┘ └────┘                  │
└────────────────────────────────────────────────┘
```

### **Mockup HTML:**

```html
<div class="filtros-container">
    <div class="filtros-section">
        <div class="filtros-row">
            <!-- EXISTENTES -->
            <div class="filtro-group">
                <select id="filtro-precio" class="filtro-select">
                    <option value="">Todos los precios</option>
                    ...
                </select>
            </div>
            
            <div class="filtro-group">
                <select id="filtro-marca" class="filtro-select">
                    <option value="">Todas las marcas</option>
                    ...
                </select>
            </div>
            
            <!-- NUEVOS FILTROS -->
            <div class="filtro-group">
                <select id="filtro-movimiento" class="filtro-select">
                    <option value="">Todos los movimientos</option>
                    <option value="Cuarzo">⚙️ Cuarzo</option>
                    <option value="Automático">🔄 Automático</option>
                    <option value="Mecánico">⚙️ Mecánico</option>
                </select>
            </div>
            
            <div class="filtro-group">
                <select id="filtro-pulsera" class="filtro-select">
                    <option value="">Todas las pulseras</option>
                    <option value="Acero">🔗 Acero Inoxidable</option>
                    <option value="Cuero">🧳 Cuero</option>
                    <option value="Silicona">💪 Silicona/Caucho</option>
                </select>
            </div>
            
            <div class="filtro-group">
                <select id="filtro-resistencia" class="filtro-select">
                    <option value="">Resistencia al agua</option>
                    <option value="3ATM">💧 Salpicaduras (3ATM)</option>
                    <option value="5ATM">🌊 Natación (5ATM)</option>
                    <option value="10ATM">🏊 Buceo (10ATM+)</option>
                </select>
            </div>
            
            <!-- Botón para limpiar filtros -->
            <div class="filtro-group">
                <button id="limpiar-filtros" class="btn-limpiar">
                    🔄 Limpiar Filtros
                </button>
            </div>
        </div>
    </div>
</div>
```

---

## 💻 IMPLEMENTACIÓN TÉCNICA

### **1. Modificar Backend (PHP)**

**Archivo:** `catalogo/php/mostrar_relojes.php`

**Agregar atributos data al HTML:**
```php
// LÍNEA 57 - Modificar esta línea
$cardsHTML .= '<div class="contenedor-card" 
    data-marca="' . htmlspecialchars($row['marca']) . '" 
    data-precio="' . $row['precio'] . '"
    data-movimiento="' . htmlspecialchars($row['movimiento']) . '"
    data-pulsera="' . htmlspecialchars($row['pulsera']) . '"
    data-resistencia="' . htmlspecialchars($row['resistencia_agua']) . '"
    data-peso="' . htmlspecialchars($row['peso']) . '">';
```

### **2. Modificar Frontend (HTML)**

**Archivo:** `catalogo/catalogo.html`

**Agregar nuevos selectores después de los existentes:**
```html
<!-- LÍNEA 74 - DESPUÉS DEL FILTRO DE MARCA -->

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

### **3. Modificar JavaScript**

**Archivo:** `catalogo/js/mostrar-reloj.js`

#### **A. Cargar opciones dinámicamente:**
```javascript
// AGREGAR DESPUÉS DE cargarMarcas() (línea ~40)

// Función para cargar valores únicos de movimiento
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
        
        // Agregar emoji según tipo
        let emoji = '⚙️';
        if (mov.toLowerCase().includes('automático')) emoji = '🔄';
        if (mov.toLowerCase().includes('mecánico')) emoji = '⚙️';
        if (mov.toLowerCase().includes('digital')) emoji = '📱';
        
        option.textContent = `${emoji} ${mov}`;
        select.appendChild(option);
    });
}

// Función para cargar valores únicos de pulsera
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
        
        // Agregar emoji según material
        let emoji = '📿';
        if (pul.toLowerCase().includes('acero')) emoji = '🔗';
        if (pul.toLowerCase().includes('cuero')) emoji = '🧳';
        if (pul.toLowerCase().includes('silicona') || pul.toLowerCase().includes('caucho')) emoji = '💪';
        if (pul.toLowerCase().includes('oro')) emoji = '✨';
        
        option.textContent = `${emoji} ${pul}`;
        select.appendChild(option);
    });
}

// Función para cargar valores únicos de resistencia al agua
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
        
        // Agregar emoji según nivel
        let emoji = '💧';
        if (res.includes('5') || res.toLowerCase().includes('nata')) emoji = '🌊';
        if (res.includes('10') || res.toLowerCase().includes('buceo')) emoji = '🏊';
        
        option.textContent = `${emoji} ${res}`;
        select.appendChild(option);
    });
}
```

#### **B. Modificar función aplicarFiltros():**
```javascript
// MODIFICAR FUNCIÓN EXISTENTE (línea 78)
function aplicarFiltros() {
    const filtroPrecio = document.getElementById('filtro-precio').value;
    const filtroMarca = document.getElementById('filtro-marca').value;
    const filtroMovimiento = document.getElementById('filtro-movimiento').value;
    const filtroPulsera = document.getElementById('filtro-pulsera').value;
    const filtroResistencia = document.getElementById('filtro-resistencia').value;
    
    console.log('🔍 Aplicando filtros:', { 
        precio: filtroPrecio, 
        marca: filtroMarca,
        movimiento: filtroMovimiento,
        pulsera: filtroPulsera,
        resistencia: filtroResistencia
    });
    
    const tarjetas = document.querySelectorAll('.contenedor-card');
    console.log('📋 Tarjetas encontradas:', tarjetas.length);
    
    let contadorVisibles = 0;
    
    tarjetas.forEach((tarjeta, index) => {
        let mostrar = true;
        
        // Excluir relojes vendidos cuando hay filtros activos
        const cuadroCard = tarjeta.querySelector('.cuadro-card');
        const esVendido = cuadroCard && cuadroCard.classList.contains('vendido');
        
        if ((filtroPrecio || filtroMarca || filtroMovimiento || filtroPulsera || filtroResistencia) && esVendido) {
            mostrar = false;
        }
        
        // Filtrar por precio
        if (filtroPrecio && mostrar) {
            const precioStr = tarjeta.getAttribute('data-precio');
            const precio = parseInt(precioStr);
            const [min, max] = filtroPrecio.split('-').map(Number);
            
            if (precio < min || precio > max) {
                mostrar = false;
            }
        }
        
        // Filtrar por marca
        if (filtroMarca && mostrar) {
            const marca = tarjeta.getAttribute('data-marca');
            if (marca !== filtroMarca) {
                mostrar = false;
            }
        }
        
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
            // Búsqueda flexible (contiene)
            if (!pulsera || !pulsera.toLowerCase().includes(filtroPulsera.toLowerCase())) {
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
        
        // Mostrar u ocultar tarjeta
        tarjeta.style.display = mostrar ? 'block' : 'none';
        if (mostrar) contadorVisibles++;
    });
    
    console.log('✅ Relojes visibles:', contadorVisibles);
    
    // Mostrar mensaje si no hay resultados
    mostrarMensajeSinResultados(contadorVisibles);
    
    // Ajustar layout
    ajustarLayout();
}
```

#### **C. Agregar función limpiar filtros:**
```javascript
// AGREGAR AL FINAL DEL ARCHIVO

// Función para limpiar todos los filtros
function limpiarFiltros() {
    console.log('🧹 Limpiando todos los filtros...');
    
    // Resetear todos los selectores
    document.getElementById('filtro-precio').value = '';
    document.getElementById('filtro-marca').value = '';
    document.getElementById('filtro-movimiento').value = '';
    document.getElementById('filtro-pulsera').value = '';
    document.getElementById('filtro-resistencia').value = '';
    
    // Aplicar filtros (mostrar todo)
    aplicarFiltros();
    
    console.log('✅ Filtros limpiados');
}

// Event listener para botón limpiar
document.addEventListener('DOMContentLoaded', () => {
    const btnLimpiar = document.getElementById('limpiar-filtros');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', limpiarFiltros);
    }
});
```

#### **D. Agregar listeners a nuevos filtros:**
```javascript
// MODIFICAR EN DOMContentLoaded (agregar después de línea ~272)

document.getElementById('filtro-movimiento').addEventListener('change', aplicarFiltros);
document.getElementById('filtro-pulsera').addEventListener('change', aplicarFiltros);
document.getElementById('filtro-resistencia').addEventListener('change', aplicarFiltros);
```

#### **E. Cargar nuevos filtros al iniciar:**
```javascript
// MODIFICAR EN cargarRelojes() success (después de cargarMarcas())

cargarMovimientos();
cargarPulseras();
cargarResistencias();
```

### **4. Estilos CSS**

**Archivo:** `catalogo/css/catalogo.css`

```css
/* AGREGAR DESPUÉS DE .filtro-select (línea ~1053) */

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

/* Layout de filtros mejorado */
.filtros-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    align-items: end;
}

/* Responsive para filtros */
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

---

## 🎯 CARACTERÍSTICAS

### **✅ Funcionalidades:**

1. **Filtros Dinámicos:** Solo muestra opciones que existen en los relojes actuales
2. **Búsqueda Combinada:** Puedes usar múltiples filtros al mismo tiempo
3. **Emojis Visuales:** Cada opción tiene un emoji para mejor UX
4. **Búsqueda Flexible:** El filtro de pulsera usa "contiene" para capturar variaciones
5. **Excluir Vendidos:** Los relojes vendidos se ocultan cuando hay filtros activos
6. **Botón Limpiar:** Un clic resetea todos los filtros
7. **Sin Resultados:** Muestra mensaje amigable si no hay relojes
8. **Responsive:** Se adapta a móviles y tablets

### **🎨 Experiencia de Usuario:**

```
Escenario 1: Usuario busca reloj deportivo
→ Selecciona: Pulsera "Silicona" + Resistencia "10ATM"
→ Solo muestra relojes deportivos resistentes al agua

Escenario 2: Usuario busca reloj clásico
→ Selecciona: Movimiento "Automático" + Pulsera "Cuero"
→ Solo muestra relojes automáticos con correa de cuero

Escenario 3: Usuario quiere ver todo
→ Clic en "Limpiar Filtros"
→ Muestra todos los relojes disponibles
```

---

## 📊 VENTAJAS

### **Para el Usuario:**
✅ Encuentra lo que busca más rápido
✅ Búsqueda personalizada según preferencias
✅ Interfaz intuitiva con emojis
✅ No ve relojes irrelevantes

### **Para el Negocio:**
✅ Mejor experiencia = más ventas
✅ Datos ya existen (no requiere cambios en BD)
✅ Fácil de extender (agregar más filtros después)
✅ SEO-friendly (URLs con parámetros para filtros)

### **Técnico:**
✅ No requiere cambios en BD
✅ JavaScript puro (sin librerías)
✅ Performance óptimo (filtrado del lado del cliente)
✅ Compatible con sistema actual

---

## 🚀 PLAN DE IMPLEMENTACIÓN

### **FASE 1: Preparación (10 min)**
```
✓ Verificar datos en BD con: admin/verificar_campos_filtros.php
✓ Revisar que campos tienen datos suficientes
✓ Decidir qué filtros implementar primero
```

### **FASE 2: Backend (15 min)**
```
✓ Modificar mostrar_relojes.php
✓ Agregar data-attributes a tarjetas
✓ Probar que datos se cargan correctamente
```

### **FASE 3: Frontend HTML (10 min)**
```
✓ Agregar nuevos selectores en catalogo.html
✓ Agregar botón "Limpiar Filtros"
✓ Verificar estructura visual
```

### **FASE 4: JavaScript (30 min)**
```
✓ Agregar funciones cargarMovimientos/Pulseras/Resistencias
✓ Modificar aplicarFiltros() para nuevos campos
✓ Agregar función limpiarFiltros()
✓ Agregar event listeners
```

### **FASE 5: Estilos (15 min)**
```
✓ Estilos para btn-limpiar
✓ Ajustes responsive
✓ Pulir detalles visuales
```

### **FASE 6: Pruebas (20 min)**
```
✓ Probar cada filtro individualmente
✓ Probar combinaciones de filtros
✓ Verificar botón "Limpiar"
✓ Probar en móvil
✓ Verificar mensaje "sin resultados"
```

**TIEMPO TOTAL ESTIMADO: ~1.5 horas** ⏱️

---

## 🧪 CASOS DE PRUEBA

### **Test 1: Filtro Individual**
```
1. Seleccionar "Movimiento: Cuarzo"
2. Verificar que solo aparecen relojes de cuarzo
3. Limpiar filtros
4. Verificar que aparecen todos los relojes
```

### **Test 2: Filtros Combinados**
```
1. Seleccionar "Pulsera: Acero"
2. Seleccionar "Precio: 100K-500K"
3. Verificar que solo aparecen relojes que cumplan AMBOS
```

### **Test 3: Sin Resultados**
```
1. Seleccionar combinación imposible
2. Verificar que aparece mensaje "No hay relojes..."
3. Verificar que botón "Limpiar" funciona
```

### **Test 4: Responsive**
```
1. Abrir en móvil
2. Verificar que filtros se apilan verticalmente
3. Verificar que botones son táctiles
```

---

## 📈 EXTENSIONES FUTURAS

### **Fase 2 (Opcional):**
```
□ Filtro por Peso (si hay datos)
□ Filtro por Tipo de Bisel
□ Filtro por Género (Hombre/Mujer/Unisex)
□ Rango de peso (ligero/medio/pesado)
```

### **Fase 3 (Avanzado):**
```
□ Búsqueda por texto
□ Filtros con checkboxes (múltiple selección)
□ Slider para rango de precio
□ Guardado de preferencias en localStorage
□ URL con parámetros para compartir búsquedas
```

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

**Backend:**
- [ ] Modificar `catalogo/php/mostrar_relojes.php` (línea 57)
- [ ] Agregar data-attributes: movimiento, pulsera, resistencia
- [ ] Probar que datos se generan correctamente

**Frontend:**
- [ ] Agregar 3 nuevos selectores en `catalogo.html` (después línea 74)
- [ ] Agregar botón "Limpiar Filtros"
- [ ] Verificar HTML válido

**JavaScript:**
- [ ] Agregar funciones `cargarMovimientos()`, `cargarPulseras()`, `cargarResistencias()`
- [ ] Modificar función `aplicarFiltros()` (línea 78)
- [ ] Agregar función `limpiarFiltros()`
- [ ] Agregar event listeners para nuevos filtros
- [ ] Llamar funciones de carga en `cargarRelojes()`

**CSS:**
- [ ] Agregar estilos para `.btn-limpiar`
- [ ] Ajustar responsive de filtros
- [ ] Verificar diseño en móvil

**Pruebas:**
- [ ] Test filtro individual
- [ ] Test filtros combinados
- [ ] Test sin resultados
- [ ] Test botón limpiar
- [ ] Test responsive móvil
- [ ] Test con relojes vendidos

---

## 🎉 RESULTADO ESPERADO

### **Antes:**
```
Catálogo con 2 filtros básicos (Precio, Marca)
Usuario tiene que revisar todos los relojes manualmente
```

### **Después:**
```
Catálogo con 5 filtros (Precio, Marca, Movimiento, Pulsera, Resistencia)
Usuario encuentra exactamente lo que busca en segundos
Mejor conversión de visitas a ventas
```

---

**¿Listo para implementar?** 🚀

Ejecuta primero: `http://127.0.0.1/finoso/admin/verificar_campos_filtros.php`  
Para ver qué datos hay disponibles y decidir qué filtros implementar.

