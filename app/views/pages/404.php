<section class="error-page">
    <div class="hex-field" aria-hidden="true"></div>
    <div class="container error-page-inner">
        <p class="error-page-code">404</p>
        <h1>This page has moved, or never existed.</h1>
        <p>Let's get you back on track.</p>
        <div class="btn-row btn-row--center">
            <a class="btn btn--primary" href="<?= e(url()) ?>">Back to home <?= icon('arrow-right') ?></a>
            <a class="btn btn--ghost-light" href="<?= e(url('services')) ?>">Our services</a>
            <a class="btn btn--ghost-light" href="<?= e(url('contact')) ?>">Contact us</a>
        </div>
    </div>
</section>
