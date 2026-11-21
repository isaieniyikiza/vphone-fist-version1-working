<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

admin_require_login();
admin_ensure_schema();

function admin_user_storage_value(array $user): int
{
    return admin_estimate_user_storage_mb($user);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $db = admin_db();

    $stats = [
        'total_users' => (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        'active_users' => (int)$db->query("SELECT COUNT(*) FROM users WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)")->fetchColumn(),
        'blocked_users' => (int)$db->query('SELECT COUNT(*) FROM users WHERE is_blocked = 1')->fetchColumn(),
        'total_apps' => (int)$db->query('SELECT COUNT(*) FROM vphone_apps')->fetchColumn(),
        'total_installs' => (int)$db->query('SELECT COUNT(*) FROM vphone_app_installs')->fetchColumn(),
    ];

    $storageBytes = 0;
    foreach ([__DIR__ . '/../../uploads', __DIR__ . '/../../user/storage/uploads'] as $storageRoot) {
        if (is_dir($storageRoot)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($storageRoot, FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $storageBytes += (int)$fileInfo->getSize();
                }
            }
        }
    }
    $stats['storage_used_mb'] = (int)round($storageBytes / 1024 / 1024);

    $userRows = $db->query("SELECT u.id, u.username, u.profile_image, u.created_at, u.last_seen, u.is_blocked,
        (SELECT COUNT(*) FROM vphone_app_installs WHERE user_id = u.id) AS installed_count,
        COALESCE(u.storage_used_mb, 0) AS storage_used_mb
        FROM users u
        ORDER BY u.last_seen DESC, u.created_at DESC
        LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);

    $users = [];
    foreach ($userRows as $user) {
        $estimatedStorage = admin_user_storage_value($user);
        $user['status'] = admin_user_status($user['last_seen'] ?? null);
        $user['storage_used_mb'] = (int)$estimatedStorage;
        $users[] = $user;
    }

    $appRows = $db->query("SELECT a.id, a.name, a.slug, COUNT(i.user_id) AS installs
        FROM vphone_apps a
        LEFT JOIN vphone_app_installs i ON i.app_id = a.id
        GROUP BY a.id, a.name, a.slug
        ORDER BY installs DESC, a.name ASC
        LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);

    $activity = [];
    foreach ($users as $user) {
        $activity[] = [
            'user' => $user['username'],
            'status' => $user['status'],
            'message' => $user['status'] === 'active' ? 'Recently active on the device.' : 'Seen ' . ($user['last_seen'] ?: $user['created_at']),
            'time' => $user['last_seen'] ?: $user['created_at'],
        ];
    }

    admin_json([
        'success' => true,
        'admin' => ['id' => (int)($_SESSION['vphone_root_admin_id'] ?? 0), 'username' => (string)($_SESSION['vphone_root_admin_username'] ?? 'Admin')],
        'stats' => $stats,
        'users' => $users,
        'apps' => $appRows,
        'activity' => array_slice($activity, 0, 8),
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PATCH' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = json_decode((string)file_get_contents('php://input'), true);
    $payload = is_array($payload) ? $payload : [];
    $action = strtolower((string)($payload['action'] ?? ''));
    $db = admin_db();

    if ($action === 'toggle_user') {
        $userId = filter_var($payload['user_id'] ?? null, FILTER_VALIDATE_INT);
        $blocked = isset($payload['is_blocked']) ? (int)$payload['is_blocked'] : 0;

        if (!$userId || $userId < 1) {
            admin_json(['success' => false, 'message' => 'Select a valid user id.'], 400);
        }

        $stmt = $db->prepare('UPDATE users SET is_blocked = ? WHERE id = ?');
        $stmt->execute([$blocked, $userId]);

        admin_json(['success' => true, 'message' => $blocked ? 'User blocked.' : 'User unblocked.']);
    }

    if ($action === 'notify') {
        $message = trim((string)($payload['message'] ?? ''));
        if ($message === '') {
            admin_json(['success' => false, 'message' => 'Write a message before sending the alert.'], 400);
        }

        $userIds = $db->query('SELECT id FROM users')->fetchAll(PDO::FETCH_COLUMN);
        $file = __DIR__ . '/../../user/api/notifications.csv';
        if (!file_exists($file)) {
            file_put_contents($file, "id,time,user_id,sender_id,message\n");
        }

        $sent = 0;
        $adminId = (int)($_SESSION['vphone_root_admin_id'] ?? 0);
        $time = date('Y-m-d H:i:s');
        $handle = fopen($file, 'a');
        foreach ($userIds as $userId) {
            $line = [time() . '_' . $sent, $time, (string)$userId, (string)$adminId, str_replace(',', ' ', $message)];
            fputcsv($handle, $line);
            $sent++;
        }
        fclose($handle);

        admin_json(['success' => true, 'message' => 'Notification sent to all ' . $sent . ' users.']);
    }

    admin_json(['success' => false, 'message' => 'Unknown dashboard action.'], 400);
}

admin_json(['success' => false, 'message' => 'Method not allowed.'], 405);
