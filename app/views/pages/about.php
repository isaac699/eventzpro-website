<?php
$company = content('company');
$clients = content('clients');
$years = years_in_business();
?>
<?= partial('page-hero', [
    'eyebrow' => 'About Eventzpro',
    'title'   => 'Organizational development at its best since ' . $company['founded'] . '.',
    'lead'    => 'We fill the knowledge and expertise gap of developmental and economic units, building sustainable corporate systems that facilitate learning, growth and wealth creation.',
    'crumbs'  => ['About' => null],
    'image'   => 'services/boardroom-strategy.webp',
]) ?>

<section class="section">
    <div class="container split">
        <div class="split-copy reveal">
            <p class="eyebrow">Our story</p>
            <h2 class="section-title">From a consulting practice to a multi-sector group</h2>
            <p class="lead">Eventzpro Consult was originally registered under the Business Names Act in <?= e($company['registered']) ?>. In <?= e($company['incorporated']) ?>, following a strategic change in corporate direction, the organisation was incorporated as a limited liability company.</p>
            <p>Over <?= e($years) ?> years we have helped manufacturers, agribusinesses, pharmaceutical plants, hospitality developers and social enterprises conceptualise, finance, build and grow, and we now operate our own strategic business units in agro-processing, hospitality, construction and distribution.</p>
            <p>Turnaround management and entrepreneur coaching are delivered in close alliance with our affiliate, <?= e($company['affiliate']) ?>.</p>
        </div>
        <ol class="milestones reveal">
            <li><span>June 2009</span><strong>Founded</strong><p>Registered under the Business Names Act as an organizational development consultancy.</p></li>
            <li><span>2014 to 2019</span><strong>International programmes</strong><p>Technoserve Inc. and UKAID/DFID, Fairtrade Africa, Plan International and the Transmission Company of Nigeria.</p></li>
            <li><span>November 2022</span><strong>Incorporated</strong><p>Became Eventzpro Consult Ltd., a limited liability company, after a strategic change in direction.</p></li>
            <li><span>2023 to today</span><strong>Group scale</strong><p>USAID MFA Transaction Advisor, Eni &amp; Plexus Energy, the World Bank Group, plus our own business units.</p></li>
        </ol>
    </div>
</section>

<section class="section section--soft">
    <div class="container vm-grid">
        <article class="vm-card reveal">
            <p class="eyebrow">Vision</p>
            <p class="vm-card-text"><?= e($company['vision']) ?></p>
        </article>
        <article class="vm-card vm-card--dark reveal">
            <p class="eyebrow eyebrow--light">Mission</p>
            <p class="vm-card-text"><?= e($company['mission']) ?></p>
        </article>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head section-head--center">
            <p class="eyebrow">How we deliver</p>
            <h2 class="section-title">Five commitments behind every engagement</h2>
        </div>
        <div class="value-grid">
            <?php foreach ($company['commitments'] as $c): ?>
                <article class="value-card reveal">
                    <span class="value-card-icon"><?= icon($c['icon']) ?></span>
                    <h3><?= e($c['title']) ?></h3>
                    <p><?= e($c['text']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--dark">
    <div class="hex-field hex-field--faint" aria-hidden="true"></div>
    <div class="container split">
        <div class="split-media reveal">
            <img class="rounded-media" src="<?= e(img('ventures/newmont-team.webp')) ?>" alt="Eventzpro and PCL construction team on site in the Ahafo Region" loading="lazy" width="930" height="486">
        </div>
        <div class="split-copy reveal">
            <p class="eyebrow eyebrow--light">Our business operating areas</p>
            <h2 class="section-title section-title--light">Breadth that de-risks complex projects</h2>
            <ul class="checklist checklist--light">
                <li><?= icon('check') ?><span><strong>Organizational development consulting</strong>: conceptualisation, baseline research, mid- and end-term evaluation, strategy, turnaround, coaching and training.</span></li>
                <li><?= icon('check') ?><span><strong>Agribusiness projects management</strong>: managing agric commodity value-chain actors and processes.</span></li>
                <li><?= icon('check') ?><span><strong>Procurement and logistics supply</strong>: ethanol, dried cassava, processed tomatoes and more.</span></li>
                <li><?= icon('check') ?><span><strong>EPC project management</strong>: engineering, procurement and construction for industrial plants.</span></li>
                <li><?= icon('check') ?><span><strong>Events management</strong>: planning and managing events for corporate and institutional clients.</span></li>
            </ul>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head section-head--center">
            <p class="eyebrow">Clientele</p>
            <h2 class="section-title">Trusted across sectors</h2>
        </div>
        <ul class="wordmark-wall reveal">
            <?php foreach ($clients['wordmarks'] as $mark): ?><li><?= e($mark) ?></li><?php endforeach; ?>
        </ul>
        <p class="center-link"><a class="btn btn--outline" href="<?= e(url('projects')) ?>">See projects &amp; full client list <?= icon('arrow-right') ?></a></p>
    </div>
</section>

<?= partial('cta') ?>
