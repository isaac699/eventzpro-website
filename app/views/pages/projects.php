<?php
$projects = content('projects');
$clients = content('clients');
?>
<?= partial('page-hero', [
    'eyebrow' => 'Projects & clients',
    'title'   => 'Work that builds industries, jobs and livelihoods',
    'lead'    => 'A selection of consulting assignments for development partners, multinationals and Ghanaian enterprises.',
    'crumbs'  => ['Projects' => null],
    'image'   => 'projects/buntaa-poultry-house.webp',
]) ?>

<section class="section">
    <div class="container">
        <div class="filters" role="toolbar" aria-label="Filter projects" data-filters>
            <button class="filter is-active" type="button" data-filter="all" aria-pressed="true">All projects</button>
            <?php foreach ($clients['tags'] as $key => $label): ?>
                <button class="filter" type="button" data-filter="<?= e($key) ?>" aria-pressed="false"><?= e($label) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="case-list" data-filter-list>
            <?php foreach ($projects as $p): ?>
                <article class="case reveal" id="<?= e($p['slug']) ?>" data-tags="<?= e(implode(' ', $p['tags'])) ?>">
                    <div class="case-media">
                        <img src="<?= e(img($p['image'])) ?>" alt="<?= e($p['client'] . ': ' . $p['title']) ?>" loading="lazy">
                        <?php if ($p['gallery']): ?>
                            <div class="case-thumbs">
                                <?php foreach (array_slice($p['gallery'], 0, 3) as $g): ?>
                                    <img src="<?= e(img($g)) ?>" alt="" loading="lazy">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="case-body">
                        <ul class="tag-list">
                            <?php foreach ($p['tags'] as $t): ?><li><?= e($clients['tags'][$t]) ?></li><?php endforeach; ?>
                        </ul>
                        <p class="case-client"><?= e($p['client']) ?></p>
                        <h2 class="case-title"><?= e($p['title']) ?></h2>
                        <dl class="case-meta">
                            <div><dt><?= icon('pin') ?>Location</dt><dd><?= e($p['location']) ?></dd></div>
                            <div><dt><?= icon('briefcase') ?>Our role</dt><dd><?= e($p['role']) ?></dd></div>
                        </dl>
                        <p><?= e($p['summary']) ?></p>
                        <ul class="checklist">
                            <?php foreach ($p['highlights'] as $h): ?><li><?= icon('check') ?><?= e($h) ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                </article>
            <?php endforeach; ?>
            <p class="filters-empty" hidden data-filter-empty>No projects in this category yet.</p>
        </div>
    </div>
</section>

<section class="section section--soft">
    <div class="container split split--top">
        <div class="split-copy reveal">
            <p class="eyebrow">International consultancy</p>
            <h2 class="section-title">Development partners &amp; multinationals</h2>
            <p class="lead">From the World Bank Group to USAID, Eni and Fairtrade Africa, international clients trust us with programmes that must work in the field.</p>
        </div>
        <ol class="timeline reveal">
            <?php foreach ($clients['international'] as $c): ?>
                <li class="timeline-item">
                    <span class="timeline-period"><?= e($c['period']) ?></span>
                    <h3 class="timeline-title"><?= e($c['name']) ?></h3>
                    <p><?= e($c['work']) ?></p>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head section-head--center">
            <p class="eyebrow">Client roster</p>
            <h2 class="section-title">Category of business service &amp; our clientele</h2>
        </div>
        <div class="roster">
            <?php foreach ($clients['roster'] as $category => $names): ?>
                <section class="roster-group reveal">
                    <h3 class="roster-title"><?= e($category) ?> <span><?= count($names) ?></span></h3>
                    <ul class="roster-list">
                        <?php foreach ($names as $n): ?><li><?= e($n) ?></li><?php endforeach; ?>
                    </ul>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?= partial('cta', ['title' => 'Could your project be our next success story?']) ?>
