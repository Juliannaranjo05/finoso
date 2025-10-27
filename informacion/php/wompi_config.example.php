<?php
/**
 * CONFIGURACIÓN DE WOMPI - ARCHIVO DE EJEMPLO
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo como 'wompi_config.php'
 * 2. Reemplaza los valores de ejemplo con tus credenciales reales de Wompi
 * 3. NO subas 'wompi_config.php' a Git (ya está en .gitignore)
 * 
 * Para obtener tus credenciales:
 * 1. Ve a https://comercios.wompi.co/
 * 2. Inicia sesión en tu cuenta
 * 3. Ve a Configuración > API Keys
 * 4. Copia tus llaves de producción o sandbox
 */

// Credenciales de Wompi (llaves de producción - usar en sandbox para pruebas)
define('WOMPI_PUBLIC_KEY', 'pub_prod_XXXXXXXXXXXXXXXXXXXXXXXX');
define('WOMPI_PRIVATE_KEY', 'prv_prod_XXXXXXXXXXXXXXXXXXXXXXXX');
define('WOMPI_EVENTS_SECRET', 'prod_integrity_XXXXXXXXXXXXXXXXXXXXXXXX');

// URLs de Wompi
define('WOMPI_BASE_URL', 'https://api.wompi.co/v1/');
define('WOMPI_SANDBOX_URL', 'https://api-sandbox.wompi.co/v1/');

// Configuración del entorno - PRODUCCIÓN para dominio real
define('WOMPI_ENVIRONMENT', 'production'); // 'production' o 'sandbox'

// URLs de redirección (cambiar por tu dominio real)
define('WOMPI_REDIRECT_URL', 'https://tudominio.com/informacion/php/wompi_response.php');
define('WOMPI_REDIRECT_URL_CARRITO', 'https://tudominio.com/informacion-carrito/php/wompi_response_carrito.php');

// VPOS ID de producción (obtener en el panel de Wompi)
define('WOMPI_VPOS_ID', 'VPOS_XXXXXX');

// Función para obtener la URL base según el entorno
function getWompiBaseUrl() {
    return WOMPI_ENVIRONMENT === 'production' ? WOMPI_BASE_URL : WOMPI_SANDBOX_URL;
}

// Función para obtener las credenciales según el entorno
function getWompiCredentials() {
    return [
        'public_key' => WOMPI_PUBLIC_KEY,
        'private_key' => WOMPI_PRIVATE_KEY
    ];
}
?>

