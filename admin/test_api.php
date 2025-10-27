<?php
// Archivo de prueba para la API de comentarios
require_once 'conexion.php';

// Configurar cabeceras
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar conexión
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

// Obtener comentarios pendientes
$sql = "SELECT c.id_comentario, c.nombre_usuario as usuario, c.calificacion, 
               c.comentario, c.fecha_comentario as fecha, r.nombre as reloj
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
    $comentarios[] = $row;
}

echo json_encode([
    'success' => true,
    'comentarios' => $comentarios
], JSON_UNESCAPED_UNICODE);

mysqli_close($conn);
?>

