<?php
/**
 * Verificación de sesión y rol de administrador para el panel de administración
 */

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nombre'])) {
    // Redirigir al login si no hay sesión
    header('Location: ../login/login.html');
    exit;
}

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    // Redirigir al index si no es administrador (sin mostrar alerts)
    header('Location: ../index.html');
    exit;
}
?>




