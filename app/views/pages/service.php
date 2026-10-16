<?php
/** @var array $service */
$company = content('company');
$all = content('services');
$related = array_values(array_filter(array_map(
    static fn ($slug) => find_by_slug(content('projects'), $slug),
    $service['projects']
)));
?>
<?= partial('page-hero', [
    'eyebrow' => 'Practice area ' . $service['number'],
    'title'   => $service['title'],
    'lead'    => $service['summary'],
    'crumbs'  => ['Services' => 'services', $service['short'] => null],
    'image'   => $service['image'],
]) ?>

<section class="section">
    <div class="container detail">
        <div class="detail-main">
            <p class="lead lead--xl reveal"><?= e($service['intro']) ?></p>

            <h2 class="detail-heading">What we deliver</h2>
            <div class="offer-grid">
                <?php foreach ($service['offerings'] as $i => $o): ?>
                    <article class="offer reveal">
                        <span class="offer-num"><?= sprintf('%02d', $i + 1) ?></span>
                        <h3><?= e($o['title']) ?></h3>
                        <p><?= e($o['text']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($service['clients']): ?>
                <h2 class="detail-heading">Selected clients</h2>
                <ul class="client-list reveal">
                    <?php foreach ($service['clients'] as $c): ?>
                        <li><?= icon('check') ?><?= e($c) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ($related): ?>
                <h2 class="detail-heading">Related projects</h2>
                <div class="project-grid project-grid--compact">
                    <?php foreach ($related as $p): ?>
                        <article class="project-card reveal">
                            <img src="<?= e(img($p['image'])) ?>" alt="<?= e($p['client']) ?>" loading="lazy">
                            <div class="project-card-body">
                                <p class="project-card-client"><?= e($p['client']) ?></p>
                                <h3 class="project-card-title"><?= e($p['title']) ?></h3>
                                <p class="project-card-meta"><?= icon('pin') ?><?= e($p['location']) ?></p>
                            </div>
                            <a class="project-card-link" href="<?= e(url('projects#' . $p['slug'])) ?>" aria-label="Read about <?= e($p['client']) ?>"><?= icon('arrow-up-right') ?></a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside class="detail-aside">
            <div class="aside-card">
                <h2 class="aside-card-title">All practice areas</h2>
                <ul class="aside-nav">
                    <?php foreach ($all as $s): ?>
                        <li><a href="<?= e(url('services/' . $s['slug'])) ?>"<?= $s['slug'] === $service['slug'] ? ' aria-current="page"' : '' ?>><?= icon($s['icon']) ?><?= e($s['short']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="aside-card aside-card--dark">
                <p class="eyebrow eyebrow--light">Talk to us</p>
                <h2 class="aside-card-title">Discuss your <?= e(strtolower($service['short'])) ?> needs</h2>
                <a class="btn btn--primary btn--block" href="<?= e(url('contact?topic=' . rawurlencode($service['title']))) ?>">Send an enquiry <?= icon('arrow-right') ?></a>
                <a class="aside-card-line" href="tel:<?= e($company['phones'][0]['tel']) ?>"><?= icon('phone') ?><?= e($company['phones'][0]['label']) ?></a>
                <a class="aside-card-line" href="mailto:<?= e($company['email']) ?>"><?= icon('mail') ?><?= e($company['email']) ?></a>
            </div>
            <a class="aside-download" href="<?= e(asset('docs/eventzpro-company-profile.pdf')) ?>" download>
                <?= icon('file') ?><span><strong>Company profile</strong><small>PDF · 3.3 MB</small></span><?= icon('download') ?>
            </a>
        </aside>
    </div>
</section>

<?= partial('cta') ?>
