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
        'enabled' => env('MAIL_ENABLED', 'true') === 'true',
        'to'      => env('MAIL_TO', 'info@eventzproconsult.com'),
        'from'    => env('MAIL_FROM', 'no-reply@eventzproconsult.com'),
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
