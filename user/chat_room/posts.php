<?php
// posts.php
require_once __DIR__ . '/../../db.php';   // Go up two levels to virtual-phone/db.php
require_once __DIR__ . '/utils.php';      // same directory

// For GET request – fetch all posts
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Optionally filter by user_id if passed
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

    $sql = "SELECT 
                p.*,
                u.username,
                u.profile_image,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as like_count,
                (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comment_count,
                (SELECT COUNT(*) FROM post_views WHERE post_id = p.id) as view_count
            FROM posts p
            LEFT JOIN users u ON p.user_id = u.id";
    
    if ($userId > 0) {
        $sql .= " WHERE p.user_id = :user_id";
    }
    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $pdo->prepare($sql);
    if ($userId > 0) {
        $stmt->execute(['user_id' => $userId]);
    } else {
        $stmt->execute();
    }

    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // For each post, fetch list of likers and comments (if needed)
    foreach ($posts as &$post) {
        // Get likers (user ids)
        $likeStmt = $pdo->prepare("SELECT user_id FROM post_likes WHERE post_id = ?");
        $likeStmt->execute([$post['id']]);
        $post['likes'] = $likeStmt->fetchAll(PDO::FETCH_COLUMN);

        // Get comments (with user info)
        $commentStmt = $pdo->prepare("
            SELECT c.*, u.username, u.profile_image 
            FROM post_comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.post_id = ? 
            ORDER BY c.created_at ASC
        ");
        $commentStmt->execute([$post['id']]);
        $post['comments'] = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    jsonResponse(['success' => true, 'posts' => $posts]);
}
?>