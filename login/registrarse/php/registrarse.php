<?php
header('Content-Type: application/json');
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

// Validar que los datos existan
if (!isset($data['nombre']) || !isset($data['contrasena']) || !isset($data['correo'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan datos requeridos'
    ]);
    exit;
}

$nombre = $data['nombre'];
$correo = $data['correo'];
$contrasena = password_hash($data['contrasena'], PASSWORD_DEFAULT);
$fecha = date("Y-m-d H:i:s");
$token = bin2hex(random_bytes(16)); // token de 32 caracteres

// Verificar si ya existe el correo
$check_query = "SELECT * FROM usuario WHERE correo = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Este correo ya está registrado. <a href="../iniciar/iniciar_sesion.html" style="color: #FFCF66;">Inicia sesión aquí</a>'
    ]);
    exit;
}

// Verificar si ya existe el nombre de usuario
$check_nombre = "SELECT * FROM usuario WHERE nombre = ?";
$stmt_nombre = $conn->prepare($check_nombre);
$stmt_nombre->bind_param("s", $nombre);
$stmt_nombre->execute();
$result_nombre = $stmt_nombre->get_result();

if ($result_nombre->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Este nombre de usuario ya está en uso. Por favor elige otro.'
    ]);
    exit;
}

// Insertar usuario con token y no verificado
$query = "INSERT INTO usuario (nombre, correo, contrasena, creado_en, verificado, token)
          VALUES (?, ?, ?, ?, 0, ?)";
$stmt_insert = $conn->prepare($query);
$stmt_insert->bind_param("sssss", $nombre, $correo, $contrasena, $fecha, $token);

if ($stmt_insert->execute()) {
    // Enviar email
    include 'enviar_correo.php';
    enviarCorreoVerificacion($correo, $token);
    
    echo json_encode([
        'success' => true,
        'message' => '¡Registro exitoso! 📧 Revisa tu correo para verificar tu cuenta.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al registrar. Por favor intenta de nuevo.'
    ]);
}

$stmt->close();
$stmt_nombre->close();
$stmt_insert->close();
$conn->close();
?>
}

// Verificar si ya existe el nombre de usuario
$check_nombre = "SELECT * FROM usuario WHERE nombre = ?";
$stmt_nombre = $conn->prepare($check_nombre);
$stmt_nombre->bind_param("s", $nombre);
$stmt_nombre->execute();
$result_nombre = $stmt_nombre->get_result();

if ($result_nombre->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Este nombre de usuario ya está en uso. Por favor elige otro.'
    ]);
    exit;
}

// Insertar usuario con token y no verificado
$query = "INSERT INTO usuario (nombre, correo, contrasena, creado_en, verificado, token)
          VALUES (?, ?, ?, ?, 0, ?)";
$stmt_insert = $conn->prepare($query);
$stmt_insert->bind_param("sssss", $nombre, $correo, $contrasena, $fecha, $token);

if ($stmt_insert->execute()) {
    // Enviar email
    include 'enviar_correo.php';
    enviarCorreoVerificacion($correo, $token);
    
    echo json_encode([
        'success' => true,
        'message' => '¡Registro exitoso! 📧 Revisa tu correo para verificar tu cuenta.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al registrar. Por favor intenta de nuevo.'
    ]);
}

$stmt->close();
$stmt_nombre->close();
$stmt_insert->close();
$conn->close();
?>