<?php
// Gestión de códigos de descuento - CRUD completo
session_start();

// Verificar que el usuario es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../../informacion/php/conexion.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'listar':
            // Obtener todos los códigos de descuento
            $stmt = $conn->prepare("
                SELECT 
                    id_codigo,
                    codigo,
                    porcentaje,
                    fecha_expiracion,
                    CASE 
                        WHEN fecha_expiracion < CURDATE() THEN 'expirado'
                        WHEN fecha_expiracion = CURDATE() THEN 'expira_hoy'
                        ELSE 'activo'
                    END as estado
                FROM codigo_descuento
                ORDER BY fecha_expiracion DESC
            ");
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $codigos = [];
            while ($row = $result->fetch_assoc()) {
                $codigos[] = $row;
            }
            
            echo json_encode(['success' => true, 'codigos' => $codigos]);
            $stmt->close();
            break;

        case 'crear':
            // Validar datos requeridos
            $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
            $porcentaje = floatval($_POST['porcentaje'] ?? 0);
            $fecha_expiracion = $_POST['fecha_expiracion'] ?? '';

            if (empty($codigo)) {
                throw new Exception('El código es requerido');
            }

            if ($porcentaje <= 0 || $porcentaje > 100) {
                throw new Exception('El porcentaje debe estar entre 0 y 100');
            }

            if (empty($fecha_expiracion)) {
                throw new Exception('La fecha de expiración es requerida');
            }

            // Verificar que el código no exista
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM codigo_descuento WHERE codigo = ?");
            $stmt->bind_param("s", $codigo);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if ($row['count'] > 0) {
                throw new Exception('El código ya existe');
            }

            // Insertar nuevo código
            $stmt = $conn->prepare("
                INSERT INTO codigo_descuento (codigo, porcentaje, fecha_expiracion) 
                VALUES (?, ?, ?)
            ");
            
            // Guardar directamente el porcentaje (ej: 20 para 20%)
            $stmt->bind_param("sds", $codigo, $porcentaje, $fecha_expiracion);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Código creado exitosamente',
                    'id_codigo' => $stmt->insert_id
                ]);
            } else {
                throw new Exception('Error al crear el código');
            }
            
            $stmt->close();
            break;

        case 'actualizar':
            $id_codigo = intval($_POST['id_codigo'] ?? 0);
            $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
            $porcentaje = floatval($_POST['porcentaje'] ?? 0);
            $fecha_expiracion = $_POST['fecha_expiracion'] ?? '';

            if ($id_codigo <= 0) {
                throw new Exception('ID de código inválido');
            }

            if (empty($codigo)) {
                throw new Exception('El código es requerido');
            }

            if ($porcentaje <= 0 || $porcentaje > 100) {
                throw new Exception('El porcentaje debe estar entre 0 y 100');
            }

            if (empty($fecha_expiracion)) {
                throw new Exception('La fecha de expiración es requerida');
            }

            // Verificar que el código no exista en otro registro
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM codigo_descuento 
                WHERE codigo = ? AND id_codigo != ?
            ");
            $stmt->bind_param("si", $codigo, $id_codigo);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if ($row['count'] > 0) {
                throw new Exception('El código ya existe');
            }

            // Actualizar código
            $stmt = $conn->prepare("
                UPDATE codigo_descuento 
                SET codigo = ?, porcentaje = ?, fecha_expiracion = ?
                WHERE id_codigo = ?
            ");
            
            // Guardar directamente el porcentaje (ej: 20 para 20%)
            $stmt->bind_param("sdsi", $codigo, $porcentaje, $fecha_expiracion, $id_codigo);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Código actualizado exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró el código o no hubo cambios']);
                }
            } else {
                throw new Exception('Error al actualizar el código');
            }
            
            $stmt->close();
            break;

        case 'eliminar':
            $id_codigo = intval($_POST['id_codigo'] ?? 0);

            if ($id_codigo <= 0) {
                throw new Exception('ID de código inválido');
            }

            $stmt = $conn->prepare("DELETE FROM codigo_descuento WHERE id_codigo = ?");
            $stmt->bind_param("i", $id_codigo);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Código eliminado exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró el código']);
                }
            } else {
                throw new Exception('Error al eliminar el código');
            }
            
            $stmt->close();
            break;

        case 'verificar':
            // Verificar si un código es válido (para probar desde el admin)
            $codigo = strtoupper(trim($_GET['codigo'] ?? ''));

            if (empty($codigo)) {
                throw new Exception('Código no proporcionado');
            }

            $stmt = $conn->prepare("
                SELECT 
                    id_codigo,
                    codigo,
                    porcentaje,
                    fecha_expiracion,
                    CASE 
                        WHEN fecha_expiracion < CURDATE() THEN 0
                        ELSE 1
                    END as es_valido
                FROM codigo_descuento
                WHERE codigo = ?
            ");
            
            $stmt->bind_param("s", $codigo);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $es_valido = $row['es_valido'] == 1;
                
                echo json_encode([
                    'success' => true,
                    'valido' => $es_valido,
                    'porcentaje' => $es_valido ? floatval($row['porcentaje']) : 0,
                    'mensaje' => $es_valido ? 'Código válido' : 'Código expirado'
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'valido' => false,
                    'mensaje' => 'Código no encontrado'
                ]);
            }
            
            $stmt->close();
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
