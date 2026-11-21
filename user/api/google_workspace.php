<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'path' => '/',
    ]);
    session_start();
}

require_once __DIR__ . '/../../db.php';

const VPHONE_GOOGLE_BASE_SCOPES = ['openid', 'email', 'profile'];

function workspaceScopesForApp(string $app): array
{
    $appScopes = [
        'drive' => ['https://www.googleapis.com/auth/drive'],
        'contacts' => ['https://www.googleapis.com/auth/contacts'],
        'photos' => ['https://www.googleapis.com/auth/photospicker.mediaitems.readonly'],
    ];
    if (!isset($appScopes[$app])) {
        throw new RuntimeException('Choose Drive, Photos, or Contacts to connect Google.');
    }
    return array_merge(VPHONE_GOOGLE_BASE_SCOPES, $appScopes[$app]);
}

function workspaceJson(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function workspaceDb(): PDO
{
    global $pdo;
    if (!$pdo instanceof PDO) {
        workspaceJson(['success' => false, 'message' => 'The application database is unavailable.'], 500);
    }
    return $pdo;
}

function workspaceUserId(): int
{
    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$userId || $userId < 1) {
        workspaceJson(['success' => false, 'message' => 'Sign in to your Virtual Phone first.'], 401);
    }
    return $userId;
}

function workspaceEnsureSchema(): void
{
    $db = workspaceDb();
    $db->exec("CREATE TABLE IF NOT EXISTS vphone_google_connections (
        user_id INT NOT NULL PRIMARY KEY,
        google_sub VARCHAR(255) NOT NULL,
        google_email VARCHAR(254) NOT NULL,
        encrypted_tokens MEDIUMTEXT NOT NULL,
        granted_scopes TEXT NOT NULL,
        connected_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->exec("CREATE TABLE IF NOT EXISTS vphone_google_contact_map (
        user_id INT NOT NULL,
        resource_name VARCHAR(255) NOT NULL,
        contact_id INT NOT NULL,
        etag VARCHAR(255) NOT NULL DEFAULT '',
        PRIMARY KEY (user_id, resource_name),
        UNIQUE KEY uq_vphone_google_contact_local (user_id, contact_id),
        INDEX idx_vphone_google_contact_id (contact_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function workspaceClientId(): string
{
    $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'google' . DIRECTORY_SEPARATOR . 'token.txt';
    $value = is_readable($path) ? trim((string)file_get_contents($path)) : '';
    if (!preg_match('/^[0-9]+-[A-Za-z0-9_-]+\.apps\.googleusercontent\.com$/', $value)) {
        throw new RuntimeException('Google OAuth client ID is missing or invalid. Check the server-side Google setup.');
    }
    return $value;
}

function workspaceClientSecret(): string
{
    $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'google' . DIRECTORY_SEPARATOR . 'api.txt';
    $value = is_readable($path) ? trim((string)file_get_contents($path)) : '';
    if (!preg_match('/^GOCSPX-[A-Za-z0-9_-]+$/', $value)) {
        throw new RuntimeException('Google OAuth client secret is missing or invalid. Check the server-side Google setup.');
    }
    return $value;
}

function workspaceRedirectUri(): string
{
    return 'http://localhost/virtual-phone/user/api/google_callback.php';
}

function workspaceCryptoKey(): string
{
    if (!function_exists('openssl_encrypt') || !function_exists('hash_hkdf')) {
        throw new RuntimeException('Secure server-side Google token storage requires PHP OpenSSL support.');
    }
    return hash_hkdf('sha256', workspaceClientSecret(), 32, 'virtual-phone-google-token-storage-v1');
}

function workspaceEncryptTokens(array $tokens): string
{
    $iv = random_bytes(12);
    $tag = '';
    $cipherText = openssl_encrypt(
        json_encode($tokens, JSON_THROW_ON_ERROR),
        'aes-256-gcm',
        workspaceCryptoKey(),
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        'virtual-phone-google-token-v1'
    );
    if (!is_string($cipherText)) {
        throw new RuntimeException('Google authorization could not be stored securely.');
    }
    return base64_encode($iv . $tag . $cipherText);
}

function workspaceDecryptTokens(string $encoded): array
{
    $packed = base64_decode($encoded, true);
    if (!is_string($packed) || strlen($packed) < 29) {
        throw new RuntimeException('The saved Google connection is damaged. Reconnect the Google account.');
    }
    $plain = openssl_decrypt(
        substr($packed, 28),
        'aes-256-gcm',
        workspaceCryptoKey(),
        OPENSSL_RAW_DATA,
        substr($packed, 0, 12),
        substr($packed, 12, 16),
        'virtual-phone-google-token-v1'
    );
    $tokens = is_string($plain) ? json_decode($plain, true) : null;
    if (!is_array($tokens)) {
        throw new RuntimeException('The saved Google connection cannot be decrypted. Reconnect the Google account.');
    }
    return $tokens;
}

function workspaceHttp(string $url, string $method = 'GET', array $headers = [], ?string $body = null): array
{
    $headerLines = array_merge(['Accept: application/json'], $headers);
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headerLines),
            'content' => $body ?? '',
            'timeout' => 25,
            'ignore_errors' => true,
            'follow_location' => 0,
        ],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);
    set_error_handler(static function (): bool {
        return true;
    });
    try {
        $responseBody = file_get_contents($url, false, $context);
        $responseHeaders = $http_response_header ?? [];
    } finally {
        restore_error_handler();
    }
    $status = 0;
    foreach ($responseHeaders as $line) {
        if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $line, $matches)) {
            $status = (int)$matches[1];
        }
    }
    if (!is_string($responseBody)) {
        throw new RuntimeException('Google could not be reached. Check the server internet connection and try again.');
    }
    return ['status' => $status, 'headers' => $responseHeaders, 'body' => $responseBody];
}

function workspaceGoogleJson(string $url, string $accessToken, string $method = 'GET', ?array $payload = null): array
{
    $headers = ['Authorization: Bearer ' . $accessToken];
    $body = null;
    if ($payload !== null) {
        $headers[] = 'Content-Type: application/json; charset=utf-8';
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
    $response = workspaceHttp($url, $method, $headers, $body);
    $data = json_decode($response['body'], true);
    if ($response['status'] >= 200 && $response['status'] < 300 && $response['body'] === '') {
        return [];
    }
    if ($response['status'] < 200 || $response['status'] >= 300 || !is_array($data)) {
        $googleMessage = is_array($data) ? (string)($data['error']['message'] ?? '') : '';
        if ($response['status'] === 401) {
            throw new RuntimeException('Google authorization expired. Reconnect your Google account.');
        }
        if ($response['status'] === 403 && $googleMessage === '') {
            throw new RuntimeException('Google denied access. Check that the requested Google API is enabled and authorized.');
        }
        throw new RuntimeException($googleMessage !== ''
            ? 'Google API error: ' . $googleMessage
            : 'Google returned an invalid response. Please try again.');
    }
    return $data;
}

function workspaceConnectionRow(int $userId): ?array
{
    $stmt = workspaceDb()->prepare('SELECT google_sub, google_email, encrypted_tokens, granted_scopes FROM vphone_google_connections WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function workspaceSaveConnection(int $userId, string $subject, string $email, array $tokens): void
{
    $db = workspaceDb();
    $existing = workspaceConnectionRow($userId);
    if (empty($tokens['refresh_token']) && $existing) {
        $tokens['refresh_token'] = workspaceDecryptTokens((string)$existing['encrypted_tokens'])['refresh_token'] ?? '';
    }
    if (empty($tokens['refresh_token'])) {
        throw new RuntimeException('Google did not provide ongoing access. Retry and approve the requested permissions.');
    }
    $scopeString = (string)($tokens['scope'] ?? '');
    $stmt = $db->prepare("INSERT INTO vphone_google_connections
        (user_id, google_sub, google_email, encrypted_tokens, granted_scopes)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE google_sub = VALUES(google_sub), google_email = VALUES(google_email),
            encrypted_tokens = VALUES(encrypted_tokens), granted_scopes = VALUES(granted_scopes)");
    $stmt->execute([$userId, $subject, $email, workspaceEncryptTokens($tokens), $scopeString]);
}

function workspaceAccessToken(int $userId): string
{
    $row = workspaceConnectionRow($userId);
    if (!$row) {
        workspaceJson(['success' => false, 'message' => 'Connect a Google account to use this app.'], 409);
    }
    $tokens = workspaceDecryptTokens((string)$row['encrypted_tokens']);
    if (!empty($tokens['access_token']) && (int)($tokens['expires_at'] ?? 0) > time() + 60) {
        return (string)$tokens['access_token'];
    }
    if (empty($tokens['refresh_token'])) {
        workspaceJson(['success' => false, 'message' => 'Google authorization expired. Reconnect your Google account.'], 401);
    }
    $response = workspaceHttp('https://oauth2.googleapis.com/token', 'POST', [
        'Content-Type: application/x-www-form-urlencoded',
    ], http_build_query([
        'client_id' => workspaceClientId(),
        'client_secret' => workspaceClientSecret(),
        'refresh_token' => $tokens['refresh_token'],
        'grant_type' => 'refresh_token',
    ], '', '&', PHP_QUERY_RFC3986));
    $updated = json_decode($response['body'], true);
    if ($response['status'] < 200 || $response['status'] >= 300 || !is_array($updated) || empty($updated['access_token'])) {
        workspaceJson(['success' => false, 'message' => 'Google authorization expired. Reconnect your Google account.'], 401);
    }
    $tokens = array_merge($tokens, $updated);
    $tokens['expires_at'] = time() + (int)($updated['expires_in'] ?? 3600);
    workspaceSaveConnection($userId, (string)$row['google_sub'], (string)$row['google_email'], $tokens);
    return (string)$tokens['access_token'];
}

function workspaceVerifyIdToken(string $idToken): array
{
    $response = workspaceHttp('https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($idToken));
    $claims = json_decode($response['body'], true);
    if ($response['status'] !== 200 || !is_array($claims)
        || ($claims['aud'] ?? '') !== workspaceClientId()
        || !in_array($claims['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true)
        || (int)($claims['exp'] ?? 0) <= time()
        || empty($claims['sub'])
        || empty($claims['email'])
        || filter_var($claims['email'], FILTER_VALIDATE_EMAIL) === false
        || !in_array($claims['email_verified'] ?? false, [true, 'true', 1, '1'], true)) {
        throw new RuntimeException('Google returned an invalid account identity. Please connect again.');
    }
    return ['sub' => (string)$claims['sub'], 'email' => strtolower((string)$claims['email'])];
}

function workspacePeopleList(string $accessToken): array
{
    $people = [];
    $pageToken = '';
    $pages = 0;
    do {
        $query = [
            'personFields' => 'names,emailAddresses,phoneNumbers,photos,metadata',
            'pageSize' => '1000',
            'sources' => 'READ_SOURCE_TYPE_CONTACT',
        ];
        if ($pageToken !== '') {
            $query['pageToken'] = $pageToken;
        }
        $data = workspaceGoogleJson(
            'https://people.googleapis.com/v1/people/me/connections?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986),
            $accessToken
        );
        foreach (($data['connections'] ?? []) as $person) {
            if (is_array($person) && !empty($person['resourceName'])) {
                $people[] = $person;
            }
        }
        $pageToken = (string)($data['nextPageToken'] ?? '');
        $pages++;
        if ($pages >= 30 && $pageToken !== '') {
            throw new RuntimeException('This Google account has too many contacts to sync in one pass. Try importing fewer contacts from Google.');
        }
    } while ($pageToken !== '');
    return $people;
}

function workspacePersonName(array $person): string
{
    $name = trim((string)($person['names'][0]['displayName'] ?? ''));
    if ($name !== '') {
        return mb_substr($name, 0, 100, 'UTF-8');
    }
    return mb_substr((string)($person['emailAddresses'][0]['value'] ?? $person['phoneNumbers'][0]['value'] ?? 'Google contact'), 0, 100, 'UTF-8');
}

function workspaceContactNumber(array $person): string
{
    $value = trim((string)($person['phoneNumbers'][0]['value'] ?? $person['emailAddresses'][0]['value'] ?? ''));
    return mb_substr($value, 0, 50, 'UTF-8');
}

function workspaceCreatePhotoPickerSession(int $userId, string $accessToken): array
{
    $session = workspaceGoogleJson('https://photospicker.googleapis.com/v1/sessions', $accessToken, 'POST', []);
    if (empty($session['id']) || empty($session['pickerUri'])) {
        throw new RuntimeException('Google Photos could not start a picker session.');
    }
    $_SESSION['vphone_google_photo_picker'] = [
        'id' => (string)$session['id'],
        'user_id' => $userId,
        'created_at' => time(),
    ];
    return $session;
}

function workspaceGoogleCallback(): never
{
    $expectedState = (string)($_SESSION['google_workspace_oauth_state'] ?? '');
    unset($_SESSION['google_workspace_oauth_state']);
    $actualState = (string)($_GET['state'] ?? '');
    if ($expectedState === '' || $actualState === '' || !hash_equals($expectedState, $actualState)) {
        throw new RuntimeException('Google authorization expired or failed its security check. Start again from the app.');
    }
    if (!empty($_GET['error'])) {
        if ((string)$_GET['error'] === 'access_denied') {
            throw new RuntimeException('Google blocked this app because its OAuth project is in Testing. In Google Cloud Console, open Google Auth Platform → Audience → Test users and add the Google email you are connecting. Until then, Google Drive, Photos, and Contacts cannot grant access.');
        }
        throw new RuntimeException('Google authorization was canceled or denied. Retry and approve the requested access.');
    }
    $code = (string)($_GET['code'] ?? '');
    $verifier = (string)($_SESSION['google_workspace_code_verifier'] ?? '');
    $userId = (int)($_SESSION['google_workspace_user_id'] ?? 0);
    unset($_SESSION['google_workspace_code_verifier'], $_SESSION['google_workspace_user_id'], $_SESSION['google_workspace_app']);
    if ($code === '' || strlen($code) > 4096 || $verifier === '' || $userId < 1
        || (int)($_SESSION['user_id'] ?? 0) !== $userId) {
        throw new RuntimeException('Google returned an incomplete authorization response.');
    }
    $response = workspaceHttp('https://oauth2.googleapis.com/token', 'POST', [
        'Content-Type: application/x-www-form-urlencoded',
    ], http_build_query([
        'code' => $code,
        'client_id' => workspaceClientId(),
        'client_secret' => workspaceClientSecret(),
        'redirect_uri' => workspaceRedirectUri(),
        'grant_type' => 'authorization_code',
        'code_verifier' => $verifier,
    ], '', '&', PHP_QUERY_RFC3986));
    $tokens = json_decode($response['body'], true);
    if ($response['status'] < 200 || $response['status'] >= 300 || !is_array($tokens) || empty($tokens['access_token']) || empty($tokens['id_token'])) {
        throw new RuntimeException('Google could not complete authorization. Check the OAuth client and consent-screen configuration.');
    }
    $identity = workspaceVerifyIdToken((string)$tokens['id_token']);
    $account = workspaceDb()->prepare('SELECT google_sub FROM users WHERE id = ? LIMIT 1');
    $account->execute([$userId]);
    $phoneGoogleSubject = $account->fetchColumn();
    if (is_string($phoneGoogleSubject) && $phoneGoogleSubject !== '' && !hash_equals($phoneGoogleSubject, $identity['sub'])) {
        throw new RuntimeException('Connect the same Google account that you used to sign in to this Virtual Phone account.');
    }
    $tokens['expires_at'] = time() + (int)($tokens['expires_in'] ?? 3600);
    workspaceSaveConnection($userId, $identity['sub'], $identity['email'], $tokens);

    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    echo '<!doctype html><meta charset="utf-8"><title>Google connected</title><p>Google account connected. Return to your Virtual Phone.</p><script>if(window.opener){window.opener.postMessage({type:"vphone-google-connected"},window.location.origin)}window.close()</script>';
    exit;
}

try {
    $userId = workspaceUserId();
    workspaceEnsureSchema();
    $action = (string)($_GET['action'] ?? $_POST['action'] ?? '');

    if ($action === 'callback' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        workspaceGoogleCallback();
    }

    if ($action === 'connect' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $state = bin2hex(random_bytes(32));
        $verifier = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
        $_SESSION['google_workspace_oauth_state'] = $state;
        $_SESSION['google_workspace_code_verifier'] = $verifier;
        $_SESSION['google_workspace_user_id'] = $userId;
        $app = (string)($_GET['app'] ?? '');
        $scopes = workspaceScopesForApp($app);
        $_SESSION['google_workspace_app'] = $app;
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
        $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => workspaceClientId(),
            'redirect_uri' => workspaceRedirectUri(),
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'state' => $state,
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
            'access_type' => 'offline',
            'include_granted_scopes' => 'true',
            'prompt' => 'consent select_account',
        ], '', '&', PHP_QUERY_RFC3986);
        header('Cache-Control: no-store');
        header('Location: ' . $url, true, 302);
        exit;
    }

    if ($action === 'status' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $row = workspaceConnectionRow($userId);
        workspaceJson([
            'success' => true,
            'connected' => (bool)$row,
            'email' => $row['google_email'] ?? '',
            'scopes' => $row ? preg_split('/\s+/', trim((string)$row['granted_scopes']), -1, PREG_SPLIT_NO_EMPTY) : [],
            'requiredScope' => isset($_GET['app'])
                ? (workspaceScopesForApp((string)$_GET['app'])[3] ?? '')
                : '',
        ]);
    }

    if ($action === 'disconnect' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $row = workspaceConnectionRow($userId);
        $revocationSucceeded = true;
        if ($row) {
            try {
                $tokens = workspaceDecryptTokens((string)$row['encrypted_tokens']);
                $revoke = (string)($tokens['refresh_token'] ?? $tokens['access_token'] ?? '');
                if ($revoke !== '') {
                    $revocation = workspaceHttp(
                        'https://oauth2.googleapis.com/revoke',
                        'POST',
                        ['Content-Type: application/x-www-form-urlencoded'],
                        http_build_query(['token' => $revoke], '', '&', PHP_QUERY_RFC3986)
                    );
                    $revocationSucceeded = $revocation['status'] >= 200 && $revocation['status'] < 300;
                }
            } catch (Throwable $error) {
                error_log('Virtual Phone Google disconnect token revocation failed: ' . $error->getMessage());
                $revocationSucceeded = false;
            }
            $stmt = workspaceDb()->prepare('DELETE FROM vphone_google_connections WHERE user_id = ?');
            $stmt->execute([$userId]);
            unset($_SESSION['vphone_google_photo_picker']);
        }
        workspaceJson([
            'success' => true,
            'message' => $revocationSucceeded
                ? 'Google account disconnected.'
                : 'Google was disconnected from this phone, but Google could not confirm token revocation. Review connected apps in your Google Account.',
            'revocationConfirmed' => $revocationSucceeded,
        ]);
    }

    $accessToken = workspaceAccessToken($userId);

    if ($action === 'drive_list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $query = [
            'q' => 'trashed = false',
            'pageSize' => '100',
            'orderBy' => 'folder,name_natural',
            'fields' => 'nextPageToken,files(id,name,mimeType,size,modifiedTime,webViewLink,thumbnailLink,iconLink,appProperties,capabilities(canEdit,canTrash))',
            'supportsAllDrives' => 'true',
            'includeItemsFromAllDrives' => 'true',
        ];
        $parentId = trim((string)($_GET['parent'] ?? ''));
        $search = trim((string)($_GET['search'] ?? ''));
        if ($parentId === '' && $search === '') {
            $query['q'] .= " and 'root' in parents";
        } elseif ($parentId !== '') {
            if (!preg_match('/^[A-Za-z0-9_-]{1,200}$/', $parentId)) {
                workspaceJson(['success' => false, 'message' => 'The selected Drive folder is invalid.'], 400);
            }
            $query['q'] .= " and '" . $parentId . "' in parents";
        }
        if ($search !== '') {
            $query['q'] .= " and name contains '" . str_replace(["\\", "'"], ["\\\\", "\\'"], mb_substr($search, 0, 100, 'UTF-8')) . "'";
        }
        $pageToken = (string)($_GET['pageToken'] ?? '');
        if ($pageToken !== '') {
            $query['pageToken'] = $pageToken;
        }
        $files = workspaceGoogleJson('https://www.googleapis.com/drive/v3/files?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986), $accessToken);
        foreach (($files['files'] ?? []) as &$file) {
            $file['canDelete'] = (bool)($file['capabilities']['canTrash'] ?? false);
            $file['canEdit'] = (bool)($file['capabilities']['canEdit'] ?? false);
        }
        unset($file);
        workspaceJson(['success' => true, 'files' => $files['files'] ?? [], 'nextPageToken' => $files['nextPageToken'] ?? null]);
    }

    if ($action === 'drive_download' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $fileId = (string)($_GET['id'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_-]{1,200}$/', $fileId)) {
            workspaceJson(['success' => false, 'message' => 'The selected Drive file is invalid.'], 400);
        }
        $meta = workspaceGoogleJson(
            'https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '?fields=id,name,mimeType,appProperties',
            $accessToken
        );
        $googleDocTypes = [
            'application/vnd.google-apps.document' => 'application/pdf',
            'application/vnd.google-apps.spreadsheet' => 'application/pdf',
            'application/vnd.google-apps.presentation' => 'application/pdf',
        ];
        $mime = (string)($meta['mimeType'] ?? 'application/octet-stream');
        $url = isset($googleDocTypes[$mime])
            ? 'https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '/export?mimeType=' . rawurlencode($googleDocTypes[$mime])
            : 'https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '?alt=media';
        $response = workspaceHttp($url, 'GET', ['Authorization: Bearer ' . $accessToken]);
        if ($response['status'] < 200 || $response['status'] >= 300) {
            workspaceJson(['success' => false, 'message' => 'Google Drive could not download this file.'], $response['status'] ?: 502);
        }
        $filename = preg_replace('/[\r\n"\\\\\/]+/', '_', (string)($meta['name'] ?? 'Google Drive file'));
        if (isset($googleDocTypes[$mime])) {
            $filename .= '.pdf';
            $mime = 'application/pdf';
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . strlen($response['body']));
        header('Content-Disposition: ' . (!empty($_GET['preview']) ? 'inline' : 'attachment') . '; filename="' . $filename . '"');
        header('Cache-Control: private, no-store');
        echo $response['body'];
        exit;
    }

    if ($action === 'drive_upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_FILES['file']) || !is_array($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            workspaceJson(['success' => false, 'message' => 'Choose a file that uploaded successfully.'], 400);
        }
        $upload = $_FILES['file'];
        if ((int)$upload['size'] < 1 || (int)$upload['size'] > 15 * 1024 * 1024 || !is_uploaded_file((string)$upload['tmp_name'])) {
            workspaceJson(['success' => false, 'message' => 'Drive uploads must be between 1 byte and 15 MB.'], 413);
        }
        $name = basename(str_replace('\\', '/', (string)$upload['name']));
        $name = trim(preg_replace('/[\x00-\x1F\x7F]/u', '_', $name));
        if ($name === '' || mb_strlen($name, 'UTF-8') > 200) {
            workspaceJson(['success' => false, 'message' => 'The file name is invalid or too long.'], 400);
        }
        $mime = mime_content_type((string)$upload['tmp_name']) ?: 'application/octet-stream';
        $boundary = 'vphone_' . bin2hex(random_bytes(16));
        $metadata = json_encode([
            'name' => $name,
            'appProperties' => ['vphoneOwner' => (string)$userId],
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $bytes = file_get_contents((string)$upload['tmp_name']);
        if (!is_string($bytes)) {
            workspaceJson(['success' => false, 'message' => 'The selected file could not be read.'], 400);
        }
        $body = '--' . $boundary . "\r\nContent-Type: application/json; charset=UTF-8\r\n\r\n" . $metadata .
            "\r\n--" . $boundary . "\r\nContent-Type: " . $mime . "\r\n\r\n" . $bytes . "\r\n--" . $boundary . "--\r\n";
        $response = workspaceHttp(
            'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name,mimeType,size,modifiedTime,webViewLink,appProperties',
            'POST',
            ['Authorization: Bearer ' . $accessToken, 'Content-Type: multipart/related; boundary=' . $boundary],
            $body
        );
        $file = json_decode($response['body'], true);
        if ($response['status'] < 200 || $response['status'] >= 300 || !is_array($file) || empty($file['id'])) {
            $message = is_array($file) ? (string)($file['error']['message'] ?? '') : '';
            workspaceJson(['success' => false, 'message' => $message !== '' ? 'Google Drive upload failed: ' . $message : 'Google Drive could not upload this file.'], $response['status'] ?: 502);
        }
        workspaceJson(['success' => true, 'file' => $file, 'message' => 'File uploaded to Google Drive.']);
    }

    if ($action === 'drive_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $payload = json_decode((string)file_get_contents('php://input'), true);
        $fileId = is_array($payload) ? (string)($payload['id'] ?? '') : '';
        if (!preg_match('/^[A-Za-z0-9_-]{1,200}$/', $fileId)) {
            workspaceJson(['success' => false, 'message' => 'The selected Drive file is invalid.'], 400);
        }
        $meta = workspaceGoogleJson('https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '?fields=id,capabilities(canTrash)', $accessToken);
        if (empty($meta['capabilities']['canTrash'])) {
            workspaceJson(['success' => false, 'message' => 'You do not have permission to move this Drive file to trash.'], 403);
        }
        workspaceGoogleJson('https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId), $accessToken, 'PATCH', ['trashed' => true]);
        workspaceJson(['success' => true, 'message' => 'Drive file moved to trash.']);
    }

    if ($action === 'drive_update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $fileId = (string)($_POST['id'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_-]{1,200}$/', $fileId)
            || !isset($_FILES['file']) || !is_array($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            workspaceJson(['success' => false, 'message' => 'Choose a valid Drive file and an uploaded replacement file.'], 400);
        }
        $upload = $_FILES['file'];
        if ((int)$upload['size'] < 1 || (int)$upload['size'] > 15 * 1024 * 1024 || !is_uploaded_file((string)$upload['tmp_name'])) {
            workspaceJson(['success' => false, 'message' => 'Drive updates must be between 1 byte and 15 MB.'], 413);
        }
        $meta = workspaceGoogleJson(
            'https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '?fields=id,name,mimeType,capabilities(canEdit)',
            $accessToken
        );
        if (empty($meta['capabilities']['canEdit'])) {
            workspaceJson(['success' => false, 'message' => 'You do not have permission to update this Drive file.'], 403);
        }
        if (str_starts_with((string)($meta['mimeType'] ?? ''), 'application/vnd.google-apps.')) {
            workspaceJson(['success' => false, 'message' => 'Google Docs, Sheets, and Slides cannot be replaced with a file upload here. Download/export a copy first.'], 415);
        }
        $bytes = file_get_contents((string)$upload['tmp_name']);
        if (!is_string($bytes)) {
            workspaceJson(['success' => false, 'message' => 'The replacement file could not be read.'], 400);
        }
        $mime = mime_content_type((string)$upload['tmp_name']) ?: 'application/octet-stream';
        $response = workspaceHttp(
            'https://www.googleapis.com/upload/drive/v3/files/' . rawurlencode($fileId) . '?uploadType=media&fields=id,name,mimeType,size,modifiedTime',
            'PATCH',
            ['Authorization: Bearer ' . $accessToken, 'Content-Type: ' . $mime],
            $bytes
        );
        $file = json_decode($response['body'], true);
        if ($response['status'] < 200 || $response['status'] >= 300 || !is_array($file) || empty($file['id'])) {
            $message = is_array($file) ? (string)($file['error']['message'] ?? '') : '';
            workspaceJson(['success' => false, 'message' => $message !== '' ? 'Google Drive update failed: ' . $message : 'Google Drive could not update this file.'], $response['status'] ?: 502);
        }
        workspaceJson(['success' => true, 'file' => $file, 'message' => 'Drive file updated.']);
    }

    if ($action === 'contacts_list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $people = workspacePeopleList($accessToken);
        $contacts = [];
        foreach ($people as $person) {
            $contacts[] = [
                'resourceName' => (string)$person['resourceName'],
                'etag' => (string)($person['etag'] ?? ''),
                'name' => workspacePersonName($person),
                'number' => workspaceContactNumber($person),
                'email' => (string)($person['emailAddresses'][0]['value'] ?? ''),
                'photo' => (string)($person['photos'][0]['url'] ?? ''),
            ];
        }
        workspaceJson(['success' => true, 'contacts' => $contacts]);
    }

    if ($action === 'local_contacts' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = workspaceDb()->prepare('SELECT id, name, number FROM contacts WHERE user_id = ? ORDER BY name ASC, id ASC');
        $stmt->execute([$userId]);
        workspaceJson(['success' => true, 'contacts' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($action === 'contacts_import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $payload = json_decode((string)file_get_contents('php://input'), true);
        $selectedResources = is_array($payload) ? ($payload['resources'] ?? []) : [];
        if (!is_array($selectedResources) || !$selectedResources || count($selectedResources) > 2000) {
            workspaceJson(['success' => false, 'message' => 'Select no more than 2,000 contacts at once.'], 400);
        }
        $selectedResources = array_fill_keys(array_filter($selectedResources, 'is_string'), true);
        if (!$selectedResources) {
            workspaceJson(['success' => false, 'message' => 'The selected Google contacts are invalid.'], 400);
        }
        $people = workspacePeopleList($accessToken);
        $db = workspaceDb();
        $db->beginTransaction();
        try {
            $findMap = $db->prepare('SELECT contact_id FROM vphone_google_contact_map WHERE user_id = ? AND resource_name = ?');
            $updateContact = $db->prepare('UPDATE contacts SET name = ?, number = ?, photo = ? WHERE id = ? AND user_id = ?');
            $insertContact = $db->prepare('INSERT INTO contacts (user_id, name, number, photo) VALUES (?, ?, ?, ?)');
            $saveMap = $db->prepare('INSERT INTO vphone_google_contact_map (user_id, resource_name, contact_id, etag)
                VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE contact_id = VALUES(contact_id), etag = VALUES(etag)');
            $imported = 0;
            foreach ($people as $person) {
                $resource = (string)$person['resourceName'];
                if ($selectedResources && !isset($selectedResources[$resource])) {
                    continue;
                }
                $number = workspaceContactNumber($person);
                if ($number === '') {
                    continue;
                }
                $name = workspacePersonName($person);
                $photo = mb_substr((string)($person['photos'][0]['url'] ?? ''), 0, 255, 'UTF-8');
                $findMap->execute([$userId, $resource]);
                $contactId = $findMap->fetchColumn();
                if ($contactId) {
                    $updateContact->execute([$name, $number, $photo, (int)$contactId, $userId]);
                    $contactId = (int)$contactId;
                } else {
                    $insertContact->execute([$userId, $name, $number, $photo]);
                    $contactId = (int)$db->lastInsertId();
                }
                $saveMap->execute([$userId, $resource, $contactId, (string)($person['etag'] ?? '')]);
                $imported++;
            }
            $db->commit();
            workspaceJson(['success' => true, 'imported' => $imported, 'message' => $imported . ' contact(s) saved to your phone.']);
        } catch (Throwable $error) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $error;
        }
    }

    if ($action === 'contacts_export' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $payload = json_decode((string)file_get_contents('php://input'), true);
        $ids = is_array($payload) && is_array($payload['contact_ids'] ?? null) ? $payload['contact_ids'] : [];
        if (!$ids || count($ids) > 50) {
            workspaceJson(['success' => false, 'message' => 'Select between 1 and 50 phone contacts to sync.'], 400);
        }
        $db = workspaceDb();
        $findContact = $db->prepare('SELECT id, name, number FROM contacts WHERE id = ? AND user_id = ?');
        $findMap = $db->prepare('SELECT resource_name FROM vphone_google_contact_map WHERE user_id = ? AND contact_id = ?');
        $saveMap = $db->prepare('INSERT INTO vphone_google_contact_map (user_id, resource_name, contact_id, etag)
            VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE contact_id = VALUES(contact_id), etag = VALUES(etag)');
        $synced = 0;
        foreach ($ids as $rawId) {
            $contactId = filter_var($rawId, FILTER_VALIDATE_INT);
            if (!$contactId || $contactId < 1) {
                continue;
            }
            $findContact->execute([$contactId, $userId]);
            $contact = $findContact->fetch(PDO::FETCH_ASSOC);
            if (!$contact) {
                continue;
            }
            $findMap->execute([$userId, $contactId]);
            $resourceName = $findMap->fetchColumn();
            $person = ['names' => [['givenName' => (string)$contact['name']]]];
            if (filter_var((string)$contact['number'], FILTER_VALIDATE_EMAIL)) {
                $person['emailAddresses'] = [['value' => (string)$contact['number']]];
            } else {
                $person['phoneNumbers'] = [['value' => (string)$contact['number']]];
            }
            if ($resourceName) {
                if (!preg_match('#^people/[A-Za-z0-9_-]+$#', (string)$resourceName)) {
                    throw new RuntimeException('A saved Google contact link is invalid. Refresh Google Contacts and retry.');
                }
                $current = workspaceGoogleJson(
                    'https://people.googleapis.com/v1/' . (string)$resourceName . '?personFields=metadata',
                    $accessToken
                );
                $person['etag'] = (string)($current['etag'] ?? '');
                $updated = workspaceGoogleJson(
                    'https://people.googleapis.com/v1/' . (string)$resourceName . ':updateContact?updatePersonFields=' . (isset($person['emailAddresses']) ? 'names,emailAddresses' : 'names,phoneNumbers'),
                    $accessToken,
                    'PATCH',
                    $person
                );
            } else {
                $updated = workspaceGoogleJson('https://people.googleapis.com/v1/people:createContact', $accessToken, 'POST', $person);
            }
            if (!empty($updated['resourceName'])) {
                $saveMap->execute([$userId, (string)$updated['resourceName'], $contactId, (string)($updated['etag'] ?? '')]);
                $synced++;
            }
        }
        workspaceJson(['success' => true, 'synced' => $synced, 'message' => $synced . ' contact(s) synced to Google Contacts.']);
    }

    if ($action === 'photo_picker_create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $session = workspaceCreatePhotoPickerSession($userId, $accessToken);
        workspaceJson(['success' => true, 'session' => $session]);
    }

    if ($action === 'photo_picker_status' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $picker = $_SESSION['vphone_google_photo_picker'] ?? null;
        if (!is_array($picker) || (int)($picker['user_id'] ?? 0) !== $userId || empty($picker['id'])) {
            workspaceJson(['success' => false, 'message' => 'Start a Google Photos selection first.'], 404);
        }
        $session = workspaceGoogleJson('https://photospicker.googleapis.com/v1/sessions/' . rawurlencode((string)$picker['id']), $accessToken);
        if (!empty($session['mediaItemsSet'])) {
            $items = workspaceGoogleJson(
                'https://photospicker.googleapis.com/v1/mediaItems?pageSize=100&sessionId=' . rawurlencode((string)$picker['id']),
                $accessToken
            );
            $session['mediaItems'] = $items['mediaItems'] ?? [];
        }
        workspaceJson(['success' => true, 'session' => $session]);
    }

    if ($action === 'photo_thumbnail' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $mediaId = (string)($_GET['id'] ?? '');
        $picker = $_SESSION['vphone_google_photo_picker'] ?? null;
        if (!is_array($picker) || (int)($picker['user_id'] ?? 0) !== $userId || empty($picker['id']) || $mediaId === '') {
            workspaceJson(['success' => false, 'message' => 'Start a Google Photos selection first.'], 400);
        }
        $items = workspaceGoogleJson(
            'https://photospicker.googleapis.com/v1/mediaItems?pageSize=100&sessionId=' . rawurlencode((string)$picker['id']),
            $accessToken
        );
        $media = null;
        foreach (($items['mediaItems'] ?? []) as $item) {
            if (($item['id'] ?? '') === $mediaId) {
                $media = $item['mediaFile'] ?? null;
                break;
            }
        }
        if (!is_array($media) || empty($media['baseUrl']) || !str_starts_with((string)($media['mimeType'] ?? ''), 'image/')) {
            workspaceJson(['success' => false, 'message' => 'The selected photo preview is unavailable.'], 404);
        }
        $thumbnail = workspaceHttp((string)$media['baseUrl'] . '=w320-h320', 'GET', ['Authorization: Bearer ' . $accessToken]);
        if ($thumbnail['status'] < 200 || $thumbnail['status'] >= 300) {
            workspaceJson(['success' => false, 'message' => 'Google Photos could not load this preview.'], 502);
        }
        header('Content-Type: ' . (string)$media['mimeType']);
        header('Cache-Control: private, no-store');
        echo $thumbnail['body'];
        exit;
    }

    if ($action === 'photo_import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once __DIR__ . '/../storage/api/common.php';
        $payload = json_decode((string)file_get_contents('php://input'), true);
        $mediaId = is_array($payload) ? (string)($payload['media_id'] ?? '') : '';
        $picker = $_SESSION['vphone_google_photo_picker'] ?? null;
        if (!is_array($picker) || (int)($picker['user_id'] ?? 0) !== $userId || empty($picker['id']) || $mediaId === '') {
            workspaceJson(['success' => false, 'message' => 'Choose a photo in Google Photos before importing it.'], 400);
        }
        $items = workspaceGoogleJson(
            'https://photospicker.googleapis.com/v1/mediaItems?pageSize=100&sessionId=' . rawurlencode((string)$picker['id']),
            $accessToken
        );
        $selectedItem = null;
        foreach (($items['mediaItems'] ?? []) as $item) {
            if (($item['id'] ?? '') === $mediaId) {
                $selectedItem = $item;
                break;
            }
        }
        if (!$selectedItem || empty($selectedItem['mediaFile']['baseUrl'])) {
            workspaceJson(['success' => false, 'message' => 'That photo is no longer available from the active picker. Select it again.'], 404);
        }
        $media = $selectedItem['mediaFile'] ?? [];
        $mime = (string)($media['mimeType'] ?? 'image/jpeg');
        if (!str_starts_with($mime, 'image/')) {
            workspaceJson(['success' => false, 'message' => 'Only photos can be imported from this picker right now.'], 415);
        }
        $downloadUrl = (string)$media['baseUrl'] . '=d';
        $download = workspaceHttp($downloadUrl, 'GET', ['Authorization: Bearer ' . $accessToken]);
        if ($download['status'] < 200 || $download['status'] >= 300 || $download['body'] === '') {
            workspaceJson(['success' => false, 'message' => 'Google Photos could not download the selected image.'], 502);
        }
        if (strlen($download['body']) > 20 * 1024 * 1024) {
            workspaceJson(['success' => false, 'message' => 'The selected photo is larger than the 20 MB import limit.'], 413);
        }
        $extension = match (strtolower($mime)) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/heic' => 'heic',
            'image/heif' => 'heif',
            'image/avif' => 'avif',
            'image/tiff' => 'tiff',
            'image/bmp' => 'bmp',
            default => '',
        };
        if ($extension === '') {
            workspaceJson(['success' => false, 'message' => 'This Google Photos image format is not supported for File Manager import.'], 415);
        }
        $userDir = storageResolveUserDir((string)$userId);
        $used = storageGetUsageBytes($userDir);
        $quotaBytes = storageGetQuotaBytes($userId);
        if (strlen($download['body']) > $quotaBytes - $used) {
            workspaceJson(['success' => false, 'message' => 'This photo exceeds your remaining Virtual Phone storage.'], 413);
        }
        $tempPath = tempnam(sys_get_temp_dir(), 'vphone_google_photo_');
        if ($tempPath === false || file_put_contents($tempPath, $download['body']) === false) {
            workspaceJson(['success' => false, 'message' => 'The selected photo could not be saved to temporary storage.'], 500);
        }
        $saveError = '';
        try {
            $candidateName = basename(str_replace('\\', '/', (string)($media['filename'] ?? '')));
            $baseName = trim((string)pathinfo($candidateName, PATHINFO_FILENAME));
            $baseName = $baseName !== '' ? $baseName : 'google_photos_' . date('Ymd_His');
            $originalName = $baseName . '.' . $extension;
            $storedName = storageSanitizeFileName($originalName);
            $target = $userDir . $storedName;
            if (!storageIsAllowedFile($originalName, $tempPath)) {
                $saveError = 'The selected image format is not allowed in File Manager.';
            } elseif (!copy($tempPath, $target)) {
                if (is_file($target)) {
                    unlink($target);
                }
                $saveError = 'The photo could not be written to your File Manager.';
            } else {
                storageCreateFileEntry($userDir, $storedName, $originalName, $tempPath);
            }
        } finally {
            if (is_file($tempPath)) {
                unlink($tempPath);
            }
        }
        if ($saveError !== '') {
            workspaceJson(['success' => false, 'message' => $saveError], $saveError === 'The photo could not be written to your File Manager.' ? 500 : 415);
        }
        workspaceJson(['success' => true, 'name' => $originalName, 'message' => 'Photo saved to your Virtual Phone File Manager.']);
    }

    header('Allow: GET, POST');
    workspaceJson(['success' => false, 'message' => 'Unknown Google app request.'], 400);
} catch (Throwable $error) {
    error_log('Virtual Phone Google app request failed: ' . $error->getMessage());
    if (in_array($action ?? '', ['connect', 'callback'], true) && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $safeMessage = htmlspecialchars($error->getMessage() ?: 'Google could not complete this request.', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $messageForScript = json_encode(
            $error->getMessage() ?: 'Google could not complete this request.',
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
        echo '<!doctype html><meta charset="utf-8"><title>Google connection</title><p>' . $safeMessage . '</p><p>Return to your Virtual Phone. This window will close shortly.</p><script>if(window.opener){window.opener.postMessage({type:"vphone-google-error",message:' . $messageForScript . '},window.location.origin)}setTimeout(()=>window.close(),2500)</script>';
        exit;
    }
    workspaceJson(['success' => false, 'message' => $error->getMessage() ?: 'Google could not complete this request.'], 500);
}
