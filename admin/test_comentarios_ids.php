<?php
// Archivo de prueba para verificar IDs de comentarios
require_once 'conexion.php';

// Configurar cabeceras
header('Content-Type: application/json; charset=utf-8');

// Verificar conexión
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

// Obtener comentarios pendientes con información detallada
$sql = "SELECT c.id_comentario, c.nombre_usuario as usuario, c.calificacion, 
               c.comentario, c.fecha_comentario as fecha, c.aprobado, r.nombre as reloj
        FROM comentarios c
        INNER JOIN reloj r ON c.id_reloj = r.id_reloj
        WHERE c.aprobado = 0
        ORDER BY c.fecha_comentario DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(['success' => false, 'error' => 'Error al consultar comentarios: ' . mysqli_error($conn)]);
    exit;
}

$comentarios = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Asegurar que el ID sea un entero
    $row['id_comentario'] = intval($row['id_comentario']);
    $comentarios[] = $row;
}

echo json_encode([
    'success' => true,
    'comentarios' => $comentarios,
    'debug' => [
        'total_encontrados' => count($comentarios),
        'ids' => array_column($comentarios, 'id_comentario')
    ]
], JSON_UNESCAPED_UNICODE);

mysqli_close($conn);
?>
