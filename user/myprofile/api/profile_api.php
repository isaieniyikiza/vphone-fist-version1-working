<?php
/**
 * Single API for profile management
 * Actions: get_profile, update_profile, delete_photo, update_username, update_password
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- Load PDO connection ---
// Adjust the path to your db.php – if this file is in /virtual-phone/user/api/
// and db.php is in /virtual-phone/db.php, use:

require_once __DIR__ . '/../../../db.php';

// Verify PDO is available
if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection not available.']);
    exit;
}

// --- Authentication ---
// If not logged in, return error (or use a demo user)
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    // For testing, you can uncomment the next line to use a default user:
    // $user_id = 1;
    // But we'll require login:
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- Helper to send JSON response ---
function sendResponse($success, $message = '', $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

try {
    switch ($action) {

        case 'get_profile':
            $stmt = $pdo->prepare("SELECT id, username, profile_image FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                sendResponse(true, '', ['user' => $user]);
            } else {
                sendResponse(false, 'User not found.');
            }
            break;

        case 'update_profile':
            // Handle file upload
            if (!isset($_FILES['profile_image']) || !is_array($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
                sendResponse(false, 'No file uploaded or upload error.');
            }

            $file = $_FILES['profile_image'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $mime = $file['type'] ?? '';
            if (!in_array($mime, $allowed, true)) {
                sendResponse(false, 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed.');
            }
            if ($file['size'] > 10 * 1024 * 1024) {
                sendResponse(false, 'File too large (max 10MB).');
            }

            // Ensure upload directory exists
            $project_root = dirname(__DIR__, 3);
            $upload_dir = $project_root . '/uploads/profile/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('profile_', true) . '.' . $ext;
            $destination = $upload_dir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                sendResponse(false, 'Failed to move uploaded file.');
            }

            // Delete old profile image if exists
            try {
                $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $old = $stmt->fetchColumn();
                if ($old) {
                    $old_path = $project_root . str_replace('/virtual-phone', '', $old);
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
            } catch (Exception $e) {
                // ignore
            }

            // Update database
            $profile_path = '/virtual-phone/uploads/profile/' . $filename;
            $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->execute([$profile_path, $user_id]);

            sendResponse(true, 'Profile photo updated.', ['profile_image' => $profile_path]);
            break;

        case 'delete_photo':
            // Get current image path
            $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $old = $stmt->fetchColumn();

            if ($old) {
                $project_root = dirname(__DIR__, 3);
                $file_path = $project_root . str_replace('/virtual-phone', '', $old);
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            $stmt = $pdo->prepare("UPDATE users SET profile_image = NULL WHERE id = ?");
            $stmt->execute([$user_id]);

            sendResponse(true, 'Profile photo removed.');
            break;

        case 'update_username':
            $input = json_decode(file_get_contents('php://input'), true);
            $new_username = trim($input['username'] ?? '');

            if (strlen($new_username) < 3) {
                sendResponse(false, 'Username must be at least 3 characters.');
            }

            // Check if username is taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$new_username, $user_id]);
            if ($stmt->fetch()) {
                sendResponse(false, 'Username already taken.');
            }

            $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->execute([$new_username, $user_id]);

            sendResponse(true, 'Username updated.');
            break;

        case 'update_password':
            $input = json_decode(file_get_contents('php://input'), true);
            $current = $input['current_password'] ?? '';
            $new = $input['new_password'] ?? '';

            if (empty($current) || empty($new)) {
                sendResponse(false, 'All fields required.');
            }
            if (strlen($new) < 6) {
                sendResponse(false, 'New password must be at least 6 characters.');
            }

            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($current, $hash)) {
                sendResponse(false, 'Current password is incorrect.');
            }

            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$new_hash, $user_id]);

            sendResponse(true, 'Password updated.');
            break;

        default:
            http_response_code(400);
            sendResponse(false, 'Invalid action.');
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    sendResponse(false, 'Database error: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    sendResponse(false, 'Server error: ' . $e->getMessage());
}
?>