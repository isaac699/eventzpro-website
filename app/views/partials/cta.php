<?php
$company = content('company');
$title ??= 'Have an idea, a stalled project or a factory to build?';
$text ??= 'Let\'s turn it into a bankable, sustainable enterprise. Speak with our consultants about your next move.';
?>
<section class="cta">
    <div class="container">
        <div class="cta-panel reveal">
            <div class="cta-hex" aria-hidden="true"></div>
            <div class="cta-copy">
                <p class="eyebrow">Start a conversation</p>
                <h2 class="cta-title"><?= e($title) ?></h2>
                <p><?= e($text) ?></p>
            </div>
            <div class="cta-actions">
                <a class="btn btn--dark btn--lg" href="<?= e(url('contact')) ?>">Book a consultation <?= icon('arrow-right') ?></a>
                <a class="cta-phone" href="tel:<?= e($company['phones'][0]['tel']) ?>"><?= icon('phone') ?><span><small>Or call us</small><?= e($company['phones'][0]['label']) ?></span></a>
            </div>
        </div>
    </div>
</section>
