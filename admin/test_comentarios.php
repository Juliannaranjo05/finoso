<?php
// Archivo de prueba para verificar comentarios
require_once 'conexion.php';

echo "<h2>🔍 Verificación de Comentarios</h2>";

// Verificar conexión
if (!$conn) {
    echo "<p style='color: red;'>❌ Error de conexión a la base de datos</p>";
    exit;
}

echo "<p style='color: green;'>✅ Conexión exitosa</p>";

// Verificar si existe la tabla comentarios
$sql_tabla = "SHOW TABLES LIKE 'comentarios'";
$result_tabla = mysqli_query($conn, $sql_tabla);

if (mysqli_num_rows($result_tabla) == 0) {
    echo "<p style='color: red;'>❌ La tabla 'comentarios' no existe</p>";
    exit;
}

echo "<p style='color: green;'>✅ La tabla 'comentarios' existe</p>";

// Verificar estructura de la tabla
$sql_estructura = "DESCRIBE comentarios";
$result_estructura = mysqli_query($conn, $sql_estructura);

echo "<h3>📋 Estructura de la tabla comentarios:</h3>";
echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";

while ($row = mysqli_fetch_assoc($result_estructura)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Contar comentarios totales
$sql_total = "SELECT COUNT(*) as total FROM comentarios";
$result_total = mysqli_query($conn, $sql_total);
$total = mysqli_fetch_assoc($result_total)['total'];

echo "<h3>📊 Estadísticas:</h3>";
echo "<p><strong>Total de comentarios:</strong> " . $total . "</p>";

if ($total > 0) {
    // Mostrar comentarios pendientes
    $sql_pendientes = "SELECT COUNT(*) as pendientes FROM comentarios WHERE aprobado = 0";
    $result_pendientes = mysqli_query($conn, $sql_pendientes);
    $pendientes = mysqli_fetch_assoc($result_pendientes)['pendientes'];
    
    echo "<p><strong>Comentarios pendientes:</strong> " . $pendientes . "</p>";
    
    // Mostrar comentarios aprobados
    $sql_aprobados = "SELECT COUNT(*) as aprobados FROM comentarios WHERE aprobado = 1";
    $result_aprobados = mysqli_query($conn, $sql_aprobados);
    $aprobados = mysqli_fetch_assoc($result_aprobados)['aprobados'];
    
    echo "<p><strong>Comentarios aprobados:</strong> " . $aprobados . "</p>";
    
    // Mostrar algunos comentarios de ejemplo
    $sql_ejemplos = "SELECT c.id_comentario, c.nombre_usuario, c.calificacion, c.comentario, c.fecha_comentario, c.aprobado, r.nombre as reloj
                    FROM comentarios c
                    LEFT JOIN reloj r ON c.id_reloj = r.id_reloj
                    ORDER BY c.fecha_comentario DESC
                    LIMIT 5";
    
    $result_ejemplos = mysqli_query($conn, $sql_ejemplos);
    
    echo "<h3>📝 Últimos 5 comentarios:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Usuario</th><th>Reloj</th><th>Calificación</th><th>Comentario</th><th>Fecha</th><th>Estado</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result_ejemplos)) {
        echo "<tr>";
        echo "<td>" . $row['id_comentario'] . "</td>";
        echo "<td>" . htmlspecialchars($row['nombre_usuario']) . "</td>";
        echo "<td>" . htmlspecialchars($row['reloj']) . "</td>";
        echo "<td>" . $row['calificacion'] . " ⭐</td>";
        echo "<td>" . htmlspecialchars(substr($row['comentario'], 0, 50)) . "...</td>";
        echo "<td>" . $row['fecha_comentario'] . "</td>";
        echo "<td>" . ($row['aprobado'] ? '✅ Aprobado' : '⏳ Pendiente') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ No hay comentarios en la base de datos</p>";
    
    // Verificar si hay relojes para hacer pruebas
    $sql_relojes = "SELECT COUNT(*) as total_relojes FROM reloj";
    $result_relojes = mysqli_query($conn, $sql_relojes);
    $total_relojes = mysqli_fetch_assoc($result_relojes)['total_relojes'];
    
    echo "<p><strong>Total de relojes:</strong> " . $total_relojes . "</p>";
    
    if ($total_relojes > 0) {
        echo "<p style='color: blue;'>💡 Puedes crear comentarios de prueba desde la página de información de un reloj</p>";
    } else {
        echo "<p style='color: red;'>❌ No hay relojes en la base de datos para crear comentarios</p>";
    }
}

mysqli_close($conn);
?>

