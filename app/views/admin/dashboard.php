<?php
/** @var array $stats @var array $recent @var array $today @var array $perDay @var array $topics @var array $me */
$cards = [
    ['label' => 'New enquiries',       'value' => $stats['new'],      'icon' => 'mail',     'tone' => 'amber', 'href' => url('admin/messages?status=new'), 'link' => 'Open new'],
    ['label' => 'Received this month', 'value' => $stats['month'],    'icon' => 'calendar', 'tone' => 'ink',   'href' => url('admin/messages'),            'link' => 'View all'],
    ['label' => 'Awaiting a reply',    'value' => $stats['awaiting'], 'icon' => 'clock',    'tone' => 'soft',  'href' => url('admin/messages?status=read'), 'link' => 'View read'],
    ['label' => 'Team members',        'value' => $stats['users'],    'icon' => 'users',    'tone' => 'soft',  'href' => url($me['role'] === 'admin' ? 'admin/users' : 'admin/account'), 'link' => $me['role'] === 'admin' ? 'Manage' : 'My account'],
];

// Chart scale: round the top gridline up to an even number so ticks stay whole.
$peak = max($perDay ?: [0]);
$top = max(4, (int) (ceil($peak / 4) * 4));
$topicPeak = max(array_column($topics, 'n') ?: [1]);
$firstName = strtok($me['name'], ' ');
?>
<p class="page-lead">Good <?= (int) date('G') < 12 ? 'morning' : ((int) date('G') < 17 ? 'afternoon' : 'evening') ?>, <?= e($firstName) ?>. Here is what has come in through the website.</p>

<section class="stat-grid" aria-label="Summary">
    <?php foreach ($cards as $c): ?>
        <article class="card stat-card">
            <div class="stat-card-body">
                <p class="stat-card-label"><?= e($c['label']) ?></p>
                <p class="stat-card-value"><?= e(number_format($c['value'])) ?></p>
                <span class="stat-card-icon stat-card-icon--<?= e($c['tone']) ?>"><?= icon($c['icon']) ?></span>
            </div>
            <a class="card-foot-link" href="<?= e($c['href']) ?>"><?= e($c['link']) ?> <?= icon('chevron-right') ?></a>
        </article>
    <?php endforeach; ?>
</section>

<div class="dash-grid">
    <section class="card">
        <header class="card-head">
            <h2 class="card-title">Latest enquiries</h2>
            <a class="text-link" href="<?= e(url('admin/messages')) ?>">View all <?= icon('chevron-right') ?></a>
        </header>
        <?php if (!$recent): ?>
            <div class="empty">
                <span class="empty-icon"><?= icon('inbox') ?></span>
                <p><strong>No enquiries yet.</strong> Messages sent from the contact page will appear here.</p>
            </div>
        <?php else: ?>
            <ul class="msg-list">
                <?php foreach ($recent as $m): ?>
                    <li>
                        <a class="msg-row<?= $m['status'] === 'new' ? ' is-new' : '' ?>" href="<?= e(url('admin/messages/' . $m['id'])) ?>">
                            <span class="avatar-chip avatar-chip--soft" aria-hidden="true"><?= e(initials($m['name'])) ?></span>
                            <span class="msg-row-who"><strong><?= e($m['name']) ?></strong><small><?= e($m['email']) ?></small></span>
                            <span class="msg-row-topic"><?= e($m['topic']) ?></span>
                            <span class="msg-row-when"><?= e(admin_when($m['created_at'])) ?></span>
                            <?= admin_status_badge($m['status']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="card today">
        <header class="card-head">
            <h2 class="card-title">Today, <strong><?= e(date('l j')) ?></strong></h2>
            <span class="muted"><?= e(date('F Y')) ?></span>
        </header>
        <?php if (!$today): ?>
            <div class="empty empty--compact">
                <p>Nothing new today yet. You'll see each enquiry here as it arrives.</p>
            </div>
        <?php else: ?>
            <ul class="today-list">
                <?php foreach ($today as $i => $m): ?>
                    <li>
                        <a class="today-item<?= $i === 0 ? ' today-item--latest' : '' ?>" href="<?= e(url('admin/messages/' . $m['id'])) ?>">
                            <span class="today-item-title"><?= e($m['name']) ?><?php if ($m['company']): ?> <small><?= e($m['company']) ?></small><?php endif; ?></span>
                            <span class="today-item-meta"><?= e(date('H:i', strtotime($m['created_at']))) ?> · <?= e($m['topic']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<div class="dash-grid dash-grid--even">
    <section class="card">
        <header class="card-head">
            <div>
                <h2 class="card-title">Enquiries, last 14 days</h2>
                <p class="card-sub"><?= e(array_sum($perDay)) ?> in total · hover a bar for the day's count</p>
            </div>
        </header>
        <div class="chart" aria-hidden="true">
            <div class="chart-axis">
                <span><?= e($top) ?></span><span><?= e($top / 2) ?></span><span>0</span>
            </div>
            <ol class="chart-bars">
                <?php foreach ($perDay as $day => $n): $ts = strtotime($day); ?>
                    <li class="chart-col<?= $day === date('Y-m-d') ? ' is-today' : '' ?>" tabindex="0" data-tip="<?= e(date('D j M', $ts) . ': ' . $n . ' ' . ($n === 1 ? 'enquiry' : 'enquiries')) ?>">
                        <span class="chart-bar" data-h="<?= e(round($n / $top * 100, 2)) ?>"></span>
                        <span class="chart-x"><?= e(date('j', $ts)) ?></span>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
        <table class="sr-only">
            <caption>Enquiries per day, last 14 days</caption>
            <thead><tr><th scope="col">Day</th><th scope="col">Enquiries</th></tr></thead>
            <tbody><?php foreach ($perDay as $day => $n): ?><tr><td><?= e(date('l j F', strtotime($day))) ?></td><td><?= e($n) ?></td></tr><?php endforeach; ?></tbody>
        </table>
    </section>

    <section class="card">
        <header class="card-head">
            <div>
                <h2 class="card-title">What people ask about</h2>
                <p class="card-sub">All enquiries, by topic</p>
            </div>
        </header>
        <?php if (!$topics): ?>
            <div class="empty empty--compact"><p>Topics will appear once enquiries arrive.</p></div>
        <?php else: ?>
            <ul class="rank">
                <?php foreach ($topics as $t): ?>
                    <li class="rank-row">
                        <span class="rank-label"><?= e($t['topic']) ?></span>
                        <span class="rank-value"><?= e($t['n']) ?></span>
                        <span class="rank-track"><span class="rank-fill" data-w="<?= e(round($t['n'] / $topicPeak * 100, 2)) ?>"></span></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>
