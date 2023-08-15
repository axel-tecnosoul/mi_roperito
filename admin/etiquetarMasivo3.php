<?php
require("config.php");
if(empty($_SESSION['user']['id_perfil'])){
  header("Location: index.php");
  die("Redirecting to index.php"); 
}
require 'database.php';
require('vendor/fpdf/fpdf.php');

$id = null;
if ( !empty($_GET['id'])) {
  $id = $_REQUEST['id'];
}

if ( null==$id ) {
  header("Location: listarProductos.php");
}

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$array = explode(',', $_GET['id']);
class PDF extends FPDF{
  function etiqueta($id, $pdo, $array){
    
    $this->AliasNbPages(); //muestra la pagina / y total de paginas
    $this->SetDrawColor(0, 0, 0); //colorBorde
    $this->SetAutoPageBreak(false);

    foreach ($array as $value)	{
      $sql = "SELECT `codigo`, `descripcion`, `precio`, `cb` FROM `productos` WHERE id = ? ";
      $q = $pdo->prepare($sql);
      $q->execute(array($value));
      $data = $q->fetch(PDO::FETCH_ASSOC);
      
      $nombre = $data['descripcion'];
      $codigo = $data['codigo'];
      $precio = $data['precio'];
      $precio = number_format($precio,2);
      $cb = $data['cb'];
      // Generar la URL de la imagen
      $url = 'barcode.php?codetype=Code39&size=50&text=' . $cb . '&print=true';

      // Inicializar cURL
      $ch = curl_init();

      // Establecer las opciones de cURL
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      // Ejecutar la solicitud y obtener el contenido de la imagen
      $imageData = curl_exec($ch);

      // Cerrar la conexión cURL
      curl_close($ch);

      // Guardar la imagen en un archivo
      $filename = 'barcode.jpg';
      file_put_contents($filename, $imageData);

      $this->Cell(0);  // mover a la derecha
      $this->SetFont('Arial', 'B', 12);
      $this->Image($filename,0,13,32);
      $this->Ln(1);

      $this->Cell(0);  // mover a la derecha
      $this->SetFont('Arial', '', 6);
      $this->Cell(0, 0, utf8_decode($codigo));
      $this->Cell(0, 0, utf8_decode($precio));
      $this->Ln(1);

    }
  }
}
Database::disconnect();

$pdf = new PDF();
$pdf->addPage();
$pdf->etiqueta($id, $pdo, $array);
$i = 0;

$i = $i + 1;
/* TABLA */



$pdf->Output('etiqueta.pdf', 'I');//nombreDescarga, Visor(I->visualizar - D->descargar)
?>