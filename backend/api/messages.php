<?php
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/jwt.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'send':
        handleSendMessage($pdo);
        break;
    case 'list':
        handleListMessages($pdo);
        break;
    case 'conversations':
        handleGetConversations($pdo);
        break;
    case 'mark_read':
        handleMarkAsRead($pdo);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

/**
 * Send a message
 */
function handleSendMessage($pdo) {
    $userId = requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);

    $recipientId = $data['recipient_id'] ?? 0;
    $contentEncrypted = $data['content_encrypted'] ?? '';
    $nonce = $data['nonce'] ?? '';

    if (empty($recipientId) || empty($contentEncrypted) || empty($nonce)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO messages (sender_id, recipient_id, content_encrypted, nonce, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $recipientId, $contentEncrypted, $nonce]);

        // Create notification
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, actor_id, reference_id, created_at)
            VALUES (?, 'message', ?, ?, NOW())
        ");
        $stmt->execute([$recipientId, $userId, $pdo->lastInsertId()]);

        echo json_encode([
            'success' => true,
            'message_id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to send message']);
    }
}

/**
 * List messages in a conversation
 */
function handleListMessages($pdo) {
    $userId = requireAuth();
    $recipientId = $_GET['recipient_id'] ?? 0;
    $limit = $_GET['limit'] ?? 50;

    if (empty($recipientId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Recipient ID required']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT m.*, u.username, u.avatar_url, u.public_key
        FROM messages m
        JOIN users u ON (u.id = m.sender_id)
        WHERE ((m.sender_id = ? AND m.recipient_id = ?) 
            OR (m.sender_id = ? AND m.recipient_id = ?))
            AND m.deleted_at IS NULL
        ORDER BY m.created_at ASC
        LIMIT ?
    ");
    $stmt->execute([$userId, $recipientId, $recipientId, $userId, (int)$limit]);

    echo json_encode(['messages' => $stmt->fetchAll()]);
}

/**
 * Get list of conversations
 */
function handleGetConversations($pdo) {
    $userId = requireAuth();

    $stmt = $pdo->prepare("
        SELECT 
            u.id, u.username, u.avatar_url,
            MAX(m.created_at) as last_message_time,
            COUNT(CASE WHEN m.recipient_id = ? AND m.is_read = FALSE THEN 1 END) as unread_count
        FROM messages m
        JOIN users u ON (u.id = CASE 
            WHEN m.sender_id = ? THEN m.recipient_id
            ELSE m.sender_id
        END)
        WHERE (m.sender_id = ? OR m.recipient_id = ?)
            AND m.deleted_at IS NULL
        GROUP BY u.id
        ORDER BY last_message_time DESC
    ");
    $stmt->execute([$userId, $userId, $userId, $userId]);

    echo json_encode(['conversations' => $stmt->fetchAll()]);
}

/**
 * Mark messages as read
 */
function handleMarkAsRead($pdo) {
    $userId = requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);
    $senderId = $data['sender_id'] ?? 0;

    if (empty($senderId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Sender ID required']);
        return;
    }

    $stmt = $pdo->prepare("
        UPDATE messages 
        SET is_read = TRUE, read_at = NOW()
        WHERE recipient_id = ? AND sender_id = ? AND is_read = FALSE
    ");
    $stmt->execute([$userId, $senderId]);

    echo json_encode(['success' => true]);
}
?>
