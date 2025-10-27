<?php
/**
 * VERIFICAR QUE NO HAYA SESIÓN
 * Redirige a index si YA hay sesión activa
 * Uso: Páginas de login/registro (no deben ser accesibles si ya estás logueado)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si YA hay sesión activa, redirigir a index
if (isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario'])) {
    header('Location: /finoso/index.html');
    exit;
}

// No hay sesión, continuar con la página de login/registro
?>

