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
            if (!res.ok) throw new Error('No se pudo cargar el reloj');
            return res.json();
        })
        .then(data => {
            if (data.error) {
                document.querySelector('.contenedor-general-informacion').innerHTML = `<p>${data.error}</p>`;
                return;
            }

            const precioOriginal = Number(data.precio);
            const descuento = Number(data.descuento);
            const precioConDescuento = Math.round(precioOriginal - (precioOriginal * descuento));

            // Construcción dinámica de miniaturas
            const thumbnails = [data.img, data.img1, data.img2, data.img3]
                .filter(src => src) // Evita valores vacíos o null
                .map((src, index) => `
                    <img class="thumbnail${index === 0 ? ' active' : ''}" 
                         src="../${src}" 
                         alt="Imagen ${index + 1}" 
                         data-full="../${src}">
                `).join('');

            const html = `
                <div class="img-informacion">
                    <img id="img-lupa" src="../${data.img}" alt="${data.nombre}">
                </div>
                <div class="contenedor-img">
                    ${thumbnails}
                </div>
                <div class="contenedor-informacion">
                    <div class="nombre-informacion">
                        <h2>${data.nombre}</h2>
                    </div>
                    <div class="contenedor-precio-total">
                        <div class="precio">
                            <div class="precio-descuento">
                                ${descuento > 0
                                    ? `<p>$${precioConDescuento.toLocaleString('es-CO')}.000</p><h4>$${precioOriginal.toLocaleString('es-CO')}.000</h4>`
                                    : `<p>$${precioOriginal.toLocaleString('es-CO')}.000</p>`}
                            </div>
                        </div>
                    </div>
                    <div class="descripcion-informacion">
                        <h1>Detalles:</h1>
                        <h2>${data.descripcion || 'No hay detalles disponibles.'}</h2>
                    </div>
                </div>
            `;

            document.querySelector('.contenedor-general-informacion').innerHTML = html;
            inicializarLupa();

            // Activar funcionalidad para cambiar imagen principal al hacer clic en miniaturas
            document.querySelectorAll('.thumbnail').forEach(thumbnail => {
                thumbnail.addEventListener('click', () => {
                    document.querySelector('#img-lupa').src = thumbnail.dataset.full;

                    document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                    thumbnail.classList.add('active');
                });
            });
        })
        .catch(error => {
            console.error('Error al cargar datos del reloj:', error);
            document.querySelector('.contenedor-general-informacion').innerHTML = "<p>Error al cargar la información del reloj.</p>";
        });
});