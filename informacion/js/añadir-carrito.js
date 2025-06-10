document.addEventListener('DOMContentLoaded', () => {
    const botonCarrito = document.querySelector('.botones-comprar-anadir button');

    botonCarrito.addEventListener('click', () => {
        const params = new URLSearchParams(window.location.search);
        const idReloj = params.get('id_reloj');

        fetch('http://127.0.0.1/finoso/login/php/verificar_sesion.php')
            .then(res => res.json())
            .then(data => {
                if (!data.logged_in) {
                    alert('Debes iniciar sesión para añadir productos al carrito.');
                    window.location.href = '../login/login.html';
                    return;
                }

                fetch('http://127.0.0.1/finoso/informacion/php/añadir_al_carrito.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_reloj: idReloj
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Producto añadido al carrito.');
                    } else {
                        alert(data.message);
                    }
                })
                .catch(err => {
                    console.error('Error al añadir al carrito:', err);
                });
            });
    });
});