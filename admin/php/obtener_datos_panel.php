<?php
// Configurar headers para CORS y JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Iniciar sesión
session_start();

try {
    // Verificar que el usuario esté logueado y sea administrador
    if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
        throw new Exception('Acceso denegado: se requiere permisos de administrador');
    }

    // Incluir conexión a la base de datos
    include '../../login/php/conexion.php';

    // Verificar conexión
    if ($conn->connect_error) {
        throw new Exception('Error de conexión a la base de datos: ' . $conn->connect_error);
    }

    // Obtener estadísticas
    $stats = [];
    
    // Total de órdenes
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orden");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_ordenes'] = $result->fetch_assoc()['total'];
    $stmt->close();

    // Órdenes pendientes
    $stmt = $conn->prepare("SELECT COUNT(*) as pendientes FROM orden WHERE estado = 'pendiente'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['pendientes'] = $result->fetch_assoc()['pendientes'];
    $stmt->close();

    // Órdenes pagadas
    $stmt = $conn->prepare("SELECT COUNT(*) as pagadas FROM orden WHERE estado = 'pagado'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['pagadas'] = $result->fetch_assoc()['pagadas'];
    $stmt->close();

    // Ventas totales
    $stmt = $conn->prepare("SELECT COALESCE(SUM(total), 0) as ventas_totales FROM orden WHERE estado = 'pagado' AND comprobante_verificado = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $ventas_raw = $result->fetch_assoc()['ventas_totales'];
    $stats['ventas_totales'] = '$ ' . number_format($ventas_raw / 1000, 0, ',', '.');
    $stmt->close();

    // Obtener todas las órdenes con detalles (no solo pendientes)
    // 🔧 CORREGIDO: GROUP BY para evitar duplicados cuando hay múltiples productos
    $stmt = $conn->prepare("
        SELECT 
            o.id_orden,
            o.nombre,
            o.cedula,
            o.celular,
            o.total,
            o.fecha,
            o.token_verificacion,
            o.estado,
            o.comprobante_pago,
            o.nombre_archivo_comprobante,
            o.comprobante_verificado,
            o.departamento,
            o.ciudad,
            o.direccion,
            o.barrio,
            o.metodo_pago,
            o.costo_envio,
            GROUP_CONCAT(r.nombre SEPARATOR ', ') as producto_nombre,
            GROUP_CONCAT(DISTINCT r.marca SEPARATOR ', ') as marca,
            SUM(od.precio_unitario) - o.costo_envio as precio_total_productos,
            COUNT(od.id_reloj) as cantidad_productos
        FROM orden o
        LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
        LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
        WHERE o.estado IN ('pendiente', 'pendiente_verificacion', 'pagado', 'aprobado', 'enviado')
        GROUP BY o.id_orden
        ORDER BY o.fecha DESC
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    $ordenes = [];

    while ($row = $result->fetch_assoc()) {
        // Limpiar datos para JSON
        $orden = [
            'id_orden' => (int)$row['id_orden'],
            'nombre' => $row['nombre'] ?? '',
            'cedula' => $row['cedula'] ?? '',
            'celular' => $row['celular'] ?? '',
            'total' => (float)$row['total'],
            'fecha' => $row['fecha'],
            'token_verificacion' => $row['token_verificacion'] ?? '',
            'estado' => $row['estado'] ?? '',
            'producto_nombre' => $row['producto_nombre'] ?? 'Sin especificar',  // Ahora contiene todos los productos
            'marca' => $row['marca'] ?? '',
            'precio_producto' => (float)$row['precio_total_productos'], // Suma de todos los productos
            'cantidad_productos' => (int)$row['cantidad_productos'],
            'departamento' => $row['departamento'] ?? '',
            'ciudad' => $row['ciudad'] ?? '',
            'direccion' => $row['direccion'] ?? '',
            'barrio' => $row['barrio'] ?? '',
            'metodo_pago' => $row['metodo_pago'] ?? '',
            'costo_envio' => (float)$row['costo_envio'],
            'comprobante_pago' => $row['comprobante_pago'] ?? '',
            'nombre_archivo_comprobante' => $row['nombre_archivo_comprobante'] ?? '',
            'comprobante_verificado' => (bool)$row['comprobante_verificado']
        ];
        $ordenes[] = $orden;
    }
    $stmt->close();

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'ordenes' => $ordenes,
        'nombre_usuario' => $_SESSION['nombre'] ?? 'Administrador'
    ]);

} catch (Exception $e) {
    // Manejo de errores
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>