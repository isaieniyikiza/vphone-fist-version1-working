<?php
header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

// Check User ID
if (empty($_POST['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "User ID is required."
    ]);
    exit;
}

$user_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['user_id']);

// Check image
if (empty($_POST['image'])) {
    echo json_encode([
        "success" => false,
        "message" => "No image received."
    ]);
    exit;
}

$image = $_POST['image'];

// Remove Base64 header
$image = str_replace('data:image/jpeg;base64,', '', $image);
$image = str_replace(' ', '+', $image);

$imageData = base64_decode($image);

if ($imageData === false) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid image data."
    ]);
    exit;
}

// Create user folder
$userFolder = __DIR__ . "/" . $user_id . "/";

if (!is_dir($userFolder)) {
    mkdir($userFolder, 0777, true);
}

// Create unique filename
$fileName = "photo_" . date("Ymd_His") . ".jpg";

$filePath = $userFolder . $fileName;

// Save image
if (file_put_contents($filePath, $imageData)) {

    echo json_encode([
        "success" => true,
        "message" => "Photo saved successfully.",
        "photo" => $user_id . "/" . $fileName
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to save photo."
    ]);

}
?>