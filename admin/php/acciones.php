<?php
// Configurar headers para CORS y JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Cargar autoloader y dependencias
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// Iniciar sesión
session_start();

try {
    // Verificar que el usuario esté logueado y sea administrador
    if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
        throw new Exception('Acceso denegado: se requiere permisos de administrador');
    }

    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Incluir conexión a la base de datos
    include '../../login/php/conexion.php';
    
    // Incluir sistema de logs
    require_once __DIR__ . '/logger.php';

    // Verificar conexión
    if ($conn->connect_error) {
        throw new Exception('Error de conexión a la base de datos: ' . $conn->connect_error);
    }

    $action = $_POST['action'] ?? '';
    $id_orden = (int)($_POST['id_orden'] ?? 0);

    if ($id_orden <= 0) {
        throw new Exception('ID de orden inválido');
    }

    switch ($action) {
        case 'aprobar':
            // Aprobar orden y eliminar reloj de la tabla (marcar como vendido)
            $conn->begin_transaction();
            
            try {
                // 1. Aprobar la orden (cambiar estado a 'pagado')
                $stmt = $conn->prepare("UPDATE orden SET estado = 'pagado', fecha_aprobacion = NOW() WHERE id_orden = ? AND estado IN ('pendiente', 'pendiente_verificacion')");
                $stmt->bind_param("i", $id_orden);
                $stmt->execute();
                $stmt->close();
                
                // 2. Obtener el ID del reloj de la orden
                $stmt = $conn->prepare("SELECT id_reloj FROM orden_detalle WHERE id_orden = ?");
                $stmt->bind_param("i", $id_orden);
                $stmt->execute();
                $result = $stmt->get_result();
                $reloj_data = $result->fetch_assoc();
                $stmt->close();
                
                if ($reloj_data) {
                    // 3. Marcar el reloj como vendido (en lugar de eliminarlo)
                    $stmt = $conn->prepare("UPDATE reloj SET vendido = 1 WHERE id_reloj = ?");
                    $stmt->bind_param("i", $reloj_data['id_reloj']);
                    $stmt->execute();
                    $stmt->close();
                }

                // 4. Enviar correo de confirmación al cliente
                // Obtener datos del cliente y totales (correo desde tabla usuario e id_usuario para asignar código)
                $stmt = $conn->prepare("SELECT o.id_usuario, o.nombre, COALESCE(o.correo, u.correo) AS correo, o.costo_envio, d.precio_unitario AS precio_producto
                                         FROM orden o
                                         JOIN orden_detalle d ON d.id_orden = o.id_orden
                                         LEFT JOIN usuario u ON u.id_usuario = o.id_usuario
                                         WHERE o.id_orden = ?");
                $stmt->bind_param("i", $id_orden);
                $stmt->execute();
                $ordenInfo = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                escribir_log('[APROBAR] Query de ordenInfo ejecutada - id_usuario obtenido: ' . ($ordenInfo['id_usuario'] ?? 'NULL'), 'DEBUG');

                if ($ordenInfo && !empty($ordenInfo['correo'])) {
                    $mail = new PHPMailer(true);
                    try {
                        error_log('[APROBAR] Preparando envío de correo a ' . $ordenInfo['correo']);
                        // SMTP + debug al error_log
                        $mail->SMTPDebug = 2;
                        $mail->Debugoutput = function($str, $level) { error_log("[SMTP:$level] $str"); };
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'davidpascuas708@gmail.com';
                        $mail->Password = 'qinc wznz hvmv zqwu';
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;
                        $mail->CharSet = 'UTF-8';

                        $mail->setFrom('davidpascuas708@gmail.com', 'Finoso');
                        $mail->addAddress($ordenInfo['correo'], $ordenInfo['nombre']);
                        $mail->isHTML(true);

                        // Verificar si es compra anónima (desde favoritos sin sesión)
                        $id_usuario_orden = isset($ordenInfo['id_usuario']) ? $ordenInfo['id_usuario'] : null;
                        $es_compra_anonima = !$id_usuario_orden || $id_usuario_orden <= 0;
                        
                        log_separador('INICIO GENERACIÓN DE CÓDIGO');
                        escribir_log('[APROBAR] ID Orden: ' . $id_orden, 'INFO');
                        escribir_log('[APROBAR] ID Usuario de la orden: ' . ($id_usuario_orden ? $id_usuario_orden : 'NULL'), 'INFO');
                        escribir_log('[APROBAR] Es compra anónima: ' . ($es_compra_anonima ? 'SÍ' : 'NO'), 'INFO');

                        if ($es_compra_anonima) {
                            // Usuario anónimo - NO generar código
                            escribir_log('[APROBAR] ⚠ COMPRA ANÓNIMA DETECTADA - NO se generará código de descuento', 'WARNING');
                            escribir_log('[APROBAR] Se enviará correo de agradecimiento simple sin código', 'INFO');
                            
                            $mail->Subject = '¡Gracias por tu compra en Finoso! Pedido confirmado';
                            
                            $precio = (float)$ordenInfo['precio_producto'];
                            $envio = (float)$ordenInfo['costo_envio'];
                            $total = $precio + $envio;
                            $precioFmt = '$' . number_format($precio, 0, ',', '.');
                            $envioFmt = '$' . number_format($envio, 0, ',', '.');
                            $totalFmt = '$' . number_format($total, 0, ',', '.');

                            $mail->Body = '<div style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 30px; border-radius: 10px; color: #333;">'
                                         . '<div style="max-width: 600px; margin: auto; background-color: white; border-radius: 8px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">'
                                         . '<h2 style="color: #333; text-align: center;">🤝 ¡Gracias por confiar en Finoso!</h2>'
                                         . '<p style="font-size: 16px; line-height: 1.6;">Tu pago fue <strong>aprobado</strong> y tu pedido quedó confirmado.</p>'
                                         . '<p><strong>Resumen:</strong><br>Precio: ' . $precioFmt . '<br>Envío: ' . $envioFmt . '<br>Total: ' . $totalFmt . '</p>'
                                         . '<div style="text-align: center; margin: 30px 0; background: #f9f9f9; padding: 20px; border-radius: 8px;">'
                                         . '<p style="font-size: 16px; color: #555; margin: 0;">✨ <strong>¿Quieres descuentos exclusivos en tu próxima compra?</strong> ✨</p>'
                                         . '<p style="font-size: 14px; color: #888; margin: 10px 0 0 0;">Regístrate en nuestra página y recibe códigos de descuento especiales con cada compra.</p>'
                                         . '</div>'
                                         . '<hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">'
                                         . '<p style="font-size: 14px; text-align: center; color: #666;">💼 Somos Finoso. Un detalle habla más que mil palabras.<br>Gracias por ser parte de esta experiencia.</p>'
                                         . '</div></div>';
                        } else {
                            // Usuario registrado - Generar código
                            escribir_log('[APROBAR] ✓ Usuario registrado detectado - Generando código de descuento', 'INFO');
                            $mail->Subject = '¡Gracias por tu compra en Finoso! Confirmación de pedido';
                            
                            $codigo_descuento = 'FIN' . strtoupper(substr(uniqid(), -6)); // Código único
                            $porcentaje = 10;
                            $fecha_expiracion = date('Y-m-d', strtotime('+90 days'));
                            
                            escribir_log('[APROBAR] Código generado: ' . $codigo_descuento, 'INFO');
                            escribir_log('[APROBAR] Porcentaje: ' . $porcentaje . '%', 'INFO');
                            escribir_log('[APROBAR] Fecha expiración: ' . $fecha_expiracion, 'INFO');
                            
                            // Insertar en tabla codigo_descuento
                            $stmtCd = $conn->prepare("INSERT INTO codigo_descuento (codigo, porcentaje, fecha_expiracion) VALUES (?, ?, ?)");
                            if ($stmtCd) { 
                                $stmtCd->bind_param("sds", $codigo_descuento, $porcentaje, $fecha_expiracion); 
                                
                                if ($stmtCd->execute()) {
                                    $id_codigo_nuevo = $stmtCd->insert_id;
                                    escribir_log('[APROBAR] ✓ Código insertado en codigo_descuento - ID: ' . $id_codigo_nuevo, 'SUCCESS');
                                    $stmtCd->close();
                                    
                                    // Asignar el código al usuario
                                    escribir_log('[APROBAR] Intentando asignar código al usuario...', 'INFO');
                                    
                                    $nota_codigo = 'Código de agradecimiento por tu compra #' . $id_orden . ' 🎉';
                                    $stmtAsignar = $conn->prepare("INSERT INTO usuario_codigo_descuento (id_usuario, id_codigo, id_orden, notas) VALUES (?, ?, ?, ?)");
                                    
                                    if ($stmtAsignar) {
                                        escribir_log('[APROBAR] Query preparada OK', 'INFO');
                                        escribir_log('[APROBAR] Params: id_usuario=' . $id_usuario_orden . ', id_codigo=' . $id_codigo_nuevo . ', id_orden=' . $id_orden . ', notas=' . $nota_codigo, 'DEBUG');
                                        
                                        $stmtAsignar->bind_param("iiis", $id_usuario_orden, $id_codigo_nuevo, $id_orden, $nota_codigo);
                                        
                                        if ($stmtAsignar->execute()) {
                                            $id_asignacion = $stmtAsignar->insert_id;
                                            escribir_log('[APROBAR] ✓✓✓ CÓDIGO ASIGNADO EXITOSAMENTE ✓✓✓', 'SUCCESS');
                                            escribir_log('[APROBAR] ID Asignación: ' . $id_asignacion, 'SUCCESS');
                                            escribir_log('[APROBAR] Código: ' . $codigo_descuento . ' → Usuario: ' . $id_usuario_orden, 'SUCCESS');
                                        } else {
                                            escribir_log('[APROBAR] ✗✗✗ ERROR al asignar código ✗✗✗', 'ERROR');
                                            escribir_log('[APROBAR] Error MySQL: ' . $stmtAsignar->error, 'ERROR');
                                            escribir_log('[APROBAR] Error Número: ' . $stmtAsignar->errno, 'ERROR');
                                        }
                                        $stmtAsignar->close();
                                    } else {
                                        escribir_log('[APROBAR] ✗ Error al preparar query de asignación: ' . $conn->error, 'ERROR');
                                    }
                                } else {
                                    escribir_log('[APROBAR] ✗ Error al insertar código en BD: ' . $stmtCd->error, 'ERROR');
                                }
                            } else {
                                escribir_log('[APROBAR] ✗ Error al preparar query de código: ' . $conn->error, 'ERROR');
                            }

                            $precio = (float)$ordenInfo['precio_producto'];
                            $envio = (float)$ordenInfo['costo_envio'];
                            $total = $precio + $envio;
                            $precioFmt = '$' . number_format($precio, 0, ',', '.');
                            $envioFmt = '$' . number_format($envio, 0, ',', '.');
                            $totalFmt = '$' . number_format($total, 0, ',', '.');
                            $fecha_expiracion_formateada = date('d/m/Y', strtotime($fecha_expiracion));

                            $mail->Body = '<div style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 30px; border-radius: 10px; color: #333;">'
                                         . '<div style="max-width: 600px; margin: auto; background-color: white; border-radius: 8px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">'
                                         . '<h2 style="color: #333; text-align: center;">🤝 ¡Gracias por confiar en Finoso!</h2>'
                                         . '<p style="font-size: 16px; line-height: 1.6;">Tu pago fue <strong>aprobado</strong> y tu pedido quedó confirmado.</p>'
                                         . '<p><strong>Resumen:</strong><br>Precio: ' . $precioFmt . '<br>Envío: ' . $envioFmt . '<br>Total: ' . $totalFmt . '</p>'
                                         . '<p style="font-size: 16px; line-height: 1.6;">Como agradecimiento, te obsequiamos un código exclusivo del <strong>' . $porcentaje . '%</strong> para tu próxima compra.</p>'
                                         . '<div style="text-align: center; margin: 30px 0;"><span style="display: inline-block; font-size: 24px; background: #000; color: #fff; padding: 15px 25px; border-radius: 5px; letter-spacing: 4px;">' . $codigo_descuento . '</span></div>'
                                         . '<p style="text-align: center; font-size: 14px; color: #888;">Válido hasta el <strong>' . $fecha_expiracion_formateada . '</strong></p>'
                                         . '</div></div>';
                        }
                        
                        log_separador('FIN GENERACIÓN DE CÓDIGO');

                        $mail->send();
                        error_log('[APROBAR] Correo enviado correctamente');
                    } catch (Exception $e) {
                        error_log('PHPMailer exception: ' . $e->getMessage());
                    }
                } else {
                    error_log('[APROBAR] No hay correo asociado a la orden');
                }

                $conn->commit();
                
                // 📱 ENVIAR NOTIFICACIÓN WHATSAPP - Pago Aprobado
                try {
                    require_once __DIR__ . '/../../includes/WhatsAppNotificacion.php';
                    require_once __DIR__ . '/../../includes/WhatsAppTemplates.php';
                    require_once __DIR__ . '/../../config/twilio_config.php';
                    
                    if (verificarConfiguracionTwilio()) {
                        // Obtener datos completos de la orden para WhatsApp
                        $stmt = $conn->prepare("SELECT o.*, r.nombre as nombre_reloj 
                                                FROM orden o
                                                LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
                                                LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
                                                WHERE o.id_orden = ?");
                        $stmt->bind_param("i", $id_orden);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            $orden = $result->fetch_assoc();
                            $whatsapp = new WhatsAppNotificacion();
                            
                            // Enviar notificación al cliente
                            $datosWhatsApp = [
                                'orden_id' => $id_orden,
                                'nombre_reloj' => $orden['nombre_reloj'] ?: 'tu reloj',
                                'nombre_cliente' => $orden['nombre']
                            ];
                            $mensaje = WhatsAppTemplates::pagoAprobado($datosWhatsApp);
                            $whatsapp->enviarMensaje($orden['celular'], $mensaje, 'pago_aprobado_admin');
                            
                            error_log("WhatsApp enviado para orden #{$id_orden} - Pago Aprobado");
                        }
                        $stmt->close();
                    }
                } catch (Exception $e) {
                    error_log("Error al enviar WhatsApp: " . $e->getMessage());
                }
                
                echo json_encode(['success' => true, 'message' => 'Orden aprobada, reloj marcado como vendido']);
                
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;

        case 'rechazar':
            $motivo = $_POST['motivo'] ?? '';
            $monto_pagado = isset($_POST['monto_pagado']) ? floatval($_POST['monto_pagado']) : 0;
            
            if (empty($motivo)) {
                throw new Exception('Debe proporcionar un motivo para el rechazo');
            }

            // Rechazar orden y guardar monto_pagado si aplica
            if ($monto_pagado > 0) {
                // Si es problema de monto, guardar cuánto pagó realmente
                $stmt = $conn->prepare("UPDATE orden SET estado = 'rechazado', motivo_rechazo = ?, monto_pagado = ?, fecha_aprobacion = NOW() WHERE id_orden = ? AND estado IN ('pendiente', 'pendiente_verificacion')");
                $stmt->bind_param("sdi", $motivo, $monto_pagado, $id_orden);
            } else {
                // Rechazo normal sin problema de monto
                $stmt = $conn->prepare("UPDATE orden SET estado = 'rechazado', motivo_rechazo = ?, fecha_aprobacion = NOW() WHERE id_orden = ? AND estado IN ('pendiente', 'pendiente_verificacion')");
                $stmt->bind_param("si", $motivo, $id_orden);
            }
            
            if (!$stmt->execute()) {
                throw new Exception('Error al rechazar la orden: ' . $conn->error);
            }
            // Guardar filas afectadas ANTES de ejecutar otros comandos
            $updated = $stmt->affected_rows;
            $stmt->close();

            if ($updated > 0) {
                // Obtener datos para notificación por correo
                $stmt = $conn->prepare("SELECT o.nombre, COALESCE(o.correo, u.correo) AS correo, o.token_verificacion
                                         FROM orden o
                                         LEFT JOIN usuario u ON u.id_usuario = o.id_usuario
                                         WHERE o.id_orden = ?");
                $stmt->bind_param("i", $id_orden);
                $stmt->execute();
                $info = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($info && !empty($info['correo'])) {
                    $mail = new PHPMailer(true);
                    try {
                        error_log('[RECHAZAR] Enviando correo a ' . $info['correo']);
                        $mail->SMTPDebug = 2;
                        $mail->Debugoutput = function($str, $level) { error_log("[SMTP:$level] $str"); };
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'davidpascuas708@gmail.com';
                        $mail->Password = 'qinc wznz hvmv zqwu';
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;
                        $mail->CharSet = 'UTF-8';

                        $mail->setFrom('davidpascuas708@gmail.com', 'Finoso');
                        $mail->addAddress($info['correo'], $info['nombre']);
                        $mail->isHTML(true);
                        $mail->Subject = 'Actualización de tu pedido - Comprobante no aprobado';

                        $tokenShort = $info['token_verificacion'] ? substr($info['token_verificacion'], 0, 16) . '…' : '-';
                        
                        // Construir el cuerpo del correo dependiendo si puede recuperar o no
                        if ($monto_pagado > 0) {
                            // CASO 1: Puede recuperar el pago (hay monto_pagado)
                            // Obtener datos completos de la orden
                            $stmt_orden = $conn->prepare("SELECT o.total, o.token_verificacion FROM orden o WHERE o.id_orden = ?");
                            $stmt_orden->bind_param("i", $id_orden);
                            $stmt_orden->execute();
                            $orden_correo = $stmt_orden->get_result()->fetch_assoc();
                            $stmt_orden->close();
                            
                            $total = $orden_correo['total'];
                            $diferencia = $total - $monto_pagado;
                            $token_orden = $orden_correo['token_verificacion'];
                            $urlRecuperacion = "https://finoso.store/informacion/recuperar_pago.html?orden={$id_orden}&token={$token_orden}";
                            
                            $body = '<div style="font-family: Arial, sans-serif; color:#222; max-width: 600px; margin: 0 auto;">'
                                  . '<div style="background: linear-gradient(135deg, #FFCF66 0%, #FFB84D 100%); padding: 20px; border-radius: 12px 12px 0 0;">'
                                  . '<h2 style="color: #000; margin: 0; text-align: center;">❌ Orden Rechazada</h2>'
                                  . '</div>'
                                  . '<div style="background: #fff; padding: 30px; border: 2px solid #FFCF66; border-top: none; border-radius: 0 0 12px 12px;">'
                                  . '<p style="font-size: 16px;">Hola <strong>' . htmlspecialchars($info['nombre'] ?? 'cliente') . '</strong>,</p>'
                                  . '<p>Tu comprobante de pago <strong>no fue aprobado</strong> por el siguiente motivo:</p>'
                                  . '<div style="background:#fff3cd; border-left: 4px solid #FFA500; padding: 15px; margin: 20px 0; border-radius: 6px;">'
                                  . '<strong>📋 ' . htmlspecialchars($motivo) . '</strong>'
                                  . '</div>'
                                  
                                  . '<div style="background: linear-gradient(135deg, rgba(76, 175, 80, 0.1) 0%, rgba(76, 175, 80, 0.05) 100%); border: 2px solid #4CAF50; padding: 20px; margin: 25px 0; border-radius: 12px; text-align: center;">'
                                  . '<h3 style="color: #4CAF50; margin-top: 0;">💡 ¡PUEDES SALVAR TU ORDEN!</h3>'
                                  . '<table style="width: 100%; margin: 15px 0; border-collapse: collapse;">'
                                  . '<tr style="border-bottom: 1px solid #ddd;"><td style="padding: 10px; text-align: left;">Total del pedido:</td><td style="padding: 10px; text-align: right; font-weight: bold;">$' . number_format($total, 0, ',', '.') . '</td></tr>'
                                  . '<tr style="border-bottom: 1px solid #ddd; color: #4CAF50;"><td style="padding: 10px; text-align: left;">✅ Ya pagaste:</td><td style="padding: 10px; text-align: right; font-weight: bold;">$' . number_format($monto_pagado, 0, ',', '.') . '</td></tr>'
                                  . '<tr style="color: #F44336;"><td style="padding: 15px 10px; text-align: left; font-size: 18px; font-weight: bold;">❌ Falta pagar:</td><td style="padding: 15px 10px; text-align: right; font-size: 22px; font-weight: bold;">$' . number_format($diferencia, 0, ',', '.') . '</td></tr>'
                                  . '</table>'
                                  . '<a href="' . $urlRecuperacion . '" style="display: inline-block; background: linear-gradient(135deg, #FFCF66 0%, #FFB84D 100%); color: #000; padding: 15px 40px; text-decoration: none; border-radius: 25px; font-weight: bold; font-size: 16px; margin: 20px 0; box-shadow: 0 4px 15px rgba(255, 207, 102, 0.4);">🎯 COMPLETAR MI PAGO AHORA</a>'
                                  . '<p style="font-size: 14px; color: #666; margin-top: 15px;">📱 Paga solo la diferencia y recupera tu orden<br>⏰ Link válido por 48 horas</p>'
                                  . '</div>'
                                  
                                  . '<p style="margin-top: 30px;"><strong>💬 ¿Necesitas ayuda?</strong></p>'
                                  . '<p>Responde este correo y te asistiremos. Incluye tu <strong>token</strong>: ' . $tokenShort . '</p>'
                                  . '<hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">'
                                  . '<p style="font-size: 12px; color: #999; text-align: center;">FINOSO - Relojes de Lujo<br>Si tienes problemas con el botón, copia este enlace:<br><a href="' . $urlRecuperacion . '" style="color: #FFCF66; word-break: break-all;">' . $urlRecuperacion . '</a></p>'
                                  . '</div>'
                                  . '</div>';
                        } else {
                            // CASO 2: No puede recuperar (rechazo genérico)
                            $body = '<div style="font-family: Arial, sans-serif; color:#222;">'
                                  . '<p>Hola ' . htmlspecialchars($info['nombre'] ?? 'cliente') . ',</p>'
                                  . '<p>Tu comprobante de pago <strong>no fue aprobado</strong> por el siguiente motivo:</p>'
                                  . '<p style="background:#fff3cd; border:1px solid #ffeeba; padding:12px; border-radius:6px;"><strong>' . htmlspecialchars($motivo) . '</strong></p>'
                                  . '<p><strong>¿Qué puedes hacer?</strong></p>'
                                  . '<ul>'
                                  . '<li>Revisa que el <strong>monto</strong> y los <strong>datos</strong> coincidan exactamente con tu pedido.</li>'
                                  . '<li>Envía un nuevo comprobante <strong>legible</strong> y con la información completa.</li>'
                                  . '<li>Responde este correo si necesitas ayuda. Incluye tu <strong>token</strong>: ' . $tokenShort . '.</li>'
                                  . '</ul>'
                                  . '<p>Estamos para ayudarte.</p>'
                                  . '</div>';
                        }
                        
                        $mail->Body = $body;
                        $mail->send();
                        error_log('[RECHAZAR] Correo de rechazo enviado');
                    } catch (Exception $e) {
                        error_log('PHPMailer RECHAZAR exception: ' . $e->getMessage());
                    }
                } else {
                    error_log('[RECHAZAR] Orden sin correo asociado para notificar');
                }
                
                // 📱 ENVIAR NOTIFICACIÓN WHATSAPP AL CLIENTE
                try {
                    require_once __DIR__ . '/../../includes/WhatsAppNotificacion.php';
                    require_once __DIR__ . '/../../includes/WhatsAppTemplates.php';
                    require_once __DIR__ . '/../../config/twilio_config.php';
                    
                    if (verificarConfiguracionTwilio()) {
                        // Obtener datos completos de la orden para WhatsApp
                        $sql_orden = "SELECT o.*, od.precio_unitario, r.nombre as nombre_reloj
                                      FROM orden o
                                      LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
                                      LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
                                      WHERE o.id_orden = ?";
                        $stmt_orden = $conn->prepare($sql_orden);
                        $stmt_orden->bind_param("i", $id_orden);
                        $stmt_orden->execute();
                        $orden_data = $stmt_orden->get_result()->fetch_assoc();
                        $stmt_orden->close();
                        
                        if ($orden_data && $orden_data['celular']) {
                            $whatsapp = new WhatsAppNotificacion();
                            
                            $datosWhatsApp = [
                                'orden_id' => $id_orden,
                                'nombre_reloj' => $orden_data['nombre_reloj'] ?: 'tu reloj',
                                'total' => $orden_data['total'],
                                'nombre_cliente' => $orden_data['nombre'],
                                'motivo_rechazo' => $motivo
                            ];
                            
                            // Si es problema de monto, agregar datos adicionales y URL de recuperación
                            if ($monto_pagado > 0) {
                                $diferencia = $orden_data['total'] - $monto_pagado;
                                $datosWhatsApp['monto_pagado'] = $monto_pagado;
                                $datosWhatsApp['diferencia'] = $diferencia;
                                $datosWhatsApp['url_recuperacion'] = "https://finoso.store/informacion/recuperar_pago.html?orden={$id_orden}&token={$orden_data['token_verificacion']}";
                            }
                            
                            $mensaje = WhatsAppTemplates::ordenRechazada($datosWhatsApp);
                            $whatsapp->enviarMensaje($orden_data['celular'], $mensaje, 'orden_rechazada');
                            
                            error_log("[RECHAZAR] Notificación WhatsApp enviada a " . $orden_data['celular']);
                        }
                    }
                } catch (Exception $e) {
                    error_log("[RECHAZAR] Error al enviar WhatsApp: " . $e->getMessage());
                    // No fallar la operación si falla WhatsApp
                }

                echo json_encode(['success' => true, 'message' => 'Orden rechazada exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontró la orden o ya fue procesada']);
            }
            break;

        case 'verificar_comprobante':
            // Marcar comprobante como verificado
            $stmt = $conn->prepare("UPDATE orden SET comprobante_verificado = 1 WHERE id_orden = ?");
            $stmt->bind_param("i", $id_orden);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Comprobante verificado exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró la orden']);
                }
            } else {
                throw new Exception('Error al verificar el comprobante: ' . $conn->error);
            }
            $stmt->close();
            break;

        case 'revertir_verificacion':
            // Revertir verificación del comprobante
            $stmt = $conn->prepare("UPDATE orden SET comprobante_verificado = 0 WHERE id_orden = ?");
            $stmt->bind_param("i", $id_orden);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Verificación revertida exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró la orden']);
                }
            } else {
                throw new Exception('Error al revertir la verificación: ' . $conn->error);
            }
            $stmt->close();
            break;

        case 'revertir_aprobacion':
            // Revertir aprobación de la orden
            $conn->begin_transaction();
            
            try {
                // 1. Revertir el estado de la orden
                $stmt = $conn->prepare("UPDATE orden SET estado = 'pendiente_verificacion', fecha_aprobacion = NULL WHERE id_orden = ? AND estado = 'pagado'");
                $stmt->bind_param("i", $id_orden);
                $stmt->execute();
                $stmt->close();
                
                // 2. Obtener el ID del reloj de la orden
                $stmt = $conn->prepare("SELECT id_reloj FROM orden_detalle WHERE id_orden = ?");
                $stmt->bind_param("i", $id_orden);
                $stmt->execute();
                $result = $stmt->get_result();
                $reloj_data = $result->fetch_assoc();
                $stmt->close();
                
                if ($reloj_data) {
                    // 3. Marcar el reloj como disponible nuevamente
                    $stmt = $conn->prepare("UPDATE reloj SET vendido = 0 WHERE id_reloj = ?");
                    $stmt->bind_param("i", $reloj_data['id_reloj']);
                    $stmt->execute();
                    $stmt->close();
                }
                
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Aprobación revertida y reloj disponible nuevamente']);
                
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;

        default:
            throw new Exception('Acción no válida');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>