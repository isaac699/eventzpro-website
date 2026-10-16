<?php
/**
 * Inner-page hero.
 * @var string $eyebrow @var string $title @var string $lead @var array $crumbs [label => path] @var ?string $image
 */
$image ??= null;
$crumbs ??= [];
?>
<section class="page-hero<?= $image ? ' page-hero--image' : '' ?>">
    <?php if ($image): ?>
        <img class="page-hero-bg" src="<?= e(img($image)) ?>" alt="" fetchpriority="high">
    <?php endif; ?>
    <div class="hex-field" aria-hidden="true"></div>
    <div class="container page-hero-inner">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="<?= e(url()) ?>">Home</a></li>
                <?php foreach ($crumbs as $label => $path): ?>
                    <li><?= $path === null ? '<span aria-current="page">' . e($label) . '</span>' : '<a href="' . e(url($path)) . '">' . e($label) . '</a>' ?></li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <p class="eyebrow eyebrow--light"><?= e($eyebrow) ?></p>
        <h1 class="page-hero-title"><?= e($title) ?></h1>
        <?php if (!empty($lead)): ?><p class="page-hero-lead"><?= e($lead) ?></p><?php endif; ?>
    </div>
</section>
