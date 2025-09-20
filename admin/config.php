<?php
$envPath = dirname(__DIR__) . '/.env';
$env = file_exists($envPath) ? parse_ini_file($envPath) : [];

// Environment selection
$appEnv = getenv('APP_ENV') ?: ($env['APP_ENV'] ?? null);

// SMTP configuration loaded from environment variables with .env
$smtpHost    = $env['SMTP_HOST'];
$smtpSecure  = $env['SMTP_SECURE'];
$smtpPort    = $env['SMTP_PORT'];
$smtpAuth    = $env['SMTP_AUTH'];
$smtpUsuario = $env['SMTP_USER'];
$smtpClave   = $env['SMTP_PASS'];
$fromEmail   = $env['SMTP_FROM'];
$fromName    = $env['SMTP_FROM_NAME'];

// reCAPTCHA keys for client (site) and server (secret)
$recaptchaSiteKey     = $env['RECAPTCHA_SITE_KEY'];
$recaptchaSecretKey   = $env['RECAPTCHA_SECRET_KEY'];
$recaptchaSiteKeyDev  = $env['RECAPTCHA_SITE_KEY_DEV'];
$recaptchaSecretKeyDev= $env['RECAPTCHA_SECRET_KEY_DEV'];

// Database configuration loaded from environment variables with .env
$host     = $env['DB_HOST'];
$username = $env['DB_USER'];
$password = $env['DB_PASS'];
$dbname   = $env['DB_NAME'];

if ($appEnv === 'development') {
  $recaptchaSiteKey   = $recaptchaSiteKeyDev   ?: $recaptchaSiteKey;
  $recaptchaSecretKey = $recaptchaSecretKeyDev ?: $recaptchaSecretKey;
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
