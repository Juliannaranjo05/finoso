<?php
/**
 * ACTUALIZAR PERFIL DE USUARIO - FINOSO
 * Backend para actualizar información básica del perfil
 */

session_start();
header('Content-Type: application/json');

try {
    // Verificar que el usuario esté autenticado
    if (!isset($_SESSION['id_usuario'])) {
        echo json_encode([
            'success' => false,
            'message' => 'No hay sesión activa'
        ]);
        exit;
    }

    // Incluir conexión a la base de datos
    require_once '../../admin/conexion.php';

    // Obtener datos del formulario
    $id_usuario = $_SESSION['id_usuario'];
    $nombre = trim($_POST['nombre'] ?? '');

    // Validaciones
    if (empty($nombre) || strlen($nombre) < 3) {
        echo json_encode([
            'success' => false,
            'message' => 'El nombre debe tener al menos 3 caracteres'
        ]);
        exit;
    }

    // Actualizar en la base de datos
    $stmt = $conn->prepare("UPDATE usuario SET nombre = ? WHERE id_usuario = ?");
    $stmt->bind_param("si", $nombre, $id_usuario);
    
    if ($stmt->execute()) {
        // Actualizar variables de sesión
        $_SESSION['nombre'] = $nombre;
        
        echo json_encode([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'data' => [
                'nombre' => $nombre
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar la base de datos: ' . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log("Error al actualizar perfil: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
?>

