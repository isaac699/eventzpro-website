<?php
declare(strict_types=1);

/** Dispatch the current request to a page. */
function run(): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = request_path();

    // Canonical URLs: no trailing slash, no index.php.
    $uriPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if ($method === 'GET' && $path !== '' && (str_ends_with($uriPath, '/') || str_ends_with($path, 'index.php'))) {
        redirect(preg_replace('#/?index\.php$#', '', $path) ?? '', 301);
    }

    if (!in_array($method, ['GET', 'HEAD', 'POST'], true)) {
        http_response_code(405);
        header('Allow: GET, HEAD, POST');
        exit;
    }

    $simplePages = [
        ''           => 'home',
        'about'      => 'about',
        'services'   => 'services',
        'projects'   => 'projects',
        'ventures'   => 'ventures',
        'leadership' => 'leadership',
        'privacy'    => 'privacy',
    ];

    if ($path === 'admin' || str_starts_with($path, 'admin/')) {
        admin_dispatch($method, $path);
        return;
    }

    if ($path === 'contact') {
        echo $method === 'POST' ? handle_contact_post() : render_contact();
        return;
    }

    if ($method === 'POST') {
        http_response_code(405);
        header('Allow: GET, HEAD');
        exit;
    }

    if (isset($simplePages[$path])) {
        echo render($simplePages[$path], ['meta' => page_meta($simplePages[$path])]);
        return;
    }

    if (preg_match('#^services/([a-z0-9-]+)$#', $path, $m)) {
        $service = find_by_slug(content('services'), $m[1]);
        if ($service) {
            echo render('service', [
                'service' => $service,
                'meta'    => ['title' => $service['title'], 'description' => $service['summary'], 'image' => $service['image']],
            ]);
            return;
        }
    }

    if ($path === 'sitemap.xml') {
        send_sitemap();
        return;
    }

    http_response_code(404);
    echo render('404', ['meta' => ['title' => 'Page not found']]);
}

function page_meta(string $page): array
{
    return [
        'home'       => ['title' => null],
        'about'      => ['title' => 'About Us', 'description' => 'Since 2009, Eventzpro Consult Ltd. has helped enterprises, investors and development partners across Ghana build sustainable, bankable businesses.'],
        'services'   => ['title' => 'Services', 'description' => 'Organizational development, EPC project management, agribusiness, commodity supply, mining services and oil & gas consultancy from Eventzpro Consult.'],
        'projects'   => ['title' => 'Projects & Clients', 'description' => 'Selected consulting assignments for the World Bank Group, USAID, Eni, WEDDI Africa, Buntaa Farms, Bodukwan, Angel Group and more.'],
        'ventures'   => ['title' => 'Business Units', 'description' => 'Eventzpro\'s own ventures: a green cassava processing SBU, a 3-star hotel in the Ahafo Region, Newmont resettlement contracts and FMCG distribution.'],
        'leadership' => ['title' => 'Leadership', 'description' => 'Meet the leadership team of Eventzpro Consult Ltd.'],
        'privacy'    => ['title' => 'Privacy Notice', 'description' => 'How Eventzpro Consult Ltd. handles information submitted through this website.'],
    ][$page] ?? [];
}

function send_sitemap(): void
{
    header('Content-Type: application/xml; charset=UTF-8');
    $paths = ['', 'about', 'services', 'projects', 'ventures', 'leadership', 'contact', 'privacy'];
    foreach (content('services') as $service) {
        $paths[] = 'services/' . $service['slug'];
    }
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($paths as $p) {
        echo '  <url><loc>' . e(absolute_url($p)) . '</loc></url>' . "\n";
    }
    echo '</urlset>';
}
