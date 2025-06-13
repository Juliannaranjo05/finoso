// Base de datos de productos
        let products = [];

        const formatPrice = price => {
        let valor = parseFloat(price);
         valor = Math.round(valor / 1000) * 1000; // Redondea al múltiplo de mil más cercano
        return '$' + valor.toLocaleString('es-CO');
        };

        fetch('http://127.0.0.1/finoso/informacion-carrito/php/obtener_carrito.php')
            .then(res => {
                console.log('Respuesta HTTP recibida, estado:', res.status);
                return res.json().then(data => {
                    console.log('Datos JSON parseados:', JSON.stringify(data, null, 2));
                    return data;
                });
            })
            .then(data => {
                console.log('Datos recibidos en el segundo then:', data);
                
                products = data;
                console.log('Products asignado:', products);

                if (products.length === 0) {
                    console.warn('No se encontraron productos en la respuesta');
                }

                if (products.length > 0) {
                    console.log('Llamando a updateProduct()');
                    updateProduct();
                }

                const contenedor = document.querySelector('.contenedor-info-relojes-carrito');
                const totalContainer = document.querySelector('.total-carrito h3');
                const precioTotal = document.getElementById('precio-total');

                console.log('Contenedor seleccionado:', contenedor);
                console.log('Total container seleccionado:', totalContainer);
                console.log('Precio total seleccionado:', precioTotal);

                contenedor.innerHTML = '';
                console.log('Contenedor vaciado');

                data.forEach((reloj, index) => {
                    console.log(`Procesando producto ${index + 1}:`, reloj);
                    
                    // Debug de precios
                    console.log(`Precio original (crudo) para ${reloj.name}:`, reloj.originalPrice);
                    console.log(`Precio actual (crudo) para ${reloj.name}:`, reloj.currentPrice);
                    
                    const precioFinal = parseFloat(reloj.currentPrice) * 1000;
                    const precioOriginal = reloj.originalPrice ? parseFloat(reloj.originalPrice) * 1000 : null;
                    
                    console.log(`Precio final calculado (x1000) para ${reloj.name}:`, precioFinal);
                    console.log(`Precio original calculado (x1000) para ${reloj.name}:`, precioOriginal);

                    const formatPrice = price => {
                    let valor = parseFloat(price) * 1000;
                    valor = Math.round(valor / 1000) * 1000; // Redondea al múltiplo de mil más cercano
                    return '$' + valor.toLocaleString('es-CO');
                    };

                    const precioFormateado = formatPrice(precioFinal); // NO multipliques por 1000 aquí
                    const precioOriginalFormateado = precioOriginal ? formatPrice(precioOriginal) : '';

                    console.log(`Precio formateado final para ${reloj.name}:`, precioFormateado);
                    console.log(`Precio original formateado para ${reloj.name}:`, precioOriginalFormateado);

                    const precioOriginalHTML = precioOriginal
                        ? `<h4 id="original-price">${precioOriginalFormateado}</h4>`
                        : '';

                    console.log(`HTML generado para precios originales:`, precioOriginalHTML);

                    const productoHTML = `
                        <div class="cuadro-info-reloj-carrito">
                            <div class="img-reloj-carrito">
                                <img src="${reloj.image}" alt="${reloj.name}">
                            </div>
                            <div class="nombre-precio-carrito">
                                <div class="nombre-carrito">
                                    <h2>${reloj.name}</h2>
                                </div>
                                <div class="precio-carrito">
                                    ${precioOriginalHTML}
                                    <h3>${precioFormateado}</h3>
                                </div>
                            </div>
                            <div class="boton-eliminar">
                                <button data-id="${reloj.id}">Eliminar</button>
                            </div>
                        </div>
                    `;

                    console.log(`HTML completo para ${reloj.name}:`, productoHTML);
                    
                    contenedor.innerHTML += productoHTML;
                });

                // Debug del cálculo del total
                console.log('Calculando total...');
                const totalSinFormato = data.reduce((acc, reloj) => {
                const precio = parseFloat(reloj.currentPrice) * 1000;
                return acc + precio;
                }, 0);

                // Redondea el total al múltiplo de mil más cercano
                const totalFormateado = formatPrice(totalSinFormato);

                // Mostrar total en dos lugares
                totalContainer.textContent = totalFormateado;
                if (precioTotal) {
                    precioTotal.textContent = totalFormateado;
                }
                
                console.log('Proceso completado. Verifica el DOM ahora.');
            })
            .catch(err => {
                console.error('Error al obtener el carrito:', err);
            });
        
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
            const product = products[currentIndex];

            if (!product) return;

            document.getElementById('img-lupa').src = product.image;
            document.getElementById('product-name').textContent = product.name;

            document.getElementById('current-price').textContent = formatPrice(product.currentPrice * 1000);
            if (product.originalPrice) {
                document.getElementById('original-price').textContent = formatPrice(product.originalPrice * 1000);
                document.getElementById('original-price').style.display = 'block';
            } else {
                document.getElementById('original-price').style.display = 'none';
            }

            document.getElementById('product-description').textContent = product.description;
            document.getElementById('currentProduct').textContent = currentIndex + 1;
            document.getElementById('totalProducts').textContent = products.length;

            document.getElementById('prevBtn').disabled = currentIndex === 0;
            document.getElementById('nextBtn').disabled = currentIndex === products.length - 1;

            updateThumbnails();
        }

        function updateThumbnails() {
            const thumbnailsContainer = document.getElementById('thumbnails');
            thumbnailsContainer.innerHTML = '';
            
            products.forEach((product, index) => {
                const thumbnail = document.createElement('img');
                thumbnail.className = `thumbnail ${index === currentIndex ? 'active' : ''}`;
                thumbnail.src = product.image;
                thumbnail.alt = product.name;
                thumbnail.onclick = () => goToProduct(index);
                thumbnailsContainer.appendChild(thumbnail);
            });
        }

        function changeProduct(direction) {
            const newIndex = currentIndex + direction;
            if (newIndex >= 0 && newIndex < products.length) {
                currentIndex = newIndex;
                updateProduct();
                createRippleEffect();
            }
        }

        function goToProduct(index) {
            if (index >= 0 && index < products.length && index !== currentIndex) {
                currentIndex = index;
                updateProduct();
                createRippleEffect();
            }
        }

        function buyProduct() {
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

        // Inicialización
        generateParticles();
        updateProduct();