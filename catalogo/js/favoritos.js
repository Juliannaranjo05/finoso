/**
 * SISTEMA DE FAVORITOS (Solo para usuarios SIN sesión)
 * Almacenamiento: localStorage
 * Objetivo: Captar leads y convertir visitantes anónimos en compradores
 */

const entornoFavoritos = (() => {
    let currentScript = document.currentScript;

    if (!currentScript || !currentScript.src) {
        const scripts = document.getElementsByTagName('script');
        for (let i = scripts.length - 1; i >= 0; i--) {
            const script = scripts[i];
            if (script.src && script.src.indexOf('favoritos.js') !== -1) {
                currentScript = script;
                break;
            }
        }
    }

    let baseUrl;

    if (currentScript && currentScript.src) {
        const scriptUrl = new URL(currentScript.src, window.location.href);
        let basePathRaw;

        if (scriptUrl.pathname.includes('/catalogo/js/')) {
            [basePathRaw] = scriptUrl.pathname.split('/catalogo/js/');
        } else {
            const segments = scriptUrl.pathname.split('/').filter(Boolean);
            segments.pop(); // remove file name

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
        baseUrl,
        buildUrl
    };
})();

function buildApiUrl(relativePath) {
    return entornoFavoritos.buildUrl(relativePath);
}

function buildAssetUrl(assetPath) {
    if (!assetPath) return '';
    if (/^https?:\/\//i.test(assetPath)) {
        return assetPath;
    }
    return entornoFavoritos.buildUrl(assetPath);
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('⭐ Sistema de favoritos cargando...');
    
    // Verificar si hay sesión activa
    verificarSesionYMostrarFavoritos();
    
    // Event listeners
    const iconoFavoritos = document.getElementById('iconoFavoritos');
    const cerrarFavoritos = document.getElementById('cerrarFavoritos');
    const btnCumplirDeseos = document.getElementById('btnCumplirDeseos');
    
    if (iconoFavoritos) {
        iconoFavoritos.addEventListener('click', abrirModalFavoritos);
    }
    
    if (cerrarFavoritos) {
        cerrarFavoritos.addEventListener('click', cerrarModalFavoritos);
    }
    
    if (btnCumplirDeseos) {
        btnCumplirDeseos.addEventListener('click', irAInformacionFavoritos);
    }
    
    // Actualizar contador al cargar
    actualizarContadorFavoritos();
    
    console.log('✅ Sistema de favoritos cargado');
});

/**
 * Verificar si hay sesión activa
 * Si hay sesión: ocultar icono de favoritos
 * Si NO hay sesión: mostrar icono de favoritos
 */
function verificarSesionYMostrarFavoritos() {
    // Verificar si hay sesión en el servidor
    fetch(buildApiUrl('login/php/verificar_sesion.php'))
        .then(response => response.json())
        .then(data => {
            const iconoFavoritos = document.getElementById('iconoFavoritos');
            
            if (data.logged_in) {
                // HAY SESIÓN: Ocultar favoritos
                if (iconoFavoritos) {
                    iconoFavoritos.style.display = 'none';
                }
                console.log('🔒 Usuario con sesión - Favoritos ocultos');
            } else {
                // NO HAY SESIÓN: Mostrar favoritos
                if (iconoFavoritos) {
                    iconoFavoritos.style.display = 'flex';
                }
                console.log('👤 Usuario sin sesión - Favoritos visibles');
            }
        })
        .catch(error => {
            console.error('❌ Error al verificar sesión:', error);
            // En caso de error, mostrar favoritos por defecto
            const iconoFavoritos = document.getElementById('iconoFavoritos');
            if (iconoFavoritos) {
                iconoFavoritos.style.display = 'flex';
            }
        });
}

/**
 * Agregar reloj a favoritos (desde botón en la tarjeta del reloj)
 */
function agregarAFavoritos(idReloj) {
    console.log('⭐ Agregando reloj', idReloj, 'a favoritos');
    
    let favoritos = obtenerFavoritos();
    
    // Verificar si ya está en favoritos
    if (favoritos.includes(idReloj)) {
        console.log('⚠️ El reloj ya está en favoritos');
        mostrarNotificacion('Este reloj ya está en tus favoritos ⭐');
        return;
    }
    
    favoritos.push(idReloj);
    localStorage.setItem('favoritos_finoso', JSON.stringify(favoritos));
    
    console.log('✅ Reloj agregado a favoritos:', favoritos);
    actualizarContadorFavoritos();
    mostrarNotificacion('Reloj agregado a favoritos ⭐');
}

/**
 * Quitar reloj de favoritos
 */
function quitarDeFavoritos(idReloj) {
    console.log('🗑️ Quitando reloj', idReloj, 'de favoritos');
    
    let favoritos = obtenerFavoritos();
    favoritos = favoritos.filter(id => id !== idReloj);
    localStorage.setItem('favoritos_finoso', JSON.stringify(favoritos));
    
    console.log('✅ Reloj quitado de favoritos:', favoritos);
    actualizarContadorFavoritos();
    cargarFavoritos(); // Recargar la vista
    mostrarNotificacion('Reloj quitado de favoritos');
}

/**
 * Obtener favoritos desde localStorage
 */
function obtenerFavoritos() {
    const favoritos = localStorage.getItem('favoritos_finoso');
    return favoritos ? JSON.parse(favoritos) : [];
}

/**
 * Actualizar contador de favoritos
 */
function actualizarContadorFavoritos() {
    const favoritos = obtenerFavoritos();
    const contador = document.getElementById('contadorFavoritos');
    
    if (contador) {
        contador.textContent = favoritos.length;
        
        if (favoritos.length === 0) {
            contador.style.display = 'none';
        } else {
            contador.style.display = 'flex';
        }
    }
}

/**
 * Abrir modal de favoritos
 */
function abrirModalFavoritos() {
    console.log('📂 Abriendo modal de favoritos');
    
    const modal = document.getElementById('cuadroFavoritos');
    if (modal) {
        modal.style.display = 'block';
        cargarFavoritos();
    }
}

/**
 * Cerrar modal de favoritos
 */
function cerrarModalFavoritos() {
    console.log('❌ Cerrando modal de favoritos');
    
    const modal = document.getElementById('cuadroFavoritos');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Cargar favoritos en el modal
 */
async function cargarFavoritos() {
    console.log('📋 Cargando favoritos...');
    
    const favoritos = obtenerFavoritos();
    const contenedor = document.getElementById('contenedor-favoritos');
    const textoInformativo = document.getElementById('texto-informativo-favoritos');
    const totalElement = document.getElementById('totalFavoritos');
    
    if (!contenedor) return;
    
    // Limpiar contenedor
    contenedor.innerHTML = '';
    
    if (favoritos.length === 0) {
        // No hay favoritos
        if (textoInformativo) textoInformativo.style.display = 'block';
        if (totalElement) totalElement.textContent = '$0';
        console.log('📭 No hay favoritos');
        return;
    }
    
    // Ocultar texto informativo
    if (textoInformativo) textoInformativo.style.display = 'none';
    
    let total = 0;
    
    // Cargar información de cada reloj favorito
    for (const idReloj of favoritos) {
        try {
            const response = await fetch(buildApiUrl(`informacion/php/obtener_reloj.php?id_reloj=${idReloj}`));
            const data = await response.json();
            
            // El PHP retorna el reloj directamente, no en data.reloj
            if (data && data.id_reloj) {
                const reloj = data;
                total += parseInt(reloj.precio);
                const relojImgUrl = buildAssetUrl(reloj.img);
                
                // Crear elemento HTML para el favorito
                const favoritoHTML = `
                    <div class="cuadro-info-reloj-carrito" data-id="${reloj.id_reloj}">
                        <div class="img-reloj-carrito">
                            <img src="${relojImgUrl}" alt="${reloj.nombre}">
                        </div>
                        <div class="nombre-precio-carrito">
                            <div class="nombre-carrito">
                                <h2>${reloj.nombre}</h2>
                            </div>
                            <div class="precio-carrito">
                                <h3>Precio:</h3>
                                <h4>$${Math.round(reloj.precio).toLocaleString('es-CO')}</h4>
                            </div>
                        </div>
                        <div class="boton-eliminar">
                            <button onclick="quitarDeFavoritos(${reloj.id_reloj})">Eliminar</button>
                        </div>
                    </div>
                `;
                
                contenedor.innerHTML += favoritoHTML;
            }
        } catch (error) {
            console.error('❌ Error al cargar reloj favorito', idReloj, error);
        }
    }
    
    // Actualizar total
    if (totalElement) {
        totalElement.textContent = '$' + total.toLocaleString('es-CO');
    }
    
    console.log('✅ Favoritos cargados. Total:', total);
}

/**
 * Redirigir a información-carrito (reutilizando la misma página)
 * Los favoritos se leen de localStorage en lugar de la BD
 */
function irAInformacionFavoritos() {
    const favoritos = obtenerFavoritos();
    
    if (favoritos.length === 0) {
        mostrarNotificacion('No tienes favoritos para comprar');
        return;
    }
    
    // Marcar que viene de favoritos (para que informacion-favoritos.html lo sepa)
    sessionStorage.setItem('origen_compra', 'favoritos');
    sessionStorage.setItem('ids_relojes_compra', JSON.stringify(favoritos));
    
    console.log('🛍️ Redirigiendo a información de favoritos...');
    
    // Detectar la ruta base según la ubicación actual
    const currentPath = window.location.pathname;
    let targetUrl;
    
    if (currentPath.includes('/catalogo/')) {
        // Desde catalogo, subir un nivel
        targetUrl = '../informacion-favoritos/informacion-favoritos.html';
    } else {
        // Desde raíz (index.html) o cualquier otra página
        targetUrl = 'informacion-favoritos/informacion-favoritos.html';
    }
    
    window.location.href = targetUrl;
}

/**
 * Mostrar notificación temporal
 */
function mostrarNotificacion(mensaje) {
    // Crear elemento de notificación
    const notificacion = document.createElement('div');
    notificacion.textContent = mensaje;
    notificacion.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: linear-gradient(135deg, #FFCF66, #FFD700);
        color: #000;
        padding: 15px 25px;
        border-radius: 10px;
        font-weight: bold;
        box-shadow: 0 4px 15px rgba(255, 207, 102, 0.4);
        z-index: 10000;
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(notificacion);
    
    // Eliminar después de 3 segundos
    setTimeout(() => {
        notificacion.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notificacion);
        }, 300);
    }, 3000);
}

// Agregar estilos de animación
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

console.log('⭐ Módulo de favoritos inicializado');

