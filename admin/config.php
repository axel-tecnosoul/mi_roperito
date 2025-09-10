<?php
// SMTP configuration loaded from environment variables with fallbacks
$smtpHost = getenv('SMTP_HOST') ?: 'c1971287.ferozo.com';
$smtpPort = getenv('SMTP_PORT') ?: 587;
$smtpSecure = getenv('SMTP_SECURE') ?: 'tls';
$smtpUsuario = getenv('SMTP_USER') ?: 'avisos@miroperito.ar';
$smtpClave = getenv('SMTP_PASS') ?: '';
$fromEmail = getenv('SMTP_FROM') ?: 'avisos@miroperito.ar';
$fromName  = getenv('SMTP_FROM_NAME') ?: 'MiRoperito';

// reCAPTCHA keys; respect values already defined and fall back to environment variables
if (!isset($recaptchaSiteKey)) {
    $recaptchaSiteKey = getenv('RECAPTCHA_SITE_KEY') ?: '';
}
if (!isset($recaptchaSecret)) {
    $recaptchaSecret = getenv('RECAPTCHA_SECRET') ?: '';
}
?>
