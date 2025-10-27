/**
 * USER MODAL - Gestión de modal de usuario y historial de compras
 */

// Función para cerrar el modal de usuario
function cerrarModalUsuario() {
    const modal = document.getElementById('cuadro-sesion');
    if (modal) {
        modal.classList.remove('show');
    }
}

// Función para cambiar entre tabs
function cambiarTab(tabName) {
    // Actualizar botones
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => btn.classList.remove('active'));
    event.target.closest('.tab-btn').classList.add('active');
    
    // Actualizar contenido
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => content.classList.remove('active'));
    
    const targetTab = document.getElementById(`tab-${tabName}`);
    if (targetTab) {
        targetTab.classList.add('active');
        
        // Si es el tab de historial y aún no se ha cargado, cargar
        if (tabName === 'historial' && !targetTab.dataset.loaded) {
            cargarHistorialCompras();
            targetTab.dataset.loaded = 'true';
        }
    }
}

// Función para cargar historial de compras
async function cargarHistorialCompras() {
    const loading = document.getElementById('historialLoading');
    const empty = document.getElementById('historialEmpty');
    const lista = document.getElementById('historialLista');
    
    loading.style.display = 'block';
    empty.style.display = 'none';
    lista.innerHTML = '';
    
    try {
        const response = await fetch('../login/php/obtener_historial_usuario.php');
        const data = await response.json();
        
        loading.style.display = 'none';
        
        if (data.success && data.ordenes && data.ordenes.length > 0) {
            // Mostrar órdenes
            lista.innerHTML = data.ordenes.map(orden => crearCardOrden(orden)).join('');
            
            // Actualizar estadísticas en tab perfil
            document.getElementById('totalOrdenes').textContent = data.stats.total_ordenes;
            document.getElementById('totalRelojes').textContent = data.stats.total_relojes;
        } else {
            empty.style.display = 'flex';
        }
    } catch (error) {
        console.error('Error al cargar historial:', error);
        loading.style.display = 'none';
        empty.style.display = 'flex';
        empty.querySelector('p').textContent = 'Error al cargar el historial. Intenta de nuevo.';
    }
}

// Función para crear tarjeta de orden
function crearCardOrden(orden) {
    const estadoConfig = getEstadoConfig(orden.estado);
    const fecha = new Date(orden.fecha).toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    return `
        <div class="orden-card">
            <div class="orden-header">
                <div class="orden-numero">
                    <span class="label">Orden #${orden.id_orden}</span>
                    <span class="fecha">${fecha}</span>
                </div>
                <span class="orden-estado estado-${orden.estado}">
                    ${estadoConfig.icon} ${estadoConfig.texto}
                </span>
            </div>
            
            <div class="orden-body">
                <div class="orden-reloj">
                    ${orden.imagen ? `
                        <img src="${orden.imagen}" alt="${orden.nombre_reloj}" class="reloj-img">
                    ` : `
                        <div class="reloj-img-placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                                <path d="M12 6v6l4 2"/>
                            </svg>
                        </div>
                    `}
                    <div class="reloj-info">
                        <p class="reloj-nombre">${orden.nombre_reloj || 'Reloj FINOSO'}</p>
                        <p class="reloj-marca">${orden.marca || ''}</p>
                        <p class="orden-metodo">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                                <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                            </svg>
                            ${orden.metodo_pago.toUpperCase()}
                        </p>
                    </div>
                </div>
                
                <div class="orden-detalles">
                    <div class="detalle-row">
                        <span>Producto:</span>
                        <span>$${formatearPrecio(orden.precio_producto)}</span>
                    </div>
                    <div class="detalle-row">
                        <span>Envío:</span>
                        <span>$${formatearPrecio(orden.costo_envio)}</span>
                    </div>
                    <div class="detalle-row total">
                        <span>Total:</span>
                        <span>$${formatearPrecio(orden.total)}</span>
                    </div>
                </div>
            </div>
            
            <div class="orden-footer">
                ${orden.estado === 'enviado' && orden.guia_envio ? `
                    <button class="btn-accion btn-rastrear" onclick="rastrearPedido('${orden.guia_envio}', '${orden.transportadora}')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                            <path d="M18 18.5c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5-1.5.67-1.5 1.5.67 1.5 1.5 1.5zm1.5-9H17V12h4.46L19.5 9.5zM6 18.5c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5-1.5.67-1.5 1.5.67 1.5 1.5 1.5zM20 8l3 4v5h-2c0 1.66-1.34 3-3 3s-3-1.34-3-3H9c0 1.66-1.34 3-3 3s-3-1.34-3-3H1V6c0-1.1.9-2 2-2h14v4h3zM8 6H4v6h4V6z"/>
                        </svg>
                        Rastrear Pedido
                    </button>
                ` : ''}
                
                ${['pagado', 'aprobado', 'enviado', 'entregado'].includes(orden.estado) ? `
                    <button class="btn-accion btn-recomprar" onclick="recomprar(${orden.id_reloj})">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                            <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                        </svg>
                        Comprar de Nuevo
                    </button>
                ` : ''}
                
                ${orden.estado === 'rechazado' ? `
                    <button class="btn-accion btn-soporte" onclick="contactarSoporteRechazo(${orden.id_orden}, '${orden.nombre_reloj.replace(/'/g, "\\'")}', '${(orden.motivo_rechazo || '').replace(/'/g, "\\'")}', '${orden.token_verificacion || ''}')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                            <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
                        </svg>
                        Contactar Soporte
                    </button>
                ` : ''}
                
                ${orden.comprobante_pago && orden.estado !== 'rechazado' ? `
                    <button class="btn-accion btn-comprobante" onclick="verComprobante(${orden.id_orden})">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                        </svg>
                        Ver Comprobante
                    </button>
                ` : ''}
            </div>
            
            ${orden.estado === 'rechazado' && orden.motivo_rechazo ? `
                <div class="orden-alerta">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                    </svg>
                    <span>
                        <strong>Motivo:</strong> ${orden.motivo_rechazo}
                        <span class="info-tooltip">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                            </svg>
                            <span class="tooltip-text">
                                📧 Revisa tu correo electrónico y WhatsApp<br>
                                📱 Te enviamos las instrucciones para recuperar tu orden<br>
                                🔗 Encontrarás un enlace para completar el pago
                            </span>
                        </span>
                    </span>
                </div>
            ` : ''}
        </div>
    `;
}

// Configuración de estados
function getEstadoConfig(estado) {
    const config = {
        'pendiente': { icon: '⏳', texto: 'Pendiente' },
        'pendiente_verificacion': { icon: '🔍', texto: 'En Verificación' },
        'pagado': { icon: '✅', texto: 'Pagado' },
        'aprobado': { icon: '✅', texto: 'Aprobado' },
        'enviado': { icon: '🚚', texto: 'En Camino' },
        'entregado': { icon: '🎁', texto: 'Entregado' },
        'rechazado': { icon: '❌', texto: 'Rechazado' },
        'cancelado': { icon: '⛔', texto: 'Cancelado' }
    };
    return config[estado] || { icon: '📦', texto: estado };
}

// Formatear precio
function formatearPrecio(precio) {
    return new Intl.NumberFormat('es-CO').format(precio);
}

// Función para rastrear pedido
function rastrearPedido(guia, transportadora) {
    const urls = {
        'SERVIENTREGA': `https://www.servientrega.com/rastreo/?guia=${guia}`,
        'INTERRAPIDISIMO': `https://www.interrapidisimo.com/rastreo/?guia=${guia}`,
        'TCC': `https://tcc.com.co/rastreo/?guia=${guia}`
    };
    
    const url = urls[transportadora.toUpperCase()] || `https://www.google.com/search?q=rastrear+${guia}`;
    window.open(url, '_blank');
}

// Función para recomprar
function recomprar(idReloj) {
    if (confirm('¿Quieres comprar este reloj de nuevo?')) {
        window.location.href = `catalogo.html?reloj=${idReloj}`;
    }
}

// Función para ver comprobante
function verComprobante(idOrden) {
    window.open(`../informacion/ver_comprobante.php?orden=${idOrden}`, '_blank', 'width=800,height=600');
}

// Función para contactar soporte sobre orden rechazada
function contactarSoporteRechazo(idOrden, nombreReloj, motivoRechazo, token) {
    const tokenDecoded = token || '';
    const tokenShort = tokenDecoded ? tokenDecoded.substring(0, 16) + '...' : 'No disponible';
    
    const mensaje = `Hola! 👋\n\n` +
                   `Mi orden #${idOrden} fue rechazada y necesito ayuda.\n\n` +
                   `Reloj: ${nombreReloj}\n` +
                   `Motivo: ${motivoRechazo || 'No especificado'}\n` +
                   `Token: ${tokenShort}\n\n` +
                   `¿Pueden ayudarme a resolver este problema?`;
    
    const numeroWhatsApp = '+573173897119';
    const url = `https://wa.me/${numeroWhatsApp}?text=${encodeURIComponent(mensaje)}`;
    window.open(url, '_blank');
}

// Cargar info de usuario cuando se abre el modal
document.addEventListener('DOMContentLoaded', function() {
    // El modal se abre desde sesion.js, aquí solo preparamos
    const modal = document.getElementById('cuadro-sesion');
    if (modal) {
        modal.addEventListener('click', function(e) {
            // Cerrar si se hace clic fuera del contenido
            if (e.target === modal) {
                cerrarModalUsuario();
            }
        });
    }
});

