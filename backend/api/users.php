<?php
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/jwt.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'current':
        handleGetCurrentUser($pdo);
        break;
    case 'profile':
        handleGetUserProfile($pdo);
        break;
    case 'update':
        handleUpdateProfile($pdo);
        break;
    case 'search':
        handleSearchUsers($pdo);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

/**
 * Get current authenticated user
 */
function handleGetCurrentUser($pdo) {
    $userId = requireAuth();

    $stmt = $pdo->prepare("
        SELECT id, username, email, full_name, avatar_url, bio, public_key, is_public, created_at
        FROM users
        WHERE id = ? AND deleted_at IS NULL
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        return;
    }

    echo json_encode(['user' => $user]);
}

/**
 * Get user profile by ID
 */
function handleGetUserProfile($pdo) {
    $userId = getUserIdFromAuth(); // Optional auth
    $profileId = $_GET['id'] ?? 0;

    if (empty($profileId)) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID required']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT id, username, full_name, avatar_url, bio, is_public, created_at
        FROM users
        WHERE id = ? AND deleted_at IS NULL
    ");
    $stmt->execute([$profileId]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        return;
    }

    // Check if users are friends
    $areFriends = false;
    if ($userId) {
        $stmt = $pdo->prepare("
            SELECT id FROM friendships
            WHERE ((requester_id = ? AND recipient_id = ?) 
                OR (requester_id = ? AND recipient_id = ?))
                AND status = 'accepted'
        ");
        $stmt->execute([$userId, $profileId, $profileId, $userId]);
        $areFriends = (bool)$stmt->fetch();
    }

    // Hide private info if not friends and profile is private
    if (!$user['is_public'] && !$areFriends && $userId != $profileId) {
        unset($user['bio']);
    }

    $user['are_friends'] = $areFriends;

    echo json_encode(['user' => $user]);
}

/**
 * Update current user profile
 */
function handleUpdateProfile($pdo) {
    $userId = requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);

    $allowedFields = ['full_name', 'bio', 'avatar_url', 'is_public'];
    $updates = [];
    $values = [];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $values[] = $data[$field];
        }
    }

    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['error' => 'No fields to update']);
        return;
    }

    $values[] = $userId;
    $sql = "UPDATE users SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update profile']);
    }
}

/**
 * Search users by username or full name
 */
function handleSearchUsers($pdo) {
    $userId = getUserIdFromAuth();
    $query = $_GET['q'] ?? '';
    $limit = $_GET['limit'] ?? 20;

    if (empty($query)) {
        echo json_encode(['users' => []]);
        return;
    }

    $searchTerm = '%' . $query . '%';

    $stmt = $pdo->prepare("
        SELECT id, username, full_name, avatar_url
        FROM users
        WHERE (username LIKE ? OR full_name LIKE ?)
            AND deleted_at IS NULL
        LIMIT ?
    ");
    $stmt->execute([$searchTerm, $searchTerm, (int)$limit]);

    echo json_encode(['users' => $stmt->fetchAll()]);
}
?>
