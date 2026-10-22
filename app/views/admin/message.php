<?php
/** @var array $message @var array $me */
$m = $message;
$replySubject = 'Re: ' . $m['topic'] . ' (Eventzpro Consult)';
$replyBody = "Dear " . strtok($m['name'], ' ') . ",\n\nThank you for contacting Eventzpro Consult.\n\n\n\n---\nOn " . date('j M Y', strtotime($m['created_at'])) . ", you wrote:\n" . $m['message'];
$mailto = 'mailto:' . $m['email'] . '?subject=' . rawurlencode($replySubject) . '&body=' . rawurlencode(mb_substr($replyBody, 0, 1500));
?>
<a class="back-link" href="<?= e(url('admin/messages')) ?>"><?= icon('chevron-left') ?> All enquiries</a>

<div class="detail">
    <article class="card">
        <header class="msg-head">
            <span class="avatar-chip avatar-chip--lg" aria-hidden="true"><?= e(initials($m['name'])) ?></span>
            <div>
                <h2 class="msg-head-name"><?= e($m['name']) ?></h2>
                <p class="msg-head-sub"><?= e($m['topic']) ?> · <?= e(date('l j F Y, H:i', strtotime($m['created_at']))) ?></p>
            </div>
            <?= admin_status_badge($m['status']) ?>
        </header>

        <dl class="facts">
            <div><dt>Email</dt><dd><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></dd></div>
            <div><dt>Phone</dt><dd><?= $m['phone'] ? '<a href="tel:' . e(preg_replace('/[^0-9+]/', '', $m['phone'])) . '">' . e($m['phone']) . '</a>' : '<span class="muted">Not provided</span>' ?></dd></div>
            <div><dt>Organisation</dt><dd><?= $m['company'] ? e($m['company']) : '<span class="muted">Not provided</span>' ?></dd></div>
            <div><dt>Reference</dt><dd><code><?= e($m['reference']) ?></code></dd></div>
        </dl>

        <div class="msg-body"><?= nl2br(e($m['message'])) ?></div>

        <div class="msg-actions">
            <a class="btn btn--primary" href="<?= e($mailto) ?>"><?= icon('reply') ?> Reply by email</a>
            <?php if ($m['status'] !== 'replied'): ?>
                <form method="post" action="<?= e(url('admin/messages/' . $m['id'])) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="status" value="replied">
                    <button class="btn btn--outline" type="submit"><?= icon('check') ?> Mark as replied</button>
                </form>
            <?php endif; ?>
        </div>
    </article>

    <aside class="detail-aside">
        <section class="card">
            <h2 class="card-title">Status</h2>
            <form class="inline-form" method="post" action="<?= e(url('admin/messages/' . $m['id'])) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="status">
                <label class="sr-only" for="status">Status</label>
                <select id="status" name="status">
                    <?php foreach (MESSAGE_STATUSES as $key => $label): ?>
                        <option value="<?= e($key) ?>"<?= $m['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn--dark btn--sm" type="submit">Update</button>
            </form>
            <ul class="timeline-mini">
                <li><span>Received</span><?= e(admin_when($m['created_at'])) ?></li>
                <li><span>Opened</span><?= e(admin_when($m['read_at'])) ?></li>
                <li><span>Replied</span><?= e(admin_when($m['replied_at'])) ?></li>
                <li><span>Handled by</span><?= e($m['handler_name'] ?? '—') ?></li>
                <li><span>Email alert</span><?= (int) $m['email_sent'] ? 'Sent to ' . e(config('mail.to')) : 'Not sent' ?></li>
            </ul>
        </section>

        <section class="card">
            <h2 class="card-title">Internal notes</h2>
            <form class="stack" method="post" action="<?= e(url('admin/messages/' . $m['id'])) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="notes">
                <label class="sr-only" for="notes">Internal notes</label>
                <textarea id="notes" name="notes" rows="5" maxlength="5000" placeholder="Only the team sees these notes, e.g. called back on Tuesday, sent proposal…"><?= e($m['notes'] ?? '') ?></textarea>
                <button class="btn btn--outline btn--sm" type="submit">Save notes</button>
            </form>
        </section>

        <?php if ($me['role'] === 'admin'): ?>
            <form method="post" action="<?= e(url('admin/messages/' . $m['id'])) ?>" data-confirm="Delete this enquiry from <?= e($m['name']) ?> permanently? This cannot be undone.">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <button class="btn btn--danger-link" type="submit"><?= icon('trash') ?> Delete enquiry</button>
            </form>
        <?php endif; ?>
    </aside>
</div>
