<?php
/**
 * Obtener relojes vendidos más caros
 */

// Configurar cabeceras
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir conexión
require_once '../php/conexion.php';

// Verificar conexión
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    // Query para obtener relojes vendidos más caros
    $sql = "
        SELECT 
            r.id_reloj,
            r.nombre,
            r.precio,
            r.img as imagen_principal,
            r.vendido,
            COUNT(c.id_comentario) as total_comentarios,
            COALESCE(AVG(c.calificacion), 0) as calificacion_promedio
        FROM reloj r
        LEFT JOIN comentarios c ON r.id_reloj = c.id_reloj AND c.aprobado = 1
        WHERE r.vendido = 1 AND r.disponible = 1
        GROUP BY r.id_reloj, r.nombre, r.precio, r.img, r.vendido
        ORDER BY r.precio DESC
        LIMIT 6
    ";
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        throw new Exception('Error al consultar vendidos: ' . mysqli_error($conn));
    }
    
    $vendidos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Asegurar que los valores numéricos sean correctos
        $row['id_reloj'] = intval($row['id_reloj']);
        $row['precio'] = intval($row['precio']);
        $row['vendido'] = intval($row['vendido']);
        $row['total_comentarios'] = intval($row['total_comentarios']);
        $row['calificacion_promedio'] = floatval($row['calificacion_promedio']);
        
        $vendidos[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'vendidos' => $vendidos,
        'total' => count($vendidos)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} finally {
    mysqli_close($conn);
}
?>
