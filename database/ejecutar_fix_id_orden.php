<?php
/**
 * Script para arreglar id_orden en códigos aplicados
 * Cuando un código se aplica a un reloj, id_orden debe ser NULL
 * Solo cuando se compra, se asigna el id_orden de la nueva compra
 */

require_once __DIR__ . '/../informacion/php/conexion.php';

echo "🔧 Arreglando id_orden en códigos aplicados...\n\n";

try {
    // Mostrar registros antes
    echo "📊 ANTES:\n";
    $stmt = $conn->query("SELECT id_usuario_codigo, id_usuario, id_codigo, id_reloj, activo, fecha_usado, id_orden FROM usuario_codigo_descuento WHERE id_reloj IS NOT NULL");
    while ($row = $stmt->fetch_assoc()) {
        echo "  ID: {$row['id_usuario_codigo']}, Usuario: {$row['id_usuario']}, Código: {$row['id_codigo']}, Reloj: {$row['id_reloj']}, Activo: {$row['activo']}, Fecha usado: {$row['fecha_usado']}, ID Orden: " . ($row['id_orden'] ?? 'NULL') . "\n";
    }
    echo "\n";
    
    // Actualizar
    $stmt = $conn->prepare("
        UPDATE usuario_codigo_descuento 
        SET id_orden = NULL
        WHERE activo = 0 
          AND id_reloj IS NOT NULL
          AND fecha_usado IS NOT NULL
          AND id_orden IS NOT NULL
    ");
    
    if ($stmt->execute()) {
        $filas_afectadas = $stmt->affected_rows;
        echo "✅ Se actualizaron {$filas_afectadas} registros\n\n";
    } else {
        echo "❌ Error al actualizar: " . $stmt->error . "\n";
        exit;
    }
    
    // Mostrar registros después
    echo "📊 DESPUÉS:\n";
    $stmt = $conn->query("SELECT id_usuario_codigo, id_usuario, id_codigo, id_reloj, activo, fecha_usado, id_orden FROM usuario_codigo_descuento WHERE id_reloj IS NOT NULL");
    while ($row = $stmt->fetch_assoc()) {
        echo "  ID: {$row['id_usuario_codigo']}, Usuario: {$row['id_usuario']}, Código: {$row['id_codigo']}, Reloj: {$row['id_reloj']}, Activo: {$row['activo']}, Fecha usado: {$row['fecha_usado']}, ID Orden: " . ($row['id_orden'] ?? 'NULL') . "\n";
    }
    
    echo "\n✓ Proceso completado exitosamente\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>


 * Script para arreglar id_orden en códigos aplicados
 * Cuando un código se aplica a un reloj, id_orden debe ser NULL
 * Solo cuando se compra, se asigna el id_orden de la nueva compra
 */

require_once __DIR__ . '/../informacion/php/conexion.php';

echo "🔧 Arreglando id_orden en códigos aplicados...\n\n";

try {
    // Mostrar registros antes
    echo "📊 ANTES:\n";
    $stmt = $conn->query("SELECT id_usuario_codigo, id_usuario, id_codigo, id_reloj, activo, fecha_usado, id_orden FROM usuario_codigo_descuento WHERE id_reloj IS NOT NULL");
    while ($row = $stmt->fetch_assoc()) {
        echo "  ID: {$row['id_usuario_codigo']}, Usuario: {$row['id_usuario']}, Código: {$row['id_codigo']}, Reloj: {$row['id_reloj']}, Activo: {$row['activo']}, Fecha usado: {$row['fecha_usado']}, ID Orden: " . ($row['id_orden'] ?? 'NULL') . "\n";
    }
    echo "\n";
    
    // Actualizar
    $stmt = $conn->prepare("
        UPDATE usuario_codigo_descuento 
        SET id_orden = NULL
        WHERE activo = 0 
          AND id_reloj IS NOT NULL
          AND fecha_usado IS NOT NULL
          AND id_orden IS NOT NULL
    ");
    
    if ($stmt->execute()) {
        $filas_afectadas = $stmt->affected_rows;
        echo "✅ Se actualizaron {$filas_afectadas} registros\n\n";
    } else {
        echo "❌ Error al actualizar: " . $stmt->error . "\n";
        exit;
    }
    
    // Mostrar registros después
    echo "📊 DESPUÉS:\n";
    $stmt = $conn->query("SELECT id_usuario_codigo, id_usuario, id_codigo, id_reloj, activo, fecha_usado, id_orden FROM usuario_codigo_descuento WHERE id_reloj IS NOT NULL");
    while ($row = $stmt->fetch_assoc()) {
        echo "  ID: {$row['id_usuario_codigo']}, Usuario: {$row['id_usuario']}, Código: {$row['id_codigo']}, Reloj: {$row['id_reloj']}, Activo: {$row['activo']}, Fecha usado: {$row['fecha_usado']}, ID Orden: " . ($row['id_orden'] ?? 'NULL') . "\n";
    }
    
    echo "\n✓ Proceso completado exitosamente\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>

