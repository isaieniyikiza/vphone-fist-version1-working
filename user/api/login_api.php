<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json');

require_once "../../db.php";

if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection not available."
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed."
    ]);
    exit;
}

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if ($username === "" || $password === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Username and password are required."
    ]);
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            username,
            password,
            profile_image,
            created_at,
            is_blocked
        FROM users
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->execute([$username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {

        echo json_encode([
            "success" => false,
            "message" => "Invalid username or password."
        ]);
        exit;

    }

    if (!password_verify($password, $user["password"])) {

        echo json_encode([
            "success" => false,
            "message" => "Invalid username or password."
        ]);
        exit;

    }

    if ((int)($user['is_blocked'] ?? 0) === 1) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "This account has been blocked. Contact the administrator."
        ]);
        exit;
    }

    session_regenerate_id(true);

    $_SESSION["user_id"] = $user["id"];
    $_SESSION["username"] = $user["username"];

    $pdo->prepare("UPDATE users SET last_seen = NOW() WHERE id = ?")->execute([$user["id"]]);

    $profile = "";

    if (!empty($user["profile_image"])) {
        $profileValue = $user["profile_image"];
        if (preg_match('#^https?://#', $profileValue) || str_starts_with($profileValue, '/')) {
            $profile = $profileValue;
        } else {
            $profile = '/virtual-phone/uploads/profile/' . $profileValue;
        }
    }

    unset($user["password"]);

    echo json_encode([
        "success" => true,
        "message" => "Login successful.",
        "user" => [
            "id" => $user["id"],
            "username" => $user["username"],
            "profile_image" => $profile,
            "created_at" => $user["created_at"]
        ]
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Database error."
    ]);
}