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

    document.querySelector('.boton-finalizar-carrito button').addEventListener('click', () => {
        window.location.href = 'http://127.0.0.1/finoso/informacion-carrito/informacion-carrito.html';
    });

    // Llamamos la función al cargar
    actualizarContadorCarrito();

    fetch('http://127.0.0.1/finoso/php/mostrar_carrito.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                console.error(data.message);
                return;
            }

            const contenedor = document.querySelector('.contenedor-info-relojes-carrito');
            const totalContainer = document.querySelector('.total-carrito h3');
            contenedor.innerHTML = '';

            data.relojes.forEach(reloj => {
                contenedor.innerHTML += `
                    <div class="cuadro-info-reloj-carrito">
                        <div class="img-reloj-carrito">
                            <img src="../${reloj.img}" alt="">
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

            // Actualizar el contador del carrito
            const cantidad = data.relojes.length;
            if (cantidad > 0) {
                contadorCarrito.textContent = cantidad;
                contadorCarrito.style.display = 'inline-block';
            } else {
                contadorCarrito.style.display = 'none';
            }

            // Añadir eventos a los botones de eliminar
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
                            // Vuelve a cargar el contenido del carrito y el contador SIN recargar la página
                            cargarCarrito(); // Ver siguiente paso
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(err => {
                        console.error('Error al eliminar el reloj:', err);
                    });
                });
            });
        })
        .catch(err => {
            console.error('Error al obtener el carrito:', err);
    });
});