<?php
header('Content-Type: application/json; charset=utf-8');

session_start();

function respond($status, $success, $message, $url = null) {
    http_response_code($status);
    $response = ['success' => $success, 'message' => $message];
    if ($url !== null) {
        $response['url'] = $url;
    }
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, false, 'Method not allowed.');
}

$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if (!$userId) {
    respond(401, false, 'Please sign in before uploading a contact photo.');
}

if (!isset($_FILES['contact_photo']) || !is_array($_FILES['contact_photo'])) {
    respond(400, false, 'Choose a photo to upload.');
}

$file = $_FILES['contact_photo'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    $message = $file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
        ? 'Contact photos must be 5 MB or smaller.'
        : 'The photo could not be uploaded.';
    respond(400, false, $message);
}

if ($file['size'] <= 0 || $file['size'] > 5 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) {
    respond(400, false, 'Choose a valid photo that is 5 MB or smaller.');
}

$imageInfo = @getimagesize($file['tmp_name']);
if ($imageInfo === false || !isset($imageInfo['mime'])) {
    respond(400, false, 'The uploaded file is not a valid image.');
}

$extensionsByMime = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
];
$extension = $extensionsByMime[$imageInfo['mime']] ?? null;
if ($extension === null) {
    respond(400, false, 'Use a JPG, PNG, GIF, or WEBP image.');
}

$uploadDirectory = __DIR__ . '/../uploads/contacts';
if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
    respond(500, false, 'The photo storage folder could not be created.');
}

try {
    $fileName = 'contact_' . $userId . '_' . bin2hex(random_bytes(12)) . '.' . $extension;
} catch (Throwable $error) {
    error_log('Could not generate a contact photo filename: ' . $error->getMessage());
    respond(500, false, 'The photo could not be saved.');
}

if (!move_uploaded_file($file['tmp_name'], $uploadDirectory . DIRECTORY_SEPARATOR . $fileName)) {
    respond(500, false, 'The photo could not be saved.');
}

respond(200, true, 'Photo uploaded successfully.', '/virtual-phone/user/contact/uploads/contacts/' . $fileName);
