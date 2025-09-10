<?php
require('admin/config.php');
require('admin/database.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = 'SELECT `id` from suscripciones where email = ?';
        $q = $pdo->prepare($sql);
        $q->execute([$email]);
        $data = $q->fetch(PDO::FETCH_ASSOC);

        if (empty($data)) {
            $sql = 'INSERT INTO `suscripciones`(`email`, `fecha_hora`) VALUES (?,now())';
            $q = $pdo->prepare($sql);
            $q->execute([$email]);
            Database::disconnect();
            echo json_encode(['status' => 'success', 'message' => 'Suscripción realizada correctamente.']);
        } else {
            Database::disconnect();
            echo json_encode(['status' => 'error', 'message' => 'El email ya está suscripto.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email inválido.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Solicitud inválida.']);
?>
