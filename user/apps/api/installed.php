<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

apps_ensure_tables();
$userId = apps_require_user();
$db = apps_db();
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
    $stmt = $db->prepare("
        SELECT a.id, a.slug, a.name, a.description, a.icon_path
        FROM vphone_app_installs i
        INNER JOIN vphone_apps a ON a.id = i.app_id
        WHERE i.user_id = ? AND a.is_published = 1
        ORDER BY a.name ASC
    ");
    $stmt->execute([$userId]);
    apps_json(['success' => true, 'apps' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

$body = json_decode(file_get_contents('php://input'), true);
$appId = filter_var($body['app_id'] ?? null, FILTER_VALIDATE_INT);
if (!$appId || $appId < 1) {
    apps_json(['success' => false, 'message' => 'Choose a valid app.'], 400);
}

if ($method === 'POST') {
    $stmt = $db->prepare('SELECT id FROM vphone_apps WHERE id = ? AND is_published = 1');
    $stmt->execute([$appId]);
    if (!$stmt->fetchColumn()) {
        apps_json(['success' => false, 'message' => 'This app is not available in the Play Store.'], 404);
    }
    $stmt = $db->prepare('INSERT IGNORE INTO vphone_app_installs (user_id, app_id) VALUES (?, ?)');
    $stmt->execute([$userId, $appId]);
    apps_json(['success' => true, 'message' => 'App installed.']);
}

if ($method === 'DELETE') {
    $stmt = $db->prepare('DELETE FROM vphone_app_installs WHERE user_id = ? AND app_id = ?');
    $stmt->execute([$userId, $appId]);
    apps_json(['success' => true, 'message' => 'App uninstalled.']);
}

header('Allow: GET, POST, DELETE');
apps_json(['success' => false, 'message' => 'Method not allowed.'], 405);
