# 🗺️ MAPA DE UBICACIÓN IMPLEMENTADO

## ✅ RESUMEN DE IMPLEMENTACIÓN

Se ha agregado una nueva sección "VISÍTANOS" en `index.html` con un **Google Maps interactivo** y información de contacto completa.

---

## 📍 UBICACIÓN ACTUAL (DATOS DE EJEMPLO)

**Estos datos son de prueba y deben reemplazarse con los datos reales:**

```
📍 Dirección: Cra 43A #10-15, El Poblado, Medellín, Colombia
🕒 Horarios: 
   - Lun - Vie: 9:00 AM - 6:00 PM
   - Sábados: 9:00 AM - 2:00 PM
   - Domingos: Cerrado
📱 WhatsApp: +57 317 389 7119
```

---

## 🎨 CARACTERÍSTICAS IMPLEMENTADAS

### 1️⃣ **Mapa de Google Maps Interactivo**
- ✅ Mapa embebido con iframe
- ✅ Estilo con borde dorado (#FFCF66)
- ✅ Efecto hover con sombra dorada
- ✅ Filtros de imagen (grayscale ligero)
- ✅ Altura fija de 350px en desktop
- ✅ Bordes redondeados (12px)
- ✅ Zoom y navegación habilitados

### 2️⃣ **Panel de Información de Contacto**
- ✅ **Dirección** con icono de ubicación
- ✅ **Horarios de atención** con icono de reloj
- ✅ **WhatsApp** con icono y enlace clickeable
- ✅ Fondo con gradiente dorado sutil
- ✅ Bordes izquierdos dorados en cada sección
- ✅ Efectos hover elegantes

### 3️⃣ **Animaciones y Transiciones**
- ✅ Fade-in del título "VISÍTANOS"
- ✅ Fade-in del contenedor completo
- ✅ Hover en mapa (elevación y sombra)
- ✅ Hover en info (desplazamiento a la derecha)

### 4️⃣ **Diseño Responsive**
- ✅ **Desktop**: Mapa a la izquierda, info a la derecha
- ✅ **Tablet (< 768px)**: Columna única, mapa arriba
- ✅ **Móvil (< 460px)**: Columna única, mapa más pequeño

---

## 📂 ARCHIVOS MODIFICADOS

### 1. `index.html` (líneas 99-143)
```html
<div class="contenedor-ubicacion">
    <div class="texto-ubicacion">
        <h3>VISÍTANOS</h3>
    </div>
    <div class="mapa-info-container">
        <div class="mapa-wrapper">
            <!-- Google Maps iframe -->
        </div>
        <div class="info-contacto-ubicacion">
            <!-- Dirección, Horarios, WhatsApp -->
        </div>
    </div>
</div>
```

### 2. `css/style.css` (líneas 424-568)
- Estilos principales de la sección
- Animaciones y transiciones
- Efectos hover

### 3. Media Queries en `css/style.css`
- **Líneas 981-1000**: Tablet (< 768px)
- **Líneas 1098-1130**: Móvil (< 460px)

---

## 🔄 CÓMO ACTUALIZAR CON DATOS REALES

### **Paso 1: Obtener la URL del Mapa de Google**
1. Ve a [Google Maps](https://www.google.com/maps)
2. Busca tu ubicación exacta
3. Clic en **"Compartir"**
4. Clic en **"Insertar un mapa"**
5. Copia el código `<iframe>` completo

### **Paso 2: Reemplazar el iframe en `index.html`**
Busca la línea 105-113 y reemplaza el `src` del iframe con tu URL real.

### **Paso 3: Actualizar Dirección** (línea 121)
```html
<span>TU_DIRECCIÓN_REAL<br>CIUDAD, PAÍS</span>
```

### **Paso 4: Actualizar Horarios** (líneas 130-132)
```html
<strong>Horarios de Atención:</strong><br>
Lun - Vie: TUS_HORARIOS<br>
Sábados: TUS_HORARIOS<br>
Domingos: Cerrado/Abierto
```

### **Paso 5: WhatsApp** (línea 139)
Ya está configurado con **+57 317 389 7119** ✅

---

## 🎯 POSICIONAMIENTO EN LA PÁGINA

La sección se encuentra entre:
- ⬆️ **Arriba**: Sección "CATÁLOGO" (botón VER TODOS)
- ⬇️ **Abajo**: Sección "CONTACTO" (redes sociales)

---

## 🔍 VISTA PREVIA

```
┌──────────────────────────────────────────┐
│  CATÁLOGO                                │
│  [VER TODOS]                             │
├──────────────────────────────────────────┤
│  VISÍTANOS                               │
│  ┌────────────┐  ┌──────────────────┐   │
│  │            │  │ 📍 Dirección     │   │
│  │   MAPA     │  │ 🕒 Horarios      │   │
│  │ GOOGLE MAPS│  │ 📱 WhatsApp      │   │
│  └────────────┘  └──────────────────┘   │
├──────────────────────────────────────────┤
│  CONTACTO                                │
│  [Instagram] [WhatsApp]                  │
└──────────────────────────────────────────┘
```

---

## 📱 RESPONSIVE MOBILE

En móvil (< 460px):
```
┌─────────────────────┐
│  VISÍTANOS          │
│  ┌───────────────┐  │
│  │               │  │
│  │     MAPA      │  │
│  │   (250px)     │  │
│  └───────────────┘  │
│                     │
│  📍 Dirección       │
│  🕒 Horarios        │
│  📱 WhatsApp        │
└─────────────────────┘
```

---

## ✅ CHECKLIST DE ACTUALIZACIÓN

- [ ] Obtener URL real de Google Maps
- [ ] Reemplazar iframe en `index.html` (línea 105)
- [ ] Actualizar dirección (línea 121)
- [ ] Actualizar horarios (líneas 130-132)
- [ ] Verificar número de WhatsApp (línea 139)
- [ ] Probar responsividad en móvil
- [ ] Verificar que el mapa carga correctamente
- [ ] Probar el enlace de WhatsApp

---

## 🎨 COLORES USADOS

| Elemento | Color | Uso |
|----------|-------|-----|
| Borde del mapa | `#FFCF66` | Dorado primario |
| Iconos | `#FFCF66` | Dorado primario |
| Hover | `#FFD700` | Dorado claro |
| Texto principal | `#090909` | Negro |
| Fondo info | `rgba(255, 207, 102, 0.05)` | Dorado transparente |
| Borde izquierdo | `#FFCF66` (3px) | Dorado primario |

---

## 📞 CONTACTO ACTUAL

**WhatsApp**: [+57 317 389 7119](https://wa.me/573173897119) ✅

---

🎉 **¡IMPLEMENTACIÓN COMPLETA!**

Cuando tengas los datos reales de ubicación, simplemente actualiza los campos mencionados en la sección "CÓMO ACTUALIZAR CON DATOS REALES".

