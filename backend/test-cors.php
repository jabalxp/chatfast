<?php
// Test CORS - Super Simple
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'OPTIONS OK']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'CORS está funcionando!',
    'method' => $_SERVER['REQUEST_METHOD'],
    'server' => $_SERVER['SERVER_NAME']
]);
?>
