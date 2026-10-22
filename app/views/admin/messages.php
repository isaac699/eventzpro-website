<?php
/** @var array $messages @var string $status @var string $q @var array $counts @var int $page @var int $pages @var int $total */
$tabs = ['' => 'All'] + MESSAGE_STATUSES;
?>
<div class="toolbar">
    <nav class="tabs" aria-label="Filter by status">
        <?php foreach ($tabs as $key => $label): $on = $status === $key; ?>
            <a class="tab<?= $on ? ' is-active' : '' ?>" href="<?= e(admin_inbox_url(['status' => $key, 'page' => null])) ?>"<?= $on ? ' aria-current="page"' : '' ?>>
                <?= e($label) ?> <span class="tab-count"><?= e($counts[$key] ?? 0) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="toolbar-actions">
        <form class="search" method="get" action="<?= e(url('admin/messages')) ?>" role="search">
            <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
            <?= icon('search') ?>
            <label class="sr-only" for="q">Search enquiries</label>
            <input type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Search name, email, topic…">
        </form>
        <a class="btn btn--outline btn--sm" href="<?= e(url('admin/messages/export') . (($s = http_build_query(array_filter(['status' => $status, 'q' => $q]))) ? '?' . $s : '')) ?>"><?= icon('download') ?> Export CSV</a>
    </div>
</div>

<section class="card card--flush">
    <?php if (!$messages): ?>
        <div class="empty">
            <span class="empty-icon"><?= icon('inbox') ?></span>
            <p><?= $q !== '' ? 'No enquiries match <strong>' . e($q) . '</strong>.' : '<strong>Nothing here.</strong> Enquiries with this status will appear here.' ?></p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">From</th>
                        <th scope="col">Topic</th>
                        <th scope="col">Organisation</th>
                        <th scope="col">Received</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $m): ?>
                        <tr class="<?= $m['status'] === 'new' ? 'is-new' : '' ?>">
                            <td>
                                <a class="who" href="<?= e(url('admin/messages/' . $m['id'])) ?>">
                                    <span class="avatar-chip avatar-chip--soft" aria-hidden="true"><?= e(initials($m['name'])) ?></span>
                                    <span><strong><?= e($m['name']) ?></strong><small><?= e($m['email']) ?></small></span>
                                </a>
                            </td>
                            <td><?= e($m['topic']) ?></td>
                            <td class="muted"><?= e($m['company'] ?: '—') ?></td>
                            <td class="nowrap"><?= e(admin_when($m['created_at'])) ?></td>
                            <td><?= admin_status_badge($m['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <footer class="table-foot">
            <span class="muted">Showing <?= e(($page - 1) * (int) config('admin.per_page') + 1) ?>–<?= e(min($total, $page * (int) config('admin.per_page'))) ?> of <?= e($total) ?></span>
            <?php if ($pages > 1): ?>
                <nav class="pager" aria-label="Pages">
                    <?php if ($page > 1): ?><a class="icon-btn" href="<?= e(admin_inbox_url(['page' => $page - 1])) ?>"><?= icon('chevron-left') ?><span class="sr-only">Previous page</span></a><?php endif; ?>
                    <span>Page <?= e($page) ?> of <?= e($pages) ?></span>
                    <?php if ($page < $pages): ?><a class="icon-btn" href="<?= e(admin_inbox_url(['page' => $page + 1])) ?>"><?= icon('chevron-right') ?><span class="sr-only">Next page</span></a><?php endif; ?>
                </nav>
            <?php endif; ?>
        </footer>
    <?php endif; ?>
</section>
