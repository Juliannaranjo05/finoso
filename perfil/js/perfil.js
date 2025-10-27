/**
 * PERFIL DE USUARIO - FINOSO
 * Gestión del perfil y historial de compras
 */

// Variable global para almacenar los datos del usuario
let datosUsuario = {};

document.addEventListener('DOMContentLoaded', function() {
    // Verificar sesión activa
    fetch('http://127.0.0.1/finoso/login/php/verificar_sesion.php')
        .then(res => res.json())
        .then(data => {
            if (!data.logged_in) {
                // Si no hay sesión, redirigir al login
                window.location.href = 'http://127.0.0.1/finoso/login/login.html';
            } else {
                // Guardar datos del usuario
                datosUsuario = data;
                
                // Cargar información del usuario
                document.getElementById('nombreUsuario').textContent = data.nombre;
                document.getElementById('correoUsuario').textContent = data.correo || '';
                
                // Cargar historial y códigos
                cargarHistorialCompras();
                cargarCodigosDescuento();
            }
        })
        .catch(error => {
            console.error('Error al verificar sesión:', error);
            window.location.href = 'http://127.0.0.1/finoso/login/login.html';
        });
    
    // Cerrar sesión
    const cerrarSesionBtn = document.getElementById('cerrarSesionBtn');
    if (cerrarSesionBtn) {
        cerrarSesionBtn.addEventListener('click', function() {
            if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
                fetch('http://127.0.0.1/finoso/login/php/logout.php')
                    .then(() => {
                        window.location.href = 'http://127.0.0.1/finoso/index.html';
                    });
            }
        });
    }
});

// Función para cargar historial de compras
async function cargarHistorialCompras() {
    const loading = document.getElementById('historialLoading');
    const empty = document.getElementById('historialEmpty');
    const lista = document.getElementById('historialLista');
    
    loading.style.display = 'flex';
    empty.style.display = 'none';
    lista.innerHTML = '';
    
    try {
        const response = await fetch('http://127.0.0.1/finoso/login/php/obtener_historial_usuario.php');
        const data = await response.json();
        
        loading.style.display = 'none';
        
        if (data.success && data.ordenes && data.ordenes.length > 0) {
            // Mostrar órdenes
            lista.innerHTML = data.ordenes.map(orden => crearCardOrden(orden)).join('');
            
            // Actualizar estadísticas
            document.getElementById('totalOrdenes').textContent = data.stats.total_ordenes || 0;
            document.getElementById('totalRelojes').textContent = data.stats.total_relojes || 0;
            document.getElementById('totalGastado').textContent = '$' + formatearPrecio(data.stats.total_gastado || 0);
        } else {
            empty.style.display = 'flex';
        }
    } catch (error) {
        console.error('Error al cargar historial:', error);
        loading.style.display = 'none';
        empty.style.display = 'flex';
        empty.querySelector('p').textContent = 'Error al cargar el historial. Intenta de nuevo más tarde.';
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
                        <img src="http://127.0.0.1/finoso/${orden.imagen}" alt="${orden.nombre_reloj}" class="reloj-img" onerror="this.parentElement.innerHTML='<div class=\\'reloj-img-placeholder\\'><svg xmlns=\\'http://www.w3.org/2000/svg\\' viewBox=\\'0 0 24 24\\' fill=\\'currentColor\\'><circle cx=\\'12\\' cy=\\'12\\' r=\\'10\\' stroke=\\'currentColor\\' stroke-width=\\'2\\' fill=\\'none\\'/><path d=\\'M12 6v6l4 2\\'/></svg></div>';">
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
                        ${orden.marca ? `<p class="reloj-marca">${orden.marca}</p>` : ''}
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
            
            ${generarBotonesAccion(orden)}
            
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

// Generar botones de acción según estado de la orden
function generarBotonesAccion(orden) {
    let botones = '';
    
    // Botón Rastrear Pedido (solo si está enviado y tiene guía)
    if (orden.estado === 'enviado' && orden.guia_envio) {
        botones += `
            <button class="btn-accion btn-rastrear" onclick="rastrearPedido('${orden.guia_envio}', '${orden.transportadora || 'SERVIENTREGA'}')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                    <path d="M18 18.5c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5-1.5.67-1.5 1.5.67 1.5 1.5 1.5zm1.5-9H17V12h4.46L19.5 9.5zM6 18.5c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5-1.5.67-1.5 1.5.67 1.5 1.5 1.5zM20 8l3 4v5h-2c0 1.66-1.34 3-3 3s-3-1.34-3-3H9c0 1.66-1.34 3-3 3s-3-1.34-3-3H1V6c0-1.1.9-2 2-2h14v4h3zM8 6H4v6h4V6z"/>
                </svg>
                Rastrear Pedido
            </button>
        `;
    }
    
    // Botón Comprar de Nuevo (solo si la orden fue exitosa)
    if (['pagado', 'aprobado', 'enviado', 'entregado'].includes(orden.estado) && orden.marca) {
        botones += `
            <button class="btn-accion btn-recomprar" onclick="comprarDeNuevo('${encodeURIComponent(orden.marca)}')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                    <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
                Comprar de Nuevo
            </button>
        `;
    }
    
    // Botón para órdenes rechazadas: solo contactar soporte por WhatsApp
    if (orden.estado === 'rechazado' && orden.motivo_rechazo) {
        const token = orden.token_verificacion || '';
        botones += `
            <button class="btn-accion btn-whatsapp" onclick="contactarSoporteRechazo(${orden.id_orden}, '${encodeURIComponent(orden.nombre_reloj)}', '${encodeURIComponent(orden.motivo_rechazo)}', '${encodeURIComponent(token)}')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                Contactar Soporte
            </button>
        `;
    }
    
    if (botones) {
        return `<div class="orden-footer">${botones}</div>`;
    }
    
    return '';
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

// Función para comprar de nuevo (filtra por marca en catálogo)
function comprarDeNuevo(marca) {
    if (marca && marca !== 'null' && marca !== 'undefined') {
        window.location.href = `../catalogo/catalogo.html?marca=${encodeURIComponent(marca)}`;
    } else {
        window.location.href = '../catalogo/catalogo.html';
    }
}

// ========================================
// FUNCIONES DE SOPORTE
// ========================================

// Contactar soporte por WhatsApp para órdenes rechazadas
function contactarSoporteRechazo(idOrden, nombreReloj, motivoRechazo, token) {
    const tokenDecoded = decodeURIComponent(token || '');
    const tokenShort = tokenDecoded ? tokenDecoded.substring(0, 16) + '...' : 'No disponible';
    
    const mensaje = `Hola! 👋\n\n` +
                   `Mi orden #${idOrden} fue rechazada y necesito ayuda.\n\n` +
                   `Reloj: ${decodeURIComponent(nombreReloj)}\n` +
                   `Motivo: ${decodeURIComponent(motivoRechazo)}\n` +
                   `Token: ${tokenShort}\n\n` +
                   `¿Pueden ayudarme a resolver este problema?`;
    
    const numeroWhatsApp = '+573173897119'; // Número de FINOSO
    const url = `https://wa.me/${numeroWhatsApp}?text=${encodeURIComponent(mensaje)}`;
    window.open(url, '_blank');
}

// ========================================
// EDITAR PERFIL
// ========================================

// Abrir modal de editar perfil
function abrirModalEditarPerfil() {
    const modal = document.getElementById('modalEditarPerfil');
    const inputNombre = document.getElementById('editNombre');
    
    // Verificar que los elementos existan
    if (!modal || !inputNombre) {
        console.error('No se encontraron los elementos del modal de edición');
        return;
    }
    
    // Cargar datos actuales en el formulario
    inputNombre.value = datosUsuario.nombre || '';
    
    // Mostrar modal
    modal.classList.add('active');
    modal.style.display = 'flex';
    
    // Cerrar al hacer clic fuera del modal
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            cerrarModalEditarPerfil();
        }
    });
}

// Cerrar modal de editar perfil
function cerrarModalEditarPerfil() {
    const modal = document.getElementById('modalEditarPerfil');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
}

// Guardar cambios del perfil
function guardarCambiosPerfil(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const nombre = formData.get('nombre');
    
    // Validaciones adicionales
    if (!nombre || nombre.trim().length < 3) {
        alert('⚠️ El nombre debe tener al menos 3 caracteres');
        return;
    }
    
    // Deshabilitar botón mientras se procesa
    const btnGuardar = event.target.querySelector('.btn-guardar');
    const textoOriginal = btnGuardar.innerHTML;
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18" style="animation: spin 1s linear infinite;">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" opacity=".3"/>
            <path d="M12 2C6.48 2 2 6.48 2 12h2c0-4.41 3.59-8 8-8s8 3.59 8 8 3.59 8 8 8v2c-5.52 0-10-4.48-10-10S6.48 2 12 2z"/>
        </svg>
        Guardando...
    `;
    
    // Enviar al backend
    fetch('php/actualizar_perfil.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Actualizar datos en la página
            datosUsuario.nombre = nombre;
            
            document.getElementById('nombreUsuario').textContent = nombre;
            
            // Cerrar modal
            cerrarModalEditarPerfil();
            
            // Mostrar mensaje de éxito
            alert('✅ Perfil actualizado correctamente');
        } else {
            alert('❌ Error: ' + (data.message || 'No se pudo actualizar el perfil'));
        }
    })
    .catch(error => {
        console.error('Error al actualizar perfil:', error);
        alert('❌ Error al conectar con el servidor');
    })
    .finally(() => {
        // Restaurar botón
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
    });
}

// ========================================
// CÓDIGOS DE DESCUENTO
// ========================================

// Función para cargar códigos de descuento del usuario
async function cargarCodigosDescuento() {
    const loading = document.getElementById('codigosLoading');
    const empty = document.getElementById('codigosEmpty');
    const lista = document.getElementById('codigosLista');
    
    loading.style.display = 'flex';
    empty.style.display = 'none';
    lista.innerHTML = '';
    
    try {
        const response = await fetch('php/obtener_codigos_usuario.php');
        const data = await response.json();
        
        loading.style.display = 'none';
        
        if (data.success && data.codigos && data.codigos.length > 0) {
            // Mostrar códigos
            lista.innerHTML = data.codigos.map(codigo => crearCardCodigo(codigo)).join('');
            
            // Agregar eventos a los botones de copiar
            document.querySelectorAll('.codigo-copiar').forEach(btn => {
                btn.addEventListener('click', function() {
                    const codigo = this.getAttribute('data-codigo');
                    copiarCodigo(codigo, this);
                });
            });
        } else {
            empty.style.display = 'flex';
        }
    } catch (error) {
        console.error('Error al cargar códigos:', error);
        loading.style.display = 'none';
        empty.style.display = 'flex';
        empty.querySelector('p').textContent = 'Error al cargar los códigos. Intenta de nuevo más tarde.';
    }
}

// Función para crear tarjeta de código
function crearCardCodigo(codigo) {
    const estadoClass = codigo.estado === 'usado' ? 'usado' : (codigo.estado === 'expirado' ? 'expirado' : '');
    const badgeClass = codigo.estado === 'usado' ? 'usado' : (codigo.estado === 'expirado' ? 'expirado' : 'disponible');
    const badgeTexto = codigo.estado === 'usado' ? 'Usado' : (codigo.estado === 'expirado' ? 'Expirado' : 'Disponible');
    
    let fechaExpiraHtml = '';
    if (codigo.fecha_expiracion) {
        const fechaExp = new Date(codigo.fecha_expiracion);
        const fechaFormateada = fechaExp.toLocaleDateString('es-CO', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const urgente = codigo.dias_para_expirar !== null && codigo.dias_para_expirar > 0 && codigo.dias_para_expirar <= 3;
        
        if (codigo.estado === 'expirado') {
            fechaExpiraHtml = `<div class="codigo-fecha-expira urgente">❌ Expiró el ${fechaFormateada}</div>`;
        } else if (urgente) {
            fechaExpiraHtml = `<div class="codigo-fecha-expira urgente">⚠️ Expira en ${codigo.dias_para_expirar} día${codigo.dias_para_expirar !== 1 ? 's' : ''}</div>`;
        } else if (codigo.dias_para_expirar !== null) {
            fechaExpiraHtml = `<div class="codigo-fecha-expira">📅 Válido hasta el ${fechaFormateada}</div>`;
        } else {
            fechaExpiraHtml = `<div class="codigo-fecha-expira">✨ Sin fecha de expiración</div>`;
        }
    }
    
    const botonCopiar = codigo.estado !== 'expirado' && codigo.estado !== 'usado' 
        ? `<button class="codigo-copiar" data-codigo="${codigo.codigo}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                </svg>
                Copiar
            </button>`
        : '';
    
    return `
        <div class="codigo-card ${estadoClass}">
            <span class="codigo-badge ${badgeClass}">${badgeTexto}</span>
            
            <div class="codigo-principal">
                <div class="codigo-texto">${codigo.codigo}</div>
                ${botonCopiar}
            </div>
            
            <div class="codigo-detalles">
                <div class="codigo-detalle-row">
                    <span class="codigo-detalle-label">Descuento:</span>
                    <span class="codigo-detalle-valor descuento">${codigo.porcentaje}%</span>
                </div>
                ${codigo.fecha_usado ? `
                <div class="codigo-detalle-row">
                    <span class="codigo-detalle-label">Usado el:</span>
                    <span class="codigo-detalle-valor">${new Date(codigo.fecha_usado).toLocaleDateString('es-CO')}</span>
                </div>
                ` : ''}
            </div>
            
            ${fechaExpiraHtml}
            
            ${codigo.notas ? `
            <div style="margin-top: 15px; padding: 10px; background: rgba(255, 207, 102, 0.1); border-radius: 8px; font-size: 12px; color: rgba(255, 255, 255, 0.7);">
                💡 ${codigo.notas}
            </div>
            ` : ''}
        </div>
    `;
}

// Función para copiar código al portapapeles
function copiarCodigo(codigo, boton) {
    navigator.clipboard.writeText(codigo).then(() => {
        // Cambiar el texto del botón temporalmente
        const textoOriginal = boton.innerHTML;
        boton.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
            </svg>
            ¡Copiado!
        `;
        boton.style.background = 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)';
        
        // Restaurar después de 2 segundos
        setTimeout(() => {
            boton.innerHTML = textoOriginal;
            boton.style.background = '';
        }, 2000);
    }).catch(err => {
        console.error('Error al copiar código:', err);
        alert('❌ No se pudo copiar el código');
    });
}

