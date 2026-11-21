<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../db.php';

function admin_json(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function admin_db(): PDO
{
    global $pdo;
    if (!$pdo instanceof PDO) {
        admin_json(['success' => false, 'message' => 'The application database is unavailable.'], 500);
    }
    return $pdo;
}

function admin_ensure_schema(): void
{
    $db = admin_db();
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS vphone_admins (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(80) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_blocked TINYINT(1) NOT NULL DEFAULT 0");
        $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS storage_used_mb INT UNSIGNED NOT NULL DEFAULT 0");
        $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_seen TIMESTAMP NULL DEFAULT NULL");
        $db->exec("CREATE TABLE IF NOT EXISTS vphone_user_activity (
            user_id INT NOT NULL PRIMARY KEY,
            app_name VARCHAR(120) NOT NULL DEFAULT 'Phone Home',
            app_path VARCHAR(255) NOT NULL DEFAULT '',
            app_icon VARCHAR(255) NOT NULL DEFAULT '',
            app_started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_ping DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_vphone_activity_ping (last_ping),
            INDEX idx_vphone_activity_app (app_path)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $error) {
        error_log('Virtual Phone admin schema could not be initialized: ' . $error->getMessage());
        admin_json(['success' => false, 'message' => 'The admin system could not initialize. Check the database connection.'], 500);
    }
}

function admin_require_login(): void
{
    if (empty($_SESSION['vphone_root_admin_id'])) {
        admin_json(['success' => false, 'message' => 'Sign in to the admin dashboard first.'], 401);
    }
}

function admin_current_user(): ?array
{
    $adminId = filter_var($_SESSION['vphone_root_admin_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$adminId || $adminId < 1) {
        return null;
    }

    $db = admin_db();
    $stmt = $db->prepare('SELECT id, username FROM vphone_admins WHERE id = ? LIMIT 1');
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    return $admin ? ['id' => (int)$admin['id'], 'username' => (string)$admin['username']] : null;
}

function admin_user_status(?string $lastSeen): string
{
    if (empty($lastSeen)) {
        return 'offline';
    }

    try {
        $lastSeenTime = new DateTimeImmutable($lastSeen);
        $diffMinutes = (time() - $lastSeenTime->getTimestamp()) / 60;
        if ($diffMinutes <= 15) {
            return 'active';
        }
        if ($diffMinutes <= 60) {
            return 'idle';
        }
        return 'offline';
    } catch (Throwable) {
        return 'offline';
    }
}

function admin_estimate_user_storage_mb(array $user): int
{
    $userId = (int)($user['id'] ?? 0);
    $sizeBytes = 0;
    $userStorage = dirname(__DIR__, 2) . '/user/storage/uploads/' . $userId;

    if ($userId > 0 && is_dir($userStorage)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($userStorage, FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $sizeBytes += (int)$fileInfo->getSize();
            }
        }
    }

    $profilePath = $user['profile_image'] ?? '';

    if (!empty($profilePath)) {
        $normalized = $profilePath;
        if (!preg_match('#^https?://#', $normalized) && !str_starts_with($normalized, '/')) {
            $normalized = '/virtual-phone/uploads/profile/' . $normalized;
        }

        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $normalized;
        if (file_exists($fullPath) && is_file($fullPath)) {
            $sizeBytes += (int)filesize($fullPath);
        }
    }

    return (int)ceil($sizeBytes / 1024 / 1024);
}
