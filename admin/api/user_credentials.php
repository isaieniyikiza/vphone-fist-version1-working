<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

admin_require_login();

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    admin_json(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$contentType = strtolower(trim(explode(';', (string)($_SERVER['CONTENT_TYPE'] ?? ''))[0]));
if ($contentType !== 'application/json') {
    admin_json(['success' => false, 'message' => 'Send account changes as JSON.'], 415);
}

$origin = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
$originParts = parse_url($origin);
$requestHost = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
$requestScheme = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off'
    ? 'https'
    : 'http';
$originPort = is_array($originParts) ? (int)($originParts['port'] ?? (($originParts['scheme'] ?? '') === 'https' ? 443 : 80)) : 0;
$requestParts = parse_url($requestScheme . '://' . $requestHost);
$requestPort = is_array($requestParts) ? (int)($requestParts['port'] ?? ($requestScheme === 'https' ? 443 : 80)) : 0;
$normalizedRequestHost = strtolower(trim((string)($requestParts['host'] ?? ''), '[]'));
$normalizedOriginHost = strtolower(trim((string)($originParts['host'] ?? ''), '[]'));
if (!is_array($originParts) || !isset($originParts['scheme'], $originParts['host']) ||
    strtolower((string)$originParts['scheme']) !== $requestScheme ||
    !hash_equals($normalizedRequestHost, $normalizedOriginHost) ||
    $originPort !== $requestPort) {
    admin_json(['success' => false, 'message' => 'This account update request could not be verified. Reload the admin page and try again.'], 403);
}

try {
    $payload = json_decode((string)file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException) {
    admin_json(['success' => false, 'message' => 'The account update request is invalid.'], 400);
}

if (!is_array($payload)) {
    admin_json(['success' => false, 'message' => 'The account update request is invalid.'], 400);
}

$userId = filter_var($payload['user_id'] ?? null, FILTER_VALIDATE_INT);
if (!$userId || $userId < 1) {
    admin_json(['success' => false, 'message' => 'Select a valid user account.'], 400);
}

$hasUsername = array_key_exists('username', $payload);
$hasPasswordField = array_key_exists('new_password', $payload);
if ($hasPasswordField && !is_string($payload['new_password'])) {
    admin_json(['success' => false, 'message' => 'The new password must be text.'], 400);
}
$hasPassword = $hasPasswordField && $payload['new_password'] !== '';
$username = $hasUsername && is_string($payload['username']) ? trim($payload['username']) : '';
$newPassword = $hasPassword ? $payload['new_password'] : '';

if (!$hasUsername && !$hasPassword) {
    admin_json(['success' => false, 'message' => 'Enter a new username or password.'], 400);
}

if ($hasUsername && ($username === '' || preg_match('//u', $username) !== 1 ||
    (function_exists('mb_strlen') ? mb_strlen($username, 'UTF-8') : strlen($username)) > 50)) {
    admin_json(['success' => false, 'message' => 'Usernames must be between 1 and 50 characters.'], 400);
}

if ($hasPassword && (strlen($newPassword) < 8 || strlen($newPassword) > 72 || str_contains($newPassword, "\0"))) {
    admin_json(['success' => false, 'message' => 'The new password must be between 8 and 72 bytes.'], 400);
}

$db = admin_db();
try {
    $stmt = $db->prepare('SELECT id, username FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        admin_json(['success' => false, 'message' => 'That user account no longer exists.'], 404);
    }

    $usernameChanged = $hasUsername && $username !== (string)$user['username'];
    if (!$usernameChanged && !$hasPassword) {
        admin_json(['success' => false, 'message' => 'Enter a different username or a new password.'], 400);
    }

    $changes = [];
    $values = [];
    if ($usernameChanged) {
        $changes[] = 'username = ?';
        $values[] = $username;
    }
    if ($hasPassword) {
        $changes[] = 'password = ?';
        $values[] = password_hash($newPassword, PASSWORD_DEFAULT);
    }
    $values[] = $userId;

    $update = $db->prepare('UPDATE users SET ' . implode(', ', $changes) . ' WHERE id = ?');
    $update->execute($values);

    admin_json([
        'success' => true,
        'message' => 'Login details updated.',
        'user' => ['id' => (int)$userId, 'username' => $usernameChanged ? $username : (string)$user['username']],
    ]);
} catch (PDOException $error) {
    if ((string)$error->getCode() === '23000') {
        admin_json(['success' => false, 'message' => 'That username is already in use. Choose another one.'], 409);
    }
    error_log('Virtual Phone admin account update failed: ' . $error->getMessage());
    admin_json(['success' => false, 'message' => 'The account login details could not be updated.'], 500);
}
