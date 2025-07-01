<?php
include 'conexion.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '/finoso/vendor/phpmailer/phpmailer/src/Exception.php';
require '/finoso/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '/finoso/vendor/phpmailer/phpmailer/src/SMTP.php';
require __DIR__ . '/../../vendor/autoload.php';

session_start();

// Obtener ID de usuario, primero de la sesión y luego de POST si no está en sesión
$id_usuario_sesion = $_SESSION['id_usuario'] ?? null;
$id_usuario_post = $_POST['id_usuario'] ?? null;

$id_usuario = $id_usuario_sesion ? intval($id_usuario_sesion) : ($id_usuario_post ? intval($id_usuario_post) : null);

// Verificar archivo de comprobante
if (!isset($_FILES['comprobante'])) {
    die("Error: No se seleccionó ningún archivo.");
}

$archivo = $_FILES['comprobante'];
$extensionesValidas = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

if (!in_array($extension, $extensionesValidas)) {
    die("Error: Solo se permiten archivos JPG, PNG, PDF o WebP.");
}

if ($archivo['size'] > 5 * 1024 * 1024) {
    die("Error: El archivo es demasiado grande. Máximo 5MB.");
}

// Procesar datos de la orden
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

// Validar correo
$correo = trim($data['correo'] ?? '');
if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    die("Error: Correo inválido.");
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
$precio_reloj = floatval($reloj['precio']);
$descuento = floatval($reloj['descuento']); // Obtener descuento desde la BD

// Corregir precio si está en formato incorrecto
if ($precio_reloj < 1000 && $precio_reloj > 0) {
    $precio_reloj = $precio_reloj * 1000;
}

// Aplicar descuento solo si existe (descuento > 0)
if ($descuento > 0) {
    $precio_reloj = $precio_reloj * (1 - $descuento);  // Aplicar descuento
}

// Redondear el precio al múltiplo de 1000 más cercano
$resto = $precio_reloj % 1000; // Restante al dividir entre 1000

if ($resto >= 500) {
    $precio_reloj = ceil($precio_reloj / 1000) * 1000; // Redondeo hacia arriba al siguiente múltiplo de 1000
} else {
    $precio_reloj = floor($precio_reloj / 1000) * 1000; // Redondeo hacia abajo al múltiplo anterior de 1000
}

$costo_envio = floatval($data['costo_envio'] ?? 0);
$total = $precio_reloj + $costo_envio;

// Guardar archivo comprobante
$directorioComprobantes = __DIR__ . '/comprobantes/';
if (!file_exists($directorioComprobantes)) {
    if (!mkdir($directorioComprobantes, 0777, true)) {
        die("Error: No se pudo crear el directorio de comprobantes.");
    }
}

$nombreArchivo = 'comprobante_' . time() . '_' . uniqid() . '.' . $extension;
$rutaCompleta = $directorioComprobantes . $nombreArchivo;

if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
    die("Error: No se pudo guardar el comprobante.");
}

// Guardar en la base de datos
try {
    $conn->begin_transaction();

    $sql_orden = "INSERT INTO orden (
        id_usuario, fecha, total, estado, metodo_pago, costo_envio,
        nombre, cedula, celular, departamento, ciudad, direccion, barrio, referencias,
        comprobante_pago, correo
    ) VALUES (
        ?, NOW(), ?, 'pagado', 'nequi', ?,
        ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?
    )";
    $stmt = $conn->prepare($sql_orden);
    $stmt->bind_param(
        "iddssssssssss", // 13 parámetros
        $id_usuario,
        $total,
        $costo_envio,
        $data['nombre'],
        $data['cedula'],
        $data['celular'],
        $data['departamento'],
        $data['ciudad'],
        $data['direccion'],
        $data['barrio'],
        $data['referencias'],
        $nombreArchivo,
        $correo
    );
    $stmt->execute();

    $id_orden = $stmt->insert_id;

    $sql_detalle = "INSERT INTO orden_detalle (id_orden, id_reloj, precio_unitario) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_detalle);
    $stmt->bind_param("iid", $id_orden, $id_reloj, $precio_reloj);
    $stmt->execute();

    $conn->commit();

    // Formatear valores para mostrar
    $precio_formateado = number_format($precio_reloj, 0, ',', '.');
    $costo_envio_formateado = number_format($costo_envio, 0, ',', '.');
    $total_formateado = number_format($total, 0, ',', '.');

    // Página de éxito con el nuevo diseño
    echo '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Exitoso - FINOSO</title>
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
                <h1 class="success-title">¡PAGO EXITOSO!</h1>
                <p class="success-subtitle">Tu orden ha sido procesada correctamente</p>
                
                <div class="order-details">
                    <h3>Detalles de tu orden</h3>
                    
                    <div class="detail-row">
                        <span class="detail-label">Número de orden:</span>
                        <span class="detail-value">#' . $id_orden . '</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Producto:</span>
                        <span class="detail-value">' . htmlspecialchars($reloj['nombre']) . '</span>
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
                        <span class="status-badge">
                            ✅ Pagado
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Correo:</span>
                        <span class="detail-value">' . htmlspecialchars($correo) . '</span>
                    </div>
                </div>
                
                <a href="/finoso/" class="home-button">VOLVER AL INICIO</a>
            </div>
        </div>
    </div>
</body>
</html>';

$codigo_descuento = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
$porcentaje = 10; // Por ejemplo, 10% de descuento
$fecha_expiracion = date('Y-m-d', strtotime('+90 days')); // 30 días desde hoy

// Guardar en la base de datos
$stmt = $conn->prepare("INSERT INTO codigo_descuento (codigo, porcentaje, fecha_expiracion) VALUES (?, ?, ?)");
$stmt->bind_param("sds", $codigo_descuento, $porcentaje, $fecha_expiracion);
$stmt->execute();


function enviarCorreoCompraExitosa($correoDestino, $codigo_descuento, $porcentaje, $fecha_expiracion) {
    $mail = new PHPMailer(true);

    try {
        echo "<script>console.log('📤 Iniciando envío de correo...');</script>";

        // Depuración SMTP activada (nivel 2 para mayor detalle)
        $mail->SMTPDebug = 2; 
        $mail->Debugoutput = function($str, $level) {
            echo "<script>console.log(" . json_encode("📧 [Nivel $level] $str") . ");</script>";
        };

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'davidpascuas708@gmail.com';  // Tu correo Gmail
        $mail->Password = 'qinc wznz hvmv zqwu';         // App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8'; // Asegura que todo el correo use UTF-8

        $mail->setFrom('davidpascuas708@gmail.com', 'Finoso');
        $mail->addAddress($correoDestino);

        $mail->isHTML(true);
        $fecha_expiracion_formateada = date('d/m/Y', strtotime($fecha_expiracion));
        $mail->Subject = '¡Gracias por tu compra en Finoso! Aqui tienes un obsequio especial';

        $mail->Body = <<<EOD
        <div style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 30px; border-radius: 10px; color: #333;">
            <div style="max-width: 600px; margin: auto; background-color: white; border-radius: 8px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                <h2 style="color: #333; text-align: center;">🤝 ¡Gracias por confiar en Finoso!</h2>
                <p style="font-size: 16px; line-height: 1.6;">
                    Hemos recibido tu compra y estamos preparando todo para que la experiencia sea inolvidable.
                </p>
                <p style="font-size: 16px; line-height: 1.6;">
                    Como agradecimiento, te obsequiamos un código exclusivo del <strong>$porcentaje%</strong> de descuento para tu próxima compra.
                </p>

                <div style="text-align: center; margin: 30px 0;">
                    <span style="display: inline-block; font-size: 24px; background: #000; color: #fff; padding: 15px 25px; border-radius: 5px; letter-spacing: 4px;">
                        $codigo_descuento
                    </span>
                </div>

                <p style="text-align: center; font-size: 14px; color: #888;">
                    Válido hasta el <strong>$fecha_expiracion_formateada</strong>
                </p>

                <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">

                <p style="font-size: 14px; text-align: center; color: #666;">
                    💼 Somos Finoso. Un detalle habla más que mil palabras.<br>
                    Gracias por ser parte de esta experiencia.
                </p>
            </div>
        </div>
        EOD;

        $mail->send();
        echo "<script>console.log('✅ Correo enviado a: " . $correoDestino . "');</script>";
    } catch (Exception $e) {
        echo "<script>console.error('❌ Error al enviar correo: " . $mail->ErrorInfo . "');</script>";
    }
}

enviarCorreoCompraExitosa($correo, $codigo_descuento, $porcentaje, $fecha_expiracion);

} catch (Exception $e) {
    $conn->rollback();
    if (file_exists($rutaCompleta)) {
        unlink($rutaCompleta);
    }
    die("Error: " . $e->getMessage());
}
?>