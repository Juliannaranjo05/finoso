<?php
/**
 * OBTENER DESCUENTO APLICADO
 * Verifica si el usuario tiene un descuento aplicado a un reloj específico
 */

session_start();
header('Content-Type: application/json');
require_once '../../admin/php/logger.php';
include 'conexion.php';

$response = [
    'tiene_descuento' => false,
    'codigo' => '',
    'porcentaje' => 0,
    'precio_original' => 0,
    'precio_con_descuento' => 0,
    'debug' => []
];

$id_reloj = intval($_GET['id_reloj'] ?? 0);
$id_usuario = $_SESSION['id_usuario'] ?? null;

escribir_log('[OBTENER-DESCUENTO] Solicitud recibida - ID Usuario: ' . ($id_usuario ?? 'NULL') . ', ID Reloj: ' . $id_reloj, 'DEBUG');
$response['debug'][] = 'ID Usuario: ' . ($id_usuario ?? 'NULL');
$response['debug'][] = 'ID Reloj: ' . $id_reloj;

if ($id_reloj <= 0 || !$id_usuario) {
    escribir_log('[OBTENER-DESCUENTO] ❌ Validación fallida - ID inválido o sin sesión', 'WARNING');
    $response['debug'][] = 'Validación fallida: ID inválido o sin sesión';
    echo json_encode($response);
    exit;
}

try {
    // Buscar código aplicado a este reloj
    $query = "
        SELECT ucd.*, cd.codigo, cd.porcentaje
        FROM usuario_codigo_descuento ucd
        JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
        WHERE ucd.id_usuario = ? 
          AND ucd.id_reloj = ?
          AND ucd.activo = 0
          AND ucd.id_orden IS NULL
    ";
    
    escribir_log('[OBTENER-DESCUENTO] Ejecutando query - Usuario: ' . $id_usuario . ', Reloj: ' . $id_reloj, 'DEBUG');
    $response['debug'][] = 'Query preparada para buscar código aplicado';
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id_usuario, $id_reloj);
    $stmt->execute();
    $result = $stmt->get_result();
    $codigo_aplicado = $result->fetch_assoc();
    $stmt->close();
    
    escribir_log('[OBTENER-DESCUENTO] Resultado query: ' . ($codigo_aplicado ? 'ENCONTRADO' : 'NO ENCONTRADO'), 'DEBUG');
    $response['debug'][] = 'Código aplicado: ' . ($codigo_aplicado ? 'SÍ' : 'NO');
    
    if ($codigo_aplicado) {
        escribir_log('[OBTENER-DESCUENTO] ✓ Código encontrado: ' . $codigo_aplicado['codigo'] . ' (' . $codigo_aplicado['porcentaje'] . '%)', 'INFO');
        $response['debug'][] = 'Código: ' . $codigo_aplicado['codigo'];
        // Obtener precio actual del reloj
        $stmt_reloj = $conn->prepare("SELECT precio, descuento FROM reloj WHERE id_reloj = ?");
        $stmt_reloj->bind_param("i", $id_reloj);
        $stmt_reloj->execute();
        $reloj = $stmt_reloj->get_result()->fetch_assoc();
        $stmt_reloj->close();
        
        if ($reloj) {
            $precio_base = $reloj['precio'];
            $descuento_reloj = $reloj['descuento'] ?? 0;
            $precio_original = $precio_base - ($precio_base * $descuento_reloj);
            $porcentaje = $codigo_aplicado['porcentaje'];
            $precio_con_descuento = $precio_original - ($precio_original * ($porcentaje / 100));
            
            escribir_log('[OBTENER-DESCUENTO] ✓ Calculando precios - Original: ' . $precio_original . ', Con descuento: ' . $precio_con_descuento, 'DEBUG');
            
            $response['tiene_descuento'] = true;
            $response['codigo'] = $codigo_aplicado['codigo'];
            $response['porcentaje'] = $porcentaje;
            $response['precio_original'] = $precio_original;
            $response['precio_con_descuento'] = $precio_con_descuento;
            $response['debug'][] = 'Precio original: ' . $precio_original;
            $response['debug'][] = 'Precio con descuento: ' . $precio_con_descuento;
            
            escribir_log('[OBTENER-DESCUENTO] ✓ Respuesta exitosa enviada', 'SUCCESS');
        } else {
            escribir_log('[OBTENER-DESCUENTO] ❌ No se encontró el reloj en BD', 'ERROR');
            $response['debug'][] = 'Error: Reloj no encontrado';
        }
    } else {
        escribir_log('[OBTENER-DESCUENTO] ℹ️ No hay código aplicado para este reloj', 'INFO');
        $response['debug'][] = 'No hay código aplicado a este reloj';
    }
    
} catch (Exception $e) {
    escribir_log('[OBTENER-DESCUENTO] ✗ Error de BD: ' . $e->getMessage(), 'ERROR');
    $response['debug'][] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>