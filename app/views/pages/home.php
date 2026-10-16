<?php
$company = content('company');
$services = content('services');
$projects = content('projects');
$clients = content('clients');
$ventures = content('ventures');
$team = content('team');
$years = years_in_business();
$featured = array_slice($projects, 0, 6);
?>
<!-- HERO -->
<section class="hero">
    <div class="hex-field" aria-hidden="true"></div>
    <div class="container hero-inner">
        <div class="hero-copy">
            <h1 class="hero-title">We turn bold ideas into <span class="text-amber">bankable, sustainable</span> enterprises.</h1>
            <p class="hero-lead">For <?= e($years) ?> years, Eventzpro Consult has guided manufacturers, agribusinesses, development partners and investors across Ghana, from concept and financing through construction, commissioning and growth.</p>
            <div class="hero-actions">
                <a class="btn btn--primary btn--lg" href="<?= e(url('contact')) ?>">Book a consultation <?= icon('arrow-right') ?></a>
                <a class="btn btn--ghost-light btn--lg" href="<?= e(url('services')) ?>">Explore our services</a>
            </div>
            <dl class="hero-facts">
                <div><dt>GHS 400M+</dt><dd>long-term financing facilitated</dd></div>
                <div><dt>35+</dt><dd>organisations served</dd></div>
                <div><dt>6</dt><dd>practice areas</dd></div>
            </dl>
        </div>

        <div class="hero-visual" aria-hidden="true">
            <div class="hex-stack">
                <figure class="hex hex--lg"><img src="<?= e(img('projects/weddi-lead-consultant.webp')) ?>" alt="" fetchpriority="high" width="843" height="615"></figure>
                <figure class="hex hex--md"><img src="<?= e(img('ventures/cassava-chips.webp')) ?>" alt="" width="930" height="618"></figure>
                <figure class="hex hex--sm"><img src="<?= e(img('projects/buntaa-poultry-house.webp')) ?>" alt="" width="930" height="657"></figure>
                <span class="hex-outline hex-outline--a"></span>
                <span class="hex-outline hex-outline--b"></span>
            </div>
            <div class="float-card float-card--top">
                <span class="float-card-icon"><?= icon('globe') ?></span>
                <span><strong>World Bank · USAID · Eni</strong><small>International engagements</small></span>
            </div>
            <div class="float-card float-card--bottom">
                <span class="float-card-icon float-card-icon--amber"><?= icon('factory') ?></span>
                <span><strong>Concept → Commissioning</strong><small>Factories delivered end to end</small></span>
            </div>
        </div>
    </div>
</section>

<!-- TRUST STRIP -->
<section class="trust" aria-label="Selected clients and partners">
    <div class="container trust-inner">
        <p class="trust-label">Trusted by development partners, multinationals and Ghanaian industry</p>
        <div class="marquee">
            <ul class="marquee-track">
                <?php foreach (array_merge($clients['wordmarks'], $clients['wordmarks']) as $i => $mark): ?>
                    <li<?= $i >= count($clients['wordmarks']) ? ' aria-hidden="true"' : '' ?>><?= e($mark) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<!-- ABOUT -->
<section class="section">
    <div class="container split">
        <div class="split-media reveal">
            <div class="media-stack">
                <img class="media-stack-main" src="<?= e(img('services/boardroom-strategy.webp')) ?>" alt="Consultant presenting strategy to a client team" loading="lazy" width="930" height="450">
                <img class="media-stack-inset" src="<?= e(img('services/agribusiness-field.webp')) ?>" alt="Eventzpro consultant inspecting a maize farm" loading="lazy" width="804" height="600">
                <div class="badge-years"><strong><?= e($years) ?>+</strong><span>years of<br>impact</span></div>
            </div>
        </div>
        <div class="split-copy reveal">
            <p class="eyebrow">Who we are</p>
            <h2 class="section-title">A Ghanaian consulting group built to make enterprises work.</h2>
            <p class="lead">Registered in <?= e($company['registered']) ?> and incorporated as a limited liability company in <?= e($company['incorporated']) ?>, Eventzpro has grown from an organizational development practice into a multi-sector group with its own industrial ventures.</p>
            <p><?= e($company['mission']) ?></p>
            <ul class="checklist checklist--2col">
                <?php foreach ($company['operating_areas'] as $area): ?>
                    <li><?= icon('check') ?><?= e($area) ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="btn-row">
                <a class="btn btn--dark" href="<?= e(url('about')) ?>">More about us <?= icon('arrow-right') ?></a>
                <a class="btn btn--link" href="<?= e(asset('docs/eventzpro-company-profile.pdf')) ?>" download><?= icon('download') ?> Download company profile</a>
            </div>
        </div>
    </div>
</section>

<!-- SERVICES -->
<section class="section section--soft">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="eyebrow">What we do</p>
                <h2 class="section-title">Six practice areas. One accountable partner.</h2>
            </div>
            <p class="section-head-text">From strategy rooms to factory floors and mine sites, we bring the expertise, networks and project discipline to deliver measurable results.</p>
        </div>
        <div class="service-grid">
            <?php foreach ($services as $s): ?>
                <a class="service-card reveal" href="<?= e(url('services/' . $s['slug'])) ?>">
                    <span class="service-card-num"><?= e($s['number']) ?></span>
                    <span class="service-card-icon"><?= icon($s['icon']) ?></span>
                    <h3 class="service-card-title"><?= e($s['short']) ?></h3>
                    <p><?= e($s['summary']) ?></p>
                    <span class="service-card-more">Learn more <?= icon('arrow-up-right') ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="stats">
    <div class="hex-field hex-field--faint" aria-hidden="true"></div>
    <div class="container stats-grid">
        <?php foreach ($company['stats'] as $stat): ?>
            <div class="stat reveal">
                <p class="stat-value"><?php if ($stat['prefix']): ?><span class="stat-unit"><?= e(trim($stat['prefix'])) ?></span><?php endif; ?><span data-count="<?= e($stat['value']) ?>"><?= e(number_format($stat['value'])) ?></span><?= e($stat['suffix']) ?></p>
                <p class="stat-label"><?= e($stat['label']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- PROCESS -->
<section class="section">
    <div class="container">
        <div class="section-head section-head--center">
            <p class="eyebrow">How we work</p>
            <h2 class="section-title">From raw idea to a running enterprise</h2>
            <p class="section-head-text">A proven path we have walked with factories, farms and social enterprises, under a strict oath of professional secrecy.</p>
        </div>
        <ol class="process">
            <?php foreach ($company['process'] as $i => $step): ?>
                <li class="process-step reveal">
                    <span class="process-num"><?= sprintf('%02d', $i + 1) ?></span>
                    <h3><?= e($step['title']) ?></h3>
                    <p><?= e($step['text']) ?></p>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<!-- PROJECTS -->
<section class="section section--soft">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="eyebrow">Selected work</p>
                <h2 class="section-title">Projects that create jobs and replace imports</h2>
            </div>
            <a class="btn btn--outline" href="<?= e(url('projects')) ?>">View all projects <?= icon('arrow-right') ?></a>
        </div>
        <div class="project-grid">
            <?php foreach ($featured as $i => $p): ?>
                <article class="project-card reveal<?= $i === 0 ? ' project-card--feature' : '' ?>">
                    <img src="<?= e(img($p['image'])) ?>" alt="<?= e($p['client'] . ': ' . $p['title']) ?>" loading="lazy">
                    <div class="project-card-body">
                        <p class="project-card-client"><?= e($p['client']) ?></p>
                        <h3 class="project-card-title"><?= e($p['title']) ?></h3>
                        <p class="project-card-meta"><?= icon('pin') ?><?= e($p['location']) ?></p>
                        <?php if ($i === 0): ?><p class="project-card-summary"><?= e($p['summary']) ?></p><?php endif; ?>
                    </div>
                    <a class="project-card-link" href="<?= e(url('projects#' . $p['slug'])) ?>" aria-label="Read about <?= e($p['client']) ?>"><?= icon('arrow-up-right') ?></a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- INTERNATIONAL -->
<section class="section">
    <div class="container split split--top">
        <div class="split-copy reveal">
            <p class="eyebrow">International consultancy</p>
            <h2 class="section-title">The partner of choice for development programmes</h2>
            <p class="lead">Global institutions rely on our local knowledge and evaluation rigour to deliver programmes that work on the ground.</p>
            <div class="credential">
                <span class="credential-icon"><?= icon('award') ?></span>
                <div>
                    <p class="credential-title">Feed the Future Ghana: MFA Transaction Advisor</p>
                    <p>USAID-supported Mobilizing Finance in Agriculture Activity · UEI <?= e($company['uei']) ?></p>
                </div>
            </div>
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

<!-- VENTURES -->
<section class="section section--dark">
    <div class="hex-field hex-field--faint" aria-hidden="true"></div>
    <div class="container">
        <div class="section-head">
            <div>
                <p class="eyebrow eyebrow--light">Our own business units</p>
                <h2 class="section-title section-title--light">We invest in what we advise on</h2>
            </div>
            <p class="section-head-text section-head-text--light">Our strategic business units put our expertise to work in agro-processing, hospitality, construction and distribution.</p>
        </div>
        <div class="venture-grid">
            <?php foreach ($ventures as $i => $v): ?>
                <a class="venture-card reveal<?= $i === 0 ? ' venture-card--feature' : '' ?>" href="<?= e(url('ventures#' . $v['slug'])) ?>">
                    <img src="<?= e(img($v['image'])) ?>" alt="" loading="lazy">
                    <span class="venture-card-letter"><?= e($v['letter']) ?></span>
                    <span class="venture-card-body">
                        <small><?= e($v['subtitle']) ?></small>
                        <strong><?= e($v['title']) ?></strong>
                        <?php if ($i === 0): ?><span class="venture-card-text"><?= e($v['summary']) ?></span><?php endif; ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- LEADERSHIP -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <p class="eyebrow">Leadership</p>
                <h2 class="section-title">Experienced hands at the helm</h2>
            </div>
            <a class="btn btn--outline" href="<?= e(url('leadership')) ?>">Meet the board <?= icon('arrow-right') ?></a>
        </div>
        <div class="team-grid">
            <?php foreach ($team as $m): ?>
                <a class="team-card reveal" href="<?= e(url('leadership#' . $m['slug'])) ?>">
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

<?= partial('cta') ?>
