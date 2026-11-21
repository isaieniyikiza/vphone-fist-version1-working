<?php
/**
 * photo-api.php – 照片管理 API
 * 依赖登录系统产生的 Session，自动识别用户 ID 并操作其专属文件夹
 */
session_start();

define('BASE_UPLOAD', dirname(__DIR__, 2) . '/user/storage/uploads'); // /virtual-phone/user/storage/uploads

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function errorResponse($msg, $code = 400) {
    jsonResponse(['success' => false, 'message' => $msg], $code);
}

// 检查登录
if (empty($_SESSION['user_id'])) {
    errorResponse('Unauthorized – please log in first', 401);
}

$userId = $_SESSION['user_id'];
$userFolder = BASE_UPLOAD . '/' . $userId;
if (!is_dir($userFolder)) {
    mkdir($userFolder, 0755, true);
}

$action = $_GET['action'] ?? '';

// ---- 列出图片 ----
if ($action === 'list') {
    $files = scandir($userFolder);
    $images = [];
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $userFolder . '/' . $f;
        if (is_file($path) && strpos(mime_content_type($path), 'image/') === 0) {
            $images[] = [
                'name' => $f,
                'size' => filesize($path),
                'modified' => filemtime($path)
            ];
        }
    }
    usort($images, fn($a, $b) => $b['modified'] - $a['modified']);
    jsonResponse(['images' => $images]);
}

// ---- 提供图片 (二进制) ----
if ($action === 'serve') {
    $file = basename($_GET['file'] ?? '');
    if (empty($file)) errorResponse('Missing file');
    $path = $userFolder . '/' . $file;
    if (!file_exists($path)) errorResponse('File not found', 404);
    header('Content-Type: ' . mime_content_type($path));
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

// ---- 上传图片 ----
if ($action === 'upload') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') errorResponse('Method not allowed', 405);
    if (empty($_FILES['photo'])) errorResponse('No file uploaded');
    $file = $_FILES['photo'];
    if ($file['error'] !== UPLOAD_ERR_OK) errorResponse('Upload error: ' . $file['error']);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (strpos($mime, 'image/') !== 0) errorResponse('Only image files are allowed');

    $origName = basename($file['name']);
    $ext = pathinfo($origName, PATHINFO_EXTENSION);
    $name = pathinfo($origName, PATHINFO_FILENAME);
    $newName = $origName;
    $counter = 1;
    while (file_exists($userFolder . '/' . $newName)) {
        $newName = $name . '_' . $counter . '.' . $ext;
        $counter++;
    }
    if (move_uploaded_file($file['tmp_name'], $userFolder . '/' . $newName)) {
        jsonResponse(['success' => true, 'filename' => $newName]);
    } else {
        errorResponse('Failed to move uploaded file', 500);
    }
}

// ---- 删除图片 ----
if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') errorResponse('Method not allowed', 405);
    $file = basename($_GET['file'] ?? '');
    if (empty($file)) errorResponse('Missing file');
    $path = $userFolder . '/' . $file;
    if (!file_exists($path)) errorResponse('File not found', 404);
    if (unlink($path)) {
        jsonResponse(['success' => true]);
    } else {
        errorResponse('Failed to delete file', 500);
    }
}

errorResponse('Invalid action', 400);
?>