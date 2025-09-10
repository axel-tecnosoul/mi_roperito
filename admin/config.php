<?php
// SMTP configuration loaded from environment variables with fallbacks
$smtpHost = getenv('SMTP_HOST') ?: 'c1971287.ferozo.com';
$smtpPort = getenv('SMTP_PORT') ?: 587;
$smtpSecure = getenv('SMTP_SECURE') ?: 'tls';
$smtpUsuario = getenv('SMTP_USER') ?: 'avisos@miroperito.ar';
$smtpClave = getenv('SMTP_PASS') ?: '';
$fromEmail = getenv('SMTP_FROM') ?: 'avisos@miroperito.ar';
$fromName  = getenv('SMTP_FROM_NAME') ?: 'MiRoperito';

// Google reCAPTCHA keys
// Using Google's test keys by default; override via environment variables.
$recaptchaSite   = getenv('RECAPTCHA_SITE') ?: '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';
$recaptchaSecret = getenv('RECAPTCHA_SECRET') ?: '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';
?>
