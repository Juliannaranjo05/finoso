document.addEventListener('DOMContentLoaded', () => {
    const contenedor = document.querySelector('.contenedor-img');
    const image = contenedor.querySelector('img');
    const botonLupa = document.querySelector('.cuadrolupa');
    const lupaSize = 120;
    const zoom = 2;
    let lupaActiva = false;

    // Crear la lupa
    const lupa = document.createElement('div');
    lupa.classList.add('lupa');
    lupa.style.display = 'none';
    contenedor.appendChild(lupa);

    // Activar/desactivar lupa con el botón
    botonLupa.addEventListener('click', () => {
        lupaActiva = !lupaActiva;
        botonLupa.classList.toggle('activa', lupaActiva);
        if (!lupaActiva) {
            lupa.style.display = 'none';
        }
    });

    // Mover la lupa
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
        lupa.style.left = `${x - lupaSize / 2}px`;
        lupa.style.top = `${y - lupaSize / 2}px`;

        const bgX = -(x * zoom - lupaSize / 2);
        const bgY = -(y * zoom - lupaSize / 2);

        lupa.style.backgroundImage = `url('${image.src}')`;
        lupa.style.backgroundSize = `${image.width * zoom}px ${image.height * zoom}px`;
        lupa.style.backgroundPosition = `${bgX}px ${bgY}px`;
    });

    // Ocultar lupa al salir del contenedor
    contenedor.addEventListener('mouseleave', () => {
        lupa.style.display = 'none';
    });
});
