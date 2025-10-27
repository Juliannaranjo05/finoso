# 🎠 Configuración del Carrusel de Relojes Destacados

## 📋 Descripción

El sistema de carrusel ahora es **completamente dinámico** y se actualiza automáticamente cuando se venden relojes. No necesitas modificar código manualmente cada vez que se venda un reloj.

## ⚙️ Configuración

### Archivo: `php/config_carrusel.php`

```php
$config_carrusel = [
    'modo' => 'auto', // 'auto', 'id_especifico', 'nombre_especifico'
    'id_reloj_efecto' => 2, // Solo se usa si modo = 'id_especifico'
    'nombre_reloj_efecto' => 'Patk Phlppe Bicolor Dorado - Negro', // Solo se usa si modo = 'nombre_especifico'
    'ordenar_por' => 'precio_desc', // 'precio_desc', 'precio_asc', 'id_asc', 'id_desc'
    'limite_relojes' => 4
];
```

## 🎯 Modos de Funcionamiento

### 1. **Modo AUTO** (Recomendado)
```php
'modo' => 'auto'
```
- ✅ **Automático**: El reloj más caro disponible tendrá el efecto
- ✅ **Se actualiza solo**: Cuando se venda un reloj, el siguiente más caro tomará su lugar
- ✅ **Sin mantenimiento**: No necesitas cambiar nada manualmente

### 2. **Modo ID ESPECÍFICO**
```php
'modo' => 'id_especifico',
'id_reloj_efecto' => 2
```
- 🎯 **Control total**: Puedes especificar exactamente qué reloj debe tener el efecto
- ⚠️ **Mantenimiento**: Si ese reloj se vende, necesitas cambiar el ID manualmente

### 3. **Modo NOMBRE ESPECÍFICO**
```php
'modo' => 'nombre_especifico',
'nombre_reloj_efecto' => 'Patk Phlppe Bicolor Dorado - Negro'
```
- 🎯 **Por nombre**: Especificas el reloj por su nombre exacto
- ⚠️ **Mantenimiento**: Si ese reloj se vende, necesitas cambiar el nombre manualmente

## 🔄 Ordenamiento

### Opciones disponibles:
- `'precio_desc'` - Por precio descendente (más caro primero) **← Recomendado**
- `'precio_asc'` - Por precio ascendente (más barato primero)
- `'id_asc'` - Por ID ascendente (orden de creación)
- `'id_desc'` - Por ID descendente (más reciente primero)

## 🚀 Ejemplos de Uso

### Escenario 1: Quieres que siempre sea el más caro
```php
$config_carrusel = [
    'modo' => 'auto',
    'ordenar_por' => 'precio_desc'
];
```

### Escenario 2: Siempre el Patek Philippe (si está disponible)
```php
$config_carrusel = [
    'modo' => 'id_especifico',
    'id_reloj_efecto' => 2
];
```

### Escenario 3: Siempre el Richard Mille por nombre
```php
$config_carrusel = [
    'modo' => 'nombre_especifico',
    'nombre_reloj_efecto' => 'Rchrd Mll Calavera Blanco Circones Negros-Dorados'
];
```

## 🔧 Cómo Cambiar la Configuración

1. **Abre** `php/config_carrusel.php`
2. **Modifica** la variable `$config_carrusel`
3. **Guarda** el archivo
4. **Recarga** la página del index

## 📊 Monitoreo

El sistema incluye logs en la consola del navegador que te muestran:
- ✅ Cuántos relojes se cargaron
- ✅ Cuál reloj tiene el efecto
- ✅ Qué modo está activo
- ✅ Lista de todos los relojes cargados

## 🎯 Recomendación

**Usa el modo AUTO** con ordenamiento por precio descendente:

```php
$config_carrusel = [
    'modo' => 'auto',
    'ordenar_por' => 'precio_desc',
    'limite_relojes' => 4
];
```

Esto te dará:
- ✅ **Automático**: Sin mantenimiento manual
- ✅ **Lógico**: Siempre el reloj más caro disponible
- ✅ **Profesional**: Se actualiza solo cuando se venden relojes

## 🔄 Flujo Automático

1. **Usuario compra un reloj** → Se marca como `vendido = 1`
2. **Sistema detecta** → El reloj ya no aparece en la consulta
3. **Carrusel se actualiza** → Automáticamente muestra los 4 relojes disponibles
4. **Efecto se aplica** → Al reloj configurado (auto = más caro disponible)

¡**Ya no necesitas tocar código cada vez que vendas un reloj!** 🎉


