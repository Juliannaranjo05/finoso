<?php
/**
 * OBTENER HISTORIAL DE COMPRAS DEL USUARIO
 * Retorna todas las órdenes del usuario actual con sus detalles
 */

session_start();
header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Usuario no autenticado'
    ]);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Conexión a BD
include '../../admin/conexion.php';

try {
    // Obtener órdenes del usuario con detalles del reloj
    $sql = "SELECT 
                o.id_orden,
                o.fecha,
                o.total,
                o.estado,
                o.metodo_pago,
                o.costo_envio,
                o.comprobante_pago,
                o.motivo_rechazo,
                o.monto_pagado,
                o.token_verificacion,
                o.transportadora,
                o.guia_envio,
                o.fecha_envio,
                o.fecha_entrega_estimada,
                o.fecha_entrega,
                od.precio_unitario as precio_producto,
                od.id_reloj,
                r.nombre as nombre_reloj,
                r.marca,
                r.img as imagen
            FROM orden o
            LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
            LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
            WHERE o.id_usuario = ? OR o.correo = (SELECT correo FROM usuario WHERE id_usuario = ?)
            ORDER BY o.fecha DESC
            LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_usuario, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $ordenes = [];
    while ($row = $result->fetch_assoc()) {
        // Formatear imagen - simplemente pasar el path relativo limpio
        if ($row['imagen'] && !empty($row['imagen'])) {
            // Asegurar que el path sea relativo desde la raíz del proyecto
            // Las imágenes están en /img/nombre.png
            $row['imagen'] = $row['imagen'];
        }
        
        $ordenes[] = $row;
    }
    
    $stmt->close();
    
    // Calcular estadísticas
    // NOTA: Solo se cuentan las órdenes ENTREGADAS (completadas exitosamente)
    $stats = [
        'total_ordenes' => count($ordenes),
        'total_relojes' => count(array_filter($ordenes, function($o) {
            return $o['estado'] === 'entregado'; // Solo relojes entregados
        })),
        'total_gastado' => array_sum(array_map(function($o) {
            return $o['estado'] === 'entregado' ? $o['total'] : 0; // Solo órdenes entregadas
        }, $ordenes))
    ];
    
    echo json_encode([
        'success' => true,
        'ordenes' => $ordenes,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener historial: ' . $e->getMessage()
    ]);
}

$conn->close();
?>


