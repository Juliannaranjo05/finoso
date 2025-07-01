<?php
include 'conexion.php';

// Obtener el contenido enviado por Mercado Pago
$body = json_decode(file_get_contents('php://input'), true);

// Verificar que hay datos
if (!$body || !isset($body['data']['id'])) {
    http_response_code(400);
    echo 'Faltan datos';
    exit;
}

$payment_id = $body['data']['id'];

// Consultar los detalles del pago usando la API
require __DIR__ . '/../../vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;

MercadoPagoConfig::setAccessToken('APP_USR-8081700083482823-052513-4b51160e6045855a3b6372cc0c14e686-2456154307');
$client = new PaymentClient();

try {
    $payment = $client->get($payment_id);

    // Validar que el pago esté aprobado
    if ($payment->status === 'approved') {
        $datos = json_decode(base64_decode($payment->external_reference), true);
        
        // Verificar que se pudieron decodificar los datos
        if (!$datos) {
            error_log('Error: No se pudieron decodificar los datos del external_reference');
            http_response_code(400);
            echo 'Error en datos';
            exit;
        }
        
        // Insertar en tabla orden
        $sql_orden = "INSERT INTO orden (nombre, cedula, celular, departamento, ciudad, direccion, barrio, referencias, total, metodo_pago, costo_envio, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pagado')";
        
        $total = $datos['precio_reloj'] + $datos['costo_envio'];
        
        $stmt = $conn->prepare($sql_orden);
        $stmt->bind_param("ssssssssdsd", $datos['nombre'], $datos['cedula'], $datos['celular'], $datos['departamento'], $datos['ciudad'], $datos['direccion'], $datos['barrio'], $datos['referencias'], $total, $datos['metodo_pago'], $datos['costo_envio']);
        $stmt->execute();
        
        $id_orden = $conn->insert_id;
        
        // Insertar en orden_detalle
        $sql_detalle = "INSERT INTO orden_detalle (id_orden, id_reloj, precio_unitario) VALUES (?, ?, ?)";
        $stmt2 = $conn->prepare($sql_detalle);
        $stmt2->bind_param("iid", $id_orden, $datos['id_reloj'], $datos['precio_reloj']);
        $stmt2->execute();
        
        error_log("Pago procesado exitosamente - ID Orden: $id_orden");
    }

    http_response_code(200);
    echo 'OK';
} catch (Exception $e) {
    error_log('Error en webhook: ' . $e->getMessage());
    http_response_code(500);
    echo 'Error';
}
?>