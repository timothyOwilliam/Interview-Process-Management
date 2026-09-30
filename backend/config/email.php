<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';
// app.php loads env.php + the .env file unconditionally,
// so all SMTP_*/FROM_* keys are already in $_ENV here.
require_once __DIR__ . '/app.php';

/**
 * Absolute base URL for links placed inside emails
 * (APP_BASE alone is relative and unusable outside the browser).
 */
function buildAppBaseUrl(): string {
    global $isLocal;

    if (!empty($isLocal)) {
        return $_ENV['APP_URL_LOCAL'] ?? ('http://localhost' . APP_BASE);
    }

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
        || ($_SERVER['SERVER_PORT'] ?? '') == 443) ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'yourdomain.com';
    return $_ENV['APP_URL_PROD'] ?? ($protocol . $host . APP_BASE);
}

/**
 * Builds a ready-to-send PHPMailer instance from $_ENV.
 * Throws on misconfiguration so callers can roll back / report.
 */
function getMailer(): PHPMailer {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
    $mail->SMTPAuth   = filter_var($_ENV['SMTP_AUTH'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $mail->Username   = $_ENV['SMTP_USERNAME'] ?? '';
    $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? '';
    $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? 'tls';
    $mail->Port       = (int)($_ENV['SMTP_PORT'] ?? 587);

    $mail->setFrom(
        $_ENV['FROM_EMAIL'] ?? 'no-reply@localhost',
        $_ENV['FROM_NAME'] ?? 'NCIA Recruitment'
    );

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Timeout = 15;

    // Logo referenced as cid:ncia_logo in the email templates.
    $mail->addEmbeddedImage(__DIR__ . '/../emails/templates/NCIA-Logo-2022.png', 'ncia_logo');

    return $mail;
}

// Backward compatibility: existing scripts expect a configured $mail
// immediately after requiring this file. Failures are logged, never echoed
// (echoing here would break header() redirects in callers).
try {
    $mail = getMailer();
} catch (Exception $e) {
    error_log('PHPMailer setup error: ' . $e->getMessage());
    $mail = new PHPMailer(true);
}

return $mail;