<?php
/**
 * Boots the application: environment, error handling, helpers and security headers.
 */
declare(strict_types=1);

define('APP_ROOT', __DIR__);
define('STORAGE_PATH', APP_ROOT . '/storage');

require APP_ROOT . '/src/env.php';
load_env(APP_ROOT . '/.env');

require APP_ROOT . '/src/helpers.php';
require APP_ROOT . '/src/security.php';
require APP_ROOT . '/src/db.php';
require APP_ROOT . '/src/mailer.php';
require APP_ROOT . '/src/contact.php';
require APP_ROOT . '/src/admin.php';
require APP_ROOT . '/src/router.php';

date_default_timezone_set((string) config('timezone'));

// Errors: never shown to visitors in production, always logged privately.
error_reporting(E_ALL);
ini_set('display_errors', config('debug') ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/php-error.log');
ini_set('expose_php', '0');

set_exception_handler(static function (Throwable $e): void {
    error_log('[' . date('c') . '] ' . $e);
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (config('debug')) {
        echo '<pre>' . e((string) $e) . '</pre>';
        return;
    }
    echo render('500', ['meta' => ['title' => 'Something went wrong']]);
});

send_security_headers();
