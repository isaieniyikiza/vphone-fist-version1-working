<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'path' => '/',
    ]);
    session_start();
}

if (!empty($_SESSION['google_workspace_oauth_state'])) {
    $_GET['action'] = 'callback';
    require __DIR__ . '/google_workspace.php';
    exit;
}

session_write_close();
$_GET['action'] = 'callback';
require __DIR__ . '/google_auth.php';
