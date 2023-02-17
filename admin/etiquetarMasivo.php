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
		header("Location: listarProductos.php");
	}
	
	$pdo = Database::connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	$array = explode(',', $_GET['id']);
	foreach ($array as $value)	{
		
		$sql = "SELECT `codigo`, `descripcion`, `precio`, `cb` FROM `productos` WHERE id = ? ";
		$q = $pdo->prepare($sql);
		$q->execute(array($value));
		$data = $q->fetch(PDO::FETCH_ASSOC);
		
		$nombre = $data['descripcion'];
		$codigo = $data['codigo'];
		$precio = $data['precio'];
		$cb = $data['cb'];
		
		echo "<center>".$nombre." (".$codigo.")</center>";
		echo "<center><img alt='testing' src='barcode.php?codetype=Code39&size=50&text=".$cb."&print=true'/></center>";
		echo "<center>$".number_format($precio,2)."</center>";
		echo "<br><br><br><br>";
	}
		
	Database::disconnect();
	
?>