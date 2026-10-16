<?php
$company = content('company');
$services = content('services');
$social = array_filter($company['social']);
?>
<footer class="site-footer">
    <div class="container">
        <div class="site-footer-grid">
            <div class="site-footer-brand">
                <img src="<?= e(img('brand/logo-white.png')) ?>" alt="<?= e($company['name']) ?>" width="216" height="110" loading="lazy">
                <p><?= e($company['description']) ?></p>
                <p class="site-footer-uei"><?= icon('shield') ?> UEI <?= e($company['uei']) ?> · USAID MFA Transaction Advisor</p>
                <?php if ($social): ?>
                    <ul class="social">
                        <?php foreach ($social as $network => $href): ?>
                            <li><a href="<?= e($href) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e(ucfirst($network)) ?>"><?= icon($network) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div>
                <h2 class="site-footer-title">Company</h2>
                <ul class="site-footer-links">
                    <li><a href="<?= e(url('about')) ?>">About us</a></li>
                    <li><a href="<?= e(url('projects')) ?>">Projects &amp; clients</a></li>
                    <li><a href="<?= e(url('ventures')) ?>">Business units</a></li>
                    <li><a href="<?= e(url('leadership')) ?>">Leadership</a></li>
                    <li><a href="<?= e(url('contact')) ?>">Contact</a></li>
                    <li><a href="<?= e(asset('docs/eventzpro-company-profile.pdf')) ?>" download>Company profile (PDF)</a></li>
                </ul>
            </div>

            <div>
                <h2 class="site-footer-title">Services</h2>
                <ul class="site-footer-links">
                    <?php foreach ($services as $s): ?>
                        <li><a href="<?= e(url('services/' . $s['slug'])) ?>"><?= e($s['short']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <h2 class="site-footer-title">Visit us</h2>
                <ul class="site-footer-contact">
                    <li><?= icon('pin') ?><span><?= e($company['address'][0]) ?><br><?= e($company['address'][1]) ?></span></li>
                    <?php foreach ($company['phones'] as $p): ?>
                        <li><?= icon('phone') ?><a href="tel:<?= e($p['tel']) ?>"><?= e($p['label']) ?></a></li>
                    <?php endforeach; ?>
                    <li><?= icon('mail') ?><a href="mailto:<?= e($company['email']) ?>"><?= e($company['email']) ?></a></li>
                </ul>
            </div>
        </div>

        <div class="site-footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= e($company['name']) ?> All rights reserved.</p>
            <p><a href="<?= e(url('privacy')) ?>">Privacy notice</a><span aria-hidden="true">·</span><?= e($company['tagline']) ?></p>
        </div>
    </div>
</footer>
