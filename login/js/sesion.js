document.addEventListener('DOMContentLoaded', () => {
    const cuadroSesion = document.getElementById('cuadro-sesion');
    const iconoLogin = document.getElementById('iconoLogin');
    const nombreUsuario = document.getElementById('nombreUsuario');
    const correoUsuario = document.getElementById('correoUsuario');
    const cerrarSesionBtn = document.getElementById('cerrarSesionBtn');

    // Si no hay icono de login, no hacer nada (estamos en página de perfil)
    if (!iconoLogin) {
        return;
    }

    let sesionActiva = false; // Bandera para saber si hay sesión

    // Consultar si hay sesión activa
    fetch('http://127.0.0.1/finoso/login/php/verificar_sesion.php')
        .then(res => res.json())
        .then(data => {
            if (data.logged_in) {
                sesionActiva = true;
                nombreUsuario.textContent = data.nombre;
                
                // Actualizar correo si existe el elemento
                if (correoUsuario) {
                    correoUsuario.textContent = data.correo || '';
                }

                // Notificar a carrito.js sobre el cambio de sesión
                if (typeof window.actualizarEstadoSesion === 'function') {
                    window.actualizarEstadoSesion(true);
                }

                iconoLogin.addEventListener('click', () => {
                    // Redirigir a la página de perfil
                    window.location.href = 'http://127.0.0.1/finoso/perfil/perfil.html';
                });
            } else {
                // Si no hay sesión, redirige al login al hacer clic en el icono
                
                // Notificar a carrito.js sobre el cambio de sesión
                if (typeof window.actualizarEstadoSesion === 'function') {
                    window.actualizarEstadoSesion(false);
                }

                iconoLogin.addEventListener('click', () => {
                    window.location.href = 'http://127.0.0.1/finoso/login/login.html';
                });
            }
        });

    // Cerrar sesión
    cerrarSesionBtn.addEventListener('click', () => {
        fetch('http://127.0.0.1/finoso/login/php/logout.php')
            .then(() => {
                // Notificar a carrito.js sobre el cierre de sesión
                if (typeof window.actualizarEstadoSesion === 'function') {
                    window.actualizarEstadoSesion(false);
                }
                location.reload(); // Recargar la página al cerrar sesión
            });
    });

    // Cerrar el cuadro si se hace clic fuera de él (solo si existe cuadroSesion)
    if (cuadroSesion && iconoLogin) {
        document.addEventListener('click', (e) => {
            if (!cuadroSesion.contains(e.target) && !iconoLogin.contains(e.target)) {
                cuadroSesion.classList.remove('show');
            }
        });
    }
});