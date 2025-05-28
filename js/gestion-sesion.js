document.addEventListener('DOMContentLoaded', () => {
    const cuadroSesion = document.getElementById('cuadroBrillante');
    const iconoLogin = document.getElementById('iconoLogin');
    const nombreUsuario = document.getElementById('nombreUsuario');
    const cerrarSesionBtn = document.getElementById('cerrarSesionBtn');

    // Consultar si hay sesión activa
    fetch('http://127.0.0.1/finisimo/php/verificar_sesion.php')
        .then(res => res.json())
        .then(data => {
            if (data.logged_in) {
                nombreUsuario.textContent = data.nombre;
                iconoLogin.addEventListener('click', () => {
                    cuadroSesion.style.display = 'block';
                });
            } else {
                // Si no hay sesión, redirige al login
                iconoLogin.addEventListener('click', () => {
                    window.location.href = 'http://127.0.0.1/finisimo/login/login.html';
                });
            }
        });

    // Cerrar sesión
    cerrarSesionBtn.addEventListener('click', () => {
        fetch('http://127.0.0.1/finisimo/php/logout.php')
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
