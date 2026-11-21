<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

apps_ensure_tables();
$userId = apps_require_user();
$stmt = apps_db()->prepare("
    SELECT a.id, a.name, a.slug, a.description, a.icon_path,
           EXISTS(
               SELECT 1 FROM vphone_app_installs i
               WHERE i.user_id = ? AND i.app_id = a.id
           ) AS installed
    FROM vphone_apps a
    WHERE a.is_published = 1
    ORDER BY a.name ASC
");
$stmt->execute([$userId]);
apps_json(['success' => true, 'apps' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
