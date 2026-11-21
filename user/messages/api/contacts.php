<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../db.php';

function contacts_json(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if (!$userId || $userId < 1) {
    contacts_json(['success' => false, 'message' => 'Sign in to your Virtual Phone first.'], 401);
}
if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    contacts_json(['success' => false, 'message' => 'Method not allowed.'], 405);
}
if (!$pdo instanceof PDO) {
    contacts_json(['success' => false, 'message' => 'The user directory is unavailable.'], 500);
}

try {
    $stmt = $pdo->prepare('SELECT id, name, number, photo FROM contacts WHERE user_id = ? ORDER BY name ASC, id ASC');
    $stmt->execute([$userId]);
    $contacts = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $contact) {
        $image = trim((string)($contact['photo'] ?? ''));
        $profileUrl = $image === ''
            ? '/virtual-phone/vphone.png'
            : (preg_match('#^https?://#i', $image) || str_starts_with($image, '/')
                ? $image
                : '/virtual-phone/user/contact/' . ltrim($image, '/'));
        $contacts[] = [
            'id' => (int)$contact['id'],
            'username' => (string)$contact['name'],
            'number' => (string)$contact['number'],
            'profile_image' => $profileUrl,
        ];
    }
    contacts_json(['success' => true, 'contacts' => $contacts]);
} catch (Throwable $error) {
    error_log('Virtual Phone contacts list failed: ' . $error->getMessage());
    contacts_json(['success' => false, 'message' => 'Could not load contacts.'], 500);
}
