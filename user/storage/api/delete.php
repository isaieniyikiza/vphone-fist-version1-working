<?php
require_once __DIR__ . '/common.php';

$userId = storageGetUserId();
if ($userId === null) {
    storageJson(['success' => false, 'message' => 'Unauthorized'], 401);
}

if (empty($_POST['id'])) {
    storageJson(['success' => false, 'message' => 'Missing file id']);
}

$userDir = storageResolveUserDir($userId);
$storedName = storageResolveStoredNameFromId($_POST['id']);
if ($storedName === '' || basename($storedName) !== $storedName || $storedName === '.metadata.json') {
    storageJson(['success' => false, 'message' => 'Invalid file id'], 400);
}
$path = $userDir . $storedName;

if (!is_file($path)) {
    storageJson(['success' => false, 'message' => 'File not found']);
}

if (!unlink($path)) {
    storageJson(['success' => false, 'message' => 'Could not delete']);
}

$metadata = storageLoadMetadata($userDir);
if (isset($metadata[$storedName])) {
    unset($metadata[$storedName]);
    storageSaveMetadata($userDir, $metadata);
}

storageJson(['success' => true, 'message' => 'Deleted']);
?>