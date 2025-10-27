<?php
/**
 * MARCAR COMO ENTREGADO - Notificación WhatsApp
 * 
 * Este archivo marca una orden como entregada y envía notificación al cliente
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/WhatsAppNotificacion.php';
require_once __DIR__ . '/../../includes/WhatsAppTemplates.php';

// Para pruebas, comentar la verificación de sesión
// include '../check_session.php';

include __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$id_orden = isset($_POST['id_orden']) ? intval($_POST['id_orden']) : 0;

if ($id_orden <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de orden inválido']);
    exit;
}

try {
    // Obtener datos de la orden
    $sql = "SELECT o.*, od.precio_unitario, r.nombre as nombre_reloj
            FROM orden o
            LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
            LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
            WHERE o.id_orden = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_orden);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Orden no encontrada']);
        exit;
    }
    
    $orden = $result->fetch_assoc();
    
    // Actualizar estado de la orden
    $fecha_entrega = date('Y-m-d H:i:s');
    
    $sql_update = "UPDATE orden SET 
                   estado = 'entregado',
                   fecha_entrega = ?
                   WHERE id_orden = ?";
    
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $fecha_entrega, $id_orden);
    $stmt_update->execute();
    
    // 🔄 MARCAR RELOJ COMO VENDIDO (Sincronización automática)
    $stmt_reloj = $conn->prepare("UPDATE reloj r 
                                   INNER JOIN orden_detalle od ON r.id_reloj = od.id_reloj 
                                   SET r.vendido = 1 
                                   WHERE od.id_orden = ?");
    $stmt_reloj->bind_param("i", $id_orden);
    $stmt_reloj->execute();
    $stmt_reloj->close();
    
    // 📱 ENVIAR NOTIFICACIÓN WHATSAPP AL CLIENTE
    $whatsappEnviado = false;
    $whatsappError = null;
    
    try {
        if (verificarConfiguracionTwilio()) {
            $whatsapp = new WhatsAppNotificacion();
            
            // Preparar datos para el mensaje
            $datosWhatsApp = [
                'orden_id' => $id_orden,
                'nombre_reloj' => $orden['nombre_reloj'],
                'nombre_cliente' => $orden['nombre']
            ];
            
            // Enviar notificación al cliente
            $mensaje = WhatsAppTemplates::productoEntregado($datosWhatsApp);
            $telefono = $orden['celular'];
            $resultado = $whatsapp->enviarMensaje($telefono, $mensaje, 'producto_entregado');
            
            // Si devuelve un SID, fue exitoso
            if ($resultado) {
                $whatsappEnviado = true;
            }
        }
    } catch (Exception $e) {
        $whatsappError = $e->getMessage();
        error_log("Error al enviar WhatsApp: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Orden marcada como entregada',
        'fecha_entrega' => $fecha_entrega,
        'whatsapp_enviado' => $whatsappEnviado,
        'whatsapp_error' => $whatsappError
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

