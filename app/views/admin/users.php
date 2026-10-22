<?php
/** @var array $users @var array $errors @var array $old @var array $me */
$val = static fn (string $k): string => e($old[$k] ?? '');
?>
<p class="page-lead">Administrators can manage users and delete enquiries. Staff can read, reply to and update enquiries.</p>

<div class="detail detail--wide-aside">
    <section class="card card--flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">User</th>
                        <th scope="col">Role</th>
                        <th scope="col">Last sign-in</th>
                        <th scope="col"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): $self = (int) $u['id'] === (int) $me['id']; $action = url('admin/users/' . $u['id']); ?>
                        <tr class="<?= (int) $u['is_active'] ? '' : 'is-muted' ?>">
                            <td>
                                <span class="who">
                                    <span class="avatar-chip<?= $u['role'] === 'admin' ? ' avatar-chip--amber' : ' avatar-chip--soft' ?>" aria-hidden="true"><?= e(initials($u['name'])) ?></span>
                                    <span><strong><?= e($u['name']) ?><?= $self ? ' <em class="you">You</em>' : '' ?></strong><small><?= e($u['email']) ?></small></span>
                                </span>
                            </td>
                            <td>
                                <?= e(ADMIN_ROLES[$u['role']] ?? $u['role']) ?>
                                <?php if (!(int) $u['is_active']): ?><span class="badge badge--archived">Deactivated</span><?php endif; ?>
                            </td>
                            <td class="nowrap"><?= e(admin_when($u['last_login_at'])) ?></td>
                            <td class="row-actions">
                                <form method="post" action="<?= e($action) ?>" data-confirm="Create a new temporary password for <?= e($u['name']) ?>? Their current password will stop working.">
                                    <?= csrf_field() ?><input type="hidden" name="action" value="reset">
                                    <button class="icon-btn" type="submit" title="Reset password"><?= icon('key') ?><span class="sr-only">Reset password for <?= e($u['name']) ?></span></button>
                                </form>
                                <?php if (!$self): ?>
                                    <form method="post" action="<?= e($action) ?>">
                                        <?= csrf_field() ?><input type="hidden" name="action" value="role">
                                        <button class="btn btn--ghost btn--xs" type="submit"><?= $u['role'] === 'admin' ? 'Make staff' : 'Make admin' ?></button>
                                    </form>
                                    <form method="post" action="<?= e($action) ?>">
                                        <?= csrf_field() ?><input type="hidden" name="action" value="toggle">
                                        <button class="btn btn--ghost btn--xs" type="submit"><?= (int) $u['is_active'] ? 'Deactivate' : 'Reactivate' ?></button>
                                    </form>
                                    <form method="post" action="<?= e($action) ?>" data-confirm="Remove <?= e($u['name']) ?> permanently? Enquiries they handled are kept.">
                                        <?= csrf_field() ?><input type="hidden" name="action" value="delete">
                                        <button class="icon-btn icon-btn--danger" type="submit" title="Remove user"><?= icon('trash') ?><span class="sr-only">Remove <?= e($u['name']) ?></span></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="card">
        <h2 class="card-title">Add a user</h2>
        <form class="stack" method="post" action="<?= e(url('admin/users')) ?>" novalidate>
            <?= csrf_field() ?>
            <div class="field">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" value="<?= $val('name') ?>" required maxlength="120"<?= admin_aria($errors, 'name') ?>>
                <?= admin_field_error($errors, 'name') ?>
            </div>
            <div class="field">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="<?= $val('email') ?>" required maxlength="190" autocomplete="off"<?= admin_aria($errors, 'email') ?>>
                <?= admin_field_error($errors, 'email') ?>
            </div>
            <div class="field">
                <label for="role">Role</label>
                <select id="role" name="role"<?= admin_aria($errors, 'role') ?>>
                    <?php foreach (ADMIN_ROLES as $key => $label): ?>
                        <option value="<?= e($key) ?>"<?= ($old['role'] ?? '') === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= admin_field_error($errors, 'role') ?>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="10" autocomplete="new-password"<?= admin_aria($errors, 'password') ?>>
                <?= admin_field_error($errors, 'password') ?>
            </div>
            <div class="field">
                <label for="password_confirm">Confirm password</label>
                <input type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password"<?= admin_aria($errors, 'password_confirm') ?>>
                <?= admin_field_error($errors, 'password_confirm') ?>
            </div>
            <p class="hint">At least 10 characters. They can change it under My account.</p>
            <button class="btn btn--primary" type="submit">Add user</button>
        </form>
    </section>
</div>
