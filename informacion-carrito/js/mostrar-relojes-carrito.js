document.addEventListener('DOMContentLoaded', () => {
    fetch('http://127.0.0.1/finisimo/php/mostrar_carrito.php')
        .then(res => res.json())
        .then(data => {
            const contenedor = document.querySelector('.contenedor-relojes-carrito');
            const totalContenedor = document.querySelector('#total-pago');

            if (!contenedor || !totalContenedor) {
                console.error('⚠️ Contenedor o total no encontrado en el DOM.');
                return;
            }

            // Limpiar relojes anteriores si ya existen
            contenedor.querySelectorAll('.cuadro-reloj-carrito').forEach(el => el.remove());

            let total = 0;

            data.relojes.forEach((reloj, index) => {
                const precioBase = parseFloat(reloj.precio) * 1000;
                const descuento = parseFloat(reloj.descuento);
                const rutaImagen = reloj.img.startsWith('img') ? `../${reloj.img}` : reloj.img;

                const precioFinal = descuento > 0
                    ? Math.round(precioBase * (1 - descuento) / 1000) * 1000
                    : precioBase;

                total += precioFinal;

                const precioBaseFormateado = precioBase.toLocaleString('es-CO');
                const precioFinalFormateado = precioFinal.toLocaleString('es-CO');

                console.log(`🕒 Reloj ${index + 1}:`, {
                    nombre: reloj.nombre,
                    precio: precioBase,
                    descuento,
                    final: precioFinal
                });

                const relojHTML = document.createElement('div');
                relojHTML.classList.add('cuadro-reloj-carrito');
                relojHTML.innerHTML = `
                    <div class="img-nombre-carrito">
                        <div class="img-carrito">
                            <img src="${rutaImagen}" alt="Cargando...">
                        </div>
                        <div class="nombre-carrito">
                            <h2>${reloj.nombre}</h2>
                        </div>
                    </div>
                    <div class="precio-cantidad">
                        <div class="precio">
                            <p>$${precioFinalFormateado}</p>
                            ${descuento > 0
                                ? `<div class="descuento"><h3>$${precioBaseFormateado}</h3></div>`
                                : ''}
                        </div>
                        <div class="cantidad">
                            <div class="texto-cantidad"><h1>cantidad</h1></div>
                            <div class="numero-cantidad"><p>1</p></div>
                        </div>
                    </div>
                `;

                // Redirección al hacer clic en el reloj
                relojHTML.addEventListener('click', () => {
                    const url = `http://127.0.0.1/finisimo/informacion/informacion.html?id_reloj=${reloj.id_reloj}`;
                    window.location.href = url;
                });

                const totalDiv = contenedor.querySelector('.calculo-total');
                contenedor.insertBefore(relojHTML, totalDiv);
            });

            totalContenedor.textContent = `$${total.toLocaleString('es-CO')}`;

            activarLupa();
        })
        .catch(err => {
            console.error('❌ Error cargando carrito:', err);
        });
});