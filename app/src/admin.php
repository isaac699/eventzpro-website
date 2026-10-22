<?php
declare(strict_types=1);

/**
 * Admin panel under /admin: sign-in, dashboard, enquiries inbox and user management.
 * Pages are never cached or indexed, every form carries a CSRF token, sign-in is
 * rate limited per IP, and a password change signs out every other session.
 */

const ADMIN_ROLES = ['admin' => 'Administrator', 'staff' => 'Staff'];
const MESSAGE_STATUSES = ['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'archived' => 'Archived'];

function admin_dispatch(string $method, string $path): void
{
    header('Cache-Control: no-store, private');
    header('X-Robots-Tag: noindex, nofollow');
    start_secure_session();

    $route = trim(substr($path, strlen('admin')), '/');
    $post = $method === 'POST';

    if ($post && !csrf_verify($_POST['_token'] ?? null)) {
        admin_flash('error', 'Your session expired, so nothing was changed. Please try again.');
        redirect($route === 'setup' ? 'admin/setup' : 'admin/login');
    }

    if ($route === 'login') {
        echo $post ? admin_login_post() : admin_login_page();
        return;
    }
    if ($route === 'setup') {
        echo $post ? admin_setup_post() : admin_setup_page();
        return;
    }
    if ($route === 'logout' && $post) {
        admin_sign_out();
        admin_flash('success', 'You have been signed out.');
        redirect('admin/login');
    }

    $user = admin_user();
    if ($user === null) {
        redirect(admin_user_count() === 0 ? 'admin/setup' : 'admin/login');
    }

    if ($route === '' && !$post) {
        echo admin_dashboard();
        return;
    }
    if ($route === 'messages' && !$post) {
        echo admin_messages();
        return;
    }
    if ($route === 'messages/export' && !$post) {
        admin_messages_export();
        return;
    }
    if (preg_match('#^messages/(\d+)$#', $route, $m)) {
        echo $post ? admin_message_update((int) $m[1]) : admin_message_show((int) $m[1]);
        return;
    }
    if ($route === 'users' && $user['role'] === 'admin') {
        echo $post ? admin_user_create() : admin_users();
        return;
    }
    if (preg_match('#^users/(\d+)$#', $route, $m) && $post && $user['role'] === 'admin') {
        admin_user_update((int) $m[1]);
        return;
    }
    if ($route === 'account') {
        echo $post ? admin_account_update() : admin_account();
        return;
    }

    http_response_code(404);
    echo admin_render('not-found', ['title' => 'Page not found']);
}

/* ---------------------------------------------------------------------------
   Session & authentication
   ------------------------------------------------------------------------ */

/** The signed-in user, or null. Ends sessions that are idle, disabled or whose password changed. */
function admin_user(): ?array
{
    static $resolved = false, $user = null;
    if ($resolved) {
        return $user;
    }
    $resolved = true;

    $id = (int) ($_SESSION['admin_uid'] ?? 0);
    if ($id === 0) {
        return null;
    }
    if (time() - (int) ($_SESSION['admin_seen'] ?? 0) > (int) config('admin.idle_timeout')) {
        admin_sign_out();
        admin_flash('info', 'You were signed out after an hour of inactivity.');
        return null;
    }
    $row = db_one('SELECT * FROM users WHERE id = ? AND is_active = 1', [$id]);
    if ($row === null || !hash_equals((string) ($_SESSION['admin_fp'] ?? ''), admin_fingerprint($row))) {
        admin_sign_out();
        return null;
    }
    $_SESSION['admin_seen'] = time();
    return $user = $row;
}

/** Changes whenever the password changes, which invalidates other sessions. */
function admin_fingerprint(array $user): string
{
    return substr(hash('sha256', $user['id'] . '|' . $user['password_hash']), 0, 32);
}

function admin_sign_in(array $user): void
{
    session_regenerate_id(true);
    unset($_SESSION['_csrf']);
    $_SESSION['admin_uid'] = (int) $user['id'];
    $_SESSION['admin_fp'] = admin_fingerprint($user);
    $_SESSION['admin_seen'] = time();
}

function admin_sign_out(): void
{
    unset($_SESSION['admin_uid'], $_SESSION['admin_fp'], $_SESSION['admin_seen'], $_SESSION['_csrf']);
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

function admin_user_count(): int
{
    return (int) db_value('SELECT COUNT(*) FROM users');
}

function admin_login_page(array $errors = [], string $email = ''): string
{
    if (admin_user() !== null) {
        redirect('admin');
    }
    return admin_render('login', ['title' => 'Sign in', 'errors' => $errors, 'email' => $email], 'auth');
}

function admin_login_post(): string
{
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if (!rate_limit('admin-login', (int) config('admin.login_max'), (int) config('admin.login_window'))) {
        http_response_code(429);
        return admin_login_page(['form' => 'Too many sign-in attempts. Please wait 15 minutes and try again.'], $email);
    }

    $user = $email === '' ? null : db_one('SELECT * FROM users WHERE email = ?', [$email]);
    // Verify against a dummy hash for unknown emails so response time doesn't reveal which accounts exist.
    $hash = $user['password_hash'] ?? '$2y$12$S1WNOzx74V14TtOiYIqcAe7838EsMR3VQycOhOAKIWQBgEmfsrOw6';
    $valid = password_verify($password, $hash) && $user !== null;

    if (!$valid) {
        http_response_code(422);
        return admin_login_page(['form' => 'That email and password combination is not correct.'], $email);
    }
    if (!(int) $user['is_active']) {
        http_response_code(403);
        return admin_login_page(['form' => 'This account has been deactivated. Please contact an administrator.'], $email);
    }

    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $user['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        db_query('UPDATE users SET password_hash = ? WHERE id = ?', [$user['password_hash'], $user['id']]);
    }
    db_query('UPDATE users SET last_login_at = ? WHERE id = ?', [db_now(), $user['id']]);
    admin_sign_in($user);
    redirect('admin');
}

/** First administrator: allowed only while no users exist, and only with ADMIN_SETUP_TOKEN. */
function admin_setup_page(array $errors = [], array $old = []): string
{
    if (admin_user_count() > 0) {
        redirect('admin/login');
    }
    return admin_render('setup', [
        'title'      => 'Create the first administrator',
        'errors'     => $errors,
        'old'        => $old,
        'tokenIsSet' => (string) config('admin.setup_token') !== '',
    ], 'auth');
}

function admin_setup_post(): string
{
    if (admin_user_count() > 0) {
        redirect('admin/login');
    }
    $token = (string) config('admin.setup_token');
    $input = admin_user_input();
    $errors = admin_validate_user($input, null, true);

    if (!rate_limit('admin-setup', 10, 900)) {
        $errors['form'] = 'Too many attempts. Please wait 15 minutes and try again.';
    } elseif ($token === '' || !hash_equals($token, (string) ($_POST['token'] ?? ''))) {
        $errors['token'] = 'The setup key does not match ADMIN_SETUP_TOKEN in app/.env.';
    }
    if ($errors) {
        http_response_code(422);
        return admin_setup_page($errors, $input);
    }

    $id = admin_insert_user($input['name'], $input['email'], $input['password'], 'admin');
    admin_sign_in(db_one('SELECT * FROM users WHERE id = ?', [$id]));
    admin_flash('success', 'Welcome! Your administrator account is ready. You can now remove ADMIN_SETUP_TOKEN from app/.env.');
    redirect('admin');
}

/* ---------------------------------------------------------------------------
   Dashboard
   ------------------------------------------------------------------------ */

function admin_dashboard(): string
{
    $today = date('Y-m-d');
    $monthStart = date('Y-m-01 00:00:00');
    $days = 14;
    $since = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));

    $perDay = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $perDay[date('Y-m-d', strtotime("-{$i} days"))] = 0;
    }
    foreach (db_all('SELECT SUBSTR(created_at, 1, 10) AS day, COUNT(*) AS n FROM messages WHERE created_at >= ? GROUP BY SUBSTR(created_at, 1, 10)', [$since]) as $row) {
        if (isset($perDay[$row['day']])) {
            $perDay[$row['day']] = (int) $row['n'];
        }
    }

    return admin_render('dashboard', [
        'title'  => 'Dashboard',
        'stats'  => [
            'new'      => (int) db_value("SELECT COUNT(*) FROM messages WHERE status = 'new'"),
            'month'    => (int) db_value('SELECT COUNT(*) FROM messages WHERE created_at >= ?', [$monthStart]),
            'awaiting' => (int) db_value("SELECT COUNT(*) FROM messages WHERE status IN ('new', 'read')"),
            'total'    => (int) db_value('SELECT COUNT(*) FROM messages'),
            'users'    => admin_user_count(),
        ],
        'recent' => db_all('SELECT id, name, email, topic, status, created_at FROM messages ORDER BY created_at DESC, id DESC LIMIT 6'),
        'today'  => db_all('SELECT id, name, company, topic, status, created_at FROM messages WHERE created_at >= ? ORDER BY created_at DESC LIMIT 5', [$today . ' 00:00:00']),
        'perDay' => $perDay,
        'topics' => db_all('SELECT topic, COUNT(*) AS n FROM messages GROUP BY topic ORDER BY n DESC, topic LIMIT 6'),
    ]);
}

/* ---------------------------------------------------------------------------
   Enquiries
   ------------------------------------------------------------------------ */

/** WHERE clause + params for the inbox filters (status tab + search). */
function admin_message_filters(): array
{
    $status = (string) ($_GET['status'] ?? '');
    $q = trim((string) ($_GET['q'] ?? ''));
    $where = [];
    $params = [];

    if (isset(MESSAGE_STATUSES[$status])) {
        $where[] = 'status = ?';
        $params[] = $status;
    } else {
        $status = '';
        $where[] = "status <> 'archived'";
    }
    if ($q !== '') {
        // "!" as the LIKE escape character works the same in SQLite and MySQL.
        $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_substr($q, 0, 100)) . '%';
        $where[] = "(name LIKE ? ESCAPE '!' OR email LIKE ? ESCAPE '!' OR company LIKE ? ESCAPE '!' OR topic LIKE ? ESCAPE '!' OR message LIKE ? ESCAPE '!' OR reference = ?)";
        array_push($params, $like, $like, $like, $like, $like, $q);
    }
    return [implode(' AND ', $where), $params, $status, $q];
}

function admin_messages(): string
{
    [$where, $params, $status, $q] = admin_message_filters();
    $perPage = (int) config('admin.per_page');
    $total = (int) db_value("SELECT COUNT(*) FROM messages WHERE {$where}", $params);
    $pages = max(1, (int) ceil($total / $perPage));
    $page = min($pages, max(1, (int) ($_GET['page'] ?? 1)));
    $offset = ($page - 1) * $perPage;

    $counts = ['' => (int) db_value("SELECT COUNT(*) FROM messages WHERE status <> 'archived'")];
    foreach (db_all('SELECT status, COUNT(*) AS n FROM messages GROUP BY status') as $row) {
        $counts[$row['status']] = (int) $row['n'];
    }

    return admin_render('messages', [
        'title'    => 'Enquiries',
        'messages' => db_all("SELECT id, reference, name, email, company, topic, status, created_at FROM messages WHERE {$where} ORDER BY created_at DESC, id DESC LIMIT {$perPage} OFFSET {$offset}", $params),
        'status'   => $status,
        'q'        => $q,
        'counts'   => $counts,
        'page'     => $page,
        'pages'    => $pages,
        'total'    => $total,
    ]);
}

function admin_find_message(int $id): array
{
    $message = db_one('SELECT m.*, u.name AS handler_name FROM messages m LEFT JOIN users u ON u.id = m.handled_by WHERE m.id = ?', [$id]);
    if ($message === null) {
        admin_flash('error', 'That enquiry no longer exists.');
        redirect('admin/messages');
    }
    return $message;
}

function admin_message_show(int $id): string
{
    $message = admin_find_message($id);
    if ($message['status'] === 'new') {
        $now = db_now();
        db_query("UPDATE messages SET status = 'read', read_at = ?, handled_by = COALESCE(handled_by, ?), updated_at = ? WHERE id = ?", [$now, admin_user()['id'], $now, $id]);
        $message = admin_find_message($id);
    }
    return admin_render('message', ['title' => 'Enquiry from ' . $message['name'], 'message' => $message]);
}

function admin_message_update(int $id): string
{
    $message = admin_find_message($id);
    $user = admin_user();
    $action = (string) ($_POST['action'] ?? '');
    $now = db_now();

    if ($action === 'status' && isset(MESSAGE_STATUSES[$_POST['status'] ?? ''])) {
        $status = (string) $_POST['status'];
        db_query(
            'UPDATE messages SET status = ?, replied_at = ?, handled_by = ?, updated_at = ? WHERE id = ?',
            [$status, $status === 'replied' ? ($message['replied_at'] ?? $now) : $message['replied_at'], $user['id'], $now, $id]
        );
        admin_flash('success', 'Marked as ' . strtolower(MESSAGE_STATUSES[$status]) . '.');
    } elseif ($action === 'notes') {
        $notes = mb_substr(trim((string) ($_POST['notes'] ?? '')), 0, 5000);
        db_query('UPDATE messages SET notes = ?, updated_at = ? WHERE id = ?', [$notes === '' ? null : $notes, $now, $id]);
        admin_flash('success', 'Internal notes saved.');
    } elseif ($action === 'delete' && $user['role'] === 'admin') {
        db_query('DELETE FROM messages WHERE id = ?', [$id]);
        admin_flash('success', 'Enquiry from ' . $message['name'] . ' was deleted.');
        redirect('admin/messages');
    }
    redirect('admin/messages/' . $id);
}

/** CSV download of the current inbox filter (opens cleanly in Excel). */
function admin_messages_export(): void
{
    [$where, $params] = admin_message_filters();
    $rows = db_all("SELECT reference, created_at, status, name, email, phone, company, topic, message, notes FROM messages WHERE {$where} ORDER BY created_at DESC", $params);

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="eventzpro-enquiries-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Reference', 'Received', 'Status', 'Name', 'Email', 'Phone', 'Organisation', 'Topic', 'Message', 'Notes'], ',', '"', '');
    foreach ($rows as $row) {
        // Neutralise spreadsheet formulas (CSV injection).
        $row = array_map(static fn ($v) => preg_match('/^[=+\-@\t\r]/', (string) $v) ? "'" . $v : (string) $v, $row);
        fputcsv($out, array_values($row), ',', '"', '');
    }
    fclose($out);
}

/* ---------------------------------------------------------------------------
   Users & account
   ------------------------------------------------------------------------ */

function admin_users(array $errors = [], array $old = []): string
{
    return admin_render('users', [
        'title'  => 'Users',
        'users'  => db_all('SELECT id, name, email, role, is_active, last_login_at, created_at FROM users ORDER BY is_active DESC, name'),
        'errors' => $errors,
        'old'    => $old + ['role' => 'staff'],
    ]);
}

function admin_user_input(): array
{
    return [
        'name'     => trim((string) ($_POST['name'] ?? '')),
        'email'    => strtolower(trim((string) ($_POST['email'] ?? ''))),
        'password' => (string) ($_POST['password'] ?? ''),
        'confirm'  => (string) ($_POST['password_confirm'] ?? ''),
        'role'     => (string) ($_POST['role'] ?? 'staff'),
    ];
}

function admin_validate_user(array $in, ?int $exceptId, bool $passwordRequired): array
{
    $errors = [];
    if (mb_strlen($in['name']) < 2 || mb_strlen($in['name']) > 120) {
        $errors['name'] = 'Please enter a name.';
    }
    if (!filter_var($in['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($in['email']) > 190) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (db_value('SELECT id FROM users WHERE email = ? AND id <> ?', [$in['email'], $exceptId ?? 0]) !== null) {
        $errors['email'] = 'Another user already has this email address.';
    }
    if ($passwordRequired || $in['password'] !== '') {
        if (mb_strlen($in['password']) < 10) {
            $errors['password'] = 'Use at least 10 characters.';
        } elseif ($in['password'] !== $in['confirm']) {
            $errors['password_confirm'] = 'The two passwords do not match.';
        }
    }
    return $errors;
}

function admin_insert_user(string $name, string $email, string $password, string $role): int
{
    $now = db_now();
    return db_insert('users', [
        'name'          => $name,
        'email'         => strtolower($email),
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role'          => isset(ADMIN_ROLES[$role]) ? $role : 'staff',
        'is_active'     => 1,
        'created_at'    => $now,
        'updated_at'    => $now,
    ]);
}

function admin_user_create(): string
{
    $input = admin_user_input();
    $errors = admin_validate_user($input, null, true);
    if (!isset(ADMIN_ROLES[$input['role']])) {
        $errors['role'] = 'Choose a role.';
    }
    if ($errors) {
        http_response_code(422);
        return admin_users($errors, $input);
    }
    admin_insert_user($input['name'], $input['email'], $input['password'], $input['role']);
    admin_flash('success', $input['name'] . ' can now sign in with ' . $input['email'] . '. Share the password with them securely.');
    redirect('admin/users');
}

function admin_user_update(int $id): void
{
    $me = admin_user();
    $target = db_one('SELECT * FROM users WHERE id = ?', [$id]);
    $action = (string) ($_POST['action'] ?? '');
    if ($target === null) {
        redirect('admin/users');
    }
    if ((int) $target['id'] === (int) $me['id'] && $action !== 'reset') {
        admin_flash('error', 'You cannot change your own role or access here. Ask another administrator.');
        redirect('admin/users');
    }
    $isLastAdmin = $target['role'] === 'admin' && (int) $target['is_active']
        && (int) db_value("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1") <= 1;
    $now = db_now();

    switch ($action) {
        case 'toggle':
            if ($isLastAdmin) {
                admin_flash('error', 'There must always be at least one active administrator.');
                break;
            }
            db_query('UPDATE users SET is_active = ?, updated_at = ? WHERE id = ?', [(int) $target['is_active'] ? 0 : 1, $now, $id]);
            admin_flash('success', $target['name'] . ((int) $target['is_active'] ? ' has been deactivated and can no longer sign in.' : ' has been reactivated.'));
            break;
        case 'role':
            $role = $target['role'] === 'admin' ? 'staff' : 'admin';
            if ($isLastAdmin) {
                admin_flash('error', 'There must always be at least one active administrator.');
                break;
            }
            db_query('UPDATE users SET role = ?, updated_at = ? WHERE id = ?', [$role, $now, $id]);
            admin_flash('success', $target['name'] . ' is now ' . ($role === 'admin' ? 'an administrator' : 'a staff member') . '.');
            break;
        case 'reset':
            $password = admin_random_password();
            db_query('UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $now, $id]);
            admin_flash('success', 'New temporary password for ' . $target['name'] . ': ' . $password . ' (shown only once; ask them to change it under My account).');
            break;
        case 'delete':
            if ($isLastAdmin) {
                admin_flash('error', 'There must always be at least one active administrator.');
                break;
            }
            db_query('DELETE FROM users WHERE id = ?', [$id]);
            admin_flash('success', $target['name'] . ' was removed.');
            break;
    }
    redirect('admin/users');
}

function admin_random_password(): string
{
    $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $out = '';
    for ($i = 0; $i < 14; $i++) {
        $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $out;
}

function admin_account(array $errors = []): string
{
    return admin_render('account', ['title' => 'My account', 'errors' => $errors]);
}

function admin_account_update(): string
{
    $me = admin_user();
    $input = admin_user_input();
    $errors = admin_validate_user($input, (int) $me['id'], false);
    $changingPassword = $input['password'] !== '';

    if ($changingPassword && !password_verify((string) ($_POST['current_password'] ?? ''), $me['password_hash'])) {
        $errors['current_password'] = 'Your current password is not correct.';
    }
    if ($errors) {
        http_response_code(422);
        return admin_account($errors);
    }

    $hash = $changingPassword ? password_hash($input['password'], PASSWORD_DEFAULT) : $me['password_hash'];
    db_query('UPDATE users SET name = ?, email = ?, password_hash = ?, updated_at = ? WHERE id = ?', [$input['name'], $input['email'], $hash, db_now(), $me['id']]);
    if ($changingPassword) {
        // Keep this session, sign out every other one.
        admin_sign_in(['id' => $me['id'], 'password_hash' => $hash]);
    }
    admin_flash('success', $changingPassword ? 'Your details and password were updated. Other devices have been signed out.' : 'Your details were updated.');
    redirect('admin/account');
}

/* ---------------------------------------------------------------------------
   Rendering helpers
   ------------------------------------------------------------------------ */

function admin_flash(string $type, string $text): void
{
    start_secure_session();
    $_SESSION['_admin_flash'] = ['type' => $type, 'text' => $text];
}

function admin_render(string $view, array $vars = [], string $layout = 'layout'): string
{
    $flash = $_SESSION['_admin_flash'] ?? null;
    unset($_SESSION['_admin_flash']);

    $vars += ['title' => 'Admin', 'errors' => []];
    $vars['me'] = admin_user();
    $vars['flash'] = $flash;
    if ($vars['me'] !== null) {
        $vars['newCount'] = (int) db_value("SELECT COUNT(*) FROM messages WHERE status = 'new'");
    }
    $content = capture(APP_ROOT . '/views/admin/' . $view . '.php', $vars);
    return capture(APP_ROOT . '/views/admin/' . $layout . '.php', $vars + ['content' => $content]);
}

/** URL to the inbox keeping the current filters, with overrides. */
function admin_inbox_url(array $overrides = []): string
{
    $query = array_filter(array_merge([
        'status' => $_GET['status'] ?? null,
        'q'      => $_GET['q'] ?? null,
    ], $overrides), static fn ($v) => $v !== null && $v !== '' && $v !== 1);
    return url('admin/messages') . ($query ? '?' . http_build_query($query) : '');
}

function admin_status_badge(string $status): string
{
    return '<span class="badge badge--' . e($status) . '">' . e(MESSAGE_STATUSES[$status] ?? ucfirst($status)) . '</span>';
}

/** "Today, 10:42" / "Yesterday, 16:05" / "5 Oct, 12:53" / "5 Oct 2025". */
function admin_when(?string $datetime): string
{
    if (!$datetime) {
        return 'Never';
    }
    $ts = strtotime($datetime);
    $day = date('Y-m-d', $ts);
    if ($day === date('Y-m-d')) {
        return 'Today, ' . date('H:i', $ts);
    }
    if ($day === date('Y-m-d', strtotime('-1 day'))) {
        return 'Yesterday, ' . date('H:i', $ts);
    }
    return date('Y', $ts) === date('Y') ? date('j M, H:i', $ts) : date('j M Y', $ts);
}

function admin_field_error(array $errors, string $key): string
{
    return isset($errors[$key]) ? '<p class="field-error" id="err-' . e($key) . '">' . e($errors[$key]) . '</p>' : '';
}

function admin_aria(array $errors, string $key): string
{
    return isset($errors[$key]) ? ' aria-invalid="true" aria-describedby="err-' . e($key) . '"' : '';
}
