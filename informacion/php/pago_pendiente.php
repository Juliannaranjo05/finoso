<?php
// Capturar datos enviados por Mercado Pago
$collection_id = $_GET['collection_id'] ?? '';
$status = $_GET['status'] ?? 'pendiente';
$payment_id = $_GET['payment_id'] ?? '';
$merchant_order_id = $_GET['merchant_order_id'] ?? '';
$order_id = $_GET['external_reference'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pago Pendiente - FINOSO</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #0c0c0c;
      color: #e0e0e0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
    }
    .card {
      background-color: #1c1c1c;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 0 15px rgba(0, 255, 136, 0.2);
      text-align: center;
      max-width: 500px;
    }
    .card h1 {
      color: #00ff88;
      margin-bottom: 10px;
    }
    .card img {
      width: 100px;
      margin: 20px 0;
    }
    .info {
      margin: 20px 0;
      font-size: 16px;
      color: #ccc;
    }
    .order-id {
      background-color: #2e2e2e;
      padding: 10px;
      border-radius: 8px;
      margin-top: 10px;
      font-weight: bold;
      color: #00ff88;
    }
    .footer {
      margin-top: 30px;
      font-size: 13px;
      color: #666;
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>Tu pago está en proceso</h1>
    <img src="https://finoso.store/finoso/img/pendiente-icono.png" alt="Pago pendiente">
    <p class="info">Estamos procesando tu pago. Esto puede tardar unos minutos.</p>

    <?php if ($order_id): ?>
      <div class="order-id">Número de orden: #<?= htmlspecialchars($order_id) ?></div>
    <?php endif; ?>

    <p class="info">Una vez se confirme, recibirás un correo electrónico con los detalles.</p>
  </div>

  <div class="footer">
    FINOSO &copy; <?= date('Y') ?>. Todos los derechos reservados.
  </div>
</body>
</html>