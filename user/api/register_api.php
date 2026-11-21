<?php
declare(strict_types=1);

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'path' => '/',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/../../db.php';

function registerRespond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    registerRespond(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$contentType = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));
if (!str_starts_with($contentType, 'application/json')) {
    registerRespond(415, ['success' => false, 'message' => 'Use the Virtual Phone sign-up form to create your account.']);
}

$origin = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
$host = (string)($_SERVER['HTTP_HOST'] ?? '');
if ($origin !== '') {
    $originHost = parse_url($origin, PHP_URL_HOST);
    $originPort = parse_url($origin, PHP_URL_PORT);
    $requestPort = isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : null;
    $requestHost = parse_url('http://' . $host, PHP_URL_HOST);
    if (!is_string($originHost) || !is_string($requestHost)
        || strtolower($originHost) !== strtolower($requestHost)
        || ($originPort !== null && $requestPort !== null && $originPort !== $requestPort)) {
        registerRespond(403, ['success' => false, 'message' => 'This account request could not be verified. Reload the phone and try again.']);
    }
}

$payload = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($payload)) {
    registerRespond(400, ['success' => false, 'message' => 'Enter a username and password to create your account.']);
}
$username = trim((string)($payload['username'] ?? ''));
$password = (string)($payload['password'] ?? '');
$confirmPassword = (string)($payload['confirm_password'] ?? '');

if (strlen($username) < 3 || strlen($username) > 50) {
    registerRespond(400, ['success' => false, 'message' => 'Choose a username between 3 and 50 characters.']);
}
if (preg_match('/[\x00-\x1F\x7F]/', $username)) {
    registerRespond(400, ['success' => false, 'message' => 'Usernames cannot contain control characters.']);
}
if (strlen($password) < 6 || strlen($password) > 72) {
    registerRespond(400, ['success' => false, 'message' => 'Your password must be between 6 and 72 characters.']);
}
if (!hash_equals($password, $confirmPassword)) {
    registerRespond(400, ['success' => false, 'message' => 'The passwords do not match.']);
}

try {
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('The account database is unavailable.');
    }
    $exists = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $exists->execute([$username]);
    if ($exists->fetchColumn()) {
        registerRespond(409, ['success' => false, 'message' => 'That username is already in use. Choose another.']);
    }
    $insert = $pdo->prepare('INSERT INTO users (username, password, profile_image) VALUES (?, ?, ?)');
    $insert->execute([$username, password_hash($password, PASSWORD_DEFAULT), '']);
    $userId = (int)$pdo->lastInsertId();
    $user = [
        'id' => $userId,
        'username' => $username,
        'profile_image' => '',
        'created_at' => date('Y-m-d H:i:s'),
    ];

    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $pdo->prepare('UPDATE users SET last_seen = NOW() WHERE id = ?')->execute([$userId]);

    registerRespond(201, ['success' => true, 'message' => 'Your Virtual Phone account is ready.', 'user' => $user]);
} catch (PDOException $error) {
    if ((string)$error->getCode() === '23000') {
        registerRespond(409, ['success' => false, 'message' => 'That username is already in use. Choose another.']);
    }
    error_log('Virtual Phone account registration failed: ' . $error->getMessage());
    registerRespond(500, ['success' => false, 'message' => 'Your account could not be created. Please try again.']);
} catch (Throwable $error) {
    error_log('Virtual Phone account registration failed: ' . $error->getMessage());
    registerRespond(500, ['success' => false, 'message' => 'Your account could not be created. Please try again.']);
}
