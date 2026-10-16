<?php /** @var array $member */ ?>
<span class="avatar<?= $member['photo'] ? '' : ' avatar--initials' ?>">
    <?php if ($member['photo']): ?>
        <img src="<?= e(img($member['photo'])) ?>" alt="Portrait of <?= e($member['name']) ?>" loading="lazy" width="800" height="960">
    <?php else: ?>
        <span class="avatar-initials" aria-hidden="true"><?= e(initials($member['name'])) ?></span>
    <?php endif; ?>
</span>
