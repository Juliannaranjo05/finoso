<?php
// Configurar cabeceras CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

include 'conexion.php';

if (!isset($_GET['id_reloj'])) {
    echo json_encode(['error' => 'ID del reloj no especificado']);
    exit;
}

$id_reloj = intval($_GET['id_reloj']);

// Verificar conexión
if (!$conn) {
    echo json_encode(['error' => 'No se pudo conectar a la base de datos']);
    exit;
}

try {
    // Obtener comentarios aprobados del reloj
    $sql = "SELECT 
                c.id_comentario,
                c.nombre_usuario,
                c.calificacion,
                c.comentario,
                c.fecha_comentario,
                u.nombre as nombre_completo
            FROM comentarios c
            LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
            WHERE c.id_reloj = ? AND c.aprobado = 1
            ORDER BY c.fecha_comentario DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        throw new Exception('Error en la consulta: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id_reloj);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    $comentarios = [];
    $calificacion_promedio = 0;
    $total_comentarios = 0;
    $suma_calificaciones = 0;
    
    while ($row = mysqli_fetch_assoc($resultado)) {
        // Usar el nombre completo del usuario si está logueado, sino el nombre del comentario
        $nombre_mostrar = !empty($row['nombre_completo']) ? $row['nombre_completo'] : $row['nombre_usuario'];
        
        $comentario = [
            'id' => $row['id_comentario'],
            'nombre' => $nombre_mostrar,
            'calificacion' => intval($row['calificacion']),
            'comentario' => $row['comentario'],
            'fecha' => $row['fecha_comentario']
        ];
        
        $comentarios[] = $comentario;
        $suma_calificaciones += intval($row['calificacion']);
        $total_comentarios++;
    }
    
    // Calcular promedio de calificaciones
    if ($total_comentarios > 0) {
        $calificacion_promedio = round($suma_calificaciones / $total_comentarios, 1);
    }
    
    mysqli_stmt_close($stmt);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'comentarios' => $comentarios,
        'estadisticas' => [
            'total_comentarios' => $total_comentarios,
            'calificacion_promedio' => $calificacion_promedio,
            'suma_calificaciones' => $suma_calificaciones
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>

