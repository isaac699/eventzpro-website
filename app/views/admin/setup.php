<?php /** @var array $errors @var array $old @var bool $tokenIsSet */
$val = static fn (string $k): string => e($old[$k] ?? '');
?>
<h1 class="auth-title">Create the first administrator</h1>

<?php if (!$tokenIsSet): ?>
    <p class="auth-intro">The admin panel has no users yet. To create the first administrator, choose one of these:</p>
    <ol class="setup-steps">
        <li>Add a long random value as <code>ADMIN_SETUP_TOKEN=</code> in <code>app/.env</code>, then reload this page and enter it below.</li>
        <li>Or, with terminal access, run <code>php scripts/create-admin.php</code> from the project folder.</li>
    </ol>
<?php else: ?>
    <p class="auth-intro">This page only works while no users exist. Enter the setup key from <code>ADMIN_SETUP_TOKEN</code> in <code>app/.env</code>.</p>

    <?php if (!empty($errors['form'])): ?>
        <div class="flash flash--error" role="alert"><?= e($errors['form']) ?></div>
    <?php endif; ?>

    <form class="stack" method="post" action="<?= e(url('admin/setup')) ?>" novalidate>
        <?= csrf_field() ?>
        <div class="field">
            <label for="token">Setup key</label>
            <input type="password" id="token" name="token" required autocomplete="off"<?= admin_aria($errors, 'token') ?>>
            <?= admin_field_error($errors, 'token') ?>
        </div>
        <div class="field">
            <label for="name">Your name</label>
            <input type="text" id="name" name="name" value="<?= $val('name') ?>" required maxlength="120" autocomplete="name"<?= admin_aria($errors, 'name') ?>>
            <?= admin_field_error($errors, 'name') ?>
        </div>
        <div class="field">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?= $val('email') ?>" required maxlength="190" autocomplete="username"<?= admin_aria($errors, 'email') ?>>
            <?= admin_field_error($errors, 'email') ?>
        </div>
        <div class="form-grid">
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
        </div>
        <p class="hint">At least 10 characters.</p>
        <button class="btn btn--primary btn--block" type="submit">Create administrator <?= icon('arrow-right') ?></button>
    </form>
<?php endif; ?>
