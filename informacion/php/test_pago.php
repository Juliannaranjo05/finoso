<?php
require '../vendor/autoload.php'; // Asegúrate de que esta ruta sea correcta
require 'config.php'; // Asegúrate de que esta ruta sea correcta

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Resources\Preference\Item;

// Configura tu token (método actualizado para v3.x)
MercadoPagoConfig::setAccessToken('TEST-5002356726305963-061612-b364cd52334af15fedb1ee9c3bdcf0ac-2456154307');

// Crea un ítem de ejemplo
$item = new Item();
$item->title = "Reloj usado";
$item->quantity = 1;
$item->unit_price = 50000;

// Crear el cliente de preferencias
$client = new PreferenceClient();

// Crear la preferencia de pago
$preference = $client->create([
    "items" => [$item],
    "back_urls" => [
        "success" => "https://tusitio.com/success.php",
        "failure" => "https://tusitio.com/failure.php",
        "pending" => "https://tusitio.com/pending.php"
    ],
    "auto_return" => "approved"
]);

// Enlace al checkout
echo '<a href="' . $preference->init_point . '" target="_blank">Ir al pago con Mercado Pago</a>';
?>