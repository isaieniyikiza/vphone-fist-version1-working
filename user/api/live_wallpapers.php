<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sign in to your Virtual Phone first.']);
    exit;
}

$wallpaperDirectory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'livewallpaper';
$extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$files = [];
if (is_dir($wallpaperDirectory)) {
    foreach (new DirectoryIterator($wallpaperDirectory) as $entry) {
        if ($entry->isDot() || !$entry->isFile() || $entry->isLink()) {
            continue;
        }
        if (!in_array(strtolower($entry->getExtension()), $extensions, true)) {
            continue;
        }
        $files[] = [
            'name' => $entry->getFilename(),
            'url' => '/virtual-phone/livewallpaper/' . rawurlencode($entry->getFilename()),
        ];
    }
}

usort($files, static fn(array $left, array $right): int => strnatcasecmp($left['name'], $right['name']));
echo json_encode(['success' => true, 'wallpapers' => $files], JSON_UNESCAPED_SLASHES);
