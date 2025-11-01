<?php
// Test database connection - InfinityFree version
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Use production configuration for InfinityFree
    require_once __DIR__ . '/config/constants.php';
    
    echo json_encode([
        'step' => 1,
        'message' => 'Constants loaded',
        'host' => DB_HOST,
        'database' => DB_NAME,
        'user' => DB_USER
    ], JSON_PRETTY_PRINT);
    echo "\n\n";
    
    // Try to connect
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
    
    echo json_encode(['step' => 2, 'message' => 'Connected to database'], JSON_PRETTY_PRINT);
    echo "\n\n";
    
    // Test query
    $query = "SHOW TABLES";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'message' => 'Backend funcionando!',
        'database' => DB_NAME,
        'host' => DB_HOST,
        'tables_count' => count($tables),
        'tables' => $tables
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error_type' => 'PDO Exception',
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error_type' => 'General Exception',
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ], JSON_PRETTY_PRINT);
}
?>
