<?php
declare(strict_types=1);

/**
 * Outgoing email. MAIL_TRANSPORT=smtp sends through a real mailbox using the small
 * SMTP client below (SSL on 465 or STARTTLS on 587), which lands in inboxes far more
 * reliably than PHP mail(). Messages are multipart: plain text plus optional HTML.
 */
function send_mail(string $to, string $subject, string $text, ?string $html = null, ?string $replyTo = null): bool
{
    if (!config('mail.enabled')) {
        return false;
    }
    $to = header_safe($to);
    $from = header_safe((string) config('mail.from'));
    $fromName = header_safe((string) config('mail.from_name'));
    $encodedSubject = '=?UTF-8?B?' . base64_encode(header_safe($subject)) . '?=';
    $domain = substr(strrchr($from, '@') ?: '@localhost', 1);

    $headers = [
        'From: =?UTF-8?B?' . base64_encode($fromName) . '?= <' . $from . '>',
        'Date: ' . date('r'),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $domain . '>',
        'MIME-Version: 1.0',
        'X-Mailer: Eventzpro-Web',
    ];
    if ($replyTo !== null && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . header_safe($replyTo);
    }

    if ($html === null) {
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'Content-Transfer-Encoding: quoted-printable';
        $body = quoted_printable_encode(mail_crlf($text));
    } else {
        $boundary = 'ezp-' . bin2hex(random_bytes(8));
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
        $body = "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n"
            . quoted_printable_encode(mail_crlf($text)) . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n"
            . quoted_printable_encode(mail_crlf($html)) . "\r\n"
            . "--{$boundary}--\r\n";
    }

    try {
        if (config('mail.transport') === 'smtp') {
            $message = 'To: ' . $to . "\r\nSubject: " . $encodedSubject . "\r\n" . implode("\r\n", $headers) . "\r\n\r\n" . $body;
            smtp_send($from, [$to], $message);
            return true;
        }
        if (!function_exists('mail')) {
            return false;
        }
        return @mail($to, $encodedSubject, $body, implode("\r\n", $headers));
    } catch (Throwable $e) {
        error_log('[mail] ' . $e->getMessage());
        return false;
    }
}

function mail_crlf(string $s): string
{
    return preg_replace('/\r\n|\r|\n/', "\r\n", $s) ?? $s;
}

/** Minimal SMTP client: EHLO, optional STARTTLS, AUTH LOGIN, one message. Throws on failure. */
function smtp_send(string $from, array $recipients, string $message): void
{
    $host = (string) config('mail.host');
    $port = (int) config('mail.port');
    $encryption = (string) config('mail.encryption');

    $context = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $host]]);
    $remote = ($encryption === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $fp = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
    if ($fp === false) {
        throw new RuntimeException("SMTP connect to {$host}:{$port} failed: {$errstr}");
    }
    stream_set_timeout($fp, 20);

    $expect = static function (array $codes, string $step) use ($fp): void {
        $reply = '';
        while (($line = fgets($fp, 1024)) !== false) {
            $reply .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        $code = (int) substr($reply, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new RuntimeException("SMTP {$step} failed: " . trim($reply));
        }
    };
    $send = static function (string $command, array $codes, string $step) use ($fp, $expect): void {
        fwrite($fp, $command . "\r\n");
        $expect($codes, $step);
    };

    try {
        $helo = preg_replace('/[^A-Za-z0-9.\-]/', '', (string) parse_url((string) config('url'), PHP_URL_HOST)) ?: 'localhost';
        $expect([220], 'greeting');
        $send('EHLO ' . $helo, [250], 'EHLO');

        if ($encryption === 'tls') {
            $send('STARTTLS', [220], 'STARTTLS');
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
                throw new RuntimeException('SMTP TLS negotiation failed');
            }
            $send('EHLO ' . $helo, [250], 'EHLO after STARTTLS');
        }

        $user = (string) config('mail.username');
        if ($user !== '') {
            $send('AUTH LOGIN', [334], 'AUTH');
            $send(base64_encode($user), [334], 'AUTH username');
            $send(base64_encode((string) config('mail.password')), [235], 'AUTH password');
        }

        $send('MAIL FROM:<' . $from . '>', [250], 'MAIL FROM');
        foreach ($recipients as $rcpt) {
            $send('RCPT TO:<' . header_safe($rcpt) . '>', [250, 251], 'RCPT TO');
        }
        $send('DATA', [354], 'DATA');
        // Dot-stuffing: a line starting with "." must be doubled.
        $data = preg_replace('/^\./m', '..', mail_crlf($message)) ?? $message;
        $send(rtrim($data, "\r\n") . "\r\n.", [250], 'message');
        fwrite($fp, "QUIT\r\n");
    } finally {
        fclose($fp);
    }
}
