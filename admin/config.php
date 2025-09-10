<?php
$smtpHost = "hosting3.tecnosoul.com.ar";
$smtpSecure = "";
$smtpPort = 25;

// SMTP configuration loaded from environment variables with fallbacks
$smtpHost = getenv('SMTP_HOST') ?: $smtpHost;
$smtpPort = getenv('SMTP_PORT') ?: $smtpPort;
$smtpSecure = getenv('SMTP_SECURE') ?: $smtpSecure;
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

// These variables define the connection information for your MySQL database
//produccion
/*$host = "localhost"; 
$username = "miroperito";
$password = "C9EpKlN8MTILc4Y";
$dbname = "miroperito";*/

//desarrollo
$host = "localhost";
$username = "root";
$password = "";
$dbname = "mi_roperito";

$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'); 
try { $db = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password, $options); } 
catch(PDOException $ex){ die("Failed to connect to the database: " . $ex->getMessage());} 
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');
session_start(); 