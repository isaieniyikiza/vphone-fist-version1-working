<?php
require_once __DIR__ . '/common.php';

$userId = storageGetUserId();
if ($userId === null) {
    http_response_code(401);
    exit;
}

if (empty($_GET['id'])) {
    http_response_code(400);
    exit;
}

$userDir = storageResolveUserDir($userId);
$storedName = storageResolveStoredNameFromId($_GET['id']);
if ($storedName === '' || basename($storedName) !== $storedName || $storedName === '.metadata.json') {
    http_response_code(400);
    exit;
}
$path = $userDir . $storedName;

if (!is_file($path)) {
    http_response_code(404);
    exit;
}

$metadata = storageLoadMetadata($userDir);
$displayName = $metadata[$storedName]['name'] ?? $storedName;
$mime = $metadata[$storedName]['mime'] ?? @mime_content_type($path) ?: 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . addslashes($displayName) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
?>