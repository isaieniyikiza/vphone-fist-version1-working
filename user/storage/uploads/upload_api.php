<?php
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Check if user_id and photo are sent
if (isset($_POST['user_id']) && isset($_FILES['photo'])) {
    $user_id = trim($_POST['user_id']);
    if (empty($user_id)) {
        $response['message'] = 'User ID is missing.';
        echo json_encode($response);
        exit;
    }

    // Base folder – adjust if needed
    $base_folder = __DIR__ . '/../../uploads/';  // stores in /virtual-phone/uploads/
    // Or use a relative path like './uploads/'
    // Make sure the folder exists or create it
    if (!is_dir($base_folder)) {
        mkdir($base_folder, 0777, true);
    }

    // User-specific folder
    $user_folder = $base_folder . $user_id . '/';
    if (!is_dir($user_folder)) {
        mkdir($user_folder, 0777, true);
    }

    $file = $_FILES['photo'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = 'photo_' . date('Ymd_His') . '.' . $ext;
        $destination = $user_folder . $new_name;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $response['success'] = true;
            $response['message'] = 'Photo uploaded successfully.';
            $response['file'] = $destination;
        } else {
            $response['message'] = 'Failed to move uploaded file.';
        }
    } else {
        $response['message'] = 'File upload error: ' . $file['error'];
    }
} else {
    $response['message'] = 'Missing user_id or photo.';
}

echo json_encode($response);
?>