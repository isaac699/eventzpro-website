<?php
/** Sign-in / setup layout: brand panel + form card. @var string $title @var string $content @var ?array $flash */
?><!doctype html>
<html lang="en-GH">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> · Eventzpro Admin</title>
    <link rel="icon" href="<?= e(asset('img/brand/favicon-32.png')) ?>" type="image/png" sizes="32x32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin admin--auth">
<?= partial('icons') ?>
<div class="auth">
    <aside class="auth-brand">
        <img src="<?= e(img('brand/logo-white.png')) ?>" alt="Eventzpro Consult" width="431" height="220">
        <div>
            <p class="auth-brand-title">Enquiries, handled.</p>
            <p>Every message sent through the website lands here, so the team can read, reply and follow up in one place.</p>
        </div>
        <a href="<?= e(url()) ?>"><?= icon('arrow-right') ?> Back to the website</a>
    </aside>
    <main class="auth-main">
        <div class="auth-card">
            <?php if ($flash): ?>
                <div class="flash flash--<?= e($flash['type']) ?>" role="status"><?= e($flash['text']) ?></div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </main>
</div>
</body>
</html>
