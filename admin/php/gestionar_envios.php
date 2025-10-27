<?php
// Gestión de envíos - CRUD completo

session_start();

// Verificar que el usuario es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header('Content-Type: application/json');
    http_response_code(403);
    error_log("❌ Acceso denegado - Sesión: " . print_r($_SESSION, true));
    echo json_encode([
        'success' => false, 
        'message' => 'Acceso denegado. Rol requerido: administrador. Rol actual: ' . ($_SESSION['rol'] ?? 'no definido'),
        'session_data' => $_SESSION
    ]);
    exit;
}

// Enviar headers DESPUÉS de verificar sesión
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../informacion/php/conexion.php';

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'listar':
            // Verificar si la tabla existe
            $check_table = $conn->query("SHOW TABLES LIKE 'envios'");
            if ($check_table->num_rows === 0) {
                throw new Exception('La tabla "envios" no existe en la base de datos. Por favor, ejecuta el archivo finoso.sql completo.');
            }
            
            // Obtener todos los envíos con filtros opcionales
            $departamento = $_GET['departamento'] ?? '';
            $busqueda = $_GET['busqueda'] ?? '';
            
            $sql = "SELECT * FROM envios WHERE 1=1";
            $params = [];
            $types = '';
            
            if (!empty($departamento)) {
                $sql .= " AND departamento = ?";
                $params[] = $departamento;
                $types .= 's';
            }
            
            if (!empty($busqueda)) {
                $sql .= " AND (ciudad LIKE ? OR departamento LIKE ?)";
                $busqueda_param = "%$busqueda%";
                $params[] = $busqueda_param;
                $params[] = $busqueda_param;
                $types .= 'ss';
            }
            
            $sql .= " ORDER BY departamento ASC, ciudad ASC";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Error al preparar consulta: ' . $conn->error);
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                throw new Exception('Error al ejecutar consulta: ' . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            $envios = [];
            while ($row = $result->fetch_assoc()) {
                // Limpiar caracteres UTF-8 inválidos
                foreach ($row as $key => $value) {
                    if (is_string($value)) {
                        $row[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    }
                }
                $envios[] = $row;
            }
            
            $stmt->close();
            
            $response = ['success' => true, 'envios' => $envios, 'total' => count($envios)];
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            
            $conn->close();
            exit; // CRÍTICO
            

        case 'departamentos':
            error_log('[DEPARTAMENTOS] Iniciando consulta de departamentos');
            
            // Obtener lista única de departamentos
            $stmt = $conn->prepare("SELECT DISTINCT departamento FROM envios ORDER BY departamento ASC");
            if (!$stmt) {
                error_log('[DEPARTAMENTOS] Error preparando consulta: ' . $conn->error);
                echo json_encode(['success' => false, 'message' => 'Error preparando consulta']);
                $conn->close();
                exit;
            }
            
            if (!$stmt->execute()) {
                error_log('[DEPARTAMENTOS] Error ejecutando consulta: ' . $stmt->error);
                echo json_encode(['success' => false, 'message' => 'Error ejecutando consulta']);
                $stmt->close();
                $conn->close();
                exit;
            }
            
            $result = $stmt->get_result();
            $departamentos = [];
            
            while ($row = $result->fetch_assoc()) {
                // Limpiar encoding
                $depto = mb_convert_encoding($row['departamento'], 'UTF-8', 'UTF-8');
                $departamentos[] = $depto;
            }
            
            $stmt->close();
            
            error_log('[DEPARTAMENTOS] Departamentos encontrados: ' . count($departamentos) . ' - ' . implode(', ', $departamentos));
            
            $response = ['success' => true, 'departamentos' => $departamentos];
            
            $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            
            if ($json === false) {
                error_log('[DEPARTAMENTOS] Error en json_encode: ' . json_last_error_msg());
                echo json_encode(['success' => false, 'message' => 'Error al codificar JSON']);
            } else {
                error_log('[DEPARTAMENTOS] Enviando respuesta: ' . $json);
                echo $json;
            }
            
            $conn->close();
            exit; // CRÍTICO - Sin esto, el código sigue ejecutándose
            

        case 'crear':
            // Validar datos requeridos
            $departamento = trim($_POST['departamento'] ?? '');
            $ciudad = trim($_POST['ciudad'] ?? '');
            $precio = floatval($_POST['precio'] ?? 0);
            $dias_estimados = intval($_POST['dias_estimados'] ?? 3);

            if (empty($departamento)) {
                throw new Exception('El departamento es requerido');
            }

            if (empty($ciudad)) {
                throw new Exception('La ciudad es requerida');
            }

            if ($precio <= 0) {
                throw new Exception('El precio debe ser mayor a 0');
            }

            if ($dias_estimados < 1 || $dias_estimados > 30) {
                throw new Exception('Los días estimados deben estar entre 1 y 30');
            }

            // Verificar que la combinación ciudad-departamento no exista
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM envios WHERE ciudad = ? AND departamento = ?");
            $stmt->bind_param("ss", $ciudad, $departamento);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if ($row['count'] > 0) {
                throw new Exception('Ya existe un registro para esta ciudad y departamento');
            }

            // Insertar nuevo envío
            $stmt = $conn->prepare("
                INSERT INTO envios (departamento, ciudad, precio, dias_estimados) 
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->bind_param("ssdi", $departamento, $ciudad, $precio, $dias_estimados);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Envío creado exitosamente',
                    'id_envio' => $stmt->insert_id
                ]);
            } else {
                throw new Exception('Error al crear el envío');
            }
            
            $stmt->close();
            $conn->close();
            exit; // CRÍTICO
            

        case 'actualizar':
            $id_envio = intval($_POST['id_envio'] ?? 0);
            $departamento = trim($_POST['departamento'] ?? '');
            $ciudad = trim($_POST['ciudad'] ?? '');
            $precio = floatval($_POST['precio'] ?? 0);
            $dias_estimados = intval($_POST['dias_estimados'] ?? 3);
            $activo = intval($_POST['activo'] ?? 1);

            if ($id_envio <= 0) {
                throw new Exception('ID de envío inválido');
            }

            if (empty($departamento)) {
                throw new Exception('El departamento es requerido');
            }

            if (empty($ciudad)) {
                throw new Exception('La ciudad es requerida');
            }

            if ($precio <= 0) {
                throw new Exception('El precio debe ser mayor a 0');
            }

            if ($dias_estimados < 1 || $dias_estimados > 30) {
                throw new Exception('Los días estimados deben estar entre 1 y 30');
            }

            // Verificar que la combinación ciudad-departamento no exista en otro registro
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM envios 
                WHERE ciudad = ? AND departamento = ? AND id_envio != ?
            ");
            $stmt->bind_param("ssi", $ciudad, $departamento, $id_envio);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if ($row['count'] > 0) {
                throw new Exception('Ya existe un registro para esta ciudad y departamento');
            }

            // Actualizar envío
            $stmt = $conn->prepare("
                UPDATE envios 
                SET departamento = ?, ciudad = ?, precio = ?, dias_estimados = ?, activo = ?
                WHERE id_envio = ?
            ");
            
            $stmt->bind_param("ssdiii", $departamento, $ciudad, $precio, $dias_estimados, $activo, $id_envio);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0 || $conn->affected_rows >= 0) {
                    echo json_encode(['success' => true, 'message' => 'Envío actualizado exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró el envío o no hubo cambios']);
                }
            } else {
                throw new Exception('Error al actualizar el envío');
            }
            
            $stmt->close();
            $conn->close();
            exit; // CRÍTICO
            

        case 'eliminar':
            $id_envio = intval($_POST['id_envio'] ?? 0);

            if ($id_envio <= 0) {
                throw new Exception('ID de envío inválido');
            }

            $stmt = $conn->prepare("DELETE FROM envios WHERE id_envio = ?");
            $stmt->bind_param("i", $id_envio);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Envío eliminado exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró el envío']);
                }
            } else {
                throw new Exception('Error al eliminar el envío');
            }
            
            $stmt->close();
            $conn->close();
            exit; // CRÍTICO
            

        case 'toggle_activo':
            // Activar/Desactivar envío
            $id_envio = intval($_POST['id_envio'] ?? 0);

            if ($id_envio <= 0) {
                throw new Exception('ID de envío inválido');
            }

            $stmt = $conn->prepare("UPDATE envios SET activo = NOT activo WHERE id_envio = ?");
            $stmt->bind_param("i", $id_envio);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Estado actualizado exitosamente']);
            } else {
                throw new Exception('Error al actualizar el estado');
            }
            
            $stmt->close();
            $conn->close();
            exit; // CRÍTICO
            

        case 'estadisticas':
            // Obtener estadísticas de envíos
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(*) as total_ciudades,
                    COUNT(DISTINCT departamento) as total_departamentos,
                    AVG(precio) as precio_promedio,
                    MIN(precio) as precio_minimo,
                    MAX(precio) as precio_maximo,
                    AVG(dias_estimados) as dias_promedio
                FROM envios
                WHERE activo = 1
            ");
            
            $stmt->execute();
            $result = $stmt->get_result();
            $stats = $result->fetch_assoc();
            
            echo json_encode(['success' => true, 'estadisticas' => $stats]);
            $stmt->close();
            $conn->close();
            exit; // CRÍTICO
            

        default:
            throw new Exception('Acción no válida');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    if (isset($conn)) {
        $conn->close();
    }
    exit;
}