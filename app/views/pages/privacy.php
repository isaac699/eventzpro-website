<?php $company = content('company'); ?>
<?= partial('page-hero', [
    'eyebrow' => 'Legal',
    'title'   => 'Privacy notice',
    'lead'    => 'How we handle the information you share with us through this website.',
    'crumbs'  => ['Privacy' => null],
]) ?>

<section class="section">
    <div class="container prose">
        <h2>What we collect</h2>
        <p>When you send an enquiry we collect the details you provide: your name, email address, and optionally your phone number, organisation and message. We also record the date of the enquiry and a one-way hash of your IP address to protect the form against abuse.</p>
        <h2>How we use it</h2>
        <p>We use your details only to respond to your enquiry and, where relevant, to discuss the services you asked about. We do not sell or rent your information.</p>
        <h2>Cookies</h2>
        <p>This site sets a single, strictly necessary session cookie on the contact page to protect the form against forgery. We do not use advertising or tracking cookies. The contact page embeds a Google Maps view, which is governed by Google's own privacy policy.</p>
        <h2>Retention and your rights</h2>
        <p>Enquiries are kept for as long as needed to handle them and maintain business records. In line with Ghana's Data Protection Act, 2012 (Act 843), you may ask to see, correct or delete the personal data we hold about you.</p>
        <h2>Contact</h2>
        <p>For any privacy request, email <a href="mailto:<?= e($company['email']) ?>"><?= e($company['email']) ?></a> or write to <?= e($company['name']) ?>, <?= e(implode(', ', $company['address'])) ?>.</p>
    </div>
</section>
