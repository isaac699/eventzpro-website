<?php
/** @var array $meta @var string $content */
$company = content('company');
$pageTitle = $meta['title'] ? $meta['title'] . ' | ' . $company['name'] : $company['name'] . ' | ' . $company['tagline'];
$canonical = absolute_url($meta['path']);
$ogImage = absolute_url('assets/img/' . $meta['image']);
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'ProfessionalService',
    'name' => $company['name'],
    'slogan' => $company['tagline'],
    'description' => $company['description'],
    'url' => absolute_url(),
    'logo' => absolute_url('assets/img/brand/logo.png'),
    'image' => $ogImage,
    'email' => $company['email'],
    'telephone' => $company['phones'][0]['tel'],
    'foundingDate' => (string) $company['founded'],
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => implode(', ', $company['address']),
        'addressLocality' => 'Accra',
        'addressCountry' => 'GH',
    ],
    'areaServed' => ['Ghana', 'West Africa'],
];
?><!doctype html>
<html lang="en-GH">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($meta['description']) ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <meta name="theme-color" content="#282929">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e($company['name']) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($meta['description']) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="<?= e(url('favicon.ico')) ?>" sizes="any">
    <link rel="icon" href="<?= e(asset('img/brand/favicon-32.png')) ?>" type="image/png" sizes="32x32">
    <link rel="icon" href="<?= e(asset('img/brand/favicon-192.png')) ?>" type="image/png" sizes="192x192">
    <link rel="apple-touch-icon" href="<?= e(asset('img/brand/apple-touch-icon.png')) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?= e(asset('vendor/bootstrap/css/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>">
    <script src="<?= e(asset('js/head.js')) ?>"></script>
    <script src="<?= e(asset('vendor/bootstrap/js/bootstrap.bundle.min.js')) ?>" defer></script>
    <script src="<?= e(asset('js/main.js')) ?>" defer></script>
    <script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
</head>
<body class="<?= e($meta['body_class']) ?>">
<?= partial('icons') ?>
<a class="skip-link" href="#main">Skip to content</a>
<?= partial('header') ?>
<main id="main" tabindex="-1">
<?= $content ?>
</main>
<?= partial('footer') ?>
<button class="to-top" type="button" data-to-top aria-label="Back to top"><?= icon('arrow-up') ?></button>
</body>
</html>
