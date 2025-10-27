// Cargar productos cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    // 🔥 LIMPIAR sessionStorage de favoritos si estamos en informacion-carrito
    // Esto evita que se cargue desde favoritos cuando se debe cargar desde carrito BD
    console.log('🧹 Limpiando sessionStorage para carrito normal...');
    sessionStorage.removeItem('origen_compra');
    sessionStorage.removeItem('ids_relojes_compra');
    console.log('✅ SessionStorage limpiado');
    
    // La protección de sesión la maneja proteccion_rutas.js
    // Aquí solo cargamos los productos
    console.log('✅ Cargando productos del carrito BD...');
    cargarProductos();
});

// Función para redondear hacia arriba a miles (GLOBAL)
const redondearAMiles = (precio) => {
    const miles = Math.floor(precio / 1000);
    const resto = precio % 1000;
    if (resto > 500) {
        return (miles + 1) * 1000;
    } else {
        return miles * 1000;
    }
};

// Función para formatear precio (GLOBAL)
const formatPrice = price => {
    let valor = parseFloat(price);
    console.log("🔍 DEBUG formatPrice - Input:", price);
    console.log("🔍 DEBUG formatPrice - parseFloat:", valor);
    
    // 🔥 MANEJAR VALORES NaN O INVÁLIDOS
    if (isNaN(valor) || !isFinite(valor)) {
        console.warn("🔍 DEBUG formatPrice - Valor inválido, usando 0");
        valor = 0;
    }
    
    // 🔥 DIVIDIR POR 1000 PRIMERO, LUEGO REDONDEAR A MILES
    const valorEnMiles = valor / 1000;
    const valorRedondeado = redondearAMiles(valorEnMiles * 1000) / 1000;
    const formateado = valorRedondeado.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    const resultado = '$' + formateado + '.000';
    
    console.log("🔍 DEBUG formatPrice - Valor redondeado:", valorRedondeado);
    console.log("🔍 DEBUG formatPrice - Formateado manual:", formateado);
    console.log("🔍 DEBUG formatPrice - Resultado:", resultado);
    return resultado;
};

// Función para cargar productos (movida desde el código original)
function cargarProductos() {
    console.log("🔍 DEBUG productos.js - Archivo cargado correctamente");
    let products = [];

        // DETECTAR SI VIENE DE FAVORITOS O DEL CARRITO NORMAL
        const origenCompra = sessionStorage.getItem('origen_compra');
        console.log('🔍 Verificando origen de compra:', origenCompra ?? 'NULL (carrito por defecto)');
        
        if (origenCompra === 'favoritos') {
            // CARGAR DESDE FAVORITOS (localStorage)
            console.warn('⚠️ DETECTADO: origen_compra = favoritos (esto NO debería pasar en informacion-carrito)');
            console.log('⭐ Cargando productos desde FAVORITOS');
            cargarDesdeFavoritos(products, formatPrice, redondearAMiles);
        } else {
            // CARGAR DESDE CARRITO NORMAL (BD)
            console.log("✅ Cargando productos desde CARRITO NORMAL (BD)");
            cargarDesdeCarrito(products, formatPrice, redondearAMiles);
        }
}

/**
 * Cargar productos desde FAVORITOS (localStorage)
 */
async function cargarDesdeFavoritos(products, formatPrice, redondearAMiles) {
    const idsRelojes = JSON.parse(sessionStorage.getItem('ids_relojes_compra') || '[]');
    console.log('⭐ IDs de relojes favoritos:', idsRelojes);
    
    if (idsRelojes.length === 0) {
        console.warn('⚠️ No hay favoritos para cargar');
        return;
    }
    
    // Cargar cada reloj individualmente
    const productosPromises = idsRelojes.map(idReloj => 
        fetch(`http://127.0.0.1/finoso/informacion/php/obtener_reloj.php?id=${idReloj}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.reloj) {
                    // Adaptar formato para que sea compatible con carrito
                    const reloj = data.reloj;
                    return {
                        id_reloj: reloj.id_reloj,
                        nombre: reloj.nombre,
                        img: reloj.img_principal, // ⚠️ Favoritos usa img_principal
                        precio_final: reloj.precio,
                        precio: reloj.precio
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
    
    console.log('⭐ Productos cargados desde favoritos:', productosValidos);
    
    // Renderizar productos
    renderizarProductos(productosValidos, formatPrice, redondearAMiles);
}

/**
 * Cargar productos desde CARRITO NORMAL (BD)
 */
function cargarDesdeCarrito(products, formatPrice, redondearAMiles) {
        console.log("🔍 DEBUG productos.js - Iniciando fetch de carrito");
        fetch('http://127.0.0.1/finoso/php/mostrar_carrito.php')
            .then(res => {
                console.log("🔍 DEBUG productos.js - Respuesta recibida:", res);
                return res.json().then(data => {
                    console.log("🔍 DEBUG productos.js - Datos parseados:", data);
                    return data;
                });
            })
            .then(data => {
                console.log("🔍 DEBUG productos.js - Datos recibidos:", data);
                // El archivo mostrar_carrito.php devuelve {success: true, relojes: [...], total: ...}
                if (data && data.success && data.relojes && Array.isArray(data.relojes)) {
                    products = data.relojes;
                    console.log("🔍 DEBUG productos.js - Productos extraídos:", products);
                } else {
                    console.error("🔍 DEBUG productos.js - Formato de datos incorrecto:", data);
                    products = [];
                }

                if (products.length === 0) {
                    console.warn('No se encontraron productos en la respuesta');
                }

                // Renderizar productos
                renderizarProductos(products, formatPrice, redondearAMiles);
                
            })
            .catch(err => {
                console.error('🔍 DEBUG productos.js - Error al obtener el carrito:', err);
                console.error('🔍 DEBUG productos.js - Error details:', err.message);
                console.error('🔍 DEBUG productos.js - Error stack:', err.stack);
            });
}

/**
 * FUNCIÓN COMPARTIDA: Renderizar productos (usado por carrito Y favoritos)
 */
function renderizarProductos(products, formatPrice, redondearAMiles) {
    console.log("🎨 Renderizando productos...", products);
    
    // ⭐ IMPORTANTE: Hacer products accesible globalmente ANTES de todo
    window.currentProducts = products;
    
    // Generar partículas una sola vez
    generateParticles();
    
    if (products.length > 0) {
        updateProduct();
    }

    const contenedor = document.querySelector('.contenedor-info-relojes-carrito');
    const totalContainer = document.querySelector('.total-carrito h3');
    const precioTotal = document.getElementById('precio-total');

    contenedor.innerHTML = '';

    console.log("🔍 DEBUG - Iniciando procesamiento de productos");
    
    if (!Array.isArray(products)) {
        console.error("🔍 DEBUG - products no es un array:", products);
        return;
    }
    
    products.forEach((reloj, index) => {
        console.log("🔍 DEBUG - Procesando producto " + (index + 1) + ":", reloj);
        
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
    
    console.log("🔍 DEBUG - Procesamiento de productos completado");

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

    console.log("✅ Productos renderizados. Total:", totalFormateado);
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
            console.log("🔄 updateProduct() llamado");
            const products = window.currentProducts || [];
            console.log("📦 Productos disponibles:", products);
            console.log("📍 Índice actual:", currentIndex);
            const product = products[currentIndex];
            console.log("🎯 Producto actual:", product);

            if (!product) {
                console.warn("⚠️ No hay producto para mostrar");
                return;
            }

            try {
                // Usar nombres de campos correctos y rutas completas
                const imgElement = document.getElementById('img-lupa');
                const nameElement = document.getElementById('product-name');
                const priceElement = document.getElementById('current-price');
                const originalPriceElement = document.getElementById('original-price');
                const descripcionElement = document.getElementById('product-description');

                if (!imgElement || !nameElement || !priceElement || !originalPriceElement || !descripcionElement) {
                    console.error("❌ No se encontraron todos los elementos del DOM");
                    console.error("img-lupa:", imgElement);
                    console.error("product-name:", nameElement);
                    console.error("current-price:", priceElement);
                    console.error("original-price:", originalPriceElement);
                    console.error("product-description:", descripcionElement);
                    return;
                }

                imgElement.src = `http://127.0.0.1/finoso/${product.img}`;
                nameElement.textContent = product.nombre;
                console.log("✅ Imagen y nombre actualizados");

                // NO multiplicar por 1000, los precios ya están correctos
                priceElement.textContent = formatPrice(product.precio_final);
                console.log("✅ Precio actualizado:", formatPrice(product.precio_final));
                
                if (product.precio && product.precio !== product.precio_final) {
                    originalPriceElement.textContent = formatPrice(product.precio);
                    originalPriceElement.style.display = 'block';
                } else {
                    originalPriceElement.style.display = 'none';
                }

                // Mostrar descripción
                if (product.descripcion) {
                    descripcionElement.innerHTML = product.descripcion;
                } else {
                    descripcionElement.innerHTML = 'Sin descripción disponible.';
                }
                console.log("✅ Descripción actualizada");
            } catch (error) {
                console.error("❌ Error en updateProduct():", error);
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