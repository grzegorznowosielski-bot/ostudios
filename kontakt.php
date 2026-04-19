<?php
declare(strict_types=1);

/**
 * Formularz kontaktowy — Outstanding Studios (dhosting / PHP mail)
 *
 * Przed pierwszym użyciem ustaw MAIL_TO i MAIL_FROM poniżej.
 * MAIL_FROM: adres z Twojej domeny (lepsza dostarczalność, zgodność z SPF).
 */

// ========== KONFIGURACJA ==========
const MAIL_TO = 'contact@ostudios.pl';
const MAIL_FROM = 'noreply@ostudios.pl';
const MAIL_FROM_NAME = 'Outstanding Studios — formularz';
const MAX_MESSAGE_LEN = 8000;
const MAX_NAME_LEN = 200;
// ==================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html', true, 302);
    exit;
}

$lang = isset($_POST['lang']) && $_POST['lang'] === 'en' ? 'en' : 'pl';
$returnPage = $lang === 'en' ? 'en/index.html' : 'index.html';

$name = isset($_POST['name']) ? trim((string) $_POST['name']) : '';
$email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
$message = isset($_POST['message']) ? trim((string) $_POST['message']) : '';
$honeypot = isset($_POST['website']) ? trim((string) $_POST['website']) : '';

if ($honeypot !== '') {
    redirect_ok($returnPage);
}

if ($name === '' || $email === '' || $message === '') {
    redirect_err($returnPage);
}

if (str_len_utf8($name) > MAX_NAME_LEN || str_len_utf8($message) > MAX_MESSAGE_LEN) {
    redirect_err($returnPage);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_err($returnPage);
}

$name = strip_tags($name);
$message = strip_tags($message);

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$date = date('Y-m-d H:i:s T');

$bodyLines = [
    $lang === 'en' ? 'New message from ostudios.pl (EN)' : 'Nowa wiadomość ze strony ostudios.pl (PL)',
    '',
    $lang === 'en' ? 'Name / company:' : 'Imię i nazwisko / firma:',
    $name,
    '',
    'E-mail:',
    $email,
    '',
    $lang === 'en' ? 'Message:' : 'Wiadomość:',
    $message,
    '',
    '---',
    'IP: ' . $ip,
    'Data: ' . $date,
];

$body = implode("\n", $bodyLines);

$subject = $lang === 'en'
    ? '[ostudios.pl] Message from contact form'
    : '[ostudios.pl] Wiadomość z formularza';

$fromHeader = sprintf(
    '%s <%s>',
    encode_mime_header(MAIL_FROM_NAME),
    MAIL_FROM
);

$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'From: ' . $fromHeader,
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . PHP_VERSION,
];

$sent = @mail(
    MAIL_TO,
    encode_mime_header($subject),
    $body,
    implode("\r\n", $headers)
);

if ($sent) {
    redirect_ok($returnPage);
}

redirect_err($returnPage);

function redirect_ok(string $returnPage): void
{
    $q = http_build_query(['sent' => '1']);
    header('Location: ' . $returnPage . '?' . $q, true, 303);
    exit;
}

function redirect_err(string $returnPage): void
{
    $q = http_build_query(['error' => '1']);
    header('Location: ' . $returnPage . '?' . $q, true, 303);
    exit;
}

function encode_mime_header(string $text): string
{
    if (preg_match('/[^\x20-\x7E]/', $text)) {
        return '=?UTF-8?B?' . base64_encode($text) . '?=';
    }
    return $text;
}

function str_len_utf8(string $s): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($s, 'UTF-8');
    }
    return strlen($s);
}
