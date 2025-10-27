<?php
// Configuración de Wompi - PRODUCCIÓN
// Credenciales de Wompi (llaves de producción)
define('WOMPI_PUBLIC_KEY', 'pub_prod_fMaA3WRKZJMMdfkwVIPubjCDDlHIcBrm');
define('WOMPI_PRIVATE_KEY', 'prv_prod_CpdPXvM70mdNBLO8F2LP2VE8C0h6tEUC');
define('WOMPI_EVENTS_SECRET', 'prod_integrity_SuvWE59pcAcQETgWhre9Ayxc0LkFXjAy');

// URLs de Wompi
define('WOMPI_BASE_URL', 'https://api.wompi.co/v1/');
define('WOMPI_SANDBOX_URL', 'https://api-sandbox.wompi.co/v1/');

// Configuración del entorno - PRODUCCIÓN para dominio real
define('WOMPI_ENVIRONMENT', 'production'); // 'production' para dominio real

// URLs de redirección (dominio real para producción)
define('WOMPI_REDIRECT_URL', 'https://finoso.store/informacion/php/wompi_response.php');
define('WOMPI_REDIRECT_URL_CARRITO', 'https://finoso.store/informacion-carrito/php/wompi_response_carrito.php');

// VPOS ID de producción
define('WOMPI_VPOS_ID', 'VPOS_bZ9re4');

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

function verificarConfiguracionTwilio() {
    return defined('TWILIO_ENABLED') && TWILIO_ENABLED === true;
}
?>
