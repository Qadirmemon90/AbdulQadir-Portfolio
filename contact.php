<?php
declare(strict_types=1);

$sessionAvailable = session_status() === PHP_SESSION_ACTIVE || @session_start();

const CONTACT_RECIPIENT = 'abdulqadir.metrisolution@gmail.com';
const MAX_NAME_LENGTH = 100;
const MAX_MESSAGE_LENGTH = 5000;
const RATE_LIMIT_SECONDS = 45;

function wantsJson(): bool
{
    return strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
        || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}

function respond(bool $success, string $message, int $status = 200): void
{
    http_response_code($status);

    if (wantsJson()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_SLASHES);
        exit;
    }

    $title = $success ? 'Message sent' : 'Message not sent';
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $color = $success ? '#b8ff63' : '#ff7d84';

    header('Content-Type: text/html; charset=UTF-8');
    echo "<!doctype html><html lang='en'><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>{$safeTitle}</title><style>body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:#070909;color:#f7f9f8;font-family:system-ui,sans-serif}.card{max-width:520px;padding:40px;border:1px solid rgba(255,255,255,.1);border-radius:28px;background:#111614;text-align:center}.dot{width:12px;height:12px;margin:0 auto 20px;border-radius:50%;background:{$color};box-shadow:0 0 18px {$color}}h1{margin:0 0 12px}p{color:#99a19e;line-height:1.6}a{display:inline-block;margin-top:18px;padding:12px 20px;border-radius:999px;background:#b8ff63;color:#071006;text-decoration:none;font-weight:700}</style></head><body><main class='card'><div class='dot'></div><h1>{$safeTitle}</h1><p>{$safeMessage}</p><a href='index.html#contact'>Return to portfolio</a></main></body></html>";
    exit;
}

function textLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    respond(false, 'This endpoint only accepts contact form submissions.', 405);
}

// Hidden honeypot field: bots commonly fill it, real visitors never see it.
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    respond(true, 'Thank you. Your message has been received.');
}

$lastSubmission = $sessionAvailable ? (int) ($_SESSION['contact_last_submission'] ?? 0) : 0;
if ($sessionAvailable && $lastSubmission > 0 && (time() - $lastSubmission) < RATE_LIMIT_SECONDS) {
    respond(false, 'Please wait a moment before sending another message.', 429);
}

$name = trim(strip_tags((string) ($_POST['name'] ?? '')));
$email = trim((string) ($_POST['email'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if ($name === '' || textLength($name) > MAX_NAME_LENGTH) {
    respond(false, 'Please enter a valid name.', 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $email)) {
    respond(false, 'Please enter a valid email address.', 422);
}

if ($message === '' || textLength($message) > MAX_MESSAGE_LENGTH) {
    respond(false, 'Please enter a message of up to 5,000 characters.', 422);
}

$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? 'metrisolution.com'));
$host = preg_replace('/:\d+$/', '', $host) ?: 'metrisolution.com';
if (!preg_match('/^(?:[a-z0-9-]+\.)+[a-z]{2,}$/', $host)) {
    $host = 'metrisolution.com';
}

$safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
$submittedAt = date('F j, Y \a\t g:i A T');
$subject = 'New portfolio enquiry from ' . $name;
$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
$fromEmail = 'no-reply@' . $host;

$body = "<!doctype html><html><body style='margin:0;background:#f3f5f4;font-family:Arial,sans-serif;color:#18201c'><div style='max-width:640px;margin:32px auto;padding:0 16px'><div style='padding:28px;background:#111614;border-radius:20px 20px 0 0;color:#fff'><div style='color:#b8ff63;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase'>Portfolio enquiry</div><h1 style='margin:10px 0 0;font-size:26px'>New message from {$safeName}</h1></div><div style='padding:28px;background:#fff;border-radius:0 0 20px 20px'><p style='margin:0 0 8px;color:#68716d;font-size:12px;text-transform:uppercase;letter-spacing:1px'>Contact details</p><p style='margin:0 0 24px'><strong>{$safeName}</strong><br><a href='mailto:{$safeEmail}' style='color:#236d51'>{$safeEmail}</a></p><p style='margin:0 0 8px;color:#68716d;font-size:12px;text-transform:uppercase;letter-spacing:1px'>Message</p><div style='padding:18px;border-left:3px solid #b8ff63;background:#f6f8f7;line-height:1.7'>{$safeMessage}</div><p style='margin:24px 0 0;color:#8b938f;font-size:12px'>Submitted {$submittedAt}</p></div></div></body></html>";

$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: AbdulQadir Portfolio <' . $fromEmail . '>',
    'Reply-To: ' . $name . ' <' . $email . '>',
    'X-Mailer: PHP/' . PHP_VERSION,
];

if (!mail(CONTACT_RECIPIENT, $encodedSubject, $body, implode("\r\n", $headers))) {
    error_log('Portfolio contact form: mail() returned false.');
    respond(false, 'The mail server could not send your message. Please try again later.', 500);
}

if ($sessionAvailable) {
    $_SESSION['contact_last_submission'] = time();
}
respond(true, 'Thanks! Your message has been sent successfully.');
