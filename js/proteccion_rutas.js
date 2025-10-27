/**
 * SISTEMA DE PROTECCIÓN DE RUTAS
 * Controla el acceso a páginas según si hay sesión o no
 * USA EL MISMO SISTEMA DE SESIÓN QUE EL RESTO DEL PROYECTO
 */

// Función para verificar si hay sesión activa
async function verificarSesion() {
    try {
        // Usar el mismo archivo que usa el resto del proyecto
        const response = await fetch('/finoso/login/php/verificar_sesion.php');
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error al verificar sesión:', error);
        return { logged_in: false };
    }
}

// Proteger ruta: requiere sesión activa (redirige a login si no hay sesión)
async function protegerRutaConSesion() {
    const sesion = await verificarSesion();
    
    if (!sesion.logged_in) {
        console.log('⚠️ Acceso denegado: se requiere iniciar sesión');
        window.location.href = '/finoso/login/login.html';
    } else {
        console.log('✅ Sesión activa, acceso permitido');
    }
}

// Proteger ruta: requiere NO tener sesión (redirige a index si ya hay sesión)
async function protegerRutaSinSesion() {
    const sesion = await verificarSesion();
    
    if (sesion.logged_in) {
        console.log('⚠️ Ya hay sesión activa, redirigiendo a inicio');
        window.location.href = '/finoso/index.html';
    } else {
        console.log('✅ Sin sesión, acceso permitido');
    }
}

// Exportar funciones (si usas módulos)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        verificarSesion,
        protegerRutaConSesion,
        protegerRutaSinSesion
    };
}


