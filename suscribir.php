<?php
require('admin/config.php');
require('admin/database.php');

header('Content-Type: application/json');

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['email'], $_POST['g-recaptcha-response'])
) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $recaptchaToken = $_POST['g-recaptcha-response'];
    $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptchaResp = file_get_contents(
        $recaptchaUrl . '?secret=' . urlencode($recaptchaSecretKey) .
        '&response=' . urlencode($recaptchaToken) .
        '&remoteip=' . urlencode($ip)
    );
    $recaptchaData = json_decode($recaptchaResp, true);
    if (empty($recaptchaData['success'])) {
        echo json_encode(['status' => 'error', 'message' => 'reCAPTCHA inválido.']);
        exit;
    }

    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Log IP and limit frequency
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS suscripciones_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip VARCHAR(45) NOT NULL,
            fecha_hora DATETIME NOT NULL
        )'
    );

    $log = $pdo->prepare('INSERT INTO suscripciones_log (ip, fecha_hora) VALUES (?, NOW())');
    $log->execute([$ip]);

    $attemptsStmt = $pdo->prepare(
        'SELECT COUNT(*) FROM suscripciones_log WHERE ip = ? AND fecha_hora >= (NOW() - INTERVAL 1 HOUR)'
    );
    $attemptsStmt->execute([$ip]);
    if ($attemptsStmt->fetchColumn() > 5) {
        Database::disconnect();
        echo json_encode(['status' => 'error', 'message' => 'Demasiadas solicitudes desde esta IP. Intente más tarde.']);
        exit;
    }

    $email = trim($_POST['email']);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $sql = 'SELECT `id` FROM suscripciones WHERE email = ?';
        $q = $pdo->prepare($sql);
        $q->execute([$email]);
        $data = $q->fetch(PDO::FETCH_ASSOC);

        if (empty($data)) {
            $sql = 'INSERT INTO `suscripciones`(`email`, `fecha_hora`) VALUES (?, NOW())';
            $q = $pdo->prepare($sql);
            $q->execute([$email]);
            Database::disconnect();
            echo json_encode(['status' => 'success', 'message' => 'Suscripción realizada correctamente.']);
        } else {
            Database::disconnect();
            echo json_encode(['status' => 'error', 'message' => 'El email ya está suscripto.']);
        }
    } else {
        Database::disconnect();
        echo json_encode(['status' => 'error', 'message' => 'Email inválido.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Solicitud inválida.']);
?>
