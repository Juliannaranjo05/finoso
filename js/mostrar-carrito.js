document.addEventListener('DOMContentLoaded', () => {
    fetch('http://127.0.0.1/finisimo/php/mostrar_carrito.php')
    .then(res => res.json())
    .then(data => {
        const contenedor = document.querySelector('.cuadro-modal-carro'); // contenedor general
        const totalContenedor = document.querySelector('.calculo-total p'); // texto del total

        const isRoot = location.pathname.endsWith('index.html') || location.pathname === '/' || location.pathname === '/index.html';

        let total = 0;

        const totalSection = document.querySelector('.contenedor-total-carro');

        // Eliminar contenido anterior
        document.querySelectorAll('.contenedor-modal-contenido').forEach(el => el.remove());

        data.relojes.forEach(reloj => {
            const precioBase = parseFloat(reloj.precio) * 1000;
            const descuento = parseFloat(reloj.descuento);

            // Aplicar descuento si lo hay y redondear a múltiplos de mil
            let precioFinal = descuento > 0
                ? Math.round(precioBase * (1 - descuento) / 1000) * 1000
                : precioBase;

            total += precioFinal;

            const precioBaseFormateado = precioBase.toLocaleString('es-CO');
            const precioFinalFormateado = precioFinal.toLocaleString('es-CO');
            const rutaImagen = isRoot ? `${reloj.img}` : `../${reloj.img}`;

            let preciosHTML = '';
            if (descuento > 0) {
                preciosHTML = `
                    <p>$${precioFinalFormateado}</p>
                    <div class="descuento"><h3>$${precioBaseFormateado}</h3></div>
                `;
            } else {
                preciosHTML = `<p>$${precioBaseFormateado}</p>`;
            }

            const div = document.createElement('div');
            div.classList.add('contenedor-modal-contenido');
            div.innerHTML = `
                <div class="img-reloj">
                    <img src="${rutaImagen}" alt="">
                </div>
                <div class="info-reloj">
                    <h4>${reloj.nombre}</h4>
                    <div class="modal-precios">
                        ${preciosHTML}
                    </div>
                </div>
                <div class="boton-reloj">
                    <button data-id="${reloj.id_reloj}">Eliminar</button>
                </div>
            `;
            contenedor.insertBefore(div, totalSection);
        });

        // Mostrar total en la sección correspondiente
        totalContenedor.textContent = `$${total.toLocaleString('es-CO')}`;

        // Lógica para eliminar
        document.querySelectorAll('.boton-reloj button').forEach(button => {
            button.addEventListener('click', () => {
                const idReloj = button.getAttribute('data-id');

                fetch('http://127.0.0.1/finisimo/php/eliminar_del_carrito.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id_reloj=${idReloj}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Puedes cambiar esto por recarga parcial
                    } else {
                        console.error(data.message);
                    }
                })
                .catch(err => console.error('Error eliminando el reloj:', err));
            });
        });
    })
    .catch(err => console.error('Error cargando carrito:', err));
});