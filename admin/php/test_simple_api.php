<?php
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'Test API funcionando',
    'timestamp' => date('Y-m-d H:i:s'),
    'data' => [
        'test' => 'value',
        'number' => 123
    ]
]);
?>


