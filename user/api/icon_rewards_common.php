<?php
declare(strict_types=1);

require_once __DIR__ . '/../../db.php';

function icon_rewards_db(): PDO
{
    global $pdo;
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('The rewards database is unavailable.');
    }
    return $pdo;
}

function icon_rewards_ensure_schema(PDO $db): void
{
    static $ready = false;
    if ($ready) {
        return;
    }

    $db->exec("CREATE TABLE IF NOT EXISTS vphone_icon_wallets (
        user_id INT NOT NULL PRIMARY KEY,
        balance INT UNSIGNED NOT NULL DEFAULT 0,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->exec("CREATE TABLE IF NOT EXISTS vphone_icon_transactions (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount INT NOT NULL,
        kind VARCHAR(30) NOT NULL,
        reference_key VARCHAR(140) NOT NULL,
        description VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_vphone_icon_user_reference (user_id, reference_key),
        INDEX idx_vphone_icon_user_created (user_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->exec("CREATE TABLE IF NOT EXISTS vphone_icon_upgrades (
        user_id INT NOT NULL,
        upgrade_key VARCHAR(50) NOT NULL,
        quantity INT UNSIGNED NOT NULL DEFAULT 0,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, upgrade_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->exec("CREATE TABLE IF NOT EXISTS vphone_icon_activity (
        user_id INT NOT NULL PRIMARY KEY,
        activity_day DATE NOT NULL,
        progress_seconds INT UNSIGNED NOT NULL DEFAULT 0,
        earned_today INT UNSIGNED NOT NULL DEFAULT 0,
        last_ping_at INT UNSIGNED DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $ready = true;
}

function icon_rewards_user_id(PDO $db): int
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$userId || $userId < 1) {
        throw new DomainException('Sign in to your Virtual Phone first.');
    }

    $stmt = $db->prepare('SELECT is_blocked FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $blocked = $stmt->fetchColumn();
    if ($blocked === false || (int)$blocked === 1) {
        throw new DomainException('This account is unavailable. Sign in again or contact the administrator.');
    }
    return (int)$userId;
}

function icon_rewards_upgrade_quantity(PDO $db, int $userId, string $upgradeKey): int
{
    icon_rewards_ensure_schema($db);
    $stmt = $db->prepare('SELECT quantity FROM vphone_icon_upgrades WHERE user_id = ? AND upgrade_key = ? LIMIT 1');
    $stmt->execute([$userId, $upgradeKey]);
    return (int)($stmt->fetchColumn() ?: 0);
}

function icon_rewards_storage_quota(PDO $db, int $userId): int
{
    return (5 * 1024 * 1024 * 1024) +
        icon_rewards_upgrade_quantity($db, $userId, 'storage_1gb') * 1024 * 1024 * 1024;
}
