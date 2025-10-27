<?php
/**
 * APLICAR CÓDIGO DE DESCUENTO
 * Valida, aplica y marca el código como usado cuando se usa en una compra
 */

session_start();
header('Content-Type: application/json');
require_once '../../admin/php/logger.php';
include 'conexion.php';

$response = [
    'success' => false,
    'mensaje' => '',
    'porcentaje' => 0,
    'codigo' => ''
];

// Obtener datos
$codigo = strtoupper(trim($_POST['codigo'] ?? ''));
$id_reloj = intval($_POST['id_reloj'] ?? 0);
$id_usuario = $_SESSION['id_usuario'] ?? null;

escribir_log('[APLICAR-CODIGO] Intento de aplicación - Código: ' . $codigo . ', ID Reloj: ' . $id_reloj . ', ID Usuario: ' . ($id_usuario ?? 'NULL'), 'INFO');

// Validación básica
if (empty($codigo)) {
    $response['mensaje'] = 'Por favor ingresa un código de descuento.';
    escribir_log('[APLICAR-CODIGO] Error: Código vacío', 'WARNING');
    echo json_encode($response);
    exit;
}

if ($id_reloj <= 0) {
    $response['mensaje'] = 'Reloj no válido.';
    escribir_log('[APLICAR-CODIGO] Error: ID reloj inválido', 'WARNING');
    echo json_encode($response);
    exit;
}

try {
    // 1. Buscar el código y verificar validez
    $stmt = $conn->prepare("
        SELECT cd.id_codigo, cd.porcentaje, cd.fecha_expiracion,
               ucd.id_usuario_codigo, ucd.fecha_usado, ucd.activo
        FROM codigo_descuento cd
        LEFT JOIN usuario_codigo_descuento ucd ON cd.id_codigo = ucd.id_codigo 
            AND ucd.id_usuario = ?
        WHERE cd.codigo = ?
    ");
    
    $stmt->bind_param("is", $id_usuario, $codigo);
    $stmt->execute();
    $result = $stmt->get_result();
    $codigo_data = $result->fetch_assoc();
    $stmt->close();
    
    escribir_log('[APLICAR-CODIGO] Búsqueda de código - Encontrado: ' . ($codigo_data ? 'SÍ' : 'NO'), 'DEBUG');
    
    if (!$codigo_data) {
        $response['mensaje'] = 'El código ingresado no existe.';
        escribir_log('[APLICAR-CODIGO] Error: Código no existe en BD', 'WARNING');
        echo json_encode($response);
        exit;
    }
    
    // 2. Verificar que no ha expirado
    if ($codigo_data['fecha_expiracion'] && strtotime($codigo_data['fecha_expiracion']) < time()) {
        $response['mensaje'] = 'Este código ya expiró el ' . date('d/m/Y', strtotime($codigo_data['fecha_expiracion'])) . '.';
        escribir_log('[APLICAR-CODIGO] Error: Código expirado', 'WARNING');
        echo json_encode($response);
        exit;
    }
    
    // 3. Si hay usuario, verificar si el código le pertenece y si ya lo usó
    if ($id_usuario) {
        if (!$codigo_data['id_usuario_codigo']) {
            // No revelar que el código existe si no le pertenece
            $response['mensaje'] = 'El código ingresado no es válido.';
            escribir_log('[APLICAR-CODIGO] Error: Código no asignado al usuario (no revelar existencia)', 'WARNING');
            echo json_encode($response);
            exit;
        }
        
        if (!$codigo_data['activo'] || $codigo_data['fecha_usado']) {
            $response['mensaje'] = 'Ya utilizaste este código anteriormente.';
            escribir_log('[APLICAR-CODIGO] Error: Código ya usado', 'WARNING');
            echo json_encode($response);
            exit;
        }
    }
    
    // 4. Obtener precio del reloj
    $stmt_precio = $conn->prepare("SELECT precio, descuento FROM reloj WHERE id_reloj = ?");
    $stmt_precio->bind_param("i", $id_reloj);
    $stmt_precio->execute();
    $result_precio = $stmt_precio->get_result();
    $reloj_data = $result_precio->fetch_assoc();
    $stmt_precio->close();
    
    if (!$reloj_data) {
        $response['mensaje'] = 'No se pudo obtener el precio del reloj.';
        escribir_log('[APLICAR-CODIGO] Error: Reloj no encontrado', 'ERROR');
        echo json_encode($response);
        exit;
    }
    
    // Calcular precio con descuento del reloj (si tiene)
    $precio_base = $reloj_data['precio'];
    $descuento_reloj = $reloj_data['descuento'] ?? 0;
    $precio_actual = $precio_base - ($precio_base * $descuento_reloj);
    
    // Calcular precio con el código de descuento
    $porcentaje = $codigo_data['porcentaje'];
    $precio_con_descuento = $precio_actual - ($precio_actual * ($porcentaje / 100));
    
    escribir_log('[APLICAR-CODIGO] Precio original: ' . $precio_actual . ', Descuento código: ' . $porcentaje . '%, Precio final: ' . $precio_con_descuento, 'DEBUG');
    
    // 5. MARCAR CÓDIGO COMO USADO Y GUARDAR id_reloj
    // El código se consume cuando se aplica, no cuando se compra
    // También limpiamos id_orden (que puede ser de la orden que generó el código)
    // para que luego se asigne el id_orden de la compra donde se use
    $stmt_marcar_usado = $conn->prepare("
        UPDATE usuario_codigo_descuento 
        SET activo = 0,
            fecha_usado = NOW(),
            id_reloj = ?,
            id_orden = NULL
        WHERE id_usuario = ? 
          AND id_codigo = ?
    ");
    $stmt_marcar_usado->bind_param("iii", $id_reloj, $id_usuario, $codigo_data['id_codigo']);
    
    if (!$stmt_marcar_usado->execute()) {
        $response['mensaje'] = 'Error al aplicar el descuento. Intenta de nuevo.';
        escribir_log('[APLICAR-CODIGO] ✗ Error al marcar código como usado: ' . $stmt_marcar_usado->error, 'ERROR');
        echo json_encode($response);
        exit;
    }
    
    $filas_afectadas = $stmt_marcar_usado->affected_rows;
    $stmt_marcar_usado->close();
    
    if ($filas_afectadas === 0) {
        $response['mensaje'] = 'No se pudo aplicar el código. Intenta de nuevo.';
        escribir_log('[APLICAR-CODIGO] ⚠ No se actualizó ninguna fila', 'WARNING');
        echo json_encode($response);
        exit;
    }
    
    escribir_log('[APLICAR-CODIGO] ✓ Código marcado como usado, vinculado al reloj #' . $id_reloj . ' y id_orden limpiado', 'SUCCESS');
    
    // 6. También guardar en sesión para usarlo en la orden
    $_SESSION['codigo_descuento_aplicado'] = [
        'codigo' => $codigo,
        'id_codigo' => $codigo_data['id_codigo'],
        'porcentaje' => $codigo_data['porcentaje'],
        'id_reloj' => $id_reloj,
        'precio_original' => $precio_actual,
        'precio_con_descuento' => $precio_con_descuento,
        'fecha_aplicado' => date('Y-m-d H:i:s')
    ];
    
    $response['success'] = true;
    $response['porcentaje'] = $codigo_data['porcentaje'];
    $response['codigo'] = $codigo;
    $response['precio_original'] = $precio_actual;
    $response['precio_con_descuento'] = $precio_con_descuento;
    $response['mensaje'] = 'Código aplicado correctamente. Descuento del ' . $codigo_data['porcentaje'] . '% activado.';
    
    escribir_log('[APLICAR-CODIGO] ✓ Código aplicado exitosamente - ' . $codigo_data['porcentaje'] . '% de descuento', 'SUCCESS');
    escribir_log('[APLICAR-CODIGO] Guardado en BD y sesión', 'INFO');
    
} catch (Exception $e) {
    $response['mensaje'] = 'Error al validar el código. Intenta de nuevo.';
    escribir_log('[APLICAR-CODIGO] ✗ Error de BD: ' . $e->getMessage(), 'ERROR');
}

echo json_encode($response);
?>