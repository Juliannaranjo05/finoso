<?php
/**
 * Obtener órdenes recientes para el panel de pruebas
 */

header('Content-Type: application/json');

// Para pruebas, comentar la verificación de sesión
// include '../check_session.php';

include __DIR__ . '/../conexion.php';

try {
    // Obtener las últimas 10 órdenes con información del cliente y reloj
    // 🔧 CORREGIDO: GROUP BY para evitar duplicados cuando hay múltiples productos
    $sql = "SELECT 
                o.id_orden,
                o.total,
                o.estado,
                o.nombre as nombre_cliente,
                o.celular,
                GROUP_CONCAT(r.nombre SEPARATOR ', ') as nombre_reloj,
                COUNT(od.id_reloj) as cantidad_productos
            FROM orden o
            LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
            LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
            GROUP BY o.id_orden
            ORDER BY o.id_orden DESC
            LIMIT 10";
    
    $result = $conn->query($sql);
    $ordenes = [];
    
    while ($row = $result->fetch_assoc()) {
        $ordenes[] = [
            'id_orden' => $row['id_orden'],
            'nombre_cliente' => $row['nombre_cliente'],
            'telefono' => $row['celular'],
            'total' => $row['total'],
            'estado' => $row['estado'],
            'nombre_reloj' => $row['nombre_reloj'],  // Ahora contiene todos los productos separados por coma
            'cantidad_productos' => $row['cantidad_productos']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'ordenes' => $ordenes
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>


}
?>

