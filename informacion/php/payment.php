<?php
// Validar el estado recibido por GET
$status = $_GET['status'] ?? '';

// Generar cupón solo si fue exitoso
$mostrarCupon = ($status === 'success');
$codigoCupon = strtoupper(substr(uniqid('FINO'), -8)); // Ejemplo: FINO3F9A1D7E
$descuento = 10; // 10% de descuento para próxima compra
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gracias por tu compra | FINOSO</title>
  <link rel="stylesheet" href="estilos.css"> <!-- Asegúrate de enlazar bien -->
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom, #000000, #FFCF66);
      color: #FFCF66;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      flex-direction: column;
      text-align: center;
    }
    h1 {
      font-size: 3rem;
      margin-bottom: 0.5em;
    }
    .cupon {
      background: #1f1f1f;
      border: 2px dashed #FFCF66;
      padding: 20px;
      margin-top: 2em;
      font-size: 1.2rem;
      border-radius: 10px;
      width: 90%;
      max-width: 500px;
    }
    .cupon span {
      font-weight: bold;
      font-size: 1.5rem;
      display: block;
      margin-top: 0.5em;
      color: #ffffff;
    }
    .boton {
      margin-top: 2em;
      background: #FFCF66;
      color: #000;
      border: none;
      padding: 10px 20px;
      font-size: 1rem;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <?php if ($status === 'success'): ?>
    <h1>¡Gracias por tu compra!</h1>
    <p>Tu reloj está siendo preparado con todo el lujo que te mereces.</p>

    <div class="cupon">
      <p>Como agradecimiento, aquí tienes un <strong><?= $descuento ?>% de descuento</strong> en tu próxima compra.</p>
      <span><?= $codigoCupon ?></span>
      <small>Usa este código al finalizar tu próxima compra</small>
    </div>

    <a href="/catalogo" class="boton">Volver al catálogo</a>

  <?php elseif ($status === 'failure'): ?>
    <h1>Ocurrió un problema 😓</h1>
    <p>No pudimos procesar tu pago. Por favor intenta nuevamente o usa otro método de pago.</p>
    <a href="/catalogo" class="boton">Volver a intentar</a>

  <?php elseif ($status === 'pending'): ?>
    <h1>¡Gracias por tu pedido!</h1>
    <p>Tu pago está pendiente de aprobación. Te notificaremos cuando se confirme.</p>
    <a href="../../catalogo/catalogo.html" class="boton">Volver al catálogo</a>

  <?php else: ?>
    <h1>Error</h1>
    <p>No se pudo determinar el estado del pago.</p>
    <a href="/catalogo" class="boton">Ir al catálogo</a>
  <?php endif; ?>
</body>
</html>
