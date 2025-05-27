document.addEventListener('DOMContentLoaded', function () {
    const iconoCarrito = document.getElementById('icono-carrito');
    const modalCarrito = document.getElementById('modal-carrito');
    const fondoModal = document.createElement('div');
    fondoModal.classList.add('fondo-modal');
    document.body.appendChild(fondoModal);

    console.log('DOM cargado');

    // Verificar sesión al cargar la página para mostrar el contador
    fetch('http://127.0.0.1/finisimo/php/verificar_sesion.php')
        .then(res => res.json())
        .then(data => {
            console.log('verificar_sesion.php ->', data);
            if (data.logged_in) {
                // Obtener los productos del carrito
                fetch('http://127.0.0.1/finisimo/php/mostrar_carrito.php')
                    .then(res => res.json())
                    .then(data => {
                        console.log('mostrar_carrito.php ->', data);
                        if (data.success && Array.isArray(data.relojes) && data.relojes.length > 0) {
                            const cantidad = data.relojes.length;

                            // Verifica si ya existe el contador y lo elimina
                            let existente = document.querySelector('.cantidad-carrito');
                            if (existente) existente.remove();

                            // Crea el contador
                            const cantidadDiv = document.createElement('div');
                            cantidadDiv.classList.add('cantidad-carrito');

                            const h2 = document.createElement('h2');
                            h2.textContent = cantidad;
                            cantidadDiv.appendChild(h2);

                            // Agregar dentro de .icono-carrito (no dentro del <svg>)
                            const contenedor = document.querySelector('.icono-carrito');
                            contenedor.appendChild(cantidadDiv);
                        }
                    })
                    .catch(err => console.error('Error al obtener el carrito:', err));
            }
        })
        .catch(err => console.error('Error al verificar sesión:', err));

    // Al hacer clic en el icono del carrito
    iconoCarrito.addEventListener('click', function (e) {
        e.stopPropagation();

        fetch('http://127.0.0.1/finisimo/php/verificar_sesion.php')
            .then(res => res.json())
            .then(data => {
                if (data.logged_in) {
                    modalCarrito.style.display = 'grid';
                    fondoModal.style.display = 'block';
                } else {
                    alert('Para usar el carrito debes iniciar sesión.');
                    window.location.href = 'http://127.0.0.1/finisimo/login/login.html';
                }
            })
            .catch(err => {
                console.error('Error verificando la sesión:', err);
                alert('Ocurrió un error. Intenta nuevamente.');
            });
    });

    document.addEventListener('click', function (e) {
        if (!modalCarrito.contains(e.target) && e.target !== iconoCarrito) {
            modalCarrito.style.display = 'none';
            fondoModal.style.display = 'none';
        }
    });

    modalCarrito.addEventListener('click', function (e) {
        e.stopPropagation();
    });
});
