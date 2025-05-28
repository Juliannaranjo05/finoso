function activarLupa() {    
    const contenedores = document.querySelectorAll('.img-carrito');
    const botonLupa = document.querySelector('.cuadrolupa'); // Asegúrate de que esto esté fuera del bucle
    const lupaSize = 120;
    const zoom = 2;
    let lupaActiva = false;

    // Crear la lupa una sola vez
    const lupa = document.createElement('div');
    lupa.classList.add('lupa');
    lupa.style.display = 'none';
    lupa.style.position = 'absolute';
    lupa.style.width = `${lupaSize}px`;
    lupa.style.height = `${lupaSize}px`;
    lupa.style.borderRadius = '50%';
    lupa.style.border = '2px solid #fff';
    lupa.style.overflow = 'hidden';
    lupa.style.pointerEvents = 'none';
    lupa.style.zIndex = '1000';
    document.body.appendChild(lupa);

    // Activar/desactivar lupa con el botón
    botonLupa.addEventListener('click', () => {
        lupaActiva = !lupaActiva;
        botonLupa.classList.toggle('activa', lupaActiva);
        if (!lupaActiva) {
            lupa.style.display = 'none';
        }
    });

    contenedores.forEach(contenedor => {
        const image = contenedor.querySelector('img');

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
            lupa.style.left = `${e.pageX - lupaSize / 2}px`;
            lupa.style.top = `${e.pageY - lupaSize / 2}px`;

            const bgX = -(x * zoom - lupaSize / 2);
            const bgY = -(y * zoom - lupaSize / 2);

            lupa.style.backgroundImage = `url('${image.src}')`;
            lupa.style.backgroundSize = `${image.width * zoom}px ${image.height * zoom}px`;
            lupa.style.backgroundPosition = `${bgX}px ${bgY}px`;
        });

        contenedor.addEventListener('mouseleave', () => {
            lupa.style.display = 'none';
        });
    });
};