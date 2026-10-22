<?php
/** Admin shell: sidebar + top bar. @var string $title @var string $content @var array $me @var ?array $flash @var int $newCount */
$path = request_path();
$nav = [
    ['path' => 'admin',          'label' => 'Dashboard', 'icon' => 'grid',  'exact' => true],
    ['path' => 'admin/messages', 'label' => 'Enquiries', 'icon' => 'inbox', 'count' => $newCount],
];
$general = [];
if ($me['role'] === 'admin') {
    $general[] = ['path' => 'admin/users', 'label' => 'Users', 'icon' => 'users'];
}
$general[] = ['path' => 'admin/account', 'label' => 'My account', 'icon' => 'user'];

$isActive = static fn (array $item): bool => !empty($item['exact']) ? $path === $item['path'] : ($path === $item['path'] || str_starts_with($path, $item['path'] . '/'));
$link = static function (array $item) use ($isActive): string {
    $active = $isActive($item);
    $count = !empty($item['count']) ? '<span class="side-count" aria-label="' . e($item['count']) . ' new">' . e($item['count']) . '</span>' : '';
    return '<a class="side-link' . ($active ? ' is-active' : '') . '" href="' . e(url($item['path'])) . '"' . ($active ? ' aria-current="page"' : '') . '>'
        . icon($item['icon']) . '<span>' . e($item['label']) . '</span>' . $count . '</a>';
};
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
    <script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</head>
<body class="admin">
<?= partial('icons') ?>
<a class="skip-link" href="#admin-main">Skip to content</a>
<div class="shell">
    <aside class="sidebar" id="sidebar" data-sidebar>
        <a class="sidebar-brand" href="<?= e(url('admin')) ?>">
            <img src="<?= e(img('brand/logo-compact-white.png')) ?>" alt="Eventzpro Consult" width="407" height="140">
            <span>Admin</span>
        </a>
        <nav class="side-nav" aria-label="Admin">
            <?php foreach ($nav as $item): ?><?= $link($item) ?><?php endforeach; ?>
            <p class="side-label">General</p>
            <?php foreach ($general as $item): ?><?= $link($item) ?><?php endforeach; ?>
            <p class="side-label">Website</p>
            <a class="side-link" href="<?= e(url()) ?>" target="_blank" rel="noopener"><?= icon('external') ?><span>View website</span></a>
            <a class="side-link" href="<?= e(url('contact')) ?>" target="_blank" rel="noopener"><?= icon('mail') ?><span>Contact page</span></a>
        </nav>
        <div class="sidebar-foot">
            <span class="avatar-chip" aria-hidden="true"><?= e(initials($me['name'])) ?></span>
            <span class="sidebar-user"><strong><?= e($me['name']) ?></strong><small><?= e(ADMIN_ROLES[$me['role']] ?? $me['role']) ?></small></span>
            <form method="post" action="<?= e(url('admin/logout')) ?>">
                <?= csrf_field() ?>
                <button class="icon-btn icon-btn--dark" type="submit" title="Sign out"><?= icon('logout') ?><span class="sr-only">Sign out</span></button>
            </form>
        </div>
    </aside>
    <div class="sidebar-scrim" data-sidebar-close></div>

    <div class="main">
        <header class="topbar">
            <button class="icon-btn menu-btn" type="button" aria-controls="sidebar" aria-expanded="false" data-sidebar-toggle><?= icon('menu') ?><span class="sr-only">Menu</span></button>
            <h1 class="topbar-title"><?= e($title) ?></h1>
            <div class="topbar-actions">
                <a class="icon-btn" href="<?= e(url('admin/messages?status=new')) ?>" title="New enquiries">
                    <?= icon('bell') ?><?php if ($newCount): ?><span class="dot" aria-hidden="true"></span><?php endif; ?>
                    <span class="sr-only"><?= e($newCount) ?> new enquiries</span>
                </a>
                <a class="avatar-chip avatar-chip--amber" href="<?= e(url('admin/account')) ?>" title="My account"><?= e(initials($me['name'])) ?></a>
            </div>
        </header>

        <main class="content" id="admin-main" tabindex="-1">
            <?php if ($flash): ?>
                <div class="flash flash--<?= e($flash['type']) ?>" role="status"><?= e($flash['text']) ?></div>
            <?php endif; ?>
            <?= $content ?>
        </main>
    </div>
</div>
</body>
</html>
