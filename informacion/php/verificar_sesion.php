<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (isset($_SESSION['id_usuario'])) {
    echo json_encode([
        'logged_in' => true,
        'id_usuario' => $_SESSION['id_usuario'],
        'nombre' => $_SESSION['nombre'] ?? null,
        'correo' => $_SESSION['correo'] ?? null
    ]);
} else {
    echo json_encode([
        'logged_in' => false
    ]);
}
?>
