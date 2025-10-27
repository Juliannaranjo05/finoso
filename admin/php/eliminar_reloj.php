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
    $sql_check = "SELECT nombre, img, vendido FROM reloj WHERE id_reloj = ?";
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
    
    // Estrategia de eliminación inteligente
    if ($orders_count > 0) {
        // Si hay órdenes, solo marcar como vendido y limpiar carritos
        $sql_marcar_vendido = "UPDATE reloj SET vendido = 1 WHERE id_reloj = ?";
        $stmt_marcar = $conn->prepare($sql_marcar_vendido);
        $stmt_marcar->bind_param("i", $id_reloj);
        
        if (!$stmt_marcar->execute()) {
            throw new Exception("Error al marcar reloj como vendido: " . $stmt_marcar->error);
        }
        $stmt_marcar->close();
        
        // Eliminar del carrito si hay registros
        if ($carrito_count > 0) {
            $sql_limpiar_carrito = "DELETE FROM carrito WHERE id_reloj = ?";
            $stmt_limpiar = $conn->prepare($sql_limpiar_carrito);
            $stmt_limpiar->bind_param("i", $id_reloj);
            $stmt_limpiar->execute();
            $stmt_limpiar->close();
        }
        
        // Intentar eliminar la imagen si existe (opcional)
        if (!empty($reloj['img']) && file_exists(__DIR__ . '/../../' . $reloj['img'])) {
            try {
                unlink(__DIR__ . '/../../' . $reloj['img']);
                error_log("Imagen eliminada: " . $reloj['img']);
            } catch (Exception $e) {
                error_log("No se pudo eliminar la imagen: " . $e->getMessage());
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Reloj marcado como vendido (tenía $orders_count órdenes y $carrito_count elementos en carrito)",
            'reloj_eliminado' => $reloj['nombre'],
            'accion' => 'marcado_vendido'
        ]);
        
    } else {
        // Si no hay órdenes, eliminar completamente
        // Primero eliminar del carrito
        if ($carrito_count > 0) {
            $sql_limpiar_carrito = "DELETE FROM carrito WHERE id_reloj = ?";
            $stmt_limpiar = $conn->prepare($sql_limpiar_carrito);
            $stmt_limpiar->bind_param("i", $id_reloj);
            $stmt_limpiar->execute();
            $stmt_limpiar->close();
        }
        
        // Luego eliminar el reloj
        $sql = "DELETE FROM reloj WHERE id_reloj = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_reloj);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al eliminar reloj: " . $stmt->error);
        }
        $stmt->close();
        
        // Intentar eliminar la imagen si existe (opcional)
        if (!empty($reloj['img']) && file_exists(__DIR__ . '/../../' . $reloj['img'])) {
            try {
                unlink(__DIR__ . '/../../' . $reloj['img']);
                error_log("Imagen eliminada: " . $reloj['img']);
            } catch (Exception $e) {
                error_log("No se pudo eliminar la imagen: " . $e->getMessage());
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Reloj eliminado completamente (tenía $carrito_count elementos en carrito)",
            'reloj_eliminado' => $reloj['nombre'],
            'accion' => 'eliminado_completo'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en eliminar_reloj.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

