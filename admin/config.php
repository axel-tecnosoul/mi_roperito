<?php
// SMTP configuration loaded from environment variables with fallbacks
$smtpHost = getenv('SMTP_HOST') ?: 'c1971287.ferozo.com';
$smtpPort = getenv('SMTP_PORT') ?: 587;
$smtpSecure = getenv('SMTP_SECURE') ?: 'tls';
$smtpUsuario = getenv('SMTP_USER') ?: 'avisos@miroperito.ar';
$smtpClave = getenv('SMTP_PASS') ?: '';
$fromEmail = getenv('SMTP_FROM') ?: 'avisos@miroperito.ar';
$fromName  = getenv('SMTP_FROM_NAME') ?: 'MiRoperito';

// reCAPTCHA keys for client (site) and server (secret)
$recaptchaSiteKey   = getenv('RECAPTCHA_SITE_KEY');
$recaptchaSecretKey = getenv('RECAPTCHA_SECRET_KEY');

if (!$recaptchaSiteKey || !$recaptchaSecretKey) {
    $envPath = dirname(__DIR__) . '/.env';
    if (file_exists($envPath)) {
        $env = parse_ini_file($envPath);
        $recaptchaSiteKey   = $recaptchaSiteKey   ?: ($env['RECAPTCHA_SITE_KEY'] ?? null);
        $recaptchaSecretKey = $recaptchaSecretKey ?: ($env['RECAPTCHA_SECRET_KEY'] ?? null);
    }
}

?>
