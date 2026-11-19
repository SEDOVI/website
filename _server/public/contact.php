<?php
// Contact form endpoint for sedovi.nl. Runs on Strato webhosting (sedovi.net).
// Sends each message through the Strato mailbox over authenticated SMTP, so it is DKIM-signed.
declare(strict_types=1);
ini_set('display_errors', '0'); // never print warnings or server paths into the JSON
date_default_timezone_set('Europe/Amsterdam');

$private = dirname(__DIR__) . '/private';
$cfg = require $private . '/config.php';
require $private . '/phpmailer/Exception.php';
require $private . '/phpmailer/SMTP.php';
require $private . '/phpmailer/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;

const ALLOWED_ORIGINS = ['https://sedovi.nl', 'https://www.sedovi.nl'];
const PROJECTS = [
    'Weet ik nog niet', 'Prototype', 'Webapp of SaaS', 'App voor iPhone en Android', 'Onderhoud en doorbouwen',
    'Not sure yet', 'Web app or SaaS', 'iPhone and Android app', 'Maintenance and growth',
];
const PER_IP_PER_HOUR = 5;
const PER_DAY = 100;

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$lang = ($_POST['lang'] ?? '') === 'en' ? 'en' : 'nl';

if (in_array($origin, ALLOWED_ORIGINS, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
}

function done(int $status, bool $ok, string $message = ''): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['success' => $ok, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Accept');
    http_response_code(204);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') done(405, false, 'method');
if ($origin !== '' && !in_array($origin, ALLOWED_ORIGINS, true)) done(403, false, 'origin');

// Honeypot: bots tick the hidden box. Pretend it worked.
if (!empty($_POST['botcheck'])) done(200, true);

function line(string $key, int $max): string
{
    $v = trim(preg_replace('/[\r\n\t]+/', ' ', (string)($_POST[$key] ?? '')));
    return mb_substr($v, 0, $max);
}

$name = line('name', 100);
$email = line('email', 254);
$company = line('company', 150);
$project = line('project', 60);
$message = mb_substr(trim(str_replace("\r\n", "\n", (string)($_POST['message'] ?? ''))), 0, 5000);

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) done(422, false, 'invalid');
if (!in_array($project, PROJECTS, true)) $project = 'Onbekend';

// Rate limits, stored next to the private files (outside the web root).
function allowed(string $file, int $window, int $max): bool
{
    $fh = @fopen($file, 'c+');
    if (!$fh) {
        error_log('contact form: rate limit storage not writable');
        return true;
    }
    flock($fh, LOCK_EX);
    $now = time();
    $hits = array_filter(json_decode(stream_get_contents($fh) ?: '[]', true) ?: [], fn($t) => $t > $now - $window);
    $ok = count($hits) < $max;
    if ($ok) $hits[] = $now;
    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, json_encode(array_values($hits)));
    flock($fh, LOCK_UN);
    fclose($fh);
    return $ok;
}

$dir = $private . '/ratelimit';
if (!is_dir($dir)) @mkdir($dir, 0700);
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!allowed($dir . '/' . hash('sha256', $cfg['salt'] . $ip), 3600, PER_IP_PER_HOUR)) done(429, false, 'rate');
if (!allowed($dir . '/all', 86400, PER_DAY)) done(429, false, 'rate');

$body = implode("\n", [
    'Naam: ' . $name,
    'E-mail: ' . $email,
    'Bedrijf: ' . ($company !== '' ? $company : '-'),
    'Soort project: ' . $project,
    'Taal van de site: ' . $lang,
    '',
    $message,
    '',
    '-- ',
    'Verstuurd via het contactformulier op sedovi.nl op ' . date('Y-m-d H:i'),
]);

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $cfg['smtp_host'] ?? 'smtp.strato.de';
    $mail->Port = $cfg['smtp_port'] ?? 465;
    $mail->SMTPSecure = $mail->Port === 465 ? PHPMailer::ENCRYPTION_SMTPS : '';
    $mail->SMTPAuth = true;
    $mail->Username = $cfg['smtp_user'];
    $mail->Password = $cfg['smtp_pass'];
    $mail->CharSet = PHPMailer::CHARSET_UTF8;
    $mail->setFrom($cfg['smtp_user'], 'Contactformulier sedovi.nl');
    $mail->addAddress($cfg['to']);
    $mail->addReplyTo($email, $name);
    $mail->Subject = 'Contactformulier: ' . $name . ' (' . $project . ')';
    $mail->Body = $body;
    $mail->send();
} catch (Throwable $e) {
    error_log('contact form: ' . $mail->ErrorInfo);
    done(502, false, 'send');
}

done(200, true);
