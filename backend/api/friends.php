<?php
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/jwt.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        handleListFriends($pdo);
        break;
    case 'request':
        handleSendFriendRequest($pdo);
        break;
    case 'accept':
        handleAcceptFriendRequest($pdo);
        break;
    case 'reject':
        handleRejectFriendRequest($pdo);
        break;
    case 'pending':
        handleGetPendingRequests($pdo);
        break;
    case 'block':
        handleBlockUser($pdo);
        break;
    case 'unblock':
        handleUnblockUser($pdo);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

/**
 * List user's friends
 */
function handleListFriends($pdo) {
    $userId = requireAuth();

    $stmt = $pdo->prepare("
        SELECT 
            u.id, u.username, u.full_name, u.avatar_url, u.bio,
            f.created_at as friends_since
        FROM friendships f
        JOIN users u ON (
            u.id = CASE 
                WHEN f.requester_id = ? THEN f.recipient_id
                ELSE f.requester_id
            END
        )
        WHERE (f.requester_id = ? OR f.recipient_id = ?)
            AND f.status = 'accepted'
            AND u.deleted_at IS NULL
        ORDER BY u.username ASC
    ");
    $stmt->execute([$userId, $userId, $userId]);

    echo json_encode(['friends' => $stmt->fetchAll()]);
}

/**
 * Send friend request
 */
function handleSendFriendRequest($pdo) {
    $userId = requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);
    $recipientId = $data['user_id'] ?? 0;

    if (empty($recipientId) || $recipientId == $userId) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid user ID']);
        return;
    }

    // Check if friendship already exists
    $stmt = $pdo->prepare("
        SELECT id, status FROM friendships
        WHERE (requester_id = ? AND recipient_id = ?)
            OR (requester_id = ? AND recipient_id = ?)
    ");
    $stmt->execute([$userId, $recipientId, $recipientId, $userId]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['status'] == 'accepted') {
            echo json_encode(['error' => 'Already friends']);
        } elseif ($existing['status'] == 'pending') {
            echo json_encode(['error' => 'Friend request already sent']);
        } elseif ($existing['status'] == 'blocked') {
            http_response_code(403);
            echo json_encode(['error' => 'Cannot send friend request']);
        }
        return;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO friendships (requester_id, recipient_id, status, created_at)
            VALUES (?, ?, 'pending', NOW())
        ");
        $stmt->execute([$userId, $recipientId]);

        // Create notification
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, actor_id, reference_id, created_at)
            VALUES (?, 'friend_request', ?, ?, NOW())
        ");
        $stmt->execute([$recipientId, $userId, $pdo->lastInsertId()]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to send friend request']);
    }
}

/**
 * Accept friend request
 */
function handleAcceptFriendRequest($pdo) {
    $userId = requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);
    $requestId = $data['request_id'] ?? 0;

    if (empty($requestId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Request ID required']);
        return;
    }

    // Verify the request is for this user
    $stmt = $pdo->prepare("
        SELECT id FROM friendships
        WHERE id = ? AND recipient_id = ? AND status = 'pending'
    ");
    $stmt->execute([$requestId, $userId]);

    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Friend request not found']);
        return;
    }

    $stmt = $pdo->prepare("
        UPDATE friendships 
        SET status = 'accepted', updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$requestId]);

    echo json_encode(['success' => true]);
}

/**
 * Reject friend request
 */
function handleRejectFriendRequest($pdo) {
    $userId = requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);
    $requestId = $data['request_id'] ?? 0;

    if (empty($requestId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Request ID required']);
        return;
    }

    // Verify the request is for this user
    $stmt = $pdo->prepare("
        SELECT id FROM friendships
        WHERE id = ? AND recipient_id = ? AND status = 'pending'
    ");
    $stmt->execute([$requestId, $userId]);

    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Friend request not found']);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM friendships WHERE id = ?");
    $stmt->execute([$requestId]);

    echo json_encode(['success' => true]);
}

/**
 * Get pending friend requests
 */
function handleGetPendingRequests($pdo) {
    $userId = requireAuth();

    $stmt = $pdo->prepare("
        SELECT 
            f.id as request_id,
            u.id, u.username, u.full_name, u.avatar_url,
            f.created_at
        FROM friendships f
        JOIN users u ON u.id = f.requester_id
        WHERE f.recipient_id = ? AND f.status = 'pending'
            AND u.deleted_at IS NULL
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$userId]);

    echo json_encode(['requests' => $stmt->fetchAll()]);
}

/**
 * Block a user
 */
function handleBlockUser($pdo) {
    $userId = requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);
    $blockUserId = $data['user_id'] ?? 0;

    if (empty($blockUserId)) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID required']);
        return;
    }

    try {
        // Delete existing friendship if any
        $stmt = $pdo->prepare("
            DELETE FROM friendships
            WHERE (requester_id = ? AND recipient_id = ?)
                OR (requester_id = ? AND recipient_id = ?)
        ");
        $stmt->execute([$userId, $blockUserId, $blockUserId, $userId]);

        // Create block record
        $stmt = $pdo->prepare("
            INSERT INTO friendships (requester_id, recipient_id, status, created_at)
            VALUES (?, ?, 'blocked', NOW())
        ");
        $stmt->execute([$userId, $blockUserId]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to block user']);
    }
}

/**
 * Unblock a user
 */
function handleUnblockUser($pdo) {
    $userId = requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);
    $unblockUserId = $data['user_id'] ?? 0;

    if (empty($unblockUserId)) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID required']);
        return;
    }

    $stmt = $pdo->prepare("
        DELETE FROM friendships
        WHERE requester_id = ? AND recipient_id = ? AND status = 'blocked'
    ");
    $stmt->execute([$userId, $unblockUserId]);

    echo json_encode(['success' => true]);
}
?>
