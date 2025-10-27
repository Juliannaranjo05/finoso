<?php
// Iniciar sesión
session_start();

// Verificar que el usuario esté logueado y sea administrador
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    die('Acceso denegado: se requiere permisos de administrador');
}

// Obtener ID de la orden
$id_orden = (int)($_GET['id'] ?? 0);

if ($id_orden <= 0) {
    die('ID de orden inválido');
}

try {
    // Incluir conexión a la base de datos
    include '../../login/php/conexion.php';

    // Verificar conexión
    if ($conn->connect_error) {
        die('Error de conexión a la base de datos: ' . $conn->connect_error);
    }

    // Obtener información de la orden y comprobante
    $stmt = $conn->prepare("
        SELECT 
            o.id_orden,
            o.nombre,
            o.total,
            o.fecha,
            o.comprobante_pago,
            o.nombre_archivo_comprobante
        FROM orden o
        WHERE o.id_orden = ?
    ");
    
    $stmt->bind_param("i", $id_orden);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die('Orden no encontrada');
    }
    
    $orden = $result->fetch_assoc();
    $stmt->close();

    // Verificar si hay comprobante
    if (empty($orden['comprobante_pago']) || empty($orden['nombre_archivo_comprobante'])) {
        die('No se encontró comprobante para esta orden');
    }

    // Obtener información del archivo
    $ruta_comprobante = '../../informacion/php/comprobantes/' . $orden['comprobante_pago'];
    
    if (!file_exists($ruta_comprobante)) {
        die('El archivo del comprobante no existe en el servidor');
    }

    // Determinar el tipo de contenido
    $extension = strtolower(pathinfo($orden['nombre_archivo_comprobante'], PATHINFO_EXTENSION));
    
    switch ($extension) {
        case 'pdf':
            $content_type = 'application/pdf';
            break;
        case 'jpg':
        case 'jpeg':
            $content_type = 'image/jpeg';
            break;
        case 'png':
            $content_type = 'image/png';
            break;
        case 'gif':
            $content_type = 'image/gif';
            break;
        default:
            $content_type = 'application/octet-stream';
    }

    // Configurar headers para descarga/visualización
    header('Content-Type: ' . $content_type);
    header('Content-Disposition: inline; filename="' . $orden['nombre_archivo_comprobante'] . '"');
    header('Content-Length: ' . filesize($ruta_comprobante));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');

    // Leer y enviar el archivo
    readfile($ruta_comprobante);
    exit;

} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

$conn->close();
?>