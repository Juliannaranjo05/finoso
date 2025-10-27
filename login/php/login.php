<?php
// Configurar codificación UTF-8
header('Content-Type: application/json; charset=utf-8');
mb_internal_encoding('UTF-8');

// Habilitar logs de error
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/apache/logs/php_errors.log');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug: Log inicial
error_log("=== INICIO LOGIN ===");
error_log("POST data: " . print_r($_POST, true));
error_log("SERVER data: " . print_r($_SERVER, true));

try {
    // Incluir archivo de conexión
    error_log("Verificando archivo conexion.php...");
    if (!file_exists('conexion.php')) {
        throw new Exception('El archivo conexion.php no existe');
    }
    include 'conexion.php';
    error_log("Archivo conexion.php incluido correctamente");
    
    // Verificar conexión
    error_log("Verificando conexión a BD...");
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Error de conexión a la base de datos: ' . (isset($conn) ? $conn->connect_error : 'Variable $conn no definida'));
    }
    error_log("Conexión a BD establecida correctamente");

    // Verificar método POST
    error_log("Verificando método HTTP...");
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido: debe ser POST');
    }
    error_log("Método POST confirmado");

    if (!isset($_POST['action']) || $_POST['action'] !== 'login') {
        throw new Exception('Acción no válida');
    }
    error_log("Acción login confirmada");

    // Obtener y limpiar datos
    error_log("Obteniendo datos del formulario...");
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';
    error_log("Datos obtenidos - Nombre: '$nombre', Contraseña length: " . strlen($contrasena));

    // Validar campos
    error_log("Validando campos...");
    if (empty($nombre) || empty($contrasena)) {
        throw new Exception('Por favor, completa todos los campos');
    }
    error_log("Campos validados correctamente");

    // CONSULTA SEGURA CON PREPARED STATEMENT
    error_log("Preparando consulta SQL...");
    $stmt = $conn->prepare("SELECT id_usuario, nombre, correo, contrasena, verificado, rol FROM usuario WHERE nombre = ?");
    if (!$stmt) {
        throw new Exception('Error en la preparación de la consulta: ' . $conn->error);
    }
    error_log("Consulta SQL preparada correctamente");
    
    error_log("Ejecutando consulta con nombre: '$nombre'");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    error_log("Consulta ejecutada, filas encontradas: " . $result->num_rows);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        error_log("Usuario encontrado: " . print_r($row, true));

        // Verificar si la cuenta está verificada
        error_log("Verificando estado de cuenta...");
        if ($row['verificado'] == 0) {
            throw new Exception('Tu cuenta no ha sido verificada. Por favor revisa tu correo electrónico.');
        }
        error_log("Cuenta verificada correctamente");

        // Verificar contraseña
        error_log("Verificando contraseña...");
        error_log("Contraseña recibida: '$contrasena'");
        error_log("Hash en BD: '" . $row['contrasena'] . "'");
        $password_verify_result = password_verify($contrasena, $row['contrasena']);
        error_log("Resultado password_verify: " . ($password_verify_result ? 'TRUE' : 'FALSE'));
        
        if ($password_verify_result) {
            error_log("Contraseña verificada correctamente, iniciando sesión...");
            // Iniciar sesión
            $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['correo'] = $row['correo'];
            $_SESSION['rol'] = $row['rol'] ?? 'usuario';
            error_log("Sesión iniciada: " . print_r($_SESSION, true));

            // Determinar redirección basada en el rol
            $redirect_url = 'http://127.0.0.1/finoso/index.html'; // Por defecto para usuarios
            
            if (isset($row['rol']) && $row['rol'] === 'administrador') {
                $redirect_url = 'http://127.0.0.1/finoso/admin/panel.php';
                error_log("Usuario es administrador, redirigiendo a panel");
            } else {
                error_log("Usuario normal, redirigiendo a index");
            }

            $response = [
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'redirect' => $redirect_url,
                'rol' => $row['rol'] ?? 'usuario'
            ];
            
            error_log("Respuesta de éxito: " . json_encode($response));
            $stmt->close();
            echo json_encode($response);
            exit;
        } else {
            error_log("ERROR: Contraseña incorrecta");
            throw new Exception('Nombre o contraseña incorrectos');
        }
    } else {
        error_log("ERROR: Usuario no encontrado en BD");
        throw new Exception('Nombre o contraseña incorrectos');
    }

} catch (Exception $e) {
    // Manejo de errores
    error_log("EXCEPCIÓN CAPTURADA: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => 'Error en el proceso de login - Ver logs para más detalles'
    ]);
    exit;
}
?>