<?php
// Archivo de prueba para simular aprobación de comentario
require_once 'conexion.php';

// Simular datos POST
$_POST['accion'] = 'aprobar';
$_POST['id'] = '6'; // ID del primer comentario

echo "<h2>🧪 Prueba de Aprobación de Comentario</h2>";
echo "<p><strong>ID a aprobar:</strong> " . $_POST['id'] . "</p>";

// Verificar conexión
if (!$conn) {
    echo "<p style='color: red;'>❌ Error de conexión a la base de datos</p>";
    exit;
}

echo "<p style='color: green;'>✅ Conexión exitosa</p>";

$id = intval($_POST['id'] ?? 0);
echo "<p><strong>ID procesado:</strong> " . $id . "</p>";

if ($id <= 0) {
    echo "<p style='color: red;'>❌ ID inválido</p>";
    exit;
}

// Verificar que el comentario existe y está pendiente
$sql_verificar = "SELECT id_comentario, nombre_usuario, comentario FROM comentarios WHERE id_comentario = ? AND aprobado = 0";
$stmt_verificar = mysqli_prepare($conn, $sql_verificar);
mysqli_stmt_bind_param($stmt_verificar, "i", $id);
mysqli_stmt_execute($stmt_verificar);
$resultado = mysqli_stmt_get_result($stmt_verificar);

if (mysqli_num_rows($resultado) === 0) {
    echo "<p style='color: red;'>❌ Comentario no encontrado o ya procesado</p>";
    mysqli_stmt_close($stmt_verificar);
    exit;
}

$comentario = mysqli_fetch_assoc($resultado);
echo "<p style='color: green;'>✅ Comentario encontrado:</p>";
echo "<ul>";
echo "<li><strong>ID:</strong> " . $comentario['id_comentario'] . "</li>";
echo "<li><strong>Usuario:</strong> " . $comentario['nombre_usuario'] . "</li>";
echo "<li><strong>Comentario:</strong> " . substr($comentario['comentario'], 0, 50) . "...</li>";
echo "</ul>";

mysqli_stmt_close($stmt_verificar);

// Intentar aprobar el comentario
$sql_aprobar = "UPDATE comentarios SET aprobado = 1, fecha_aprobacion = NOW() WHERE id_comentario = ?";
$stmt_aprobar = mysqli_prepare($conn, $sql_aprobar);
mysqli_stmt_bind_param($stmt_aprobar, "i", $id);

if (mysqli_stmt_execute($stmt_aprobar)) {
    echo "<p style='color: green;'>✅ Comentario aprobado correctamente</p>";
    
    // Verificar el cambio
    $sql_verificar_aprobado = "SELECT aprobado, fecha_aprobacion FROM comentarios WHERE id_comentario = ?";
    $stmt_verificar_aprobado = mysqli_prepare($conn, $sql_verificar_aprobado);
    mysqli_stmt_bind_param($stmt_verificar_aprobado, "i", $id);
    mysqli_stmt_execute($stmt_verificar_aprobado);
    $resultado_aprobado = mysqli_stmt_get_result($stmt_verificar_aprobado);
    $comentario_aprobado = mysqli_fetch_assoc($resultado_aprobado);
    
    echo "<p><strong>Estado después de aprobar:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Aprobado:</strong> " . ($comentario_aprobado['aprobado'] ? 'Sí' : 'No') . "</li>";
    echo "<li><strong>Fecha de aprobación:</strong> " . $comentario_aprobado['fecha_aprobacion'] . "</li>";
    echo "</ul>";
    
    mysqli_stmt_close($stmt_verificar_aprobado);
} else {
    echo "<p style='color: red;'>❌ Error al aprobar comentario: " . mysqli_stmt_error($stmt_aprobar) . "</p>";
}

mysqli_stmt_close($stmt_aprobar);
mysqli_close($conn);
?>

