<?php
/**
 * Obtener relojes destacados
 * Combina criterios de precio alto y popularidad
 */

// Configurar cabeceras
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir conexión
require_once '../../php/conexion.php';

// Verificar conexión
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    // Query para obtener relojes destacados
    // Combina precio alto, comentarios y calificaciones
    $sql = "
        SELECT 
            r.id_reloj,
            r.nombre,
            r.precio,
            r.img as imagen_principal,
            r.vendido,
            COUNT(c.id_comentario) as total_comentarios,
            COALESCE(AVG(c.calificacion), 0) as calificacion_promedio,
            -- Score combinado: precio (30%) + comentarios (40%) + calificación (30%)
            (r.precio * 0.3) + (COUNT(c.id_comentario) * 10000) + (COALESCE(AVG(c.calificacion), 0) * 50000) as score_destacado
        FROM reloj r
        LEFT JOIN comentarios c ON r.id_reloj = c.id_reloj AND c.aprobado = 1
        WHERE r.disponible = 1
        GROUP BY r.id_reloj, r.nombre, r.precio, r.img, r.vendido
        HAVING score_destacado > 0
        ORDER BY score_destacado DESC
        LIMIT 6
    ";
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        throw new Exception('Error al consultar destacados: ' . mysqli_error($conn));
    }
    
    $destacados = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Asegurar que los valores numéricos sean correctos
        $row['id_reloj'] = intval($row['id_reloj']);
        $row['precio'] = intval($row['precio']);
        $row['vendido'] = intval($row['vendido']);
        $row['total_comentarios'] = intval($row['total_comentarios']);
        $row['calificacion_promedio'] = floatval($row['calificacion_promedio']);
        $row['score_destacado'] = floatval($row['score_destacado']);
        
        $destacados[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'destacados' => $destacados,
        'total' => count($destacados)
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
