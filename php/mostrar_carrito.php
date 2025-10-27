<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require 'conexion.php';

// Verificar si el usuario tiene sesión activa
if (!isset($_SESSION['id_usuario']) && !isset($_SESSION['nombre'])) {
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesión']);
    exit;
}

// Obtener el ID del usuario
$id_usuario = null;

if (isset($_SESSION['id_usuario'])) {
    // Si ya tenemos el ID en la sesión, usarlo directamente
    $id_usuario = $_SESSION['id_usuario'];
} else if (isset($_SESSION['nombre'])) {
    // Si solo tenemos el nombre, buscar el ID
    $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE nombre = ?");
    $stmt->bind_param("s", $_SESSION['nombre']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }

    $usuario = $result->fetch_assoc();
    $id_usuario = $usuario['id_usuario'];
} else {
    echo json_encode(['success' => false, 'message' => 'Sesión inválida']);
    exit;
}

// Ahora obtener los relojes del carrito con descripción y especificaciones técnicas
$sql = "SELECT r.id_reloj, r.nombre, r.precio, r.descuento, r.img, r.descripcion, 
               r.eslabones, r.tipo_bisel, r.movimiento, r.pulsera, r.peso, r.resistencia_agua,
               r.img_lateral, r.img_detalle
        FROM carrito c
        INNER JOIN reloj r ON c.id_reloj = r.id_reloj
        WHERE c.id_usuario = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$relojes = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $precioOriginal = (float) $row['precio'];
    $descuento = (float) $row['descuento'];

    error_log("🔍 DEBUG mostrar_carrito.php - Producto: " . $row['nombre']);
    error_log("🔍 DEBUG mostrar_carrito.php - Precio BD (raw): " . $row['precio']);
    error_log("🔍 DEBUG mostrar_carrito.php - Precio BD (float): " . $precioOriginal);
    error_log("🔍 DEBUG mostrar_carrito.php - Descuento: " . $descuento);

    // Calcular precio con descuento si aplica
    if ($descuento > 0) {
        $precio_final = $precioOriginal * (1 - $descuento);
        error_log("🔍 DEBUG mostrar_carrito.php - Precio con descuento: " . $precio_final);
    } else {
        $precio_final = $precioOriginal;
        error_log("🔍 DEBUG mostrar_carrito.php - Precio sin descuento: " . $precio_final);
    }

    // NO dividir por 1000 aquí, el JavaScript se encarga del formateo
    error_log("🔍 DEBUG mostrar_carrito.php - Precio final (sin dividir): " . $precio_final);

    // Sumar al total numérico
    $total += $precio_final;
    error_log("🔍 DEBUG mostrar_carrito.php - Total acumulado: " . $total);

    $relojes[] = [
        'id_reloj' => $row['id_reloj'],
        'nombre' => $row['nombre'],
        'precio' => $precioOriginal, // Precio original sin dividir
        'descuento' => $descuento,
        'img' => $row['img'],
        'precio_final' => $precio_final, // Precio final sin dividir
        'descripcion' => isset($row['descripcion']) ? $row['descripcion'] : '',
        'eslabones' => isset($row['eslabones']) ? $row['eslabones'] : '',
        'tipo_bisel' => isset($row['tipo_bisel']) ? $row['tipo_bisel'] : '',
        'movimiento' => isset($row['movimiento']) ? $row['movimiento'] : '',
        'pulsera' => isset($row['pulsera']) ? $row['pulsera'] : '',
        'peso' => isset($row['peso']) ? $row['peso'] : '',
        'resistencia_agua' => isset($row['resistencia_agua']) ? $row['resistencia_agua'] : '',
        'img_lateral' => isset($row['img_lateral']) ? $row['img_lateral'] : '',
        'img_detalle' => isset($row['img_detalle']) ? $row['img_detalle'] : ''
    ];
}

error_log("🔍 DEBUG mostrar_carrito.php - Total final: " . $total);

// Retorna el total sin formato para usarlo como número en JS
$response = [
    'success' => true,
    'relojes' => $relojes,
    'total' => $total
];

// Función para limpiar caracteres UTF-8
function limpiarUTF8($string) {
    // Convertir de ISO-8859-1 a UTF-8 si es necesario
    if (!mb_check_encoding($string, 'UTF-8')) {
        $string = mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
    }
    
    // Limpiar caracteres de control problemáticos
    $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
    
    return $string;
}

// Limpiar todos los strings en el array de relojes
foreach ($relojes as &$reloj) {
    $reloj['nombre'] = limpiarUTF8($reloj['nombre']);
    $reloj['descripcion'] = limpiarUTF8($reloj['descripcion']);
    $reloj['eslabones'] = limpiarUTF8($reloj['eslabones']);
    $reloj['tipo_bisel'] = limpiarUTF8($reloj['tipo_bisel']);
    $reloj['movimiento'] = limpiarUTF8($reloj['movimiento']);
    $reloj['pulsera'] = limpiarUTF8($reloj['pulsera']);
    $reloj['peso'] = limpiarUTF8($reloj['peso']);
    $reloj['resistencia_agua'] = limpiarUTF8($reloj['resistencia_agua']);
    $reloj['img'] = limpiarUTF8($reloj['img']);
    $reloj['img_lateral'] = limpiarUTF8($reloj['img_lateral']);
    $reloj['img_detalle'] = limpiarUTF8($reloj['img_detalle']);
}

// Verificar que el JSON se puede codificar correctamente
$json_output = json_encode($response, JSON_UNESCAPED_UNICODE);

if ($json_output === false) {
    // Si hay error en JSON, devolver error
    error_log("🔍 DEBUG mostrar_carrito.php - Error JSON: " . json_last_error_msg());
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar datos',
        'error' => json_last_error_msg()
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo $json_output;
}

mysqli_close($conn);
?>