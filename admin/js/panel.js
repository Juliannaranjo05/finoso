function verComprobante(idOrden) {
    // Abrir comprobante en nueva ventana
    window.open(`php/ver_comprobante.php?id=${idOrden}`, '_blank');
}

// ============================================
// 📱 FUNCIONES PARA NOTIFICACIONES WHATSAPP
// ============================================

async function marcarComoEnviado(idOrden) {
    const transportadora = prompt('Ingresa la transportadora (ej: SERVIENTREGA, INTERRAPIDÍSIMO):', 'SERVIENTREGA');
    if (!transportadora) return;
    
    const guia = prompt('Ingresa el número de guía de seguimiento:');
    if (!guia || guia.trim() === '') {
        alert('El número de guía es obligatorio');
        return;
    }
    
    if (!confirm(`¿Marcar orden #${idOrden} como enviada?\n\nSe enviará WhatsApp al cliente con:\n- Transportadora: ${transportadora}\n- Guía: ${guia}`)) {
        return;
    }
    
    try {
        const response = await fetch('php/marcar_enviado.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id_orden=${idOrden}&transportadora=${encodeURIComponent(transportadora)}&guia=${encodeURIComponent(guia)}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            let mensaje = `✅ Orden marcada como enviada\n🚚 Guía: ${result.guia}`;
            if (result.whatsapp_enviado) {
                mensaje += '\n📱 WhatsApp enviado al cliente';
            } else if (result.whatsapp_error) {
                mensaje += `\n⚠️ WhatsApp no enviado: ${result.whatsapp_error}`;
            }
            alert(mensaje);
            location.reload();
        } else {
            alert('❌ Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error de conexión');
    }
}

async function marcarComoEntregado(idOrden) {
    if (!confirm(`¿Confirmar que la orden #${idOrden} fue entregada?\n\nSe enviará WhatsApp al cliente solicitando feedback.`)) {
        return;
    }
    
    try {
        const response = await fetch('php/marcar_entregado.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id_orden=${idOrden}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            let mensaje = '✅ Orden marcada como entregada';
            if (result.whatsapp_enviado) {
                mensaje += '\n📱 WhatsApp de feedback enviado al cliente';
            } else if (result.whatsapp_error) {
                mensaje += `\n⚠️ WhatsApp no enviado: ${result.whatsapp_error}`;
            }
            alert(mensaje);
            location.reload();
        } else {
            alert('❌ Error: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error de conexión');
    }
}

async function generarReporteMensual() {
    if (!confirm('¿Generar reporte mensual?\n\nSe enviará por WhatsApp con las estadísticas del mes anterior.')) {
        return;
    }
    
    // Mostrar loading
    const loadingMsg = '⏳ Generando reporte y enviando por WhatsApp...';
    alert(loadingMsg);
    
    try {
        const response = await fetch('php/generar_reporte_mensual.php', {
            method: 'GET'
        });
        
        const result = await response.json();
        
        if (result.success) {
            let mensaje = `📊 Reporte generado para ${result.mes} ${result.anio}\n\n`;
            mensaje += `💰 Ventas Totales: $${Number(result.datos.ventas_total || 0).toLocaleString()}\n`;
            mensaje += `📦 Órdenes Procesadas: ${result.datos.num_ordenes || 0}\n`;
            
            if (result.whatsapp_enviado) {
                mensaje += '\n📱 WhatsApp enviado correctamente';
            } else if (result.whatsapp_error) {
                mensaje += `\n⚠️ WhatsApp no enviado: ${result.whatsapp_error}`;
            }
            
            alert(mensaje);
        } else {
            alert('❌ Error al generar reporte: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error de conexión: ' + error.message);
    }
}

function aprobarOrden(idOrden) {
    if (confirm('¿Estás seguro de que quieres aprobar esta orden?')) {
        fetch('php/acciones.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=aprobar&id_orden=${idOrden}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Orden aprobada exitosamente');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al procesar la solicitud');
            console.error('Error:', error);
        });
    }
}

function rechazarOrden(idOrden) {
    const modal = document.getElementById('modal-rechazo');
    const select = document.getElementById('motivo-rechazo');
    const detalleWrapper = document.getElementById('detalle-otro-wrapper');
    const detalle = document.getElementById('detalle-otro');
    const montoPagadoWrapper = document.getElementById('monto-pagado-wrapper');
    const montoPagadoInput = document.getElementById('monto-pagado');
    const btnCancelar = document.getElementById('cancelar-rechazo');
    const btnCerrar = document.getElementById('cerrar-modal-rechazo');
    const btnConfirmar = document.getElementById('confirmar-rechazo');

    if (!modal) return;
    modal.style.display = 'flex';
    select.value = '';
    detalle.value = '';
    montoPagadoInput.value = '';
    detalleWrapper.style.display = 'none';
    montoPagadoWrapper.style.display = 'none';

    select.onchange = () => {
        const motivo = select.value;
        
        // Mostrar campo "Otro" si aplica
        if (motivo.startsWith('Otro')) {
            detalleWrapper.style.display = 'block';
        } else {
            detalleWrapper.style.display = 'none';
            detalle.value = '';
        }
        
        // Mostrar campo "Monto Pagado" si es problema de monto
        if (motivo.includes('monto') || motivo.includes('coincide')) {
            montoPagadoWrapper.style.display = 'block';
        } else {
            montoPagadoWrapper.style.display = 'none';
            montoPagadoInput.value = '';
        }
    };

    const closeModal = () => { 
        modal.style.display = 'none';
        montoPagadoWrapper.style.display = 'none';
        montoPagadoInput.value = '';
    };
    
    btnCancelar.onclick = closeModal;
    btnCerrar.onclick = closeModal;
    window.onclick = (e) => { if (e.target === modal) closeModal(); };

    btnConfirmar.onclick = () => {
        const baseMotivo = select.value.trim();
        const motivo = baseMotivo ? (baseMotivo.startsWith('Otro') ? `${baseMotivo}: ${detalle.value.trim()}` : baseMotivo) : '';
        if (!motivo) { alert('⚠️ Selecciona un motivo.'); return; }

        // Validar monto pagado si es problema de monto
        let montoPagado = 0;
        if (baseMotivo.includes('monto') || baseMotivo.includes('coincide')) {
            montoPagado = parseFloat(montoPagadoInput.value) || 0;
            if (montoPagado <= 0) {
                alert('⚠️ Debes ingresar el monto que el cliente pagó realmente.\n\nEjemplo: Si pagó $15.000, escribe 15000');
                montoPagadoInput.focus();
                return;
            }
        }

        // Construir body con monto_pagado si aplica
        let body = `action=rechazar&id_orden=${idOrden}&motivo=${encodeURIComponent(motivo)}`;
        if (montoPagado > 0) {
            body += `&monto_pagado=${montoPagado}`;
        }

        fetch('php/acciones.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeModal();
                alert('✅ Orden rechazada correctamente.\n\nEl cliente recibirá opciones para recuperar su orden.');
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(err => {
            alert('❌ Error al procesar la solicitud');
            console.error(err);
        });
    };
}

function verificarComprobante(idOrden) {
    if (confirm('¿Estás seguro de que quieres marcar este comprobante como verificado?')) {
        fetch('php/acciones.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=verificar_comprobante&id_orden=${idOrden}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Comprobante verificado exitosamente');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al procesar la solicitud');
            console.error('Error:', error);
        });
    }
}

function revertirVerificacion(idOrden) {
    if (confirm('¿Estás seguro de que quieres revertir la verificación de este comprobante?')) {
        fetch('php/acciones.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=revertir_verificacion&id_orden=${idOrden}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Verificación revertida exitosamente');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al procesar la solicitud');
            console.error('Error:', error);
        });
    }
}

function revertirAprobacion(idOrden) {
    if (confirm('¿Estás seguro de que quieres revertir la aprobación de esta orden? El reloj volverá a estar disponible.')) {
        fetch('php/acciones.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=revertir_aprobacion&id_orden=${idOrden}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Aprobación revertida exitosamente');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al procesar la solicitud');
            console.error('Error:', error);
        });
    }
}

// ============================================
// FUNCIONES PARA GESTIÓN DE CÓDIGOS DE DESCUENTO
// ============================================

// Cambiar entre tabs
function cambiarTab(tab) {
    // Ocultar todos los tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Desactivar todos los botones
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Activar el tab seleccionado
    document.getElementById(`tab-${tab}`).classList.add('active');
    event.target.classList.add('active');
    
    // Cargar datos si es necesario
    if (tab === 'relojes') {
        cargarRelojes();
    } else if (tab === 'codigos') {
        cargarCodigos();
    } else if (tab === 'envios') {
        cargarEnvios();
    } else if (tab === 'comentarios') {
        cargarComentarios();
    }
}

// Cargar códigos de descuento
async function cargarCodigos() {
    try {
        const response = await fetch('php/gestionar_codigos.php?action=listar');
        const data = await response.json();
        
        if (data.success) {
            mostrarCodigos(data.codigos);
        } else {
            document.getElementById('codigos-content').innerHTML = 
                '<div class="error">Error al cargar códigos: ' + data.message + '</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('codigos-content').innerHTML = 
            '<div class="error">Error de conexión al cargar códigos</div>';
    }
}

// Mostrar códigos en la tabla
function mostrarCodigos(codigos) {
    const content = document.getElementById('codigos-content');
    
    if (codigos.length === 0) {
        content.innerHTML = '<p class="no-orders">No hay códigos de descuento registrados</p>';
        return;
    }
    
    let html = '<table class="discount-codes-table">';
    html += `
        <thead>
            <tr>
                <th>Código</th>
                <th>Descuento</th>
                <th>Fecha Expiración</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
    `;
    
    codigos.forEach(codigo => {
        const porcentaje = parseFloat(codigo.porcentaje).toFixed(0);
        const fecha = new Date(codigo.fecha_expiracion + 'T00:00:00').toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        let estadoBadge = '';
        if (codigo.estado === 'activo') {
            estadoBadge = '<span class="badge badge-success">✅ Activo</span>';
        } else if (codigo.estado === 'expira_hoy') {
            estadoBadge = '<span class="badge badge-warning">⚠️ Expira Hoy</span>';
        } else {
            estadoBadge = '<span class="badge badge-danger">❌ Expirado</span>';
        }
        
        html += `
            <tr>
                <td><strong>${escapeHtml(codigo.codigo)}</strong></td>
                <td>${porcentaje}%</td>
                <td>${fecha}</td>
                <td>${estadoBadge}</td>
                <td class="actions-cell">
                    <button class="btn btn-small btn-edit" onclick="editarCodigo(${codigo.id_codigo})" title="Editar">
                        ✏️
                    </button>
                    <button class="btn btn-small btn-delete" onclick="eliminarCodigo(${codigo.id_codigo}, '${escapeHtml(codigo.codigo)}')" title="Eliminar">
                        🗑️
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    content.innerHTML = html;
}

// Mostrar modal para crear código
function mostrarModalCrearCodigo() {
    document.getElementById('modal-titulo').textContent = 'Crear Código de Descuento';
    document.getElementById('form-codigo').reset();
    document.getElementById('id_codigo').value = '';
    document.getElementById('modal-codigo').style.display = 'block';
}

// Cerrar modal
function cerrarModalCodigo() {
    document.getElementById('modal-codigo').style.display = 'none';
}

// Editar código
async function editarCodigo(idCodigo) {
    try {
        const response = await fetch('php/gestionar_codigos.php?action=listar');
        const data = await response.json();
        
        if (data.success) {
            const codigo = data.codigos.find(c => c.id_codigo == idCodigo);
            if (codigo) {
                document.getElementById('modal-titulo').textContent = 'Editar Código de Descuento';
                document.getElementById('id_codigo').value = codigo.id_codigo;
                document.getElementById('codigo').value = codigo.codigo;
                document.getElementById('porcentaje').value = parseFloat(codigo.porcentaje).toFixed(2);
                document.getElementById('fecha_expiracion').value = codigo.fecha_expiracion;
                document.getElementById('modal-codigo').style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar datos del código');
    }
}

// Guardar código (crear o actualizar)
async function guardarCodigo(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const idCodigo = formData.get('id_codigo');
    const action = idCodigo ? 'actualizar' : 'crear';
    
    formData.append('action', action);
    
    try {
        const response = await fetch('php/gestionar_codigos.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            cerrarModalCodigo();
            cargarCodigos();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al guardar el código');
    }
}

// Eliminar código
async function eliminarCodigo(idCodigo, nombreCodigo) {
    if (confirm(`¿Estás seguro de que quieres eliminar el código "${nombreCodigo}"?`)) {
        try {
            const response = await fetch('php/gestionar_codigos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=eliminar&id_codigo=${idCodigo}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert(data.message);
                cargarCodigos();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al eliminar el código');
        }
    }
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modalCodigo = document.getElementById('modal-codigo');
    const modalEnvio = document.getElementById('modal-envio');
    
    if (event.target === modalCodigo) {
        cerrarModalCodigo();
    }
    if (event.target === modalEnvio) {
        cerrarModalEnvio();
    }
}

// ============================================
// FUNCIONES PARA GESTIÓN DE ENVÍOS
// ============================================

// Variable global para almacenar todos los envíos
let todosLosEnvios = [];

// Cargar envíos
async function cargarEnvios() {
    try {
        const response = await fetch('php/gestionar_envios.php?action=listar');
        const data = await response.json();
        
        if (data.success) {
            todosLosEnvios = data.envios;
            mostrarEnvios(todosLosEnvios);
            cargarEstadisticasEnvios();
            cargarDepartamentosSelect();
        } else {
            document.getElementById('envios-content').innerHTML = 
                '<div class="error">Error al cargar envíos: ' + escapeHtml(data.message) + '</div>';
        }
    } catch (error) {
        console.error('Error al cargar envíos:', error);
        document.getElementById('envios-content').innerHTML = 
            '<div class="error">Error de conexión al cargar envíos</div>';
    }
}

// Mostrar envíos en la tabla
function mostrarEnvios(envios) {
    const content = document.getElementById('envios-content');
    
    if (envios.length === 0) {
        content.innerHTML = '<p class="no-orders">No hay envíos registrados</p>';
        return;
    }
    
    let html = '<table class="envios-table-data">';
    html += `
        <thead>
            <tr>
                <th>Departamento</th>
                <th>Ciudad</th>
                <th>Precio</th>
                <th>Días Estimados</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
    `;
    
    envios.forEach(envio => {
        const precio = '$ ' + parseFloat(envio.precio).toLocaleString('es-CO');
        const activo = parseInt(envio.activo) === 1;
        const estadoBadge = activo ? 
            '<span class="badge badge-success">✅ Activo</span>' : 
            '<span class="badge badge-danger">❌ Inactivo</span>';
        
        html += `
            <tr>
                <td><strong>${escapeHtml(envio.departamento)}</strong></td>
                <td>${escapeHtml(envio.ciudad)}</td>
                <td>${precio}</td>
                <td>${envio.dias_estimados} días</td>
                <td>${estadoBadge}</td>
                <td class="actions-cell">
                    <button class="btn btn-small btn-edit" onclick="editarEnvio(${envio.id_envio})" title="Editar">
                        ✏️
                    </button>
                    <button class="btn btn-small ${activo ? 'btn-warning' : 'btn-success'}" 
                            onclick="toggleActivoEnvio(${envio.id_envio})" 
                            title="${activo ? 'Desactivar' : 'Activar'}">
                        ${activo ? '👁️' : '👁️‍🗨️'}
                    </button>
                    <button class="btn btn-small btn-delete" onclick="eliminarEnvio(${envio.id_envio}, '${escapeHtml(envio.ciudad)}')" title="Eliminar">
                        🗑️
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    content.innerHTML = html;
}

// Cargar estadísticas de envíos
async function cargarEstadisticasEnvios() {
    try {
        const response = await fetch('php/gestionar_envios.php?action=estadisticas');
        const data = await response.json();
        
        if (data.success) {
            const stats = data.estadisticas;
            const statsHtml = `
                <div class="stats-grid-small">
                    <div class="stat-card-small">
                        <div class="stat-number">${stats.total_ciudades}</div>
                        <div class="stat-label">Ciudades</div>
                    </div>
                    <div class="stat-card-small">
                        <div class="stat-number">${stats.total_departamentos}</div>
                        <div class="stat-label">Departamentos</div>
                    </div>
                    <div class="stat-card-small">
                        <div class="stat-number">$ ${parseFloat(stats.precio_promedio).toLocaleString('es-CO', {maximumFractionDigits: 0})}</div>
                        <div class="stat-label">Precio Promedio</div>
                    </div>
                    <div class="stat-card-small">
                        <div class="stat-number">${parseFloat(stats.dias_promedio).toFixed(1)} días</div>
                        <div class="stat-label">Entrega Promedio</div>
                    </div>
                </div>
            `;
            document.getElementById('envios-stats').innerHTML = statsHtml;
        }
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
}

// Cargar departamentos para el select
// Cargar departamentos para el select
async function cargarDepartamentosSelect() {
    console.log('🔄 Cargando departamentos...');
    
    try {
        const response = await fetch('php/gestionar_envios.php?action=departamentos');
        console.log('📡 Response status:', response.status);
        
        // Debug: ver qué está devolviendo
        const text = await response.text();
        console.log('📄 Respuesta raw:', text);
        console.log('📏 Longitud respuesta:', text.length);
        
        if (!text.trim()) {
            console.error('❌ Respuesta vacía del servidor');
            alert('Error: El servidor no devolvió datos de departamentos.');
            return;
        }
        
        let data;
        try {
            data = JSON.parse(text);
            console.log('✅ JSON parseado:', data);
        } catch (parseError) {
            console.error('❌ Error parseando JSON:', parseError);
            console.error('Texto recibido:', text.substring(0, 200));
            alert('Error: Respuesta inválida del servidor');
            return;
        }
        
        if (data.success) {
            console.log('✅ Departamentos recibidos:', data.departamentos.length);
            
            const select = document.getElementById('filtro-departamento');
            
            if (!select) {
                console.error('❌ No se encontró el elemento #filtro-departamento');
                return;
            }
            
            console.log('📝 Select encontrado, llenando opciones...');
            
            // Limpiar select (mantener solo la primera opción "Todos los departamentos")
            const firstOption = select.firstElementChild;
            select.innerHTML = '';
            if (firstOption) {
                select.appendChild(firstOption);
            } else {
                // Si no existe, crear la opción por defecto
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Todos los departamentos';
                select.appendChild(defaultOption);
            }
            
            // Llenar select con departamentos
            data.departamentos.forEach((depto, index) => {
                const option = document.createElement('option');
                option.value = depto;
                option.textContent = depto;
                select.appendChild(option);
                console.log(`  ${index + 1}. ${depto}`);
            });
            
            // Llenar datalist del modal (si existe)
            const datalist = document.getElementById('departamentos-list');
            if (datalist) {
                datalist.innerHTML = '';
                data.departamentos.forEach(depto => {
                    const option = document.createElement('option');
                    option.value = depto;
                    datalist.appendChild(option);
                });
                console.log('✅ Datalist también actualizado');
            }
            
            console.log(`✅ Select actualizado con ${data.departamentos.length} departamentos`);
        } else {
            console.error('❌ Error en respuesta:', data.message || 'Error desconocido');
            // Si el usuario no tiene permisos, ya fue redirigido por PHP
            // No mostramos alert para evitar confusión
        }
    } catch (error) {
        console.error('❌ Error al cargar departamentos:', error);
        console.error('Stack:', error.stack);
        // Si hay error de conexión, el usuario ya fue redirigido si no era admin
    }
}

// Filtrar envíos
function filtrarEnvios() {
    const departamento = document.getElementById('filtro-departamento').value;
    const busqueda = document.getElementById('buscar-envio').value.toLowerCase();
    
    let enviosFiltrados = todosLosEnvios;
    
    // Filtrar por departamento
    if (departamento) {
        enviosFiltrados = enviosFiltrados.filter(e => e.departamento === departamento);
    }
    
    // Filtrar por búsqueda
    if (busqueda) {
        enviosFiltrados = enviosFiltrados.filter(e => 
            e.ciudad.toLowerCase().includes(busqueda) ||
            e.departamento.toLowerCase().includes(busqueda)
        );
    }
    
    mostrarEnvios(enviosFiltrados);
}

// Mostrar modal para crear envío
function mostrarModalCrearEnvio() {
    document.getElementById('modal-envio-titulo').textContent = 'Agregar Ciudad';
    document.getElementById('form-envio').reset();
    document.getElementById('id_envio').value = '';
    document.getElementById('activo-group').style.display = 'none';
    document.getElementById('modal-envio').style.display = 'block';
}

// Cerrar modal
function cerrarModalEnvio() {
    document.getElementById('modal-envio').style.display = 'none';
}

// Editar envío
async function editarEnvio(idEnvio) {
    const envio = todosLosEnvios.find(e => e.id_envio == idEnvio);
    if (envio) {
        document.getElementById('modal-envio-titulo').textContent = 'Editar Envío';
        document.getElementById('id_envio').value = envio.id_envio;
        document.getElementById('departamento_envio').value = envio.departamento;
        document.getElementById('ciudad_envio').value = envio.ciudad;
        document.getElementById('precio_envio').value = parseFloat(envio.precio);
        document.getElementById('dias_estimados_envio').value = envio.dias_estimados;
        document.getElementById('activo_envio').value = envio.activo;
        document.getElementById('activo-group').style.display = 'block';
        document.getElementById('modal-envio').style.display = 'block';
    }
}

// Guardar envío (crear o actualizar)
async function guardarEnvio(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const idEnvio = formData.get('id_envio');
    const action = idEnvio ? 'actualizar' : 'crear';
    
    formData.append('action', action);
    
    try {
        const response = await fetch('php/gestionar_envios.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            cerrarModalEnvio();
            cargarEnvios();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al guardar el envío');
    }
}

// Eliminar envío
async function eliminarEnvio(idEnvio, nombreCiudad) {
    if (confirm(`¿Estás seguro de que quieres eliminar el envío a "${nombreCiudad}"?`)) {
        try {
            const response = await fetch('php/gestionar_envios.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=eliminar&id_envio=${idEnvio}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert(data.message);
                cargarEnvios();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al eliminar el envío');
        }
    }
}

// Toggle activo/inactivo
async function toggleActivoEnvio(idEnvio) {
    try {
        const response = await fetch('php/gestionar_envios.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=toggle_activo&id_envio=${idEnvio}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            cargarEnvios();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cambiar el estado');
    }
}

// Cargar relojes
async function cargarRelojes() {
    console.log('🔄 Iniciando carga de relojes...');
    
    try {
        console.log('📡 Enviando petición a php/listar_relojes.php');
        const response = await fetch('php/listar_relojes.php');
        
        console.log('📊 Respuesta recibida:', {
            status: response.status,
            statusText: response.statusText,
            ok: response.ok,
            url: response.url,
            headers: Object.fromEntries(response.headers.entries())
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        console.log('📄 Obteniendo texto de la respuesta...');
        const text = await response.text();
        console.log('📝 Texto recibido (longitud):', text.length);
        console.log('📝 Primeros 200 caracteres:', text.substring(0, 200));
        
        if (!text.trim()) {
            throw new Error('Respuesta vacía del servidor');
        }
        
        console.log('🔄 Parseando JSON...');
        const data = JSON.parse(text);
        console.log('✅ JSON parseado exitosamente:', data);
        
        if (data.success) {
            console.log('🎯 Datos válidos, mostrando relojes...');
            
            // Guardar datos globalmente para uso en edición
            window.relojesData = data.relojes;
            
            mostrarRelojes(data.relojes);
            actualizarEstadisticasRelojes(data.relojes);
        } else {
            console.log('❌ Error en respuesta:', data.message);
            document.getElementById('relojes-content').innerHTML = '<div class="error">Error al cargar relojes: ' + data.message + '</div>';
        }
    } catch (error) {
        console.error('❌ Error completo:', error);
        console.error('❌ Stack trace:', error.stack);
        document.getElementById('relojes-content').innerHTML = '<div class="error">Error de conexión: ' + error.message + '</div>';
    }
}

function mostrarRelojes(relojes) {
    const relojesContent = document.getElementById('relojes-content');
    
    if (relojes.length === 0) {
        relojesContent.innerHTML = '<div class="no-orders">No hay relojes registrados</div>';
        return;
    }
    
    let html = '';
    relojes.forEach(reloj => {
        const estadoColor = reloj.vendido ? '#f44336' : (reloj.disponible ? '#4CAF50' : '#FF9800');
        const estadoTexto = reloj.vendido ? 'Vendido' : (reloj.disponible ? 'Disponible' : 'No disponible');
        
        html += `
            <div class="order-item">
                <div class="order-header">
                    <div class="order-id">${reloj.marca} - ${reloj.nombre}</div>
                    <div class="order-date" style="color: ${estadoColor};">${estadoTexto}</div>
                </div>
                
                <div class="order-details">
                    <div class="detail-row">
                        <div class="detail-item">
                            <div class="detail-label">Precio</div>
                            <div class="detail-value">${reloj.precio_formateado}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Descuento</div>
                            <div class="detail-value">${reloj.descuento_formateado}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Estado</div>
                            <div class="detail-value" style="color: ${estadoColor};">${estadoTexto}</div>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-item" style="grid-column: 1 / -1;">
                            <div class="detail-label">Descripción</div>
                            <div class="detail-value">${escapeHtml(reloj.descripcion)}</div>
                        </div>
                    </div>
                </div>
                
                <div class="order-actions">
                    <button class="btn btn-view" onclick="editarReloj(${reloj.id_reloj})">
                        ✏️ Editar
                    </button>
                    <button class="btn btn-${reloj.disponible ? 'warning' : 'success'}" onclick="toggleDisponibilidadReloj(${reloj.id_reloj})">
                        ${reloj.disponible ? '⏸️ Pausar' : '▶️ Activar'}
                    </button>
                    <button class="btn btn-danger" onclick="eliminarReloj(${reloj.id_reloj})">
                        🗑️ Eliminar
                    </button>
                </div>
            </div>
        `;
    });
    
    relojesContent.innerHTML = html;
}

function actualizarEstadisticasRelojes(relojes) {
    const total = relojes.length;
    const disponibles = relojes.filter(r => r.disponible && !r.vendido).length;
    const vendidos = relojes.filter(r => r.vendido).length;
    const noDisponibles = relojes.filter(r => !r.disponible && !r.vendido).length;
    
    document.getElementById('total-relojes').textContent = total;
    document.getElementById('disponibles-relojes').textContent = disponibles;
    document.getElementById('vendidos-relojes').textContent = vendidos;
    document.getElementById('no-disponibles-relojes').textContent = noDisponibles;
}

function editarReloj(id) {
    console.log('🔧 Editando reloj ID:', id);
    
    // Obtener datos del reloj desde los datos cargados (mejor método)
    let relojData = null;
    
    // Buscar en los datos cargados previamente
    if (window.relojesData && Array.isArray(window.relojesData)) {
        relojData = window.relojesData.find(reloj => reloj.id_reloj == id);
    }
    
    // Si no encontramos en los datos, intentar extraer del DOM como fallback
    if (!relojData) {
        console.log('⚠️ No se encontraron datos en memoria, intentando extraer del DOM...');
        
        const relojElement = document.querySelector(`button[onclick="editarReloj(${id})"]`)?.closest('.order-item');
        if (relojElement) {
            const headerText = relojElement.querySelector('.order-id')?.textContent || '';
            const [marca, nombre] = headerText.split(' - ');
            
            // Extraer precio (primer detail-value)
            const precioText = relojElement.querySelectorAll('.detail-value')[0]?.textContent || '';
            const precio = precioText.replace(/[^0-9]/g, '') || '0';
            
            // Extraer descuento (segundo detail-value)
            const descuentoText = relojElement.querySelectorAll('.detail-value')[1]?.textContent || '';
            const descuento = descuentoText.replace(/[^0-9]/g, '') || '0';
            
            // Extraer descripción (tercer detail-value)
            const descripcion = relojElement.querySelectorAll('.detail-value')[3]?.textContent || '';
            
            const estado = relojElement.querySelector('.order-date')?.textContent || '';
            
            relojData = {
                id: id,
                marca: marca || '',
                nombre: nombre || '',
                descripcion: descripcion,
                precio: precio,
                descuento: descuento,
                disponible: estado === 'Disponible',
                vendido: estado === 'Vendido'
            };
        }
    }
    
    if (!relojData) {
        alert('❌ No se pudieron obtener los datos del reloj');
        return;
    }
    
    console.log('✅ Datos del reloj obtenidos:', relojData);
    
    // Crear modal de edición
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 600px; max-height: 80vh; overflow-y: auto;">
            <div class="modal-header">
                <h3>✏️ Editar Reloj</h3>
                <button class="close-modal" onclick="this.closest('.modal-overlay').remove()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editarRelojForm">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="edit-marca" style="display: block; margin-bottom: 5px; font-weight: bold;">Marca:</label>
                        <input type="text" id="edit-marca" name="marca" value="${relojData.marca}" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="edit-nombre" style="display: block; margin-bottom: 5px; font-weight: bold;">Nombre:</label>
                        <input type="text" id="edit-nombre" name="nombre" value="${relojData.nombre}" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="edit-descripcion" style="display: block; margin-bottom: 5px; font-weight: bold;">Descripción:</label>
                        <textarea id="edit-descripcion" name="descripcion" rows="3" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;">${relojData.descripcion}</textarea>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="edit-eslabones" style="display: block; margin-bottom: 5px; font-weight: bold;">Eslabones:</label>
                        <input type="text" id="edit-eslabones" name="eslabones" value="${relojData.eslabones || ''}" placeholder="Ej: 20 eslabones" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="edit-tipo-bisel" style="display: block; margin-bottom: 5px; font-weight: bold;">Tipo de Bisel:</label>
                        <select id="edit-tipo-bisel" name="tipo_bisel" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">Seleccionar tipo</option>
                            <option value="estatico" ${relojData.tipo_bisel === 'estatico' ? 'selected' : ''}>Estático</option>
                            <option value="giratorio" ${relojData.tipo_bisel === 'giratorio' ? 'selected' : ''}>Giratorio</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="edit-movimiento" style="display: block; margin-bottom: 5px; font-weight: bold;">Movimiento:</label>
                        <input type="text" id="edit-movimiento" name="movimiento" value="${relojData.movimiento || ''}" placeholder="Ej: Automático Swiss Made" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="edit-pulsera" style="display: block; margin-bottom: 5px; font-weight: bold;">Pulsera:</label>
                        <input type="text" id="edit-pulsera" name="pulsera" value="${relojData.pulsera || ''}" placeholder="Ej: Cuero genuino" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="edit-peso" style="display: block; margin-bottom: 5px; font-weight: bold;">Peso:</label>
                        <input type="text" id="edit-peso" name="peso" value="${relojData.peso || ''}" placeholder="Ej: 150g" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="edit-resistencia-agua" style="display: block; margin-bottom: 5px; font-weight: bold;">Resistencia al Agua:</label>
                        <input type="text" id="edit-resistencia-agua" name="resistencia_agua" value="${relojData.resistencia_agua || ''}" placeholder="Ej: 100m" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="edit-precio" style="display: block; margin-bottom: 5px; font-weight: bold;">Precio ($):</label>
                        <input type="number" id="edit-precio" name="precio" value="${relojData.precio}" min="0" step="1000" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="edit-descuento" style="display: block; margin-bottom: 5px; font-weight: bold;">Descuento (%):</label>
                        <input type="number" id="edit-descuento" name="descuento" value="${relojData.descuento}" min="0" max="100" step="1" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" id="edit-disponible" name="disponible" ${relojData.disponible ? 'checked' : ''}>
                            Disponible
                        </label>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" id="edit-vendido" name="vendido" ${relojData.vendido ? 'checked' : ''}>
                            Vendido
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="display: flex; gap: 10px; justify-content: flex-end; padding: 15px; border-top: 1px solid #ddd;">
                <button type="button" class="btn-cancel" onclick="this.closest('.modal-overlay').remove()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancelar</button>
                <button type="button" class="btn-save" onclick="guardarReloj(${id})" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">💾 Guardar Cambios</button>
            </div>
        </div>
    `;
    
    // Agregar estilos para el modal
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    `;
    
    document.body.appendChild(modal);
}

function guardarReloj(id) {
    const form = document.getElementById('editarRelojForm');
    const formData = new FormData(form);
    
    // Agregar ID del reloj
    formData.append('id_reloj', id);
    
    // Mostrar loading
    const saveBtn = document.querySelector('.btn-save');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = '💾 Guardando...';
    saveBtn.disabled = true;
    
    fetch('php/editar_reloj.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Reloj actualizado exitosamente');
            // Cerrar modal
            document.querySelector('.modal-overlay').remove();
            // Recargar lista de relojes
            cargarRelojes();
        } else {
            alert('❌ Error al actualizar reloj: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error de conexión: ' + error.message);
    })
    .finally(() => {
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
}

function toggleDisponibilidadReloj(id) {
    console.log('⏸️ Cambiando disponibilidad del reloj ID:', id);
    
    if (confirm('¿Estás seguro de que quieres cambiar la disponibilidad de este reloj?')) {
        fetch('php/toggle_reloj.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id_reloj: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Disponibilidad actualizada');
                cargarRelojes(); // Recargar lista
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error de conexión');
        });
    }
}

function eliminarReloj(id) {
    console.log('🗑️ Eliminando reloj ID:', id);
    
    if (confirm('⚠️ ¿Estás SEGURO de que quieres eliminar este reloj?\n\nEsta acción NO se puede deshacer.')) {
        fetch('php/eliminar_reloj.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id_reloj: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.accion === 'marcado_vendido') {
                    alert('✅ Reloj marcado como vendido (tenía órdenes asociadas)\n\n📋 ' + data.message);
                } else if (data.accion === 'eliminado_completo') {
                    alert('✅ Reloj eliminado completamente\n\n📋 ' + data.message);
                } else {
                    alert('✅ Reloj eliminado exitosamente');
                }
                cargarRelojes(); // Recargar lista
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error de conexión');
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando gestión de envíos...');
    
    // Cargar departamentos para el filtro
    cargarDepartamentosSelect();
    
    // Cargar envíos iniciales
    cargarEnvios();
    
    console.log('✅ Inicialización completa');
});

// También cargar cuando la pestaña de envíos se active (si usas tabs)
function activarTabEnvios() {
    console.log('📦 Tab de envíos activado');
    cargarDepartamentosSelect();
    cargarEnvios();
}

// ==================== FUNCIONES DE COMENTARIOS ====================

let comentariosData = [];

async function cargarComentarios() {
    try {
        console.log('💬 Cargando comentarios...');
        const response = await fetch('php/gestionar_comentarios_simple.php?accion=obtener_pendientes');
        const data = await response.json();
        
        if (data.success) {
            comentariosData = data.comentarios;
            mostrarComentarios(comentariosData);
            actualizarEstadisticasComentarios();
        } else {
            mostrarErrorComentarios('Error al cargar comentarios: ' + data.error);
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarErrorComentarios('Error de conexión');
    }
}

function mostrarComentarios(comentarios) {
    const container = document.getElementById('comments-container');
    
    if (comentarios.length === 0) {
        container.innerHTML = `
            <div class="no-comments">
                <i class="fas fa-check-circle"></i>
                <h3>¡Excelente!</h3>
                <p>No hay comentarios pendientes de aprobación</p>
            </div>
        `;
        return;
    }

    const html = comentarios.map(comentario => crearHTMLComentario(comentario)).join('');
    container.innerHTML = html;
}

function crearHTMLComentario(comentario) {
    console.log('🔍 Creando HTML para comentario:', comentario);
    console.log('🔍 ID del comentario:', comentario.id_comentario);
    
    const fecha = new Date(comentario.fecha).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    const estrellas = crearEstrellasHTML(comentario.calificacion);

    return `
        <div class="comment-item">
            <div class="comment-header">
                <div class="comment-info">
                    <h3>${escapeHtml(comentario.usuario)}</h3>
                    <span class="comment-date">${fecha}</span>
                    <span class="comment-product">${escapeHtml(comentario.reloj)}</span>
                </div>
                <div class="comment-rating">
                    ${estrellas}
                </div>
            </div>
            <div class="comment-content">
                <p>${escapeHtml(comentario.comentario)}</p>
            </div>
            <div class="comment-actions">
                <button class="btn btn-approve" onclick="aprobarComentario(${comentario.id_comentario})" data-id="${comentario.id_comentario}">
                    <i class="fas fa-check"></i> Aprobar
                </button>
                <button class="btn btn-reject" onclick="rechazarComentario(${comentario.id_comentario})" data-id="${comentario.id_comentario}">
                    <i class="fas fa-times"></i> Rechazar
                </button>
            </div>
        </div>
    `;
}

function crearEstrellasHTML(calificacion) {
    let estrellas = '';
    for (let i = 1; i <= 5; i++) {
        const clase = i <= calificacion ? 'star-filled' : 'star-empty';
        estrellas += `<span class="star ${clase}">★</span>`;
    }
    return estrellas;
}

async function aprobarComentario(id) {
    console.log('🔍 ID del comentario a aprobar:', id);
    
    if (!confirm('¿Estás seguro de que quieres aprobar este comentario?')) {
        return;
    }

    try {
        const payload = `accion=aprobar&id=${id}`;
        console.log('📤 Enviando payload:', payload);
        
        const response = await fetch('php/gestionar_comentarios_simple.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: payload
        });

        const data = await response.json();
        
        if (data.success) {
            mostrarMensajeComentarios(data.message, 'success');
            cargarComentarios(); // Recargar la lista
        } else {
            mostrarMensajeComentarios('Error: ' + data.error, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarMensajeComentarios('Error de conexión', 'error');
    }
}

async function rechazarComentario(id) {
    if (!confirm('¿Estás seguro de que quieres rechazar este comentario? Esta acción no se puede deshacer.')) {
        return;
    }

    try {
        const response = await fetch('php/gestionar_comentarios_simple.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `accion=rechazar&id=${id}`
        });

        const data = await response.json();
        
        if (data.success) {
            mostrarMensajeComentarios(data.message, 'success');
            cargarComentarios(); // Recargar la lista
        } else {
            mostrarMensajeComentarios('Error: ' + data.error, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarMensajeComentarios('Error de conexión', 'error');
    }
}

function actualizarEstadisticasComentarios() {
    const total = comentariosData.length;
    const pendientes = comentariosData.length; // Solo mostramos pendientes
    const aprobados = 0; // Se calcularía con otra consulta
    const promedio = comentariosData.length > 0 ? 
        (comentariosData.reduce((sum, c) => sum + c.calificacion, 0) / comentariosData.length).toFixed(1) : 0;

    document.getElementById('total-comentarios').textContent = total;
    document.getElementById('pendientes-comentarios').textContent = pendientes;
    document.getElementById('aprobados-comentarios').textContent = aprobados;
    document.getElementById('promedio-calificacion').textContent = promedio;
}

function mostrarErrorComentarios(mensaje) {
    const container = document.getElementById('comments-container');
    container.innerHTML = `
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Error</h3>
            <p>${mensaje}</p>
            <button onclick="cargarComentarios()" class="btn btn-primary">
                <i class="fas fa-redo"></i> Reintentar
            </button>
        </div>
    `;
}

function mostrarMensajeComentarios(mensaje, tipo) {
    const mensajeDiv = document.createElement('div');
    mensajeDiv.className = `alert alert-${tipo}`;
    mensajeDiv.innerHTML = `
        <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${mensaje}
    `;
    
    document.body.insertBefore(mensajeDiv, document.body.firstChild);
    
    setTimeout(() => {
        mensajeDiv.remove();
    }, 5000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}