<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['nombre'])) {
    echo json_encode([
        'logged_in' => true,
        'nombre' => $_SESSION['nombre']
    ]);
} else {
    echo json_encode([
        'logged_in' => false
    ]);
}
?>