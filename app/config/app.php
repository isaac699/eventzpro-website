<?php
/**
 * Application settings. Values can be overridden per environment in app/.env
 * (copy app/.env.example). Never commit app/.env.
 */
declare(strict_types=1);

return [
    'env'       => env('APP_ENV', 'production'),          // production | local
    'debug'     => env('APP_DEBUG', 'false') === 'true',
    'url'       => rtrim(env('APP_URL', ''), '/'),           // e.g. https://www.eventzproconsult.com (used for canonical/OG URLs)
    'base_path' => env('APP_BASE_PATH', null),               // force a sub-folder base, e.g. /Eventzpro-Website ; auto-detected when empty
    'timezone'  => 'Africa/Accra',

    'mail' => [
        'enabled'    => env('MAIL_ENABLED', 'true') === 'true',
        'to'         => env('MAIL_TO', 'info@eventzproconsult.com'),
        'from'       => env('MAIL_FROM', 'no-reply@eventzproconsult.com'),
        'from_name'  => env('MAIL_FROM_NAME', 'Eventzpro Website'),
        'transport'  => env('MAIL_TRANSPORT', 'mail'),       // mail (PHP mail()) | smtp
        'host'       => env('MAIL_HOST', 'smtp.hostinger.com'),
        'port'       => (int) env('MAIL_PORT', '465'),
        'encryption' => env('MAIL_ENCRYPTION', 'ssl'),       // ssl (port 465) | tls (STARTTLS, port 587) | none
        'username'   => env('MAIL_USERNAME', ''),
        'password'   => env('MAIL_PASSWORD', ''),
    ],

    // SQLite needs no server (one file in app/storage/database); use mysql on Hostinger if preferred.
    'db' => [
        'driver'      => env('DB_CONNECTION', 'sqlite'),     // sqlite | mysql
        'sqlite_path' => env('DB_SQLITE_PATH', APP_ROOT . '/storage/database/eventzpro.sqlite'),
        'host'        => env('DB_HOST', '127.0.0.1'),
        'port'        => (int) env('DB_PORT', '3306'),
        'database'    => env('DB_DATABASE', 'eventzpro'),
        'username'    => env('DB_USERNAME', 'root'),
        'password'    => env('DB_PASSWORD', ''),
    ],

    'admin' => [
        'setup_token'   => env('ADMIN_SETUP_TOKEN', ''),     // required once, to create the first administrator at /admin/setup
        'idle_timeout'  => 3600,  // sign out after an hour without activity
        'login_max'     => 10,    // sign-in attempts…
        'login_window'  => 900,   // …per IP per 15 minutes
        'per_page'      => 20,
    ],

    'contact' => [
        'rate_limit_max'    => 5,     // submissions…
        'rate_limit_window' => 3600,  // …per IP per hour
        'min_fill_seconds'  => 4,     // forms submitted faster than this are treated as bots
    ],

    // Set to true only when the site sits behind a trusted proxy/CDN (e.g. Cloudflare)
    'trust_proxy' => env('TRUST_PROXY', 'false') === 'true',

    // Google Maps embed query for the contact page
    'map_query' => 'GT Bank Building, Westland Avenue, West Legon, Accra, Ghana',
];
