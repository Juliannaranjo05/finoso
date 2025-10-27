<?php
/**
 * OBTENER ORDEN RECHAZADA
 * Retorna los datos de una orden rechazada para el proceso de recuperación
 */

session_start();
header('Content-Type: application/json');

// Obtener parámetros
$id_orden = isset($_GET['orden']) ? (int)$_GET['orden'] : 0;
$token = isset($_GET['token']) ? $_GET['token'] : null;
$id_usuario_sesion = $_SESSION['id_usuario'] ?? null;

if ($id_orden === 0) {
    echo json_encode([
        'success' => false,
        'error' => 'ID de orden inválido'
    ]);
    exit;
}

// No requerir sesión si se proporciona token
// La validación se hará después con el token de la orden

// Conexión a BD
include '../../admin/conexion.php';

try {
    // Obtener datos de la orden rechazada
    $sql = "SELECT 
                o.id_orden,
                o.fecha,
                o.total,
                o.estado,
                o.metodo_pago,
                o.costo_envio,
                o.motivo_rechazo,
                o.monto_pagado,
                o.id_usuario,
                o.correo,
                o.token_verificacion,
                od.precio_unitario as precio_producto,
                od.id_reloj,
                r.nombre as nombre_reloj,
                r.marca,
                r.img as imagen
            FROM orden o
            LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
            LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
            WHERE o.id_orden = ?
            AND o.estado = 'rechazado'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_orden);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Orden no encontrada o no está rechazada'
        ]);
        exit;
    }
    
    $orden = $result->fetch_assoc();
    $stmt->close();
    
    // VALIDAR ACCESO: Por token O por sesión
    $acceso_valido = false;
    
    // Opción 1: Validar por token (funciona sin sesión)
    if ($token && $orden['token_verificacion'] === $token) {
        $acceso_valido = true;
        error_log('[RECUPERAR-PAGO] Acceso concedido por token válido - Orden #' . $id_orden);
    }
    // Opción 2: Validar por sesión (si hay id_usuario)
    elseif ($id_usuario_sesion && $orden['id_usuario'] == $id_usuario_sesion) {
        $acceso_valido = true;
        error_log('[RECUPERAR-PAGO] Acceso concedido por sesión - Usuario #' . $id_usuario_sesion . ', Orden #' . $id_orden);
    }
    // Opción 3: Validar por email en sesión (compras sin sesión pero ahora logueado)
    elseif (isset($_SESSION['correo']) && $orden['correo'] === $_SESSION['correo']) {
        $acceso_valido = true;
        error_log('[RECUPERAR-PAGO] Acceso concedido por email en sesión - Orden #' . $id_orden);
    }
    
    if (!$acceso_valido) {
        echo json_encode([
            'success' => false,
            'error' => 'No tienes permiso para acceder a esta orden'
        ]);
        error_log('[RECUPERAR-PAGO] Acceso denegado - Orden #' . $id_orden . ', Token: ' . ($token ? 'Proporcionado' : 'No') . ', Sesión: ' . ($id_usuario_sesion ? 'Sí' : 'No'));
        exit;
    }
    
    // Verificar que tenga monto_pagado (es problema de monto)
    if (!isset($orden['monto_pagado']) || $orden['monto_pagado'] <= 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Esta orden no tiene un monto pagado registrado. Por favor, contacta soporte.'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'orden' => $orden
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener los datos: ' . $e->getMessage()
    ]);
}

$conn->close();
?>

