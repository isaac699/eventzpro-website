<?php
$company = content('company');
$services = content('services');
$nav = [
    ['path' => '',           'label' => 'Home'],
    ['path' => 'about',      'label' => 'About'],
    ['path' => 'services',   'label' => 'Services', 'mega' => true],
    ['path' => 'projects',   'label' => 'Projects'],
    ['path' => 'ventures',   'label' => 'Business Units'],
    ['path' => 'leadership', 'label' => 'Leadership'],
];
?>
<div class="topbar">
    <div class="container topbar-inner">
        <p class="topbar-tag">Since <?= e($company['founded']) ?> · <?= e($company['tagline']) ?></p>
        <ul class="topbar-links">
            <li><a href="tel:<?= e($company['phones'][0]['tel']) ?>"><?= icon('phone') ?><?= e($company['phones'][0]['label']) ?></a></li>
            <li><a href="mailto:<?= e($company['email']) ?>"><?= icon('mail') ?><?= e($company['email']) ?></a></li>
            <li><a href="<?= e(asset('docs/eventzpro-company-profile.pdf')) ?>" download><?= icon('download') ?>Company profile</a></li>
        </ul>
    </div>
</div>

<header class="site-header" data-header>
    <div class="container site-header-inner">
        <a class="brand" href="<?= e(url()) ?>" aria-label="<?= e($company['name']) ?>, home">
            <img src="<?= e(img('brand/logo-compact.png')) ?>" alt="<?= e($company['name']) ?>" width="204" height="70">
        </a>

        <nav class="nav" id="site-nav" aria-label="Main" data-nav>
            <ul class="nav-list">
                <?php foreach ($nav as $item): $active = is_active($item['path']); ?>
                    <li class="nav-item<?= !empty($item['mega']) ? ' has-mega' : '' ?>">
                        <a class="nav-link<?= $active ? ' is-active' : '' ?>" href="<?= e(url($item['path'])) ?>"<?= $active ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a>
                        <?php if (!empty($item['mega'])): ?>
                            <button class="nav-expand" type="button" aria-expanded="false" aria-controls="mega-services" data-mega-toggle>
                                <?= icon('chevron-down') ?><span class="sr-only">Show services</span>
                            </button>
                            <div class="mega" id="mega-services">
                                <div class="mega-grid">
                                    <?php foreach ($services as $s): ?>
                                        <a class="mega-item" href="<?= e(url('services/' . $s['slug'])) ?>">
                                            <span class="mega-icon"><?= icon($s['icon']) ?></span>
                                            <span><strong><?= e($s['short']) ?></strong><small><?= e($s['summary']) ?></small></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mega-aside">
                                    <p class="eyebrow eyebrow--light">Not sure where to start?</p>
                                    <p>Tell us about your idea, project or organisation and we'll recommend the right engagement.</p>
                                    <a class="btn btn--primary btn--sm" href="<?= e(url('contact')) ?>">Talk to a consultant <?= icon('arrow-right') ?></a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="nav-mobile-extra">
                <a class="btn btn--primary btn--block" href="<?= e(url('contact')) ?>">Get in touch <?= icon('arrow-right') ?></a>
                <a class="nav-mobile-contact" href="tel:<?= e($company['phones'][0]['tel']) ?>"><?= icon('phone') ?><?= e($company['phones'][0]['label']) ?></a>
                <a class="nav-mobile-contact" href="mailto:<?= e($company['email']) ?>"><?= icon('mail') ?><?= e($company['email']) ?></a>
            </div>
        </nav>

        <div class="site-header-actions">
            <a class="btn btn--dark btn--sm site-header-cta" href="<?= e(url('contact')) ?>">Get in touch <?= icon('arrow-right') ?></a>
            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
                <span class="nav-toggle-open"><?= icon('menu') ?></span>
                <span class="nav-toggle-close"><?= icon('close') ?></span>
                <span class="sr-only">Menu</span>
            </button>
        </div>
    </div>
</header>
