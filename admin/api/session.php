<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

admin_ensure_schema();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $admin = admin_current_user();
    $db = admin_db();
    $setupRequired = (int)$db->query('SELECT COUNT(*) FROM vphone_admins')->fetchColumn() === 0;
    admin_json(['success' => true, 'setup_required' => $setupRequired, 'admin' => $admin]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = json_decode((string)file_get_contents('php://input'), true);
    $payload = is_array($payload) ? $payload : [];
    $action = strtolower((string)($payload['action'] ?? ''));
    $username = trim((string)($payload['username'] ?? ''));
    $password = (string)($payload['password'] ?? '');

    $db = admin_db();
    $count = (int)$db->query('SELECT COUNT(*) FROM vphone_admins')->fetchColumn();

    if ($action === 'setup') {
        $remoteAddress = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        if (!in_array($remoteAddress, ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true)) {
            admin_json(['success' => false, 'message' => 'Initial admin setup is only allowed from this computer.'], 403);
        }
        if ($count > 0) {
            admin_json(['success' => false, 'message' => 'The admin account already exists. Please sign in instead.'], 409);
        }
        if (strlen($username) < 3 || strlen($username) > 80 || strlen($password) < 8) {
            admin_json(['success' => false, 'message' => 'Use a username of at least 3 characters and a password of at least 8 characters.'], 400);
        }

        $lock = (int)$db->query("SELECT GET_LOCK('virtual-phone-admin-setup', 5)")->fetchColumn();
        if ($lock !== 1) {
            admin_json(['success' => false, 'message' => 'Admin setup is busy. Please try again.'], 503);
        }
        try {
            $count = (int)$db->query('SELECT COUNT(*) FROM vphone_admins')->fetchColumn();
            if ($count > 0) {
                admin_json(['success' => false, 'message' => 'The admin account already exists. Please sign in instead.'], 409);
            }
            $stmt = $db->prepare('INSERT INTO vphone_admins (username, password_hash) VALUES (?, ?)');
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            $adminId = (int)$db->lastInsertId();
            $db->query("SELECT RELEASE_LOCK('virtual-phone-admin-setup')");
            session_regenerate_id(true);
            $_SESSION['vphone_root_admin_id'] = $adminId;
            $_SESSION['vphone_root_admin_username'] = $username;
            admin_json(['success' => true, 'admin' => ['id' => $adminId, 'username' => $username]]);
        } catch (Throwable $error) {
            $db->query("SELECT RELEASE_LOCK('virtual-phone-admin-setup')");
            throw $error;
        }
    }

    if ($action === 'login') {
        if ($count === 0) {
            admin_json(['success' => false, 'message' => 'No admin account exists yet. Create one first.'], 404);
        }
        if (strlen($username) < 3 || strlen($password) < 8) {
            admin_json(['success' => false, 'message' => 'Username and password are required.'], 400);
        }

        $stmt = $db->prepare('SELECT id, username, password_hash FROM vphone_admins WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin || !password_verify($password, $admin['password_hash'] ?? '')) {
            admin_json(['success' => false, 'message' => 'Incorrect admin username or password.'], 401);
        }

        session_regenerate_id(true);
        $_SESSION['vphone_root_admin_id'] = (int)$admin['id'];
        $_SESSION['vphone_root_admin_username'] = $admin['username'];
        admin_json(['success' => true, 'admin' => ['id' => (int)$admin['id'], 'username' => (string)$admin['username']]]);
    }

    admin_json(['success' => false, 'message' => 'Unknown admin action.'], 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $_SESSION = [];
    session_destroy();
    admin_json(['success' => true, 'message' => 'Signed out.']);
}

admin_json(['success' => false, 'message' => 'Method not allowed.'], 405);
