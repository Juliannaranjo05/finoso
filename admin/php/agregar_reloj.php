<?php
session_start();
header('Content-Type: application/json');

// Verificar que el usuario esté logueado (sin verificar rol específico por ahora)
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permisos para realizar esta acción'
    ]);
    exit();
}

require_once __DIR__ . '/../../php/conexion.php';

try {
    // Debug: Log de datos recibidos
    error_log("=== DEBUG AGREGAR RELOJ ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));
    
    // Validar que se recibieron todos los campos obligatorios (excepto imagen que se valida por separado)
    $campos_obligatorios = ['marca', 'nombre', 'descripcion', 'precio'];
    foreach ($campos_obligatorios as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            throw new Exception("El campo $campo es obligatorio");
        }
    }
    
    // Validar archivo de imagen principal (obligatorio)
    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        $error_code = isset($_FILES['imagen']['error']) ? $_FILES['imagen']['error'] : 'NO_FILE';
        throw new Exception("El campo imagen principal es obligatorio. Error code: $error_code");
    }
    
    $archivo_principal = $_FILES['imagen'];
    $archivo_lateral = isset($_FILES['imagen_lateral']) && $_FILES['imagen_lateral']['error'] === UPLOAD_ERR_OK ? $_FILES['imagen_lateral'] : null;
    $archivo_detalle = isset($_FILES['imagen_detalle']) && $_FILES['imagen_detalle']['error'] === UPLOAD_ERR_OK ? $_FILES['imagen_detalle'] : null;
    
    // Función para validar y subir imagen
    function validarYSubirImagen($archivo, $prefijo) {
        if (!$archivo) return null;
        
        $extensiones_validas = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $extensiones_validas)) {
            throw new Exception("Solo se permiten archivos JPG, PNG y WebP para $prefijo");
        }
        
        if ($archivo['size'] > 5 * 1024 * 1024) {
            throw new Exception("La imagen $prefijo es demasiado grande. Máximo 5MB");
        }
        
        // Generar nombre único
        $nombre_archivo = $prefijo . '_' . uniqid() . '.' . $extension;
        $directorio_imagenes = '../../img/';
        $ruta_completa = $directorio_imagenes . $nombre_archivo;
        
        if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            throw new Exception("Error al guardar la imagen $prefijo");
        }
        
        chmod($ruta_completa, 0644);
        return 'img/' . $nombre_archivo;
    }
    
    // Validar y subir imágenes
    $ruta_imagen_principal = validarYSubirImagen($archivo_principal, 'principal');
    $ruta_imagen_lateral = validarYSubirImagen($archivo_lateral, 'lateral');
    $ruta_imagen_detalle = validarYSubirImagen($archivo_detalle, 'detalle');
    
    // Limpiar y validar datos
    $marca = trim($_POST['marca']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $eslabones = isset($_POST['eslabones']) ? trim($_POST['eslabones']) : null;
    $tipo_bisel = isset($_POST['tipo_bisel']) ? trim($_POST['tipo_bisel']) : null;
    $movimiento = isset($_POST['movimiento']) ? trim($_POST['movimiento']) : null;
    $pulsera = isset($_POST['pulsera']) ? trim($_POST['pulsera']) : null;
    $peso = isset($_POST['peso']) ? trim($_POST['peso']) : null;
    $resistencia_agua = isset($_POST['resistencia_agua']) ? trim($_POST['resistencia_agua']) : null;
    $precio = floatval($_POST['precio']);
    $descuento = isset($_POST['descuento']) && !empty($_POST['descuento']) 
                ? floatval($_POST['descuento']) 
                : null;
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    $vendido = isset($_POST['vendido']) ? 1 : 0;
    
    // Validaciones adicionales
    if (strlen($marca) > 100) {
        throw new Exception("La marca no puede tener más de 100 caracteres");
    }
    
    if (strlen($nombre) > 100) {
        throw new Exception("El nombre no puede tener más de 100 caracteres");
    }
    
    if ($precio <= 0) {
        throw new Exception("El precio debe ser mayor a 0");
    }
    
    if ($descuento !== null && ($descuento < 0 || $descuento > 100)) {
        throw new Exception("El descuento debe estar entre 0 y 100%");
    }
    
    // Lógica de disponibilidad y venta
    if ($vendido && $disponible) {
        // Si está vendido, no puede estar disponible
        $disponible = 0;
    }
    
    // Crear directorio de imágenes si no existe
    $directorio_imagenes = '../../img/';
    if (!file_exists($directorio_imagenes)) {
        if (!mkdir($directorio_imagenes, 0755, true)) {
            throw new Exception("No se pudo crear el directorio de imágenes");
        }
    }
    
    
    // Guardar en la base de datos
    $conn->begin_transaction();
    
    try {
        $sql = "INSERT INTO reloj (marca, nombre, descripcion, eslabones, tipo_bisel, movimiento, pulsera, peso, resistencia_agua, precio, descuento, img, img_lateral, img_detalle, disponible, vendido) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("sssssssssdssssii", 
            $marca, 
            $nombre, 
            $descripcion,
            $eslabones,
            $tipo_bisel,
            $movimiento,
            $pulsera,
            $peso,
            $resistencia_agua,
            $precio, 
            $descuento,
            $ruta_imagen_principal,
            $ruta_imagen_lateral,
            $ruta_imagen_detalle, 
            $disponible, 
            $vendido
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }
        
        $id_reloj = $stmt->insert_id;
        $stmt->close();
        
        $conn->commit();
        
        // Log de la acción
        error_log("Reloj agregado: ID $id_reloj - $marca $nombre por usuario " . $_SESSION['id_usuario']);
        
        echo json_encode([
            'success' => true,
            'message' => "Reloj '$nombre' agregado exitosamente con ID $id_reloj",
            'id_reloj' => $id_reloj,
            'datos' => [
                'marca' => $marca,
                'nombre' => $nombre,
                'precio' => $precio,
                'descuento' => $descuento,
                'disponible' => $disponible,
                'vendido' => $vendido,
                'imagen_principal' => $ruta_imagen_principal,
                'imagen_lateral' => $ruta_imagen_lateral,
                'imagen_detalle' => $ruta_imagen_detalle
            ]
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        
        // Eliminar imagen si hubo error en la base de datos
        if (file_exists($ruta_completa)) {
            unlink($ruta_completa);
        }
        
        throw $e;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    // Log del error
    error_log("Error al agregar reloj: " . $e->getMessage() . " - Usuario: " . $_SESSION['id_usuario']);
}
?>
