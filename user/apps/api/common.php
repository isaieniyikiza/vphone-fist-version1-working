<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../db.php';

function apps_json(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function apps_db(): PDO
{
    global $pdo;
    if (!$pdo instanceof PDO) {
        apps_json(['success' => false, 'message' => 'The application database is unavailable.'], 500);
    }
    return $pdo;
}

function apps_ensure_tables(): void
{
    $db = apps_db();
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS vphone_admins (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(80) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS vphone_apps (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                slug VARCHAR(80) NOT NULL UNIQUE,
                name VARCHAR(100) NOT NULL,
                description VARCHAR(500) NOT NULL DEFAULT '',
                icon_path VARCHAR(255) NOT NULL,
                entry_file VARCHAR(255) NOT NULL,
                is_published TINYINT(1) NOT NULL DEFAULT 1,
                created_by INT UNSIGNED NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_apps_published (is_published)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS vphone_app_installs (
                user_id INT UNSIGNED NOT NULL,
                app_id INT UNSIGNED NOT NULL,
                installed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, app_id),
                INDEX idx_app_installs_app (app_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    } catch (Throwable $error) {
        error_log('Virtual Phone app tables could not be initialized: ' . $error->getMessage());
        apps_json(['success' => false, 'message' => 'The app catalog could not be initialized. Check the database connection.'], 500);
    }
}

function apps_require_user(): int
{
    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$userId || $userId < 1) {
        apps_json(['success' => false, 'message' => 'Sign in to your Virtual Phone first.'], 401);
    }
    return $userId;
}

function apps_require_admin(): int
{
    $adminId = filter_var($_SESSION['vphone_root_admin_id'] ?? $_SESSION['vphone_admin_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$adminId || $adminId < 1) {
        apps_json(['success' => false, 'message' => 'Sign in to the Admin dashboard first.'], 401);
    }
    return $adminId;
}

function apps_request_method(string $method): void
{
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== strtoupper($method)) {
        header('Allow: ' . strtoupper($method));
        apps_json(['success' => false, 'message' => 'Method not allowed.'], 405);
    }
}

function apps_size_bytes(string $value): int
{
    if (!preg_match('/^\s*(\d+)\s*([KMG])?B?\s*$/i', $value, $match)) {
        return 0;
    }

    $multiplier = match (strtoupper($match[2] ?? '')) {
        'G' => 1024 * 1024 * 1024,
        'M' => 1024 * 1024,
        'K' => 1024,
        default => 1,
    };
    return (int)$match[1] * $multiplier;
}
