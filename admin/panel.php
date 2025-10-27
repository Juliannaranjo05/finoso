<?php
// Verificar sesión de administrador antes de mostrar la página
require_once 'check_session.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Finoso</title>
    <link rel="icon" href="../img/finoso_logo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/panel.css">
    <link rel="stylesheet" href="css/comentarios.css">
</head>
<body>
    <div class="header">
        <div class="logo">FINOSO ADMIN</div>
        <div class="user-info">
            <span class="user-name" id="user-name">Cargando...</span>
            <a href="../login/php/logout.php" class="logout-btn">Cerrar Sesión</a>
        </div>
    </div>

    <div class="container">
        <!-- Pestañas de navegación -->
        <div class="tabs">
            <button class="tab-btn active" onclick="cambiarTab('ordenes')">📦 Órdenes</button>
            <button class="tab-btn" onclick="cambiarTab('relojes')">⌚ Gestión de Relojes</button>
            <button class="tab-btn" onclick="cambiarTab('codigos')">🎟️ Códigos de Descuento</button>
            <button class="tab-btn" onclick="cambiarTab('envios')">🚚 Gestión de Envíos</button>
            <button class="tab-btn" onclick="cambiarTab('comentarios')">💬 Comentarios</button>
        </div>

        <!-- Tab de Órdenes -->
        <div id="tab-ordenes" class="tab-content active">
            <!-- Estadísticas -->
            <div class="stats-grid" id="stats-grid">
                <!-- Las estadísticas se cargarán dinámicamente -->
            </div>

            <!-- Botón Reporte Mensual -->
            <div style="text-align: right; margin: 20px 0;">
                <button class="btn btn-primary" onclick="generarReporteMensual()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 12px 24px; font-size: 16px; font-weight: 600;">
                    📊 Generar Reporte Mensual
                </button>
            </div>

            <!-- Órdenes Pendientes -->
            <h2 class="section-title">Órdenes Pendientes de Verificación</h2>
            <div class="orders-table">
                <div class="table-content" id="orders-content">
                    <!-- Las órdenes se cargarán dinámicamente -->
                </div>
            </div>
        </div>

        <!-- Modal Rechazo -->
        <div id="modal-rechazo" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:9999;">
            <div class="modal-content" style="background:#1a1a1a; border:1px solid #333; border-radius:10px; width: 95%; max-width: 520px; color:#eee;">
                <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border-bottom:1px solid #333;">
                    <h3 style="margin:0;">Rechazar orden</h3>
                    <button id="cerrar-modal-rechazo" class="close" style="background:none; border:none; color:#aaa; font-size:20px; cursor:pointer;">×</button>
                </div>
                <div class="modal-body" style="padding:18px 20px;">
                    <label for="motivo-rechazo" style="display:block; margin-bottom:8px; color:#ccc;">Motivo</label>
                    <select id="motivo-rechazo" class="filter-select" style="width:100%; padding:10px; border-radius:6px; border:1px solid #444; background:#111; color:#eee;">
                        <option value="">Selecciona un motivo...</option>
                        <option>El monto del comprobante no coincide</option>
                        <option>El comprobante pertenece a otra transacción</option>
                        <option>El comprobante es ilegible o no válido</option>
                        <option>El comprobante está repetido o ya usado</option>
                        <option>Tiempo límite de carga de comprobante excedido</option>
                        <option>Datos del comprador no coinciden</option>
                        <option>Pago no acreditado por la entidad</option>
                        <option>Otro (se solicitará detalle)</option>
                    </select>
                    <div id="detalle-otro-wrapper" style="margin-top:12px; display:none;">
                        <label for="detalle-otro" style="display:block; margin-bottom:8px; color:#ccc;">Detalle</label>
                        <textarea id="detalle-otro" rows="3" style="width:100%; padding:10px; border-radius:6px; border:1px solid #444; background:#111; color:#eee; resize:vertical;" placeholder="Describe brevemente el motivo"></textarea>
                    </div>
                    <!-- Campo para monto pagado (solo aparece si es problema de monto) -->
                    <div id="monto-pagado-wrapper" style="margin-top:12px; display:none;">
                        <label for="monto-pagado" style="display:block; margin-bottom:8px; color:#FFC107; font-weight: 600;">💰 Monto que el cliente pagó</label>
                        <input type="number" id="monto-pagado" step="1000" min="0" placeholder="Ej: 15000" style="width:100%; padding:10px; border-radius:6px; border:2px solid #FFC107; background:#111; color:#eee; font-size: 16px; font-weight: 600;" />
                        <small style="color:#aaa; display:block; margin-top:6px;">💡 Ingresa el monto que realmente pagó el cliente según el comprobante</small>
                    </div>
                </div>
                <div class="modal-actions" style="display:flex; gap:10px; justify-content:flex-end; padding:14px 20px; border-top:1px solid #333;">
                    <button id="cancelar-rechazo" class="btn" style="padding:10px 14px; background:#333; color:#fff; border:none; border-radius:6px; cursor:pointer;">Cancelar</button>
                    <button id="confirmar-rechazo" class="btn btn-danger" style="padding:10px 14px; background:#b02a37; color:#fff; border:none; border-radius:6px; cursor:pointer;">Rechazar</button>
                </div>
            </div>
        </div>

        <!-- Tab de Gestión de Relojes -->
        <div id="tab-relojes" class="tab-content">
            <div class="relojes-header">
                <h2 class="section-title">Gestión de Relojes</h2>
                <div class="relojes-controls">
                    <a href="agregar_reloj.html" class="btn btn-primary">
                        ➕ Agregar Nuevo Reloj
                    </a>
                </div>
            </div>

            <!-- Estadísticas de relojes -->
            <div class="relojes-stats" id="relojes-stats">
                <div class="stats-grid-small">
                    <div class="stat-card">
                        <div class="stat-number" id="total-relojes">-</div>
                        <div class="stat-label">Total Relojes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="disponibles-relojes">-</div>
                        <div class="stat-label">Disponibles</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="vendidos-relojes">-</div>
                        <div class="stat-label">Vendidos</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="no-disponibles-relojes">-</div>
                        <div class="stat-label">No Disponibles</div>
                    </div>
                </div>
            </div>

            <!-- Lista de relojes -->
            <div class="relojes-table">
                <div class="table-content" id="relojes-content">
                    <!-- Los relojes se cargarán dinámicamente -->
                </div>
            </div>
        </div>

        <!-- Tab de Códigos de Descuento -->
        <div id="tab-codigos" class="tab-content">
            <div class="discount-header">
                <h2 class="section-title">Gestión de Códigos de Descuento</h2>
                <button class="btn btn-primary" onclick="mostrarModalCrearCodigo()">
                    ➕ Crear Nuevo Código
                </button>
            </div>

            <div class="discount-table">
                <div class="table-content" id="codigos-content">
                    <!-- Los códigos se cargarán dinámicamente -->
                </div>
            </div>
        </div>

        <!-- Tab de Gestión de Envíos -->
        <div id="tab-envios" class="tab-content">
            <!-- Estadísticas de envíos -->
            <div class="envios-stats" id="envios-stats">
                <!-- Las estadísticas se cargarán dinámicamente -->
            </div>

            <div class="envios-header">
                <h2 class="section-title">Gestión de Precios de Envío</h2>
                <div class="envios-controls">
                    <select id="filtro-departamento" class="filter-select" onchange="filtrarEnvios()">
                        <option value="">Todos los departamentos</option>
                    </select>
                    <input type="text" id="buscar-envio" class="search-input" placeholder="Buscar ciudad..." onkeyup="filtrarEnvios()">
                    <button class="btn btn-primary" onclick="mostrarModalCrearEnvio()">
                        ➕ Agregar Ciudad
                    </button>
                </div>
            </div>

            <div class="envios-table">
                <div class="table-content" id="envios-content">
                    <!-- Los envíos se cargarán dinámicamente -->
                </div>
            </div>
        </div>

        <!-- Tab de Comentarios -->
        <div id="tab-comentarios" class="tab-content">
            <!-- Estadísticas de comentarios -->
            <div class="stats-grid" id="comentarios-stats">
                <div class="stat-card">
                    <div class="stat-number" id="total-comentarios">0</div>
                    <div class="stat-label">Total Comentarios</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="pendientes-comentarios">0</div>
                    <div class="stat-label">Pendientes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="aprobados-comentarios">0</div>
                    <div class="stat-label">Aprobados</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="promedio-calificacion">0.0</div>
                    <div class="stat-label">Calificación Promedio</div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filters-section">
                <div class="filter-group">
                    <label for="filtro-estado-comentarios">Estado:</label>
                    <select id="filtro-estado-comentarios" onchange="filtrarComentarios()">
                        <option value="todos">Todos</option>
                        <option value="pendientes">Pendientes</option>
                        <option value="aprobados">Aprobados</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filtro-reloj-comentarios">Reloj:</label>
                    <select id="filtro-reloj-comentarios" onchange="filtrarComentarios()">
                        <option value="todos">Todos los relojes</option>
                    </select>
                </div>
                <button class="btn btn-primary" onclick="cargarComentarios()">
                    🔄 Actualizar
                </button>
            </div>

            <!-- Lista de comentarios -->
            <div class="comments-section">
                <h2 class="section-title">Comentarios Pendientes de Aprobación</h2>
                <div class="comments-container" id="comments-container">
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        Cargando comentarios...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar Código -->
    <div id="modal-codigo" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-titulo">Crear Código de Descuento</h3>
                <span class="close" onclick="cerrarModalCodigo()">&times;</span>
            </div>
            <form id="form-codigo" onsubmit="guardarCodigo(event)">
                <input type="hidden" id="id_codigo" name="id_codigo">
                
                <div class="form-group">
                    <label for="codigo">Código *</label>
                    <input 
                        type="text" 
                        id="codigo" 
                        name="codigo" 
                        required 
                        placeholder="Ej: VERANO2025"
                        maxlength="50"
                        style="text-transform: uppercase;">
                    <small>El código se convertirá automáticamente a mayúsculas</small>
                </div>

                <div class="form-group">
                    <label for="porcentaje">Porcentaje de Descuento (%) *</label>
                    <input 
                        type="number" 
                        id="porcentaje" 
                        name="porcentaje" 
                        required 
                        min="1" 
                        max="100" 
                        step="0.01"
                        placeholder="Ej: 20">
                    <small>Ingresa un valor entre 1 y 100</small>
                </div>

                <div class="form-group">
                    <label for="fecha_expiracion">Fecha de Expiración *</label>
                    <input 
                        type="date" 
                        id="fecha_expiracion" 
                        name="fecha_expiracion" 
                        required
                        min="<?php echo date('Y-m-d'); ?>">
                    <small>El código expirará al final de esta fecha</small>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalCodigo()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Crear/Editar Envío -->
    <div id="modal-envio" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-envio-titulo">Agregar Ciudad</h3>
                <span class="close" onclick="cerrarModalEnvio()">&times;</span>
            </div>
            <form id="form-envio" onsubmit="guardarEnvio(event)">
                <input type="hidden" id="id_envio" name="id_envio">
                
                <div class="form-group">
                    <label for="departamento_envio">Departamento *</label>
                    <input 
                        type="text" 
                        id="departamento_envio" 
                        name="departamento" 
                        required 
                        placeholder="Ej: Antioquia"
                        maxlength="100"
                        list="departamentos-list">
                    <datalist id="departamentos-list">
                        <!-- Se llenará dinámicamente -->
                    </datalist>
                    <small>Selecciona o escribe un departamento</small>
                </div>

                <div class="form-group">
                    <label for="ciudad_envio">Ciudad *</label>
                    <input 
                        type="text" 
                        id="ciudad_envio" 
                        name="ciudad" 
                        required 
                        placeholder="Ej: Medellín"
                        maxlength="100">
                    <small>Nombre de la ciudad</small>
                </div>

                <div class="form-group">
                    <label for="precio_envio">Precio de Envío ($) *</label>
                    <input 
                        type="number" 
                        id="precio_envio" 
                        name="precio" 
                        required 
                        min="1000" 
                        max="1000000" 
                        step="1000"
                        placeholder="Ej: 15000">
                    <small>Precio en pesos colombianos</small>
                </div>

                <div class="form-group">
                    <label for="dias_estimados_envio">Días Estimados *</label>
                    <input 
                        type="number" 
                        id="dias_estimados_envio" 
                        name="dias_estimados" 
                        required 
                        min="1" 
                        max="30"
                        placeholder="Ej: 3">
                    <small>Tiempo estimado de entrega en días hábiles</small>
                </div>

                <div class="form-group" id="activo-group" style="display: none;">
                    <label for="activo_envio">Estado</label>
                    <select id="activo_envio" name="activo">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                    <small>Los envíos inactivos no aparecen en el frontend</small>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalEnvio()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/panel.js"></script>
    <script>
        // Cargar datos del panel al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            cargarDatosPanel();
        });

        async function cargarDatosPanel() {
            try {
                const response = await fetch('php/obtener_datos_panel.php');
                const data = await response.json();
                
                if (data.success) {
                    mostrarEstadisticas(data.stats);
                    mostrarOrdenes(data.ordenes);
                    document.getElementById('user-name').textContent = `Bienvenido, ${data.nombre_usuario}`;
                } else {
                    console.error('Error al cargar datos:', data.message);
                    document.getElementById('stats-grid').innerHTML = '<div class="error">Error al cargar estadísticas</div>';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('stats-grid').innerHTML = '<div class="error">Error de conexión</div>';
            }
        }

        function mostrarEstadisticas(stats) {
            const statsGrid = document.getElementById('stats-grid');
            statsGrid.innerHTML = `
                <div class="stat-card">
                    <div class="stat-number">${stats.total_ordenes}</div>
                    <div class="stat-label">Total Órdenes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${stats.pendientes}</div>
                    <div class="stat-label">Pendientes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${stats.pagadas}</div>
                    <div class="stat-label">Pagadas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${stats.ventas_totales.toLocaleString('es-CO', {maximumFractionDigits:0})}.000</div>
                    <div class="stat-label">Ventas Totales</div>
                </div>
            `;
        }

        function mostrarOrdenes(ordenes) {
            const ordersContent = document.getElementById('orders-content');
            
            if (ordenes.length === 0) {
                ordersContent.innerHTML = '<div class="no-orders">No hay órdenes pendientes de verificación</div>';
                return;
            }

            let html = '';
            ordenes.forEach(orden => {
                const fecha = new Date(orden.fecha).toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const token = orden.token_verificacion ? orden.token_verificacion.substring(0, 16) + '...' : 'N/A';

                const estadoBadge = orden.estado === 'pagado' ? 
                    '<span class="badge badge-success">Pagado</span>' : 
                    orden.estado === 'pendiente_verificacion' ? 
                    '<span class="badge badge-info">Verificación</span>' : 
                    '<span class="badge badge-warning">Pendiente</span>';
                
                const comprobanteStatus = orden.comprobante_verificado ? 
                    '<span class="badge badge-success">Verificado</span>' : 
                    '<span class="badge badge-danger">Sin verificar</span>';

                html += `
                    <div class="order-item">
                        <div class="order-header">
                            <div class="order-id">Orden #${orden.id_orden} ${estadoBadge}</div>
                            <div class="order-date">${fecha}</div>
                        </div>
                        
                        <div class="order-details">
                            <div class="detail-row">
                                <div class="detail-item">
                                    <div class="detail-label">Cliente</div>
                                    <div class="detail-value">${escapeHtml(orden.nombre)}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Cédula</div>
                                    <div class="detail-value">${escapeHtml(orden.cedula)}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Celular</div>
                                    <div class="detail-value">${escapeHtml(orden.celular)}</div>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-item">
                                    <div class="detail-label">Producto${orden.cantidad_productos > 1 ? 's (' + orden.cantidad_productos + ')' : ''}</div>
                                    <div class="detail-value">${escapeHtml(orden.producto_nombre || 'N/A')}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Marca</div>
                                    <div class="detail-value">${escapeHtml(orden.marca || 'N/A')}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Precio Producto${orden.cantidad_productos > 1 ? 's' : ''}</div>
                                    <div class="detail-value">$ ${parseFloat(orden.precio_producto || 0).toLocaleString('es-CO', {maximumFractionDigits:0})}</div>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-item">
                                    <div class="detail-label">Dirección</div>
                                    <div class="detail-value">${escapeHtml(orden.direccion || 'N/A')}, ${escapeHtml(orden.barrio || 'N/A')}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Ciudad</div>
                                    <div class="detail-value">${escapeHtml(orden.ciudad || 'N/A')}, ${escapeHtml(orden.departamento || 'N/A')}</div>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-item">
                                    <div class="detail-label">Costo Envío</div>
                                    <div class="detail-value">$ ${parseFloat(orden.costo_envio || 0).toLocaleString('es-CO', {maximumFractionDigits:0})}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Total</div>
                                    <div class="detail-value"><strong>$ ${parseFloat(orden.total || 0).toLocaleString('es-CO', {maximumFractionDigits:0})}</strong></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Método Pago</div>
                                    <div class="detail-value">${escapeHtml(orden.metodo_pago || 'N/A')}</div>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-item">
                                    <div class="detail-label">Token</div>
                                    <div class="detail-value" style="font-family: monospace; font-size: 12px;">
                                        ${token}
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Comprobante</div>
                                    <div class="detail-value">${comprobanteStatus}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-actions">
                            <button class="btn btn-view" onclick="verComprobante(${orden.id_orden})">
                                Ver Comprobante
                            </button>
                            ${orden.estado === 'pagado' || orden.estado === 'aprobado' ? `
                                <button class="btn btn-success" onclick="marcarComoEnviado(${orden.id_orden})" style="background: #28a745;">
                                    🚚 Marcar Enviado
                                </button>
                                <button class="btn btn-revert" onclick="revertirAprobacion(${orden.id_orden})" style="background: #6c757d;">
                                    ↩️ Revertir Aprobación
                                </button>
                            ` : orden.estado === 'enviado' ? `
                                <button class="btn btn-info" onclick="marcarComoEntregado(${orden.id_orden})" style="background: #17a2b8;">
                                    🎁 Marcar Entregado
                                </button>
                            ` : orden.comprobante_verificado ? `
                                <button class="btn btn-approve" onclick="aprobarOrden(${orden.id_orden})">
                                    ✅ Aprobar Orden
                                </button>
                                <button class="btn btn-reject" onclick="rechazarOrden(${orden.id_orden})">
                                    ❌ Rechazar
                                </button>
                            ` : `
                                <button class="btn btn-verify" onclick="verificarComprobante(${orden.id_orden})">
                                    ✓ Verificar Comprobante
                                </button>
                                <button class="btn btn-reject" onclick="rechazarOrden(${orden.id_orden})">
                                    ❌ Rechazar
                                </button>
                            `}
                        </div>
                    </div>
                `;
            });

            ordersContent.innerHTML = html;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
