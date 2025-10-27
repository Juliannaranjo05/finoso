<?php
// Configurar cabeceras CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

include 'conexion.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Obtener datos del POST
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $input = $_POST; // Fallback para form-data
}

// Validar datos requeridos
$id_reloj = isset($input['id_reloj']) ? intval($input['id_reloj']) : 0;
$nombre_usuario = isset($input['nombre_usuario']) ? trim($input['nombre_usuario']) : '';
$calificacion = isset($input['calificacion']) ? intval($input['calificacion']) : 0;
$comentario = isset($input['comentario']) ? trim($input['comentario']) : '';
$id_usuario = isset($input['id_usuario']) ? intval($input['id_usuario']) : null;

// Validaciones
if ($id_reloj <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID del reloj inválido']);
    exit;
}

if (empty($nombre_usuario) || strlen($nombre_usuario) < 2) {
    echo json_encode(['success' => false, 'error' => 'El nombre debe tener al menos 2 caracteres']);
    exit;
}

if ($calificacion < 1 || $calificacion > 5) {
    echo json_encode(['success' => false, 'error' => 'La calificación debe estar entre 1 y 5 estrellas']);
    exit;
}

if (empty($comentario) || strlen($comentario) < 10) {
    echo json_encode(['success' => false, 'error' => 'El comentario debe tener al menos 10 caracteres']);
    exit;
}

if (strlen($comentario) > 500) {
    echo json_encode(['success' => false, 'error' => 'El comentario no puede exceder 500 caracteres']);
    exit;
}

// Verificar conexión
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'No se pudo conectar a la base de datos']);
    exit;
}

try {
    // Verificar que el reloj existe
    $sql_verificar = "SELECT id_reloj FROM reloj WHERE id_reloj = ?";
    $stmt_verificar = mysqli_prepare($conn, $sql_verificar);
    mysqli_stmt_bind_param($stmt_verificar, "i", $id_reloj);
    mysqli_stmt_execute($stmt_verificar);
    $resultado_verificar = mysqli_stmt_get_result($stmt_verificar);
    
    if (mysqli_num_rows($resultado_verificar) === 0) {
        mysqli_stmt_close($stmt_verificar);
        echo json_encode(['success' => false, 'error' => 'El reloj especificado no existe']);
        exit;
    }
    mysqli_stmt_close($stmt_verificar);
    
    // Si el usuario está logueado, verificar que existe
    if ($id_usuario !== null) {
        $sql_usuario = "SELECT id_usuario FROM usuario WHERE id_usuario = ?";
        $stmt_usuario = mysqli_prepare($conn, $sql_usuario);
        mysqli_stmt_bind_param($stmt_usuario, "i", $id_usuario);
        mysqli_stmt_execute($stmt_usuario);
        $resultado_usuario = mysqli_stmt_get_result($stmt_usuario);
        
        if (mysqli_num_rows($resultado_usuario) === 0) {
            mysqli_stmt_close($stmt_usuario);
            echo json_encode(['success' => false, 'error' => 'Usuario no válido']);
            exit;
        }
        mysqli_stmt_close($stmt_usuario);
    }
    
    // Insertar el comentario
    $sql_insertar = "INSERT INTO comentarios (id_reloj, id_usuario, nombre_usuario, calificacion, comentario, aprobado) 
                     VALUES (?, ?, ?, ?, ?, 0)";
    
    $stmt_insertar = mysqli_prepare($conn, $sql_insertar);
    
    if (!$stmt_insertar) {
        throw new Exception('Error en la consulta: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt_insertar, "iisis", $id_reloj, $id_usuario, $nombre_usuario, $calificacion, $comentario);
    
    if (mysqli_stmt_execute($stmt_insertar)) {
        $id_comentario = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_insertar);
        
        echo json_encode([
            'success' => true,
            'message' => 'Comentario enviado correctamente. Será revisado antes de ser publicado.',
            'id_comentario' => $id_comentario
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Error al insertar el comentario: ' . mysqli_stmt_error($stmt_insertar));
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>

