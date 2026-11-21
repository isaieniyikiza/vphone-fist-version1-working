<?php
/**
 * get_images.php
 * Returns a list of images for a given user ID.
 * Expected GET parameter: user_id
 * Images are stored in ./{user_id}/ folder.
 * Returns JSON: { success: true, images: [ "user_id/filename.jpg", ... ] }
 */

header('Content-Type: application/json');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Get and sanitize user ID
$user_id = isset($_GET['user_id']) ? trim($_GET['user_id']) : null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id parameter']);
    exit;
}

$sessionUserId = isset($_SESSION['user_id']) ? (string)$_SESSION['user_id'] : '';
if ($sessionUserId === '' || $user_id !== $sessionUserId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You can only view photos in your own account.']);
    exit;
}

// Sanitize to prevent directory traversal
$user_id = preg_replace('/[^a-zA-Z0-9_\-]/', '', $user_id);
if (empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

$folder = __DIR__ . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR;
$images = [];

if (is_dir($folder)) {
    $files = scandir($folder);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        // Check if it's an image file (by extension)
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
            $images[] = $user_id . '/' . $file;
        }
    }

    // Sort by file modification time (newest first)
    usort($images, function($a, $b) use ($folder) {
        $mtime_a = filemtime($folder . basename($a));
        $mtime_b = filemtime($folder . basename($b));
        return $mtime_b - $mtime_a;
    });
}

echo json_encode(['success' => true, 'images' => $images]);
?>