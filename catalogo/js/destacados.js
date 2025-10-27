/**
 * Sistema de Productos Vendidos
 * Muestra los relojes vendidos más caros
 */

class SistemaVendidos {
    constructor() {
        this.vendidos = [];
        this.init();
    }

    async init() {
        console.log('🏆 Inicializando sistema de productos vendidos...');
        console.log('🔍 Buscando contenedor principal...');
        const container = document.getElementById('contenedor-relojes');
        if (!container) {
            console.error('❌ No se encontró el contenedor principal');
            return;
        }
        console.log('✅ Contenedor encontrado:', container);
        await this.cargarVendidos();
    }

    async cargarVendidos() {
        try {
            const response = await fetch('php/obtener_vendidos.php');
            const data = await response.json();
            
            if (data.success) {
                this.vendidos = data.vendidos;
                this.mostrarVendidos();
            } else {
                this.mostrarError('Error al cargar productos vendidos: ' + data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error de conexión');
        }
    }

    mostrarVendidos() {
        this.renderizarVendidos(this.vendidos);
    }

    renderizarVendidos(vendidos) {
        console.log('🎨 Renderizando vendidos:', vendidos);
        const container = document.getElementById('contenedor-relojes');
        
        if (!container) {
            console.error('❌ No se encontró el contenedor para renderizar');
            return;
        }
        
        if (vendidos.length === 0) {
            console.log('ℹ️ No hay productos vendidos para mostrar');
            return;
        }

        console.log(`✅ Mostrando ${vendidos.length} productos vendidos`);
        
        // Crear indicador sutil
        const indicador = document.createElement('div');
        indicador.className = 'vendidos-indicador';
        indicador.innerHTML = '<span class="vendidos-texto">Productos Vendidos</span>';
        
        // Crear HTML de productos vendidos
        const htmlVendidos = vendidos.map(reloj => this.crearHTMLVendido(reloj)).join('');
        
        console.log('📝 HTML generado:', htmlVendidos);
        
        // Agregar al final del contenedor principal
        container.appendChild(indicador);
        container.insertAdjacentHTML('beforeend', htmlVendidos);
        
        console.log('✅ Productos vendidos agregados al contenedor');
    }

    crearHTMLVendido(reloj) {
        const precioFormateado = this.formatearPrecio(reloj.precio);
        const estrellas = this.crearEstrellasHTML(reloj.calificacion_promedio);
        
        return `
            <div class="contenedor-card">
                <div class="cuadro-card">
                    <div class="vendido-badge">VENDIDO</div>
                    <img src="../img/${reloj.imagen_principal}" class="zoom-img" alt="${reloj.nombre}" loading="lazy">
                    <div class="texto-card">
                        <h3>${this.escapeHtml(reloj.nombre)}</h3>
                        <div class="precio">
                            <p>${precioFormateado}</p>
                        </div>
                        <div class="stats-vendido">
                            <div class="stat-item">
                                <i class="fas fa-comments"></i>
                                <span>${reloj.total_comentarios} comentarios</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-star"></i>
                                <span>${estrellas}</span>
                            </div>
                        </div>
                        <div class="boton-wh">
                            <button class="btn-whatsapp" onclick="verDetalle(${reloj.id_reloj})">Ver Detalle</button>
                            <svg class="ornamento" width="60" height="10" viewBox="0 0 60 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <line x1="0" y1="5" x2="20" y2="5" stroke="#FFCF66" stroke-width="1" opacity="0.3"/>
                                <polygon points="27,5 30,0 33,5 30,10" fill="#FFCF66"/>
                                <polygon points="20,5 22,2.5 24,5 22,7.5" fill="#FFCF66"/>
                                <polygon points="36,5 38,2.5 40,5 38,7.5" fill="#FFCF66"/>
                                <line x1="40" y1="5" x2="60" y2="5" stroke="#FFCF66" stroke-width="1" opacity="0.3"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }


    crearEstrellasHTML(calificacion) {
        const calificacionNum = parseFloat(calificacion) || 0;
        let estrellas = '';
        
        for (let i = 1; i <= 5; i++) {
            const clase = i <= calificacionNum ? 'estrella-llena' : 'estrella-vacia';
            estrellas += `<span class="estrella ${clase}">★</span>`;
        }
        
        return estrellas + ` <span class="calificacion-numero">(${calificacionNum.toFixed(1)})</span>`;
    }

    formatearPrecio(precio) {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0
        }).format(precio);
    }

    mostrarError(mensaje) {
        const container = document.getElementById('contenedor-vendidos');
        container.innerHTML = `
            <div class="error-vendidos">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Error</h3>
                <p>${mensaje}</p>
                <button onclick="sistemaVendidos.cargarVendidos()" class="btn-reintentar">
                    <i class="fas fa-redo"></i> Reintentar
                </button>
            </div>
        `;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Funciones globales
function verDetalle(idReloj) {
    window.location.href = `../informacion/informacion.html?id=${idReloj}`;
}

// Función para cargar vendidos directamente
async function cargarVendidos() {
    console.log('🔄 Cargando productos vendidos...');
    try {
        const response = await fetch('php/obtener_vendidos.php');
        const data = await response.json();
        
        console.log('📊 Datos recibidos:', data);
        
        if (data.success && data.vendidos.length > 0) {
            const container = document.getElementById('contenedor-relojes');
            if (container) {
                // Crear indicador
                const indicador = document.createElement('div');
                indicador.className = 'vendidos-indicador';
                indicador.innerHTML = '<span class="vendidos-texto">Productos Vendidos</span>';
                
                // Crear HTML de productos
                const htmlVendidos = data.vendidos.map(reloj => {
                    const precioFormateado = new Intl.NumberFormat('es-CO', {
                        style: 'currency',
                        currency: 'COP',
                        minimumFractionDigits: 0
                    }).format(reloj.precio);
                    
                    return `
                        <div class="contenedor-card">
                            <div class="cuadro-card">
                                <div class="vendido-badge">VENDIDO</div>
                                <img src="../img/${reloj.imagen_principal}" class="zoom-img" alt="${reloj.nombre}" loading="lazy">
                                <div class="texto-card">
                                    <h3>${reloj.nombre}</h3>
                                    <div class="precio">
                                        <p>${precioFormateado}</p>
                                    </div>
                                    <div class="stats-vendido">
                                        <div class="stat-item">
                                            <i class="fas fa-comments"></i>
                                            <span>${reloj.total_comentarios} comentarios</span>
                                        </div>
                                        <div class="stat-item">
                                            <i class="fas fa-star"></i>
                                            <span>${reloj.calificacion_promedio} ⭐</span>
                                        </div>
                                    </div>
                                    <div class="boton-wh">
                                        <button class="btn-whatsapp" onclick="verDetalle(${reloj.id_reloj})">Ver Detalle</button>
                                        <svg class="ornamento" width="60" height="10" viewBox="0 0 60 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <line x1="0" y1="5" x2="20" y2="5" stroke="#FFCF66" stroke-width="1" opacity="0.3"/>
                                            <polygon points="27,5 30,0 33,5 30,10" fill="#FFCF66"/>
                                            <polygon points="20,5 22,2.5 24,5 22,7.5" fill="#FFCF66"/>
                                            <polygon points="36,5 38,2.5 40,5 38,7.5" fill="#FFCF66"/>
                                            <line x1="40" y1="5" x2="60" y2="5" stroke="#FFCF66" stroke-width="1" opacity="0.3"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
                
                // Agregar al contenedor
                container.appendChild(indicador);
                container.insertAdjacentHTML('beforeend', htmlVendidos);
                
                console.log('✅ Productos vendidos agregados');
            } else {
                console.error('❌ No se encontró el contenedor');
            }
        } else {
            console.log('ℹ️ No hay productos vendidos');
        }
    } catch (error) {
        console.error('❌ Error al cargar vendidos:', error);
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM cargado, cargando vendidos...');
    cargarVendidos();
});

// También intentar cargar después de un pequeño delay
setTimeout(() => {
    console.log('⏰ Intentando cargar vendidos después de delay...');
    cargarVendidos();
}, 2000);
