<?php
/**
 * VALIDAR DISPONIBILIDAD DE RELOJES EN CARRITO
 * Verifica si algún reloj del carrito ya está vendido antes de finalizar compra
 */

session_start();
header('Content-Type: application/json');
require_once '../admin/conexion.php';

$response = [
    'success' => true,
    'relojes_vendidos' => [],
    'mensaje' => ''
];

$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    $response['success'] = false;
    $response['mensaje'] = 'No hay sesión activa.';
    echo json_encode($response);
    exit;
}

try {
    // Obtener todos los relojes del carrito del usuario
    $stmt = $conn->prepare("
        SELECT c.id_carrito, c.id_reloj, r.nombre, r.vendido, r.disponible
        FROM carrito c
        INNER JOIN reloj r ON c.id_reloj = r.id_reloj
        WHERE c.id_usuario = ?
    ");
    
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $relojes_vendidos = [];
    $ids_eliminar = [];
    
    while ($row = $result->fetch_assoc()) {
        // Verificar si el reloj está vendido o no disponible
        if ($row['vendido'] == 1 || $row['disponible'] == 0) {
            $relojes_vendidos[] = [
                'id_reloj' => $row['id_reloj'],
                'nombre' => $row['nombre'],
                'vendido' => $row['vendido'],
                'disponible' => $row['disponible']
            ];
            $ids_eliminar[] = $row['id_carrito'];
        }
    }
    $stmt->close();
    
    // Si hay relojes vendidos, eliminarlos del carrito automáticamente
    if (!empty($relojes_vendidos)) {
        foreach ($ids_eliminar as $id_carrito) {
            $stmt_delete = $conn->prepare("DELETE FROM carrito WHERE id_carrito = ?");
            $stmt_delete->bind_param("i", $id_carrito);
            $stmt_delete->execute();
            $stmt_delete->close();
        }
        
        $response['success'] = false;
        $response['relojes_vendidos'] = $relojes_vendidos;
        $response['mensaje'] = 'Algunos relojes de tu carrito ya no están disponibles y han sido eliminados.';
        
        error_log('[VALIDAR-CARRITO] Relojes vendidos eliminados: ' . count($relojes_vendidos) . ' para usuario ' . $id_usuario);
    } else {
        $response['mensaje'] = 'Todos los relojes están disponibles.';
    }
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['mensaje'] = 'Error al validar disponibilidad.';
    error_log('[VALIDAR-CARRITO] Error: ' . $e->getMessage());
}

echo json_encode($response);
?>

