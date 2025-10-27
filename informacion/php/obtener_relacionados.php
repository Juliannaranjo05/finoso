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

$id_reloj = intval($_GET['id_reloj']);

// Primero obtenemos la marca del reloj principal usando prepared statement
$sqlMarca = "SELECT marca FROM reloj WHERE id_reloj = ?";
$stmtMarca = mysqli_prepare($conn, $sqlMarca);

if (!$stmtMarca) {
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param($stmtMarca, "i", $id_reloj);
mysqli_stmt_execute($stmtMarca);
$resultMarca = mysqli_stmt_get_result($stmtMarca);

if (mysqli_num_rows($resultMarca) === 0) {
    echo json_encode(['error' => 'Reloj no encontrado']);
    exit;
}

$marca = mysqli_fetch_assoc($resultMarca)['marca'];
mysqli_stmt_close($stmtMarca);

// Luego buscamos otros relojes con la misma marca, excluyendo el reloj principal
$sqlRelacionados = "SELECT * FROM reloj WHERE marca = ? AND id_reloj != ? LIMIT 4";
$stmtRelacionados = mysqli_prepare($conn, $sqlRelacionados);

if (!$stmtRelacionados) {
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param($stmtRelacionados, "si", $marca, $id_reloj);
mysqli_stmt_execute($stmtRelacionados);
$resultRelacionados = mysqli_stmt_get_result($stmtRelacionados);

$relacionados = [];
while ($row = mysqli_fetch_assoc($resultRelacionados)) {
    $relacionados[] = $row;
}

mysqli_stmt_close($stmtRelacionados);

echo json_encode($relacionados);
?>