<?php
declare(strict_types=1);

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'path' => '/',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/icon_rewards_common.php';

function icon_rewards_json(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function icon_rewards_wallet(PDO $db, int $userId): int
{
    $db->prepare('INSERT IGNORE INTO vphone_icon_wallets (user_id) VALUES (?)')->execute([$userId]);
    $stmt = $db->prepare('SELECT balance FROM vphone_icon_wallets WHERE user_id = ? FOR UPDATE');
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

function icon_rewards_grant_daily_login(PDO $db, int $userId): array
{
    icon_rewards_ensure_schema($db);
    $day = gmdate('Y-m-d');
    $reference = 'daily-login:' . $day;
    $db->beginTransaction();
    try {
        $balance = icon_rewards_wallet($db, $userId);
        $find = $db->prepare('SELECT id FROM vphone_icon_transactions WHERE user_id = ? AND reference_key = ? LIMIT 1');
        $find->execute([$userId, $reference]);
        $awarded = 0;
        if (!$find->fetchColumn()) {
            $db->prepare("INSERT INTO vphone_icon_transactions
                (user_id, amount, kind, reference_key, description)
                VALUES (?, 10, 'login_bonus', ?, 'Daily sign-in bonus')")->execute([$userId, $reference]);
            $db->prepare('UPDATE vphone_icon_wallets SET balance = balance + 10 WHERE user_id = ?')->execute([$userId]);
            $balance += 10;
            $awarded = 10;
        }
        $db->commit();
        return ['awarded' => $awarded, 'balance' => $balance];
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $error;
    }
}

function icon_rewards_activity_ping(PDO $db, int $userId): array
{
    icon_rewards_ensure_schema($db);
    $now = time();
    $day = gmdate('Y-m-d', $now);
    $db->beginTransaction();
    try {
        $balance = icon_rewards_wallet($db, $userId);
        $db->prepare("INSERT INTO vphone_icon_activity (user_id, activity_day)
            VALUES (?, ?) ON DUPLICATE KEY UPDATE user_id = VALUES(user_id)")->execute([$userId, $day]);
        $activityQuery = $db->prepare('SELECT activity_day, progress_seconds, earned_today, last_ping_at FROM vphone_icon_activity WHERE user_id = ? FOR UPDATE');
        $activityQuery->execute([$userId]);
        $activity = $activityQuery->fetch(PDO::FETCH_ASSOC);

        $progress = (string)$activity['activity_day'] === $day ? (int)$activity['progress_seconds'] : 0;
        $earnedToday = (string)$activity['activity_day'] === $day ? (int)$activity['earned_today'] : 0;
        $lastPing = (string)$activity['activity_day'] === $day ? (int)($activity['last_ping_at'] ?? 0) : 0;
        $elapsed = $lastPing > 0 && $now > $lastPing && $now - $lastPing <= 35 ? $now - $lastPing : 0;
        $progress += $elapsed;
        $awarded = 0;

        while ($progress >= 240 && $earnedToday < 30) {
            $progress -= 240;
            $credit = min(4, 30 - $earnedToday);
            $reference = 'activity:' . $day . ':' . $earnedToday;
            $db->prepare("INSERT INTO vphone_icon_transactions
                (user_id, amount, kind, reference_key, description)
                VALUES (?, ?, 'activity_reward', ?, 'Four minutes of active rewards-app use')")
                ->execute([$userId, $credit, $reference]);
            $earnedToday += $credit;
            $awarded += $credit;
        }

        if ($awarded > 0) {
            $db->prepare('UPDATE vphone_icon_wallets SET balance = balance + ? WHERE user_id = ?')
                ->execute([$awarded, $userId]);
            $balance += $awarded;
        }
        $db->prepare('UPDATE vphone_icon_activity
            SET activity_day = ?, progress_seconds = ?, earned_today = ?, last_ping_at = ?
            WHERE user_id = ?')->execute([$day, min($progress, 239), $earnedToday, $now, $userId]);
        $db->commit();
        return [
            'awarded' => $awarded,
            'balance' => $balance,
            'earned_today' => $earnedToday,
            'progress_seconds' => min($progress, 239),
            'daily_limit' => 30,
        ];
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $error;
    }
}

function icon_rewards_state(PDO $db, int $userId): array
{
    icon_rewards_ensure_schema($db);
    $balance = icon_rewards_wallet($db, $userId);
    $storageCount = icon_rewards_upgrade_quantity($db, $userId, 'storage_1gb');
    $themeUnlocked = icon_rewards_upgrade_quantity($db, $userId, 'theme_pack') > 0;
    $insightsUnlocked = icon_rewards_upgrade_quantity($db, $userId, 'premium_insights') > 0;
    $today = gmdate('Y-m-d');
    $activityQuery = $db->prepare('SELECT activity_day, progress_seconds, earned_today FROM vphone_icon_activity WHERE user_id = ? LIMIT 1');
    $activityQuery->execute([$userId]);
    $activity = $activityQuery->fetch(PDO::FETCH_ASSOC);
    $todayProgress = is_array($activity) && (string)$activity['activity_day'] === $today
        ? (int)$activity['progress_seconds']
        : 0;
    $earnedToday = is_array($activity) && (string)$activity['activity_day'] === $today
        ? (int)$activity['earned_today']
        : 0;
    $history = [];
    if ($insightsUnlocked) {
        $historyQuery = $db->prepare('SELECT amount, kind, description, created_at FROM vphone_icon_transactions WHERE user_id = ? ORDER BY id DESC LIMIT 50');
        $historyQuery->execute([$userId]);
        $history = $historyQuery->fetchAll(PDO::FETCH_ASSOC);
    }

    return [
        'balance' => $balance,
        'storage_quota_bytes' => icon_rewards_storage_quota($db, $userId),
        'upgrades' => [
            'storage_1gb' => $storageCount,
            'theme_pack' => $themeUnlocked,
            'premium_insights' => $insightsUnlocked,
        ],
        'activity' => [
            'progress_seconds' => $todayProgress,
            'earned_today' => $earnedToday,
            'daily_limit' => 30,
            'reward_seconds' => 240,
            'reward_amount' => 4,
        ],
        'products' => [
            [
                'id' => 'storage_1gb',
                'name' => 'Add 1 GB storage',
                'description' => 'Permanently adds 1 GB to your File Manager quota. Maximum five upgrades.',
                'price' => 100,
                'quantity' => $storageCount,
                'limit' => 5,
            ],
            [
                'id' => 'theme_pack',
                'name' => 'Premium theme: Aurora',
                'description' => 'Unlock the Aurora theme in Settings for your phone.',
                'price' => 50,
                'owned' => $themeUnlocked,
            ],
            [
                'id' => 'premium_insights',
                'name' => 'Unlock Premium Insights',
                'description' => 'See your Icon earnings and purchase history.',
                'price' => 75,
                'owned' => $insightsUnlocked,
            ],
        ],
        'history' => $history,
    ];
}

try {
    $db = icon_rewards_db();
    $userId = icon_rewards_user_id($db);
    icon_rewards_ensure_schema($db);
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $getAction = $_GET['action'] ?? null;
    $action = is_string($getAction) ? $getAction : '';

    if ($method === 'GET' && $action === 'state') {
        icon_rewards_json(['success' => true] + icon_rewards_state($db, $userId));
    }

    if ($method !== 'POST') {
        header('Allow: GET, POST');
        icon_rewards_json(['success' => false, 'message' => 'Method not allowed.'], 405);
    }

    $origin = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
    if ($origin !== '') {
        $originParts = parse_url($origin);
        $hostParts = parse_url('http://' . (string)($_SERVER['HTTP_HOST'] ?? ''));
        $scheme = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
        $requestParts = parse_url($scheme . '://' . (string)($_SERVER['HTTP_HOST'] ?? ''));
        if (!is_array($originParts) || !is_array($requestParts) ||
            strtolower((string)($originParts['scheme'] ?? '')) !== $scheme ||
            strtolower((string)($originParts['host'] ?? '')) !== strtolower((string)($hostParts['host'] ?? '')) ||
            (int)($originParts['port'] ?? ($scheme === 'https' ? 443 : 80)) !==
                (int)($requestParts['port'] ?? ($scheme === 'https' ? 443 : 80))) {
            icon_rewards_json(['success' => false, 'message' => 'This rewards request could not be verified. Reload the app and try again.'], 403);
        }
    }

    $contentType = strtolower(trim(explode(';', (string)($_SERVER['CONTENT_TYPE'] ?? ''))[0]));
    if ($contentType !== 'application/json') {
        icon_rewards_json(['success' => false, 'message' => 'Send rewards actions as JSON.'], 415);
    }
    try {
        $payload = json_decode((string)file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        icon_rewards_json(['success' => false, 'message' => 'The rewards request is invalid.'], 400);
    }
    if (!is_array($payload)) {
        icon_rewards_json(['success' => false, 'message' => 'The rewards request is invalid.'], 400);
    }

    $payloadAction = $payload['action'] ?? null;
    $action = is_string($payloadAction) ? strtolower($payloadAction) : '';
    if ($action === 'login_bonus') {
        icon_rewards_json(['success' => true] + icon_rewards_grant_daily_login($db, $userId));
    }
    if ($action === 'activity_ping') {
        icon_rewards_json(['success' => true] + icon_rewards_activity_ping($db, $userId));
    }
    if ($action === 'purchase') {
        $requestedProduct = $payload['product_id'] ?? null;
        $productId = is_string($requestedProduct) ? $requestedProduct : '';
        $products = [
            'storage_1gb' => ['price' => 100, 'max' => 5, 'name' => 'Add 1 GB storage'],
            'theme_pack' => ['price' => 50, 'max' => 1, 'name' => 'Premium theme: Aurora'],
            'premium_insights' => ['price' => 75, 'max' => 1, 'name' => 'Unlock Premium Insights'],
        ];
        if (!isset($products[$productId])) {
            icon_rewards_json(['success' => false, 'message' => 'Choose an available upgrade.'], 400);
        }

        $product = $products[$productId];
        $db->beginTransaction();
        try {
            $balance = icon_rewards_wallet($db, $userId);
            $ownedQuery = $db->prepare('SELECT quantity FROM vphone_icon_upgrades WHERE user_id = ? AND upgrade_key = ? FOR UPDATE');
            $ownedQuery->execute([$userId, $productId]);
            $quantity = (int)($ownedQuery->fetchColumn() ?: 0);
            if ($quantity >= $product['max']) {
                $db->rollBack();
                icon_rewards_json(['success' => false, 'message' => $productId === 'storage_1gb'
                    ? 'You have reached the five-upgrade storage limit.'
                    : 'You already own this upgrade.'], 409);
            }
            if ($balance < $product['price']) {
                $db->rollBack();
                icon_rewards_json(['success' => false, 'message' => 'You need ' . $product['price'] . ' Icons for this upgrade.'], 409);
            }

            $quantity++;
            $db->prepare("INSERT INTO vphone_icon_upgrades (user_id, upgrade_key, quantity)
                VALUES (?, ?, 1)
                ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)")
                ->execute([$userId, $productId, $quantity]);
            $reference = 'purchase:' . $productId . ':' . $quantity;
            $db->prepare("INSERT INTO vphone_icon_transactions
                (user_id, amount, kind, reference_key, description)
                VALUES (?, ?, 'purchase', ?, ?)")
                ->execute([$userId, -$product['price'], $reference, $product['name']]);
            $db->prepare('UPDATE vphone_icon_wallets SET balance = balance - ? WHERE user_id = ?')
                ->execute([$product['price'], $userId]);
            $db->commit();
            icon_rewards_json([
                'success' => true,
                'message' => $product['name'] . ' unlocked.',
                'balance' => $balance - $product['price'],
                'quantity' => $quantity,
            ]);
        } catch (Throwable $error) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $error;
        }
    }

    icon_rewards_json(['success' => false, 'message' => 'Unknown rewards action.'], 400);
} catch (DomainException $error) {
    icon_rewards_json(['success' => false, 'message' => $error->getMessage()], 401);
} catch (Throwable $error) {
    error_log('Virtual Phone Icon Rewards request failed: ' . $error->getMessage());
    icon_rewards_json(['success' => false, 'message' => 'Icon Rewards could not complete that request. Please try again.'], 500);
}
