<?php
// SMTP configuration loaded from environment variables with fallbacks
$smtpHost = getenv('SMTP_HOST') ?: 'c1971287.ferozo.com';
$smtpPort = getenv('SMTP_PORT') ?: 587;
$smtpSecure = getenv('SMTP_SECURE') ?: 'tls';
$smtpUsuario = getenv('SMTP_USER') ?: 'avisos@miroperito.ar';
$smtpClave = getenv('SMTP_PASS') ?: '';
$fromEmail = getenv('SMTP_FROM') ?: 'avisos@miroperito.ar';
$fromName  = getenv('SMTP_FROM_NAME') ?: 'MiRoperito';

$envPath = dirname(__DIR__) . '/.env';
$env = file_exists($envPath) ? parse_ini_file($envPath) : [];

// Environment selection
$appEnv = getenv('APP_ENV') ?: ($env['APP_ENV'] ?? null);

// reCAPTCHA keys for client (site) and server (secret)
$recaptchaSiteKey     = getenv('RECAPTCHA_SITE_KEY')     ?: ($env['RECAPTCHA_SITE_KEY']     ?? null);
$recaptchaSecretKey   = getenv('RECAPTCHA_SECRET_KEY')   ?: ($env['RECAPTCHA_SECRET_KEY']   ?? null);
$recaptchaSiteKeyDev  = getenv('RECAPTCHA_SITE_KEY_DEV') ?: ($env['RECAPTCHA_SITE_KEY_DEV'] ?? null);
$recaptchaSecretKeyDev= getenv('RECAPTCHA_SECRET_KEY_DEV') ?: ($env['RECAPTCHA_SECRET_KEY_DEV'] ?? null);

if ($appEnv === 'development') {
    $recaptchaSiteKey   = $recaptchaSiteKeyDev   ?: $recaptchaSiteKey;
    $recaptchaSecretKey = $recaptchaSecretKeyDev ?: $recaptchaSecretKey;
}

?>
