<?php
// Script rápido para resetear recordatorio y poder probar de nuevo
include 'conexion.php';

$sql = "UPDATE orden SET recordatorio_enviado = 0 WHERE estado = 'rechazado'";
if ($conn->query($sql)) {
    echo "✅ Campo recordatorio_enviado reseteado a 0 para todas las órdenes rechazadas.<br>";
    echo "Ahora puedes probar el envío de recordatorio.<br><br>";
    echo "<a href='test_recordatorio_rechazada.html'>⬅️ Volver a la página de prueba</a>";
} else {
    echo "❌ Error: " . $conn->error;
}

$conn->close();
?>

