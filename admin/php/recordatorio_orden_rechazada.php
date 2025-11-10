<?php
/**
 * RECORDATORIO ORDEN RECHAZADA
 * 
 * Este script detecta órdenes rechazadas hace más de 24 horas
 * y envía un recordatorio por WhatsApp al cliente.
 * 
 * Se ejecuta automáticamente mediante CRON cada 12 horas.
 * Ejemplo CRON: 0 12,0 * * * (ejecutar a mediodia y medianoche)
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/WhatsAppNotificacion.php';
require_once __DIR__ . '/../../includes/WhatsAppTemplates.php';
require_once __DIR__ . '/../../config/twilio_config.php';

// Permitir ejecución desde CLI (CRON) o web
$is_cli = php_sapi_name() === 'cli';

// Si no es CLI, verificar sesión de admin (comentado para pruebas)
if (!$is_cli) {
    // include '../check_session.php';
}

include __DIR__ . '/../conexion.php';

try {
    // Buscar órdenes rechazadas hace más de 24 horas que NO tienen recordatorio enviado
    $intervalo_horas = 24; // 24 horas en producción
    
    $sql = "SELECT 
                o.id_orden,
                o.nombre as nombre_cliente,
                o.celular,
                o.total,
                o.motivo_rechazo,
                o.monto_pagado,
                o.fecha,
                r.nombre as nombre_reloj
            FROM orden o
            LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
            LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
            WHERE o.estado = 'rechazado'
            AND o.fecha < DATE_SUB(NOW(), INTERVAL ? HOUR)
            AND (o.recordatorio_enviado IS NULL OR o.recordatorio_enviado = 0)
            ORDER BY o.fecha DESC
            LIMIT 10";
    
    // Ejecutar query para buscar órdenes
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $intervalo_horas);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $recordatorios_enviados = 0;
    $errores = [];
    
    if ($result && $result->num_rows > 0) {
        while ($orden = $result->fetch_assoc()) {
            try {
                if (verificarConfiguracionTwilio()) {
                    $whatsapp = new WhatsAppNotificacion();
                    
                    // Preparar datos para el mensaje
                    $datosWhatsApp = [
                        'orden_id' => $orden['id_orden'],
                        'nombre_cliente' => $orden['nombre_cliente'],
                        'nombre_reloj' => $orden['nombre_reloj'] ?? 'Reloj FINOSO',
                        'total' => $orden['total'],
                        'motivo_rechazo' => $orden['motivo_rechazo'] ?? 'Inconsistencias en el comprobante de pago'
                    ];
                    
                    // Si es problema de monto, agregar datos adicionales y URL de recuperación
                    $monto_pagado = floatval($orden['monto_pagado'] ?? 0);
                    if ($monto_pagado > 0) {
                        $diferencia = $orden['total'] - $monto_pagado;
                        $datosWhatsApp['monto_pagado'] = $monto_pagado;
                        $datosWhatsApp['diferencia'] = $diferencia;
                        $datosWhatsApp['url_recuperacion'] = "https://finoso.store/informacion/recuperar_pago.html?orden={$orden['id_orden']}&token={$orden['token_verificacion']}";
                    }
                    
                    // Enviar WhatsApp
                    $mensaje = WhatsAppTemplates::ordenRechazada($datosWhatsApp);
                    $resultado = $whatsapp->enviarMensaje($orden['celular'], $mensaje, 'orden_rechazada');
                    
                    if ($resultado) {
                        // Marcar como recordatorio enviado
                        $stmt = $conn->prepare("UPDATE orden SET recordatorio_enviado = 1 WHERE id_orden = ?");
                        $stmt->bind_param("i", $orden['id_orden']);
                        $stmt->execute();
                        $stmt->close();
                        
                        $recordatorios_enviados++;
                        error_log("Recordatorio orden rechazada enviado: Orden #{$orden['id_orden']}");
                    }
                }
            } catch (Exception $e) {
                $error_msg = "Error al enviar recordatorio orden #{$orden['id_orden']}: " . $e->getMessage();
                error_log($error_msg);
                $errores[] = $error_msg;
            }
        }
    }
    
    // Guardar el número de filas antes de cerrar
    $num_ordenes = $result ? $result->num_rows : 0;
    
    // Cerrar el statement solo si existe y no está cerrado
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        try {
            $stmt->close();
        } catch (Exception $e) {
            // Ignorar si ya está cerrado
        }
    }
    
    $response = [
        'success' => true,
        'recordatorios_enviados' => $recordatorios_enviados,
        'ordenes_procesadas' => $num_ordenes,
        'errores' => $errores
    ];
    
    if ($is_cli) {
        echo "\n📱 RECORDATORIOS ÓRDENES RECHAZADAS\n";
        echo "✅ Enviados: {$recordatorios_enviados}\n";
        echo "📋 Procesadas: {$num_ordenes}\n";
        if (!empty($errores)) {
            echo "❌ Errores: " . count($errores) . "\n";
        }
    } else {
        echo json_encode($response);
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    
    if ($is_cli) {
        echo "\n❌ Error: " . $e->getMessage() . "\n";
    } else {
        echo json_encode($response);
    }
}

$conn->close();
?>

