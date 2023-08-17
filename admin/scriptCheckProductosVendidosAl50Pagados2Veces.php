<?php
require("config.php");
require 'database.php';
require 'funciones.php';

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$modoDebug=0;
$pdo->beginTransaction();

//3,9,51,73,74,83,84,92,94,102

//VENTAS
//$sql = "SELECT vd.id AS id_venta_detalle,vd.deuda_proveedor,p.id_proveedor,v.tipo_comprobante,v.fecha_hora,vd.fecha_hora_pago FROM ventas v INNER JOIN ventas_detalle vd ON vd.id_venta=v.id INNER JOIN productos p ON vd.id_producto=p.id WHERE vd.id_modalidad=50 AND vd.pagado=1 AND v.anulada=0 AND DATE(fecha_hora_pago)='2023-08-11' AND DATE(fecha_hora)<'2023-07-10'";// and v.fecha_hora<DATE_SUB(NOW(), INTERVAL 1 MONTH)
$sql="SELECT vd.id AS id_venta_detalle,vd.deuda_proveedor,p.id_proveedor,vd.fecha_hora_pago,v.fecha_hora,DATEDIFF(DATE(vd.fecha_hora_pago),DATE(v.fecha_hora)) AS diferencia_dias FROM ventas_detalle vd INNER JOIN ventas v ON vd.id_venta=v.id INNER JOIN productos p ON vd.id_producto=p.id WHERE vd.id_modalidad=50 AND vd.fecha_hora_pago IS NOT NULL AND DATEDIFF(DATE(vd.fecha_hora_pago),DATE(v.fecha_hora))>32 AND DATE(vd.fecha_hora_pago)>='2023-08-11' ORDER BY vd.fecha_hora_pago ASC";
$q = $pdo->prepare($sql);
$q->execute();

//if ($modoDebug==1) {
  echo htmlspecialchars($sql)."<br><br>";
//}

$aProveedores=[];
$aVentas=[];
$aCanjes=[];
$ok=$ok2=0;
foreach($q->fetchAll(PDO::FETCH_ASSOC) as $row){
  var_dump($row);
  $aProveedores[]=$row["id_proveedor"];
  $aVentas[]=$row["id_venta_detalle"];
  $ok2++;

  $sql2="SELECT credito FROM proveedores WHERE id=".$row["id_proveedor"];
  $q2 = $pdo->prepare($sql2);
  $q2->execute();
  $data=$q2->fetch(PDO::FETCH_ASSOC);
  var_dump($data["credito"]);

  $sql = "UPDATE proveedores set credito = credito - ? where id = ?";
  $q2 = $pdo->prepare($sql);
  $q2->execute(array($row["deuda_proveedor"],$row["id_proveedor"]));
  $afe=$q2->rowCount();

  //if ($modoDebug==1) {
    $q2->debugDumpParams();
    echo "<br><br>Afe: ".$afe;
    echo "<br><br>";
  //}

  $sql2="SELECT credito FROM proveedores WHERE id=".$row["id_proveedor"];
  $q2 = $pdo->prepare($sql2);
  $q2->execute();
  $data=$q2->fetch(PDO::FETCH_ASSOC);
  var_dump($data["credito"]);

  if($afe==1){

    $sql2="SELECT fecha_hora_pago FROM ventas_detalle WHERE id=".$row["id_venta_detalle"];
    $q2 = $pdo->prepare($sql2);
    $q2->execute();
    $data=$q2->fetch(PDO::FETCH_ASSOC);
    var_dump($data["fecha_hora_pago"]);

    $sql = "UPDATE ventas_detalle set fecha_hora_pago = NULL where id = ?";
    $q2 = $pdo->prepare($sql);
    $q2->execute(array($row["id_venta_detalle"]));
    $afe=$q2->rowCount();

    if($afe==1){
      $ok++;
    }

    //if ($modoDebug==1) {
      $q2->debugDumpParams();
      echo "<br><br>Afe: ".$q2->rowCount();
      echo "<br><br>";
    //}

    $sql2="SELECT fecha_hora_pago FROM ventas_detalle WHERE id=".$row["id_venta_detalle"];
    $q2 = $pdo->prepare($sql2);
    $q2->execute();
    $data=$q2->fetch(PDO::FETCH_ASSOC);
    var_dump($data["fecha_hora_pago"]);

  }
}

//CANJES
//$sql = "SELECT cd.id AS id_canje_detalle,cd.deuda_proveedor,p.id_proveedor,c.fecha_hora,cd.fecha_hora_pago FROM canjes c INNER JOIN canjes_detalle cd ON cd.id_canje=c.id INNER JOIN productos p ON cd.id_producto=p.id WHERE cd.id_modalidad=50 AND cd.pagado=1 AND c.anulado=0 AND DATE(fecha_hora_pago)='2023-08-11' AND DATE(fecha_hora)<'2023-07-10'";//WHERE p.id_proveedor=667;
$sql="SELECT cd.id AS id_canje_detalle,cd.deuda_proveedor,p.id_proveedor,cd.fecha_hora_pago,v.fecha_hora,DATEDIFF(DATE(cd.fecha_hora_pago),DATE(v.fecha_hora)) AS diferencia_dias FROM canjes_detalle cd INNER JOIN canjes v ON cd.id_canje=v.id INNER JOIN productos p ON cd.id_producto=p.id WHERE cd.id_modalidad=50 AND cd.fecha_hora_pago IS NOT NULL AND DATEDIFF(DATE(cd.fecha_hora_pago),DATE(v.fecha_hora))>32 AND DATE(cd.fecha_hora_pago)!='2023-06-13' ORDER BY cd.fecha_hora_pago ASC;";
$q = $pdo->prepare($sql);
$q->execute();
//$ok=$ok2=0;

//if ($modoDebug==1) {
  echo $sql."<br><br>";
//}

foreach($q->fetchAll(PDO::FETCH_ASSOC) as $row){
  var_dump($row);
  $aProveedores[]=$row["id_proveedor"];
  $aCanjes[]=$row["id_canje_detalle"];
  $ok2++;

  $sql2="SELECT credito FROM proveedores WHERE id=".$row["id_proveedor"];
  $q2 = $pdo->prepare($sql2);
  $q2->execute();
  $data=$q2->fetch(PDO::FETCH_ASSOC);
  var_dump($data["credito"]);

  $sql = "UPDATE proveedores set credito = credito - ? where id = ?";
  $q2 = $pdo->prepare($sql);
  $q2->execute(array($row["deuda_proveedor"],$row["id_proveedor"]));
  $afe=$q2->rowCount();

  //if ($modoDebug==1) {
    $q2->debugDumpParams();
    echo "<br><br>Afe: ".$afe;
    echo "<br><br>";
  //}

  $sql2="SELECT credito FROM proveedores WHERE id=".$row["id_proveedor"];
  $q2 = $pdo->prepare($sql2);
  $q2->execute();
  $data=$q2->fetch(PDO::FETCH_ASSOC);
  var_dump($data["credito"]);

  if($afe==1){

    $sql2="SELECT fecha_hora_pago FROM canjes_detalle WHERE id=".$row["id_canje_detalle"];
    $q2 = $pdo->prepare($sql2);
    $q2->execute();
    $data=$q2->fetch(PDO::FETCH_ASSOC);
    var_dump($data["fecha_hora_pago"]);

    $sql = "UPDATE canjes_detalle set fecha_hora_pago = NULL where id = ?";
    $q2 = $pdo->prepare($sql);
    $q2->execute(array($row["id_canje_detalle"]));
    $afe=$q2->rowCount();

    if($afe==1){
      $ok++;
    }

    //if ($modoDebug==1) {
      $q2->debugDumpParams();
      echo "<br><br>Afe: ".$q2->rowCount();
      echo "<br><br>";
    //}

    $sql2="SELECT fecha_hora_pago FROM canjes_detalle WHERE id=".$row["id_canje_detalle"];
    $q2 = $pdo->prepare($sql2);
    $q2->execute();
    $data=$q2->fetch(PDO::FETCH_ASSOC);
    var_dump($data["fecha_hora_pago"]);

  }
}

echo "Proveedores: ".implode(",",array_unique($aProveedores))."<br><br>";
echo "Ventas: ".implode(",",array_unique($aVentas))."<br><br>";
echo "Canjes: ".implode(",",array_unique($aCanjes))."<br><br>";


//$pdo->rollBack();
//if ($modoDebug==1) {
  //$pdo->rollBack();
  echo "ok==ok2<br>";
  echo "$ok==$ok2<br>";
//}
if($ok==$ok2){
  if ($modoDebug==1) {
    $pdo->rollBack();
    echo "Todo bien. Hacemos COMMIT";
  }else{
    $pdo->commit();
    //echo "Hacemos COMMIT";
  }
}else{
  $pdo->rollBack();
  if ($modoDebug==1) {
    echo "Hacemos rollback";
  }
}

$pdo = Database::disconnect();
?>