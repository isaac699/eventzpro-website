<?php
/**
 * Front controller — the only PHP file inside the web root.
 * All application code, content and storage live in ../app (outside public).
 *
 * Hostinger: upload the contents of /public into public_html and the /app folder
 * next to it, or set EVENTZPRO_APP_ROOT to the absolute path of the app folder.
 */
declare(strict_types=1);

// PHP built-in server (php -S): let it serve existing static files directly.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_file($file)) {
        return false;
    }
}

$appRoot = getenv('EVENTZPRO_APP_ROOT') ?: null;
if (!$appRoot) {
    foreach ([dirname(__DIR__) . '/app', dirname(__DIR__) . '/eventzpro-app'] as $candidate) {
        if (is_file($candidate . '/bootstrap.php')) {
            $appRoot = $candidate;
            break;
        }
    }
}
if (!$appRoot) {
    http_response_code(500);
    exit('Application folder not found.');
}

if (!getenv('PUBLIC_ROOT')) {
    putenv('PUBLIC_ROOT=' . __DIR__);
}

require $appRoot . '/bootstrap.php';
run();
