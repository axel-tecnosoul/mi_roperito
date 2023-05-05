<?php
require("config.php");
require 'database.php';
require 'funciones.php';

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = "SELECT id, subtotal, id_modalidad, deuda_proveedor FROM canjes_detalle WHERE deuda_proveedor < 1";                             
$q = $pdo->prepare($sql);
$q->execute();
$canje_detalle = $q->fetchAll(PDO::FETCH_ASSOC);
foreach ($canje_detalle as $data){
    $forma_pago = 1;
    $modalidad = $data['id_modalidad'];
    $subtotal = $data['subtotal'];
    $deuda_proveedor=calcularDeudaProveedor($forma_pago,$modalidad,$subtotal);

    $id = $data['id'];
    $sql = "UPDATE canjes_detalle set deuda_proveedor = ? WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($deuda_proveedor, $id));
}
$pdo = Database::disconnect();
?>