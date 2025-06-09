document.addEventListener('DOMContentLoaded', () => {
    const cuadroSesion = document.getElementById('cuadro-sesion');
    const iconoLogin = document.getElementById('iconoLogin');
    const nombreUsuario = document.getElementById('nombreUsuario');
    const cerrarSesionBtn = document.getElementById('cerrarSesionBtn');

    let sesionActiva = false; // Bandera para saber si hay sesión

    // Consultar si hay sesión activa
    fetch('http://127.0.0.1/finoso/login/php/verificar_sesion.php')
        .then(res => res.json())
        .then(data => {
            if (data.logged_in) {
                sesionActiva = true;
                nombreUsuario.textContent = data.nombre;

                iconoLogin.addEventListener('click', () => {
                    // Mostrar u ocultar el cuadro de sesión
                    cuadroSesion.style.display = (cuadroSesion.style.display === 'block') ? 'none' : 'block';
                });
            } else {
                // Si no hay sesión, redirige al login al hacer clic en el icono
                iconoLogin.addEventListener('click', () => {
                    window.location.href = 'http://127.0.0.1/finoso/login/login.html';
                });
            }
        });

    // Cerrar sesión
    cerrarSesionBtn.addEventListener('click', () => {
        fetch('http://127.0.0.1/finoso/login/php/logout.php')
            .then(() => {
                location.reload(); // Recargar la página al cerrar sesión
            });
    });

    // Cerrar el cuadro si se hace clic fuera de él
    document.addEventListener('click', (e) => {
        if (!cuadroSesion.contains(e.target) && !iconoLogin.contains(e.target)) {
            cuadroSesion.style.display = 'none';
        }
    });
});