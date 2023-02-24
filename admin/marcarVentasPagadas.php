<?php
require("config.php");
if(empty($_SESSION['user'])){
  header("Location: index.php");
  die("Redirecting to index.php"); 
}
require 'database.php';

$id = null;
if ( !empty($_GET['id'])) {
  $id = $_REQUEST['id'];
}

if ( null==$id ) {
  header("Location: listarPagosPendientes.php");
}

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$array = explode(',', $_GET['id']);
foreach ($array as $value)	{
  $sql = "UPDATE ventas_detalle set pagado = 1, fecha_hora_pago = NOW(), caja_egreso = ?, id_forma_pago = ?, id_almacen = ? WHERE id = ?";
  $q = $pdo->prepare($sql);
  $q->execute(array($_POST["tipo_caja"], $_POST["id_forma_pago"], $_POST["id_almacen"],$value));
  /*$q->debugDumpParams();
  echo "<br><br>Afe: ".$q->rowCount();*/
}
  
Database::disconnect();
  
header("Location: listarPagosPendientes.php");

?>