<?php
require_once __DIR__ . '/../../api/icon_rewards_common.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', '0');

function storageGetUserId() {
    if (!isset($_SESSION['user_id']) || !is_scalar($_SESSION['user_id'])) {
        return null;
    }

    $userId = trim((string) $_SESSION['user_id']);
    return $userId !== '' ? $userId : null;
}

function storageJson($payload, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function storageResolveBaseDir() {
    $baseDir = __DIR__ . '/../uploads';
    if (!is_dir($baseDir)) {
        @mkdir($baseDir, 0755, true);
    }
    return $baseDir;
}

function storageResolveUserDir($userId) {
    $baseDir = storageResolveBaseDir();
    $safeUserId = preg_replace('/[^A-Za-z0-9._-]/', '_', (string) $userId);
    $safeUserId = trim($safeUserId, "._-");
    if ($safeUserId === '') {
        $safeUserId = 'guest';
    }

    $userDir = $baseDir . '/' . $safeUserId . '/';
    if (!is_dir($userDir)) {
        @mkdir($userDir, 0755, true);
    }
    return $userDir;
}

function storageGetUsageBytes($userDir) {
    $usedBytes = 0;
    foreach (new DirectoryIterator($userDir) as $item) {
        if ($item->isDot() || !$item->isFile() || $item->getFilename() === '.metadata.json') {
            continue;
        }

        $size = $item->getSize();
        if ($size !== false) {
            $usedBytes += $size;
        }
    }

    return $usedBytes;
}

function storageGetQuotaBytes($userId) {
    return icon_rewards_storage_quota(icon_rewards_db(), (int) $userId);
}

function storageParseIniBytes($value) {
    if (!preg_match('/^\s*(\d+(?:\.\d+)?)\s*([KMG])?B?\s*$/i', (string) $value, $matches)) {
        return null;
    }

    $unit = strtoupper($matches[2] ?? '');
    $multiplier = $unit === 'G' ? 1024 ** 3 : ($unit === 'M' ? 1024 ** 2 : ($unit === 'K' ? 1024 : 1));
    return (int) floor((float) $matches[1] * $multiplier);
}

function storageSanitizeFileName($name) {
    $name = trim((string) basename($name));
    $name = str_replace(['\\', '/'], '-', $name);

    $info = pathinfo($name);
    $base = isset($info['filename']) ? $info['filename'] : 'file';
    $extension = isset($info['extension']) ? strtolower($info['extension']) : 'bin';

    $base = preg_replace('/[^A-Za-z0-9._-]+/', '_', $base);
    $base = trim($base, '._-');
    if ($base === '') {
        $base = 'file';
    }
    $base = substr($base, 0, 80);

    $extension = preg_replace('/[^a-z0-9]+/', '', $extension);
    if ($extension === '') {
        $extension = 'bin';
    }

    $timestamp = time();
    $random = bin2hex(random_bytes(4));
    return $base . '_' . $timestamp . '_' . $random . '.' . $extension;
}

function storageGetMetadataPath($userDir) {
    return $userDir . '.metadata.json';
}

function storageLoadMetadata($userDir) {
    $metadataPath = storageGetMetadataPath($userDir);
    if (!is_file($metadataPath)) {
        return [];
    }

    $contents = @file_get_contents($metadataPath);
    if ($contents === false) {
        return [];
    }

    $decoded = json_decode($contents, true);
    return is_array($decoded) ? $decoded : [];
}

function storageSaveMetadata($userDir, $metadata) {
    $metadataPath = storageGetMetadataPath($userDir);
    return @file_put_contents($metadataPath, json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}

function storageGetFileType($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'avif', 'heic', 'heif', 'tif', 'tiff', 'ico'];
    $videoExts = ['mp4', 'mov', 'avi', 'mkv', 'webm', 'flv', 'm4v', 'mpeg', 'mpg', '3gp', '3g2', 'ts'];
    $audioExts = ['mp3', 'wav', 'flac', 'aac', 'ogg', 'wma', 'm4a', 'opus', 'amr', 'aiff', 'aif', 'm4b'];
    $appExts = ['apk', 'aab', 'apks', 'xapk', 'ipa', 'msi', 'dmg', 'pkg', 'deb', 'rpm', 'zip', 'rar', '7z', 'tar', 'gz'];

    if (in_array($ext, $imageExts, true)) {
        return 'image';
    }
    if (in_array($ext, $videoExts, true)) {
        return 'video';
    }
    if (in_array($ext, $audioExts, true)) {
        return 'audio';
    }
    if (in_array($ext, $appExts, true)) {
        return 'app';
    }
    return 'document';
}

function storageIsAllowedFile($filename, $tmpName) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $blockedExts = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'bat', 'cmd', 'com', 'scr', 'js', 'jar'];
    if (in_array($extension, $blockedExts, true)) {
        return false;
    }

    $allowedExts = [
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'avif', 'heic', 'heif', 'tif', 'tiff', 'ico',
        'mp4', 'mov', 'avi', 'mkv', 'webm', 'flv', 'm4v', 'mpeg', 'mpg', '3gp', '3g2', 'ts',
        'mp3', 'wav', 'flac', 'aac', 'ogg', 'wma', 'm4a', 'opus', 'amr', 'aiff', 'aif', 'm4b',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'json', 'xml', 'zip', 'rar', '7z', 'tar', 'gz',
        'apk', 'aab', 'apks', 'xapk', 'ipa', 'msi', 'dmg', 'pkg', 'deb', 'rpm',
        'md', 'log', 'psd', 'ai', 'eps', 'cdr', 'dat', 'odt', 'ods', 'odp', 'rtf'
    ];

    if ($extension === '' || !in_array($extension, $allowedExts, true)) {
        return false;
    }

    if (!is_uploaded_file($tmpName) && !is_file($tmpName)) {
        return false;
    }

    return true;
}

function storageCreateFileEntry($userDir, $storedName, $originalName, $tmpName) {
    $path = $userDir . $storedName;
    $metadata = storageLoadMetadata($userDir);
    $fileType = storageGetFileType($storedName);
    $size = @filesize($path);

    if (!isset($metadata[$storedName])) {
        $metadata[$storedName] = [
            'stored_name' => $storedName,
            'name' => $originalName,
            'type' => $fileType,
            'size' => $size !== false ? $size : 0,
            'date_added' => time() * 1000,
            'starred' => false,
            'safe' => false,
            'mime' => @mime_content_type($path) ?: 'application/octet-stream',
        ];
    }

    storageSaveMetadata($userDir, $metadata);
    return $metadata[$storedName];
}

function storageResolveStoredNameFromId($id) {
    if (empty($id)) {
        return '';
    }

    $decoded = @base64_decode((string) $id, true);
    if ($decoded !== false && $decoded !== '') {
        return (string) $decoded;
    }

    return (string) $id;
}

function storageGetFileIconClass($type) {
    $icons = [
        'image' => 'img',
        'video' => 'vid',
        'audio' => 'aud',
        'app' => 'app',
        'document' => 'doc',
    ];
    return $icons[$type] ?? 'doc';
}

function storageGetCategoryIcon($type) {
    $icons = [
        'image' => 'fa-image',
        'video' => 'fa-video',
        'audio' => 'fa-music',
        'app' => 'fa-th',
        'document' => 'fa-file-alt',
    ];
    return $icons[$type] ?? 'fa-file-alt';
}
?>