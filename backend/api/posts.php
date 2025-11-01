<?php
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/jwt.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'feed':
        handleGetFeed($pdo);
        break;
    case 'create':
        handleCreatePost($pdo);
        break;
    case 'like':
        handleLikePost($pdo);
        break;
    case 'unlike':
        handleUnlikePost($pdo);
        break;
    case 'comment':
        handleCommentOnPost($pdo);
        break;
    case 'delete':
        handleDeletePost($pdo);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

/**
 * Get user feed
 */
function handleGetFeed($pdo) {
    $userId = requireAuth();
    $page = $_GET['page'] ?? 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;

    // Get posts from user and friends
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            u.username,
            u.avatar_url,
            COUNT(DISTINCT pl.id) as like_count,
            COUNT(DISTINCT c.id) as comment_count,
            EXISTS(
                SELECT 1 FROM post_likes pl2 
                WHERE pl2.post_id = p.id AND pl2.user_id = ?
            ) as user_liked
        FROM posts p
        JOIN users u ON u.id = p.user_id
        LEFT JOIN post_likes pl ON pl.post_id = p.id
        LEFT JOIN comments c ON c.post_id = p.id AND c.deleted_at IS NULL
        WHERE p.deleted_at IS NULL
            AND (
                p.user_id = ?
                OR (p.visibility = 'public')
                OR (p.visibility = 'friends' AND EXISTS(
                    SELECT 1 FROM friendships f
                    WHERE ((f.requester_id = ? AND f.recipient_id = p.user_id)
                        OR (f.recipient_id = ? AND f.requester_id = p.user_id))
                        AND f.status = 'accepted'
                ))
            )
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$userId, $userId, $userId, $userId, $limit, $offset]);

    $posts = $stmt->fetchAll();

    // Get media for each post
    foreach ($posts as &$post) {
        $stmt = $pdo->prepare("SELECT * FROM post_media WHERE post_id = ?");
        $stmt->execute([$post['id']]);
        $post['media'] = $stmt->fetchAll();
    }

    echo json_encode(['posts' => $posts]);
}

/**
 * Create a new post
 */
function handleCreatePost($pdo) {
    $userId = requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);

    $content = $data['content'] ?? '';
    $visibility = $data['visibility'] ?? 'friends';

    if (empty($content)) {
        http_response_code(400);
        echo json_encode(['error' => 'Content is required']);
        return;
    }

    if (!in_array($visibility, ['public', 'friends', 'private'])) {
        $visibility = 'friends';
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO posts (user_id, content, visibility, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $content, $visibility]);

        echo json_encode([
            'success' => true,
            'post_id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create post']);
    }
}

/**
 * Like a post
 */
function handleLikePost($pdo) {
    $userId = requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);
    $postId = $data['post_id'] ?? 0;

    if (empty($postId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Post ID required']);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO post_likes (post_id, user_id, created_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$postId, $userId]);

        // Create notification
        $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        $post = $stmt->fetch();

        if ($post && $post['user_id'] != $userId) {
            $stmt = $pdo->prepare("
                INSERT INTO notifications (user_id, type, actor_id, reference_id, created_at)
                VALUES (?, 'like', ?, ?, NOW())
            ");
            $stmt->execute([$post['user_id'], $userId, $postId]);
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to like post']);
    }
}

/**
 * Unlike a post
 */
function handleUnlikePost($pdo) {
    $userId = requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);
    $postId = $data['post_id'] ?? 0;

    if (empty($postId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Post ID required']);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$postId, $userId]);

    echo json_encode(['success' => true]);
}

/**
 * Comment on a post
 */
function handleCommentOnPost($pdo) {
    $userId = requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);

    $postId = $data['post_id'] ?? 0;
    $content = $data['content'] ?? '';

    if (empty($postId) || empty($content)) {
        http_response_code(400);
        echo json_encode(['error' => 'Post ID and content required']);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO comments (post_id, user_id, content, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$postId, $userId, $content]);

        // Create notification
        $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        $post = $stmt->fetch();

        if ($post && $post['user_id'] != $userId) {
            $stmt = $pdo->prepare("
                INSERT INTO notifications (user_id, type, actor_id, reference_id, created_at)
                VALUES (?, 'comment', ?, ?, NOW())
            ");
            $stmt->execute([$post['user_id'], $userId, $postId]);
        }

        echo json_encode([
            'success' => true,
            'comment_id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add comment']);
    }
}

/**
 * Delete a post (soft delete)
 */
function handleDeletePost($pdo) {
    $userId = requireAuth();
    $postId = $_GET['post_id'] ?? 0;

    if (empty($postId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Post ID required']);
        return;
    }

    // Verify ownership
    $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();

    if (!$post || $post['user_id'] != $userId) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }

    $stmt = $pdo->prepare("UPDATE posts SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$postId]);

    echo json_encode(['success' => true]);
}
?>
