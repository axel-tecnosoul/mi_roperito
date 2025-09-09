<?php 
    require("admin/config.php");
    require("admin/database.php");

    require("admin/PHPMailer/class.phpmailer.php");
    require("admin/PHPMailer/class.smtp.php");

    $nombre  = $_POST["nombre"] ?? '';
    $email   = $_POST["email"] ?? '';
    $mensaje = $_POST["mensaje"] ?? '';
    $subject = $_POST["asunto"] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($nombre) > 100 || strlen($subject) > 150 || strlen($mensaje) > 1000 ||
        $nombre !== strip_tags($nombre) || $subject !== strip_tags($subject) || $mensaje !== strip_tags($mensaje)) {
        header("Location: index.php");
        exit;
    }

    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "INSERT INTO `contactos`(`fecha_hora`, `nombre`, `email`, `asunto`, `mensaje`) VALUES (now(),?,?,?,?)";
    $q = $pdo->prepare($sql);
    $q->execute(array($nombre,$email,$subject,$mensaje));
	
	$message = "
	<html>
	<head>
	<title>Contacto MiRoperito</title>
	</head>
	<body>
	<table width='50%' border='0' align='center' cellpadding='0' cellspacing='0'>
	<tr>
	<td colspan='2' align='center' valign='top'><img style=' margin-top: 15px; ' src='https://miroperito.ar/images/logo/Logo-Mi-roperito.png' ></td>
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
	";
	
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;

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
        $mail->AddAddress("vende@miroperito.ar");
	$mensaje = $message;
	$mail->Subject = "Formulario de Contacto MiRoperito"; 
	$mensajeHtml = nl2br($mensaje);
	$mail->Body = "{$mensajeHtml} <br /><br />"; 
	$mail->AltBody = "{$mensaje} \n\n"; 
		
	$mail->Send();

	Database::disconnect();		
	
	header("Location: index.php");
?>
