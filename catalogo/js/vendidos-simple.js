// Script simple para cargar productos vendidos
console.log('🔄 Iniciando carga de productos vendidos...');

async function cargarVendidosSimple() {
    try {
        console.log('📡 Haciendo petición a obtener_vendidos.php...');
        const response = await fetch('php/obtener_vendidos.php');
        console.log('📊 Respuesta recibida:', response.status);
        
        const data = await response.json();
        console.log('📋 Datos:', data);
        
        if (data.success && data.vendidos && data.vendidos.length > 0) {
            console.log(`✅ Encontrados ${data.vendidos.length} productos vendidos`);
            
            const container = document.getElementById('contenedor-relojes');
            if (container) {
                console.log('🎯 Contenedor encontrado, agregando productos...');
                
                // Crear indicador
                const indicador = document.createElement('div');
                indicador.className = 'vendidos-indicador';
                indicador.innerHTML = '<span class="vendidos-texto">Productos Vendidos</span>';
                
                // Crear productos usando la estructura exacta del PHP
                const htmlVendidos = data.vendidos.map(reloj => {
                    const precio = Math.floor(reloj.precio / 1000);
                    const precioFormateado = precio.toLocaleString('es-CO');
                    
                    return `
                        <div class="contenedor-card" data-marca="${reloj.marca || 'N/A'}" data-precio="${reloj.precio}">
                            <div class="cuadro-card vendido" data-reloj-id="${reloj.id_reloj}">
                                <div class="badge-vendido">VENDIDO</div>
                                <img src="../img/${reloj.imagen_principal}" class="zoom-img">
                                <div class="hover-overlay">
                                    <div class="hover-content">
                                        <div class="hover-title">${reloj.marca || 'Reloj'}</div>
                                        <div class="hover-details">
                                            <div class="detail-item">
                                                <span class="detail-label">Precio:</span>
                                                <span class="detail-value">$${precioFormateado}.000</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="hover-buttons">
                                        <div class="vendido-message">Este reloj ya fue vendido</div>
                                    </div>
                                </div>
                            </div>
                            <div class="texto-card">
                                <h3>${reloj.nombre}</h3>
                            </div>
                        </div>
                    `;
                }).join('');
                
                // Agregar al contenedor
                container.appendChild(indicador);
                container.insertAdjacentHTML('beforeend', htmlVendidos);
                
                console.log('✅ Productos vendidos agregados exitosamente');
            } else {
                console.error('❌ No se encontró el contenedor con ID "contenedor-relojes"');
            }
        } else {
            console.log('ℹ️ No hay productos vendidos para mostrar');
        }
    } catch (error) {
        console.error('❌ Error:', error);
    }
}

// Función global para ver detalle
function verDetalle(idReloj) {
    window.location.href = `../informacion/informacion.html?id=${idReloj}`;
}

// Ejecutar inmediatamente
console.log('🚀 Ejecutando carga de vendidos...');
cargarVendidosSimple();

// También ejecutar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM listo, ejecutando carga de vendidos...');
    cargarVendidosSimple();
});
