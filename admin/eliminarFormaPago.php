<?php
require("config.php");
if(empty($_SESSION['user']['id_perfil'])){
  header("Location: index.php");
  die("Redirecting to index.php"); 
}
require 'database.php';

$id = null;
if ( !empty($_GET['id'])) {
  $id = $_REQUEST['id'];
}

if ( null==$id ) {
  header("Location: listarFormasPago.php");
}

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
$sql = "DELETE from forma_pago WHERE id = ?";
$q = $pdo->prepare($sql);
$q->execute(array($id));

Database::disconnect();
  
header("Location: listarFormasPago.php");
	
?>