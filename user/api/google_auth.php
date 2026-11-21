<?php
declare(strict_types=1);

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'path' => '/',
]);
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/../../db.php';

final class GoogleAuthException extends RuntimeException
{
    public function __construct(string $message, public readonly int $status)
    {
        parent::__construct($message);
    }
}

function respond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function googleLinkPopup(string $type, string $message = ''): never
{
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    $event = json_encode(
        ['type' => $type, 'message' => $message],
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );
    echo '<!doctype html><meta charset="utf-8"><title>Google account</title><p>'
        . htmlspecialchars($message ?: 'Google account verified. Return to your Virtual Phone.', ENT_QUOTES, 'UTF-8')
        . '</p><script>if(window.opener){window.opener.postMessage(' . $event . ',window.location.origin)}window.close()</script>';
    exit;
}

function getGoogleClientId(): string
{
    $clientIdPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'google' . DIRECTORY_SEPARATOR . 'token.txt';
    if (!is_file($clientIdPath) || !is_readable($clientIdPath)) {
        throw new RuntimeException('Google OAuth client ID is missing from google/token.txt.');
    }

    $clientId = trim((string)file_get_contents($clientIdPath));
    if (!preg_match('/^[0-9]+-[A-Za-z0-9_-]+\.apps\.googleusercontent\.com$/', $clientId)) {
        throw new RuntimeException('google/token.txt must contain the Google OAuth web client ID.');
    }

    return $clientId;
}

function getGoogleClientSecret(): string
{
    $secretPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'google' . DIRECTORY_SEPARATOR . 'api.txt';
    if (!is_file($secretPath) || !is_readable($secretPath)) {
        throw new RuntimeException('Google OAuth client secret is missing from google/api.txt.');
    }

    $clientSecret = trim((string)file_get_contents($secretPath));
    if (!preg_match('/^GOCSPX-[A-Za-z0-9_-]+$/', $clientSecret)) {
        throw new RuntimeException('google/api.txt must contain the OAuth client secret for the same Google client ID.');
    }

    return $clientSecret;
}

function getGoogleRedirectUri(): string
{
    return 'http://localhost/virtual-phone/user/api/google_callback.php';
}

function verifyGoogleCredential(string $credential, string $clientId): array
{
    if ($credential === '' || strlen($credential) > 8192) {
        throw new GoogleAuthException('Google sign-in credential is missing or invalid.', 400);
    }

    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($credential);
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'ignore_errors' => true,
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    set_error_handler(static function (int $severity, string $message): never {
        throw new GoogleAuthException('Unable to verify the Google credential. Check the server connection.', 502);
    });
    try {
        $response = file_get_contents($url, false, $context);
    } finally {
        restore_error_handler();
    }

    $statusLine = $http_response_header[0] ?? '';
    if ($response === false || !preg_match('/\s200\s/', $statusLine)) {
        throw new GoogleAuthException('Google rejected the sign-in. Please try again.', 401);
    }

    $claims = json_decode($response, true);
    if (!is_array($claims)
        || ($claims['aud'] ?? '') !== $clientId
        || !in_array($claims['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true)
        || !isset($claims['exp'])
        || (int)$claims['exp'] <= time()
        || !isset($claims['sub'])
        || !isset($claims['email'])
        || filter_var($claims['email'], FILTER_VALIDATE_EMAIL) === false
        || !in_array($claims['email_verified'] ?? false, [true, 'true', 1, '1'], true)
        || (isset($claims['azp']) && $claims['azp'] !== $clientId)) {
        throw new GoogleAuthException('Google returned an invalid or unverified account.', 401);
    }

    return [
        'sub' => (string)$claims['sub'],
        'email' => strtolower((string)$claims['email']),
    ];
}

function ensureGoogleColumns(PDO $pdo): void
{
    $columns = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN, 0);
    if (!in_array('email', $columns, true)) {
        $pdo->exec('ALTER TABLE users ADD COLUMN email VARCHAR(254) NULL');
    }
    if (!in_array('google_sub', $columns, true)) {
        $pdo->exec('ALTER TABLE users ADD COLUMN google_sub VARCHAR(255) NULL');
    }
    if (!in_array('recovery_email', $columns, true)) {
        $pdo->exec('ALTER TABLE users ADD COLUMN recovery_email VARCHAR(254) NULL');
    }

    $indexes = $pdo->query('SHOW INDEX FROM users')->fetchAll(PDO::FETCH_ASSOC);
    $hasGoogleIndex = false;
    foreach ($indexes as $index) {
        if (($index['Key_name'] ?? '') === 'uq_users_google_sub') {
            $hasGoogleIndex = true;
            break;
        }
    }
    if (!$hasGoogleIndex) {
        $duplicate = $pdo->query(
            "SELECT google_sub FROM users WHERE google_sub IS NOT NULL GROUP BY google_sub HAVING COUNT(*) > 1 LIMIT 1"
        )->fetchColumn();
        if ($duplicate !== false) {
            throw new RuntimeException('Duplicate Google account links must be resolved before Google sign-in can be enabled.');
        }
        $pdo->exec('ALTER TABLE users ADD UNIQUE KEY uq_users_google_sub (google_sub)');
    }
}

function createGooglePending(PDO $pdo, array $identity): array
{
    ensureGoogleColumns($pdo);

    $bySubject = $pdo->prepare('SELECT id, username, email, google_sub, is_blocked FROM users WHERE google_sub = ? LIMIT 1');
    $bySubject->execute([$identity['sub']]);
    $user = $bySubject->fetch(PDO::FETCH_ASSOC);
    $targetUserId = null;

    if ($user) {
        $mode = 'password';
    } else {
        $byEmail = $pdo->prepare('SELECT id, username, email, google_sub, is_blocked FROM users WHERE email = ? LIMIT 1');
        $byEmail->execute([$identity['email']]);
        $user = $byEmail->fetch(PDO::FETCH_ASSOC);
        if ($user && !empty($user['google_sub'])) {
            throw new GoogleAuthException('This email is already linked to a different Google account.', 409);
        }
        if ($user) {
            $mode = 'link';
            $targetUserId = (int)$user['id'];
        } else {
            $mode = 'register';
        }
    }

    if ($user && (int)($user['is_blocked'] ?? 0) === 1) {
        throw new GoogleAuthException('This account has been blocked. Contact the administrator.', 403);
    }

    $_SESSION['google_pending'] = [
        'sub' => $identity['sub'],
        'email' => $identity['email'],
        'target_user_id' => $targetUserId,
        'mode' => $mode,
        'expires' => time() + 300,
    ];

    return ['mode' => $mode, 'email' => $identity['email']];
}

function profileImageUrl(?string $profileImage): string
{
    if (!$profileImage) {
        return '';
    }
    if (preg_match('#^https?://#', $profileImage) || str_starts_with($profileImage, '/')) {
        return $profileImage;
    }
    return '/virtual-phone/uploads/profile/' . $profileImage;
}

function finishLogin(PDO $pdo, array $user): never
{
    if ((int)($user['is_blocked'] ?? 0) === 1) {
        respond(403, ['success' => false, 'message' => 'This account has been blocked. Contact the administrator.']);
    }

    session_regenerate_id(true);
    unset($_SESSION['google_pending']);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $pdo->prepare('UPDATE users SET last_seen = NOW() WHERE id = ?')->execute([$user['id']]);

    respond(200, [
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'profile_image' => profileImageUrl($user['profile_image'] ?? null),
            'created_at' => $user['created_at'] ?? null,
        ],
    ]);
}

try {
    $oauthPurpose = '';
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new RuntimeException('Database connection is unavailable.');
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    if ($action === 'config' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        respond(200, ['success' => true, 'client_id' => getGoogleClientId()]);
    }
    if ($action === 'account' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$userId || $userId < 1) {
            respond(401, ['success' => false, 'message' => 'Sign in to manage account recovery.']);
        }
        ensureGoogleColumns($pdo);
        $stmt = $pdo->prepare('SELECT email, recovery_email, google_sub, is_blocked FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$account) {
            respond(404, ['success' => false, 'message' => 'Your Virtual Phone account could not be found.']);
        }
        if ((int)($account['is_blocked'] ?? 0) === 1) {
            respond(403, ['success' => false, 'message' => 'This account has been blocked. Contact the administrator.']);
        }
        respond(200, [
            'success' => true,
            'linked' => !empty($account['google_sub']),
            'email' => !empty($account['google_sub'])
                ? (string)($account['recovery_email'] ?: $account['email'] ?: '')
                : '',
        ]);
    }
    if ($action === 'status' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!empty($_SESSION['google_error'])) {
            $message = (string)$_SESSION['google_error'];
            unset($_SESSION['google_error']);
            respond(400, ['success' => false, 'message' => $message]);
        }
        $pending = $_SESSION['google_pending'] ?? null;
        if (is_array($pending) && (int)($pending['expires'] ?? 0) >= time()) {
            respond(200, [
                'success' => true,
                'pending' => true,
                'mode' => $pending['mode'],
                'email' => $pending['email'],
            ]);
        }
        respond(200, ['success' => true, 'pending' => false]);
    }
    if ($action === 'authorize' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $purpose = (string)($_GET['purpose'] ?? '');
        if ($purpose !== '' && $purpose !== 'account_link') {
            respond(400, ['success' => false, 'message' => 'Invalid Google sign-in request.']);
        }
        if ($purpose === 'account_link' && empty($_SESSION['user_id'])) {
            respond(401, ['success' => false, 'message' => 'Sign in to your Virtual Phone before linking a recovery account.']);
        }
        $state = bin2hex(random_bytes(32));
        $_SESSION['google_oauth_state'] = $state;
        $_SESSION['google_oauth_purpose'] = $purpose;
        $_SESSION['google_oauth_user_id'] = $purpose === 'account_link' ? (int)$_SESSION['user_id'] : null;
        if ($purpose === 'account_link') {
            unset($_SESSION['google_settings_pending']);
        }
        $authorizationUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => getGoogleClientId(),
            'redirect_uri' => getGoogleRedirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ], '', '&', PHP_QUERY_RFC3986);
        header('Location: ' . $authorizationUrl, true, 302);
        exit;
    }
    if ($action === 'callback' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $oauthPurpose = (string)($_SESSION['google_oauth_purpose'] ?? '');
        $oauthUserId = (int)($_SESSION['google_oauth_user_id'] ?? 0);
        $expectedState = (string)($_SESSION['google_oauth_state'] ?? '');
        unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_purpose'], $_SESSION['google_oauth_user_id']);
        $actualState = (string)($_GET['state'] ?? '');
        if ($expectedState === '' || $actualState === '' || !hash_equals($expectedState, $actualState)) {
            throw new GoogleAuthException('Google sign-in expired or failed its security check. Please try again.', 400);
        }
        if (!empty($_GET['error'])) {
            if ((string)$_GET['error'] === 'access_denied') {
                throw new GoogleAuthException('Google blocked this app because its OAuth project is in Testing. Add your Google email under Google Cloud Console → Google Auth Platform → Audience → Test users, or complete verification before allowing other accounts.', 403);
            }
            throw new GoogleAuthException('Google sign-in was cancelled or denied. Please try again.', 401);
        }

        $code = (string)($_GET['code'] ?? '');
        if ($code === '' || strlen($code) > 4096) {
            throw new GoogleAuthException('Google did not return an authorization code. Please try again.', 400);
        }
        $tokenContext = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query([
                    'code' => $code,
                    'client_id' => getGoogleClientId(),
                    'client_secret' => getGoogleClientSecret(),
                    'redirect_uri' => getGoogleRedirectUri(),
                    'grant_type' => 'authorization_code',
                ], '', '&', PHP_QUERY_RFC3986),
                'timeout' => 10,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        set_error_handler(static function (int $severity, string $message): never {
            throw new GoogleAuthException('Unable to complete Google sign-in. Check the server connection and try again.', 502);
        });
        try {
            $tokenResponse = file_get_contents('https://oauth2.googleapis.com/token', false, $tokenContext);
        } finally {
            restore_error_handler();
        }
        $tokenData = is_string($tokenResponse) ? json_decode($tokenResponse, true) : null;
        if (!is_array($tokenData) || empty($tokenData['id_token'])) {
            throw new GoogleAuthException('Google could not exchange the sign-in code. Verify the OAuth client secret and redirect URI.', 502);
        }

        $identity = verifyGoogleCredential((string)$tokenData['id_token'], getGoogleClientId());
        if ($oauthPurpose === 'account_link') {
            $currentUserId = (int)($_SESSION['user_id'] ?? 0);
            if ($oauthUserId < 1 || $currentUserId !== $oauthUserId) {
                throw new GoogleAuthException('Your Virtual Phone session changed. Sign in again and retry.', 401);
            }
            ensureGoogleColumns($pdo);
            $owner = $pdo->prepare('SELECT id FROM users WHERE google_sub = ? LIMIT 1');
            $owner->execute([$identity['sub']]);
            $ownerId = $owner->fetchColumn();
            if ($ownerId !== false && (int)$ownerId !== $oauthUserId) {
                throw new GoogleAuthException('This Google account is already linked to another Virtual Phone account.', 409);
            }
            $_SESSION['google_settings_pending'] = [
                'sub' => $identity['sub'],
                'email' => $identity['email'],
                'user_id' => $oauthUserId,
                'already_linked' => $ownerId !== false,
                'expires' => time() + 300,
            ];
            googleLinkPopup('vphone-google-link-ready');
        }
        createGooglePending($pdo, $identity);
        header('Location: /virtual-phone/user/user_phone_dashboard.html?google=continue', true, 302);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(405, ['success' => false, 'message' => 'Method not allowed.']);
    }

    if ($action === 'begin') {
        $clientId = getGoogleClientId();
        $identity = verifyGoogleCredential((string)($_POST['credential'] ?? ''), $clientId);
        $pending = createGooglePending($pdo, $identity);
        respond(200, ['success' => true] + $pending);
    }

    if ($action === 'complete') {
        $pending = $_SESSION['google_pending'] ?? null;
        if (!is_array($pending) || (int)($pending['expires'] ?? 0) < time()) {
            unset($_SESSION['google_pending']);
            respond(401, ['success' => false, 'message' => 'Your Google sign-in expired. Start again.']);
        }
        ensureGoogleColumns($pdo);

        $mode = (string)($_POST['mode'] ?? '');
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');
        if (strlen($password) < 6) {
            respond(400, ['success' => false, 'message' => 'Phone password must be at least 6 characters.']);
        }
        if (strlen($password) > 72) {
            respond(400, ['success' => false, 'message' => 'Choose a phone password no longer than 72 characters.']);
        }

        if ($mode === 'password') {
            $stmt = $pdo->prepare('SELECT id, username, password, profile_image, created_at, is_blocked FROM users WHERE google_sub = ? LIMIT 1');
            $stmt->execute([$pending['sub']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user || !password_verify($password, $user['password'])) {
                respond(401, ['success' => false, 'message' => 'Incorrect Virtual Phone password.']);
            }
            finishLogin($pdo, $user);
        }

        if ($mode === 'recover') {
            if (!hash_equals($password, $confirmPassword)) {
                respond(400, ['success' => false, 'message' => 'The new passwords do not match.']);
            }
            $stmt = $pdo->prepare('SELECT id, username, password, profile_image, created_at, is_blocked FROM users WHERE google_sub = ? LIMIT 1');
            $stmt->execute([$pending['sub']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                respond(401, ['success' => false, 'message' => 'This Google account is not linked to a Virtual Phone account.']);
            }
            if ((int)($user['is_blocked'] ?? 0) === 1) {
                respond(403, ['success' => false, 'message' => 'This account has been blocked. Contact the administrator.']);
            }
            $update = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $update->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
            finishLogin($pdo, $user);
        }

        if ($mode === 'link') {
            if ($username === '') {
                respond(400, ['success' => false, 'message' => 'Enter your existing Virtual Phone username.']);
            }
            $stmt = $pdo->prepare('SELECT id, username, password, profile_image, created_at, is_blocked, email, google_sub FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user
                || !password_verify($password, $user['password'])
                || ($pending['target_user_id'] !== null && (int)$user['id'] !== (int)$pending['target_user_id'])) {
                respond(401, ['success' => false, 'message' => 'The username or password does not match the account to link.']);
            }
            if ((int)($user['is_blocked'] ?? 0) === 1) {
                respond(403, ['success' => false, 'message' => 'This account has been blocked. Contact the administrator.']);
            }
            if (!empty($user['google_sub']) && $user['google_sub'] !== $pending['sub']) {
                respond(409, ['success' => false, 'message' => 'This Virtual Phone account is already linked to another Google account.']);
            }
            if (!empty($user['email']) && strtolower((string)$user['email']) !== $pending['email']) {
                respond(409, ['success' => false, 'message' => 'This Virtual Phone account already uses a different email address.']);
            }
            $link = $pdo->prepare('UPDATE users SET email = ?, google_sub = ? WHERE id = ? AND (google_sub IS NULL OR google_sub = ?)');
            $link->execute([$pending['email'], $pending['sub'], $user['id'], $pending['sub']]);
            if ($link->rowCount() !== 1 && $user['google_sub'] !== $pending['sub']) {
                respond(409, ['success' => false, 'message' => 'This account could not be linked. Please try again.']);
            }
            finishLogin($pdo, $user);
        }

        if ($mode === 'register') {
            if (!hash_equals($password, $confirmPassword)) {
                respond(400, ['success' => false, 'message' => 'The passwords do not match.']);
            }
            if ($username === '' || strlen($username) < 3 || strlen($username) > 50) {
                respond(400, ['success' => false, 'message' => 'Choose a username between 3 and 50 characters.']);
            }
            if (strlen($password) > 72) {
                respond(400, ['success' => false, 'message' => 'Choose a phone password no longer than 72 characters.']);
            }
            $exists = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            $exists->execute([$username]);
            if ($exists->fetchColumn()) {
                respond(409, ['success' => false, 'message' => 'That username is already in use. Choose another.']);
            }
            $exists = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $exists->execute([$pending['email']]);
            if ($exists->fetchColumn()) {
                respond(409, ['success' => false, 'message' => 'An account already uses this email. Choose “Link an existing account”.']);
            }

            $insert = $pdo->prepare('INSERT INTO users (username, password, profile_image, email, google_sub) VALUES (?, ?, ?, ?, ?)');
            $insert->execute([
                $username,
                password_hash($password, PASSWORD_DEFAULT),
                '',
                $pending['email'],
                $pending['sub'],
            ]);
            $user = [
                'id' => $pdo->lastInsertId(),
                'username' => $username,
                'profile_image' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'is_blocked' => 0,
            ];
            finishLogin($pdo, $user);
        }

        respond(400, ['success' => false, 'message' => 'Invalid Google sign-in step. Start again.']);
    }

    if ($action === 'link_account') {
        $pending = $_SESSION['google_settings_pending'] ?? null;
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if (!is_array($pending) || (int)($pending['expires'] ?? 0) < time()
            || !$userId || (int)($pending['user_id'] ?? 0) !== $userId) {
            unset($_SESSION['google_settings_pending']);
            respond(401, ['success' => false, 'message' => 'Google verification expired. Start linking again.']);
        }
        $password = (string)($_POST['password'] ?? '');
        $stmt = $pdo->prepare('SELECT password, google_sub, is_blocked FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || !password_verify($password, (string)$user['password'])) {
            respond(401, ['success' => false, 'message' => 'Enter your current Virtual Phone password to confirm this change.']);
        }
        if ((int)($user['is_blocked'] ?? 0) === 1) {
            respond(403, ['success' => false, 'message' => 'This account has been blocked. Contact the administrator.']);
        }
        if (!empty($user['google_sub']) && !hash_equals((string)$user['google_sub'], (string)$pending['sub'])) {
            respond(409, ['success' => false, 'message' => 'A different Google account is already linked.']);
        }
        $owner = $pdo->prepare('SELECT id FROM users WHERE google_sub = ? AND id <> ? LIMIT 1');
        $owner->execute([$pending['sub'], $userId]);
        if ($owner->fetchColumn()) {
            respond(409, ['success' => false, 'message' => 'This Google account is already linked to another Virtual Phone account.']);
        }
        $update = $pdo->prepare('UPDATE users SET google_sub = ?, recovery_email = ?, email = CASE WHEN email IS NULL OR email = \'\' THEN ? ELSE email END WHERE id = ?');
        $update->execute([$pending['sub'], $pending['email'], $pending['email'], $userId]);
        unset($_SESSION['google_settings_pending']);
        respond(200, ['success' => true, 'email' => (string)$pending['email'], 'message' => 'Your verified Google account is ready for account recovery.']);
    }

    respond(400, ['success' => false, 'message' => 'Invalid request.']);
} catch (GoogleAuthException $error) {
    if (($action ?? '') === 'callback') {
        if ($oauthPurpose === 'account_link') {
            unset($_SESSION['google_oauth_purpose'], $_SESSION['google_oauth_user_id'], $_SESSION['google_oauth_state']);
            googleLinkPopup('vphone-google-link-error', $error->getMessage());
        }
        $_SESSION['google_error'] = $error->getMessage();
        header('Location: /virtual-phone/user/user_phone_dashboard.html?google=error', true, 302);
        exit;
    }
    respond($error->status, ['success' => false, 'message' => $error->getMessage()]);
} catch (RuntimeException $error) {
    if (($action ?? '') === 'callback') {
        if ($oauthPurpose === 'account_link') {
            unset($_SESSION['google_oauth_purpose'], $_SESSION['google_oauth_user_id'], $_SESSION['google_oauth_state']);
            googleLinkPopup('vphone-google-link-error', $error->getMessage());
        }
        $_SESSION['google_error'] = $error->getMessage();
        header('Location: /virtual-phone/user/user_phone_dashboard.html?google=error', true, 302);
        exit;
    }
    respond(500, ['success' => false, 'message' => $error->getMessage()]);
} catch (PDOException $error) {
    if (($action ?? '') === 'callback') {
        if ($oauthPurpose === 'account_link') {
            unset($_SESSION['google_oauth_purpose'], $_SESSION['google_oauth_user_id'], $_SESSION['google_oauth_state']);
            googleLinkPopup('vphone-google-link-error', 'Google verification could not complete because of a database error.');
        }
        $_SESSION['google_error'] = 'Google sign-in could not complete because of a database error.';
        header('Location: /virtual-phone/user/user_phone_dashboard.html?google=error', true, 302);
        exit;
    }
    respond(500, ['success' => false, 'message' => 'Google sign-in could not complete because of a database error.']);
}
