<?php
// Habilitar visualización de errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Capturar el output en un buffer para poder enviarlo como JSON después
ob_start();

try {
    session_start();
    
    // Incluir archivo de conexión
    if (!file_exists('conexion.php')) {
        throw new Exception('El archivo conexion.php no existe');
    }
    include 'conexion.php';
    
    // Verificar conexión
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Error de conexión a la base de datos: ' . (isset($conn) ? $conn->connect_error : 'Variable $conn no definida'));
    }

    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido: debe ser POST');
    }

    if (!isset($_POST['action']) || $_POST['action'] !== 'login') {
        throw new Exception('Acción no válida');
    }

    // Obtener y limpiar datos
    $nombre = isset($_POST['nombre']) ? mysqli_real_escape_string($conn, trim($_POST['nombre'])) : '';
    $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : ''; // Cambiado a "contresena" (sin ñ)

    // Validar campos
    if (empty($nombre) || empty($contrasena)) {
        throw new Exception('Por favor, completa todos los campos');
    }

    // Consulta a la base de datos
    $query = "SELECT * FROM usuario WHERE nombre = '$nombre'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception('Error en la consulta: ' . mysqli_error($conn));
    }

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Verificar si la cuenta está verificada
        if ($row['verificado'] == 0) {
            throw new Exception('Tu cuenta no ha sido verificada. Por favor revisa tu correo electrónico.');
        }

        // Verificar contraseña (usa el nombre de columna correcto de tu BD)
        if (password_verify($contrasena, $row['contraseña'])) { // Asegúrate que coincida con el nombre en tu BD
            // Iniciar sesión
            $_SESSION['id_nombre'] = $row['id'];
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['email'] = $row['email'];

            $response = [
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'redirect' => 'http://127.0.0.1/finoso/index.html'
            ];
        } else {
            throw new Exception('Nombre o contraseña incorrectos');
        }
    } else {
        throw new Exception('Nombre o contraseña incorrectos');
    }

} catch (Exception $e) {
    // Capturar cualquier error
    $error = ob_get_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => $error ?: 'No hay información de depuración adicional'
    ]);
    exit;
}

// Si llegamos aquí, todo salió bien
$output = ob_get_clean();
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>