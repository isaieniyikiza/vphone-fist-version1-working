<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../db.php";

if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit;
}

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);
    exit;
}

$user_id = (int)$_SESSION["user_id"];

$stmt = $pdo->prepare("
    SELECT
        id,
        username,
        profile_image,
        created_at
    FROM users
    WHERE id=?
    LIMIT 1
");

$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {

    session_destroy();

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "User not found."
    ]);

    exit;
}

$GLOBALS["loggedUser"] = $user;
$GLOBALS["loggedUserId"] = $user["id"];