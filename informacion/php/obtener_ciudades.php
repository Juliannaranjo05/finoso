<?php
// Obtener ciudades y departamentos desde la base de datos

// Headers primero
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'conexion.php';

$action = $_GET['action'] ?? '';

try {
    error_log("🔍 DEBUG obtener_ciudades.php - Acción solicitada: " . $action);
    
    switch ($action) {
        case 'departamentos':
            error_log("🔍 DEBUG obtener_ciudades.php - Procesando departamentos");
            // Obtener lista de departamentos únicos con ciudades activas
            $stmt = $conn->prepare("
                SELECT DISTINCT departamento 
                FROM envios 
                WHERE activo = 1 
                ORDER BY departamento ASC
            ");
            
            if (!$stmt) {
                error_log("🔍 DEBUG obtener_ciudades.php - Error al preparar consulta: " . $conn->error);
                throw new Exception('Error al preparar consulta: ' . $conn->error);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $departamentos = [];
            while ($row = $result->fetch_assoc()) {
                // Limpiar UTF-8
                $depto = mb_convert_encoding($row['departamento'], 'UTF-8', 'UTF-8');
                $departamentos[] = $depto;
                error_log("🔍 DEBUG obtener_ciudades.php - Departamento encontrado: " . $depto);
            }
            
            error_log("🔍 DEBUG obtener_ciudades.php - Total departamentos: " . count($departamentos));
            
            $json = json_encode([
                'success' => true,
                'departamentos' => $departamentos,
                'total' => count($departamentos)
            ], JSON_INVALID_UTF8_SUBSTITUTE);
            
            if ($json === false) {
                error_log("🔍 DEBUG obtener_ciudades.php - Error al generar JSON: " . json_last_error_msg());
                throw new Exception('Error al generar JSON: ' . json_last_error_msg());
            }
            
            error_log("🔍 DEBUG obtener_ciudades.php - JSON generado exitosamente");
            echo $json;
            $stmt->close();
            break;
            
        case 'ciudades':
            error_log("🔍 DEBUG obtener_ciudades.php - Procesando ciudades");
            // Obtener ciudades de un departamento específico
            $departamento = $_GET['departamento'] ?? '';
            error_log("🔍 DEBUG obtener_ciudades.php - Departamento solicitado: " . $departamento);
            
            if (empty($departamento)) {
                error_log("🔍 DEBUG obtener_ciudades.php - Departamento vacío");
                echo json_encode([
                    'success' => false,
                    'message' => 'Departamento no especificado'
                ]);
                exit;
            }
            
            $stmt = $conn->prepare("
                SELECT ciudad, precio, dias_estimados 
                FROM envios 
                WHERE departamento = ? AND activo = 1 
                ORDER BY ciudad ASC
            ");
            
            if (!$stmt) {
                error_log("🔍 DEBUG obtener_ciudades.php - Error al preparar consulta ciudades: " . $conn->error);
                throw new Exception('Error al preparar consulta: ' . $conn->error);
            }
            
            $stmt->bind_param("s", $departamento);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $ciudades = [];
            while ($row = $result->fetch_assoc()) {
                // Limpiar UTF-8
                $ciudad = mb_convert_encoding($row['ciudad'], 'UTF-8', 'UTF-8');
                $precio = floatval($row['precio']);
                $dias = intval($row['dias_estimados']);
                
                $ciudades[] = [
                    'ciudad' => $ciudad,
                    'precio' => $precio,
                    'dias_estimados' => $dias
                ];
                
                error_log("🔍 DEBUG obtener_ciudades.php - Ciudad encontrada: " . $ciudad . " Precio: " . $precio . " Días: " . $dias);
            }
            
            error_log("🔍 DEBUG obtener_ciudades.php - Total ciudades: " . count($ciudades));
            
            $json = json_encode([
                'success' => true,
                'ciudades' => $ciudades,
                'total' => count($ciudades)
            ], JSON_INVALID_UTF8_SUBSTITUTE);
            
            if ($json === false) {
                error_log("🔍 DEBUG obtener_ciudades.php - Error al generar JSON ciudades: " . json_last_error_msg());
                throw new Exception('Error al generar JSON: ' . json_last_error_msg());
            }
            
            error_log("🔍 DEBUG obtener_ciudades.php - JSON ciudades generado exitosamente");
            echo $json;
            $stmt->close();
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
    }
} catch (Exception $e) {
    error_log("🔍 DEBUG obtener_ciudades.php - Error capturado: " . $e->getMessage());
    error_log("🔍 DEBUG obtener_ciudades.php - Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
