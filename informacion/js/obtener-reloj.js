// Función para redondear hacia arriba a miles
function redondearAMiles(precio) {
    const miles = Math.floor(precio / 1000);
    const resto = precio % 1000;
    if (resto > 500) {
        return (miles + 1) * 1000;
    } else {
        return miles * 1000;
    }
}


document.addEventListener('DOMContentLoaded', () => {
    function inicializarLupa() {
        const contenedor = document.querySelector('.img-informacion');
        const image = contenedor.querySelector('#img-lupa');
        const botonLupa = document.querySelector('.cuadrolupa');
        const contenedorInfoPago = document.querySelector('.contenedor-informacion-pago');

        if (!contenedor || !image || !botonLupa || !contenedorInfoPago) {
            console.warn('Elementos necesarios para la lupa no encontrados.');
            return;
        }

        const lupaSize = 120;
        const zoom = 2;
        let lupaActiva = false;

        const lupa = document.createElement('div');
        lupa.classList.add('lupa');
        lupa.style.cssText = `
            position: absolute;
            width: ${lupaSize}px;
            height: ${lupaSize}px;
            border: 2px solid #FFCF66;
            border-radius: 50%;
            pointer-events: none;
            display: none;
            overflow: hidden;
            background-repeat: no-repeat;
            transform: translate(50%, 50%);
            box-shadow: 0px 0px 15px #FFCF66;
            z-index: 999;
        `;
        contenedor.appendChild(lupa);

        botonLupa.addEventListener('click', () => {
            lupaActiva = !lupaActiva;
            botonLupa.classList.toggle('activa', lupaActiva);
            lupa.style.display = lupaActiva ? 'block' : 'none';
        });

        contenedor.addEventListener('mousemove', (e) => {
            if (!lupaActiva) return;

            const rect = image.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            if (x < 0 || y < 0 || x > rect.width || y > rect.height) {
                lupa.style.display = 'none';
                return;
            }

            lupa.style.display = 'block';
            lupa.style.left = `${x}px`;
            lupa.style.top = `${y}px`;

            const bgX = -(x * zoom - lupaSize / 2);
            const bgY = -(y * zoom - lupaSize / 2);

            lupa.style.backgroundImage = `url('${image.src}')`;
            lupa.style.backgroundSize = `${rect.width * zoom}px ${rect.height * zoom}px`;
            lupa.style.backgroundPosition = `${bgX}px ${bgY}px`;
        });

        contenedor.addEventListener('mouseleave', () => {
            lupa.style.display = 'none';
        });

        const ajustarPosicionLupa = () => {
            const contenedorTop = contenedorInfoPago.offsetTop;
            const contenedorBottom = contenedorTop + contenedorInfoPago.offsetHeight;
            const scrollY = window.scrollY;
            const botonHeight = botonLupa.offsetHeight;
            const fixedTop = window.innerHeight * 0.9 - botonHeight;

            if (scrollY + fixedTop + botonHeight >= contenedorBottom) {
                botonLupa.style.position = 'absolute';
                botonLupa.style.top = `${contenedorInfoPago.offsetHeight - botonHeight}px`;
                botonLupa.style.left = '1%';
            } else {
                botonLupa.style.position = 'fixed';
                botonLupa.style.top = '90vh';
                botonLupa.style.left = '1%';
            }
        };

        window.addEventListener('scroll', ajustarPosicionLupa);
        ajustarPosicionLupa();
    }
    const params = new URLSearchParams(window.location.search);
    const relojId = params.get('id_reloj');

    if (!relojId) {
        document.querySelector('.contenedor-general-informacion').innerHTML = "<p>No se especificó ningún reloj.</p>";
        return;
    }

    fetch(`http://127.0.0.1/finoso/informacion/php/obtener_reloj.php?id_reloj=${relojId}`)
        .then(res => {
            if (!res.ok) {
                throw new Error(`Error HTTP: ${res.status} - ${res.statusText}`);
            }
            return res.text().then(text => {
                if (!text.trim()) {
                    throw new Error('Respuesta vacía del servidor');
                }
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Error al parsear JSON:', text);
                    throw new Error('Respuesta no válida del servidor');
                }
            });
        })
        .then(data => {
            if (data.error) {
                document.querySelector('.contenedor-general-informacion').innerHTML = `<p>${data.error}</p>`;
                return;
            }

            // Verificar si el reloj está vendido
            const esVendido = data.vendido == 1;
            
            const precioOriginal = Number(data.precio);
            const descuento = Number(data.descuento);
            
            
            const precioConDescuento = redondearAMiles(precioOriginal - (precioOriginal * descuento));

            // Construcción dinámica de miniaturas con las 3 imágenes
            const imagenes = [
                { src: data.img, alt: 'Vista Frontal', tipo: 'frontal' },
                { src: data.img_lateral, alt: 'Vista Lateral', tipo: 'lateral' },
                { src: data.img_detalle, alt: 'Vista Detalle', tipo: 'detalle' }
            ].filter(img => img.src && img.src.trim() !== ''); // Solo imágenes que existen
            
            const thumbnails = imagenes.map((img, index) => `
                <img class="thumbnail${index === 0 ? ' active' : ''}" 
                     src="../${img.src}" 
                     alt="${img.alt}" 
                     data-full="../${img.src}"
                     title="${img.alt}">
            `).join('');

            const html = `
                <div class="img-informacion">
                    <img id="img-lupa" src="../${data.img}" alt="${data.nombre}">
                </div>
                <div class="contenedor-img">
                    ${thumbnails}
                </div>
                ${imagenes.length > 1 ? `
                    <div class="controles-imagenes">
                        <button class="btn-navegacion" id="btn-anterior">‹</button>
                        <span class="contador-imagenes">
                            <span id="imagen-actual">1</span> / <span id="total-imagenes">${imagenes.length}</span>
                        </span>
                        <button class="btn-navegacion" id="btn-siguiente">›</button>
                    </div>
                ` : ''}
                <div class="contenedor-informacion">
                    <div class="nombre-informacion">
                        <h2>${data.nombre}</h2>
                    </div>
                    <div class="contenedor-precio-total">
                        <div class="precio">
                            ${esVendido 
                                ? `<div class="precio-vendido">
                                    <div class="badge-vendido-grande">VENDIDO</div>
                                    <p class="precio-original">$${precioOriginal.toLocaleString('es-CO')}</p>
                                </div>`
                                : `<div class="precio-descuento">
                                    ${descuento > 0
                                        ? `<p class="precio-descuentos">$${precioConDescuento.toLocaleString('es-CO')}</p><h4 class="precio-normal">$${precioOriginal.toLocaleString('es-CO')}</h4>`
                                        : `<p class="precio-normal">$${precioOriginal.toLocaleString('es-CO')}</p>`}
                                </div>`
                            }
                        </div>
                    </div>
                    <div class="descripcion-informacion">
                        <h1>Descripción:</h1>
                        <h2>${data.descripcion || 'No hay descripción disponible.'}</h2>
                    </div>
                    ${(data.eslabones || data.tipo_bisel || data.movimiento || data.pulsera || data.peso || data.resistencia_agua) ? `
                        <div class="especificaciones-informacion">
                            <h1>Especificaciones:</h1>
                            <div class="especificaciones-lista">
                                ${data.eslabones ? `<div class="especificacion-item">• Cantidad de Eslabones: ${data.eslabones}</div>` : ''}
                                ${data.tipo_bisel ? `<div class="especificacion-item">• Tipo de Bisel: ${data.tipo_bisel === 'estatico' ? 'Estático' : data.tipo_bisel === 'giratorio' ? 'Giratorio' : data.tipo_bisel}</div>` : ''}
                                ${data.movimiento ? `<div class="especificacion-item">• Tipo deMovimiento: ${data.movimiento}</div>` : ''}
                                ${data.pulsera ? `<div class="especificacion-item">• Material de la Pulsera: ${data.pulsera}</div>` : ''}
                                ${data.peso ? `<div class="especificacion-item">• Peso del Reloj: ${data.peso} g </div>` : ''}
                                ${data.resistencia_agua ? `<div class="especificacion-item">• Resistencia al Agua: ${data.resistencia_agua}</div>` : ''}
                            </div>
                        </div>
                    ` : ''}
                    <!-- Sección de comentarios existentes -->
                    <div class="comentarios-existente" id="comentarios-existente">
                        <div class="cargando-comentarios">
                            <i class="fas fa-spinner fa-spin"></i>
                            Cargando comentarios...
                        </div>
                    </div>
                </div>
            `;

            document.querySelector('.contenedor-general-informacion').innerHTML = html;
            
            // Manejar relojes vendidos
            if (esVendido) {
                const formContainer = document.querySelector('.form-container');
                if (formContainer) {
                    formContainer.innerHTML = `
                        <div class="mensaje-vendido">
                            <h2>Este reloj ya fue vendido</h2>
                            <p>Lamentamos informarte que este reloj ya no está disponible para la venta.</p>
                        </div>
                    `;
                }
            }
            
            inicializarLupa();

            // Variables para navegación
            let imagenActual = 0;
            const totalImagenes = imagenes.length;
            
            // Función para cambiar imagen
            function cambiarImagen(indice) {
                if (indice < 0 || indice >= totalImagenes) return;
                
                imagenActual = indice;
                const imagen = imagenes[imagenActual];
                const imgLupa = document.querySelector('#img-lupa');
                
                imgLupa.src = `../${imagen.src}`;
                imgLupa.alt = imagen.alt;
                
                // Actualizar thumbnails
                document.querySelectorAll('.thumbnail').forEach((thumb, index) => {
                    thumb.classList.toggle('active', index === imagenActual);
                });
                
                // Actualizar contador
                if (document.getElementById('imagen-actual')) {
                    document.getElementById('imagen-actual').textContent = imagenActual + 1;
                }
            }
            
            // Activar funcionalidad para cambiar imagen principal al hacer clic en miniaturas
            document.querySelectorAll('.thumbnail').forEach((thumbnail, index) => {
                thumbnail.addEventListener('click', () => {
                    cambiarImagen(index);
                });
            });
            
            // Controles de navegación
            if (totalImagenes > 1) {
                const btnAnterior = document.getElementById('btn-anterior');
                const btnSiguiente = document.getElementById('btn-siguiente');
                
                if (btnAnterior) {
                    btnAnterior.addEventListener('click', () => {
                        const nuevaImagen = (imagenActual - 1 + totalImagenes) % totalImagenes;
                        cambiarImagen(nuevaImagen);
                    });
                }
                
                if (btnSiguiente) {
                    btnSiguiente.addEventListener('click', () => {
                        const nuevaImagen = (imagenActual + 1) % totalImagenes;
                        cambiarImagen(nuevaImagen);
                    });
                }
            }
            
            // ✅ CARGAR DESCUENTO APLICADO DESPUÉS DE RENDERIZAR EL RELOJ
            // Esperar un momento para que el DOM se actualice completamente
            setTimeout(() => {
                if (typeof cargarCodigoAplicado === 'function') {
                    console.log('🔄 Verificando descuento aplicado...');
                    cargarCodigoAplicado();
                }
            }, 100);
        })
        .catch(error => {
            console.error('Error al cargar datos del reloj:', error);
            document.querySelector('.contenedor-general-informacion').innerHTML = "<p>Error al cargar la información del reloj.</p>";
        });

        fetch(`http://127.0.0.1/finoso/informacion/php/obtener_relacionados.php?id_reloj=${relojId}`)
            .then(res => {
                if (!res.ok) {
                    throw new Error(`Error HTTP: ${res.status} - ${res.statusText}`);
                }
                return res.text().then(text => {
                    if (!text.trim()) {
                        return []; // Retorna array vacío si no hay datos
                    }
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Error al parsear JSON de relacionados:', text);
                        return []; // Retorna array vacío en caso de error
                    }
                });
            })
            .then(relacionados => {
                if (!Array.isArray(relacionados) || relacionados.length === 0) {
                    document.querySelector('.contenedor-general-cards').innerHTML = "<p></p>";
                    document.getElementById('productoRelacionados').style.display = 'none';
                    return;

                    
                }

                const relacionadosHTML = relacionados.map(prod => {
                    const precioOriginal = Number(prod.precio);
                    const descuento = Number(prod.descuento);
                    
                    // Aplicar redondeo a miles al precio original
                    const precioOriginalRedondeado = redondearAMiles(precioOriginal);
                    const precioConDescuento = redondearAMiles(precioOriginalRedondeado - (precioOriginalRedondeado * descuento));
                    
                    const disponible = Number(prod.disponible) === 1;

                    // Precio o mensaje de agotado
                    const precioHTML = disponible
                        ? (descuento > 0
                            ? `$${precioConDescuento.toLocaleString('es-CO')} <span class="tachado">$${precioOriginalRedondeado.toLocaleString('es-CO')}</span>`
                            : `$${precioOriginalRedondeado.toLocaleString('es-CO')}`)
                        : `<span class="tachado">AGOTADO</span>`;

                    // Botón con estilos si está deshabilitado
                    const botonExplorar = `
                        <button class="btn-whatsapp" ${!disponible ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : `onclick="window.location.href='../informacion/informacion.html?id_reloj=${prod.id_reloj}'"`}>
                            Explorar modelo
                        </button>
                    `;

                    return `
                        <div class="contenedor-card">
                            <div class="cuadro-card">
                                <img src="../${prod.img}" class="zoom-img" alt="${prod.nombre}">
                                <div class="texto-card">
                                    <h3>${prod.nombre}</h3>
                                    <p>${precioHTML}</p>
                                    <div class="boton-wh">
                                        ${botonExplorar}
                                        <svg class="ornamento" width="60" height="10" viewBox="0 0 60 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <line x1="0" y1="5" x2="20" y2="5" stroke="#FFCF66" stroke-width="1" opacity="0.3"/>
                                            <polygon points="27,5 30,0 33,5 30,10" fill="#FFCF66"/>
                                            <polygon points="20,5 22,2.5 24,5 22,7.5" fill="#FFCF66"/>
                                            <polygon points="36,5 38,2.5 40,5 38,7.5" fill="#FFCF66"/>
                                            <line x1="40" y1="5" x2="60" y2="5" stroke="#FFCF66" stroke-width="1" opacity="0.3"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

                document.querySelector('.contenedor-general-cards').innerHTML = relacionadosHTML;
            })
            .catch(err => {
                console.error("Error al cargar productos relacionados:", err);
                document.querySelector('.contenedor-general-cards').innerHTML = "<p>Error al mostrar productos relacionados.</p>";
        });
});