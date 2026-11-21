<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$host = "localhost";
$dbname = "vphone";
$user = "root";
$pass = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

// Get Post ID
$postId = isset($_GET["post_id"]) ? (int)$_GET["post_id"] : 0;

if ($postId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid post_id"
    ]);
    exit;
}

try {

    // Get post
    $stmt = $pdo->prepare("
        SELECT
            p.*,
            u.username,
            u.profile_image
        FROM posts p
        LEFT JOIN users u
            ON p.user_id = u.id
        WHERE p.id = ?
        LIMIT 1
    ");
    $stmt->execute([$postId]);

    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        echo json_encode([
            "success" => false,
            "message" => "Post not found"
        ]);
        exit;
    }

    // Likes
    $stmt = $pdo->prepare("
        SELECT user_id
        FROM post_likes
        WHERE post_id = ?
    ");
    $stmt->execute([$postId]);

    $likes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Comments
    $stmt = $pdo->prepare("
        SELECT
            c.*,
            u.username,
            u.profile_image
        FROM post_comments c
        LEFT JOIN users u
            ON c.user_id = u.id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$postId]);

    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "post" => $post,
        "likes" => $likes,
        "likes_count" => count($likes),
        "comments" => $comments,
        "comments_count" => count($comments)
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

}