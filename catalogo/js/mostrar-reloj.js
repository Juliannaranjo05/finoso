const entornoCatalogo = (() => {
    let currentScript = document.currentScript;

    if (!currentScript || !currentScript.src) {
        const scripts = document.getElementsByTagName('script');
        for (let i = scripts.length - 1; i >= 0; i--) {
            const script = scripts[i];
            if (script.src && script.src.indexOf('mostrar-reloj.js') !== -1) {
                currentScript = script;
                break;
            }
        }
    }

    let baseUrl = `${window.location.origin}/`;

    if (currentScript && currentScript.src) {
        const scriptUrl = new URL(currentScript.src, window.location.href);
        let basePathRaw = '';

        if (scriptUrl.pathname.includes('/catalogo/js/')) {
            [basePathRaw] = scriptUrl.pathname.split('/catalogo/js/');
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

        baseUrl = `${scriptUrl.origin}${normalizedBasePath}`;
    }

    const buildUrl = (relativePath = '') => {
        const sanitizedPath = (relativePath || '').replace(/^\/+/, '');
        return new URL(sanitizedPath, baseUrl).href;
    };

    return {
        buildUrl
    };
})();

const buildAssetUrlCatalogo = (assetPath) => {
    if (!assetPath) return '';
    if (/^https?:\/\//i.test(assetPath)) {
        return assetPath;
    }
    return entornoCatalogo.buildUrl(assetPath);
};

document.addEventListener('DOMContentLoaded', () => {
    // Cargar marcas primero, luego relojes
    cargarMarcas().then(() => {
        cargarRelojes();
        
        // Verificar si hay parámetro de marca en la URL (desde "Ver Similares")
        aplicarFiltroDesdeURL();
    });
    
    // Event listeners para filtros
    document.getElementById('filtro-precio').addEventListener('change', aplicarFiltros);
    document.getElementById('filtro-marca').addEventListener('change', aplicarFiltros);
    document.getElementById('filtro-movimiento').addEventListener('change', aplicarFiltros);
    document.getElementById('filtro-pulsera').addEventListener('change', aplicarFiltros);
    
    // Actualizar contador del carrito al cargar la página
    actualizarContadorCarrito();
});

function cargarMarcas() {
    console.log('🔍 Cargando marcas...');
    return fetch(entornoCatalogo.buildUrl('admin/php/obtener_marcas.php'))
        .then(response => {
            console.log('📡 Respuesta de marcas:', response);
            return response.json();
        })
        .then(data => {
            console.log('📋 Datos de marcas:', data);
            if (data.success) {
                const select = document.getElementById('filtro-marca');
                console.log('🎯 Select encontrado:', select);
                
                data.marcas.forEach(marca => {
                    const option = document.createElement('option');
                    option.value = marca.nombre;
                    option.textContent = marca.nombre;
                    select.appendChild(option);
                });
                console.log('✅ Marcas cargadas:', data.marcas.length);
            }
        })
        .catch(error => {
            console.error('❌ Error al cargar marcas:', error);
        });
}

// Función para cargar movimientos únicos
function cargarMovimientos() {
    console.log('🔧 DEBUG: Iniciando cargarMovimientos()');
    const tarjetas = document.querySelectorAll('.contenedor-card');
    console.log('🔧 DEBUG: Tarjetas encontradas:', tarjetas.length);
    const movimientos = new Set();
    
    tarjetas.forEach((tarjeta, index) => {
        const movimiento = tarjeta.getAttribute('data-movimiento');
        console.log(`🔧 DEBUG: Tarjeta ${index} - movimiento="${movimiento}"`);
        if (movimiento && movimiento.trim() !== '' && movimiento !== 'null') {
            console.log(`🔧 DEBUG: ✅ Agregando movimiento: "${movimiento}"`);
            movimientos.add(movimiento);
        }
    });
    
    console.log('🔧 DEBUG: Total movimientos únicos encontrados:', movimientos.size);
    console.log('🔧 DEBUG: Movimientos:', Array.from(movimientos));
    
    const select = document.getElementById('filtro-movimiento');
    console.log('🔧 DEBUG: Select encontrado:', select ? 'SÍ' : 'NO');
    if (!select) {
        console.error('❌ ERROR: No se encontró el select #filtro-movimiento');
        return;
    }
    
    // Limpiar opciones existentes (excepto la primera)
    while (select.options.length > 1) {
        select.remove(1);
    }
    
    const sortedMovimientos = Array.from(movimientos).sort();
    
    sortedMovimientos.forEach(mov => {
        const option = document.createElement('option');
        option.value = mov;
        option.textContent = `⚙️ ${mov}`;
        select.appendChild(option);
    });
    
    console.log('✅ Movimientos cargados:', sortedMovimientos.length);
}

// Función para cargar tipos de pulsera únicos
function cargarPulseras() {
    const tarjetas = document.querySelectorAll('.contenedor-card');
    const pulseras = new Set();
    
    tarjetas.forEach(tarjeta => {
        const pulsera = tarjeta.getAttribute('data-pulsera');
        if (pulsera && pulsera.trim() !== '' && pulsera !== 'null') {
            pulseras.add(pulsera);
        }
    });
    
    const select = document.getElementById('filtro-pulsera');
    if (!select) return;
    
    // Limpiar opciones existentes (excepto la primera)
    while (select.options.length > 1) {
        select.remove(1);
    }
    
    const sortedPulseras = Array.from(pulseras).sort();
    
    sortedPulseras.forEach(pul => {
        const option = document.createElement('option');
        option.value = pul;
        option.textContent = `📿 ${pul}`;
        select.appendChild(option);
    });
    
    console.log('✅ Pulseras cargadas:', sortedPulseras.length);
}

function cargarRelojes() {
    console.log('🕐 Cargando relojes...');
    fetch(entornoCatalogo.buildUrl('catalogo/php/mostrar_relojes.php'))
        .then(response => {
            if (!response.ok) throw new Error('Error al cargar los relojes');
            return response.text();
        })
        .then(data => {
            console.log('📦 Datos de relojes recibidos:', data.length, 'caracteres');
            console.log('🔍 Contenido HTML:', data);
            document.getElementById('contenedor-relojes').innerHTML = data;
            
            // Verificar que las tarjetas tengan los atributos necesarios
            const tarjetas = document.querySelectorAll('.contenedor-card');
            console.log('🎯 Tarjetas cargadas:', tarjetas.length);
            
            tarjetas.forEach((tarjeta, index) => {
                const marca = tarjeta.getAttribute('data-marca');
                const precio = tarjeta.getAttribute('data-precio');
                console.log(`📱 Tarjeta ${index}: marca="${marca}", precio="${precio}"`);
            });
            
            // Ajustar layout para pocos relojes
            ajustarLayoutRelojes(tarjetas.length);
            
            // Cargar opciones de filtros avanzados
            cargarMovimientos();
            cargarPulseras();
            
            // Guardar los datos originales para filtrado
            window.relojesOriginales = data;
        })
        .catch(error => {
            console.error('❌ Hubo un problema al cargar los relojes:', error);
        });
}

function aplicarFiltros() {
    const filtroPrecio = document.getElementById('filtro-precio').value;
    const filtroMarca = document.getElementById('filtro-marca').value;
    const filtroMovimiento = document.getElementById('filtro-movimiento').value;
    const filtroPulsera = document.getElementById('filtro-pulsera').value;
    
    console.log('🔍 Aplicando filtros:', { filtroPrecio, filtroMarca, filtroMovimiento, filtroPulsera });
    
    // Obtener todas las tarjetas
    const tarjetas = document.querySelectorAll('.contenedor-card');
    console.log('📋 Tarjetas encontradas:', tarjetas.length);
    
    tarjetas.forEach((tarjeta, index) => {
        let mostrar = true;
        
        // NUEVO: Excluir relojes vendidos cuando hay filtros activos
        const cuadroCard = tarjeta.querySelector('.cuadro-card');
        const esVendido = cuadroCard && cuadroCard.classList.contains('vendido');
        
        // Si hay algún filtro activo, excluir vendidos
        if ((filtroPrecio || filtroMarca || filtroMovimiento || filtroPulsera) && esVendido) {
            mostrar = false;
            console.log(`❌ Tarjeta ${index}: VENDIDO - excluido del filtro`);
        }
        
        // Filtrar por precio (solo si no está vendido)
        if (filtroPrecio && mostrar) {
            const precioStr = tarjeta.getAttribute('data-precio');
            const descuentoStr = tarjeta.getAttribute('data-descuento');
            const precioOriginal = parseInt(precioStr);
            const descuento = parseFloat(descuentoStr) || 0;
            
            // Calcular precio final con descuento (si aplica)
            let precioFinal = precioOriginal;
            if (descuento > 0) {
                // Si el descuento es menor a 1, es decimal (0.16 = 16%)
                const descuentoPorcentaje = descuento < 1 ? descuento * 100 : descuento;
                precioFinal = precioOriginal - (precioOriginal * (descuentoPorcentaje / 100));
            }
            
            const [min, max] = filtroPrecio.split('-').map(Number);
            
            console.log(`💰 Tarjeta ${index}: precioOriginal=${precioOriginal}, descuento=${descuento}%, precioFinal=${precioFinal}, rango=${min}-${max}`);
            
            if (precioFinal < min || precioFinal > max) {
                mostrar = false;
            }
        }
        
        // Filtrar por marca (solo si no está vendido)
        if (filtroMarca && mostrar) {
            const marca = tarjeta.getAttribute('data-marca');
            console.log(`⌚ Tarjeta ${index}: marca="${marca}", filtro="${filtroMarca}"`);
            
            if (marca !== filtroMarca) {
                mostrar = false;
            }
        }
        
        // Filtrar por movimiento
        if (filtroMovimiento && mostrar) {
            const movimiento = tarjeta.getAttribute('data-movimiento');
            console.log(`⚙️ Tarjeta ${index}: movimiento="${movimiento}", filtro="${filtroMovimiento}"`);
            
            if (movimiento !== filtroMovimiento) {
                mostrar = false;
            }
        }
        
        // Filtrar por pulsera
        if (filtroPulsera && mostrar) {
            const pulsera = tarjeta.getAttribute('data-pulsera');
            console.log(`📿 Tarjeta ${index}: pulsera="${pulsera}", filtro="${filtroPulsera}"`);
            
            if (pulsera !== filtroPulsera) {
                mostrar = false;
            }
        }
        
        console.log(`📱 Tarjeta ${index}: mostrar=${mostrar}`);
        
        // Mostrar u ocultar tarjeta
        tarjeta.style.display = mostrar ? 'block' : 'none';
    });
    
    // Ajustar layout después del filtrado
    const tarjetasVisibles = Array.from(tarjetas).filter(tarjeta => 
        tarjeta.style.display !== 'none'
    );
    ajustarLayoutRelojes(tarjetasVisibles.length);
    
    // Verificar si hay relojes vendidos visibles
    const vendidosVisibles = Array.from(tarjetas).filter(tarjeta => {
        const cuadroCard = tarjeta.querySelector('.cuadro-card');
        const esVendido = cuadroCard && cuadroCard.classList.contains('vendido');
        return esVendido && tarjeta.style.display !== 'none';
    });
    
    // Ocultar/mostrar el indicador "LO MÁS DESEADO" según haya vendidos visibles
    const indicadorVendidos = document.querySelector('.vendidos-indicador');
    if (indicadorVendidos) {
        if (vendidosVisibles.length === 0) {
            indicadorVendidos.style.display = 'none';
            console.log('🚫 Indicador "LO MÁS DESEADO" oculto (no hay vendidos visibles)');
        } else {
            indicadorVendidos.style.display = 'block';
            console.log('✅ Indicador "LO MÁS DESEADO" visible (' + vendidosVisibles.length + ' vendidos)');
        }
    }
    
    // Mostrar mensaje si no hay resultados
    mostrarMensajeResultados(tarjetas);
}


function mostrarMensajeResultados(tarjetas) {
    const tarjetasVisibles = Array.from(tarjetas).filter(tarjeta => 
        tarjeta.style.display !== 'none'
    );
    
    // Remover mensaje anterior si existe
    ocultarMensajeResultados();
    
    if (tarjetasVisibles.length === 0) {
        const contenedor = document.getElementById('contenedor-relojes');
        const filtroMarca = document.getElementById('filtro-marca').value;
        
        // Mensaje personalizado si vienen desde "Ver Similares"
        const urlParams = new URLSearchParams(window.location.search);
        const marcaParam = urlParams.get('marca');
        
        let mensajeTitulo = '🔍 No se encontraron relojes';
        let mensajeTexto = 'No hay relojes disponibles que coincidan con los filtros seleccionados.';
        
        if (marcaParam && filtroMarca) {
            mensajeTitulo = '😔 No hay relojes similares disponibles';
            mensajeTexto = `Actualmente no tenemos relojes de la marca <strong>${filtroMarca}</strong> disponibles en stock. <br><br>💬 ¡Contáctanos! Podemos conseguir el reloj que buscas.`;
        }
        
        const mensaje = document.createElement('div');
        mensaje.className = 'mensaje-sin-resultados';
        mensaje.innerHTML = `
            <div class="mensaje-contenido">
                <h3>${mensajeTitulo}</h3>
                <p>${mensajeTexto}</p>
            </div>
        `;
        contenedor.appendChild(mensaje);
    }
}

function ocultarMensajeResultados() {
    const mensaje = document.querySelector('.mensaje-sin-resultados');
    if (mensaje) {
        mensaje.remove();
    }
}

function ajustarLayoutRelojes(cantidadRelojes) {
    const contenedor = document.getElementById('contenedor-relojes');
    const tarjetas = document.querySelectorAll('.contenedor-card');
    
    // Remover clases anteriores
    contenedor.classList.remove('pocos-relojes', 'un-reloj', 'dos-relojes');
    
    if (cantidadRelojes === 1) {
        contenedor.classList.add('pocos-relojes', 'un-reloj');
    } else if (cantidadRelojes === 2) {
        contenedor.classList.add('pocos-relojes', 'dos-relojes');
    }
    // Para 3+ relojes no agregamos clases especiales, usa el grid normal
}

// Funciones para el Historial de Éxitos
function buscarSimilares(idReloj) {
    console.log('🔍 Buscando relojes similares para:', idReloj);
    // Por ahora, redirigir al catálogo con filtros aplicados
    window.location.href = 'catalogo.html?similar=' + idReloj;
}

// Nueva función para buscar similares por marca
function buscarSimilaresPorMarca(button) {
    const marca = button.getAttribute('data-marca');
    console.log('🔍 Buscando relojes similares de la marca:', marca);
    
    // Redirigir al catálogo con el parámetro de marca
    window.location.href = 'catalogo.html?marca=' + encodeURIComponent(marca);
}

// Función para aplicar filtro desde parámetros de URL
function aplicarFiltroDesdeURL() {
    // Obtener parámetros de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const marcaParam = urlParams.get('marca');
    
    console.log('🔗 Parámetros de URL:', { marca: marcaParam });
    
    if (marcaParam) {
        // Auto-seleccionar la marca en el filtro
        const filtroMarca = document.getElementById('filtro-marca');
        
        // Buscar la opción que coincida con la marca
        for (let i = 0; i < filtroMarca.options.length; i++) {
            if (filtroMarca.options[i].value === marcaParam) {
                filtroMarca.selectedIndex = i;
                console.log('✅ Marca auto-seleccionada:', marcaParam);
                
                // NUEVO: Limpiar la URL después de aplicar el filtro inicial
                // Esto evita que se vuelva a aplicar el filtro al recargar la página
                const nuevaURL = window.location.pathname;
                window.history.replaceState({}, document.title, nuevaURL);
                console.log('🧹 URL limpiada - parámetro removido');
                
                // Aplicar los filtros automáticamente
                setTimeout(() => {
                    aplicarFiltros();
                    
                    // Scroll suave al catálogo
                    const contenedorRelojes = document.getElementById('contenedor-relojes');
                    if (contenedorRelojes) {
                        contenedorRelojes.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 300); // Pequeño delay para asegurar que todo esté cargado
                
                break;
            }
        }
    }
}

// Nueva función para contactar por WhatsApp directamente (usando data attributes)
function contactarWhatsAppVendido(button) {
    // Obtener datos del botón de forma segura
    const idReloj = button.getAttribute('data-reloj-id');
    const nombreReloj = button.getAttribute('data-reloj-nombre');
    
    // DEBUG: Ver qué datos estamos obteniendo
    console.log('🔍 DEBUG - Button:', button);
    console.log('🔍 DEBUG - ID Reloj:', idReloj);
    console.log('🔍 DEBUG - Nombre Reloj:', nombreReloj);
    console.log('🔍 DEBUG - Todos los atributos:', button.attributes);
    
    // Verificar que tengamos los datos
    if (!idReloj || !nombreReloj) {
        console.error('❌ ERROR: Faltan datos del reloj');
        alert('Error: No se pudo obtener la información del reloj. Por favor contacta directamente.');
        return;
    }
    
    console.log('💬 Contactando por WhatsApp para:', nombreReloj);
    
    // Número de WhatsApp del negocio
    const numeroNegocio = '573173897119';
    
    // Mensaje predeterminado simplificado (más corto para evitar error 429)
    const mensaje = `Hola! Vi el reloj "${nombreReloj}" (Ref: ${idReloj}) que ya se vendió. Me interesa encontrar uno igual o similar. Tienen disponibilidad?`;
    
    console.log('📱 Mensaje a enviar:', mensaje);
    console.log('🔗 URL completa:', `https://wa.me/${numeroNegocio}?text=${encodeURIComponent(mensaje)}`);
    
    // Abrir WhatsApp en nueva pestaña
    const urlWhatsApp = `https://wa.me/${numeroNegocio}?text=${encodeURIComponent(mensaje)}`;
    window.open(urlWhatsApp, '_blank');
}

// Función legacy (por si se usa en otro lugar)
function contactarWhatsApp(idReloj, nombreReloj) {
    console.log('💬 Contactando por WhatsApp para:', nombreReloj);
    
    // Número de WhatsApp del negocio
    const numeroNegocio = '573173897119';
    
    // Mensaje predeterminado simplificado (más corto para evitar error 429)
    const mensaje = `Hola! Vi el reloj "${nombreReloj}" (Ref: ${idReloj}) que ya se vendió. Me interesa encontrar uno igual o similar. Tienen disponibilidad?`;
    
    // Abrir WhatsApp en nueva pestaña
    const urlWhatsApp = `https://wa.me/${numeroNegocio}?text=${encodeURIComponent(mensaje)}`;
    window.open(urlWhatsApp, '_blank');
}

function abrirNotificaciones(idReloj) {
    console.log('🔔 Abriendo notificaciones para:', idReloj);
    
    // Crear modal para capturar número de WhatsApp
    const modal = document.createElement('div');
    modal.className = 'modal-notificaciones';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>🔔 Recibir Alertas de Relojes Similares</h3>
                <button class="close-modal" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Te notificaremos cuando tengamos relojes similares disponibles.</p>
                <div class="input-group">
                    <label>Número de WhatsApp:</label>
                    <input type="tel" id="numero-whatsapp" placeholder="+57 300 123 4567" maxlength="15">
                </div>
                <div class="input-group">
                    <label>Tu nombre:</label>
                    <input type="text" id="nombre-usuario" placeholder="Tu nombre completo">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-enviar" onclick="enviarNotificacion(${idReloj})">Enviar a WhatsApp</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Estilos del modal
    const style = document.createElement('style');
    style.textContent = `
        .modal-notificaciones {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: #1a1a1a;
            border: 1px solid #ffcf66;
            border-radius: 15px;
            padding: 20px;
            max-width: 400px;
            width: 90%;
            color: white;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        .modal-header h3 {
            color: #ffcf66;
            margin: 0;
        }
        .close-modal {
            background: none;
            border: none;
            color: #ffcf66;
            font-size: 24px;
            cursor: pointer;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #ffcf66;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #333;
            border-radius: 8px;
            background: #2a2a2a;
            color: white;
            box-sizing: border-box;
        }
        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .btn-cancelar, .btn-enviar {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-cancelar {
            background: #666;
            color: white;
        }
        .btn-enviar {
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
        }
    `;
    document.head.appendChild(style);
}

function cerrarModal() {
    const modal = document.querySelector('.modal-notificaciones');
    if (modal) {
        modal.remove();
    }
}

function enviarNotificacion(idReloj) {
    const numero = document.getElementById('numero-whatsapp').value;
    const nombre = document.getElementById('nombre-usuario').value;
    
    if (!numero || !nombre) {
        alert('Por favor completa todos los campos');
        return;
    }
    
    // Limpiar número (solo números)
    const numeroLimpio = numero.replace(/\D/g, '');
    
    // Mensaje para WhatsApp
    const mensaje = `Hola ${nombre}! 👋

Te notificaremos cuando tengamos relojes similares disponibles.

📱 Tu número: ${numero}
🔔 Tipo: Relojes similares
⏰ Fecha: ${new Date().toLocaleDateString()}

¡Gracias por tu interés en FINOSO! 🏆`;
    
    // Abrir WhatsApp
    const urlWhatsApp = `https://wa.me/57${numeroLimpio}?text=${encodeURIComponent(mensaje)}`;
    window.open(urlWhatsApp, '_blank');
    
    cerrarModal();
}

// Función para agregar al carrito (usando la misma lógica que informacion/añadir-carrito.js)
function agregarAlCarrito(idReloj) {
    console.log('🛒 Agregando reloj al carrito:', idReloj);
    
    // Verificar sesión primero
    fetch(entornoCatalogo.buildUrl('login/php/verificar_sesion.php'))
        .then(res => res.json())
        .then(data => {
            if (!data.logged_in) {
                alert('Debes iniciar sesión para añadir productos al carrito.');
                window.location.href = '../login/login.html';
                return;
            }

            // Si hay sesión, proceder a añadir al carrito
            fetch(entornoCatalogo.buildUrl('informacion/php/añadir_al_carrito.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_reloj: idReloj
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion('✅ Producto añadido al carrito');
                    // Actualizar contador del carrito si existe
                    actualizarContadorCarrito();
                    // Refresh automático después de 1.5 segundos
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    mostrarNotificacion('❌ ' + data.message);
                }
            })
            .catch(err => {
                console.error('Error al añadir al carrito:', err);
                mostrarNotificacion('❌ Error al añadir al carrito');
            });
        })
        .catch(err => {
            console.error('Error al verificar sesión:', err);
            mostrarNotificacion('❌ Error de conexión');
        });
}

// Función para mostrar notificación
function mostrarNotificacion(mensaje) {
    // Crear elemento de notificación
    const notificacion = document.createElement('div');
    notificacion.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #FFCF66, #FFD700);
        color: #000;
        padding: 15px 20px;
        border-radius: 10px;
        font-weight: bold;
        font-size: 1rem;
        z-index: 9999;
        box-shadow: 0 5px 15px rgba(255, 207, 102, 0.4);
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    notificacion.textContent = mensaje;
    
    document.body.appendChild(notificacion);
    
    // Animar entrada
    setTimeout(() => {
        notificacion.style.transform = 'translateX(0)';
    }, 100);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notificacion.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notificacion);
        }, 300);
    }, 3000);
}

// Función para actualizar contador del carrito (desde la base de datos)
function actualizarContadorCarrito() {
    console.log('🔍 DEBUG mostrar-reloj.js - actualizarContadorCarrito() iniciada');
    // Verificar sesión primero
    fetch(entornoCatalogo.buildUrl('login/php/verificar_sesion.php'))
        .then(res => res.json())
        .then(data => {
            console.log('🔍 DEBUG mostrar-reloj.js - Verificación de sesión:', data);
            if (data.logged_in) {
                // Obtener el contador del carrito desde la base de datos
                fetch(entornoCatalogo.buildUrl('php/contar_carrito.php'))
                    .then(res => res.json())
                    .then(carritoData => {
                        console.log('🔍 DEBUG mostrar-reloj.js - Datos del carrito:', carritoData);
                        // Buscar el contador en la página
                        const contador = document.querySelector('.contador-carrito');
                        console.log('🔍 DEBUG mostrar-reloj.js - Contador encontrado:', !!contador);
                        if (contador) {
                            contador.textContent = carritoData.count || 0;
                            contador.style.display = (carritoData.count > 0) ? 'block' : 'none';
                            console.log('🔍 DEBUG mostrar-reloj.js - Contador actualizado:', contador.textContent);
                        }
                    })
                    .catch(err => {
                        console.error('Error al obtener contador del carrito:', err);
                    });
            } else {
                // Si no hay sesión, ocultar el contador
                const contador = document.querySelector('.contador-carrito');
                if (contador) {
                    contador.textContent = '0';
                    contador.style.display = 'none';
                }
            }
        })
        .catch(err => {
            console.error('Error al verificar sesión para contador:', err);
        });
}

// Hacer las funciones globales
window.agregarAlCarrito = agregarAlCarrito;