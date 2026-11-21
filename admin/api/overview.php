<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

admin_require_login();
admin_ensure_schema();

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    admin_json(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$db = admin_db();
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(500, max(1, (int)($_GET['limit'] ?? 100)));
$includeStorage = !isset($_GET['storage']) || $_GET['storage'] !== '0';
$offset = ($page - 1) * $limit;
$totalUsers = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();

$stats = [
    'total_users' => $totalUsers,
    'active_users' => (int)$db->query("SELECT COUNT(*) FROM vphone_user_activity WHERE last_ping >= DATE_SUB(NOW(), INTERVAL 75 SECOND)")->fetchColumn(),
    'blocked_users' => (int)$db->query('SELECT COUNT(*) FROM users WHERE is_blocked = 1')->fetchColumn(),
    'total_apps' => (int)$db->query('SELECT COUNT(*) FROM vphone_apps')->fetchColumn(),
    'published_apps' => (int)$db->query('SELECT COUNT(*) FROM vphone_apps WHERE is_published = 1')->fetchColumn(),
    'total_installs' => (int)$db->query('SELECT COUNT(*) FROM vphone_app_installs')->fetchColumn(),
    'active_phone_users' => (int)$db->query("SELECT COUNT(*) FROM vphone_user_activity WHERE last_ping >= DATE_SUB(NOW(), INTERVAL 75 SECOND)")->fetchColumn(),
    'running_users' => (int)$db->query("SELECT COUNT(*) FROM vphone_user_activity WHERE app_path <> '' AND last_ping >= DATE_SUB(NOW(), INTERVAL 75 SECOND)")->fetchColumn(),
];

$stats['storage_used_mb'] = null;
if ($includeStorage) {
    $storageBytes = 0;
    foreach ([__DIR__ . '/../../uploads', __DIR__ . '/../../user/storage/uploads'] as $storageRoot) {
        if (!is_dir($storageRoot)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($storageRoot, FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $storageBytes += (int)$fileInfo->getSize();
            }
        }
    }
    $stats['storage_used_mb'] = (int)round($storageBytes / 1024 / 1024);
}

$userQuery = $db->prepare("SELECT u.id, u.username, u.profile_image, u.created_at, u.last_seen, u.is_blocked,
        a.app_name AS current_app_name, a.app_path AS current_app_path, a.app_icon AS current_app_icon,
        a.app_started_at AS current_app_started_at, a.last_ping AS current_app_ping,
        (SELECT COUNT(*) FROM vphone_app_installs WHERE user_id = u.id) AS installed_count
    FROM users u
    LEFT JOIN vphone_user_activity a ON a.user_id = u.id AND a.last_ping >= DATE_SUB(NOW(), INTERVAL 75 SECOND)
    ORDER BY u.last_seen DESC, u.created_at DESC
    LIMIT :limit OFFSET :offset");
$userQuery->bindValue(':limit', $limit, PDO::PARAM_INT);
$userQuery->bindValue(':offset', $offset, PDO::PARAM_INT);
$userQuery->execute();
$users = $userQuery->fetchAll(PDO::FETCH_ASSOC);

$userIds = array_map(static fn(array $user): int => (int)$user['id'], $users);
$userApps = [];
if ($userIds) {
    $placeholders = implode(',', array_fill(0, count($userIds), '?'));
    $installQuery = $db->prepare("SELECT i.user_id, i.app_id, a.name, a.slug, i.installed_at
        FROM vphone_app_installs i
        INNER JOIN vphone_apps a ON a.id = i.app_id
        WHERE i.user_id IN ($placeholders)
        ORDER BY i.installed_at DESC");
    $installQuery->execute($userIds);
    foreach ($installQuery->fetchAll(PDO::FETCH_ASSOC) as $install) {
        $userApps[(int)$install['user_id']][] = [
            'id' => (int)$install['app_id'],
            'name' => (string)$install['name'],
            'slug' => (string)$install['slug'],
            'installed_at' => (string)$install['installed_at'],
        ];
    }
}

$activity = [];
foreach ($users as &$user) {
    $userId = (int)$user['id'];
    $user['id'] = $userId;
    $user['is_blocked'] = (int)$user['is_blocked'];
    $user['installed_count'] = (int)$user['installed_count'];
    $user['installed_apps'] = $userApps[$userId] ?? [];
    $user['storage_used_mb'] = $includeStorage ? admin_estimate_user_storage_mb($user) : null;
    $user['phone_active'] = !empty($user['current_app_ping']);
    $user['status'] = $user['phone_active'] ? 'active' : admin_user_status($user['last_seen'] ?? null);
    $profileImage = (string)($user['profile_image'] ?? '');
    $user['profile_url'] = $profileImage === ''
        ? ''
        : (preg_match('#^https?://#i', $profileImage) || str_starts_with($profileImage, '/')
            ? $profileImage
            : '/virtual-phone/uploads/profile/' . $profileImage);
    $user['current_app_name'] = $user['phone_active'] ? (string)$user['current_app_name'] : '';
    $user['current_app_path'] = $user['phone_active'] ? (string)$user['current_app_path'] : '';
    $user['current_app_icon'] = $user['phone_active'] ? (string)$user['current_app_icon'] : '';
    $activity[] = [
        'user_id' => $userId,
        'user' => (string)$user['username'],
        'status' => $user['status'],
        'message' => $user['status'] === 'active' ? 'Recently active on the device.' : 'Last seen ' . ($user['last_seen'] ?: $user['created_at']),
        'time' => $user['last_seen'] ?: $user['created_at'],
    ];
}
unset($user);

$runningRows = $db->query("SELECT a.user_id, a.app_name, a.app_path, a.app_icon, a.app_started_at, a.last_ping,
        u.username, u.profile_image
    FROM vphone_user_activity a
    INNER JOIN users u ON u.id = a.user_id
    WHERE a.app_path <> '' AND a.last_ping >= DATE_SUB(NOW(), INTERVAL 75 SECOND)
    ORDER BY a.app_name ASC, a.last_ping DESC")->fetchAll(PDO::FETCH_ASSOC);
$activePhoneRows = $db->query("SELECT a.user_id, a.app_name, a.app_path, a.app_icon, a.app_started_at, a.last_ping,
        u.username, u.profile_image
    FROM vphone_user_activity a
    INNER JOIN users u ON u.id = a.user_id
    WHERE a.last_ping >= DATE_SUB(NOW(), INTERVAL 75 SECOND)
    ORDER BY a.last_ping DESC")->fetchAll(PDO::FETCH_ASSOC);
$activePhoneUsers = [];
foreach ($activePhoneRows as $activePhoneRow) {
    $profileImage = (string)($activePhoneRow['profile_image'] ?? '');
    $activePhoneUsers[] = [
        'id' => (int)$activePhoneRow['user_id'],
        'username' => (string)$activePhoneRow['username'],
        'profile_url' => $profileImage === ''
            ? ''
            : (preg_match('#^https?://#i', $profileImage) || str_starts_with($profileImage, '/')
                ? $profileImage
                : '/virtual-phone/uploads/profile/' . $profileImage),
        'phone_active' => true,
        'current_app_name' => (string)$activePhoneRow['app_name'],
        'current_app_path' => (string)$activePhoneRow['app_path'],
        'current_app_icon' => (string)$activePhoneRow['app_icon'],
        'current_app_started_at' => (string)$activePhoneRow['app_started_at'],
        'current_app_ping' => (string)$activePhoneRow['last_ping'],
    ];
}
$runningUsers = [];
$runningApps = [];
foreach ($runningRows as $runningRow) {
    $userId = (int)$runningRow['user_id'];
    $profileImage = (string)($runningRow['profile_image'] ?? '');
    $profileUrl = $profileImage === ''
        ? ''
        : (preg_match('#^https?://#i', $profileImage) || str_starts_with($profileImage, '/')
            ? $profileImage
            : '/virtual-phone/uploads/profile/' . $profileImage);
    $runningUser = [
        'id' => $userId,
        'username' => (string)$runningRow['username'],
        'profile_url' => $profileUrl,
        'app_name' => (string)$runningRow['app_name'],
        'app_path' => (string)$runningRow['app_path'],
        'app_icon' => (string)$runningRow['app_icon'],
        'started_at' => (string)$runningRow['app_started_at'],
        'last_ping' => (string)$runningRow['last_ping'],
    ];
    $runningUsers[] = $runningUser;

    $appKey = (string)$runningRow['app_path'];
    if (!isset($runningApps[$appKey])) {
        $runningApps[$appKey] = [
            'name' => (string)$runningRow['app_name'],
            'path' => $appKey,
            'icon' => (string)$runningRow['app_icon'],
            'active_count' => 0,
            'users' => [],
        ];
    }
    $runningApps[$appKey]['active_count']++;
    $runningApps[$appKey]['users'][] = [
        'id' => $userId,
        'username' => (string)$runningRow['username'],
        'profile_url' => $profileUrl,
        'started_at' => (string)$runningRow['app_started_at'],
    ];
}
$runningApps = array_values($runningApps);

$appQuery = $db->query("SELECT a.id, a.name, a.slug, a.description, a.icon_path, a.entry_file,
        a.is_published, a.created_at, COUNT(i.user_id) AS installs
    FROM vphone_apps a
    LEFT JOIN vphone_app_installs i ON i.app_id = a.id
    GROUP BY a.id, a.name, a.slug, a.description, a.icon_path, a.entry_file, a.is_published, a.created_at
    ORDER BY installs DESC, a.name ASC");
$apps = $appQuery->fetchAll(PDO::FETCH_ASSOC);
foreach ($apps as &$app) {
    $app['id'] = (int)$app['id'];
    $app['is_published'] = (int)$app['is_published'];
    $app['installs'] = (int)$app['installs'];
}
unset($app);

admin_json([
    'success' => true,
    'admin' => [
        'id' => (int)($_SESSION['vphone_root_admin_id'] ?? 0),
        'username' => (string)($_SESSION['vphone_root_admin_username'] ?? 'Admin'),
    ],
    'stats' => $stats,
    'users' => $users,
    'apps' => $apps,
    'running_users' => $runningUsers,
    'running_apps' => $runningApps,
    'active_phone_users' => $activePhoneUsers,
    'activity' => array_slice($activity, 0, 8),
    'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'total' => $totalUsers,
        'pages' => (int)ceil($totalUsers / $limit),
    ],
    'generated_at' => date(DATE_ATOM),
]);
