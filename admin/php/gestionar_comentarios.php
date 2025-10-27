<?php
// Verificar sesión de administrador
require_once '../check_session.php';

// Configurar cabeceras
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir conexión a la base de datos
require_once '../conexion.php';

// Verificar conexión
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

// Obtener la acción
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

try {
    switch ($accion) {
        case 'obtener_pendientes':
            obtenerComentariosPendientes();
            break;
        case 'aprobar':
            aprobarComentario();
            break;
        case 'rechazar':
            rechazarComentario();
            break;
        case 'estadisticas':
            obtenerEstadisticas();
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function obtenerComentariosPendientes() {
    global $conn;
    
    $sql = "SELECT c.id_comentario, c.nombre_usuario as usuario, c.calificacion, 
                   c.comentario, c.fecha_comentario as fecha, r.nombre as reloj
            FROM comentarios c
            INNER JOIN reloj r ON c.id_reloj = r.id_reloj
            WHERE c.aprobado = 0
            ORDER BY c.fecha_comentario DESC";
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        throw new Exception('Error al consultar comentarios: ' . mysqli_error($conn));
    }
    
    $comentarios = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $comentarios[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'comentarios' => $comentarios
    ], JSON_UNESCAPED_UNICODE);
}

function aprobarComentario() {
    global $conn;
    
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        throw new Exception('ID de comentario inválido');
    }
    
    // Verificar que el comentario existe y está pendiente
    $sql_verificar = "SELECT id_comentario FROM comentarios WHERE id_comentario = ? AND aprobado = 0";
    $stmt_verificar = mysqli_prepare($conn, $sql_verificar);
    mysqli_stmt_bind_param($stmt_verificar, "i", $id);
    mysqli_stmt_execute($stmt_verificar);
    $resultado = mysqli_stmt_get_result($stmt_verificar);
    
    if (mysqli_num_rows($resultado) === 0) {
        mysqli_stmt_close($stmt_verificar);
        throw new Exception('Comentario no encontrado o ya procesado');
    }
    mysqli_stmt_close($stmt_verificar);
    
    // Aprobar el comentario
    $sql_aprobar = "UPDATE comentarios SET aprobado = 1, fecha_aprobacion = NOW() WHERE id_comentario = ?";
    $stmt_aprobar = mysqli_prepare($conn, $sql_aprobar);
    mysqli_stmt_bind_param($stmt_aprobar, "i", $id);
    
    if (mysqli_stmt_execute($stmt_aprobar)) {
        mysqli_stmt_close($stmt_aprobar);
        echo json_encode([
            'success' => true,
            'message' => 'Comentario aprobado correctamente'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        mysqli_stmt_close($stmt_aprobar);
        throw new Exception('Error al aprobar comentario: ' . mysqli_stmt_error($stmt_aprobar));
    }
}

function rechazarComentario() {
    global $conn;
    
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        throw new Exception('ID de comentario inválido');
    }
    
    // Verificar que el comentario existe y está pendiente
    $sql_verificar = "SELECT id_comentario FROM comentarios WHERE id_comentario = ? AND aprobado = 0";
    $stmt_verificar = mysqli_prepare($conn, $sql_verificar);
    mysqli_stmt_bind_param($stmt_verificar, "i", $id);
    mysqli_stmt_execute($stmt_verificar);
    $resultado = mysqli_stmt_get_result($stmt_verificar);
    
    if (mysqli_num_rows($resultado) === 0) {
        mysqli_stmt_close($stmt_verificar);
        throw new Exception('Comentario no encontrado o ya procesado');
    }
    mysqli_stmt_close($stmt_verificar);
    
    // Rechazar el comentario (eliminarlo)
    $sql_rechazar = "DELETE FROM comentarios WHERE id_comentario = ?";
    $stmt_rechazar = mysqli_prepare($conn, $sql_rechazar);
    mysqli_stmt_bind_param($stmt_rechazar, "i", $id);
    
    if (mysqli_stmt_execute($stmt_rechazar)) {
        mysqli_stmt_close($stmt_rechazar);
        echo json_encode([
            'success' => true,
            'message' => 'Comentario rechazado y eliminado'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        mysqli_stmt_close($stmt_rechazar);
        throw new Exception('Error al rechazar comentario: ' . mysqli_stmt_error($stmt_rechazar));
    }
}

function obtenerEstadisticas() {
    global $conn;
    
    // Total de comentarios
    $sql_total = "SELECT COUNT(*) as total FROM comentarios";
    $result_total = mysqli_query($conn, $sql_total);
    $total = mysqli_fetch_assoc($result_total)['total'];
    
    // Comentarios pendientes
    $sql_pendientes = "SELECT COUNT(*) as pendientes FROM comentarios WHERE aprobado = 0";
    $result_pendientes = mysqli_query($conn, $sql_pendientes);
    $pendientes = mysqli_fetch_assoc($result_pendientes)['pendientes'];
    
    // Comentarios aprobados
    $sql_aprobados = "SELECT COUNT(*) as aprobados FROM comentarios WHERE aprobado = 1";
    $result_aprobados = mysqli_query($conn, $sql_aprobados);
    $aprobados = mysqli_fetch_assoc($result_aprobados)['aprobados'];
    
    // Calificación promedio
    $sql_promedio = "SELECT AVG(calificacion) as promedio FROM comentarios WHERE aprobado = 1";
    $result_promedio = mysqli_query($conn, $sql_promedio);
    $promedio = mysqli_fetch_assoc($result_promedio)['promedio'];
    
    echo json_encode([
        'success' => true,
        'estadisticas' => [
            'total' => intval($total),
            'pendientes' => intval($pendientes),
            'aprobados' => intval($aprobados),
            'promedio' => round($promedio, 1)
        ]
    ], JSON_UNESCAPED_UNICODE);
}
?>