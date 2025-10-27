<?php
/**
 * VERIFICAR SESIÓN ACTIVA
 * Redirige a login si NO hay sesión
 * Uso: Páginas que requieren estar logueado (perfil, carrito con sesión, etc.)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si NO hay sesión activa, redirigir a login
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    header('Location: /finoso/login/login.html');
    exit;
}

// Usuario está logueado, continuar con la página
?>

