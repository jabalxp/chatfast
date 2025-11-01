<?php
// Test database connection
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Use LOCAL configuration for development
require_once __DIR__ . '/config/constants.local.php';
require_once __DIR__ . '/config/database.php';

try {
    // Show connection details
    echo json_encode([
        'attempting_connection' => true,
        'host' => DB_HOST,
        'database' => DB_NAME,
        'user' => DB_USER,
    ], JSON_PRETTY_PRINT);
    echo "\n\n";
    
    // Try to connect with detailed error reporting
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Test query to check tables
    $query = "SHOW TABLES";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'message' => 'Conexão bem-sucedida com o banco de dados InfinityFree!',
        'database' => DB_NAME,
        'host' => DB_HOST,
        'tables_count' => count($tables),
        'tables' => $tables
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao conectar ao banco de dados',
        'error' => $e->getMessage(),
        'error_code' => $e->getCode(),
        'database' => DB_NAME,
        'host' => DB_HOST,
        'user' => DB_USER,
        'note' => 'InfinityFree pode bloquear conexões externas. Este teste deve ser feito no próprio servidor InfinityFree.'
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro geral',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
