<?php
/**
 * TEMPLATES DE MENSAJES WHATSAPP
 * 
 * Aquí se definen todos los mensajes que se enviarán por WhatsApp
 * Puedes personalizar cada mensaje según tus necesidades
 */

class WhatsAppTemplates {
    
    /**
     * 1. COMPRA EXITOSA - Después de subir comprobante
     */
    public static function compraExitosa($datos) {
        $ordenId = $datos['orden_id'];
        $nombreReloj = $datos['nombre_reloj'];
        $total = number_format($datos['total'] / 1000, 0, '', '.');
        $nombreCliente = $datos['nombre_cliente'];
        
        return "¡Gracias por tu compra en FINOSO! 🎉\n\n" .
               "Hola {$nombreCliente}!\n\n" .
               "📦 Orden #{$ordenId}\n" .
               "⌚ Reloj: {$nombreReloj}\n" .
               "💰 Total: \${$total}.000\n\n" .
               "✅ Tu comprobante fue recibido correctamente\n" .
               "Lo verificaremos en las próximas 24-48 horas\n\n" .
               "📱 Te notificaremos cuando se apruebe tu pago\n\n" .
               "¿Dudas? Responde este mensaje";
    }
    
    /**
     * 2. PAGO APROBADO - Admin aprueba el comprobante
     */
    public static function pagoAprobado($datos) {
        $ordenId = $datos['orden_id'];
        $nombreReloj = $datos['nombre_reloj'];
        $nombreCliente = $datos['nombre_cliente'];
        
        return "✅ ¡Tu pago fue APROBADO! 🎊\n\n" .
               "Hola {$nombreCliente}!\n\n" .
               "📦 Orden #{$ordenId}\n" .
               "⌚ {$nombreReloj}\n\n" .
               "🚚 Próximo paso: ENVÍO\n" .
               "📅 Tiempo estimado: 2-4 días hábiles\n\n" .
               "Te enviaremos la guía de seguimiento muy pronto\n\n" .
               "¡Gracias por confiar en FINOSO! ⌚✨";
    }
    
    /**
     * 3. PRODUCTO ENVIADO - Con guía de seguimiento
     */
    public static function productoEnviado($datos) {
        $ordenId = $datos['orden_id'];
        $nombreReloj = $datos['nombre_reloj'];
        $nombreCliente = $datos['nombre_cliente'];
        $transportadora = $datos['transportadora'] ?? 'SERVIENTREGA';
        $guia = $datos['guia'] ?? 'En proceso';
        $fechaEstimada = $datos['fecha_estimada'] ?? date('d M Y', strtotime('+3 days'));
        
        return "📦 ¡Tu reloj va en camino! 🚚\n\n" .
               "Hola {$nombreCliente}!\n\n" .
               "Orden #{$ordenId}\n" .
               "⌚ {$nombreReloj}\n\n" .
               "📍 Transportadora: {$transportadora}\n" .
               "🔢 Guía: {$guia}\n" .
               "📅 Llegada estimada: {$fechaEstimada}\n\n" .
               "Rastrea tu pedido aquí:\n" .
               "https://www.servientrega.com/rastreo/\n\n" .
               "¡Ya casi es tuyo! 🎁";
    }
    
    /**
     * 4. PRODUCTO ENTREGADO - Solicitar feedback
     */
    public static function productoEntregado($datos) {
        $ordenId = $datos['orden_id'];
        $nombreReloj = $datos['nombre_reloj'];
        $nombreCliente = $datos['nombre_cliente'];
        
        return "🎉 ¡Entrega completada! ⌚\n\n" .
               "Hola {$nombreCliente}!\n\n" .
               "Tu {$nombreReloj} fue entregado exitosamente\n" .
               "Orden #{$ordenId}\n\n" .
               "¿Cómo estuvo tu experiencia? 😊\n" .
               "Tu opinión nos ayuda a mejorar\n\n" .
               "📸 Comparte una foto con tu reloj\n" .
               "🌟 Etiquétanos en Instagram: @finoso.club\n\n" .
               "🔒 Garantía: 30 días\n" .
               "📱 Soporte: Responde este mensaje\n\n" .
               "¡Gracias por elegir FINOSO! 💛";
    }
    
    /**
     * 5. CARRITO ABANDONADO - Recuperación (NO IMPLEMENTADA AÚN)
     */
    public static function carritoAbandonado($datos) {
        $nombreCliente = $datos['nombre_cliente'] ?? 'Cliente';
        $nombreReloj = $datos['nombre_reloj'];
        $precio = number_format($datos['precio'] / 1000, 0, '', '.');
        
        return "Hola {$nombreCliente}! 👋\n\n" .
               "Notamos que dejaste este reloj en tu carrito:\n" .
               "⌚ {$nombreReloj}\n" .
               "💰 \${$precio}.000\n\n" .
               "¿Necesitas ayuda para completar tu compra?\n\n" .
               "🎁 Usa el código: FINOSO10\n" .
               "para 10% OFF (válido 48h)\n\n" .
               "Responde este mensaje si tienes dudas 😊";
    }
    
    /**
     * 6. RECORDATORIO PAGO PENDIENTE - 12h después de crear orden
     */
    public static function recordatorioPago($datos) {
        $ordenId = $datos['orden_id'];
        $nombreReloj = $datos['nombre_reloj'];
        $total = number_format($datos['total'] / 1000, 0, '', '.');
        $nombreCliente = $datos['nombre_cliente'];
        
        return "⏰ Recordatorio FINOSO\n\n" .
               "Hola {$nombreCliente}!\n\n" .
               "Tu orden #{$ordenId} está pendiente de pago\n\n" .
               "⌚ {$nombreReloj}\n" .
               "💰 \${$total}.000\n\n" .
               "📎 Sube tu comprobante aquí:\n" .
               "https://finoso.com/informacion/informacion.html?id_orden={$ordenId}\n\n" .
               "¿Necesitas otra forma de pago?\n" .
               "Responde este mensaje 📱";
    }
    
    /**
     * 7. ORDEN RECHAZADA - Recordatorio para reintentar
     */
    public static function ordenRechazada($datos) {
        $ordenId = $datos['orden_id'];
        $nombreReloj = $datos['nombre_reloj'];
        $total = number_format($datos['total'] / 1000, 0, '', '.');
        $nombreCliente = $datos['nombre_cliente'];
        $motivo = $datos['motivo_rechazo'] ?? 'inconsistencias en el comprobante';
        $montoPagado = isset($datos['monto_pagado']) ? number_format($datos['monto_pagado'] / 1000, 0, '', '.') : null;
        $diferencia = isset($datos['diferencia']) ? number_format($datos['diferencia'] / 1000, 0, '', '.') : null;
        $urlRecuperacion = $datos['url_recuperacion'] ?? null;
        
        $mensaje = "❌ Orden Rechazada - FINOSO\n\n" .
                   "Hola {$nombreCliente},\n\n" .
                   "Tu orden #{$ordenId} fue rechazada:\n" .
                   "⌚ {$nombreReloj}\n" .
                   "💰 Total: \${$total}.000\n\n" .
                   "📋 Motivo:\n" .
                   "{$motivo}\n\n";
        
        // Si hay monto pagado (problema de monto incorrecto), mostrar opción de completar pago
        if ($montoPagado && $urlRecuperacion) {
            $mensaje .= "💡 PUEDES SALVAR TU ORDEN 💡\n\n" .
                       "✅ Ya pagaste: \${$montoPagado}.000\n" .
                       "❌ Falta pagar: \${$diferencia}.000\n\n" .
                       "🎯 ¡COMPLETA TU PAGO AQUÍ!\n" .
                       "{$urlRecuperacion}\n\n" .
                       "📱 Paga solo lo que falta y recupera tu orden\n" .
                       "⏰ Link válido por 48 horas\n\n";
        } else {
            // Mensaje genérico para otros tipos de rechazo
            $mensaje .= "🔄 ¿Quieres reintentar?\n" .
                       "Puedes hacer una nueva compra o contactarnos para ayudarte.\n\n";
        }
        
        $mensaje .= "💬 ¿Necesitas ayuda?\n" .
                   "Responde este mensaje y te asistimos 😊\n\n" .
                   "🛒 Volver a comprar:\n" .
                   "https://finoso.store/catalogo/catalogo.html";
        
        return $mensaje;
    }
    
    /**
     * 8. NUEVA ORDEN PARA ADMIN - Notificación interna
     */
    public static function nuevaOrdenAdmin($datos) {
        $ordenId = $datos['orden_id'];
        $nombreCliente = $datos['nombre_cliente'];
        $telefono = $datos['telefono'];
        $email = $datos['email'];
        $nombreReloj = $datos['nombre_reloj'];
        $total = number_format($datos['total'] / 1000, 0, '', '.');
        $metodoPago = $datos['metodo_pago'] ?? 'Nequi';
        
        return "🔔 NUEVA ORDEN #{$ordenId}\n\n" .
               "Cliente: {$nombreCliente}\n" .
               "📱 {$telefono}\n" .
               "📧 {$email}\n\n" .
               "⌚ {$nombreReloj}\n" .
               "💰 \${$total}.000\n" .
               "🏦 {$metodoPago}\n\n" .
               "✅ Comprobante adjunto\n\n" .
               "👉 Revisar orden:\n" .
               "http://localhost/finoso/admin/panel.html";
    }
    
    /**
     * 8. REPORTE MENSUAL PARA ADMIN
     */
    public static function reporteMensualAdmin($datos) {
        $mes = $datos['mes'];
        $anio = $datos['anio'];
        $ventas_total = number_format($datos['ventas_total'] ?? 0, 0, ',', '.');
        $ventas_productos = number_format($datos['ventas_productos'] ?? 0, 0, ',', '.');
        $ventas_envios = number_format($datos['ventas_envios'] ?? 0, 0, ',', '.');
        $num_ordenes = $datos['num_ordenes'] ?? 0;
        $ticket_promedio = number_format($datos['ticket_promedio'] ?? 0, 0, ',', '.');
        $ordenes_entregadas = $datos['ordenes_entregadas'] ?? 0;
        $ordenes_enviadas = $datos['ordenes_enviadas'] ?? 0;
        $ordenes_pagadas = $datos['ordenes_pagadas'] ?? 0;
        
        $mensaje = "📊 REPORTE MENSUAL FINOSO\n";
        $mensaje .= "{$mes} {$anio}\n\n";
        $mensaje .= "💰 Ventas Totales: \${$ventas_total}\n";
        $mensaje .= "   📦 Productos: \${$ventas_productos}\n";
        $mensaje .= "   🚚 Envíos: \${$ventas_envios}\n\n";
        $mensaje .= "📦 Órdenes Válidas: {$num_ordenes}\n";
        $mensaje .= "🎯 Ticket Promedio: \${$ticket_promedio}\n\n";
        $mensaje .= "📊 ESTADO DE ÓRDENES:\n";
        $mensaje .= "✅ Entregadas: {$ordenes_entregadas}\n";
        $mensaje .= "🚚 En Envío: {$ordenes_enviadas}\n";
        $mensaje .= "💳 Pagadas/Aprobadas: {$ordenes_pagadas}\n";
        
        // Top relojes vendidos
        if (isset($datos['top_relojes']) && !empty($datos['top_relojes'])) {
            $mensaje .= "\n🏆 TOP RELOJES:\n";
            foreach ($datos['top_relojes'] as $idx => $reloj) {
                $posicion = $idx + 1;
                $nombre = $reloj['nombre'] ?? 'N/A'; // No usar htmlspecialchars en WhatsApp
                $cantidad = $reloj['cantidad'] ?? 0;
                $mensaje .= "{$posicion}. {$nombre} ({$cantidad} ventas)\n";
            }
        }
        
        return $mensaje;
    }
}
?>

