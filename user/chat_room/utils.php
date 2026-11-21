<?php
// utils.php
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function getUserId() {
    // In a real app, extract from session/token.
    // For now, we assume the frontend sends user_id in POST body or GET param.
    // We'll use a global variable $currentUserId set via a middleware.
    global $currentUserId;
    return $currentUserId;
}

function getUserById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT id, username, profile_image FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>