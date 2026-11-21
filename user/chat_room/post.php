<?php
// post.php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/utils.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if (!$id) {
    jsonResponse(['success' => false, 'message' => 'Post ID required'], 400);
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($action) {
    case 'get':
        // GET single post (used for comments modal)
        if ($method !== 'GET') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $stmt = $pdo->prepare("
            SELECT p.*, u.username, u.profile_image,
                   (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as like_count,
                   (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comment_count,
                   (SELECT COUNT(*) FROM post_views WHERE post_id = p.id) as view_count
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$post) {
            jsonResponse(['success' => false, 'message' => 'Post not found'], 404);
        }
        // likers
        $likeStmt = $pdo->prepare("SELECT user_id FROM post_likes WHERE post_id = ?");
        $likeStmt->execute([$id]);
        $post['likes'] = $likeStmt->fetchAll(PDO::FETCH_COLUMN);
        // comments
        $commentStmt = $pdo->prepare("
            SELECT c.*, u.username, u.profile_image 
            FROM post_comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.post_id = ? 
            ORDER BY c.created_at ASC
        ");
        $commentStmt->execute([$id]);
        $post['comments'] = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(['success' => true, 'post' => $post]);
        break;

    case 'like':
        if ($method !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['user_id'] ?? 0;
        if (!$userId) {
            jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
        }
        // Check if already liked
        $check = $pdo->prepare("SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?");
        $check->execute([$id, $userId]);
        if ($check->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Already liked'], 409);
        }
        $stmt = $pdo->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$id, $userId]);
        jsonResponse(['success' => true, 'message' => 'Liked']);
        break;

    case 'unlike':
        if ($method !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['user_id'] ?? 0;
        if (!$userId) {
            jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
        }
        $stmt = $pdo->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        jsonResponse(['success' => true, 'message' => 'Unliked']);
        break;

    case 'comment':
        if ($method !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['user_id'] ?? 0;
        $comment = trim($data['comment'] ?? '');
        if (!$userId) {
            jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
        }
        if (empty($comment)) {
            jsonResponse(['success' => false, 'message' => 'Comment cannot be empty'], 400);
        }
        $stmt = $pdo->prepare("INSERT INTO post_comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$id, $userId, $comment]);
        $newId = $pdo->lastInsertId();
        // Fetch user info
        $user = getUserById($pdo, $userId);
        jsonResponse([
            'success' => true,
            'comment' => [
                'id'          => $newId,
                'user_id'     => $userId,
                'username'    => $user['username'],
                'profile_image' => $user['profile_image'],
                'comment'     => $comment,
                'created_at'  => date('Y-m-d H:i:s')
            ]
        ]);
        break;

    case 'view':
        if ($method !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['user_id'] ?? 0;
        if (!$userId) {
            jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
        }
        // Avoid duplicate views
        $check = $pdo->prepare("SELECT id FROM post_views WHERE post_id = ? AND user_id = ?");
        $check->execute([$id, $userId]);
        if (!$check->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO post_views (post_id, user_id) VALUES (?, ?)");
            $stmt->execute([$id, $userId]);
        }
        jsonResponse(['success' => true]);
        break;

    case 'delete':
        if ($method !== 'DELETE') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['user_id'] ?? 0;
        if (!$userId) {
            jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
        }
        // Check ownership
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        if ($stmt->rowCount()) {
            jsonResponse(['success' => true, 'message' => 'Post deleted']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Not authorized or post not found'], 403);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
?>