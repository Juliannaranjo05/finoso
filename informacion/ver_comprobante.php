<?php
/**
 * VER COMPROBANTE DE PAGO
 * Muestra el comprobante de una orden del usuario autenticado
 */

session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    die('<html><head><meta charset="UTF-8"></head><body style="font-family: Arial; text-align: center; padding: 50px; background: #1a1a1a; color: #fff;"><h2>⚠️ Acceso Denegado</h2><p>Debes iniciar sesión para ver tus comprobantes</p></body></html>');
}

// Obtener ID de orden
$id_orden = isset($_GET['orden']) ? (int)$_GET['orden'] : 0;

if ($id_orden === 0) {
    die('<html><head><meta charset="UTF-8"></head><body style="font-family: Arial; text-align: center; padding: 50px; background: #1a1a1a; color: #fff;"><h2>⚠️ Orden inválida</h2></body></html>');
}

// Conexión a BD
include '../admin/conexion.php';

// Verificar que la orden pertenezca al usuario
$stmt = $conn->prepare("SELECT comprobante_pago, nombre_archivo_comprobante, id_usuario, correo 
                        FROM orden 
                        WHERE id_orden = ?");
$stmt->bind_param("i", $id_orden);
$stmt->execute();
$result = $stmt->get_result();
$orden = $result->fetch_assoc();
$stmt->close();

if (!$orden) {
    die('<html><head><meta charset="UTF-8"></head><body style="font-family: Arial; text-align: center; padding: 50px; background: #1a1a1a; color: #fff;"><h2>⚠️ Orden no encontrada</h2></body></html>');
}

// Verificar que la orden sea del usuario actual
$es_usuario_valido = ($orden['id_usuario'] == $_SESSION['id_usuario']);

// Si no coincide por ID, verificar por correo
if (!$es_usuario_valido && isset($_SESSION['correo_usuario'])) {
    $es_usuario_valido = ($orden['correo'] === $_SESSION['correo_usuario']);
}

if (!$es_usuario_valido) {
    die('<html><head><meta charset="UTF-8"></head><body style="font-family: Arial; text-align: center; padding: 50px; background: #1a1a1a; color: #fff;"><h2>⚠️ No tienes permiso para ver este comprobante</h2></body></html>');
}

if (!$orden['comprobante_pago']) {
    die('<html><head><meta charset="UTF-8"></head><body style="font-family: Arial; text-align: center; padding: 50px; background: #1a1a1a; color: #fff;"><h2>⚠️ Esta orden no tiene comprobante</h2></body></html>');
}

// Decodificar la imagen
$imagen_data = base64_decode($orden['comprobante_pago']);
$nombre_archivo = $orden['nombre_archivo_comprobante'] ?: 'comprobante.jpg';

// Detectar el tipo de imagen
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->buffer($imagen_data);

// Validar que sea una imagen
if (strpos($mime_type, 'image/') !== 0) {
    $mime_type = 'image/jpeg'; // Default
}

// Enviar headers
header('Content-Type: ' . $mime_type);
header('Content-Disposition: inline; filename="' . $nombre_archivo . '"');
header('Content-Length: ' . strlen($imagen_data));
header('Cache-Control: private, max-age=0, must-revalidate');

// Enviar la imagen
echo $imagen_data;

$conn->close();
?>

