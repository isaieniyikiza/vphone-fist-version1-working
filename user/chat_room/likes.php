<?php
// likes.php
// require_once 'db.php';
require_once __DIR__ . '/../../db.php';   // Go up two levels to virtual-phone/db.php

require_once 'utils.php';

$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
if (!$postId) {
    jsonResponse(['success' => false, 'message' => 'Post ID required'], 400);
}

$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.profile_image 
    FROM post_likes l 
    JOIN users u ON l.user_id = u.id 
    WHERE l.post_id = ?
");
$stmt->execute([$postId]);
$likes = $stmt->fetchAll(PDO::FETCH_ASSOC);

jsonResponse(['success' => true, 'likes' => $likes]);
?>