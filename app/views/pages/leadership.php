<?php $team = content('team'); ?>
<?= partial('page-hero', [
    'eyebrow' => 'Leadership',
    'title'   => 'Board of Directors',
    'lead'    => 'Finance, strategy, HR, logistics and supply-chain leaders who bring decades of combined experience to every client engagement.',
    'crumbs'  => ['Leadership' => null],
]) ?>

<section class="section">
    <div class="container">
        <div class="team-grid team-grid--overview">
            <?php foreach ($team as $m): ?>
                <a class="team-card reveal" href="#<?= e($m['slug']) ?>">
                    <?= partial('avatar', ['member' => $m]) ?>
                    <span class="team-card-body">
                        <strong><?= e($m['name']) ?></strong>
                        <small><?= e($m['role']) ?></small>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--soft">
    <div class="container profiles">
        <?php foreach ($team as $i => $m): ?>
            <article class="profile reveal" id="<?= e($m['slug']) ?>">
                <div class="profile-media"><?= partial('avatar', ['member' => $m]) ?></div>
                <div class="profile-body">
                    <p class="eyebrow"><?= e($m['role']) ?></p>
                    <h2 class="profile-name"><?= e($m['name']) ?></h2>
                    <p class="profile-tags"><?= e($m['tags']) ?></p>
                    <?php foreach ($m['bio'] as $para): ?><p><?= e($para) ?></p><?php endforeach; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?= partial('cta') ?>
