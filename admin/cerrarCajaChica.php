<?php
require("config.php");
if(empty($_SESSION['user'])){
  header("Location: index.php");
  die("Redirecting to index.php"); 
}
require 'database.php';

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id_usuario=$_SESSION['user']["id"];
$id_almacen=$_GET['id_almacen'];

$sql = "INSERT INTO cierres_caja (id_usuario, id_almacen, tipo_caja) VALUES (?,?,'Chica')";
$q = $pdo->prepare($sql);
$q->execute(array($id_usuario,$id_almacen));
$idCierreCaja = $pdo->lastInsertId();

$sql = "UPDATE ventas set id_cierre_caja = $idCierreCaja WHERE id_almacen = $id_almacen AND id_cierre_caja = 0";
$q = $pdo->prepare($sql);
$q->execute(array());

//$sql = "UPDATE egresos_caja_chica set id_cierre_caja = $idCierreCaja WHERE id_cierre_caja = 0";
$sql = "UPDATE movimientos_caja set id_cierre_caja = $idCierreCaja WHERE tipo_caja='Chica' AND id_almacen = $id_almacen AND id_cierre_caja = 0";
$q = $pdo->prepare($sql);
$q->execute(array());
  
Database::disconnect();
  
header("Location: listarCajaChica.php");

?>