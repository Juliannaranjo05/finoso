<?php
// Webhook de Wompi para recibir notificaciones de eventos
session_start();

include 'wompi_config.php';
include 'conexion.php';

$LOG_FILE = __DIR__ . '/../../logs/wompi_flow.log';
if (!file_exists(dirname($LOG_FILE))) {
    @mkdir(dirname($LOG_FILE), 0775, true);
}

function wompi_log($message) {
    global $LOG_FILE;
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message\n", 3, $LOG_FILE);
}

// Obtener el cuerpo de la petición
$input = file_get_contents('php://input');
if (!$input) {
    wompi_log('❌ Webhook recibido sin cuerpo');
}
if (isset($_SERVER['REQUEST_URI'])) {
    wompi_log('=== ➡️ wompi_webhook.php INICIO (' . $_SERVER['REQUEST_URI'] . ') ===');
}
$event = json_decode($input, true);
wompi_log('Payload bruto: ' . $input);

// Verificar que es un evento válido
if (!$event || !isset($event['data']) || !isset($event['event'])) {
    wompi_log('❌ Evento inválido recibido');
    http_response_code(400);
    exit('Evento inválido');
}

// Verificar la firma del evento (importante para seguridad)
$signature = $_SERVER['HTTP_SIGNATURE'] ?? '';
$expected_signature = hash_hmac('sha256', $input, WOMPI_EVENTS_SECRET);

if (!hash_equals($signature, $expected_signature)) {
    wompi_log('❌ Firma inválida. header=' . $signature . ' expected=' . $expected_signature);
    http_response_code(401);
    exit('Firma inválida');
}

// Log del evento recibido
wompi_log('Evento Wompi recibido: ' . json_encode($event));

// Procesar según el tipo de evento
switch ($event['event']) {
    case 'transaction.updated':
        wompi_log('➡️ Evento transaction.updated');
        procesarActualizacionTransaccion($event['data'], $conn);
        break;
    
    case 'transaction.approved':
        wompi_log('➡️ Evento transaction.approved');
        procesarTransaccionAprobada($event['data'], $conn);
        break;
    
    case 'transaction.declined':
        wompi_log('➡️ Evento transaction.declined');
        procesarTransaccionDeclinada($event['data'], $conn);
        break;
    
    default:
        wompi_log('Evento Wompi no manejado: ' . $event['event']);
}

// Responder con 200 OK
http_response_code(200);
echo 'OK';
wompi_log('=== ⬅️ wompi_webhook.php FIN ===');

/**
 * Procesar actualización de transacción
 */
function procesarActualizacionTransaccion($transactionData, $conn) {
    $transaction_id = $transactionData['id'];
    $status = $transactionData['status'];
    $reference = $transactionData['reference'];
    
    wompi_log("Actualizando transacción $transaction_id a estado: $status");
    
    // Buscar la orden por referencia
    $stmt = $conn->prepare("SELECT id_orden FROM orden WHERE token_verificacion = ?");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $orden = $result->fetch_assoc();
        $id_orden = $orden['id_orden'];
        
        // Actualizar estado de la orden
        $nuevo_estado = '';
        switch ($status) {
            case 'APPROVED':
                $nuevo_estado = 'pagado';
                break;
            case 'DECLINED':
            case 'VOIDED':
                $nuevo_estado = 'cancelado';
                break;
            case 'PENDING':
                $nuevo_estado = 'pendiente';
                break;
        }
        
        if ($nuevo_estado) {
            $stmt_update = $conn->prepare("UPDATE orden SET estado = ? WHERE id_orden = ?");
            $stmt_update->bind_param("si", $nuevo_estado, $id_orden);
            $stmt_update->execute();
            
            // 🔄 MARCAR RELOJ COMO VENDIDO si el pago fue aprobado (Sincronización automática)
            if ($nuevo_estado === 'pagado') {
                $stmt_reloj = $conn->prepare("UPDATE reloj r 
                                               INNER JOIN orden_detalle od ON r.id_reloj = od.id_reloj 
                                               SET r.vendido = 1 
                                               WHERE od.id_orden = ?");
                $stmt_reloj->bind_param("i", $id_orden);
                $stmt_reloj->execute();
                $stmt_reloj->close();
                
                // 🎟️ VINCULAR ORDEN CON CÓDIGO DE DESCUENTO (si se usó uno)
                // NOTA: El código ya fue marcado como usado cuando se aplicó
                $stmt_codigo_check = $conn->prepare("
                    SELECT ucd.id_usuario, ucd.id_codigo 
                    FROM usuario_codigo_descuento ucd
                    JOIN orden_detalle od ON ucd.id_reloj = od.id_reloj
                    WHERE od.id_orden = ? 
                      AND ucd.id_orden IS NULL
                      AND ucd.activo = 0
                    LIMIT 1
                ");
                $stmt_codigo_check->bind_param("i", $id_orden);
                $stmt_codigo_check->execute();
                $result_codigo = $stmt_codigo_check->get_result();
                
                if ($result_codigo->num_rows > 0) {
                    $codigo_data = $result_codigo->fetch_assoc();
                    $id_usuario_codigo = $codigo_data['id_usuario'];
                    $id_codigo = $codigo_data['id_codigo'];
                    
                    // Solo actualizar el id_orden para tener referencia
                    $stmt_update_codigo = $conn->prepare("
                        UPDATE usuario_codigo_descuento 
                        SET id_orden = ?
                        WHERE id_usuario = ? 
                          AND id_codigo = ?
                    ");
                    $stmt_update_codigo->bind_param("iii", $id_orden, $id_usuario_codigo, $id_codigo);
                    $stmt_update_codigo->execute();
                    $stmt_update_codigo->close();
                    
                    wompi_log("Orden vinculada con código de descuento #$id_orden");
                }
                $stmt_codigo_check->close();
            }
            
            wompi_log("Orden $id_orden actualizada a estado: $nuevo_estado");
        }
    }
}

/**
 * Procesar transacción aprobada
 */
function procesarTransaccionAprobada($transactionData, $conn) {
    wompi_log("Transacción aprobada: " . json_encode($transactionData));
    procesarActualizacionTransaccion($transactionData, $conn);
    
    $reference = $transactionData['reference'];
    
    // Obtener datos completos de la orden
    $stmt = $conn->prepare("
        SELECT o.*, 
               r.nombre as nombre_reloj,
               r.marca
        FROM orden o
        LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
        LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
        WHERE o.token_verificacion = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        wompi_log("❌ Orden no encontrada con token: $reference");
        return;
    }
    
    $orden = $result->fetch_assoc();
    $id_orden = $orden['id_orden'];
    $id_usuario = $orden['id_usuario'];
    $correo = $orden['correo'];
    $nombre_cliente = $orden['nombre'];
    
    wompi_log("Procesando orden #$id_orden - Usuario: " . ($id_usuario ?: 'NULL (anónimo)'));
    
    // ========================================
    // 1. GENERAR CÓDIGO DE DESCUENTO (si hay sesión)
    // ========================================
    $codigo_generado = null;
    
    if ($id_usuario) {
        try {
            // Crear código único
            $codigo = 'FIN' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
            $porcentaje = 10.00;
            $fecha_expiracion = date('Y-m-d', strtotime('+30 days'));
            
            // Insertar código en tabla codigo_descuento
            $stmt_codigo = $conn->prepare("
                INSERT INTO codigo_descuento (codigo, porcentaje, fecha_expiracion, activo)
                VALUES (?, ?, ?, 1)
            ");
            $stmt_codigo->bind_param("sds", $codigo, $porcentaje, $fecha_expiracion);
            $stmt_codigo->execute();
            $id_codigo = $conn->insert_id;
            $stmt_codigo->close();
            
            // Asignar código al usuario
            $notas = "Código de agradecimiento por tu compra #$id_orden 🎉";
            $stmt_asignar = $conn->prepare("
                INSERT INTO usuario_codigo_descuento 
                (id_usuario, id_codigo, fecha_asignado, id_orden, veces_usado, activo, notas)
                VALUES (?, ?, NOW(), ?, 0, 1, ?)
            ");
            $stmt_asignar->bind_param("iiis", $id_usuario, $id_codigo, $id_orden, $notas);
            $stmt_asignar->execute();
            $stmt_asignar->close();
            
            $codigo_generado = [
                'codigo' => $codigo,
                'porcentaje' => $porcentaje,
                'fecha_expiracion' => $fecha_expiracion
            ];
            
            wompi_log("Código generado: $codigo para usuario #$id_usuario");
            
        } catch (Exception $e) {
            wompi_log("Error al generar código: " . $e->getMessage());
        }
    } else {
        wompi_log("No se genera código (orden anónima)");
    }
    
    // ========================================
    // 2. ENVIAR EMAIL DE CONFIRMACIÓN
    // ========================================
    try {
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'davidpascuas708@gmail.com';
        $mail->Password = 'qinc wznz hvmv zqwu';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        $mail->setFrom('davidpascuas708@gmail.com', 'FINOSO');
        $mail->addAddress($correo, $nombre_cliente);
        
        // Contenido del email
        if ($codigo_generado) {
            // Email CON código (usuario registrado)
            $mail->Subject = '¡Pago Aprobado! Tu Código de Descuento - FINOSO';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px;'>
                    <div style='background: linear-gradient(135deg, #FFCF66 0%, #FFB84D 100%); padding: 30px; text-align: center; border-radius: 12px 12px 0 0;'>
                        <h1 style='color: #000; margin: 0; font-size: 28px;'>¡Pago Aprobado! 🎉</h1>
                    </div>
                    
                    <div style='background: #fff; padding: 30px; border-radius: 0 0 12px 12px;'>
                        <p style='font-size: 16px; color: #333;'>Hola <strong>$nombre_cliente</strong>,</p>
                        
                        <p style='font-size: 16px; color: #333;'>¡Excelente noticia! Tu pago ha sido procesado exitosamente.</p>
                        
                        <div style='background: #f0f0f0; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                            <p style='margin: 5px 0; color: #555;'><strong>📦 Orden:</strong> #$id_orden</p>
                            <p style='margin: 5px 0; color: #555;'><strong>🕐 Reloj:</strong> {$orden['nombre_reloj']}</p>
                            <p style='margin: 5px 0; color: #555;'><strong>💰 Total:</strong> $" . number_format($orden['total'], 0, ',', '.') . "</p>
                            <p style='margin: 5px 0; color: #555;'><strong>💳 Método:</strong> Wompi (Tarjeta/PSE)</p>
                        </div>
                        
                        <div style='background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); padding: 25px; border-radius: 12px; text-align: center; margin: 30px 0;'>
                            <h2 style='color: #000; margin: 0 0 15px 0; font-size: 22px;'>🎁 Tu Código de Descuento</h2>
                            <div style='background: #000; color: #FFD700; padding: 15px; border-radius: 8px; font-size: 24px; font-weight: bold; letter-spacing: 3px; font-family: monospace;'>
                                {$codigo_generado['codigo']}
                            </div>
                            <p style='color: #000; margin: 15px 0 5px 0; font-size: 18px;'><strong>{$codigo_generado['porcentaje']}% de descuento</strong></p>
                            <p style='color: #333; margin: 0; font-size: 14px;'>Válido hasta: {$codigo_generado['fecha_expiracion']}</p>
                        </div>
                        
                        <div style='background: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin: 20px 0;'>
                            <h3 style='color: #2e7d32; margin: 0 0 10px 0; font-size: 18px;'>📋 Próximos Pasos</h3>
                            <ol style='color: #555; margin: 10px 0; padding-left: 20px;'>
                                <li style='margin-bottom: 8px;'>Tu pedido será preparado en las próximas horas</li>
                                <li style='margin-bottom: 8px;'>Recibirás la guía de envío por correo</li>
                                <li style='margin-bottom: 8px;'>Usa tu código en tu próxima compra</li>
                            </ol>
                        </div>
                        
                        <p style='text-align: center; margin-top: 30px;'>
                            <a href='https://finoso.store/perfil/perfil.html' style='background: linear-gradient(135deg, #FFCF66 0%, #FFB84D 100%); color: #000; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>Ver Mi Perfil</a>
                        </p>
                        
                        <p style='color: #999; font-size: 12px; text-align: center; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;'>
                            FINOSO - Relojes de Lujo<br>
                            Token de verificación: {$orden['token_verificacion']}
                        </p>
                    </div>
                </div>
            ";
        } else {
            // Email SIN código (usuario anónimo)
            $mail->Subject = '¡Pago Aprobado! - FINOSO';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px;'>
                    <div style='background: linear-gradient(135deg, #FFCF66 0%, #FFB84D 100%); padding: 30px; text-align: center; border-radius: 12px 12px 0 0;'>
                        <h1 style='color: #000; margin: 0; font-size: 28px;'>¡Pago Aprobado! 🎉</h1>
                    </div>
                    
                    <div style='background: #fff; padding: 30px; border-radius: 0 0 12px 12px;'>
                        <p style='font-size: 16px; color: #333;'>Hola <strong>$nombre_cliente</strong>,</p>
                        
                        <p style='font-size: 16px; color: #333;'>¡Excelente noticia! Tu pago ha sido procesado exitosamente.</p>
                        
                        <div style='background: #f0f0f0; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                            <p style='margin: 5px 0; color: #555;'><strong>📦 Orden:</strong> #$id_orden</p>
                            <p style='margin: 5px 0; color: #555;'><strong>🕐 Reloj:</strong> {$orden['nombre_reloj']}</p>
                            <p style='margin: 5px 0; color: #555;'><strong>💰 Total:</strong> $" . number_format($orden['total'], 0, ',', '.') . "</p>
                            <p style='margin: 5px 0; color: #555;'><strong>💳 Método:</strong> Wompi (Tarjeta/PSE)</p>
                        </div>
                        
                        <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;'>
                            <h3 style='color: #856404; margin: 0 0 10px 0; font-size: 18px;'>💡 ¿Sabías que?</h3>
                            <p style='color: #856404; margin: 0;'>Al crear una cuenta, recibes <strong>códigos de descuento</strong> en cada compra. ¡Regístrate ahora!</p>
                            <p style='text-align: center; margin-top: 15px;'>
                                <a href='https://finoso.store/login/registrarse/registrarse.html' style='background: #ffc107; color: #000; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>Crear Cuenta Gratis</a>
                            </p>
                        </div>
                        
                        <div style='background: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin: 20px 0;'>
                            <h3 style='color: #2e7d32; margin: 0 0 10px 0; font-size: 18px;'>📋 Próximos Pasos</h3>
                            <ol style='color: #555; margin: 10px 0; padding-left: 20px;'>
                                <li style='margin-bottom: 8px;'>Tu pedido será preparado en las próximas horas</li>
                                <li style='margin-bottom: 8px;'>Recibirás la guía de envío por correo</li>
                                <li style='margin-bottom: 8px;'>Podrás rastrear tu pedido</li>
                            </ol>
                        </div>
                        
                        <p style='color: #999; font-size: 12px; text-align: center; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;'>
                            FINOSO - Relojes de Lujo<br>
                            Token de verificación: {$orden['token_verificacion']}
                        </p>
                    </div>
                </div>
            ";
        }
        
        $mail->isHTML(true);
        $mail->send();
        
        wompi_log("Email enviado a: $correo");
        
    } catch (Exception $e) {
        wompi_log("Error al enviar email: " . $e->getMessage());
    }
    
    // ========================================
    // 3. ENVIAR WHATSAPP
    // ========================================
    try {
        require_once __DIR__ . '/../../includes/WhatsAppNotificacion.php';
        require_once __DIR__ . '/../../includes/WhatsAppTemplates.php';
        require_once __DIR__ . '/../../config/twilio_config.php';
        
        if (verificarConfiguracionTwilio()) {
            $whatsapp = new WhatsAppNotificacion();
            
            // Mensaje al cliente
            $datosCliente = [
                'orden_id' => $id_orden,
                'nombre_reloj' => $orden['nombre_reloj'] ?: 'tu reloj',
                'total' => $orden['total'],
                'nombre_cliente' => $nombre_cliente
            ];
            
            if ($codigo_generado) {
                $datosCliente['codigo_descuento'] = $codigo_generado['codigo'];
                $datosCliente['porcentaje_descuento'] = $codigo_generado['porcentaje'];
            }
            
            $mensajeCliente = WhatsAppTemplates::compraExitosa($datosCliente);
            $whatsapp->enviarMensaje($orden['celular'], $mensajeCliente, 'compra_exitosa_wompi');
            
            // Mensaje al admin
            $datosAdmin = [
                'orden_id' => $id_orden,
                'nombre_cliente' => $nombre_cliente,
                'telefono' => $orden['celular'],
                'email' => $correo,
                'nombre_reloj' => $orden['nombre_reloj'] ?: 'Reloj',
                'total' => $orden['total'],
                'metodo_pago' => 'Wompi'
            ];
            $mensajeAdmin = WhatsAppTemplates::nuevaOrdenAdmin($datosAdmin);
            $whatsapp->enviarMensaje(ADMIN_WHATSAPP, $mensajeAdmin, 'nueva_orden_wompi');
            
            wompi_log("WhatsApp enviado para orden #$id_orden");
        }
    } catch (Exception $e) {
        wompi_log("Error al enviar WhatsApp: " . $e->getMessage());
    }
    
    wompi_log("Orden #$id_orden procesada completamente");
}

/**
 * Procesar transacción declinada
 */
function procesarTransaccionDeclinada($transactionData, $conn) {
    wompi_log("Transacción declinada: " . json_encode($transactionData));
    procesarActualizacionTransaccion($transactionData, $conn);
}
?>
