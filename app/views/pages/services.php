<?php
$services = content('services');
$od = $services[0];
?>
<?= partial('page-hero', [
    'eyebrow' => 'Services',
    'title'   => 'Expertise that carries projects from idea to impact',
    'lead'    => 'Six integrated practice areas spanning strategy, construction, agribusiness, trade, mining and energy, so one partner can see your project through.',
    'crumbs'  => ['Services' => null],
    'image'   => 'projects/weddi-sorting-line.webp',
]) ?>

<section class="section">
    <div class="container">
        <?php foreach ($services as $i => $s): ?>
            <article class="service-row reveal<?= $i % 2 ? ' service-row--flip' : '' ?>" id="<?= e($s['slug']) ?>">
                <div class="service-row-media">
                    <img src="<?= e(img($s['image'])) ?>" alt="<?= e($s['title']) ?>" loading="lazy">
                    <span class="service-row-num"><?= e($s['number']) ?></span>
                </div>
                <div class="service-row-copy">
                    <span class="service-card-icon"><?= icon($s['icon']) ?></span>
                    <h2 class="service-row-title"><?= e($s['title']) ?></h2>
                    <p class="lead"><?= e($s['intro']) ?></p>
                    <ul class="checklist">
                        <?php foreach (array_slice($s['offerings'], 0, 4) as $o): ?>
                            <li><?= icon('check') ?><?= e($o['title']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a class="btn btn--dark" href="<?= e(url('services/' . $s['slug'])) ?>">Explore <?= e($s['short']) ?> <?= icon('arrow-right') ?></a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section section--soft">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="eyebrow">Organizational development packages</p>
                <h2 class="section-title">Packaged services, tailored to your institution</h2>
            </div>
            <p class="section-head-text">We have packages for each of the services below. Our standardised training courses are fully treated within five days, with content customised to the pressing needs of the requesting institution.</p>
        </div>
        <div class="package-grid">
            <?php foreach ($od['offerings'] as $i => $o): ?>
                <article class="package reveal">
                    <span class="package-num"><?= sprintf('%02d', $i + 1) ?></span>
                    <h3><?= e($o['title']) ?></h3>
                    <p><?= e($o['text']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?= partial('cta', ['title' => 'Need a team that can plan it, fund it and build it?']) ?>
