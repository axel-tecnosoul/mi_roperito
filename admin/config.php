<?php
$envPath = dirname(__DIR__) . '/.env';
$env = file_exists($envPath) ? parse_ini_file($envPath) : [];

// SMTP configuration loaded from environment variables with .env fallbacks
$smtpHost    = getenv('SMTP_HOST') ?: ($env['SMTP_HOST'] ?? 'hosting3.tecnosoul.com.ar');
$smtpSecure  = getenv('SMTP_SECURE') ?: ($env['SMTP_SECURE'] ?? '');
$smtpPort    = getenv('SMTP_PORT') ?: ($env['SMTP_PORT'] ?? 25);
$smtpUsuario = getenv('SMTP_USER') ?: ($env['SMTP_USER'] ?? 'avisos@miroperito.ar');
$smtpClave   = getenv('SMTP_PASS') ?: ($env['SMTP_PASS'] ?? '');
$fromEmail   = getenv('SMTP_FROM') ?: ($env['SMTP_FROM'] ?? 'avisos@miroperito.ar');
$fromName    = getenv('SMTP_FROM_NAME') ?: ($env['SMTP_FROM_NAME'] ?? 'MiRoperito');

// Environment selection
$appEnv = getenv('APP_ENV') ?: ($env['APP_ENV'] ?? null);

// reCAPTCHA keys for client (site) and server (secret)
$recaptchaSiteKey     = getenv('RECAPTCHA_SITE_KEY')     ?: ($env['RECAPTCHA_SITE_KEY']     ?? null);
$recaptchaSecretKey   = getenv('RECAPTCHA_SECRET_KEY')   ?: ($env['RECAPTCHA_SECRET_KEY']   ?? null);
$recaptchaSiteKeyDev  = getenv('RECAPTCHA_SITE_KEY_DEV') ?: ($env['RECAPTCHA_SITE_KEY_DEV'] ?? null);
$recaptchaSecretKeyDev= getenv('RECAPTCHA_SECRET_KEY_DEV') ?: ($env['RECAPTCHA_SECRET_KEY_DEV'] ?? null);

// Database configuration loaded from environment variables with .env fallbacks
$host     = $env['DB_HOST'];
$username = $env['DB_USER'];
$password = $env['DB_PASS'];
$dbname   = $env['DB_NAME'];

if ($appEnv === 'development') {
    $recaptchaSiteKey   = $recaptchaSiteKeyDev   ?: $recaptchaSiteKey;
    $recaptchaSecretKey = $recaptchaSecretKeyDev ?: $recaptchaSecretKey;
    // For development, you might want to use different DB credentials
    $host     = 'localhost';
    $username = 'root';
    $password = '';
    $dbname   = 'mi_roperito';
}

$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'); 
try { $db = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password, $options); } 
catch(PDOException $ex){ die("Failed to connect to the database: " . $ex->getMessage());} 
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
date_default_timezone_set('America/Argentina/Buenos_Aires');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
