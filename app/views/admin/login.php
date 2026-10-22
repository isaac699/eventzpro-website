<?php /** @var array $errors @var string $email */ ?>
<h1 class="auth-title">Sign in</h1>
<p class="auth-intro">Use the email and password your administrator set up for you.</p>

<?php if (!empty($errors['form'])): ?>
    <div class="flash flash--error" role="alert"><?= e($errors['form']) ?></div>
<?php endif; ?>

<form class="stack" method="post" action="<?= e(url('admin/login')) ?>">
    <?= csrf_field() ?>
    <div class="field">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required autocomplete="username" autofocus>
    </div>
    <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
    </div>
    <button class="btn btn--primary btn--block" type="submit">Sign in <?= icon('arrow-right') ?></button>
</form>
