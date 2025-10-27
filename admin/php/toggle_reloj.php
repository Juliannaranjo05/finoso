<?php
session_start();
header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permisos para realizar esta acción'
    ]);
    exit();
}

require_once __DIR__ . '/../../php/conexion.php';

try {
    // Obtener datos del JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id_reloj']) || empty($input['id_reloj'])) {
        throw new Exception("ID del reloj es obligatorio");
    }
    
    $id_reloj = intval($input['id_reloj']);
    
    // Obtener el estado actual del reloj
    $sql_check = "SELECT disponible, vendido FROM reloj WHERE id_reloj = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_reloj);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 0) {
        throw new Exception("El reloj no existe");
    }
    
    $reloj = $result_check->fetch_assoc();
    $stmt_check->close();
    
    // Cambiar disponibilidad (si está vendido, no se puede cambiar)
    if ($reloj['vendido']) {
        throw new Exception("No se puede cambiar la disponibilidad de un reloj vendido");
    }
    
    $nueva_disponibilidad = $reloj['disponible'] ? 0 : 1;
    
    // Actualizar disponibilidad
    $sql = "UPDATE reloj SET disponible = ? WHERE id_reloj = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $nueva_disponibilidad, $id_reloj);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar disponibilidad: " . $stmt->error);
    }
    
    $stmt->close();
    
    $estado = $nueva_disponibilidad ? 'disponible' : 'no disponible';
    
    echo json_encode([
        'success' => true,
        'message' => "Reloj marcado como $estado",
        'nueva_disponibilidad' => $nueva_disponibilidad
    ]);
    
} catch (Exception $e) {
    error_log("Error en toggle_reloj.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>


