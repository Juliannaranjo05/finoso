// Base de datos de productos
        let products = [
            {
                id: 1,
                name: "Rchrd Mll Calavera Circones Dorado-Negro",
                currentPrice: "$130.000",
                originalPrice: "$145.000",
                description: "Calavera central con incrustación de circones facetados. Bisel de acero pavonado y corona mecanizada con acabado satinado. Un diseño único que combina elegancia y rebeldía.",
                image: "../img/IMG_0168-Photoroom.png"
            },
            {
                id: 2,
                name: "Rchrd Mll Sport Edition",
                currentPrice: "$195.000",
                originalPrice: "$220.000",
                description: "Edición deportiva con resistencia al agua IPX8, cronómetro integrado y correa de silicona de alta calidad. Diseñado para aventureros y atletas de élite.",
                image: "../img/ChatGPT_Image_24_abr_2025__19_42_00-removebg-preview.png"
            },
            {
                id: 3,
                name: "Rchrd Mll Classic Gold",
                currentPrice: "$250.000",
                originalPrice: "$300.000",
                description: "Elegante diseño clásico con caja de oro rosa y esfera de nácar. Perfecto para ocasiones especiales y ejecutivos distinguidos.",
                image: "../img/IMG_0163-Photoroom.png"
            },
            {
                id: 3,
                name: "Rchrd Mll Classic Gold",
                currentPrice: "$250.000",
                originalPrice: "$300.000",
                description: "Elegante diseño clásico con caja de oro rosa y esfera de nácar. Perfecto para ocasiones especiales y ejecutivos distinguidos.",
                image: "../img/IMG_0163-Photoroom.png"
            }
        ];

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
            
            document.getElementById('img-lupa').src = product.image;
            document.getElementById('product-name').textContent = product.name;
            document.getElementById('current-price').textContent = product.currentPrice;
            document.getElementById('original-price').textContent = product.originalPrice;
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