<?php
declare(strict_types=1);

/**
 * Contact form: CSRF token, honeypot, minimum fill time and per-IP rate limit.
 * Valid enquiries are saved to the database (handled in the admin panel) and
 * emailed to MAIL_TO. If the database is unavailable they are appended to
 * app/storage/messages instead, so no enquiry is ever lost.
 */

function contact_topics(): array
{
    $topics = array_column(content('services'), 'title');
    $topics[] = 'Partnership / investment in our business units';
    $topics[] = 'Training & capacity building';
    $topics[] = 'Other enquiry';
    return $topics;
}

function render_contact(array $errors = [], array $old = [], ?string $status = null): string
{
    start_secure_session();
    $_SESSION['_contact_started'] = time();

    if ($status === null && !empty($_SESSION['_contact_flash'])) {
        $status = (string) $_SESSION['_contact_flash'];
        unset($_SESSION['_contact_flash']);
    }

    // Pages with a session must never be cached by shared proxies.
    header('Cache-Control: no-store, private');

    $old += ['topic' => $_GET['topic'] ?? ''];

    return render('contact', [
        'errors' => $errors,
        'old'    => $old,
        'status' => $status,
        'topics' => contact_topics(),
        'meta'   => [
            'title'       => 'Contact Us',
            'description' => 'Speak with Eventzpro Consult Ltd., 1st Floor, GT Bank Building, Westland Avenue, Accra. Call 0553747991 or email info@eventzproconsult.com.',
        ],
    ]);
}

function handle_contact_post(): string
{
    start_secure_session();

    $input = [
        'name'    => trim((string) ($_POST['name'] ?? '')),
        'email'   => trim((string) ($_POST['email'] ?? '')),
        'phone'   => trim((string) ($_POST['phone'] ?? '')),
        'company' => trim((string) ($_POST['company'] ?? '')),
        'topic'   => trim((string) ($_POST['topic'] ?? '')),
        'message' => trim((string) ($_POST['message'] ?? '')),
        'consent' => !empty($_POST['consent']),
    ];

    if (!csrf_verify($_POST['_token'] ?? null)) {
        http_response_code(419);
        return render_contact(['form' => 'Your session expired. Please submit the form again.'], $input);
    }

    // Bots: filled the hidden field, or submitted impossibly fast. Pretend success.
    $started = (int) ($_SESSION['_contact_started'] ?? 0);
    if (($_POST['website'] ?? '') !== '' || time() - $started < (int) config('contact.min_fill_seconds')) {
        $_SESSION['_contact_flash'] = 'sent';
        redirect('contact#enquiry');
    }

    $errors = validate_contact($input);
    if ($errors) {
        http_response_code(422);
        return render_contact($errors, $input);
    }

    if (!rate_limit('contact', (int) config('contact.rate_limit_max'), (int) config('contact.rate_limit_window'))) {
        http_response_code(429);
        return render_contact(['form' => 'You have sent several messages recently. Please try again later or call us directly.'], $input);
    }

    $record = [
        'reference'  => bin2hex(random_bytes(8)),
        'name'       => $input['name'],
        'email'      => $input['email'],
        'phone'      => $input['phone'],
        'company'    => $input['company'],
        'topic'      => $input['topic'],
        'message'    => $input['message'],
        'ip_hash'    => hash('sha256', client_ip()),
        'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        'created_at' => db_now(),
    ];

    $id = store_enquiry($record);
    if (send_enquiry_email($record, $id) && $id !== null) {
        try {
            db_query('UPDATE messages SET email_sent = 1 WHERE id = ?', [$id]);
        } catch (Throwable $e) {
            error_log('[contact] Could not flag email_sent for ' . $record['reference'] . ': ' . $e->getMessage());
        }
    }

    // Rotate the token after a successful submission (prevents double posts).
    unset($_SESSION['_csrf']);
    $_SESSION['_contact_flash'] = 'sent';
    redirect('contact#enquiry');
}

function validate_contact(array $in): array
{
    $errors = [];
    $len = static fn (string $s): int => mb_strlen($s, 'UTF-8');

    if ($len($in['name']) < 2 || $len($in['name']) > 120) {
        $errors['name'] = 'Please enter your full name.';
    }
    if (!filter_var($in['email'], FILTER_VALIDATE_EMAIL) || $len($in['email']) > 190) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($in['phone'] !== '' && !preg_match('/^[0-9+()\-\s]{7,25}$/', $in['phone'])) {
        $errors['phone'] = 'Please enter a valid phone number.';
    }
    if ($len($in['company']) > 160) {
        $errors['company'] = 'Organisation name is too long.';
    }
    if (!in_array($in['topic'], contact_topics(), true)) {
        $errors['topic'] = 'Please choose what your enquiry is about.';
    }
    if ($len($in['message']) < 15 || $len($in['message']) > 5000) {
        $errors['message'] = 'Please tell us a little more (at least 15 characters).';
    }
    if (!$in['consent']) {
        $errors['consent'] = 'Please confirm we may contact you about your enquiry.';
    }
    return $errors;
}

/** Save to the database; returns the new id, or null when it had to fall back to a file. */
function store_enquiry(array $record): ?int
{
    try {
        return db_insert('messages', $record + ['status' => 'new', 'updated_at' => $record['created_at']]);
    } catch (Throwable $e) {
        error_log('[contact] Database unavailable, enquiry ' . $record['reference'] . ' saved to file: ' . $e->getMessage());
    }
    $file = STORAGE_PATH . '/messages/enquiries-' . date('Y-m') . '.jsonl';
    $line = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    if (@file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
        error_log('[contact] Could not write enquiry ' . $record['reference']);
    }
    return null;
}

/** Notify the company inbox. Replying to the email answers the visitor directly. */
function send_enquiry_email(array $r, ?int $id): bool
{
    $subject = 'New website enquiry: ' . $r['topic'] . ' from ' . $r['name'];
    $adminUrl = $id !== null ? absolute_url('admin/messages/' . $id) : null;
    $fields = [
        'Name'         => $r['name'],
        'Email'        => $r['email'],
        'Phone'        => $r['phone'] ?: 'Not provided',
        'Organisation' => $r['company'] ?: 'Not provided',
        'Topic'        => $r['topic'],
        'Received'     => date('j M Y, H:i', strtotime($r['created_at'])) . ' (Accra)',
        'Reference'    => $r['reference'],
    ];

    $text = "New enquiry from the Eventzpro website\n" . str_repeat('-', 44) . "\n";
    foreach ($fields as $label => $value) {
        $text .= str_pad($label . ':', 14) . $value . "\n";
    }
    $text .= "\n" . $r['message'] . "\n";
    if ($adminUrl) {
        $text .= "\nManage this enquiry: {$adminUrl}\n";
    }

    $html = capture(APP_ROOT . '/views/emails/enquiry.php', ['r' => $r, 'fields' => $fields, 'adminUrl' => $adminUrl]);

    $sent = send_mail((string) config('mail.to'), $subject, $text, $html, $r['email']);
    if (!$sent && config('mail.enabled')) {
        error_log('[contact] Notification email failed for enquiry ' . $r['reference'] . ' (it is saved and visible in the admin panel)');
    }
    return $sent;
}
