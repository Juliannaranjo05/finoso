// Base de datos de productos
        let products = [];

        fetch('http://127.0.0.1/finoso/informacion-carrito/php/obtener_carrito.php')
            .then(res => res.json())
            .then(data => {
                products = data;

                if (products.length === 0) {
                    console.warn('No se encontraron productos en la respuesta');
                }

                if (products.length > 0) {
                    updateProduct();
                }

                const contenedor = document.querySelector('.contenedor-info-relojes-carrito');
                const totalContainer = document.querySelector('.total-carrito h3');

                contenedor.innerHTML = '';

                data.forEach(reloj => {
                    const precioFormateado = `$${Number(reloj.currentPrice).toLocaleString('es-CO')}.000`;
                    const precioOriginalFormateado = reloj.originalPrice
                        ? `$${Number(reloj.originalPrice).toLocaleString('es-CO')}.000`
                        : '';

                    const precioOriginalHTML = reloj.originalPrice
                        ? `<h4 id="original-price">${precioOriginalFormateado}</h4>`
                        : '';

                    contenedor.innerHTML += `
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
                });

                // Calcular total
                const total = data.reduce((acc, reloj) => acc + Number(reloj.currentPrice), 0);
                totalContainer.textContent = `$${total.toLocaleString('es-CO')}.000`;
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

            // Verifica si el producto existe antes de acceder a sus propiedades
            if (!product) {
                return; // Termina la función si no hay un producto válido
            }

            document.getElementById('img-lupa').src = product.image;
            document.getElementById('product-name').textContent = product.name;
            document.getElementById('current-price').textContent = product.currentPrice;

            // Si hay descuento, muestra el precio original
            if (product.originalPrice) {
                document.getElementById('original-price').textContent = product.originalPrice;
                document.getElementById('original-price').style.display = 'block';
            } else {
                document.getElementById('original-price').style.display = 'none';
            }

            document.getElementById('product-description').textContent = product.description;
            document.getElementById('currentProduct').textContent = currentIndex + 1;
            document.getElementById('totalProducts').textContent = products.length;

            document.getElementById('prevBtn').disabled = currentIndex === 0;
            document.getElementById('nextBtn').disabled = currentIndex === products.length - 1;
            
            // Actualizar miniaturas
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