document.addEventListener('DOMContentLoaded', () => {
    const contenedor = document.querySelector('.img-informacion');
    const image = contenedor.querySelector('#img-lupa');
    const botonLupa = document.querySelector('.cuadrolupa');
    const contenedorInfoPago = document.querySelector('.contenedor-informacion-pago');

    const lupaSize = 120;
    const zoom = 2;
    let lupaActiva = false;

    // Crear la lupa
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

    // Activar/desactivar lupa
    botonLupa.addEventListener('click', () => {
        lupaActiva = !lupaActiva;
        botonLupa.classList.toggle('activa', lupaActiva);
        lupa.style.display = lupaActiva ? 'block' : 'none';
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
        lupa.style.left = `${x}px`;
        lupa.style.top = `${y}px`;

        const bgX = -(x * zoom - lupaSize / 2);
        const bgY = -(y * zoom - lupaSize / 2);

        lupa.style.backgroundImage = `url('${image.src}')`;
        lupa.style.backgroundSize = `${rect.width * zoom}px ${rect.height * zoom}px`;
        lupa.style.backgroundPosition = `${bgX}px ${bgY}px`;
    });

    // Ocultar lupa al salir del contenedor
    contenedor.addEventListener('mouseleave', () => {
        lupa.style.display = 'none';
    });

    // 📌 Controlar el límite de movimiento del botón .cuadrolupa
    const ajustarPosicionLupa = () => {
        const contenedorTop = contenedorInfoPago.offsetTop;
        const contenedorBottom = contenedorTop + contenedorInfoPago.offsetHeight;
        const scrollY = window.scrollY;
        const botonHeight = botonLupa.offsetHeight;
        const fixedTop = window.innerHeight * 0.9 - botonHeight; // 90vh

        if (scrollY + fixedTop + botonHeight >= contenedorBottom) {
            // Pasa el límite: cambia a absolute dentro del contenedor
            botonLupa.style.position = 'absolute';
            botonLupa.style.top = `${contenedorInfoPago.offsetHeight - botonHeight}px`;
            botonLupa.style.left = '1%';
        } else {
            // Sigue siendo fixed
            botonLupa.style.position = 'fixed';
            botonLupa.style.top = '90vh';
            botonLupa.style.left = '1%';
        }
    };

    // Ejecutar en scroll y al cargar
    window.addEventListener('scroll', ajustarPosicionLupa);
    ajustarPosicionLupa();
});
