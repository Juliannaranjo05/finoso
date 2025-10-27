// Funciones globales para formateo de precios
function redondearAMiles(precio) {
    const miles = Math.floor(precio / 1000);
    const resto = precio % 1000;
    return resto > 500 ? (miles + 1) * 1000 : miles * 1000;
}

function formatPrice(price) {
    let valor = parseFloat(price);
    
    if (isNaN(valor) || !isFinite(valor)) {
        valor = 0;
    }
    
    const valorEnMiles = valor / 1000;
    const valorRedondeado = redondearAMiles(valorEnMiles * 1000) / 1000;
    const formateado = valorRedondeado.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    return '$' + formateado + '.000';
}

// SIN VALIDACIÓN DE SESIÓN - Para usuarios anónimos con favoritos
document.addEventListener('DOMContentLoaded', () => {
    console.log('⭐ Información de Favoritos - Sin validación de sesión');
    cargarProductos();
});

// Función para cargar productos
function cargarProductos() {
    const origenCompra = sessionStorage.getItem('origen_compra');
    
    if (origenCompra === 'favoritos') {
        console.log('⭐ Cargando desde FAVORITOS');
        cargarDesdeFavoritos();
    } else {
        console.log("🛒 Cargando desde CARRITO");
        cargarDesdeCarrito();
    }
}

/**
 * Cargar productos desde FAVORITOS (localStorage)
 */
async function cargarDesdeFavoritos() {
    const idsRelojes = JSON.parse(sessionStorage.getItem('ids_relojes_compra') || '[]');
    
    if (idsRelojes.length === 0) {
        console.warn('⚠️ No hay favoritos para cargar');
        return;
    }
    
    console.log('⭐ Cargando', idsRelojes.length, 'relojes...');
    
    // Cargar cada reloj individualmente
    const productosPromises = idsRelojes.map(idReloj => 
        fetch(`http://127.0.0.1/finoso/informacion/php/obtener_reloj.php?id_reloj=${idReloj}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.id_reloj) {
                    // Adaptar formato para que sea compatible con carrito
                    return {
                        id_reloj: data.id_reloj,
                        nombre: data.nombre,
                        img: data.img,
                        precio_final: parseFloat(data.precio) || 0,
                        precio: parseFloat(data.precio) || 0,
                        descripcion: data.descripcion || '',
                        // Datos adicionales para el carrusel/detalles
                        marca: data.marca || '',
                        eslabones: data.eslabones || '',
                        tipo_bisel: data.tipo_bisel || '',
                        movimiento: data.movimiento || '',
                        pulsera: data.pulsera || '',
                        peso: data.peso || '',
                        resistencia_agua: data.resistencia_agua || ''
                    };
                }
                return null;
            })
            .catch(err => {
                console.error(`❌ Error al cargar reloj ${idReloj}:`, err);
                return null;
            })
    );
    
    const relojesData = await Promise.all(productosPromises);
    const productosValidos = relojesData.filter(reloj => reloj !== null);
    
    console.log('✅', productosValidos.length, 'relojes listos');
    
    // Renderizar productos
    renderizarProductos(productosValidos);
}

/**
 * Cargar productos desde CARRITO NORMAL (BD)
 */
function cargarDesdeCarrito() {
    fetch('http://127.0.0.1/finoso/php/mostrar_carrito.php')
        .then(res => res.json())
        .then(data => {
            let products = [];
            
            if (data && data.success && data.relojes && Array.isArray(data.relojes)) {
                products = data.relojes;
            }

            if (products.length === 0) {
                console.warn('⚠️ Carrito vacío');
            } else {
                console.log('✅', products.length, 'relojes en carrito');
            }

            renderizarProductos(products);
        })
        .catch(err => {
            console.error('❌ Error al obtener carrito:', err);
        });
}

/**
 * FUNCIÓN COMPARTIDA: Renderizar productos (usado por carrito Y favoritos)
 */
function renderizarProductos(products) {
    console.log("🎨 Renderizando", products.length, "productos");
    
    const contenedor = document.querySelector('.contenedor-info-relojes-carrito');
    const totalContainer = document.querySelector('.total-carrito h3');
    const precioTotal = document.getElementById('precio-total');

    if (!contenedor || !totalContainer) {
        console.error("❌ Elementos del DOM no encontrados");
        return;
    }

    contenedor.innerHTML = '';
    
    if (!Array.isArray(products) || products.length === 0) {
        console.warn("⚠️ No hay productos para renderizar");
        return;
    }
    
    products.forEach((reloj, index) => {
        
        const precioFinal = parseFloat(reloj.precio_final) || 0;
        const precioOriginal = reloj.precio ? parseFloat(reloj.precio) || 0 : null;
        
        const precioFormateado = formatPrice(precioFinal);
        const precioOriginalFormateado = precioOriginal ? formatPrice(precioOriginal) : '';

        const precioOriginalHTML = precioOriginal
            ? `<h4 id="original-price">${precioOriginalFormateado}</h4>`
            : '';

        const productoHTML = `
            <div class="cuadro-info-reloj-carrito">
                <div class="img-reloj-carrito">
                    <img src="http://127.0.0.1/finoso/${reloj.img}" alt="${reloj.nombre}">
                </div>
                <div class="nombre-precio-carrito">
                    <div class="nombre-carrito">
                        <h2>${reloj.nombre}</h2>
                    </div>
                    <div class="precio-carrito">
                        ${precioOriginalHTML}
                        <h3>${precioFormateado}</h3>
                    </div>
                </div>
                <div class="boton-eliminar">
                    <button data-id="${reloj.id_reloj}">Eliminar</button>
                </div>
            </div>
        `;

        contenedor.innerHTML += productoHTML;
    });

    // Calcular total
    const totalSinFormato = products.reduce((acc, reloj) => {
        let precio = parseFloat(reloj.precio_final) || 0;
        return acc + precio;
    }, 0);
    
    const totalFormateado = formatPrice(totalSinFormato);

    // Mostrar total
    totalContainer.textContent = totalFormateado;
    if (precioTotal) {
        precioTotal.textContent = totalFormateado;
    }

    console.log("✅ Renderizado completo. Total:", totalFormateado);
    
    // Hacer products accesible globalmente para updateProduct()
    window.currentProducts = products;
    
    // Actualizar carrusel de productos
    if (products.length > 0) {
        updateProduct();
    }
}

// Variables globales para la navegación de productos
let currentIndex = 0;

function generateParticles() {
    const particlesContainer = document.getElementById('particles');
    for (let i = 0; i < 30; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + 'vw';
        particle.style.animationDelay = Math.random() * 6 + 's';
        particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
        particlesContainer.appendChild(particle);
    }
}

function updateProduct() {
    const products = window.currentProducts || [];
    const product = products[currentIndex];

    if (!product) return;

    // Usar nombres de campos correctos y rutas completas
    document.getElementById('img-lupa').src = `http://127.0.0.1/finoso/${product.img}`;
    document.getElementById('product-name').textContent = product.nombre;

    // NO multiplicar por 1000, los precios ya están correctos
    document.getElementById('current-price').textContent = formatPrice(product.precio_final);
    if (product.precio && product.precio !== product.precio_final) {
        document.getElementById('original-price').textContent = formatPrice(product.precio);
        document.getElementById('original-price').style.display = 'block';
    } else {
        document.getElementById('original-price').style.display = 'none';
    }

    // Mostrar descripción
    const descripcionElement = document.getElementById('product-description');
    if (product.descripcion) {
        descripcionElement.innerHTML = product.descripcion;
    } else {
        descripcionElement.innerHTML = 'Sin descripción disponible.';
    }
    
    // Mostrar especificaciones técnicas si existen
    mostrarEspecificacionesTecnicas(product);
    
    document.getElementById('currentProduct').textContent = currentIndex + 1;
    document.getElementById('totalProducts').textContent = products.length;
    document.getElementById('prevBtn').disabled = currentIndex === 0;
    document.getElementById('nextBtn').disabled = currentIndex === products.length - 1;

    updateThumbnails();
}

function updateThumbnails() {
    const products = window.currentProducts || [];
    const thumbnailsContainer = document.getElementById('thumbnails');
    thumbnailsContainer.innerHTML = '';
    
    products.forEach((product, index) => {
        const thumbnail = document.createElement('img');
        thumbnail.className = `thumbnail ${index === currentIndex ? 'active' : ''}`;
        thumbnail.src = `http://127.0.0.1/finoso/${product.img}`;
        thumbnail.alt = product.nombre;
        thumbnail.onclick = () => goToProduct(index);
        thumbnailsContainer.appendChild(thumbnail);
    });
}

function changeProduct(direction) {
    const products = window.currentProducts || [];
    const newIndex = currentIndex + direction;
    if (newIndex >= 0 && newIndex < products.length) {
        currentIndex = newIndex;
        updateProduct();
        createRippleEffect();
    }
}

function goToProduct(index) {
    const products = window.currentProducts || [];
    if (index >= 0 && index < products.length && index !== currentIndex) {
        currentIndex = index;
        updateProduct();
        createRippleEffect();
    }
}

function buyProduct() {
    const products = window.currentProducts || [];
    const product = products[currentIndex];
    alert(`¡Comprando ${product.name} por ${product.currentPrice}!`);
    createRippleEffect();
}

function createRippleEffect() {
    const ripple = document.createElement('div');
    ripple.className = 'ripple-effect';
    ripple.style.left = Math.random() * window.innerWidth + 'px';
    ripple.style.top = Math.random() * window.innerHeight + 'px';
    ripple.style.width = '20px';
    ripple.style.height = '20px';
    document.body.appendChild(ripple);
    
    setTimeout(() => {
        document.body.removeChild(ripple);
    }, 600);
}

// Navegación con teclado
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowLeft') {
        changeProduct(-1);
    } else if (e.key === 'ArrowRight') {
        changeProduct(1);
    }
});

// Soporte para gestos táctiles
let startX = 0;
let endX = 0;

document.addEventListener('touchstart', function(e) {
    startX = e.touches[0].clientX;
});

document.addEventListener('touchend', function(e) {
    endX = e.changedTouches[0].clientX;
    const diff = startX - endX;
    
    if (Math.abs(diff) > 50) {
        if (diff > 0) {
            changeProduct(1);
        } else {
            changeProduct(-1);
        }
    }
});

// Función para mostrar especificaciones técnicas
function mostrarEspecificacionesTecnicas(product) {
    // Buscar o crear el contenedor de especificaciones
    let especificacionesContainer = document.querySelector('.especificaciones-informacion');
    
    // Si no existe, crearlo después de la descripción
    if (!especificacionesContainer) {
        const descripcionContainer = document.querySelector('.descripcion-informacion');
        if (descripcionContainer) {
            especificacionesContainer = document.createElement('div');
            especificacionesContainer.className = 'especificaciones-informacion';
            descripcionContainer.parentNode.insertBefore(especificacionesContainer, descripcionContainer.nextSibling);
        }
    }
    
    // Limpiar contenido anterior
    if (especificacionesContainer) {
        especificacionesContainer.innerHTML = '';
        
        // Verificar si hay especificaciones técnicas
        const tieneEspecificaciones = product.eslabones || product.tipo_bisel || product.movimiento || 
                                   product.pulsera || product.peso || product.resistencia_agua;
        
        if (tieneEspecificaciones) {
            especificacionesContainer.innerHTML = `
                <h1>Especificaciones Técnicas:</h1>
                <div class="especificaciones-lista">
                    ${product.eslabones ? `<div class="especificacion-item">• Eslabones: ${product.eslabones}</div>` : ''}
                    ${product.tipo_bisel ? `<div class="especificacion-item">• Tipo de Bisel: ${product.tipo_bisel === 'estatico' ? 'Estático' : product.tipo_bisel === 'giratorio' ? 'Giratorio' : product.tipo_bisel}</div>` : ''}
                    ${product.movimiento ? `<div class="especificacion-item">• Movimiento: ${product.movimiento}</div>` : ''}
                    ${product.pulsera ? `<div class="especificacion-item">• Pulsera: ${product.pulsera}</div>` : ''}
                    ${product.peso ? `<div class="especificacion-item">• Peso: ${product.peso}</div>` : ''}
                    ${product.resistencia_agua ? `<div class="especificacion-item">• Resistencia al Agua: ${product.resistencia_agua}</div>` : ''}
                </div>
            `;
            especificacionesContainer.style.display = 'block';
        } else {
            especificacionesContainer.style.display = 'none';
        }
    }
}

// Inicialización de efectos visuales
generateParticles();
// updateProduct() se llama después de renderizar productos