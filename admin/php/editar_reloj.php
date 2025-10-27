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
    // Debug: Log de datos recibidos
    error_log("=== DEBUG EDITAR RELOJ ===");
    error_log("POST data: " . print_r($_POST, true));
    
    // Validar ID del reloj
    if (!isset($_POST['id_reloj']) || empty($_POST['id_reloj'])) {
        throw new Exception("ID del reloj es obligatorio");
    }
    
    $id_reloj = intval($_POST['id_reloj']);
    
    // Validar que se recibieron todos los campos obligatorios
    $campos_obligatorios = ['marca', 'nombre', 'descripcion', 'precio'];
    foreach ($campos_obligatorios as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            throw new Exception("El campo $campo es obligatorio");
        }
    }
    
    // Sanitizar y obtener datos
    $marca = $conn->real_escape_string(trim($_POST['marca']));
    $nombre = $conn->real_escape_string(trim($_POST['nombre']));
    $descripcion = $conn->real_escape_string(trim($_POST['descripcion']));
    $eslabones = isset($_POST['eslabones']) ? $conn->real_escape_string(trim($_POST['eslabones'])) : NULL;
    $tipo_bisel = isset($_POST['tipo_bisel']) ? $conn->real_escape_string(trim($_POST['tipo_bisel'])) : NULL;
    $movimiento = isset($_POST['movimiento']) ? $conn->real_escape_string(trim($_POST['movimiento'])) : NULL;
    $pulsera = isset($_POST['pulsera']) ? $conn->real_escape_string(trim($_POST['pulsera'])) : NULL;
    $peso = isset($_POST['peso']) ? $conn->real_escape_string(trim($_POST['peso'])) : NULL;
    $resistencia_agua = isset($_POST['resistencia_agua']) ? $conn->real_escape_string(trim($_POST['resistencia_agua'])) : NULL;
    $precio = floatval($_POST['precio']);
    $descuento = isset($_POST['descuento']) && !empty(trim($_POST['descuento'])) ? floatval($_POST['descuento']) : NULL;
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    $vendido = isset($_POST['vendido']) ? 1 : 0;
    
    // Verificar que el reloj existe
    $sql_check = "SELECT id_reloj FROM reloj WHERE id_reloj = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_reloj);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 0) {
        throw new Exception("El reloj no existe");
    }
    $stmt_check->close();
    
    // Actualizar en la base de datos
    $sql = "UPDATE reloj SET marca = ?, nombre = ?, descripcion = ?, eslabones = ?, tipo_bisel = ?, movimiento = ?, pulsera = ?, peso = ?, resistencia_agua = ?, precio = ?, descuento = ?, disponible = ?, vendido = ? WHERE id_reloj = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("sssssssssdiiii", $marca, $nombre, $descripcion, $eslabones, $tipo_bisel, $movimiento, $pulsera, $peso, $resistencia_agua, $precio, $descuento, $disponible, $vendido, $id_reloj);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar reloj en la base de datos: " . $stmt->error);
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Reloj actualizado exitosamente'
    ]);
    
} catch (Exception $e) {
    error_log("Error en editar_reloj.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
