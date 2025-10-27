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
    
    // Verificar que el reloj existe y obtener información
    $sql_check = "SELECT nombre, marca, precio, vendido, disponible FROM reloj WHERE id_reloj = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_reloj);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 0) {
        throw new Exception("El reloj no existe");
    }
    
    $reloj = $result_check->fetch_assoc();
    $stmt_check->close();
    
    // Verificar si el reloj está vendido
    if ($reloj['vendido']) {
        throw new Exception("No se puede eliminar un reloj que ya ha sido vendido");
    }
    
    // Verificar relaciones del reloj
    $sql_carrito = "SELECT COUNT(*) as count FROM carrito WHERE id_reloj = ?";
    $stmt_carrito = $conn->prepare($sql_carrito);
    $stmt_carrito->bind_param("i", $id_reloj);
    $stmt_carrito->execute();
    $result_carrito = $stmt_carrito->get_result();
    $carrito_count = $result_carrito->fetch_assoc()['count'];
    $stmt_carrito->close();
    
    $sql_orders = "SELECT COUNT(*) as count FROM orden_detalle WHERE id_reloj = ?";
    $stmt_orders = $conn->prepare($sql_orders);
    $stmt_orders->bind_param("i", $id_reloj);
    $stmt_orders->execute();
    $result_orders = $stmt_orders->get_result();
    $orders_count = $result_orders->fetch_assoc()['count'];
    $stmt_orders->close();
    
    // Información detallada
    $informacion = [
        'reloj' => $reloj,
        'carrito_count' => $carrito_count,
        'orders_count' => $orders_count,
        'puede_eliminar' => $orders_count == 0,
        'accion_recomendada' => $orders_count > 0 ? 'marcar_vendido' : 'eliminar_completo'
    ];
    
    echo json_encode([
        'success' => true,
        'informacion' => $informacion,
        'message' => $orders_count > 0 
            ? "El reloj tiene $orders_count órdenes asociadas. Se marcará como vendido."
            : "El reloj se puede eliminar completamente."
    ]);
    
} catch (Exception $e) {
    error_log("Error en verificar_reloj_eliminacion.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

