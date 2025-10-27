<?php
/**
 * SUBIR COMPROBANTE DE DIFERENCIA
 * Procesa el pago adicional de órdenes rechazadas por monto incorrecto
 */

session_start();

// Obtener datos
$id_orden = isset($_POST['id_orden']) ? (int)$_POST['id_orden'] : 0;
$diferencia = isset($_POST['diferencia']) ? (float)$_POST['diferencia'] : 0;
$token = isset($_POST['token']) ? $_POST['token'] : null;
$id_usuario_sesion = $_SESSION['id_usuario'] ?? null;

error_log('[SUBIR-DIFERENCIA] Datos recibidos - Orden: ' . $id_orden . ', Diferencia: ' . $diferencia . ', Token: ' . ($token ? 'Sí' : 'No') . ', Sesión: ' . ($id_usuario_sesion ? 'Sí' : 'No'));

if ($id_orden === 0 || $diferencia <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

// Verificar que se subió un archivo
if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No se recibió el comprobante']);
    exit;
}

// Conexión a BD
include '../../admin/conexion.php';

try {
    // 1. VERIFICAR QUE LA ORDEN PERTENEZCA AL USUARIO Y ESTÉ RECHAZADA
    $sql_verificar = "SELECT o.id_orden, o.monto_pagado, o.total, o.estado, o.id_usuario, o.correo, o.nombre, o.celular,
                             o.token_verificacion,
                             r.nombre as nombre_reloj
                      FROM orden o
                      LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
                      LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
                      WHERE o.id_orden = ? AND o.estado = 'rechazado'";
    
    $stmt = $conn->prepare($sql_verificar);
    $stmt->bind_param("i", $id_orden);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Orden no encontrada o no está rechazada']);
        exit;
    }
    
    $orden = $result->fetch_assoc();
    $stmt->close();
    
    // VALIDAR ACCESO: Por token O por sesión
    $acceso_valido = false;
    
    // Opción 1: Validar por token (funciona sin sesión)
    if ($token && $orden['token_verificacion'] === $token) {
        $acceso_valido = true;
        error_log('[SUBIR-DIFERENCIA] Acceso concedido por token válido - Orden #' . $id_orden);
    }
    // Opción 2: Validar por sesión (si hay id_usuario)
    elseif ($id_usuario_sesion && $orden['id_usuario'] == $id_usuario_sesion) {
        $acceso_valido = true;
        error_log('[SUBIR-DIFERENCIA] Acceso concedido por sesión - Usuario #' . $id_usuario_sesion . ', Orden #' . $id_orden);
    }
    // Opción 3: Validar por email en sesión
    elseif (isset($_SESSION['correo']) && $orden['correo'] === $_SESSION['correo']) {
        $acceso_valido = true;
        error_log('[SUBIR-DIFERENCIA] Acceso concedido por email en sesión - Orden #' . $id_orden);
    }
    
    if (!$acceso_valido) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'No tienes permiso para modificar esta orden']);
        error_log('[SUBIR-DIFERENCIA] Acceso denegado - Orden #' . $id_orden . ', Token proporcionado: ' . ($token ? 'Sí' : 'No') . ', Sesión: ' . ($id_usuario_sesion ? 'Sí' : 'No'));
        exit;
    }
    
    // 2. PROCESAR Y GUARDAR EL COMPROBANTE
    $archivo = $_FILES['comprobante'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'pdf'];
    
    if (!in_array($extension, $extensiones_permitidas)) {
        echo json_encode(['success' => false, 'error' => 'Formato de archivo no permitido']);
        exit;
    }
    
    // Generar nombre único para el comprobante
    $nombre_archivo = 'comprobante_diferencia_' . $id_orden . '_' . uniqid() . '.' . $extension;
    $ruta_destino = __DIR__ . '/../comprobantes/' . $nombre_archivo;
    
    // Crear carpeta si no existe
    if (!file_exists(__DIR__ . '/../comprobantes/')) {
        mkdir(__DIR__ . '/../comprobantes/', 0777, true);
    }
    
    // Mover archivo
    if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        echo json_encode(['success' => false, 'error' => 'Error al guardar el comprobante']);
        exit;
    }
    
    // Convertir imagen a base64 para BD
    $comprobante_base64 = base64_encode(file_get_contents($ruta_destino));
    $mime_type = mime_content_type($ruta_destino);
    $comprobante_data = "data:$mime_type;base64,$comprobante_base64";
    
    // 3. ACTUALIZAR LA ORDEN
    // Sumar la diferencia al monto_pagado y cambiar estado a pendiente_verificacion
    $nuevo_monto_pagado = $orden['monto_pagado'] + $diferencia;
    
    $sql_update = "UPDATE orden 
                   SET monto_pagado = ?,
                       estado = 'pendiente_verificacion',
                       comprobante_pago = ?,
                       nombre_archivo_comprobante = ?,
                       fecha = NOW()
                   WHERE id_orden = ?";
    
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("dssi", $nuevo_monto_pagado, $comprobante_data, $nombre_archivo, $id_orden);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al actualizar la orden: ' . $conn->error);
    }
    $stmt->close();
    
    // 4. ENVIAR NOTIFICACIONES WHATSAPP
    try {
        require_once __DIR__ . '/../../includes/WhatsAppNotificacion.php';
        require_once __DIR__ . '/../../includes/WhatsAppTemplates.php';
        require_once __DIR__ . '/../../config/twilio_config.php';

        if (verificarConfiguracionTwilio()) {
            $whatsapp = new WhatsAppNotificacion();
            
            // Notificación al cliente
            $datosCliente = [
                'orden_id' => $id_orden,
                'nombre_reloj' => $orden['nombre_reloj'] ?: 'tu reloj',
                'total' => $orden['total'],
                'nombre_cliente' => $orden['nombre'] ?: 'Cliente'
            ];
            
            $mensajeCliente = WhatsAppTemplates::compraExitosa($datosCliente);
            $whatsapp->enviarMensaje($orden['celular'], $mensajeCliente, 'comprobante_diferencia_recibido');
            
            // Notificación al admin
            $datosAdmin = [
                'orden_id' => $id_orden,
                'nombre_cliente' => $orden['nombre'] ?: 'Cliente',
                'telefono' => $orden['celular'],
                'email' => $orden['correo'],
                'nombre_reloj' => $orden['nombre_reloj'] ?: 'Reloj',
                'total' => $orden['total'],
                'metodo_pago' => 'Nequi (Diferencia)'
            ];
            
            $mensajeAdmin = "🔔 COMPROBANTE ADICIONAL RECIBIDO\n\n" .
                          "Orden #{$id_orden}\n" .
                          "Cliente: {$datosAdmin['nombre_cliente']}\n" .
                          "📱 {$datosAdmin['telefono']}\n\n" .
                          "💰 Pagó anteriormente: \$" . number_format($orden['monto_pagado'], 0, ',', '.') . "\n" .
                          "💰 Pagó ahora (diferencia): \$" . number_format($diferencia, 0, ',', '.') . "\n" .
                          "💰 Total pagado: \$" . number_format($nuevo_monto_pagado, 0, ',', '.') . "\n" .
                          "💰 Total del pedido: \$" . number_format($orden['total'], 0, ',', '.') . "\n\n" .
                          "⌚ {$datosAdmin['nombre_reloj']}\n\n" .
                          "✅ Nuevo comprobante adjunto\n" .
                          "👉 Revisar orden en el panel";
            
            $whatsapp->enviarMensaje(ADMIN_WHATSAPP, $mensajeAdmin, 'comprobante_diferencia_admin');
        }
    } catch (Exception $e) {
        error_log("Error al enviar WhatsApp: " . $e->getMessage());
        // No fallar la operación si falla WhatsApp
    }
    
    // 5. REDIRIGIR A PÁGINA DE ÉXITO CON TODOS LOS DATOS
    $params_array = [
        'orden' => $id_orden,
        'tipo' => 'diferencia',
        'diferencia' => $diferencia,
        'total_orden' => $orden['total'],
        'monto_anterior' => $orden['monto_pagado'],
        'monto_nuevo' => $nuevo_monto_pagado
    ];
    
    // Agregar token si está disponible (para acceso sin sesión)
    if ($token && $orden['token_verificacion'] === $token) {
        $params_array['token'] = $token;
        error_log('[SUBIR-DIFERENCIA] Redirigiendo con token para acceso sin sesión');
    }
    
    $params = http_build_query($params_array);
    header('Location: ../pago_exitoso_wompi.html?' . $params);
    exit;
    
} catch (Exception $e) {
    error_log("Error en subir_comprobante_diferencia.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error al procesar el pago: ' . $e->getMessage()]);
}

$conn->close();
?>

