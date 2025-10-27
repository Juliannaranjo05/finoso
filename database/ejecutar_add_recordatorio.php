<?php
/**
 * Ejecutar actualización de BD: Agregar columna recordatorio_enviado
 */

include '../admin/conexion.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Actualizar Base de Datos</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>📊 Actualizar Base de Datos</h1>";

try {
    // Verificar si la columna ya existe
    $result = $conn->query("SHOW COLUMNS FROM orden LIKE 'recordatorio_enviado'");
    
    if ($result->num_rows > 0) {
        echo "<div class='error'>";
        echo "⚠️ La columna 'recordatorio_enviado' ya existe en la tabla 'orden'.";
        echo "</div>";
    } else {
        // Ejecutar SQL
        $sql = "ALTER TABLE orden 
                ADD COLUMN recordatorio_enviado TINYINT(1) DEFAULT 0 
                COMMENT 'Indica si se envió recordatorio WhatsApp para orden rechazada'";
        
        if ($conn->query($sql) === TRUE) {
            echo "<div class='success'>";
            echo "✅ <strong>Columna agregada exitosamente!</strong><br><br>";
            echo "Se agregó la columna <code>recordatorio_enviado</code> a la tabla <code>orden</code>.<br>";
            echo "Esto permite trackear si ya se envió un recordatorio WhatsApp para órdenes rechazadas.";
            echo "</div>";
        } else {
            throw new Exception($conn->error);
        }
    }
    
    echo "<p><a href='../admin/panel.php' style='display: inline-block; background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px;'>⬅️ Volver al Panel</a></p>";
    
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

