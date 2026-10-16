<?php
/**
 * Static export for the GitHub Pages review preview.
 *
 * Renders every route through the real PHP app and writes plain HTML, so reviewers
 * can browse the site without a PHP server. The contact form is disabled in this build.
 *
 * Usage:  php scripts/export-static.php [output-dir]
 * Env:    APP_BASE_PATH  sub-path the preview is served from, e.g. /eventzpro-website
 *         APP_URL        public preview URL, used for canonical/Open Graph links
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("CLI only\n");
}

$root = dirname(__DIR__);
$out = rtrim($argv[1] ?? $root . '/dist', '/\\');
$base = rtrim((string) getenv('APP_BASE_PATH'), '/');
if ($base !== '' && !preg_match('#^/[A-Za-z0-9._/-]+$#', $base)) {
    fwrite(STDERR, "APP_BASE_PATH must look like /repo-name, got: {$base}\n");
    exit(1);
}

putenv('STATIC_EXPORT=true');
putenv('APP_ENV=production');
putenv('MAIL_ENABLED=false');
putenv('PUBLIC_ROOT=' . $root . '/public');
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = $base . '/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';

require $root . '/app/bootstrap.php';

$routes = ['', 'about', 'services', 'projects', 'ventures', 'leadership', 'contact', 'privacy'];
foreach (content('services') as $service) {
    $routes[] = 'services/' . $service['slug'];
}

/** Render one route through the router and return its output. */
$renderRoute = static function (string $path) use ($base): string {
    $_SERVER['REQUEST_URI'] = $base . '/' . $path;
    $_GET = [];
    ob_start();
    run();
    return (string) ob_get_clean();
};

$write = static function (string $file, string $contents): void {
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0755, true);
    }
    file_put_contents($file, $contents);
};

$copyDir = static function (string $from, string $to) use (&$copyDir): void {
    foreach (scandir($from) ?: [] as $item) {
        if ($item === '.' || $item === '..' || $item[0] === '.' || str_ends_with($item, '.map')) {
            continue;
        }
        $src = $from . '/' . $item;
        $dest = $to . '/' . $item;
        if (is_dir($src)) {
            $copyDir($src, $dest);
        } else {
            if (!is_dir($to)) {
                mkdir($to, 0755, true);
            }
            copy($src, $dest);
        }
    }
};

if (is_dir($out)) {
    fwrite(STDERR, "Output folder exists, files will be overwritten: {$out}\n");
}

foreach ($routes as $route) {
    $file = $out . '/' . ($route === '' ? '' : $route . '/') . 'index.html';
    $write($file, $renderRoute($route));
    echo 'page  /' . $route . PHP_EOL;
}

$write($out . '/404.html', $renderRoute('__not-found__'));
$write($out . '/sitemap.xml', $renderRoute('sitemap.xml'));
// Keep the review copy out of search engines.
$write($out . '/robots.txt', "User-agent: *\nDisallow: /\n");
// Serve files as-is on GitHub Pages (no Jekyll processing).
$write($out . '/.nojekyll', '');

$copyDir($root . '/public/assets', $out . '/assets');
copy($root . '/public/favicon.ico', $out . '/favicon.ico');

echo "Static preview written to {$out}\n";
