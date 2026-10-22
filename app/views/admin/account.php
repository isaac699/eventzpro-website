<?php
/** @var array $errors @var array $me */
$val = static fn (string $k): string => e($_POST[$k] ?? $me[$k] ?? '');
?>
<section class="card card--narrow">
    <form class="stack" method="post" action="<?= e(url('admin/account')) ?>" novalidate>
        <?= csrf_field() ?>
        <h2 class="card-title">Your details</h2>
        <div class="form-grid">
            <div class="field">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" value="<?= $val('name') ?>" required maxlength="120" autocomplete="name"<?= admin_aria($errors, 'name') ?>>
                <?= admin_field_error($errors, 'name') ?>
            </div>
            <div class="field">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="<?= $val('email') ?>" required maxlength="190" autocomplete="username"<?= admin_aria($errors, 'email') ?>>
                <?= admin_field_error($errors, 'email') ?>
            </div>
        </div>

        <h2 class="card-title card-title--spaced">Change password</h2>
        <p class="hint">Leave these empty to keep your current password. Changing it signs you out on other devices.</p>
        <div class="field">
            <label for="current_password">Current password</label>
            <input type="password" id="current_password" name="current_password" autocomplete="current-password"<?= admin_aria($errors, 'current_password') ?>>
            <?= admin_field_error($errors, 'current_password') ?>
        </div>
        <div class="form-grid">
            <div class="field">
                <label for="password">New password</label>
                <input type="password" id="password" name="password" minlength="10" autocomplete="new-password"<?= admin_aria($errors, 'password') ?>>
                <?= admin_field_error($errors, 'password') ?>
            </div>
            <div class="field">
                <label for="password_confirm">Confirm new password</label>
                <input type="password" id="password_confirm" name="password_confirm" autocomplete="new-password"<?= admin_aria($errors, 'password_confirm') ?>>
                <?= admin_field_error($errors, 'password_confirm') ?>
            </div>
        </div>
        <button class="btn btn--primary" type="submit">Save changes</button>
    </form>
</section>
