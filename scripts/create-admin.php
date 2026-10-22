<?php
/**
 * Create an admin panel user from the terminal (creates the database tables if needed).
 *
 * Usage:  php scripts/create-admin.php                       (asks for the details)
 *         php scripts/create-admin.php "Ama Mensah" ama@eventzproconsult.com [admin|staff]
 * The password is always typed in, never passed as an argument (it would end up in shell history).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("CLI only\n");
}

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__) . '/app/bootstrap.php';

$ask = static function (string $prompt): string {
    fwrite(STDOUT, $prompt);
    return trim((string) fgets(STDIN));
};

$name = $argv[1] ?? $ask('Full name: ');
$email = strtolower($argv[2] ?? $ask('Email address: '));
$role = $argv[3] ?? 'admin';
$password = $ask('Password (at least 10 characters): ');
$confirm = $ask('Confirm password: ');

$errors = admin_validate_user(['name' => $name, 'email' => $email, 'password' => $password, 'confirm' => $confirm], null, true);
if (!isset(ADMIN_ROLES[$role])) {
    $errors['role'] = 'Role must be admin or staff.';
}
if ($errors) {
    fwrite(STDERR, implode("\n", $errors) . "\n");
    exit(1);
}

admin_insert_user($name, $email, $password, $role);
echo "Created " . ADMIN_ROLES[$role] . " {$name} <{$email}>. Sign in at /admin/login\n";
