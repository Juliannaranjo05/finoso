document.addEventListener('DOMContentLoaded', () => {
    console.log('🔍 DEBUG carrito.js - DOM cargado, inicializando...');
    
    const iconoCarrito = document.getElementById('iconoCarrito');
    const cuadroCarrito = document.getElementById('cuadroCarrito');
    const cerrarCarrito = document.getElementById('cerrarCarrito');
    const contadorCarrito = document.getElementById('contadorCarrito');

    console.log('🔍 DEBUG carrito.js - Elementos encontrados:', {
        iconoCarrito: !!iconoCarrito,
        cuadroCarrito: !!cuadroCarrito,
        cerrarCarrito: !!cerrarCarrito,
        contadorCarrito: !!contadorCarrito
    });

    let sesionIniciada = false; // Control de sesión

    // Función para verificar sesión
    function verificarSesion() {
        return fetch('http://127.0.0.1/finoso/login/php/verificar_sesion.php')
            .then(res => res.json())
            .then(data => {
                sesionIniciada = data.logged_in;
                console.log('🔍 DEBUG carrito.js - Sesión verificada:', sesionIniciada);
                return data.logged_in;
            })
            .catch(err => {
                console.error('Error al verificar sesión:', err);
                sesionIniciada = false;
                return false;
            });
    }

    // Verificar sesión al cargar
    verificarSesion();

    // Función global para actualizar estado de sesión
    window.actualizarEstadoSesion = function(loggedIn) {
        sesionIniciada = loggedIn;
        console.log('🔍 DEBUG carrito.js - Estado de sesión actualizado:', sesionIniciada);
    };

    // Click en icono del carrito (solo si existe)
    if (iconoCarrito && cuadroCarrito) {
        iconoCarrito.addEventListener('click', () => {
            if (!sesionIniciada) {
                alert('No has iniciado sesión');
                window.location.href = 'http://127.0.0.1/finoso/login/login.html';
                return;
            }
            cuadroCarrito.style.display = 'grid';
        });
    }

    // Cerrar con botón (solo si existe)
    if (cerrarCarrito && cuadroCarrito) {
        cerrarCarrito.addEventListener('click', () => {
            cuadroCarrito.style.display = 'none';
        });
    }

    // Cerrar al hacer clic fuera del modal (solo si existe)
    if (cuadroCarrito) {
        document.addEventListener('click', (e) => {
            const modalVisible = getComputedStyle(cuadroCarrito).display === 'grid';
            const esClickDentroModal = e.target.closest('.cuadro-carrito');
            if (modalVisible && !esClickDentroModal && !e.target.closest('#iconoCarrito')) {
                cuadroCarrito.style.display = 'none';
            }
        });
    }

    function cargarCarrito() {
        console.log('🔍 DEBUG carrito.js - Iniciando cargarCarrito()');
        console.log('🔍 DEBUG carrito.js - URL:', window.location.href);
        fetch('http://127.0.0.1/finoso/php/mostrar_carrito.php', {
            credentials: 'include'
        })
            .then(res => {
                console.log('🔍 DEBUG carrito.js - Status:', res.status);
                return res.text(); // Obtener como texto primero
            })
            .then(text => {
                console.log('🔍 DEBUG carrito.js - Respuesta:', text);
                console.log('🔍 DEBUG carrito.js - Longitud:', text.length);
                if (!text || text.trim() === '') {
                    throw new Error('Respuesta vacía del servidor');
                }
                return JSON.parse(text);
            })
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
                const botonFinalizar = document.querySelector('.boton-finalizar-carrito');
                const textoInformativo = document.querySelector('#texto-informativo');
                
                console.log('🔍 DEBUG carrito.js - Elementos encontrados:');
                console.log('- contenedor:', contenedor);
                console.log('- totalContainer:', totalContainer);
                console.log('- botonFinalizar:', botonFinalizar);
                console.log('- textoInformativo:', textoInformativo);
                
                // Verificar que los elementos existen
                if (!contenedor || !totalContainer || !botonFinalizar) {
                    console.error('Elementos del carrito no encontrados en el DOM');
                    return;
                }
                
                // Limpiar contenedor
                contenedor.innerHTML = '';

                if (data.relojes.length === 0) {
                    console.log('🔍 DEBUG carrito.js - Carrito vacío detectado');
                    // Carrito vacío - mostrar mensaje y ocultar botón
                    if (textoInformativo) {
                        console.log('🔍 DEBUG carrito.js - Usando elemento textoInformativo existente');
                        textoInformativo.textContent = 'No hay productos en el carrito';
                        textoInformativo.style.display = 'block';
                    } else {
                        console.log('🔍 DEBUG carrito.js - Creando mensaje dinámico');
                        // Si no existe el elemento, crear un mensaje dinámico
                        contenedor.innerHTML = `
                            <div style="text-align: center; padding: 40px 20px; color: #FFCF66; font-size: 1.5rem; background: linear-gradient(135deg, rgba(255, 207, 102, 0.1), rgba(255, 184, 77, 0.1)); border: 2px solid rgba(255, 207, 102, 0.3); border-radius: 15px; margin: 20px 0; box-shadow: 0 4px 15px rgba(255, 207, 102, 0.2);">
                                No hay productos en el carrito
                            </div>
                        `;
                    }
                    console.log('🔍 DEBUG carrito.js - Ocultando botón finalizar');
                    botonFinalizar.style.display = 'none';
                    totalContainer.textContent = '$0';
                } else {
                    console.log('🔍 DEBUG carrito.js - Carrito con productos:', data.relojes.length);
                    // Hay productos - ocultar mensaje y mostrar botón
                    if (textoInformativo) {
                        textoInformativo.style.display = 'none';
                    }
                    console.log('🔍 DEBUG carrito.js - Mostrando botón finalizar');
                    botonFinalizar.style.display = 'block';
                    
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
                                        ${reloj.descuento && reloj.descuento > 0 && reloj.precio_final !== reloj.precio
                                            ? `<h3>$${Math.round(reloj.precio_final).toLocaleString('es-CO')}</h3><h4>$${Math.round(reloj.precio).toLocaleString('es-CO')}</h4>`
                                            : `<h3>$${Math.round(reloj.precio).toLocaleString('es-CO')}</h3>`
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
                    totalContainer.textContent = `$${Math.round(data.total).toLocaleString('es-CO')}`;
                }

                // Mostrar contador si hay relojes
                const cantidad = data.relojes.length;
                console.log('🔍 DEBUG carrito.js - Actualizando contador:', cantidad);
                if (cantidad > 0) {
                    contadorCarrito.textContent = cantidad;
                    contadorCarrito.style.display = 'inline-block';
                    console.log('🔍 DEBUG carrito.js - Contador mostrado:', contadorCarrito.textContent);
                } else {
                    contadorCarrito.style.display = 'none';
                    console.log('🔍 DEBUG carrito.js - Contador oculto');
                }

                // Botones para eliminar productos del carrito (solo si hay productos)
                if (data.relojes.length > 0) {
                    contenedor.querySelectorAll('.boton-eliminar button').forEach(button => {
                        button.addEventListener('click', () => {
                            const id = button.getAttribute('data-id');
                            fetch('http://127.0.0.1/finoso/php/eliminar_del_carrito.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `id_reloj=${id}`,
                                credentials: 'include'
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
                }
            })
            .catch(err => {
                console.error('Error al obtener el carrito:', err);
            });
    }

    // Botón para finalizar compra
    document.querySelector('.boton-finalizar-carrito button').addEventListener('click', async () => {
        const productos = document.querySelectorAll('.cuadro-info-reloj-carrito');
        const textoInformativo = document.querySelector('#texto-informativo');

        // Verificar si el carrito está vacío
        if (productos.length === 0 || textoInformativo.style.display !== 'none') {
            alert('No hay productos en el carrito');
            return;
        }

        // Deshabilitar botón mientras valida
        const boton = document.querySelector('.boton-finalizar-carrito button');
        const textoOriginal = boton.textContent;
        boton.disabled = true;
        boton.textContent = 'Validando...';

        try {
            // Validar disponibilidad de todos los relojes del carrito
            const response = await fetch('http://127.0.0.1/finoso/php/validar_disponibilidad_carrito.php');
            const data = await response.json();

            if (!data.success && data.relojes_vendidos && data.relojes_vendidos.length > 0) {
                // Hay relojes que ya no están disponibles
                const nombresVendidos = data.relojes_vendidos.map(r => `• ${r.nombre}`).join('\n');
                alert(`⚠️ Algunos relojes ya no están disponibles:\n\n${nombresVendidos}\n\nHan sido eliminados de tu carrito automáticamente.`);
                
                // Recargar el carrito para mostrar cambios
                cargarCarrito();
                
                // Habilitar botón
                boton.disabled = false;
                boton.textContent = textoOriginal;
                return;
            }

            // Todo OK, continuar a finalizar compra
            window.location.href = 'http://127.0.0.1/finoso/informacion-carrito/informacion-carrito.html';
            
        } catch (error) {
            console.error('Error al validar disponibilidad:', error);
            alert('Error al validar el carrito. Por favor, intenta de nuevo.');
            boton.disabled = false;
            boton.textContent = textoOriginal;
        }
    });

    // Función global para actualizar el contador (llamable desde otros scripts)
    window.actualizarContadorCarrito = function() {
        console.log('🔍 DEBUG carrito.js - actualizarContadorCarrito() llamada');
        cargarCarrito();
    };

    // Cargar carrito al iniciar
    cargarCarrito();
});