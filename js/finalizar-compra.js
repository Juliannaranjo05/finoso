document.addEventListener('DOMContentLoaded', () => {
    const botonFinalizar = document.getElementById('finalizar-compra');

    botonFinalizar.addEventListener('click', () => {
        const productosCarrito = document.querySelectorAll('.contenedor-modal-contenido');

        if (productosCarrito.length === 0) {
            alert('No hay productos en el carrito. Agrega alguno antes de finalizar la compra.');
            window.location.href = 'http://127.0.0.1/finisimo/catalogo/catalogo.html'; // Cambia a la ruta real de tu catálogo
        } else {
            // Guardar los productos y total antes de redirigir
            guardarProductosYTotal();

            // Redirigir a la página de resumen de compra
            window.location.href = 'http://127.0.0.1/finisimo/informacion-carrito/informacion-carrito.html'; // Cambia según tu ruta real
        }
    });

    function guardarProductosYTotal() {
        const productos = [];
        let total = 0;

        document.querySelectorAll('.contenedor-modal-contenido').forEach(producto => {
            const nombre = producto.querySelector('.info-reloj h4').textContent;
            const precioTexto = producto.querySelector('.modal-precios p').textContent;
            const precio = parseInt(precioTexto.replace(/\D/g, ''), 10); // Elimina puntos, signos, etc.

            productos.push({ nombre, precio });
            total += precio;
        });

        localStorage.setItem('productosResumen', JSON.stringify(productos));
        localStorage.setItem('totalResumen', total);
    }
});