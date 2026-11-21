<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

function gallery_delete_json(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    gallery_delete_json(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$sessionUserId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if (!$sessionUserId || $sessionUserId < 1) {
    gallery_delete_json(['success' => false, 'message' => 'Sign in to your Virtual Phone first.'], 401);
}

$requestedUserId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
$imagePath = str_replace('\\', '/', trim((string)($_POST['image_path'] ?? '')));
$parts = explode('/', $imagePath);
if ($requestedUserId !== $sessionUserId || count($parts) !== 2 || (int)$parts[0] !== $sessionUserId) {
    gallery_delete_json(['success' => false, 'message' => 'You can only delete photos in your own account.'], 403);
}

$filename = $parts[1];
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
if ($filename === '' || $filename === '.' || $filename === '..' || basename($filename) !== $filename ||
    !in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true)) {
    gallery_delete_json(['success' => false, 'message' => 'Invalid image path.'], 400);
}

$userDirectory = realpath(__DIR__ . DIRECTORY_SEPARATOR . $sessionUserId);
$targetPath = $userDirectory ? realpath($userDirectory . DIRECTORY_SEPARATOR . $filename) : false;
if (!$userDirectory || !$targetPath || !str_starts_with($targetPath, $userDirectory . DIRECTORY_SEPARATOR) || !is_file($targetPath)) {
    gallery_delete_json(['success' => false, 'message' => 'Image not found.'], 404);
}

if (!unlink($targetPath)) {
    gallery_delete_json(['success' => false, 'message' => 'Could not delete the image.'], 500);
}

gallery_delete_json(['success' => true, 'message' => 'Image deleted.']);