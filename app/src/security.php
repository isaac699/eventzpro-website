<?php
declare(strict_types=1);

/**
 * HTTP security headers. The site loads no inline scripts, so the Content
 * Security Policy can stay strict: only first-party scripts, Google Fonts and
 * the Google Maps embed on the contact page are allowed.
 */
function send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }
    header_remove('X-Powered-By');

    $directives = [
        "default-src 'self'",
        "script-src 'self'",
        "style-src 'self' https://fonts.googleapis.com",
        "font-src 'self' https://fonts.gstatic.com",
        "img-src 'self' data:",
        "frame-src https://www.google.com https://maps.google.com",
        "connect-src 'self'",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'none'",
    ];
    if (is_https()) {
        $directives[] = 'upgrade-insecure-requests';
    }
    $csp = implode('; ', $directives);

    header('Content-Security-Policy: ' . $csp);
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

/** Sessions are only started where needed (the contact form), keeping other pages cookie-free and cacheable. */
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('ezp_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => base_path() === '' ? '/' : base_path() . '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    start_secure_session();
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(?string $token): bool
{
    start_secure_session();
    return is_string($token) && !empty($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}

function client_ip(): string
{
    if (config('trust_proxy') && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = trim(explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

/**
 * File-based fixed-window rate limiter. Returns true when the action is allowed.
 * Keys are hashed so no raw IPs are written to disk.
 */
function rate_limit(string $bucket, int $max, int $window): bool
{
    $dir = STORAGE_PATH . '/ratelimit';
    $file = $dir . '/' . hash('sha256', $bucket . '|' . client_ip()) . '.json';
    $now = time();

    $fp = @fopen($file, 'c+');
    if ($fp === false) {
        return true; // fail open rather than block genuine enquiries
    }
    flock($fp, LOCK_EX);
    $state = json_decode((string) stream_get_contents($fp), true) ?: ['start' => $now, 'count' => 0];
    if ($now - (int) $state['start'] >= $window) {
        $state = ['start' => $now, 'count' => 0];
    }
    $allowed = $state['count'] < $max;
    if ($allowed) {
        $state['count']++;
    }
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, (string) json_encode($state));
    flock($fp, LOCK_UN);
    fclose($fp);

    return $allowed;
}

/** Remove characters that could inject extra mail headers. */
function header_safe(string $value): string
{
    return trim(preg_replace('/[\r\n\t]+/', ' ', $value) ?? '');
}
