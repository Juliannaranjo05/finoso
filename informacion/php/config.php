<?php
require __DIR__ . '/../vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;          

// === CREDENCIALES DE PRODUCCIÓN ===
define('MP_ACCESS_TOKEN', 'APP_USR-8081700083482823-052513-4b51160e6045855a3b6372cc0c14e686-2456154307');
define('MP_PUBLIC_KEY', 'APP_USR-4c28e5a0-022c-4337-8564-b0e0d9aa9f4f');

// Configuraciones adicionales
define('MP_COUNTRY', 'CO'); // Cambia según tu país (AR, BR, CL, CO, MX, PE, UY, VE)
define('MP_CURRENCY', 'COP'); // Cambia según tu moneda (ARS, BRL, CLP, COP, MXN, PEN, UYU, VES)

// MODO PRODUCCIÓN (sin configurar sandbox)
// MercadoPagoConfig::setEnvironment(\MercadoPago\MercadoPagoConfig::SANDBOX); // Comentado

// URLs base para callbacks (ajusta según tu dominio)
define('BASE_URL', 'https://finoso.store'); // Cambia por tu dominio real

// Configuración de logging (opcional)
define('MP_DEBUG', true); // Cambiar a false en producción