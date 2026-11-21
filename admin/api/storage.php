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
$search = trim((string)($_GET['search'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(200, max(1, (int)($_GET['limit'] ?? 100)));
$offset = ($page - 1) * $limit;

$where = '';
$params = [];
if ($search !== '') {
    $where = 'WHERE u.username LIKE :search';
}
$countQuery = $db->prepare("SELECT COUNT(*) FROM users u $where");
if ($search !== '') {
    $countQuery->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}
$countQuery->execute();
$totalUsers = (int)$countQuery->fetchColumn();

$userQuery = $db->prepare("SELECT u.id, u.username, u.profile_image, u.created_at
    FROM users u $where
    ORDER BY u.username ASC
    LIMIT :limit OFFSET :offset");
if ($search !== '') {
    $userQuery->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}
$userQuery->bindValue(':limit', $limit, PDO::PARAM_INT);
$userQuery->bindValue(':offset', $offset, PDO::PARAM_INT);
$userQuery->execute();
$users = $userQuery->fetchAll(PDO::FETCH_ASSOC);

$storageRoot = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads';
$allUsedBytes = 0;
foreach ($users as &$user) {
    $userId = (int)$user['id'];
    $userDir = $storageRoot . DIRECTORY_SEPARATOR . $userId;
    $files = [];
    $usedBytes = 0;
    if (is_dir($userDir)) {
        $metadataPath = $userDir . DIRECTORY_SEPARATOR . '.metadata.json';
        $metadata = [];
        if (is_file($metadataPath)) {
            $decoded = json_decode((string)file_get_contents($metadataPath), true);
            $metadata = is_array($decoded) ? $decoded : [];
        }
        foreach (new DirectoryIterator($userDir) as $entry) {
            if ($entry->isDot() || !$entry->isFile() || $entry->isLink() || $entry->getFilename() === '.metadata.json') {
                continue;
            }
            $storedName = $entry->getFilename();
            $size = (int)$entry->getSize();
            $usedBytes += $size;
            $files[] = [
                'name' => (string)($metadata[$storedName]['name'] ?? $storedName),
                'stored_name' => $storedName,
                'type' => (string)($metadata[$storedName]['type'] ?? 'file'),
                'size_bytes' => $size,
                'modified_at' => date(DATE_ATOM, (int)$entry->getMTime()),
            ];
        }
    }

    $profileImage = (string)($user['profile_image'] ?? '');
    $profileUrl = $profileImage === ''
        ? ''
        : (preg_match('#^https?://#i', $profileImage) || str_starts_with($profileImage, '/')
            ? $profileImage
            : '/virtual-phone/uploads/profile/' . $profileImage);
    $profilePath = $profileUrl !== '' && str_starts_with($profileUrl, '/virtual-phone/')
        ? rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\') . $profileUrl
        : '';
    if ($profilePath !== '' && is_file($profilePath) && !is_link($profilePath)) {
        $profileSize = (int)filesize($profilePath);
        $usedBytes += $profileSize;
        $files[] = [
            'name' => basename($profilePath),
            'stored_name' => basename($profilePath),
            'type' => 'profile image',
            'size_bytes' => $profileSize,
            'modified_at' => date(DATE_ATOM, (int)filemtime($profilePath)),
        ];
    }

    usort($files, static fn(array $left, array $right): int => strcmp($right['modified_at'], $left['modified_at']));
    $allUsedBytes += $usedBytes;
    $user['id'] = $userId;
    $user['profile_url'] = $profileUrl;
    $user['used_bytes'] = $usedBytes;
    $user['used_mb'] = round($usedBytes / 1024 / 1024, 2);
    $user['file_count'] = count($files);
    $user['files'] = $files;
}
unset($user);

admin_json([
    'success' => true,
    'users' => $users,
    'totals' => [
        'users' => $totalUsers,
        'files_on_page' => array_sum(array_column($users, 'file_count')),
        'storage_bytes_on_page' => $allUsedBytes,
        'storage_mb_on_page' => round($allUsedBytes / 1024 / 1024, 2),
    ],
    'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'total' => $totalUsers,
        'pages' => (int)ceil($totalUsers / $limit),
    ],
    'generated_at' => date(DATE_ATOM),
]);
