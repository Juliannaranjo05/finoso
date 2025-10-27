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
    $stmt = $conn->prepare("SELECT o.*, GROUP_CONCAT(r.nombre SEPARATOR ', ') as nombre_relojes
                            FROM orden o 
                            LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
                            LEFT JOIN reloj r ON od.id_reloj = r.id_reloj
                            WHERE o.id_orden = ?
                            GROUP BY o.id_orden");
    $stmt->bind_param("i", $orden_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $orden = $result->fetch_assoc();
        $stmt->close();
        // Mostrar página de éxito sin procesar nada
        mostrarPaginaExito($orden, $conn);
        exit();
    }
    $stmt->close();
}

// 🔥 VERIFICAR QUE SEA UNA PETICIÓN POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /finoso/');
    exit();
}

// 🔥 VERIFICAR TOKEN DE SESIÓN PARA EVITAR DOBLE ENVÍO
if (isset($_SESSION['ultimo_token_procesado_favoritos'])) {
    $token_anterior = $_SESSION['ultimo_token_procesado_favoritos'];
    $tiempo_anterior = $_SESSION['tiempo_ultimo_token_favoritos'] ?? 0;
    
    // Si el token se procesó hace menos de 10 segundos, es un reenvío
    if ((time() - $tiempo_anterior) < 10) {
        // Redirigir a la página de confirmación de la orden anterior
        if (isset($_SESSION['ultima_orden_id_favoritos'])) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?orden_id=" . $_SESSION['ultima_orden_id_favoritos'] . "&token=" . $token_anterior);
            exit();
        }
    }
}

session_start();
$ip_address = $_SERVER['REMOTE_ADDR'];

$id_usuario_sesion = $_SESSION['id_usuario'] ?? null;
$id_usuario_post = $_POST['id_usuario'] ?? null;
$id_usuario = $id_usuario_sesion ? intval($id_usuario_sesion) : ($id_usuario_post ? intval($id_usuario_post) : null);

// ⭐ DEBUG CRÍTICO: Rastrear origen del id_usuario
error_log('=== INICIO DEBUG COMPRA ANÓNIMA ===');
error_log('[NEQUI-CARRITO] id_usuario_sesion (SESSION): ' . ($id_usuario_sesion === null ? 'NULL' : "'" . $id_usuario_sesion . "'"));
error_log('[NEQUI-CARRITO] id_usuario_post (POST): ' . ($id_usuario_post === null ? 'NULL' : "'" . $id_usuario_post . "'"));
error_log('[NEQUI-CARRITO] id_usuario FINAL: ' . ($id_usuario === null ? 'NULL' : "'" . $id_usuario . "'"));
error_log('[NEQUI-CARRITO] Correo: ' . ($_POST['correo'] ?? 'NO DISPONIBLE'));
error_log('=================================');

if (!isset($_FILES['comprobante'])) {
    die("Error: No se seleccionó ningún archivo.");
}

$archivo = $_FILES['comprobante'];

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

if (!is_uploaded_file($archivo['tmp_name'])) {
    die("Error: El archivo no se ha subido correctamente.");
}

$extensionesValidas = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

if (!in_array($extension, $extensionesValidas)) {
    echo "<script>alert('Error: Solo se permiten archivos JPG, PNG, PDF o WebP.'); history.back();</script>";
}

if ($archivo['size'] > 5 * 1024 * 1024) {
    echo "<script>alert('El archivo es demasiado grande. Máximo 5MB.'); history.back();</script>";
}

if ($archivo['size'] < 1024) {
    echo "<script>alert('El archivo es demasiado pequeño para ser un comprobante válido.'); history.back();</script>";
}

$hash_archivo = md5_file($archivo['tmp_name']);

// Validación de archivo duplicado - verificar por nombre de archivo
$stmt = $conn->prepare("SELECT COUNT(*) FROM orden WHERE nombre_archivo_comprobante = ?");
$stmt->bind_param("s", $nombre_archivo);
$stmt->execute();
$stmt->bind_result($ya_subido);
$stmt->fetch();
$stmt->close();

if ($ya_subido > 0) {
    echo "<script>alert('Este comprobante ya fue registrado anteriormente.'); history.back();</script>";
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $archivo['tmp_name']);
finfo_close($finfo);

$mimes_validos = [
    'image/jpeg' => ['jpg', 'jpeg'],
    'image/png' => ['png'],
    'image/webp' => ['webp'],
    'application/pdf' => ['pdf']
];

$mime_valido = false;
foreach ($mimes_validos as $mime => $extensiones) {
    if ($mime_type === $mime && in_array($extension, $extensiones)) {
        $mime_valido = true;
        break;
    }
}

if (!$mime_valido) {
    die("Error: El tipo de archivo no coincide con su extensión.");
}

$productos_raw = $_POST['productos'] ?? '';
error_log('[FAVORITOS-DEBUG] productos_raw recibido: ' . substr($productos_raw, 0, 200));
$productos = json_decode($productos_raw, true) ?: [];
error_log('[FAVORITOS-DEBUG] productos decodificado: ' . print_r($productos, true));
error_log('[FAVORITOS-DEBUG] Total de productos en POST: ' . count($productos));

if (empty($productos)) {
    error_log('[FAVORITOS-DEBUG] ERROR: Array de productos está vacío');
    die("Error: No hay productos en el carritooo.");
}

$datos_orden_raw = $_POST['datos_orden'] ?? '';
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

$correo = trim($data['correo'] ?? '');
if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    die("Error: Correo inválido.");
}

$campos_obligatorios = ['nombre', 'cedula', 'celular', 'departamento', 'ciudad', 'direccion'];
foreach ($campos_obligatorios as $campo) {
    if (empty(trim($data[$campo] ?? ''))) {
        die("Error: El campo $campo es obligatorio.");
    }
}

if (!preg_match('/^\d{7,10}$/', $data['cedula'])) {
    die("Error: La cédula debe contener entre 7 y 10 dígitos.");
}

if (!preg_match('/^3\d{9}$/', $data['celular'])) {
    die("Error: El celular debe tener formato colombiano (10 dígitos comenzando por 3).");
}

$productos_unicos = [];
foreach ($productos as $producto) {
    // Intentar obtener ID de ambas claves posibles (id_reloj o id)
    $id_reloj = intval($producto['id_reloj'] ?? $producto['id'] ?? 0);
    error_log('[FAVORITOS-DEBUG] Producto pre-filtrado: id_reloj encontrado = ' . $id_reloj);
    if ($id_reloj > 0) {
        $productos_unicos[$id_reloj] = $producto;
    } else {
        error_log('[FAVORITOS-DEBUG] ⚠️ Producto rechazado por ID inválido: ' . print_r($producto, true));
    }
}
$productos = array_values($productos_unicos);
error_log('[FAVORITOS-DEBUG] Productos únicos después del filtro: ' . count($productos));

$total = 0;
$productos_detalle = [];

error_log('[FAVORITOS-DEBUG] Iniciando procesamiento de ' . count($productos) . ' productos');

foreach ($productos as $index => $producto) {
    error_log('[FAVORITOS-DEBUG] Procesando producto #' . $index . ': ' . print_r($producto, true));
    // Intentar obtener ID de ambas claves posibles (id_reloj o id)
    $id_reloj = intval($producto['id_reloj'] ?? $producto['id'] ?? 0);
    
    if ($id_reloj <= 0) {
        error_log('[FAVORITOS-DEBUG] ERROR: ID de reloj inválido: ' . ($producto['id_reloj'] ?? $producto['id'] ?? 'NULL'));
        die("Error: ID de reloj inválido en el carrito.");
    }
    
    error_log('[FAVORITOS-DEBUG] ID reloj válido: ' . $id_reloj);
    
    $stmt = $conn->prepare("SELECT nombre, precio, marca, descuento FROM reloj WHERE id_reloj = ?");
    $stmt->bind_param("i", $id_reloj);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die("Error: Reloj con ID $id_reloj no encontrado.");
    }
    
    $reloj_data = $result->fetch_assoc();
    $stmt->close();
    
    $precio_reloj = floatval($reloj_data['precio']);
    $descuento = floatval($reloj_data['descuento']);
    
    if ($precio_reloj < 1000 && $precio_reloj > 0) {
        $precio_reloj = $precio_reloj * 1000;
    }
    
    if ($descuento > 0) {
        $precio_reloj = $precio_reloj * (1 - $descuento);
    }
    
    $resto = $precio_reloj % 1000;
    if ($resto >= 500) {
        $precio_reloj = ceil($precio_reloj / 1000) * 1000;
    } else {
        $precio_reloj = floor($precio_reloj / 1000) * 1000;
    }
    
    $total += $precio_reloj;
    
    $productos_detalle[] = [
        'id_reloj' => $id_reloj,
        'nombre' => $reloj_data['nombre'],
        'precio' => $precio_reloj,
        'marca' => $reloj_data['marca']
    ];
}

$costo_envio = floatval($data['costo_envio'] ?? 0);
$total += $costo_envio;

error_log('[FAVORITOS-DEBUG] Total productos procesados: ' . count($productos_detalle));
error_log('[FAVORITOS-DEBUG] Contenido de $productos_detalle: ' . print_r($productos_detalle, true));
error_log('[FAVORITOS-DEBUG] Total calculado: ' . $total);

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

$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';

// Capturar monto pagado (opcional)
$monto_pagado = isset($_POST['monto_pagado']) && !empty($_POST['monto_pagado']) ? floatval($_POST['monto_pagado']) : null;

$param_estado = 'pendiente_verificacion';  // Estado inicial al subir comprobante
$param_metodo = 'nequi';
$param_nombre = $data['nombre'];
$param_cedula = $data['cedula'];
$param_celular = $data['celular'];
$param_departamento = $data['departamento'];
$param_ciudad = $data['ciudad'];
$param_direccion = $data['direccion'];
$param_barrio = $data['barrio'];
$param_referencias = $data['referencias'];

// Generar token de verificación ANTES de crear la orden
$token_verificacion = bin2hex(random_bytes(16));

try {
    $conn->begin_transaction();

    $sql_orden = "INSERT INTO orden (
        id_usuario, total, estado, metodo_pago, costo_envio,
        nombre, cedula, celular, departamento, ciudad, direccion, barrio, referencias,
        comprobante_pago, nombre_archivo_comprobante, correo, token_verificacion, monto_pagado
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql_orden);
    $stmt->bind_param(
        "idssdssssssssssssd",
        $id_usuario,
        $total,
        $param_estado,
        $param_metodo,
        $costo_envio,
        $param_nombre,
        $param_cedula,
        $param_celular,
        $param_departamento,
        $param_ciudad,
        $param_direccion,
        $param_barrio,
        $param_referencias,
        $nombreArchivo,
        $nombreArchivo,  // También guardar en nombre_archivo_comprobante
        $correo,
        $token_verificacion,
        $monto_pagado
    );


    if (!$stmt->execute()) {
        throw new Exception("Error ejecutando la orden: " . $stmt->error);
    }

    $id_orden = $stmt->insert_id;
    $stmt->close();

    $sql_detalle = "INSERT INTO orden_detalle (id_orden, id_reloj, precio_unitario) VALUES (?, ?, ?)";
    $stmt_detalle = $conn->prepare($sql_detalle);

    error_log('[FAVORITOS] Insertando ' . count($productos_detalle) . ' productos en orden_detalle para orden #' . $id_orden);
    
    $productos_html = '';
    foreach ($productos_detalle as $producto) {
        $stmt_detalle->bind_param("iid", $id_orden, $producto['id_reloj'], $producto['precio']);
        $stmt_detalle->execute();
        error_log('[FAVORITOS] Insertado producto: ID=' . $producto['id_reloj'] . ', Precio=' . $producto['precio']);

        $nombre_producto = htmlspecialchars($producto['nombre']);
        $precio_producto_formateado = number_format($producto['precio'], 0, ',', '.');
        
        $productos_html .= '
            <div class="producto-item" style="display: flex; justify-content: space-between; padding: 6px 12px; border-bottom: 1px solid #ffffff1a;">
                <span class="producto-nombre" style="font-weight: 500;">' . $nombre_producto . '</span>
                <span class="producto-precio" style="color: #fff;">$' . $precio_producto_formateado . '</span>
            </div>';
    }

    $stmt_detalle->close();
    $conn->commit();


    //  ENVIAR CORREO DE CONFIRMACIÓN AL CLIENTE
    $productos_texto = '';
    foreach ($productos as $prod) {
        $productos_texto .= $prod['nombre'] . ' - $' . number_format($prod['precio'], 0, ',', '.') . "
";
    }
    $productos_para_correo = trim($productos_texto);
    $productos_para_correo = trim($productos_texto);
    error_log('[NEQUI-FAVORITOS]  Preparando envío de correo de confirmación...');
    error_log('[NEQUI-FAVORITOS] Correo: ' . $correo . ', Nombre: ' . $param_nombre . ', Orden: #' . $id_orden);
    enviarCorreoConfirmacionOrden($correo, $param_nombre, $id_orden, $productos_para_correo, $total, $token_verificacion);
    error_log('[NEQUI-FAVORITOS]  Llamada a enviarCorreoConfirmacionOrden() completada');
    // Guardar token en sesión para evitar reenvíos
    $_SESSION['ultimo_token_procesado_favoritos'] = $token_verificacion;
    $_SESSION['tiempo_ultimo_token_favoritos'] = time();
    $_SESSION['ultima_orden_id_favoritos'] = $id_orden;

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
function mostrarPaginaExito($orden, $conn) {
    // Calcular valores formateados
    $total_productos = $orden['total'] - $orden['costo_envio'];
    $total_productos_formateado = number_format($total_productos, 0, ',', '.');
    $costo_envio_formateado = number_format($orden['costo_envio'], 0, ',', '.');
    $total_formateado = number_format($orden['total'], 0, ',', '.');
    
    // Obtener productos de esta orden
    error_log('[MOSTRAR-EXITO-FAVORITOS] Buscando productos para orden #' . $orden['id_orden']);
    $stmt = $conn->prepare("SELECT r.nombre, od.precio_unitario 
                            FROM orden_detalle od
                            JOIN reloj r ON od.id_reloj = r.id_reloj
                            WHERE od.id_orden = ?");
    $stmt->bind_param("i", $orden['id_orden']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    error_log('[MOSTRAR-EXITO-FAVORITOS] Productos encontrados: ' . $result->num_rows);
    
    $productos_html = '';
    while ($row = $result->fetch_assoc()) {
        $nombre_producto = htmlspecialchars($row['nombre']);
        $precio_producto_formateado = number_format($row['precio_unitario'], 0, ',', '.');
        
        error_log('[MOSTRAR-EXITO-FAVORITOS] Producto: ' . $nombre_producto . ' - Precio: ' . $row['precio_unitario']);
        
        $productos_html .= '
            <div class="producto-item" style="display: flex; justify-content: space-between; padding: 6px 12px; border-bottom: 1px solid #ffffff1a;">
                <span class="producto-nombre" style="font-weight: 500;">' . $nombre_producto . '</span>
                <span class="producto-precio" style="color: #fff;">$' . $precio_producto_formateado . '</span>
            </div>';
    }
    $stmt->close();
    
    error_log('[MOSTRAR-EXITO-FAVORITOS] HTML generado: ' . (strlen($productos_html) > 0 ? 'SÍ (' . strlen($productos_html) . ' chars)' : 'NO (vacío)'));

 echo '
 <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante Recibido - Finoso</title>
    <link rel="icon" href="http://127.0.0.1/finoso/img/finoso_logo.png" type="image/x-icon">
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
                        <span class="detail-label">Número de orden:</span>
                        <span class="detail-value">#' . $orden['id_orden'] . '</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Producto:</span>
                    </div>
                    <div class="productos-lista" style="border: 1px solid #ffffff1a; border-radius: 5px; overflow: hidden; margin-bottom: 10px;">
                        ' . $productos_html . '
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Precio:</span>
                        <span class="detail-value">$' . $total_productos_formateado . '</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Costo de envío:</span>
                        <span class="detail-value">$' . $costo_envio_formateado . '</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Total:</span>
                        <span class="detail-value total-amount">$' . $total_formateado . '</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Estado:</span>
                        <span class="status-badge" style="background: rgba(255, 193, 7, 0.2); color: #FFC107; border: 1px solid rgba(255, 193, 7, 0.3);">
                            ⏳ Pendiente de Verificación
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Correo:</span>
                        <span class="detail-value">' . htmlspecialchars($orden['correo']) . '</span>
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
}

// FIN DEL ARCHIVO - El código de generación de códigos y emails NO se ejecuta en favoritos (son compras anónimas)
?>
