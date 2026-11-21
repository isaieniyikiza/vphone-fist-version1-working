<?php
require_once __DIR__ . '/common.php';

$userId = storageGetUserId();
if ($userId === null) {
    storageJson(['success' => false, 'message' => 'Unauthorized'], 401);
}

$userDir = storageResolveUserDir($userId);
$quotaBytes = storageGetQuotaBytes($userId);
if (empty($_FILES['files']['name'])) {
    $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
    $postMaxBytes = storageParseIniBytes(ini_get('post_max_size'));
    if ($contentLength > 0 && $postMaxBytes !== null && $contentLength > $postMaxBytes) {
        storageJson([
            'success' => false,
            'message' => 'The selected files exceed this server’s ' . ini_get('post_max_size') . ' total request upload limit. Upload fewer or smaller files.',
        ], 413);
    }
    storageJson(['success' => false, 'message' => 'No files were received. Select a supported file and try again.'], 400);
}

$uploaded = 0;
$errors = 0;
$uploadedFiles = [];
$quotaExceeded = false;
$uploadErrors = [];
$usedBytes = storageGetUsageBytes($userDir);

$files = $_FILES['files'];
$names = is_array($files['name'] ?? null) ? $files['name'] : [$files['name'] ?? ''];
$tmpNames = is_array($files['tmp_name'] ?? null) ? $files['tmp_name'] : [$files['tmp_name'] ?? ''];
$errorsList = is_array($files['error'] ?? null) ? $files['error'] : [$files['error'] ?? UPLOAD_ERR_NO_FILE];

for ($i = 0; $i < count($names); $i++) {
    $name = $names[$i] ?? '';
    $tmpName = $tmpNames[$i] ?? '';
    $errorCode = $errorsList[$i] ?? UPLOAD_ERR_NO_FILE;

    if ($errorCode !== UPLOAD_ERR_OK || $name === '' || $tmpName === '') {
        $errors++;
        if ($errorCode === UPLOAD_ERR_INI_SIZE) {
            $uploadErrors[] = 'A file exceeds the server’s ' . ini_get('upload_max_filesize') . ' per-file upload limit.';
        } elseif ($errorCode === UPLOAD_ERR_PARTIAL) {
            $uploadErrors[] = 'A file was only partially uploaded. Please retry.';
        } elseif ($errorCode === UPLOAD_ERR_NO_TMP_DIR || $errorCode === UPLOAD_ERR_CANT_WRITE) {
            $uploadErrors[] = 'The server could not save an uploaded file. Check the XAMPP upload folder permissions.';
        } else {
            $uploadErrors[] = 'One or more files could not be uploaded.';
        }
        continue;
    }

    if (!storageIsAllowedFile($name, $tmpName)) {
        $errors++;
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $uploadErrors[] = $extension === ''
            ? 'A file is missing its extension. Add a supported file extension and try again.'
            : '“' . $extension . '” files are not supported. Supported media formats include JPG, PNG, GIF, MP4, MOV, MP3, WAV, FLAC, and OGG. App packages include APK, AAB, APKS, XAPK, and IPA.';
        continue;
    }

    $fileSize = filesize($tmpName);
    if ($fileSize === false || $fileSize > $quotaBytes - $usedBytes) {
        $errors++;
        $quotaExceeded = true;
        continue;
    }

    $safeName = storageSanitizeFileName($name);
    $dest = $userDir . $safeName;
    if (move_uploaded_file($tmpName, $dest)) {
        storageCreateFileEntry($userDir, $safeName, $name, $tmpName);
        $usedBytes += $fileSize;
        $uploaded++;
        $uploadedFiles[] = ['name' => $name, 'stored_name' => $safeName];
    } else {
        $errors++;
    }
}

if ($uploaded === 0 && $quotaExceeded) {
    storageJson([
        'success' => false,
        'message' => 'This upload exceeds your remaining storage. Remove files or upgrade storage in the Icon Rewards app.',
        'used_bytes' => $usedBytes,
        'quota_bytes' => $quotaBytes,
    ], 413);
}

$uploadErrors = array_values(array_unique($uploadErrors));
$message = $uploaded . ' file(s) uploaded, ' . $errors . ' failed.';
if ($uploadErrors) {
    $message .= ' ' . implode(' ', $uploadErrors);
}
if ($quotaExceeded && $uploaded > 0) {
    $message .= ' Some files did not fit in your remaining storage.';
}

storageJson([
    'success' => $uploaded > 0,
    'uploaded' => $uploaded,
    'errors' => $errors,
    'files' => $uploadedFiles,
    'used_bytes' => $usedBytes,
    'quota_bytes' => $quotaBytes,
    'message' => $message
], $uploaded > 0 ? 200 : 400);
?>