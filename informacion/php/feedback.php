<?php
include 'conexion.php';

$es_simulado = isset($_GET['simulado']) && $_GET['simulado'] == 1;

// Si es simulado, recuperamos del navegador usando JS
if ($es_simulado) {
    echo "<script>
        const datos = JSON.parse(localStorage.getItem('datos_orden'));
        if (!datos) {
            document.body.innerHTML = '<h2>Error: No hay datos de orden simulada</h2>';
        } else {
            fetch('mercadopago_webhook.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(datos)
            })
            .then(res => res.text())
            .then(res => {
                document.body.innerHTML = '<h2>¡Gracias por tu compra simulada!</h2><p>' + res + '</p>';
                localStorage.removeItem('datos_orden');
            });
        }
    </script>";
    exit;
}

// MODO REAL CON PAYMENT_ID AQUÍ (cuando ya tengas pagos reales funcionando)
if (isset($_GET['payment_id'])) {
    echo "<h2>Procesando pago real... (aquí validas con la API de MercadoPago)</h2>";
    // Aquí iría el código para consultar el payment_id y guardar en la base de datos
}
?>
