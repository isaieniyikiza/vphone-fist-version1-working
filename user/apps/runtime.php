<?php
declare(strict_types=1);

require_once __DIR__ . '/api/common.php';

apps_ensure_tables();
$userId = apps_require_user();
$appId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$appId || $appId < 1) {
    http_response_code(404);
    exit('App not found.');
}

$stmt = apps_db()->prepare("
    SELECT a.id, a.name, a.entry_file, a.slug
    FROM vphone_apps a
    INNER JOIN vphone_app_installs i ON i.app_id = a.id
    WHERE a.id = ? AND i.user_id = ? AND a.is_published = 1
    LIMIT 1
");
$stmt->execute([$appId, $userId]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$app) {
    http_response_code(404);
    exit('This app is not installed or is no longer available.');
}

$appsRoot = realpath(__DIR__ . '/published');
$appRoot = $appsRoot ? realpath($appsRoot . DIRECTORY_SEPARATOR . $app['slug']) : false;
$entryPath = $appRoot ? realpath($appRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $app['entry_file'])) : false;
if (!$appsRoot || !$appRoot || !$entryPath || !str_starts_with($entryPath, $appRoot . DIRECTORY_SEPARATOR) || !is_file($entryPath)) {
    http_response_code(404);
    exit('The app files are missing.');
}

$entryUrl = '/virtual-phone/user/apps/published/' .
    implode('/', array_map('rawurlencode', explode('/', $app['slug'] . '/' . $app['entry_file'])));
$safeName = htmlspecialchars($app['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$safeUrl = htmlspecialchars($entryUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $safeName ?> · Virtual Phone</title>
    <link rel="stylesheet" href="/virtual-phone/user/settings/theme.css">
    <script defer src="/virtual-phone/user/settings/theme.js"></script>
    <style>
        *{box-sizing:border-box}html,body{height:100%;margin:0;font:14px system-ui,-apple-system,Segoe UI,sans-serif;background:#10131f;color:#fff}
        .phone{height:100%;display:flex;flex-direction:column;background:#111522}
        header{height:52px;flex:none;display:flex;align-items:center;gap:12px;padding:0 16px;background:#1a2030;border-bottom:1px solid #ffffff18}
        header button{border:0;border-radius:10px;padding:8px 12px;color:#fff;background:#ffffff16;font-weight:600;cursor:pointer}
        header button:hover{background:#ffffff28}header strong{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        iframe{flex:1;width:100%;min-height:0;border:0;background:#fff}
    </style>
</head>
<body>
    <main class="phone">
        <header>
            <button type="button" onclick="window.location.href='/virtual-phone/user/user_phone_dashboard.html'">Close</button>
            <strong><?= $safeName ?></strong>
            <button type="button" onclick="window.location.href='/virtual-phone/user/user_phone_dashboard.html'">Home</button>
        </header>
        <iframe id="appFrame" title="<?= $safeName ?>" src="<?= $safeUrl ?>"></iframe>
    </main>
    <script>
        window.addEventListener('message', function (event) {
            if (event.origin === window.location.origin && event.data === 'vphone-close-app') {
                window.location.href = '/virtual-phone/user/user_phone_dashboard.html';
            }
        });
        document.getElementById('appFrame').addEventListener('load', function () {
            try {
                const appDocument = this.contentDocument;
                if (!appDocument) return;
                const appHead = appDocument.head || appDocument.documentElement;
                if (!appHead) return;
                if (!appDocument.querySelector('link[data-vphone-theme-styles], link[href="/virtual-phone/user/settings/theme.css"]')) {
                    const themeStyles = appDocument.createElement('link');
                    themeStyles.rel = 'stylesheet';
                    themeStyles.href = '/virtual-phone/user/settings/theme.css';
                    themeStyles.dataset.vphoneThemeStyles = 'true';
                    appHead.appendChild(themeStyles);
                }
                if (!appDocument.querySelector('script[data-vphone-theme-script], script[src="/virtual-phone/user/settings/theme.js"]')) {
                    const themeScript = appDocument.createElement('script');
                    themeScript.src = '/virtual-phone/user/settings/theme.js';
                    themeScript.dataset.vphoneThemeScript = 'true';
                    appHead.appendChild(themeScript);
                }
                this.contentDocument.addEventListener('click', function (event) {
                    if (!(event.target instanceof Element)) return;
                    const control = event.target.closest('a,button,[role="button"],input[type="button"]');
                    if (!control) return;

                    const label = [
                        control.getAttribute('aria-label'),
                        control.getAttribute('title'),
                        control.getAttribute('data-app-close'),
                        control.id,
                        control.className,
                    ].join(' ').toLowerCase();
                    const targetUrl = control instanceof HTMLAnchorElement ? control.href : '';
                    if (targetUrl.includes('/virtual-phone/user/user_phone_dashboard.html') ||
                        /(?:app-close|close-app|exit-app|home-phone)/.test(label)) {
                        event.preventDefault();
                        event.stopImmediatePropagation();
                        window.location.href = '/virtual-phone/user/user_phone_dashboard.html';
                    }
                }, true);
            } catch (error) {
                console.error('Could not connect the custom app close action:', error);
            }
        });
    </script>
</body>
</html>
