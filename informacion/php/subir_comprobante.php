<?php
include 'conexion.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '/finoso/vendor/phpmailer/phpmailer/src/Exception.php';
require '/finoso/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '/finoso/vendor/phpmailer/phpmailer/src/SMTP.php';
require __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/enviar_correo_confirmacion.php';
// 🔥 PROTECCIÓN CONTRA REENVÍO DE FORMULARIO (POST-REDIRECT-GET)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya se procesó esta orden, redirigir a la página de confirmación
if (isset($_GET['orden_id']) && isset($_GET['token'])) {
    $orden_id = intval($_GET['orden_id']);
    $token = $_GET['token'];
    
    // Verificar que la orden existe y obtener sus datos
    $stmt = $conn->prepare("SELECT o.*, od.precio_unitario, r.nombre as nombre_reloj 
                            FROM orden o 
                            LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
                            LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
                            WHERE o.id_orden = ? AND o.token_verificacion = ?");
    $stmt->bind_param("is", $orden_id, $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $orden = $result->fetch_assoc();
        // Mostrar página de éxito sin procesar nada
        mostrarPaginaExito($orden);
        exit();
    }
}

// 🔥 VERIFICAR QUE SEA UNA PETICIÓN POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /finoso/');
    exit();
}

// 🔥 VERIFICAR TOKEN DE SESIÓN PARA EVITAR DOBLE ENVÍO
if (isset($_SESSION['ultimo_token_procesado'])) {
    $token_anterior = $_SESSION['ultimo_token_procesado'];
    $tiempo_anterior = $_SESSION['tiempo_ultimo_token'] ?? 0;
    
    // Si el token se procesó hace menos de 10 segundos, es un reenvío
    if ((time() - $tiempo_anterior) < 10) {
        // Redirigir a la página de confirmación de la orden anterior
        if (isset($_SESSION['ultima_orden_id'])) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?orden_id=" . $_SESSION['ultima_orden_id'] . "&token=" . $token_anterior);
            exit();
        }
    }
}

// Temporal: Verificar qué está llegando por POST
file_put_contents("debug_post.txt", print_r($_POST, true));

// Si no hay sesión activa, iniciar una nueva
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$ip_address = $_SERVER['REMOTE_ADDR'];

// Obtener ID de usuario, primero de la sesión y luego de POST si no está en sesión
$id_usuario_sesion = $_SESSION['id_usuario'] ?? null;
$id_usuario_post = $_POST['id_usuario'] ?? null;

$id_usuario = $id_usuario_sesion ? intval($id_usuario_sesion) : ($id_usuario_post ? intval($id_usuario_post) : null);

// Debug: Log de ID de usuario al subir comprobante
error_log('[NEQUI-INDIVIDUAL] id_usuario_sesion: ' . ($id_usuario_sesion ?? 'NULL'));
error_log('[NEQUI-INDIVIDUAL] id_usuario_post: ' . ($id_usuario_post ?? 'NULL'));
error_log('[NEQUI-INDIVIDUAL] id_usuario final: ' . ($id_usuario ?? 'NULL'));

// VALIDACIONES ANTI-ABUSE PREVIAS
// Nota: Las validaciones por IP, correo y cédula se han deshabilitado temporalmente
// debido a que las columnas correspondientes no existen en la tabla 'orden'
// Se pueden reactivar cuando se agreguen estas columnas a la base de datos

// 🔒 VALIDACIONES DE SEGURIDAD ADICIONALES
// 1. Verificar límite de tiempo (máximo 2 horas desde la creación de la orden)
$tiempo_limite = 2 * 60 * 60; // 2 horas en segundos
$timestamp_actual = time();
$timestamp_orden = isset($_POST['timestamp_orden']) ? intval($_POST['timestamp_orden']) : 0;

// Solo validar tiempo si se proporciona un timestamp válido
if ($timestamp_orden > 0 && ($timestamp_actual - $timestamp_orden) > $tiempo_limite) {
    $tiempo_transcurrido = round(($timestamp_actual - $timestamp_orden) / 60); // en minutos
    $tiempo_limite_minutos = round($tiempo_limite / 60);
    die("Error: El tiempo límite para subir el comprobante ha expirado ({$tiempo_transcurrido} minutos transcurridos de {$tiempo_limite_minutos} permitidos). Por favor, inicia una nueva compra.");
}

// 2. Verificar que el monto coincida exactamente
$monto_esperado = isset($_POST['monto_esperado']) ? floatval($_POST['monto_esperado']) : 0;
$monto_tolerancia = 100; // Tolerancia de $100 pesos

// Obtener el total de la orden desde POST
$total = isset($_POST['total']) ? floatval($_POST['total']) : 0;

if ($monto_esperado > 0 && abs($total - $monto_esperado) > $monto_tolerancia) {
    die("Error: El monto del comprobante no coincide con el total de la orden.");
}

// 3. Verificar que no sea un comprobante duplicado (por hash del archivo)
// Nota: Esta validación se deshabilita temporalmente hasta que se agregue la columna hash_archivo
/*
$hash_archivo = hash_file('sha256', $archivo['tmp_name']);

$stmt = $conn->prepare("SELECT COUNT(*) FROM orden WHERE hash_archivo = ? AND estado = 'pagado'");
$stmt->bind_param("s", $hash_archivo);
$stmt->execute();
$stmt->bind_result($comprobante_duplicado);
$stmt->fetch();
$stmt->close();

if ($comprobante_duplicado > 0) {
    die("Error: Este comprobante ya fue utilizado anteriormente.");
}
*/

// 4. Verificar que el archivo no sea sospechoso (tamaño mínimo y máximo)
// Nota: Esta validación se moverá después de definir $archivo
/*
if ($archivo['size'] < 50000 || $archivo['size'] > 5242880) { // Entre 50KB y 5MB
    die("Error: El archivo no cumple con los requisitos de tamaño.");
}

// 5. Verificar que el nombre del archivo no contenga caracteres sospechosos
$nombre_archivo = $archivo['name'];
if (preg_match('/[<>:"|?*]/', $nombre_archivo) || strlen($nombre_archivo) > 255) {
    die("Error: Nombre de archivo inválido.");
}
*/

// 6. Generar token de verificación único para esta transacción
$token_verificacion = bin2hex(random_bytes(32));
$timestamp_verificacion = time();

// Verificar archivo de comprobante
if (!isset($_FILES['comprobante'])) {
    die("Error: No se seleccionó ningún archivo.");
}

$archivo = $_FILES['comprobante'];

// Validar que el archivo se subió correctamente ANTES de procesarlo
if ($archivo['error'] !== UPLOAD_ERR_OK) {
    switch ($archivo['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            die("Error: El archivo es demasiado grande.");
        case UPLOAD_ERR_PARTIAL:
            die("Error: El archivo se subió parcialmente.");
        case UPLOAD_ERR_NO_FILE:
            die("Error: No se seleccionó ningún archivo.");
        default:
            die("Error: Error al subir el archivo.");
    }
}

// Validar que es realmente un archivo subido
if (!is_uploaded_file($archivo['tmp_name'])) {
    die("Error: El archivo no se ha subido correctamente.");
}

// 🔒 VALIDACIONES DE SEGURIDAD ADICIONALES (después de definir $archivo)

// 1. Verificar que el archivo no sea sospechoso (tamaño mínimo y máximo)
// Nota: Comentado temporalmente para pruebas del panel de administración
/*
if ($archivo['size'] < 50000 || $archivo['size'] > 5242880) { // Entre 50KB y 5MB
    die("Error: El archivo no cumple con los requisitos de tamaño.");
}
*/

// 2. Verificar que el nombre del archivo no contenga caracteres sospechosos
$nombre_archivo = $archivo['name'];
if (preg_match('/[<>:"|?*]/', $nombre_archivo) || strlen($nombre_archivo) > 255) {
    die("Error: Nombre de archivo inválido.");
}

$extensionesValidas = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

if (!in_array($extension, $extensionesValidas)) {
    echo "<script>alert('Error: Solo se permiten archivos JPG, PNG, PDF o WebP.'); history.back();</script>";
}

// Nota: Comentado temporalmente para pruebas del panel de administración
/*
if ($archivo['size'] > 5 * 1024 * 1024) {
    echo "<script>alert('El archivo es demasiado grande. Máximo 5MB.'); history.back();</script>";
}

// Validar que el archivo no esté vacío
if ($archivo['size'] < 1024) { // Mínimo 1KB
    echo "<script>alert('El archivo es demasiado pequeño para ser un comprobante válido.'); history.back();</script>";
}
*/

// Generar hash del archivo ANTES de moverlo
$hash_archivo = md5_file($archivo['tmp_name']);

// Verificar si ya existe este comprobante
// Nota: La verificación de comprobantes duplicados se ha deshabilitado temporalmente
// debido a que la columna 'hash_archivo' no existe en la tabla 'orden'
// Se puede reactivar cuando se agregue esta columna a la base de datos

// VALIDACIONES ADICIONALES PARA COMPROBANTES
// Verificar tipo MIME real del archivo (más permisivo)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $archivo['tmp_name']);
finfo_close($finfo);

$mimes_validos = [
    'image/jpeg' => ['jpg', 'jpeg'],
    'image/png' => ['png'],
    'image/webp' => ['webp'],
    'application/pdf' => ['pdf'],
    'image/jpg' => ['jpg', 'jpeg'], // Algunos servidores reportan image/jpg
    'application/octet-stream' => ['jpg', 'jpeg', 'png', 'pdf', 'webp'] // Fallback para archivos no reconocidos
];

$mime_valido = false;
foreach ($mimes_validos as $mime => $extensiones) {
    if ($mime_type === $mime && in_array($extension, $extensiones)) {
        $mime_valido = true;
        break;
    }
}

// Si el MIME no coincide exactamente, pero la extensión es válida, permitir el archivo
if (!$mime_valido) {
    // Verificar solo por extensión como fallback
    $extensiones_validas = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
    if (!in_array($extension, $extensiones_validas)) {
        die("Error: Solo se permiten archivos JPG, PNG, PDF o WebP.");
    }
    // Si la extensión es válida, continuar (puede ser un falso positivo del MIME)
    error_log("Advertencia: MIME type ($mime_type) no coincide con extensión ($extension), pero se permite por extensión válida");
}

// Procesar datos de la orden
$datos_orden_raw = $_POST['datos_orden'] ?? '';
$descuento_porcentaje = isset($_POST['descuento_porcentaje']) ? floatval($_POST['descuento_porcentaje']) : 0;
$data = json_decode($datos_orden_raw, true) ?: [
    'id_reloj' => $_POST['id_reloj'] ?? '',
    'id_usuario' => $id_usuario,
    'nombre' => $_POST['nombre'] ?? '',
    'cedula' => $_POST['cedula'] ?? '',
    'celular' => $_POST['celular'] ?? '',
    'departamento' => $_POST['departamento'] ?? '',
    'ciudad' => $_POST['ciudad'] ?? '',
    'direccion' => $_POST['direccion'] ?? '',
    'barrio' => $_POST['barrio'] ?? '',
    'referencias' => $_POST['referencias'] ?? '',
    'costo_envio' => floatval($_POST['costo_envio'] ?? 0),
    'correo' => $_POST['correo'] ?? ''
];

// Validar correo
$correo = trim($data['correo'] ?? '');
if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    die("Error: Correo inválido.");
}

// Validar campos obligatorios
$campos_obligatorios = ['nombre', 'cedula', 'celular', 'departamento', 'ciudad', 'direccion'];
foreach ($campos_obligatorios as $campo) {
    if (empty(trim($data[$campo] ?? ''))) {
        die("Error: El campo $campo es obligatorio.");
    }
}

// Validar cédula (solo números, mínimo 7 dígitos)
if (!preg_match('/^\d{7,10}$/', $data['cedula'])) {
    die("Error: La cédula debe contener entre 7 y 10 dígitos.");
}

// Validar celular (formato colombiano)
if (!preg_match('/^3\d{9}$/', $data['celular'])) {
    die("Error: El celular debe tener formato colombiano (10 dígitos comenzando por 3).");
}

// Consultar información del reloj
$id_reloj = intval($data['id_reloj'] ?? 0);
if ($id_reloj <= 0) {
    die("Error: Datos de la orden inválidos (ID reloj).");
}

$stmt = $conn->prepare("SELECT nombre, precio, marca, descuento FROM reloj WHERE id_reloj = ?");
$stmt->bind_param("i", $id_reloj);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Reloj no encontrado.");
}

$reloj = $result->fetch_assoc();
$precio_bd = floatval($reloj['precio']);
$descuento_bd = floatval($reloj['descuento']); // Obtener descuento desde la BD

// Corregir precio si está en formato incorrecto
if ($precio_bd < 1000 && $precio_bd > 0) {
    $precio_bd = $precio_bd * 1000;
}

// 🔥 USAR DIRECTAMENTE LOS DATOS QUE VIENEN DEL FRONTEND
// Estos datos ya están correctamente calculados
$precio_final = isset($data['precio_final']) && $data['precio_final'] > 0 
    ? floatval($data['precio_final']) 
    : $precio_bd;

$costo_envio = floatval($data['costo_envio'] ?? 0);
$descuento_valor = isset($_POST['descuento_valor']) ? floatval($_POST['descuento_valor']) : 0;
$descuento_porcentaje = isset($_POST['descuento_porcentaje']) ? floatval($_POST['descuento_porcentaje']) : 0;

// Validar que los valores no sean negativos
if ($descuento_valor < 0) $descuento_valor = 0;
if ($descuento_porcentaje < 0) $descuento_porcentaje = 0;

// 🔥 CALCULAR TOTAL FINAL SIN DOBLE DESCUENTO
// El precio_final ya viene con el descuento aplicado
$total = $precio_final + $costo_envio;

// Asegurar que el total no sea negativo
if ($total < 0) $total = 0;

// 🔥 ELIMINAR ESTA SECCIÓN PROBLEMÁTICA:
/*
// Redondear el precio al múltiplo de 1000 más cercano
$resto = $precio_final % 1000;
if ($resto >= 500) {
    $precio_final = ceil($precio_final / 1000) * 1000;
} else {
    $precio_final = floor($precio_final / 1000) * 1000;
}

// NO RESTAR EL DESCUENTO DE NUEVO
$total = $precio_final + $costo_envio;
$total -= $descuento_valor; // ❌ ESTA LÍNEA ESTÁ CAUSANDO EL PROBLEMA
*/

// 🔥 PARA MOSTRAR LOS VALORES CORRECTOS:
$precio_original = isset($data['precio_original']) ? floatval($data['precio_original']) : $precio_bd;

// Guardar archivo comprobante
$directorioComprobantes = __DIR__ . '/comprobantes/';
if (!file_exists($directorioComprobantes)) {
    if (!mkdir($directorioComprobantes, 0755, true)) {
        die("Error: No se pudo crear el directorio de comprobantes.");
    }
}

$nombreArchivo = 'comprobante_' . time() . '_' . uniqid() . '.' . $extension;
$rutaCompleta = $directorioComprobantes . $nombreArchivo;

if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
    die("Error: No se pudo guardar el comprobante.");
}

chmod($rutaCompleta, 0644);

// Capturar monto pagado (opcional)
$monto_pagado = isset($_POST['monto_pagado']) && !empty($_POST['monto_pagado']) ? floatval($_POST['monto_pagado']) : null;

// Guardar en la base de datos
try {
    $conn->begin_transaction();

    // Nombre final guardado en el servidor (solo nombre, sin ruta absoluta)
    $nombre_archivo_final = $nombreArchivo; // p.ej. comprobante_1696690000_a1b2c3.png

    $sql_orden = "INSERT INTO orden (
        id_usuario, fecha, total, estado, metodo_pago, costo_envio,
        nombre, correo, cedula, celular, departamento, ciudad, direccion, barrio, referencias,
        token_verificacion, comprobante_pago, nombre_archivo_comprobante, monto_pagado
    ) VALUES (
        ?, NOW(), ?, 'pendiente_verificacion', 'nequi', ?,
        ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?
    )";
    
    $stmt = $conn->prepare($sql_orden);
    $stmt->bind_param(
        "iddssssssssssssd",
        $id_usuario,
        $total,
        $costo_envio,
        $data['nombre'],
        $correo,
        $data['cedula'],
        $data['celular'],
        $data['departamento'],
        $data['ciudad'],
        $data['direccion'],
        $data['barrio'],
        $data['referencias'],
        $token_verificacion,
        $nombre_archivo_final,
        $archivo['name'],
        $monto_pagado
    );

    $stmt->execute();
    $id_orden = $stmt->insert_id;

    // 🔥 GUARDAR EL PRECIO FINAL CORRECTO EN LA BD
    $sql_detalle = "INSERT INTO orden_detalle (id_orden, id_reloj, precio_unitario) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_detalle);
    $stmt->bind_param("iid", $id_orden, $id_reloj, $precio_final);
    $stmt->execute();

    // 🎟️ VINCULAR ORDEN CON CÓDIGO DE DESCUENTO (si se aplicó uno)
    // NOTA: El código ya fue marcado como usado cuando se aplicó
    if (isset($_SESSION['codigo_descuento_aplicado']) && $id_usuario) {
        $codigo_info = $_SESSION['codigo_descuento_aplicado'];
        
        error_log('[NEQUI-INDIVIDUAL] Código aplicado detectado en sesión: ' . $codigo_info['codigo']);
        error_log('[NEQUI-INDIVIDUAL] Vinculando orden con código...');
        
        // Solo actualizar el id_orden para tener referencia
        $stmt_update_codigo = $conn->prepare("
            UPDATE usuario_codigo_descuento 
            SET id_orden = ?
            WHERE id_usuario = ? 
              AND id_codigo = ?
        ");
        
        if ($stmt_update_codigo) {
            $stmt_update_codigo->bind_param("iii", $id_orden, $id_usuario, $codigo_info['id_codigo']);
            $stmt_update_codigo->execute();
            $stmt_update_codigo->close();
            error_log('[NEQUI-INDIVIDUAL] ✓ Orden vinculada al código');
        }
        
        // Limpiar la sesión del código aplicado
        unset($_SESSION['codigo_descuento_aplicado']);
        error_log('[NEQUI-INDIVIDUAL] Sesión de código limpiada');
    }

    $conn->commit();

    // 📦 OBTENER NOMBRE DEL RELOJ PARA NOTIFICACIONES
    $sql_reloj = "SELECT nombre FROM reloj WHERE id_reloj = ?";
    $stmt_reloj = $conn->prepare($sql_reloj);
    $stmt_reloj->bind_param("i", $id_reloj);
    $stmt_reloj->execute();
    $result_reloj = $stmt_reloj->get_result();
    $nombre_reloj = ($result_reloj->num_rows > 0) ? $result_reloj->fetch_assoc()['nombre'] : 'Reloj FINOSO';
    $stmt_reloj->close();
    
    // 📱 ENVIAR NOTIFICACIÓN WHATSAPP AL CLIENTE - Compra Exitosa
    try {
        require_once __DIR__ . '/../../includes/WhatsAppNotificacion.php';
        require_once __DIR__ . '/../../includes/WhatsAppTemplates.php';
        
        if (verificarConfiguracionTwilio()) {
            $whatsapp = new WhatsAppNotificacion();
            
            // Preparar datos para el mensaje
            $datosWhatsApp = [
                'orden_id' => $id_orden,
                'nombre_reloj' => $nombre_reloj,
                'total' => $total,
                'nombre_cliente' => $data['nombre']
            ];
            
            // Enviar notificación al cliente
            $mensajeCliente = WhatsAppTemplates::compraExitosa($datosWhatsApp);
            $whatsapp->enviarMensaje($data['celular'], $mensajeCliente, 'compra_exitosa');
            
            // 📱 ENVIAR NOTIFICACIÓN WHATSAPP AL ADMIN - Nueva Orden
            $datosAdmin = [
                'orden_id' => $id_orden,
                'nombre_cliente' => $data['nombre'],
                'telefono' => $data['celular'],
                'email' => $data['correo'],
                'nombre_reloj' => $nombre_reloj,
                'total' => $total,
                'metodo_pago' => 'Nequi'
            ];
            $mensajeAdmin = WhatsAppTemplates::nuevaOrdenAdmin($datosAdmin);
            $whatsapp->enviarMensaje(ADMIN_WHATSAPP, $mensajeAdmin, 'nueva_orden_admin');
        }
    } catch (Exception $e) {
        // Log del error pero no detener el proceso
        error_log("Error al enviar WhatsApp: " . $e->getMessage());
    }


    error_log('[NEQUI-INDIVIDUAL]  Preparando envío de correo de confirmación...');
    error_log('[NEQUI-INDIVIDUAL] Correo: ' . $correo . ', Nombre: ' . $data['nombre'] . ', Orden: #' . $id_orden);

    //  ENVIAR CORREO DE CONFIRMACIÓN AL CLIENTE
    enviarCorreoConfirmacionOrden($correo, $data['nombre'], $id_orden, $nombre_reloj, $total, $token_verificacion);
    error_log('[NEQUI-INDIVIDUAL]  Llamada a enviarCorreoConfirmacionOrden() completada');
    enviarCorreoConfirmacionOrden($correo, $data['nombre'], $id_orden, $nombre_reloj, $total, $token_verificacion);
     // 🔒 ENVIAR NOTIFICACIÓN POR EMAIL AL ADMINISTRADOR (Mantener existente)
     $asunto_admin = "Nuevo pago Nequi - Orden #$id_orden";
     $mensaje_admin = "
     Se ha recibido un nuevo pago Nequi que requiere verificación manual:
     
     - Orden: #$id_orden
     - Cliente: {$data['nombre']} ({$data['cedula']})
     - Total: $" . number_format($total, 0, ',', '.') . "
     - Token: $token_verificacion
     - IP: $ip_cliente
     - Timestamp: " . date('Y-m-d H:i:s', $timestamp_verificacion) . "
     
     IMPORTANTE: Verificar manualmente el comprobante antes de aprobar el envío.
     ";
     
     // Aquí podrías enviar un correo al administrador
     // mail('admin@finoso.com', $asunto_admin, $mensaje_admin);

     // 🔥 GUARDAR TOKEN EN SESIÓN PARA EVITAR DOBLE PROCESAMIENTO
     $_SESSION['ultimo_token_procesado'] = $token_verificacion;
     $_SESSION['tiempo_ultimo_token'] = time();
     $_SESSION['ultima_orden_id'] = $id_orden;

     // 🔥 REDIRIGIR A LA MISMA PÁGINA CON GET (POST-REDIRECT-GET PATTERN)
     header("Location: " . $_SERVER['PHP_SELF'] . "?orden_id=" . $id_orden . "&token=" . $token_verificacion);
     exit();

} catch (Exception $e) {
    $conn->rollback();
    if (file_exists($rutaCompleta)) {
        unlink($rutaCompleta);
    }
    die("Error: " . $e->getMessage());
}

// 🔥 FUNCIÓN PARA MOSTRAR LA PÁGINA DE ÉXITO
function mostrarPaginaExito($orden) {
    $precio_formateado = number_format($orden['precio_unitario'], 0, ',', '.');
    $costo_envio_formateado = number_format($orden['costo_envio'], 0, ',', '.');
    $total_formateado = number_format($orden['total'], 0, ',', '.');
    
    // Para mostrar información del descuento si existe
    $descuento_info = '';
    if (isset($descuento_valor) && $descuento_valor > 0) {
        $precio_original_formateado = number_format($precio_original, 0, ',', '.');
        $descuento_formateado = number_format($descuento_valor, 0, ',', '.');
        $descuento_info = "
            <p><strong>Precio original:</strong> $" . $precio_original_formateado . "</p>
            <p><strong>Descuento aplicado:</strong> -$" . $descuento_formateado . " ({$descuento_porcentaje}%)</p>
        ";
    }

    // Página de éxito con el nuevo diseño
    echo '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante Recibido - Finoso</title>
    <link rel="icon" href="https://finoso.store/img/finoso_logo.png" type="image/x-icon">
    <style>
        * {
            font-family: \'Playfair Display\', serif;
            margin: 0;
            color: #FFCF66;
        }
        
        @font-face {
            font-family: \'Playfair Display\';
            src: url(\'/finoso/fonts/Playfair_Display/PlayfairDisplay-VariableFont_wght.ttf\') format(\'truetype\');
        }

        body {
            font-family: \'Arial\', sans-serif;
            background: linear-gradient(135deg, #573720 0%, #FFCF66 50%, #46310d 100%);
            min-height: 100vh;
            color: #fff;
            overflow-x: hidden;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 50px;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }

        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #FFCF66;
            letter-spacing: 3px;
        }

        .nav {
            display: flex;
            gap: 40px;
        }

        .nav a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            letter-spacing: 1px;
            transition: color 0.3s ease;
        }

        .nav a:hover {
            color: #FFCF66;
        }

        .user-actions {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .user-icon, .cart-icon {
            width: 24px;
            height: 24px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .user-icon:hover, .cart-icon:hover {
            transform: scale(1.1);
            filter: drop-shadow(0 0 8px #FFCF66);
        }

        /* Main Content */
        .main-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 100px);
            padding: 50px 20px;
            position: relative;
        }

        /* Decorative Background Elements */
        .bg-decoration {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
        }

        .bg-decoration::before {
            content: \'\';
            position: absolute;
            top: 20%;
            right: 10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .bg-decoration::after {
            content: \'\';
            position: absolute;
            bottom: 20%;
            left: 10%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }

        /* Portacuentas elegante - Versión 2 */
        .portacuentas-container {
            position: relative;
            width: 650px;
            max-width: 90vw;
            perspective: 1500px;
            transform-style: preserve-3d;
        }

        /* Mesa/superficie donde está el portacuentas */
        .mesa-surface {
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 120%;
            height: 40px;
            background: radial-gradient(ellipse, rgba(0, 0, 0, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            animation: fadeOut 0.5s ease-in-out 1.8s forwards;
        }

        /* Portacuentas cerrado inicial */
        .portacuentas-closed {
            position: absolute;
            width: 100%;
            height: 60%;
            background: linear-gradient(145deg, #3d2817 0%, #2a1810 30%, #1a0f08 100%);
            border-radius: 15px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.6),
                inset 0 1px 0 rgba(212, 175, 55, 0.3),
                inset 0 -1px 0 rgba(0, 0, 0, 0.5);
            border: 2px solid rgba(212, 175, 55, 0.4);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
            animation: liftAndSlide 2s ease-in-out 1.5s forwards;
            z-index: 15;
        }

        /* Textura de cuero en el portacuentas */
        .portacuentas-closed::before {
            content: \'\';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 30%, rgba(212, 175, 55, 0.1) 1px, transparent 1px),
                radial-gradient(circle at 80% 70%, rgba(212, 175, 55, 0.1) 1px, transparent 1px),
                radial-gradient(circle at 40% 80%, rgba(212, 175, 55, 0.05) 1px, transparent 1px);
            background-size: 40px 40px, 35px 35px, 30px 30px;
            border-radius: 15px;
        }

        .portacuentas-logo-v2 {
            font-size: 54px;
            font-weight: bold;
            color: #FFCF66;
            letter-spacing: 4px;
            text-shadow: 
                0 0 20px rgba(212, 175, 55, 0.8),
                0 0 40px rgba(212, 175, 55, 0.4);
            z-index: 1;
        }

        .portacuentas-subtitle {
            font-size: 16px;
            color: rgba(255, 207, 102, 0.8);
            letter-spacing: 2px;
            text-transform: uppercase;
            z-index: 1;
        }

        /* Cierre/broche del portacuentas */
        .portacuentas-clasp {
            position: absolute;
            top: 50%;
            right: -8px;
            width: 16px;
            height: 80px;
            background: linear-gradient(180deg, #FFCF66 0%, #B8941F 100%);
            border-radius: 8px;
            transform: translateY(-50%);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            animation: openClasp 0.8s ease-in-out 1.5s forwards;
            z-index: 1;
        }

        /* Contenido final */
        .success-card {
            background: rgb(0 0 0 / 60%);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(212, 175, 55, 0.4);
            border-radius: 20px;
            padding: 20px 50px;
            box-sizing: border-box;
            max-width: 600px;
            width: 100%;
            text-align: center;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.6),
                inset 0 1px 0 rgba(212, 175, 55, 0.2);
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: scale(0.7) translateY(50px);
            animation: revealContent 1.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) 2.5s forwards;
        }

        .success-card::before {
            content: \'\';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.2), transparent);
            animation: shine 2s ease-in-out 6.5s infinite;
        }

        /* Animaciones */
        @keyframes liftAndSlide {
            0% {
                transform: translateY(0) rotateX(0deg);
            }
            50% {
                transform: translateY(-30px) rotateX(5deg);
            }
            100% {
                transform: translateY(-30px) rotateX(5deg) translateX(-100vw);
                opacity: 0;
            }
        }

        @keyframes openClasp {
            0% {
                transform: translateY(-50%) rotateZ(0deg);
            }
            100% {
                transform: translateY(-50%) rotateZ(90deg) translateX(20px);
                opacity: 0;
            }
        }

        @keyframes fadeOut {
            to { opacity: 0; }
        }

        @keyframes showBook {
            to { opacity: 1; }
        }

        @keyframes flipLeft {
            0% {
                transform: rotateY(0deg);
            }
            100% {
                transform: rotateY(-180deg);
            }
        }

        @keyframes flipRight {
            0% {
                transform: rotateY(0deg);
            }
            100% {
                transform: rotateY(180deg);
            }
        }

        @keyframes flipPagesLeft {
            0% {
                transform: rotateY(0deg);
            }
            100% {
                transform: rotateY(-170deg);
            }
        }

        @keyframes flipPagesRight {
            0% {
                transform: rotateY(0deg);
            }
            100% {
                transform: rotateY(170deg);
            }
        }

        @keyframes revealContent {
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes shine {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .success-icon {
            font-size: 90px;
            color: #4CAF50;
            animation: successPop 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) 3s forwards;
            transform: scale(0);
        }

        @keyframes successPop {
            0% { transform: scale(0) rotate(-180deg); }
            80% { transform: scale(1.3) rotate(10deg); }
            100% { transform: scale(1) rotate(0deg); }
        }

        .success-title {
            font-size: 32px;
            font-weight: bold;
            color: #FFCF66;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }

        .success-subtitle {
            font-size: 18px;
            color: #ccc;
            margin-bottom: 20px;
        }

        .order-details {
            background: rgba(212, 175, 55, 0.1);
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            border: 1px solid rgba(212, 175, 55, 0.2);
        }

        .order-details h3 {
            color: #FFCF66;
            font-size: 24px;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 10px;
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .detail-label {
            font-weight: bold;
            color: #fff;
        }

        .detail-value {
            color: #FFCF66;
            font-weight: 500;
            text-align: right;
        }

        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .home-button {
            display: inline-block;
            background: linear-gradient(135deg, #FFCF66 0%, #B8941F 100%);
            color: #000;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 16px;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin-top: 30px;
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.3);
        }

        .home-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(212, 175, 55, 0.4);
            background: linear-gradient(135deg, #E5C047 0%, #C9A52F 100%);
        }

        @media (max-width: 768px) {
            .header {
                padding: 15px 20px;
                flex-direction: column;
                align-items: flex-start;
            }

            .logo {
                font-size: 24px;
            }

            .nav {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .nav a {
                font-size: 14px;
            }

            .portacuentas-container {
                width: 100%;
                margin: 20px 0;
            }

            .success-card {
                padding: 40px 30px;
            }

            .success-title {
                font-size: 24px;
            }

            .order-details {
                padding: 20px;
            }

            .detail-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .portacuentas-logo-v2 {
                font-size: 36px;
            }
        }

        @media (max-width: 480px) {
            .nav {
                display: none;
            }

            .success-card {
                padding: 30px 20px;
            }

            .success-title {
                font-size: 20px;
            }

            .order-details h3 {
                font-size: 20px;
            }

            .portacuentas-logo-v2 {
                font-size: 28px;
            }
            .detail-value {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <!-- Decorative Background Elements -->
    <div class="bg-decoration"></div>

    <!-- Main Content -->
    <div class="main-container">
        <div class="portacuentas-container">
            <!-- Sombra de mesa -->
            <div class="mesa-surface"></div>
            
            <!-- Portacuentas cerrado inicial -->
            <div class="portacuentas-closed">
                <div class="portacuentas-logo-v2">FINOSO</div>
                <div class="portacuentas-subtitle">Premium Collection</div>
                <div class="portacuentas-clasp"></div>
            </div>

            <!-- Contenido interior -->
            <div class="success-card">
                <div class="success-icon">✅</div>
                <h1 class="success-title">¡Comprobante recibido!</h1>
                <p class="success-subtitle">Tu comprobante fue recibido y está en verificación. Esto puede tardar hasta 3 horas.</p>
                
                <div class="order-details">
                    <h3>Detalles de tu orden</h3>
                    
                    
                    
                    <div class="detail-row">
                        <span class="detail-label">Producto:</span>
                        <span class="detail-value">' . htmlspecialchars($orden['nombre_reloj']) . '</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Precio:</span>
                        <span class="detail-value">$' . $precio_formateado . '</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Costo de envío:</span>
                        <span class="detail-value">$' . $costo_envio_formateado . '</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Total pagado:</span>
                        <span class="detail-value total-amount">$' . $total_formateado . '</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Estado:</span>
                        <span class="status-badge" style="background: rgba(255, 193, 7, 0.2); color: #FFC107; border-color: rgba(255, 193, 7, 0.3);">
                            ⏳ Pendiente de Verificación
                        </span>
                    </div>
                    
                    
                </div>
                
                <div style="background: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.3); border-radius: 10px; padding: 20px; margin: 20px 0; text-align: left;">
                    <h4 style="color: #FFC107; margin-bottom: 10px;">📋 Próximos pasos</h4>
                    <ul style="color: #ccc; margin: 0; padding-left: 20px;">
                        <li>Tu comprobante será verificado en las próximas <strong>3 horas</strong>.</li>
                        <li>Si la verificación es correcta, recibirás la <strong>confirmación del pedido por correo</strong>.</li>
                        <li>Si no se valida o hay inconsistencias en el monto o datos, te notificaremos por correo con los pasos a seguir.</li>
                        <li>Conserva tu comprobante y el token de verificación para cualquier revisión.</li>
                    </ul>
                </div>
                
                <a href="/finoso/" class="home-button">VOLVER AL INICIO</a>
            </div>
        </div>
    </div>
</body>
</html>';

 // Envío de correo y código de descuento movido al flujo de aprobación del admin.

}
?>
