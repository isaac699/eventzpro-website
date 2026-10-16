<?php
$ventures = content('ventures');
[$cassava, $hotel, $newmont, $fmcg] = $ventures;
$maxRevenue = max(array_column($cassava['products'], 'revenue'));
?>
<?= partial('page-hero', [
    'eyebrow' => 'Ongoing own projects',
    'title'   => 'Our strategic business units',
    'lead'    => 'We put our own capital and expertise to work in green agro-processing, hospitality, construction and distribution.',
    'crumbs'  => ['Business Units' => null],
    'image'   => 'ventures/cassava-factory-render.webp',
]) ?>

<nav class="subnav" aria-label="Business units">
    <div class="container subnav-inner">
        <?php foreach ($ventures as $v): ?>
            <a href="#<?= e($v['slug']) ?>"><span><?= e($v['letter']) ?></span><?= e($v['title']) ?></a>
        <?php endforeach; ?>
    </div>
</nav>

<!-- A. CASSAVA -->
<section class="section" id="<?= e($cassava['slug']) ?>">
    <div class="container">
        <div class="split split--top">
            <div class="split-copy reveal">
                <p class="eyebrow">Unit <?= e($cassava['letter']) ?> · <?= e($cassava['subtitle']) ?></p>
                <h2 class="section-title"><?= e($cassava['title']) ?></h2>
                <p class="lead"><?= e($cassava['summary']) ?></p>
                <p class="mantra"><?= icon('sun') ?> Mantra: <strong><?= e($cassava['mantra']) ?></strong></p>
                <h3 class="detail-heading">Strategic goals</h3>
                <ol class="goal-list">
                    <?php foreach ($cassava['goals'] as $g): ?><li><?= e($g) ?></li><?php endforeach; ?>
                </ol>
            </div>
            <div class="split-media reveal">
                <img class="rounded-media" src="<?= e(img($cassava['image'])) ?>" alt="Architectural render of the automated cassava factory" loading="lazy" width="930" height="627">
                <div class="mini-gallery">
                    <?php foreach ($cassava['gallery'] as $g): ?><img src="<?= e(img($g)) ?>" alt="" loading="lazy"><?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="impact-grid">
            <?php foreach ($cassava['impact'] as $i): ?>
                <div class="impact reveal">
                    <p class="impact-value"><?= e($i['value']) ?></p>
                    <p class="impact-label"><?= e($i['label']) ?></p>
                    <p class="impact-note"><?= e($i['note']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="products reveal">
            <div class="products-head">
                <h3 class="detail-heading">Product lines &amp; projected annual revenue</h3>
                <p>USD, with projected gross margin</p>
            </div>
            <ul class="bars">
                <?php foreach ($cassava['products'] as $p): ?>
                    <li class="bar">
                        <div class="bar-label">
                            <strong><?= e($p['name']) ?></strong>
                            <small><?= e($p['text']) ?></small>
                        </div>
                        <div class="bar-track" aria-hidden="true">
                            <span class="bar-fill" data-width="<?= e(round($p['revenue'] / $maxRevenue * 100)) ?>"></span>
                        </div>
                        <div class="bar-value">
                            <strong><?= e($p['revenue_label'] ?? '$' . $p['revenue'] . 'M') ?></strong>
                            <small><?= e($p['margin_label'] ?? $p['margin'] . '%') ?> margin</small>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="hubs reveal">
            <?php foreach ($cassava['hubs'] as $plant => $towns): ?>
                <div class="hub">
                    <h3><?= icon('pin') ?><?= e($plant) ?></h3>
                    <p>Sourcing catchment</p>
                    <ul class="tag-list tag-list--soft">
                        <?php foreach ($towns as $t): ?><li><?= e($t) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- B. HOTEL -->
<section class="section section--soft" id="<?= e($hotel['slug']) ?>">
    <div class="container split">
        <div class="split-media reveal">
            <img class="rounded-media" src="<?= e(img($hotel['image'])) ?>" alt="Architectural render of the 3-star hotel at Duayaw Nkwanta" loading="lazy" width="930" height="657">
        </div>
        <div class="split-copy reveal">
            <p class="eyebrow">Unit <?= e($hotel['letter']) ?> · <?= e($hotel['subtitle']) ?></p>
            <h2 class="section-title"><?= e($hotel['title']) ?></h2>
            <p class="lead"><?= e($hotel['summary']) ?></p>
            <div class="phase-grid">
                <?php foreach ($hotel['phases'] as $phase): ?>
                    <div class="phase">
                        <h3><?= e($phase['title']) ?></h3>
                        <ul class="checklist">
                            <?php foreach ($phase['items'] as $item): ?><li><?= icon('check') ?><?= e($item) ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- C. NEWMONT -->
<section class="section section--dark" id="<?= e($newmont['slug']) ?>">
    <div class="hex-field hex-field--faint" aria-hidden="true"></div>
    <div class="container split">
        <div class="split-copy reveal">
            <p class="eyebrow eyebrow--light">Unit <?= e($newmont['letter']) ?> · <?= e($newmont['subtitle']) ?></p>
            <h2 class="section-title section-title--light"><?= e($newmont['title']) ?></h2>
            <p class="lead"><?= e($newmont['summary']) ?></p>
            <ul class="checklist checklist--light">
                <?php foreach ($newmont['scope'] as $s): ?><li><?= icon('check') ?><?= e($s) ?></li><?php endforeach; ?>
            </ul>
        </div>
        <div class="split-media reveal">
            <div class="photo-mosaic">
                <img src="<?= e(img($newmont['image'])) ?>" alt="Resettlement house under construction" loading="lazy">
                <?php foreach ($newmont['gallery'] as $g): ?><img src="<?= e(img($g)) ?>" alt="" loading="lazy"><?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- D. FMCG -->
<section class="section" id="<?= e($fmcg['slug']) ?>">
    <div class="container split">
        <div class="split-media reveal">
            <div class="photo-mosaic photo-mosaic--three">
                <img src="<?= e(img($fmcg['image'])) ?>" alt="Beverage range distributed by Eventzpro" loading="lazy">
                <?php foreach ($fmcg['gallery'] as $g): ?><img src="<?= e(img($g)) ?>" alt="" loading="lazy"><?php endforeach; ?>
            </div>
        </div>
        <div class="split-copy reveal">
            <p class="eyebrow">Unit <?= e($fmcg['letter']) ?> · <?= e($fmcg['subtitle']) ?></p>
            <h2 class="section-title"><?= e($fmcg['title']) ?></h2>
            <p class="lead"><?= e($fmcg['summary']) ?></p>
            <ul class="checklist">
                <?php foreach ($fmcg['scope'] as $s): ?><li><?= icon('check') ?><?= e($s) ?></li><?php endforeach; ?>
            </ul>
            <a class="btn btn--dark" href="<?= e(url('contact?topic=' . rawurlencode('Partnership / investment in our business units'))) ?>">Partner or invest with us <?= icon('arrow-right') ?></a>
        </div>
    </div>
</section>

<?= partial('cta', ['title' => 'Interested in partnering on our business units?', 'text' => 'We welcome off-takers, investors, financiers and technical partners. Let\'s talk about where we can grow together.']) ?>
