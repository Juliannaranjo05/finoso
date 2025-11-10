<?php
/**
 * TEST - MENSAJE DE RECUPERACIÓN DE PAGO
 * Prueba el mensaje de WhatsApp con enlace de recuperación
 */

require_once __DIR__ . '/../includes/WhatsAppTemplates.php';

echo "<html><head><meta charset='UTF-8'><title>Test Mensaje Recuperación</title>";
echo "<style>
body {
    font-family: Arial, sans-serif;
    background: #0a0a0a;
    color: #fff;
    padding: 40px;
    max-width: 800px;
    margin: 0 auto;
}
.container {
    background: #1a1a1a;
    border: 2px solid #FFCF66;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
}
h1 {
    color: #FFCF66;
    border-bottom: 2px solid #FFCF66;
    padding-bottom: 15px;
}
h2 {
    color: #FFB84D;
    margin-top: 30px;
}
.mensaje {
    background: #2a2a2a;
    border-left: 4px solid #25D366;
    padding: 20px;
    margin: 20px 0;
    white-space: pre-wrap;
    font-family: 'Courier New', monospace;
    line-height: 1.6;
    border-radius: 8px;
}
.info {
    background: rgba(255, 207, 102, 0.1);
    border: 1px solid rgba(255, 207, 102, 0.3);
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
}
.error {
    background: rgba(244, 67, 54, 0.1);
    border: 1px solid rgba(244, 67, 54, 0.3);
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    color: #F44336;
}
.success {
    background: rgba(76, 175, 80, 0.1);
    border: 1px solid rgba(76, 175, 80, 0.3);
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    color: #4CAF50;
}
a {
    color: #FFCF66;
}
</style></head><body>";

echo "<div class='container'>";
echo "<h1>📱 TEST: Mensajes de Orden Rechazada con Recuperación</h1>";

// CASO 1: Orden rechazada con monto pagado (puede recuperar)
echo "<h2>CASO 1: Rechazo por Monto Incorrecto (CON recuperación)</h2>";
echo "<div class='info'>";
echo "<strong>Escenario:</strong> Cliente pagó $15.000 pero el total era $117.000<br>";
echo "<strong>Monto pagado:</strong> $15.000<br>";
echo "<strong>Falta pagar:</strong> $102.000<br>";
echo "<strong>¿Puede recuperar?:</strong> ✅ SÍ - Se incluye enlace";
echo "</div>";

$datosConRecuperacion = [
    'orden_id' => 6,
    'nombre_reloj' => 'Rolex Submariner Dorado',
    'total' => 117000,
    'nombre_cliente' => 'Juan Pérez',
    'motivo_rechazo' => 'El monto del comprobante no coincide con el total del pedido. Pagaste $15.000 pero el total es $117.000',
    'monto_pagado' => 15000,
    'diferencia' => 102000,
    'url_recuperacion' => 'https://finoso.store/informacion/recuperar_pago.html?orden=6'
];

$mensajeConRecuperacion = WhatsAppTemplates::ordenRechazada($datosConRecuperacion);
echo "<div class='mensaje'>" . htmlspecialchars($mensajeConRecuperacion) . "</div>";

// CASO 2: Orden rechazada sin monto pagado (no puede recuperar)
echo "<h2>CASO 2: Rechazo por Otro Motivo (SIN recuperación)</h2>";
echo "<div class='info'>";
echo "<strong>Escenario:</strong> Comprobante ilegible o datos incorrectos<br>";
echo "<strong>Monto pagado:</strong> No registrado<br>";
echo "<strong>¿Puede recuperar?:</strong> ❌ NO - Mensaje genérico";
echo "</div>";

$datosSinRecuperacion = [
    'orden_id' => 7,
    'nombre_reloj' => 'Casio G-Shock Negro',
    'total' => 85000,
    'nombre_cliente' => 'María García',
    'motivo_rechazo' => 'El comprobante de pago está muy borroso y no se puede verificar la información. Por favor sube una imagen más clara.'
];

$mensajeSinRecuperacion = WhatsAppTemplates::ordenRechazada($datosSinRecuperacion);
echo "<div class='mensaje'>" . htmlspecialchars($mensajeSinRecuperacion) . "</div>";

// CASO 3: Orden rechazada con monto pagado pero sin diferencia (pago completo pero otro problema)
echo "<h2>CASO 3: Rechazo con Monto Completo pero Otro Problema</h2>";
echo "<div class='info'>";
echo "<strong>Escenario:</strong> Pagó completo pero comprobante de otra persona<br>";
echo "<strong>Monto pagado:</strong> $85.000 (completo)<br>";
echo "<strong>¿Puede recuperar?:</strong> ❌ NO - Mensaje genérico (no hay diferencia)";
echo "</div>";

$datosCompletoOtroProblema = [
    'orden_id' => 8,
    'nombre_reloj' => 'Seiko Automático Plateado',
    'total' => 85000,
    'nombre_cliente' => 'Carlos Rodríguez',
    'motivo_rechazo' => 'El comprobante pertenece a otra persona. El nombre del titular no coincide con el de la orden.'
];

$mensajeCompletoOtroProblema = WhatsAppTemplates::ordenRechazada($datosCompletoOtroProblema);
echo "<div class='mensaje'>" . htmlspecialchars($mensajeCompletoOtroProblema) . "</div>";

echo "</div>";

// Comparación
echo "<div class='container'>";
echo "<h1>📊 Comparación de Mensajes</h1>";

echo "<div class='success'>";
echo "<h3>✅ MENSAJE CON RECUPERACIÓN (Caso 1)</h3>";
echo "<ul>";
echo "<li>Incluye desglose: Ya pagaste / Falta pagar</li>";
echo "<li>Muestra enlace directo: <code>recuperar_pago.html?orden=6</code></li>";
echo "<li>Mensaje motivador: <strong>PUEDES SALVAR TU ORDEN</strong></li>";
echo "<li>Indica validez del link: 48 horas</li>";
echo "<li>CTA claro: Paga solo lo que falta</li>";
echo "</ul>";
echo "</div>";

echo "<div class='error'>";
echo "<h3>❌ MENSAJE SIN RECUPERACIÓN (Casos 2 y 3)</h3>";
echo "<ul>";
echo "<li>Mensaje genérico de rechazo</li>";
echo "<li>Sugiere volver a comprar desde el catálogo</li>";
echo "<li>Ofrece contacto por WhatsApp</li>";
echo "<li>No incluye enlace de recuperación</li>";
echo "</ul>";
echo "</div>";

echo "</div>";

// Instrucciones
echo "<div class='container'>";
echo "<h1>🧪 Cómo Probar en Tiempo Real</h1>";

echo "<div class='info'>";
echo "<h3>Paso 1: Rechaza una orden con monto pagado</h3>";
echo "<ol>";
echo "<li>Ve al panel de admin: <a href='https://finoso.store/admin/panel.php' target='_blank'>panel.php</a></li>";
echo "<li>Busca una orden pendiente (ej: orden #6)</li>";
echo "<li>Click en 'Rechazar'</li>";
echo "<li>Selecciona: <strong>'El monto del comprobante no coincide...'</strong></li>";
echo "<li>Ingresa el monto pagado: <strong>15000</strong></li>";
echo "<li>Confirma el rechazo</li>";
echo "</ol>";
echo "</div>";

echo "<div class='success'>";
echo "<h3>Paso 2: Verifica que llegó el WhatsApp</h3>";
echo "<ol>";
echo "<li>Revisa tu WhatsApp conectado a Twilio Sandbox</li>";
echo "<li>Deberías recibir el mensaje CON el enlace</li>";
echo "<li>Click en el enlace: <code>recuperar_pago.html?orden=6</code></li>";
echo "<li>Verifica que carga la página correctamente</li>";
echo "</ol>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>Paso 3: Simula el pago de diferencia</h3>";
echo "<ol>";
echo "<li>En la página <code>recuperar_pago.html</code>, verás:</li>";
echo "<li style='margin-left: 20px;'>- Total: $117.000</li>";
echo "<li style='margin-left: 20px;'>- Ya pagaste: $15.000</li>";
echo "<li style='margin-left: 20px;'>- Falta: $102.000</li>";
echo "<li>Sube un comprobante (imagen o PDF)</li>";
echo "<li>Click 'Enviar comprobante'</li>";
echo "<li>El sistema actualizará <code>monto_pagado</code> a $117.000</li>";
echo "<li>Estado cambiará a <code>pendiente_verificacion</code></li>";
echo "</ol>";
echo "</div>";

echo "</div>";

echo "<div class='container'>";
echo "<h1>🔗 Enlaces Útiles</h1>";
echo "<ul>";
echo "<li><a href='https://finoso.store/admin/panel.php'>Panel de Admin</a></li>";
echo "<li><a href='https://finoso.store/informacion/recuperar_pago.html?orden=6'>Página de Recuperación (ejemplo orden #6)</a></li>";
echo "<li><a href='https://finoso.store/perfil/perfil.html'>Perfil de Usuario</a></li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>

