document.addEventListener('DOMContentLoaded', () => {
    const iconoCarrito = document.getElementById('iconoCarrito');
    const cuadroCarrito = document.getElementById('cuadroCarrito');
    const cerrarCarrito = document.getElementById('cerrarCarrito');
    const contadorCarrito = document.getElementById('contadorCarrito');

    let sesionIniciada = false; // Control de sesión

    // Click en icono del carrito
    iconoCarrito.addEventListener('click', () => {
        if (!sesionIniciada) {
            alert('No has iniciado sesión');
            window.location.href = 'http://127.0.0.1/finoso/login/login.html';
            return;
        }
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

    function cargarCarrito() {
        fetch('http://127.0.0.1/finoso/php/mostrar_carrito.php')
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    // Si no hay sesión, ocultamos el contador y evitamos mostrar el carrito
                    contadorCarrito.style.display = 'none';
                    sesionIniciada = false;
                    return;
                }

                sesionIniciada = true;

                const contenedor = document.querySelector('.contenedor-info-relojes-carrito');
                const totalContainer = document.querySelector('.total-carrito h3');
                contenedor.innerHTML = '';

                data.relojes.forEach(reloj => {
                    contenedor.innerHTML += `
                        <div class="cuadro-info-reloj-carrito">
                            <div class="img-reloj-carrito">
                                <img src="http://127.0.0.1/finoso/${reloj.img}" alt="">
                            </div>
                            <div class="nombre-precio-carrito">
                                <div class="nombre-carrito">
                                    <h2>${reloj.nombre}</h2>
                                </div>
                                <div class="precio-carrito">
                                    ${reloj.descuento && reloj.descuento !== 0 && reloj.descuento !== null
                                        ? `<h3>$${reloj.precio_final}.000</h3><h4>$${reloj.precio}0</h4>`
                                        : `<h3>$${reloj.precio}0</h3>`
                                    }
                                </div>
                            </div>
                            <div class="boton-eliminar">
                                <button data-id="${reloj.id_reloj}">Eliminar</button>
                            </div>
                        </div>
                    `;
                });

                // Actualizar el total
                totalContainer.textContent = `$${data.total}.000`;

                // Mostrar contador si hay relojes
                const cantidad = data.relojes.length;
                if (cantidad > 0) {
                    contadorCarrito.textContent = cantidad;
                    contadorCarrito.style.display = 'inline-block';
                } else {
                    contadorCarrito.style.display = 'none';
                }

                // Botones para eliminar productos del carrito
                contenedor.querySelectorAll('.boton-eliminar button').forEach(button => {
                    button.addEventListener('click', () => {
                        const id = button.getAttribute('data-id');
                        fetch('http://127.0.0.1/finoso/php/eliminar_del_carrito.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `id_reloj=${id}`
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                cargarCarrito(); // Recargar contenido
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(err => console.error('Error al eliminar el reloj:', err));
                    });
                });
            })
            .catch(err => {
                console.error('Error al obtener el carrito:', err);
            });
    }

    // Botón para finalizar compra
    document.querySelector('.boton-finalizar-carrito button').addEventListener('click', () => {
        const productos = document.querySelectorAll('.cuadro-info-reloj-carrito');

        if (productos.length === 0) {
            alert('No hay productos en el carrito');
            return;
        }

        window.location.href = 'http://127.0.0.1/finoso/informacion-carrito/informacion-carrito.html';
    });

    // Cargar carrito al iniciar
    cargarCarrito();
});