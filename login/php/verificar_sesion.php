<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (isset($_SESSION['nombre'])) {
    echo json_encode([
        'logged_in' => true,
        'id_usuario' => $_SESSION['id_usuario'] ?? null,
        'nombre' => $_SESSION['nombre'],
        'correo' => $_SESSION['correo'] ?? null
    ]);
} else {
    echo json_encode([
        'logged_in' => false
    ]);
}
?>