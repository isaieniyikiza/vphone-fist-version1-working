<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();

require_once __DIR__ . '/../../../db.php';

if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized."
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed."
    ]);
    exit;
}

if (!isset($_FILES['contact_photo']) || $_FILES['contact_photo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "No file uploaded or upload error."
    ]);
    exit;
}

$file = $_FILES['contact_photo'];

$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Only JPG, PNG, GIF and WEBP images are allowed."
    ]);
    exit;
}

// Create upload folder for contact photos
$uploadDir = __DIR__ . "/../uploads/contacts/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$fileName = "contact_" . $user_id . "_" . time() . "." . $extension;
$filePath = $uploadDir . $fileName;

if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to save image."
    ]);
    exit;
}

// Return the URL
$imageUrl = "uploads/contacts/" . $fileName;

echo json_encode([
    "success" => true,
    "message" => "Photo uploaded successfully.",
    "url" => $imageUrl
]);