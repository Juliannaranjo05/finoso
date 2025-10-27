<?php
/**
 * OBTENER CÓDIGOS DE DESCUENTO DEL USUARIO - FINOSO
 * Retorna los códigos de descuento asignados al usuario
 */

session_start();
header('Content-Type: application/json');

try {
    // Verificar que el usuario esté autenticado
    if (!isset($_SESSION['id_usuario'])) {
        echo json_encode([
            'success' => false,
            'message' => 'No hay sesión activa'
        ]);
        exit;
    }

    // Incluir conexión a la base de datos
    require_once '../../admin/conexion.php';

    $id_usuario = $_SESSION['id_usuario'];
    
    // Consulta para obtener los códigos del usuario con información completa
    $query = "
        SELECT 
            ucd.id_usuario_codigo,
            ucd.fecha_asignado,
            ucd.fecha_usado,
            ucd.activo,
            ucd.notas,
            cd.id_codigo,
            cd.codigo,
            cd.porcentaje,
            cd.fecha_expiracion,
            o.id_orden,
            o.fecha as fecha_orden
        FROM usuario_codigo_descuento ucd
        INNER JOIN codigo_descuento cd ON ucd.id_codigo = cd.id_codigo
        LEFT JOIN orden o ON ucd.id_orden = o.id_orden
        WHERE ucd.id_usuario = ?
        ORDER BY 
            CASE 
                WHEN cd.fecha_expiracion IS NULL THEN 0
                WHEN cd.fecha_expiracion >= CURDATE() THEN 1
                ELSE 2
            END,
            ucd.activo DESC,
            ucd.fecha_asignado DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $codigos = [];
    $fecha_actual = date('Y-m-d');
    
    while ($row = $result->fetch_assoc()) {
        // Determinar el estado del código
        $estado = 'disponible';
        $dias_para_expirar = null;
        
        // 1. Verificar si fue usado (fecha_usado existe O activo = 0)
        if ($row['fecha_usado'] || !$row['activo']) {
            $estado = 'usado';
        }
        // 2. Verificar si expiró (solo si no fue usado)
        else if ($row['fecha_expiracion']) {
            $fecha_exp = new DateTime($row['fecha_expiracion']);
            $fecha_hoy = new DateTime($fecha_actual);
            $diff = $fecha_hoy->diff($fecha_exp);
            $dias_para_expirar = ($fecha_exp > $fecha_hoy) ? $diff->days : -$diff->days;
            
            if ($row['fecha_expiracion'] < $fecha_actual) {
                $estado = 'expirado';
            }
        }
        
        // Calcular días para expirar solo si no está usado
        if ($estado !== 'usado' && $row['fecha_expiracion']) {
            $fecha_exp = new DateTime($row['fecha_expiracion']);
            $fecha_hoy = new DateTime($fecha_actual);
            $diff = $fecha_hoy->diff($fecha_exp);
            $dias_para_expirar = ($fecha_exp > $fecha_hoy) ? $diff->days : -$diff->days;
        }
        
        $codigos[] = [
            'id_usuario_codigo' => $row['id_usuario_codigo'],
            'codigo' => $row['codigo'],
            'porcentaje' => floatval($row['porcentaje']),
            'fecha_expiracion' => $row['fecha_expiracion'],
            'dias_para_expirar' => $dias_para_expirar,
            'fecha_asignado' => $row['fecha_asignado'],
            'fecha_usado' => $row['fecha_usado'],
            'activo' => boolval($row['activo']),
            'estado' => $estado,
            'notas' => $row['notas'],
            'id_orden' => $row['id_orden']
        ];
    }
    
    // Estadísticas
    $stats = [
        'total' => count($codigos),
        'disponibles' => count(array_filter($codigos, fn($c) => $c['estado'] === 'disponible')),
        'usados' => count(array_filter($codigos, fn($c) => $c['estado'] === 'usado')),
        'expirados' => count(array_filter($codigos, fn($c) => $c['estado'] === 'expirado'))
    ];
    
    echo json_encode([
        'success' => true,
        'codigos' => $codigos,
        'stats' => $stats
    ]);
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log("Error al obtener códigos del usuario: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage()
    ]);
}
?>