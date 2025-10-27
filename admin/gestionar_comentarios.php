<?php
// Verificar sesión de administrador
require_once 'check_session.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Comentarios - Finoso Admin</title>
    <link rel="icon" href="../img/finoso_logo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
        <div class="page-header">
            <h1><i class="fas fa-comments"></i> Gestionar Comentarios</h1>
            <p>Revisa y aprueba los comentarios de los usuarios</p>
            <a href="panel.html" class="btn-back">← Volver al Panel</a>
        </div>

        <!-- Estadísticas de comentarios -->
        <div class="stats-grid" id="stats-comentarios">
            <div class="stat-card">
                <div class="stat-number" id="total-comentarios">0</div>
                <div class="stat-label">Total Comentarios</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="pendientes">0</div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="aprobados">0</div>
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
                <label for="filtro-estado">Estado:</label>
                <select id="filtro-estado">
                    <option value="todos">Todos</option>
                    <option value="pendientes">Pendientes</option>
                    <option value="aprobados">Aprobados</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filtro-reloj">Reloj:</label>
                <select id="filtro-reloj">
                    <option value="todos">Todos los relojes</option>
                </select>
            </div>
            <button class="btn-refresh" onclick="cargarComentarios()">
                <i class="fas fa-sync-alt"></i> Actualizar
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

    <script>
        let comentariosData = [];

        document.addEventListener('DOMContentLoaded', function() {
            cargarComentarios();
        });

        async function cargarComentarios() {
            try {
                const response = await fetch('php/gestionar_comentarios.php?accion=obtener_pendientes');
                const data = await response.json();
                
                if (data.success) {
                    comentariosData = data.comentarios;
                    mostrarComentarios(comentariosData);
                    actualizarEstadisticas();
                } else {
                    mostrarError('Error al cargar comentarios: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error de conexión');
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
                        <button class="btn btn-approve" onclick="aprobarComentario(${comentario.id})">
                            <i class="fas fa-check"></i> Aprobar
                        </button>
                        <button class="btn btn-reject" onclick="rechazarComentario(${comentario.id})">
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
            if (!confirm('¿Estás seguro de que quieres aprobar este comentario?')) {
                return;
            }

            try {
                const response = await fetch('php/gestionar_comentarios.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `accion=aprobar&id=${id}`
                });

                const data = await response.json();
                
                if (data.success) {
                    mostrarMensaje(data.message, 'success');
                    cargarComentarios(); // Recargar la lista
                } else {
                    mostrarMensaje('Error: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarMensaje('Error de conexión', 'error');
            }
        }

        async function rechazarComentario(id) {
            if (!confirm('¿Estás seguro de que quieres rechazar este comentario? Esta acción no se puede deshacer.')) {
                return;
            }

            try {
                const response = await fetch('php/gestionar_comentarios.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `accion=rechazar&id=${id}`
                });

                const data = await response.json();
                
                if (data.success) {
                    mostrarMensaje(data.message, 'success');
                    cargarComentarios(); // Recargar la lista
                } else {
                    mostrarMensaje('Error: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarMensaje('Error de conexión', 'error');
            }
        }

        function actualizarEstadisticas() {
            const total = comentariosData.length;
            const pendientes = comentariosData.length; // Solo mostramos pendientes
            const aprobados = 0; // Se calcularía con otra consulta
            const promedio = comentariosData.length > 0 ? 
                (comentariosData.reduce((sum, c) => sum + c.calificacion, 0) / comentariosData.length).toFixed(1) : 0;

            document.getElementById('total-comentarios').textContent = total;
            document.getElementById('pendientes').textContent = pendientes;
            document.getElementById('aprobados').textContent = aprobados;
            document.getElementById('promedio-calificacion').textContent = promedio;
        }

        function mostrarError(mensaje) {
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

        function mostrarMensaje(mensaje, tipo) {
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
    </script>

    <style>
        .page-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255, 207, 102, 0.1);
            border-radius: 10px;
        }

        .page-header h1 {
            color: #FFCF66;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #ccc;
            font-size: 16px;
        }

        .btn-back {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: rgba(255, 207, 102, 0.2);
            color: #FFCF66;
            text-decoration: none;
            border-radius: 5px;
            border: 1px solid rgba(255, 207, 102, 0.3);
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: rgba(255, 207, 102, 0.3);
            transform: translateY(-2px);
        }

        .filters-section {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            color: #FFCF66;
            font-weight: 600;
        }

        .filter-group select {
            padding: 8px 12px;
            border: 1px solid rgba(255, 207, 102, 0.3);
            border-radius: 5px;
            background: rgba(0, 0, 0, 0.5);
            color: #FFCF66;
        }

        .btn-refresh {
            background: linear-gradient(135deg, #FFCF66, #FFB84D);
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-refresh:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 207, 102, 0.4);
        }

        .comments-section {
            margin-top: 30px;
        }

        .comment-item {
            background: rgba(0, 0, 0, 0.4);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 207, 102, 0.2);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .comment-info h3 {
            color: #FFCF66;
            margin: 0 0 5px 0;
        }

        .comment-date {
            color: #999;
            font-size: 14px;
        }

        .comment-product {
            color: #ccc;
            font-size: 14px;
            font-style: italic;
        }

        .comment-rating {
            display: flex;
            gap: 2px;
        }

        .star {
            font-size: 18px;
        }

        .star-filled {
            color: #FFCF66;
        }

        .star-empty {
            color: #666;
        }

        .comment-content {
            margin-bottom: 15px;
        }

        .comment-content p {
            color: #E0E0E0;
            line-height: 1.6;
            margin: 0;
        }

        .comment-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-approve {
            background: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background: #218838;
        }

        .btn-reject {
            background: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background: #c82333;
        }

        .no-comments,
        .error-message,
        .loading {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .no-comments i,
        .error-message i,
        .loading i {
            font-size: 48px;
            color: #FFCF66;
            margin-bottom: 15px;
            display: block;
        }

        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background: #28a745;
        }

        .alert-error {
            background: #dc3545;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
    </style>
</body>
</html>
