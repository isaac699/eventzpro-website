<?php
declare(strict_types=1);

/** Read a config value using dot notation, e.g. config('mail.to'). */
function config(string $key, mixed $default = null): mixed
{
    static $config = null;
    $config ??= require APP_ROOT . '/config/app.php';

    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

/** Load a content file from app/data (cached per request). */
function content(string $name): array
{
    static $cache = [];
    if (!preg_match('/^[a-z0-9_-]+$/', $name)) {
        throw new InvalidArgumentException('Invalid content name');
    }
    return $cache[$name] ??= require APP_ROOT . "/data/{$name}.php";
}

/** HTML-escape for output. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL prefix when the site runs in a sub-folder (e.g. /Eventzpro-Website on XAMPP). */
function base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $forced = config('base_path');
    if (is_string($forced) && $forced !== '') {
        return $base = '/' . trim($forced, '/');
    }
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $uri = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    // Root .htaccess rewrites into /public — hide that segment from generated URLs.
    if (str_ends_with($dir, '/public') && !str_starts_with($uri, $dir . '/')) {
        $dir = substr($dir, 0, -strlen('/public'));
    }
    return $base = $dir;
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return base_path() . '/' . $path;
}

/** Absolute URL for canonical / Open Graph tags. */
function absolute_url(string $path = ''): string
{
    $root = (string) config('url');
    if ($root === '') {
        $scheme = is_https() ? 'https' : 'http';
        $host = preg_replace('/[^A-Za-z0-9.\-:]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
        $root = $scheme . '://' . $host . base_path();
    }
    return rtrim($root, '/') . '/' . ltrim($path, '/');
}

/** Versioned asset URL for cache busting. */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $file = dirname(APP_ROOT) . '/public/assets/' . $path;
    $publicRoot = env('PUBLIC_ROOT');
    if ($publicRoot) {
        $file = rtrim($publicRoot, '/\\') . '/assets/' . $path;
    }
    $version = is_file($file) ? '?v=' . filemtime($file) : '';
    return url('assets/' . $path) . $version;
}

function img(string $path): string
{
    return url('assets/img/' . ltrim($path, '/'));
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443
        || (config('trust_proxy') && ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

/** Current route path without base or slashes, e.g. "services/agribusiness". */
function request_path(): string
{
    $uri = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $uri = rawurldecode($uri);
    $base = base_path();
    if ($base !== '' && str_starts_with($uri, $base)) {
        $uri = substr($uri, strlen($base));
    }
    return trim($uri, '/');
}

function is_active(string $section): bool
{
    $path = request_path();
    return $section === '' ? $path === '' : ($path === $section || str_starts_with($path, $section . '/'));
}

function redirect(string $path, int $status = 303): never
{
    header('Location: ' . url($path), true, $status);
    exit;
}

function years_in_business(): int
{
    return (int) date('Y') - (int) content('company')['founded'];
}

/** Inline SVG icon from the sprite in views/partials/icons.php. */
function icon(string $name, string $class = 'icon'): string
{
    return '<svg class="' . e($class) . '" aria-hidden="true" focusable="false"><use href="#i-' . e($name) . '"></use></svg>';
}

/** Render a partial template with variables. */
function partial(string $name, array $vars = []): string
{
    $file = APP_ROOT . '/views/partials/' . $name . '.php';
    return capture($file, $vars);
}

/** Render a page template inside the main layout. */
function render(string $page, array $vars = []): string
{
    $vars['meta'] = array_merge([
        'title'       => null,
        'description' => content('company')['description'],
        'image'       => 'brand/og-image.jpg',
        'path'        => request_path(),
        'body_class'  => '',
    ], $vars['meta'] ?? []);

    $content = capture(APP_ROOT . '/views/pages/' . $page . '.php', $vars);
    return capture(APP_ROOT . '/views/layouts/main.php', $vars + ['content' => $content]);
}

function capture(string $__file, array $__vars): string
{
    if (!is_file($__file)) {
        throw new RuntimeException('View not found: ' . basename($__file));
    }
    extract($__vars, EXTR_SKIP);
    ob_start();
    try {
        require $__file;
    } catch (Throwable $t) {
        ob_end_clean();
        throw $t;
    }
    return (string) ob_get_clean();
}

/** Find an item in a content list by its slug. */
function find_by_slug(array $items, string $slug): ?array
{
    foreach ($items as $item) {
        if (($item['slug'] ?? null) === $slug) {
            return $item;
        }
    }
    return null;
}

/** Two-letter initials for leaders without a photo. */
function initials(string $name): string
{
    $parts = array_values(array_filter(explode(' ', preg_replace('/^(Dr|Mr|Mrs|Ms|Prof)\.?\s+/i', '', $name))));
    $first = $parts[0][0] ?? '';
    $last = count($parts) > 1 ? $parts[count($parts) - 1][0] : '';
    return strtoupper($first . $last);
}
