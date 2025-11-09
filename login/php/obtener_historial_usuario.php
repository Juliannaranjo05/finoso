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
    $ordenesUnicas = [];

    while ($row = $result->fetch_assoc()) {
        if ($row['imagen'] && !empty($row['imagen'])) {
            $row['imagen'] = $row['imagen'];
        }

        $ordenes[] = $row;

        $idOrden = $row['id_orden'];
        if (!isset($ordenesUnicas[$idOrden])) {
            $ordenesUnicas[$idOrden] = [
                'estado' => strtolower($row['estado']),
                'total' => is_null($row['total']) ? 0 : (float) $row['total'],
                'monto_pagado' => is_null($row['monto_pagado']) ? null : (float) $row['monto_pagado'],
                'costo_envio' => is_null($row['costo_envio']) ? 0 : (float) $row['costo_envio'],
                'total_productos' => 0
            ];
        } else {
            // Mantener el estado más avanzado si llega información diferente
            $estadoActual = $ordenesUnicas[$idOrden]['estado'];
            $nuevoEstado = strtolower($row['estado']);
            $prioridadEstados = [
                'pendiente' => 1,
                'pendiente_verificacion' => 2,
                'rechazado' => 0,
                'pagado' => 3,
                'aprobado' => 4,
                'enviado' => 5,
                'entregado' => 6,
                'cancelado' => -1
            ];
            $prioridadActual = $prioridadEstados[$estadoActual] ?? 0;
            $prioridadNueva = $prioridadEstados[$nuevoEstado] ?? 0;
            if ($prioridadNueva > $prioridadActual) {
                $ordenesUnicas[$idOrden]['estado'] = $nuevoEstado;
            }

            // Actualizar totales si llegan valores no nulos
            if (!is_null($row['total']) && (float) $row['total'] > 0) {
                $ordenesUnicas[$idOrden]['total'] = (float) $row['total'];
            }
            if (!is_null($row['monto_pagado']) && (float) $row['monto_pagado'] > 0) {
                $ordenesUnicas[$idOrden]['monto_pagado'] = (float) $row['monto_pagado'];
            }
            if (!is_null($row['costo_envio']) && (float) $row['costo_envio'] > 0) {
                $ordenesUnicas[$idOrden]['costo_envio'] = (float) $row['costo_envio'];
            }
        }

        $ordenesUnicas[$idOrden]['total_productos'] += is_null($row['precio_producto']) ? 0 : (float) $row['precio_producto'];
    }
    
    $stmt->close();

    $estadosCompletados = ['pagado', 'aprobado', 'enviado', 'entregado'];
    $totalRelojes = 0;
    $totalGastado = 0;

    foreach ($ordenesUnicas as $orden) {
        if (in_array($orden['estado'], $estadosCompletados, true)) {
            $totalRelojes++;
            $monto = $orden['monto_pagado'];

            if (is_null($monto) || $monto <= 0) {
                if ($orden['total'] > 0) {
                    $monto = $orden['total'];
                } else {
                    $monto = $orden['total_productos'] + $orden['costo_envio'];
                }
            }

            $totalGastado += $monto;
        }
    }

    $stats = [
        'total_ordenes' => count($ordenesUnicas),
        'total_relojes' => $totalRelojes,
        'total_gastado' => $totalGastado
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


