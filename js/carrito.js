document.addEventListener('DOMContentLoaded', () => {
    const iconoCarrito = document.getElementById('iconoCarrito');
    const cuadroCarrito = document.getElementById('cuadroCarrito');
    const cerrarCarrito = document.getElementById('cerrarCarrito');
    const contadorCarrito = document.getElementById('contadorCarrito');

    // Abrir modal
    iconoCarrito.addEventListener('click', () => {
        cuadroCarrito.style.display = 'grid';
    });

    // Cerrar con botón
    cerrarCarrito.addEventListener('click', () => {
        cuadroCarrito.style.display = 'none';
    });

    // Cerrar al hacer clic fuera del modal
    document.addEventListener('click', (e) => {
        const modalVisible = getComputedStyle(cuadroCarrito).display === 'grid';
        const esClickDentroModal = e.target.closest('.cuadro-carrito');

        if (modalVisible && !esClickDentroModal && !e.target.closest('#iconoCarrito')) {
            cuadroCarrito.style.display = 'none';
        }
    });

    // Función para actualizar contador
    function actualizarContadorCarrito() {
        const productos = document.querySelectorAll('.cuadro-info-reloj-carrito');
        const cantidad = productos.length;

        if (cantidad > 0) {
            contadorCarrito.textContent = cantidad;
            contadorCarrito.style.display = 'inline-block';
        } else {
            contadorCarrito.style.display = 'none';
        }
    }

    // Llamamos la función al cargar
    actualizarContadorCarrito();
});