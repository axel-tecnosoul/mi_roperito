<?php 
require_once __DIR__ . "/admin/config.php";
require_once __DIR__ . "/admin/database.php";

require_once __DIR__ . "/admin/PHPMailer/class.phpmailer.php";
require_once __DIR__ . "/admin/PHPMailer/class.smtp.php";

header('Content-Type: application/json');

$nombre  = trim($_POST["nombre"] ?? '');
$email   = trim($_POST["email"] ?? '');
$mensaje = trim($_POST["mensaje"] ?? '');
$subject = trim($_POST["asunto"] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) ||
strlen($nombre) > 100 || strlen($subject) > 150 || strlen($mensaje) > 1000 ||
$nombre !== strip_tags($nombre) || $subject !== strip_tags($subject) || $mensaje !== strip_tags($mensaje)) {
  echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
  exit;
}

$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
$recaptchaValid = false;
if ($recaptchaResponse && !empty($recaptchaSecretKey)) {
  $verifyResponse = file_get_contents(
    'https://www.google.com/recaptcha/api/siteverify?secret=' .
    urlencode($recaptchaSecretKey) . '&response=' . urlencode($recaptchaResponse)
  );
  $responseData = json_decode($verifyResponse, true);
  $recaptchaValid = $responseData['success'] ?? false;
}
if (!$recaptchaValid) {
  echo json_encode(['success' => false, 'message' => 'Error: reCAPTCHA inválido.']);
  exit;
}

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = "INSERT INTO `contactos`(`fecha_hora`, `nombre`, `email`, `asunto`, `mensaje`) VALUES (now(),?,?,?,?)";
$q = $pdo->prepare($sql);
$q->execute(array($nombre,$email,$subject,$mensaje));

$emailSucursal="miroperitooficial@gmail.com";
if($idAlmacen==7){//nuñez
  //$emailSucursal="";
}

/*$message = "
<html>
<head>
<title>Contacto MiRoperito</title>
</head>
<body>
<table width='50%' border='0' align='center' cellpadding='0' cellspacing='0'>
<tr>
<td colspan='2' align='center' valign='top'><img style=' margin-top: 15px; width: 100%; max-width: 300px;' src='https://miroperito.ar/images/logo/Logo-Mi-roperito.png' ></td>
</tr>
<tr>
<td width='50%' align='right'>&nbsp;</td>
<td align='left'>&nbsp;</td>
</tr>
<tr>
<td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Nombre:</td>
<td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$nombre."</td>
</tr>
<tr>
<td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Email:</td>
<td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$email."</td>
</tr>
<tr>
<td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Asunto:</td>
<td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$subject."</td>
</tr>
<tr>
<td align='right' valign='top' style='border-top:1px solid #dfdfdf; border-bottom:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Mensaje:</td>
<td align='left' valign='top' style='border-top:1px solid #dfdfdf; border-bottom:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".nl2br($mensaje)."</td>
</tr>
</table>
</body>
</html>
";*/

$message = '<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="Content-Language" content="es">
  <title>Contacto MiRoperito</title>
</head>
<body style="margin:0; padding:10px; background-color:#f0f0f0; font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#333; line-height:1.2;">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table width="600" border="0" cellspacing="0" cellpadding="0" style="background:#ffffff; border:1px solid #dfdfdf; border-radius:6px; overflow:hidden; box-shadow:0 2px 5px rgba(0,0,0,0.05);">
          
          <!-- Logo -->
          <tr>
            <td align="center" style="padding:0;">
              <img src="https://miroperito.ar/images/logo/Logo-Mi-roperito.png" alt="Mi Roperito" style="width:100%; max-width:300px; display:block;">
            </td>
          </tr>

          <!-- Datos -->
          <tr>
            <td>
              <table width="100%" border="0" cellspacing="0" cellpadding="4" style="border-top:1px solid #dfdfdf; font-size:13px; line-height:1.3;">
                <tr>
                  <td width="30%" align="right" style="font-weight:bold; padding:4px;">Nombre:</td>
                  <td style="padding:4px;">'.$nombre.'</td>
                </tr>
                <tr>
                  <td align="right" style="font-weight:bold; border-top:1px solid #eee; padding:4px;">Email:</td>
                  <td style="border-top:1px solid #eee; padding:4px;">'.$email.'</td>
                </tr>
                <tr>
                  <td align="right" style="font-weight:bold; border-top:1px solid #eee; padding:4px;">Asunto:</td>
                  <td style="border-top:1px solid #eee; padding:4px;">'.$subject.'</td>
                </tr>
                <tr>
                  <td align="right" valign="top" style="font-weight:bold; border-top:1px solid #eee; border-bottom:1px solid #eee; padding:4px;">Mensaje:</td>
                  <td style="border-top:1px solid #eee; border-bottom:1px solid #eee; padding:4px;">'.nl2br($mensaje).'</td>
                </tr>
              </table>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>';

$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPAuth = true;
//habilitar debug
//$mail->SMTPDebug = 3;

if($smtpSecure!=""){
  $mail->SMTPSecure = $smtpSecure;
}
$mail->Port = $smtpPort;

$mail->IsHTML(true); 
$mail->CharSet = "utf-8";
$mail->Host = $smtpHost;
$mail->Username = $smtpUsuario;
$mail->Password = $smtpClave;
$mail->From = $fromEmail;
$mail->FromName = $fromName;
$mail->AddReplyTo($email, $nombre);
$mail->AddAddress($emailSucursal);
//$mail->AddAddress("axelbritzius@gmail.com");
$mensaje = $message;
$mail->Subject = "Formulario de Contacto MiRoperito"; 
$mensajeHtml = nl2br($mensaje);
$mail->Body = "{$mensajeHtml} <br /><br />"; 
$mail->AltBody = "{$mensaje} \n\n"; 
  
$mail->Send();

Database::disconnect();

echo json_encode(['success' => true, 'message' => 'Mensaje enviado correctamente.']);
exit;
