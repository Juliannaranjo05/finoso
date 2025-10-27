<?php
/**
 * Sistema de protección de rutas - Session Guard
 * Verifica sesiones y redirige según el tipo de usuario
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de rutas
$config = [
    'redirect_unauthorized' => '/finoso/index.html',
    'redirect_admin_required' => '/finoso/index.html', 
    'redirect_user_required' => '/finoso/login/login.html'
];

/**
 * Verificar si hay una sesión válida
 */
function hasValidSession() {
    return isset($_SESSION['id_usuario']) && 
           isset($_SESSION['nombre']) && 
           isset($_SESSION['rol']);
}

/**
 * Verificar si el usuario es administrador
 */
function isAdmin() {
    return hasValidSession() && $_SESSION['rol'] === 'administrador';
}

/**
 * Verificar si el usuario es usuario normal
 */
function isUser() {
    return hasValidSession() && $_SESSION['rol'] === 'usuario';
}

/**
 * Proteger página que requiere sesión de administrador
 */
function requireAdmin($redirect_url = null) {
    global $config;
    
    if (!isAdmin()) {
        $redirect = $redirect_url ?? $config['redirect_admin_required'];
        header("Location: $redirect");
        exit();
    }
}

/**
 * Proteger página que requiere sesión de usuario
 */
function requireUser($redirect_url = null) {
    global $config;
    
    if (!isUser()) {
        $redirect = $redirect_url ?? $config['redirect_user_required'];
        header("Location: $redirect");
        exit();
    }
}

/**
 * Proteger página que requiere cualquier sesión válida
 */
function requireSession($redirect_url = null) {
    global $config;
    
    if (!hasValidSession()) {
        $redirect = $redirect_url ?? $config['redirect_user_required'];
        header("Location: $redirect");
        exit();
    }
}

/**
 * Redirigir según el rol del usuario
 */
function redirectByRole() {
    if (isAdmin()) {
        header('Location: /finoso/admin/panel.html');
        exit();
    } elseif (isUser()) {
        header('Location: /finoso/index.html');
        exit();
    }
}

/**
 * Verificar sesión y devolver datos del usuario (para AJAX)
 */
function checkSessionAjax() {
    if (!hasValidSession()) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado', 'redirect' => '/finoso/index.html']);
        exit();
    }
    
    return [
        'id_usuario' => $_SESSION['id_usuario'],
        'nombre' => $_SESSION['nombre'],
        'correo' => $_SESSION['correo'],
        'rol' => $_SESSION['rol']
    ];
}

/**
 * Verificar sesión de admin para AJAX
 */
function checkAdminAjax() {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Acceso denegado', 'redirect' => '/finoso/index.html']);
        exit();
    }
    
    return checkSessionAjax();
}

/**
 * Verificar sesión de usuario para AJAX
 */
function checkUserAjax() {
    if (!isUser()) {
        http_response_code(403);
        echo json_encode(['error' => 'Acceso denegado', 'redirect' => '/finoso/login/login.html']);
        exit();
    }
    
    return checkSessionAjax();
}
?>




