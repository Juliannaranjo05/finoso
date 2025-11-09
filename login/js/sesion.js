const entornoSesion = (() => {
    let currentScript = document.currentScript;

    if (!currentScript || !currentScript.src) {
        const scripts = document.getElementsByTagName('script');
        for (let i = scripts.length - 1; i >= 0; i--) {
            const script = scripts[i];
            if (script.src && script.src.indexOf('sesion.js') !== -1) {
                currentScript = script;
                break;
            }
        }
    }

    let baseUrl;

    if (currentScript && currentScript.src) {
        const scriptUrl = new URL(currentScript.src, window.location.href);
        let basePathRaw;

        if (scriptUrl.pathname.includes('/login/js/')) {
            [basePathRaw] = scriptUrl.pathname.split('/login/js/');
        } else {
            const segments = scriptUrl.pathname.split('/').filter(Boolean);
            segments.pop();

            if (segments[segments.length - 1] === 'js') {
                segments.pop();
            }

            basePathRaw = segments.length ? `/${segments.join('/')}` : '';
        }

        const normalizedBasePath = basePathRaw.endsWith('/')
            ? basePathRaw
            : `${basePathRaw}/`;
        baseUrl = `${scriptUrl.origin}${normalizedBasePath || '/'}`;
    } else {
        baseUrl = `${window.location.origin}/`;
    }

    const buildUrl = (relativePath = '') => {
        const sanitizedPath = (relativePath || '').replace(/^\/+/, '');
        return new URL(sanitizedPath, baseUrl).href;
    };

    return {
        buildUrl
    };
})();

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
    fetch(entornoSesion.buildUrl('login/php/verificar_sesion.php'))
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
                    window.location.href = entornoSesion.buildUrl('perfil/perfil.html');
                });
            } else {
                // Si no hay sesión, redirige al login al hacer clic en el icono
                
                // Notificar a carrito.js sobre el cambio de sesión
                if (typeof window.actualizarEstadoSesion === 'function') {
                    window.actualizarEstadoSesion(false);
                }

                iconoLogin.addEventListener('click', () => {
                    window.location.href = entornoSesion.buildUrl('login/login.html');
                });
            }
        });

    // Cerrar sesión
    cerrarSesionBtn.addEventListener('click', () => {
        fetch(entornoSesion.buildUrl('login/php/logout.php'))
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