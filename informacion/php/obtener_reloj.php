<?php
// Configurar cabeceras CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

include 'conexion.php';

if (!isset($_GET['id_reloj'])) {
    echo json_encode(['error' => 'ID no especificado']);
    exit;
}

$id = intval($_GET['id_reloj']); // Seguridad básica

// Verificar conexión
if (!$conn) {
    echo json_encode(['error' => 'No se pudo conectar a la base de datos']);
    exit;
}

// Usar prepared statement para mayor seguridad - incluir relojes vendidos
$sql = "SELECT * FROM reloj WHERE id_reloj = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) === 0) {
    echo json_encode(['error' => 'Reloj no encontrado']);
    exit;
}

$reloj = mysqli_fetch_assoc($resultado);

// Cerrar statement
mysqli_stmt_close($stmt);

// Limpiar y corregir datos problemáticos
foreach ($reloj as $campo => $valor) {
    if ($valor === null) {
        $reloj[$campo] = ''; // Convertir NULL a string vacío
    } else {
        // Limpiar caracteres UTF-8 malformados
        $reloj[$campo] = mb_convert_encoding($valor, 'UTF-8', 'UTF-8');
        
        // Si aún hay problemas, usar una limpieza más agresiva
        if (!mb_check_encoding($reloj[$campo], 'UTF-8')) {
            $reloj[$campo] = mb_convert_encoding($valor, 'UTF-8', 'auto');
        }
        
        // Si sigue fallando, limpiar caracteres problemáticos
        if (!mb_check_encoding($reloj[$campo], 'UTF-8')) {
            $reloj[$campo] = filter_var($valor, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        }
    }
}

// Intentar hacer json_encode con opciones adicionales
$json_result = json_encode($reloj, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);

if ($json_result === false) {
    // Si falla, intentar con una limpieza más agresiva
    $reloj_limpio = array_map(function($valor) {
        if (is_string($valor)) {
            // Remover caracteres no válidos
            return preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $valor);
        }
        return $valor;
    }, $reloj);
    
    $json_result = json_encode($reloj_limpio, JSON_UNESCAPED_UNICODE);
    
    if ($json_result === false) {
        echo json_encode(['error' => 'Error al generar JSON: ' . json_last_error_msg()]);
        exit;
    }
}

// Devuelve los datos en formato JSON
echo $json_result;
?>