<?php
// validar_codigo_descuento.php
include 'conexion.php'; // tu archivo de conexión

$codigo = $_POST['codigo'] ?? '';

$response = [
    'valido' => false,
    'mensaje' => 'Código no válido.',
];

if (!empty($codigo)) {
    $stmt = $conn->prepare("SELECT porcentaje FROM codigo_descuento WHERE codigo = ? AND fecha_expiracion >= CURDATE()");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $stmt->bind_result($porcentaje);
    
    if ($stmt->fetch()) {
        $response['valido'] = true;
        $response['porcentaje'] = $porcentaje;
        $response['mensaje'] = 'Descuento aplicado correctamente.';
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($response);
?>