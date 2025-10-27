<?php
/**
 * SCRIPT DE SINCRONIZACIÓN - Marcar relojes como vendidos
 * 
 * Este script sincroniza el campo 'vendido' de la tabla 'reloj' 
 * con el estado real de las órdenes.
 * 
 * LÓGICA:
 * - Si un reloj tiene una orden con estado: pagado, aprobado, enviado o entregado
 * - Entonces ese reloj debe marcarse como vendido = 1
 */

include 'conexion.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Sincronización de Relojes Vendidos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
        }
        .section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        .item {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-vendido { background: #28a745; color: white; }
        .badge-disponible { background: #6c757d; color: white; }
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔄 Sincronización de Relojes Vendidos</h1>";

try {
    // PASO 1: Ver estado ANTES de sincronizar
    echo "<div class='section'>";
    echo "<h2>📊 Estado ACTUAL (Antes de sincronizar)</h2>";
    
    $sql_before = "SELECT 
                    r.id_reloj,
                    r.nombre,
                    r.vendido as vendido_actual,
                    o.id_orden,
                    o.estado as estado_orden
                   FROM reloj r
                   LEFT JOIN orden_detalle od ON r.id_reloj = od.id_reloj
                   LEFT JOIN orden o ON od.id_orden = o.id_orden
                   ORDER BY r.id_reloj";
    
    $result_before = $conn->query($sql_before);
    
    echo "<table>";
    echo "<tr><th>ID Reloj</th><th>Nombre</th><th>Vendido Actual</th><th>Orden</th><th>Estado Orden</th></tr>";
    
    while ($row = $result_before->fetch_assoc()) {
        $vendido_badge = $row['vendido_actual'] == 1 
            ? "<span class='badge badge-vendido'>SÍ</span>" 
            : "<span class='badge badge-disponible'>NO</span>";
        
        $orden = $row['id_orden'] ? "#" . $row['id_orden'] : "-";
        $estado = $row['estado_orden'] ?? "-";
        
        echo "<tr>";
        echo "<td>{$row['id_reloj']}</td>";
        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
        echo "<td>$vendido_badge</td>";
        echo "<td>$orden</td>";
        echo "<td>$estado</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "</div>";
    
    // PASO 2: SINCRONIZAR
    echo "<div class='section'>";
    echo "<h2>⚙️ Ejecutando Sincronización...</h2>";
    
    // Marcar como vendido = 1 los relojes con órdenes válidas
    $sql_sync = "UPDATE reloj r
                 INNER JOIN orden_detalle od ON r.id_reloj = od.id_reloj
                 INNER JOIN orden o ON od.id_orden = o.id_orden
                 SET r.vendido = 1
                 WHERE o.estado IN ('pagado', 'aprobado', 'enviado', 'entregado')
                 AND r.vendido = 0";
    
    $conn->query($sql_sync);
    $actualizados = $conn->affected_rows;
    
    if ($actualizados > 0) {
        echo "<div class='success'>";
        echo "✅ <strong>$actualizados</strong> reloj(es) marcado(s) como vendido";
        echo "</div>";
    } else {
        echo "<div class='warning'>";
        echo "⚠️ No se encontraron relojes para actualizar (ya estaban sincronizados)";
        echo "</div>";
    }
    
    echo "</div>";
    
    // PASO 3: Ver estado DESPUÉS de sincronizar
    echo "<div class='section'>";
    echo "<h2>✅ Estado SINCRONIZADO (Después)</h2>";
    
    $result_after = $conn->query($sql_before);
    
    echo "<table>";
    echo "<tr><th>ID Reloj</th><th>Nombre</th><th>Vendido</th><th>Orden</th><th>Estado Orden</th></tr>";
    
    while ($row = $result_after->fetch_assoc()) {
        $vendido_badge = $row['vendido_actual'] == 1 
            ? "<span class='badge badge-vendido'>SÍ</span>" 
            : "<span class='badge badge-disponible'>NO</span>";
        
        $orden = $row['id_orden'] ? "#" . $row['id_orden'] : "-";
        $estado = $row['estado_orden'] ?? "-";
        
        echo "<tr>";
        echo "<td>{$row['id_reloj']}</td>";
        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
        echo "<td>$vendido_badge</td>";
        echo "<td>$orden</td>";
        echo "<td>$estado</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "</div>";
    
    // RESUMEN FINAL
    echo "<div class='success'>";
    echo "<h3>🎉 Sincronización Completada</h3>";
    echo "<p>El campo <strong>'vendido'</strong> ahora refleja correctamente el estado de las órdenes.</p>";
    echo "<p>De ahora en adelante, cada vez que una orden cambie a <strong>pagado, aprobado, enviado o entregado</strong>, el reloj se marcará automáticamente como vendido.</p>";
    echo "</div>";
    
    echo "<div style='text-align: center;'>";
    echo "<a href='panel.php' class='btn'>⬅️ Volver al Panel Admin</a>";
    echo "<a href='debug_bd.php' class='btn' style='background: #28a745; margin-left: 10px;'>🔍 Ver Base de Datos</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "❌ Error: " . $e->getMessage();
    echo "</div>";
}

$conn->close();

echo "    </div>
</body>
</html>";
?>

