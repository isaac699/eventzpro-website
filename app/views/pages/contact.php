<?php
/** @var array $errors @var array $old @var ?string $status @var array $topics */
$company = content('company');
$val = static fn (string $k): string => e($old[$k] ?? '');
$err = static fn (string $k): string => isset($errors[$k]) ? '<p class="field-error" id="err-' . e($k) . '">' . e($errors[$k]) . '</p>' : '';
$aria = static fn (string $k): string => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . e($k) . '"' : '';
$mapSrc = 'https://www.google.com/maps?q=' . rawurlencode((string) config('map_query')) . '&output=embed';
$static = env('STATIC_EXPORT') === 'true'; // GitHub Pages review build: no PHP, so the form is shown but disabled
?>
<?= partial('page-hero', [
    'eyebrow' => 'Contact us',
    'title'   => 'Let\'s build something that lasts',
    'lead'    => 'Tell us about your idea, project or organisation. Our team will get back to you as soon as possible.',
    'crumbs'  => ['Contact' => null],
]) ?>

<section class="section">
    <div class="container contact">
        <aside class="contact-info">
            <div class="info-card reveal">
                <span class="info-card-icon"><?= icon('pin') ?></span>
                <div>
                    <h2>Visit our office</h2>
                    <p><?= e($company['address'][0]) ?><br><?= e($company['address'][1]) ?></p>
                </div>
            </div>
            <div class="info-card reveal">
                <span class="info-card-icon"><?= icon('phone') ?></span>
                <div>
                    <h2>Call us</h2>
                    <p><?php foreach ($company['phones'] as $p): ?><a href="tel:<?= e($p['tel']) ?>"><?= e($p['label']) ?></a><br><?php endforeach; ?></p>
                </div>
            </div>
            <div class="info-card reveal">
                <span class="info-card-icon"><?= icon('mail') ?></span>
                <div>
                    <h2>Email us</h2>
                    <p><a href="mailto:<?= e($company['email']) ?>"><?= e($company['email']) ?></a></p>
                </div>
            </div>
            <figure class="office reveal">
                <img src="<?= e(img('brand/office-building.webp')) ?>" alt="GT Bank Building on Westland Avenue, home of the Eventzpro office" loading="lazy" width="543" height="768">
                <figcaption>1st Floor, GT Bank Building</figcaption>
            </figure>
        </aside>

        <div class="contact-form reveal" id="enquiry">
            <?php if ($static): ?>
                <div class="alert alert--error" role="note"><div><strong>Preview build.</strong><p>This review copy runs without PHP, so the form is disabled. It works on the live server.</p></div></div>
            <?php endif; ?>
            <?php if ($status === 'sent'): ?>
                <div class="alert alert--success" role="status">
                    <?= icon('check') ?>
                    <div><strong>Thank you, your message has been received.</strong><p>Our team will get back to you shortly. For urgent matters, call <?= e($company['phones'][0]['label']) ?>.</p></div>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors['form'])): ?>
                <div class="alert alert--error" role="alert"><div><strong><?= e($errors['form']) ?></strong></div></div>
            <?php elseif ($errors): ?>
                <div class="alert alert--error" role="alert"><div><strong>Please check the highlighted fields.</strong></div></div>
            <?php endif; ?>

            <h2 class="form-title">Send us an enquiry</h2>
            <p class="form-intro">Fields marked <span class="req">*</span> are required.</p>

            <form class="form" method="post" action="<?= e(url('contact')) ?>#enquiry" novalidate data-contact-form>
                <?php if ($static): ?><fieldset disabled><?php endif; ?>
                <?= csrf_field() ?>
                <div class="hp" aria-hidden="true">
                    <label for="website">Leave this field empty</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-row">
                    <div class="field">
                        <label for="name">Full name <span class="req">*</span></label>
                        <input type="text" id="name" name="name" value="<?= $val('name') ?>" required maxlength="120" autocomplete="name"<?= $aria('name') ?>>
                        <?= $err('name') ?>
                    </div>
                    <div class="field">
                        <label for="company">Organisation</label>
                        <input type="text" id="company" name="company" value="<?= $val('company') ?>" maxlength="160" autocomplete="organization"<?= $aria('company') ?>>
                        <?= $err('company') ?>
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label for="email">Email address <span class="req">*</span></label>
                        <input type="email" id="email" name="email" value="<?= $val('email') ?>" required maxlength="190" autocomplete="email"<?= $aria('email') ?>>
                        <?= $err('email') ?>
                    </div>
                    <div class="field">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?= $val('phone') ?>" maxlength="25" autocomplete="tel"<?= $aria('phone') ?>>
                        <?= $err('phone') ?>
                    </div>
                </div>
                <div class="field">
                    <label for="topic">How can we help? <span class="req">*</span></label>
                    <select id="topic" name="topic" required<?= $aria('topic') ?>>
                        <option value="">Choose a topic…</option>
                        <?php foreach ($topics as $t): ?>
                            <option value="<?= e($t) ?>"<?= ($old['topic'] ?? '') === $t ? ' selected' : '' ?>><?= e($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= $err('topic') ?>
                </div>
                <div class="field">
                    <label for="message">Your message <span class="req">*</span></label>
                    <textarea id="message" name="message" rows="6" required minlength="15" maxlength="5000"<?= $aria('message') ?>><?= $val('message') ?></textarea>
                    <?= $err('message') ?>
                </div>
                <div class="field field--check">
                    <input type="checkbox" id="consent" name="consent" value="1"<?= !empty($old['consent']) ? ' checked' : '' ?><?= $aria('consent') ?>>
                    <label for="consent">I agree that Eventzpro may store my details and contact me about this enquiry, as described in the <a href="<?= e(url('privacy')) ?>">privacy notice</a>. <span class="req">*</span></label>
                    <?= $err('consent') ?>
                </div>
                <button class="btn btn--primary btn--lg" type="submit">Send message <?= icon('arrow-right') ?></button>
                <?php if ($static): ?></fieldset><?php endif; ?>
            </form>
        </div>
    </div>
</section>

<section class="map" aria-label="Office location map">
    <iframe src="<?= e($mapSrc) ?>" title="Map showing the Eventzpro office at GT Bank Building, Westland Avenue" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
</section>
