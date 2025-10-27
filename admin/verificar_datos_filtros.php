<?php
require_once 'conexion.php';

$sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN movimiento IS NOT NULL AND movimiento != '' THEN 1 ELSE 0 END) as con_movimiento,
    SUM(CASE WHEN pulsera IS NOT NULL AND pulsera != '' THEN 1 ELSE 0 END) as con_pulsera,
    SUM(CASE WHEN resistencia_agua IS NOT NULL AND resistencia_agua != '' THEN 1 ELSE 0 END) as con_resistencia,
    SUM(CASE WHEN disponible = 1 AND vendido = 0 THEN 1 ELSE 0 END) as disponibles
FROM reloj";

$result = $conn->query($sql);
$stats = $result->fetch_assoc();

echo "========== ESTADO DE DATOS PARA FILTROS ==========\n";
echo "Total de relojes: " . $stats['total'] . "\n";
echo "Relojes disponibles (no vendidos): " . $stats['disponibles'] . "\n";
echo "\n";
echo "Con datos de MOVIMIENTO: " . $stats['con_movimiento'] . " (" . round(($stats['con_movimiento']/$stats['total'])*100, 1) . "%)\n";
echo "Con datos de PULSERA: " . $stats['con_pulsera'] . " (" . round(($stats['con_pulsera']/$stats['total'])*100, 1) . "%)\n";
echo "Con datos de RESISTENCIA: " . $stats['con_resistencia'] . " (" . round(($stats['con_resistencia']/$stats['total'])*100, 1) . "%)\n";
echo "\n";

// Obtener valores únicos
echo "========== VALORES ÚNICOS ==========\n";

$movimientos = $conn->query("SELECT DISTINCT movimiento FROM reloj WHERE movimiento IS NOT NULL AND movimiento != '' AND disponible = 1 ORDER BY movimiento");
echo "\nMOVIMIENTOS:\n";
while ($row = $movimientos->fetch_assoc()) {
    echo "  - " . $row['movimiento'] . "\n";
}

$pulseras = $conn->query("SELECT DISTINCT pulsera FROM reloj WHERE pulsera IS NOT NULL AND pulsera != '' AND disponible = 1 ORDER BY pulsera");
echo "\nPULSERAS:\n";
while ($row = $pulseras->fetch_assoc()) {
    echo "  - " . $row['pulsera'] . "\n";
}

$resistencias = $conn->query("SELECT DISTINCT resistencia_agua FROM reloj WHERE resistencia_agua IS NOT NULL AND resistencia_agua != '' AND disponible = 1 ORDER BY resistencia_agua");
echo "\nRESISTENCIAS AL AGUA:\n";
while ($row = $resistencias->fetch_assoc()) {
    echo "  - " . $row['resistencia_agua'] . "\n";
}

$conn->close();
?>

