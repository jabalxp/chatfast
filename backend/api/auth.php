<?php
require_once __DIR__ . '/../middleware/cors.php';
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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $publicKey = $data['publicKey'] ?? '';

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($publicKey)) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        return;
    }

    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 8 characters']);
        return;
    }

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'User already exists']);
        return;
    }

    // Hash password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, public_key, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$username, $email, $passwordHash, $publicKey]);

        $userId = $pdo->lastInsertId();

        // Create default privacy settings
        $stmt = $pdo->prepare("
            INSERT INTO privacy_settings (user_id) 
            VALUES (?)
        ");
        $stmt->execute([$userId]);

        // Generate JWT token
        $token = generateJWT(['user_id' => $userId, 'username' => $username]);

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $userId,
                'username' => $username,
                'email' => $email
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create user']);
    }
}

/**
 * Handle user login
 */
function handleLogin($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password are required']);
        return;
    }

    // Get user
    $stmt = $pdo->prepare("
        SELECT id, username, email, password_hash, public_key 
        FROM users 
        WHERE email = ? AND deleted_at IS NULL
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        return;
    }

    // Generate JWT token
    $token = generateJWT([
        'user_id' => $user['id'],
        'username' => $user['username']
    ]);

    echo json_encode([
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'publicKey' => $user['public_key']
        ]
    ]);
}

/**
 * Handle user logout
 */
function handleLogout() {
    // Since we're using JWT, logout is handled client-side
    // This endpoint can be used for logging logout events if needed
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}
?>
