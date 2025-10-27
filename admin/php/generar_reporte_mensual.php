<?php
/**
 * GENERAR REPORTE MENSUAL - Notificación WhatsApp al Admin
 * 
 * Este archivo genera un reporte mensual y lo envía al admin por WhatsApp
 * Se puede ejecutar manualmente o mediante CRON al final de cada mes
 * 
 * CRON sugerido: 0 8 1 * * (Ejecutar el día 1 de cada mes a las 8:00 AM)
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/WhatsAppNotificacion.php';
require_once __DIR__ . '/../../includes/WhatsAppTemplates.php';

// Permitir ejecución desde CLI (CRON) o web
$is_cli = php_sapi_name() === 'cli';

// Si no es CLI, verificar sesión de admin
if (!$is_cli) {
    // Para pruebas, comentar verificación de sesión
    // include '../check_session.php';
}

include __DIR__ . '/../conexion.php';

// Obtener mes y año (del mes ACTUAL para pruebas y reportes en tiempo real)
$mes_actual = date('n'); // Mes actual
$anio = date('Y'); // Año actual
$nombre_mes = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
][$mes_actual];

try {
    // Calcular ventas totales del mes (SOLO ÓRDENES VÁLIDAS: pagado, aprobado, enviado, entregado)
    $sql_ventas = "SELECT 
                    COUNT(DISTINCT o.id_orden) as num_ordenes,
                    SUM(o.total) as ventas_total,
                    SUM(od.precio_unitario) as ventas_productos,
                    SUM(o.costo_envio) as ventas_envios,
                    AVG(o.total) as ticket_promedio,
                    COUNT(DISTINCT CASE WHEN o.estado = 'entregado' THEN o.id_orden END) as ordenes_entregadas,
                    COUNT(DISTINCT CASE WHEN o.estado IN ('pendiente', 'pendiente_verificacion') THEN o.id_orden END) as ordenes_pendientes,
                    COUNT(DISTINCT CASE WHEN o.estado = 'enviado' THEN o.id_orden END) as ordenes_enviadas,
                    COUNT(DISTINCT CASE WHEN o.estado IN ('pagado', 'aprobado') THEN o.id_orden END) as ordenes_pagadas
                   FROM orden o
                   LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
                   WHERE MONTH(o.fecha) = ? AND YEAR(o.fecha) = ?
                   AND o.estado IN ('pagado', 'aprobado', 'enviado', 'entregado')";
    
    $stmt = $conn->prepare($sql_ventas);
    $stmt->bind_param("ii", $mes_actual, $anio);
    $stmt->execute();
    $result = $stmt->get_result();
    $datos = $result->fetch_assoc();
    
    // Obtener relojes más vendidos (SOLO DE ÓRDENES VÁLIDAS)
    $sql_top_relojes = "SELECT r.nombre, r.marca, COUNT(od.id_reloj) as cantidad
                        FROM orden_detalle od
                        JOIN reloj r ON od.id_reloj = r.id_reloj
                        JOIN orden o ON od.id_orden = o.id_orden
                        WHERE MONTH(o.fecha) = ? AND YEAR(o.fecha) = ?
                        AND o.estado IN ('pagado', 'aprobado', 'enviado', 'entregado')
                        GROUP BY od.id_reloj
                        ORDER BY cantidad DESC
                        LIMIT 3";
    
    $stmt_top = $conn->prepare($sql_top_relojes);
    $stmt_top->bind_param("ii", $mes_actual, $anio);
    $stmt_top->execute();
    $result_top = $stmt_top->get_result();
    
    $top_relojes = [];
    while ($row = $result_top->fetch_assoc()) {
        $top_relojes[] = $row;
    }
    
    // 📱 ENVIAR REPORTE POR WHATSAPP AL ADMIN
    $whatsappEnviado = false;
    $whatsappError = null;
    
    try {
        if (verificarConfiguracionTwilio()) {
            $whatsapp = new WhatsAppNotificacion();
            
            // Preparar datos del reporte
            $datosWhatsApp = [
                'mes' => $nombre_mes,
                'anio' => $anio,
                'ventas_total' => $datos['ventas_total'] ?: 0,
                'ventas_productos' => $datos['ventas_productos'] ?: 0,
                'ventas_envios' => $datos['ventas_envios'] ?: 0,
                'num_ordenes' => $datos['num_ordenes'] ?: 0,
                'ticket_promedio' => $datos['ticket_promedio'] ?: 0,
                'ordenes_entregadas' => $datos['ordenes_entregadas'] ?: 0,
                'ordenes_enviadas' => $datos['ordenes_enviadas'] ?: 0,
                'ordenes_pagadas' => $datos['ordenes_pagadas'] ?: 0,
                'top_relojes' => $top_relojes
            ];
            
            // Enviar reporte al admin
            $mensaje = WhatsAppTemplates::reporteMensualAdmin($datosWhatsApp);
            $resultado = $whatsapp->enviarMensaje(ADMIN_WHATSAPP, $mensaje, 'reporte_mensual');
            
            // Si devuelve un SID, fue exitoso
            if ($resultado) {
                $whatsappEnviado = true;
            }
        }
    } catch (Exception $e) {
        $whatsappError = $e->getMessage();
        error_log("Error al enviar reporte mensual por WhatsApp: " . $e->getMessage());
    }
    
    $response = [
        'success' => true,
        'mes' => $nombre_mes,
        'anio' => $anio,
        'datos' => $datos,
        'top_relojes' => $top_relojes,
        'whatsapp_enviado' => $whatsappEnviado,
        'whatsapp_error' => $whatsappError
    ];
    
    if ($is_cli) {
        echo "\n📊 REPORTE MENSUAL GENERADO - {$nombre_mes} {$anio}\n";
        echo "Ventas: $" . number_format($datos['ventas_total'], 0, ',', '.') . "\n";
        echo "Órdenes: {$datos['num_ordenes']}\n";
        echo "WhatsApp enviado: " . ($whatsappEnviado ? 'Sí ✅' : 'No ❌') . "\n";
    } else {
        echo json_encode($response);
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    
    if ($is_cli) {
        echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    } else {
        echo json_encode($response);
    }
}
?>

