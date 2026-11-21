<?php
require_once __DIR__ . '/common.php';

$userId = storageGetUserId();
if ($userId === null) {
    storageJson(['success' => false, 'message' => 'Unauthorized'], 401);
}

$userDir = storageResolveUserDir($userId);
$quotaBytes = storageGetQuotaBytes($userId);
$files = [];
$metadata = storageLoadMetadata($userDir);

if (is_dir($userDir)) {
    $items = scandir($userDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === '.metadata.json') {
            continue;
        }

        $path = $userDir . $item;
        if (!is_file($path)) {
            continue;
        }

        $entryMeta = $metadata[$item] ?? [];
        $type = storageGetFileType($item);
        $name = $entryMeta['name'] ?? $item;
        $size = $entryMeta['size'] ?? @filesize($path);
        $dateAdded = $entryMeta['date_added'] ?? @filemtime($path) * 1000;
        $starred = !empty($entryMeta['starred']);
        $safe = !empty($entryMeta['safe']);

        $files[] = [
            'id' => base64_encode($item),
            'name' => $name,
            'stored_name' => $item,
            'type' => $type,
            'size' => (int) $size,
            'date_added' => (int) $dateAdded,
            'starred' => $starred,
            'safe' => $safe,
        ];
    }

    usort($files, function ($a, $b) {
        return $b['date_added'] - $a['date_added'];
    });
}

$usedBytes = storageGetUsageBytes($userDir);
storageJson([
    'success' => true,
    'files' => $files,
    'used_bytes' => $usedBytes,
    'quota_bytes' => $quotaBytes,
]);
?>