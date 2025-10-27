<?php
require_once __DIR__ . '/../../php/conexion.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT id_marca, nombre FROM marca WHERE activa = 1 ORDER BY nombre";
    $result = mysqli_query($conn, $sql);
    
    $marcas = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $marcas[] = [
            'id_marca' => $row['id_marca'],
            'nombre' => $row['nombre']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'marcas' => $marcas
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener marcas: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>


