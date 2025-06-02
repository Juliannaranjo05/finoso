class ImageSelector {
            constructor() {
                this.mainImage = document.getElementById('img-lupa');
                this.thumbnails = document.querySelectorAll('.thumbnail');
                this.init();
            }

            init() {
                this.thumbnails.forEach((thumbnail, index) => {
                    thumbnail.addEventListener('click', (e) => {
                        this.selectImage(e.target);
                    });

                    // Agregar navegación con teclado
                    thumbnail.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            this.selectImage(e.target);
                        }
                    });

                    // Hacer los thumbnails accesibles
                    thumbnail.setAttribute('tabindex', '0');
                    thumbnail.setAttribute('role', 'button');
                    thumbnail.setAttribute('aria-label', `Seleccionar imagen ${index + 1}`);
                });

                // Precargar imágenes para mejor rendimiento
                this.preloadImages();
            }

            selectImage(selectedThumbnail) {
                // Efecto de transición suave
                this.mainImage.classList.add('fade-transition');
                
                setTimeout(() => {
                    // Cambiar la imagen principal
                    const newImageSrc = selectedThumbnail.getAttribute('data-full');
                    this.mainImage.src = newImageSrc;
                    this.mainImage.alt = selectedThumbnail.alt;

                    // Actualizar estados activos
                    this.updateActiveState(selectedThumbnail);

                    // Remover la transición una vez cargada la imagen
                    this.mainImage.onload = () => {
                        this.mainImage.classList.remove('fade-transition');
                        this.mainImage.classList.add('loaded');
                    };
                }, 150);
            }

            updateActiveState(selectedThumbnail) {
                // Remover clase active de todos los thumbnails
                this.thumbnails.forEach(thumb => {
                    thumb.classList.remove('active');
                    thumb.setAttribute('aria-selected', 'false');
                });

                // Agregar clase active al seleccionado
                selectedThumbnail.classList.add('active');
                selectedThumbnail.setAttribute('aria-selected', 'true');
            }

            preloadImages() {
                this.thumbnails.forEach(thumbnail => {
                    const img = new Image();
                    img.src = thumbnail.getAttribute('data-full');
                });
            }
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', () => {
            new ImageSelector();
        });

        // Función adicional para cambiar imágenes programáticamente
        function changeToImage(index) {
            const thumbnails = document.querySelectorAll('.thumbnail');
            if (thumbnails[index]) {
                thumbnails[index].click();
            }
        }