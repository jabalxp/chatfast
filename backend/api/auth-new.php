<?php
// CORS Headers - INLINE (must be first!)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
header('Content-Type: application/json; charset=utf-8');

// Handle OPTIONS preflight request immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'OPTIONS OK']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/jwt.php';

$pdo = getDBConnection();

// Get action from query parameter
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        handleRegister($pdo);
        break;
    case 'login':
        handleLogin($pdo);
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

/**
 * Handle user registration
 */
function handleRegister($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $name = $data['name'] ?? '';
    $username = $data['username'] ?? '';
    $publicKey = $data['publicKey'] ?? '';
    
    // Validation
    if (empty($email) || empty($password) || empty($name) || empty($username)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        return;
    }
    
    // Check if email or username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Email or username already exists']);
        return;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO users (email, password_hash, name, username, public_key, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    try {
        $stmt->execute([$email, $hashedPassword, $name, $username, $publicKey]);
        $userId = $pdo->lastInsertId();
        
        // Generate JWT token
        $token = generateJWT(['user_id' => $userId, 'email' => $email]);
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $userId,
                'email' => $email,
                'name' => $name,
                'username' => $username,
                'publicKey' => $publicKey
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
    }
}

/**
 * Handle user login
 */
function handleLogin($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing email or password']);
        return;
    }
    
    // Find user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        return;
    }
    
    // Update last login
    $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->execute([$user['id']]);
    
    // Generate JWT token
    $token = generateJWT(['user_id' => $user['id'], 'email' => $user['email']]);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'username' => $user['username'],
            'publicKey' => $user['public_key'],
            'avatar' => $user['avatar']
        ]
    ]);
}

/**
 * Handle user logout
 */
function handleLogout() {
    // In a stateless JWT system, logout is handled client-side
    // by removing the token from storage
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}
?>
