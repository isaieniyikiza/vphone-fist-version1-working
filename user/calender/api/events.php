<?php
// ============================================================
// 0. SESSION COOKIE CONFIGURATION (must be before session_start)
// ============================================================
// For cross‑origin requests, the session cookie needs SameSite=None and Secure (HTTPS only).
// Adjust if you are on HTTP locally – but production must use HTTPS.
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    session_set_cookie_params([
        'samesite' => 'None',
        'secure'   => true,
        'httponly' => true,
    ]);
}

session_start();

// ============================================================
// 1. CORS HEADERS (handled before any logic)
// ============================================================
// Read the actual origin from the request
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Allow your frontend origin(s) – you can list multiple or use a whitelist.
// For development, you can echo back any origin, but for production,
// restrict it to your known domain(s).
$allowed_origins = [
    'https://your-frontend-domain.com',
    'https://another-domain.com',
    // add more as needed
];

// If the origin is in the allowed list, use it; otherwise fallback to '*' (but then no credentials)
if ($origin && in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} else {
    // If no origin or not allowed, you might still want to allow requests without credentials
    // but we will not send credentials header.
    header('Access-Control-Allow-Origin: *');
    // Do NOT send Access-Control-Allow-Credentials: true when using '*'
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// ============================================================
// 2. HANDLE PREFLIGHT (OPTIONS)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ============================================================
// 3. AUTHENTICATION
// ============================================================
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    errorResponse('Please log in first.', 401);
}

// ============================================================
// 4. DATABASE CONNECTION (adjust path if needed)
// ============================================================
require_once __DIR__ . '/../../../db.php'; 

if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection not available.']);
    exit;
}

// ============================================================
// 5. HELPER FUNCTIONS
// ============================================================
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function errorResponse($message, $status = 400) {
    sendResponse(['success' => false, 'error' => $message], $status);
}

// ============================================================
// 6. ROUTING
// ============================================================
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['history']) && $_GET['history'] == '1') {
                $stmt = $pdo->prepare("SELECT id, event_date, title, time, location, notes, tags, done FROM events WHERE user_id = ? AND done = 1 ORDER BY event_date DESC, id DESC");
                $stmt->execute([$user_id]);
                sendResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
            } elseif (isset($_GET['date'])) {
                $date = $_GET['date'];
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    errorResponse('Invalid date format. Use YYYY-MM-DD.');
                }
                $stmt = $pdo->prepare("SELECT id, user_id, event_date, title, time, location, notes, tags, done, created_at FROM events WHERE user_id = ? AND event_date = ? ORDER BY done ASC, id ASC");
                $stmt->execute([$user_id, $date]);
                sendResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
            } else {
                errorResponse('Missing date or history parameter.', 400);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) errorResponse('Invalid JSON payload.');
            if (empty($data['date']) || empty($data['title'])) {
                errorResponse('Missing required fields: date, title');
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
                errorResponse('Invalid date format. Use YYYY-MM-DD.');
            }
            $stmt = $pdo->prepare("INSERT INTO events (user_id, event_date, title, time, location, notes, tags, done) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                $data['date'],
                $data['title'],
                $data['time'] ?? '',
                $data['location'] ?? '',
                $data['notes'] ?? '',
                $data['tags'] ?? '',
                isset($data['done']) ? (int)$data['done'] : 0
            ]);
            sendResponse(['success' => true, 'id' => $pdo->lastInsertId()]);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['id'])) errorResponse('Missing event id.');
            $id = (int)$data['id'];
            $fields = [];
            $params = [];
            $allowed = ['title', 'time', 'location', 'notes', 'tags', 'done'];
            foreach ($allowed as $field) {
                if (array_key_exists($field, $data)) {
                    $fields[] = "$field = ?";
                    $params[] = ($field === 'done') ? (int)$data[$field] : $data[$field];
                }
            }
            if (empty($fields)) errorResponse('No fields to update.');
            $params[] = $id;
            $params[] = $user_id;
            $sql = "UPDATE events SET " . implode(', ', $fields) . " WHERE id = ? AND user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            if ($stmt->rowCount() === 0) {
                errorResponse('Event not found or you do not have permission.', 404);
            }
            sendResponse(['success' => true]);
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['id'])) errorResponse('Missing event id.');
            $id = (int)$data['id'];
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            if ($stmt->rowCount() === 0) {
                errorResponse('Event not found or you do not have permission.', 404);
            }
            sendResponse(['success' => true]);
            break;

        default:
            errorResponse('Method not allowed.', 405);
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    errorResponse('Database error occurred.', 500);
} catch (Exception $e) {
    error_log('General error: ' . $e->getMessage());
    errorResponse('An unexpected error occurred.', 500);
}
?>