<?php
require('admin/config.php');
require('admin/database.php');

require('admin/PHPMailer/class.phpmailer.php');
require('admin/PHPMailer/class.smtp.php');

header('Content-Type: application/json');

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function jsonResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

function turnoDisponible($pdo, $idAlmacen, $fecha, $hora){
    $diaSemana = (int)date('N', strtotime($fecha)) - 1;
    $sqlFranjas = 'SELECT hora_inicio,hora_fin,frecuencia_minutos,bloqueo_minutos FROM almacenes_horarios WHERE id_almacen = ? AND dia_semana = ? ORDER BY hora_inicio';
    $q = $pdo->prepare($sqlFranjas);
    $q->execute([$idAlmacen,$diaSemana]);
    $franjas = $q->fetchAll(PDO::FETCH_ASSOC);

    $slots = [];
    foreach ($franjas as $franja) {
        $inicio = new DateTime($franja['hora_inicio']);
        $fin    = new DateTime($franja['hora_fin']);
        $freq   = (int)$franja['frecuencia_minutos'];
        $bloq   = (int)$franja['bloqueo_minutos'];
        for ($t = clone $inicio; $t < $fin; $t->modify('+'.$freq.' minutes')) {
            $slots[] = ['hora' => $t->format('H:i'), 'bloqueo' => $bloq, 'frecuencia' => $freq];
        }
    }

    $slotEncontrado = null;
    foreach ($slots as $slot) {
        if ($slot['hora'] == $hora) {
            $slotEncontrado = $slot;
            break;
        }
    }
    if (!$slotEncontrado) {
        return false;
    }

    $sqlTurnos = 'SELECT hora FROM turnos WHERE id_almacen = ? AND fecha = ? AND id_estado = 1 FOR UPDATE';
    $q = $pdo->prepare($sqlTurnos);
    $q->execute([$idAlmacen,$fecha]);
    $reservas = $q->fetchAll(PDO::FETCH_COLUMN);

    $bloqueados = [];
    foreach ($reservas as $res) {
        $r = new DateTime($res);
        foreach ($franjas as $franja) {
            $inicioFr = new DateTime($franja['hora_inicio']);
            $finFr    = new DateTime($franja['hora_fin']);
            if ($r >= $inicioFr && $r < $finFr) {
                $freq = (int)$franja['frecuencia_minutos'];
                $bloq = (int)$franja['bloqueo_minutos'];
                $inicioBloq = (clone $r)->modify('-'.$bloq.' minutes');
                $finBloq    = (clone $r)->modify('+'.$bloq.' minutes');
                for ($t = clone $r; $t < $finBloq; $t->modify('+'.$freq.' minutes')) {
                    $bloqueados[$t->format('H:i')] = true;
                }
                for ($t = clone $r; $t > $inicioBloq; $t->modify('-'.$freq.' minutes')) {
                    $bloqueados[$t->format('H:i')] = true;
                }
                break;
            }
        }
    }

    return !isset($bloqueados[$hora]);
}

function rateLimited($ip, $limit = 5, $windowSeconds = 60) {
    $file = sys_get_temp_dir() . '/turno_requests.log';
    $now = time();
    $entries = [];
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) {
            $entries = $data;
        }
    }
    // Remove old entries
    $entries = array_filter($entries, function ($entry) use ($now, $windowSeconds) {
        return ($entry['time'] ?? 0) >= ($now - $windowSeconds);
    });
    $count = 0;
    foreach ($entries as $entry) {
        if (($entry['ip'] ?? '') === $ip) {
            $count++;
        }
    }
    // Log current request
    $entries[] = ['ip' => $ip, 'time' => $now];
    file_put_contents($file, json_encode($entries));
    return $count >= $limit;
}

function verifyRecaptcha($token, $secret, $ip) {
    if (!$token || !$secret) {
        return false;
    }
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = http_build_query(['secret' => $secret, 'response' => $token, 'remoteip' => $ip]);
    $opts = ['http' => ['method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $data]];
    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);
    if (!$response) {
        return false;
    }
    $result = json_decode($response, true);
    return $result['success'] ?? false;
}

$fecha = $_POST['fecha'] ?? '';
$hora  = $_POST['hora'] ?? '';
$fechaSolicitada = $fecha;
$horaSolicitada  = $hora;
$idAlmacen = $_POST['id_almacen'] ?? '';
$cantidad = $_POST['cantidad'] ?? '';
$dni      = $_POST['dni'] ?? '';
$nombre   = $_POST['nombre'] ?? '';
$email    = $_POST['email'] ?? '';
$telefono = $_POST['telefono'] ?? '';

$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

if (rateLimited($ip)) {
    Database::disconnect();
    jsonResponse(false, 'Demasiadas solicitudes desde esta IP. Intente nuevamente más tarde.');
}

if (!verifyRecaptcha($recaptchaResponse, $recaptchaSecretKey, $ip)) {
    Database::disconnect();
    jsonResponse(false, 'Verificación de reCAPTCHA fallida.');
}
$hoy = new DateTime('today');
$limite = new DateTime('+60 minutes');

$fechaDT = DateTime::createFromFormat('Y-m-d', $fechaSolicitada);
if (!$fechaDT || $fechaDT < $hoy) {
    Database::disconnect();
    jsonResponse(false, 'La fecha seleccionada no es válida.');
}

if ($fechaDT->format('Y-m-d') === $hoy->format('Y-m-d')) {
    $horaDT = DateTime::createFromFormat('H:i', $horaSolicitada);
    if (!$horaDT || $horaDT < $limite) {
        Database::disconnect();
        jsonResponse(false, 'La hora debe ser al menos 60 minutos posterior a la actual.');
    }
}

$errorDatos = (!filter_var($email, FILTER_VALIDATE_EMAIL) ||
               strlen($nombre) > 100 || strlen($dni) > 20 || strlen($telefono) > 20 ||
               $nombre !== strip_tags($nombre) || $dni !== strip_tags($dni) || $telefono !== strip_tags($telefono));
if ($errorDatos) {
    Database::disconnect();
    jsonResponse(false, 'Datos inválidos');
}

$pdo->beginTransaction();
if(!turnoDisponible($pdo, $idAlmacen, $fecha, $hora)){
    $pdo->rollBack();
    Database::disconnect();
    jsonResponse(false, 'Horario ocupado');
}

$sql = 'INSERT INTO `turnos`(`fecha_hora`,`id_almacen`, `cantidad`, `fecha`, `hora`, `dni`, `nombre`, `email`, `telefono`, `id_estado`) VALUES (now(),?,?,?,?,?,?,?,?,1)';
$q = $pdo->prepare($sql);
try {
    $q->execute([$idAlmacen,$cantidad,$fecha,$hora,$dni,$nombre,$email,$telefono]);
    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    Database::disconnect();
    jsonResponse(false, 'Error al generar turno');
}

  //var_dump($_POST);
  

  $sqlZon = "SELECT almacen,direccion FROM almacenes WHERE id = ?";
  $q = $pdo->prepare($sqlZon);
  $q->execute([$idAlmacen]);
  $fila = $q->fetch(PDO::FETCH_ASSOC);
  $almacen=$fila['almacen'];
  $direccion=$fila['direccion'];

	//$sucursal =$_POST['id_almacen'];
  $sucursal =$almacen;
	
	$message = "
	<html>
	<head>
	<title>Solicitud de Turno MiRoperito</title>
	</head>
	<body>
	<table width='50%' border='0' align='center' cellpadding='0' cellspacing='0'>
	<tr>
	<td colspan='2' align='center' valign='top'><img style=' margin-top: 15px;max-width: 100%; ' src='https://miroperito.ar/images/logo/Logo-Mi-roperito.png' ></td>
	</tr>
	<tr>
	<td width='50%' align='right'>&nbsp;</td>
	<td align='left'>&nbsp;</td>
	</tr>
	<tr>
	<td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Sucursal:</td>
	<td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$sucursal."</td>
	</tr>
	<tr>
	<td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Cantidad:</td>
	<td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$cantidad."</td>
	</tr>
	<tr>
	<td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Fecha:</td>
	<td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$fecha."</td>
	</tr>
	<tr>
	<td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Hora:</td>
	<td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$hora."</td>
	</tr>
	<tr>
	<td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>DNI:</td>
	<td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$dni."</td>
	</tr>
	<tr>
	<td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Nombre:</td>
	<td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$nombre."</td>
	</tr>
	<tr>
	<td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>E-Mail:</td>
	<td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$email."</td>
	</tr>
	<tr>
	<td align='right' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;'>Teléfono:</td>
	<td align='left' valign='top' style='border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;'>".$telefono."</td>
	</tr>
	</table>
	</body>
	</html>
	";

  //SELECT * FROM `turnos` WHERE DATE(fecha_hora)>="2023-03-02" AND fecha_hora<"2023-03-28 16:01";
	
  //$smtpHost = "c1971287.ferozo.com";
  //$smtpHost = "miroperito.ar";
  //$smtpHost = "tecnosoul.com.ar";
  $mail = new PHPMailer();
  $mail->IsSMTP();
  $mail->SMTPAuth = true;

  $modoDebug = 0;
  
  if($modoDebug==1 and $email=="axelbritzius@gmail.com"){
    $mail->SMTPDebug = 3;
  }
	/*$mail->Port = 465; 
	$mail->SMTPSecure = 'ssl';*/
  
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
  $mail->AddAddress($email);
	$mensaje = $message;
	$mail->Subject = "Solicitud de Turno MiRoperito"; 
	$mensajeHtml = nl2br($mensaje);
	$mail->Body = "{$mensajeHtml} <br /><br />"; 
	$mail->AltBody = "{$mensaje} \n\n"; 
		
	$ok=$mail->Send();

  if($modoDebug==1 and $email=="axelbritzius@gmail.com"){
    var_dump($ok);
    die();
  }

  //COMENTADO PARA SUBIR A PRODUCCION
  /*include_once("admin/funciones.php");
  require_once("admin/WABADatosApi.php");*/
  //global $API_URL;

  //confirmacion_turno: Gracias por acercarte a ofrecer tu ropa en MiRoperito. Tenés turno para el día {{fecha}} a las {{hora}}hs en {{direccion}}, {{almacen}}. Te esperamos!

  //recordatorio_turno: Hola! Desde MiRoperito queremos recordarte acerca del turno que tenés reservado para hoy a las {{hora}}hs en {{direccion}}, {{almacen}}. Si no podés asistir a tu turno y deseas cancelarlo, responde CANCELAR. Muchas gracias!

  /*$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $sqlZon = "SELECT almacen,direccion FROM almacenes WHERE id = ?";
  $q = $pdo->prepare($sqlZon);
  $q->execute([$sucursal]);
  $fila = $q->fetch(PDO::FETCH_ASSOC);
  $almacen=$fila['almacen'];*/

  //COMENTADO PARA SUBIR A PRODUCCION

  /*$url=$API_URL.$FROM_PHONE_NUMBER_ID."/messages";
  $tipoPeticion="POST";
  $opcionales=[
    "header"=>[
      'Content-Type: application/json',
      'Authorization: Bearer '.$ACCESS_TOKEN
    ],
    "parametros"=>[
      "messaging_product" => "whatsapp",
      "recipient_type"    => "individual",
      "to"                => $telefono,
      "type"              => "template",
      "template"          =>[
        //"name"=>"hello_world",
        "name"=>"confirmacion_turno",
        "language"=>[
          //"code"=> "en_US"
          "code"=> "es_AR"
        ],
        "components"=>[
          [
            "type"=> "body",
            "parameters"=>[
              [
                "type"=> "text",//fecha
                "text"=> date("d-m-Y",strtotime($fecha)),
              ],[
                "type"=> "text",//hora
                "text"=> $hora,
              ],[
                "type"=> "text",//direccion
                "text"=> $direccion,
              ],[
                "type"=> "text",//almacen
                "text"=> $almacen,
              ]
            ]
          ]
        ]
      ]
    ]
  ];*/
  //var_dump($opcionales);

  /*$json='{
    "to": "recipient_wa_id",
    "type": "template",
    "template": {
          "namespace": "your-namespace",
          "name": "your-template-name",
          "language": {
              "code": "your-language-and-locale-code",
              "policy": "deterministic"
          },
          "components": [{
              "type": "body",
              "parameters": [
                  {
                      "type": "text",
                      "text": "your-text-string"
                  },
                  {
                      "type": "currency",
                      "currency": {
                          "fallback_value": "$100.99",
                          "code": "USD",
                          "amount_1000": 100990
                      }
                  },
                  {
                      "type": "date_time",
                      "date_time" : {
                          "fallback_value": "February 25, 1977",
                          "day_of_week": 5,
                          "day_of_month": 25,
                          "year": 1977,
                          "month": 2,
                          "hour": 15,
                          "minute": 33
                      }
                  },
                  {
                  "type": "date_time",
                      "date_time" : {
                      "fallback_value": "February 25, 1977",
                      "timestamp": 1485470276
                      }
                  }
              ]
          }]
      }
  }';
  var_dump(json_decode($json,true));*/


  //COMENTADO PARA SUBIR A PRODUCCION

  /*$response=curl($url, $tipoPeticion, $ACCESS_TOKEN, $opcionales);
  var_dump($response);

  $response=curl($url, $tipoPeticion, $ACCESS_TOKEN, $opcionales);*/

  Database::disconnect();

  jsonResponse(true, 'Turno generado correctamente.');
?>
