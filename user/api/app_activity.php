<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/../../db.php';

function activity_json(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function activity_record_elapsed(PDO $pdo, int $userId, array $activity): void
{
    $elapsed = (int)($activity['elapsed_seconds'] ?? 0);
    if ($elapsed < 1 || $elapsed > 35) {
        return;
    }

    $appName = (string)($activity['app_name'] ?? 'Phone Home');
    $appPath = (string)($activity['app_path'] ?? '');
    $appKey = hash('sha256', $appPath !== '' ? $appPath : $appName);
    $save = $pdo->prepare("INSERT INTO vphone_user_activity_daily
        (user_id, activity_date, app_key, app_name, app_path, seconds)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE seconds = LEAST(86400, seconds + VALUES(seconds)),
            app_name = VALUES(app_name), app_path = VALUES(app_path)");
    $save->execute([
        $userId,
        (string)$activity['activity_date'],
        $appKey,
        $appName,
        $appPath,
        $elapsed,
    ]);
}

$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if (!$userId || $userId < 1) {
    activity_json(['success' => false, 'message' => 'Sign in to your Virtual Phone first.'], 401);
}

$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? ''));
if (!in_array($method, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    activity_json(['success' => false, 'message' => 'Method not allowed.'], 405);
}

if (!$pdo instanceof PDO) {
    activity_json(['success' => false, 'message' => 'The application database is unavailable.'], 500);
}

$payload = [];
if ($method === 'POST') {
    try {
        $payload = json_decode((string)file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        activity_json(['success' => false, 'message' => 'Invalid activity payload.'], 400);
    }
    if (!is_array($payload)) {
        activity_json(['success' => false, 'message' => 'Invalid activity payload.'], 400);
    }
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS vphone_user_activity (
        user_id INT NOT NULL PRIMARY KEY,
        app_name VARCHAR(120) NOT NULL DEFAULT 'Phone Home',
        app_path VARCHAR(255) NOT NULL DEFAULT '',
        app_icon VARCHAR(255) NOT NULL DEFAULT '',
        app_started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        last_ping DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_vphone_activity_ping (last_ping),
        INDEX idx_vphone_activity_app (app_path)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS vphone_user_activity_daily (
        user_id INT NOT NULL,
        activity_date DATE NOT NULL,
        app_key CHAR(64) NOT NULL,
        app_name VARCHAR(120) NOT NULL,
        app_path VARCHAR(255) NOT NULL DEFAULT '',
        seconds INT UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (user_id, activity_date, app_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $userQuery = $pdo->prepare('SELECT is_blocked FROM users WHERE id = ? LIMIT 1');
    $userQuery->execute([$userId]);
    $user = $userQuery->fetch(PDO::FETCH_ASSOC);
    if (!$user || (int)$user['is_blocked'] === 1) {
        $_SESSION = [];
        session_destroy();
        activity_json(['success' => false, 'message' => 'This account is unavailable.'], 403);
    }

    $requestedAction = $method === 'GET' ? ($_GET['action'] ?? '') : ($payload['action'] ?? 'heartbeat');
    $action = is_string($requestedAction) ? strtolower($requestedAction) : '';
    if ($method === 'GET') {
        if ($action !== 'summary') {
            activity_json(['success' => false, 'message' => 'Unknown activity action.'], 400);
        }

        $todayQuery = $pdo->prepare('SELECT COALESCE(SUM(seconds), 0) FROM vphone_user_activity_daily WHERE user_id = ? AND activity_date = CURDATE()');
        $todayQuery->execute([$userId]);
        $weekQuery = $pdo->prepare('SELECT COALESCE(SUM(seconds), 0) FROM vphone_user_activity_daily WHERE user_id = ? AND activity_date >= CURDATE() - INTERVAL 6 DAY');
        $weekQuery->execute([$userId]);
        $dailyQuery = $pdo->prepare("SELECT DATE_FORMAT(activity_date, '%Y-%m-%d') AS activity_date, SUM(seconds) AS seconds
            FROM vphone_user_activity_daily
            WHERE user_id = ? AND activity_date >= CURDATE() - INTERVAL 6 DAY
            GROUP BY activity_date ORDER BY activity_date DESC");
        $dailyQuery->execute([$userId]);
        $appsQuery = $pdo->prepare('SELECT app_name, app_path, SUM(seconds) AS seconds
            FROM vphone_user_activity_daily WHERE user_id = ? AND activity_date = CURDATE()
            GROUP BY app_key, app_name, app_path ORDER BY seconds DESC LIMIT 10');
        $appsQuery->execute([$userId]);
        activity_json([
            'success' => true,
            'today_seconds' => (int)$todayQuery->fetchColumn(),
            'week_seconds' => (int)$weekQuery->fetchColumn(),
            'daily' => $dailyQuery->fetchAll(PDO::FETCH_ASSOC),
            'top_apps' => $appsQuery->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    if ($action === 'stop') {
        $pdo->beginTransaction();
        $previousQuery = $pdo->prepare('SELECT app_name, app_path, DATE(last_ping) AS activity_date,
                TIMESTAMPDIFF(SECOND, last_ping, NOW()) AS elapsed_seconds
            FROM vphone_user_activity WHERE user_id = ? FOR UPDATE');
        $previousQuery->execute([$userId]);
        $previous = $previousQuery->fetch(PDO::FETCH_ASSOC);
        if (is_array($previous)) {
            activity_record_elapsed($pdo, (int)$userId, $previous);
        }
        $pdo->prepare('DELETE FROM vphone_user_activity WHERE user_id = ?')->execute([$userId]);
        $pdo->commit();
        activity_json(['success' => true]);
    }

    if ($method !== 'POST' || $action !== 'heartbeat') {
        activity_json(['success' => false, 'message' => 'Unknown activity action.'], 400);
    }

    $appNameValue = $payload['app_name'] ?? 'Phone Home';
    $appPathValue = $payload['app_path'] ?? '';
    $appIconValue = $payload['app_icon'] ?? '';
    $appName = is_string($appNameValue) ? trim($appNameValue) : '';
    $appPath = is_string($appPathValue) ? trim($appPathValue) : '';
    $appIcon = is_string($appIconValue) ? trim($appIconValue) : '';
    if ($appName === '' || strlen($appName) > 120 || strlen($appPath) > 255 || strlen($appIcon) > 255 ||
        ($appPath !== '' && (!str_starts_with($appPath, '/virtual-phone/user/') || str_contains($appPath, '..'))) ||
        ($appIcon !== '' && (!str_starts_with($appIcon, '/virtual-phone/') || str_contains($appIcon, '..')))) {
        activity_json(['success' => false, 'message' => 'Invalid app activity details.'], 400);
    }

    $pdo->beginTransaction();
    $previousQuery = $pdo->prepare('SELECT app_name, app_path, DATE(last_ping) AS activity_date,
            TIMESTAMPDIFF(SECOND, last_ping, NOW()) AS elapsed_seconds
        FROM vphone_user_activity WHERE user_id = ? FOR UPDATE');
    $previousQuery->execute([$userId]);
    $previous = $previousQuery->fetch(PDO::FETCH_ASSOC);
    if (is_array($previous)) {
        activity_record_elapsed($pdo, (int)$userId, $previous);
    }

    $save = $pdo->prepare("INSERT INTO vphone_user_activity
        (user_id, app_name, app_path, app_icon, app_started_at, last_ping)
        VALUES (?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            app_started_at = IF(app_path <> VALUES(app_path), NOW(), app_started_at),
            app_name = VALUES(app_name),
            app_path = VALUES(app_path),
            app_icon = VALUES(app_icon),
            last_ping = NOW()");
    $save->execute([$userId, $appName, $appPath, $appIcon]);
    $pdo->prepare('UPDATE users SET last_seen = NOW() WHERE id = ?')->execute([$userId]);
    $pdo->commit();

    activity_json(['success' => true]);
} catch (Throwable $error) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Virtual Phone activity update failed: ' . $error->getMessage());
    activity_json(['success' => false, 'message' => 'Could not update phone activity.'], 500);
}
