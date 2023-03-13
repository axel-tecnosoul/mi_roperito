<?php 
session_start(); 
if(empty($_SESSION['user'])){
	header("Location: index.php");
	die("Redirecting to index.php"); 
}
include 'database.php';
$pdo = Database::connect();

$desde=$_GET["desde"];
$filtroDesdePagoProv=$filtroDesde="";
if($desde!=""){
  $filtroDesde="AND DATE(fecha_hora)>='$desde'";
  $filtroDesdePagoProv="AND DATE(fecha_hora_pago)>='$desde'";
}
$hasta=$_GET["hasta"];
$filtroHastaPagoProv=$filtroHasta="";
if($hasta!=""){
  $filtroHasta="AND DATE(fecha_hora)<='$hasta'";
  $filtroHastaPagoProv="AND DATE(fecha_hora_pago)<='$hasta'";
}
$forma_pago=$_GET["forma_pago"];
$filtroFormaPago="";
if($forma_pago!=""){
  $filtroFormaPago="AND id_forma_pago IN ($forma_pago)";
}
$id_almacen=$_GET["id_almacen"];
$filtroAlmacen="";
if($id_almacen!=0){
  $filtroAlmacen="AND a.id IN ($id_almacen)";
}
$motivo=$_GET["motivo"];
//var_dump($motivo);
$filtroMotivo="";
if($motivo!=""){
  $filtroMotivo="AND id_motivo IN ($motivo)";
}
//$tipo_comprobante=$_GET["tipo_comprobante"];

//como los pagos a proveedores se hacen solamente en efectivo por el momento debemos mostrar esta consulta solamente si se elije "efectivo" entre las formas de pago
$id_forma_pago_efectivo=1;
$id_motivo_pago_proveedoras=22;

$mostrarPagoProveedores=0;
//var_dump(explode(",",$forma_pago));
//var_dump(in_array($id_forma_pago_efectivo,explode(",",$forma_pago)));

if(
  ($forma_pago=="" or in_array($id_forma_pago_efectivo,explode(",",$forma_pago))) and 
  ($motivo=="" or in_array($id_motivo_pago_proveedoras,explode(",",$motivo))))
  {
  $mostrarPagoProveedores=1;
}
//var_dump($mostrarPagoProveedores);


$aCaja=[];
//if($desde<=$hasta and $id_almacen!=0){
if($desde<=$hasta){

  $wherePagoProv=" $filtroAlmacen";
  $where=$wherePagoProv." $filtroMotivo $filtroFormaPago";

  $filtroHastaSaldoAnterior="AND DATE(fecha_hora)<'$desde'";
  $filtroHastaSaldoAnteriorPagoProv="AND DATE(fecha_hora_pago)<'$desde'";

  //obtenemos los ingresos registrados por los usuarios para el saldo anterior
  $sql = "SELECT SUM(mc.monto) AS ingresos_externos FROM movimientos_caja mc inner join almacenes a on a.id = mc.id_almacen inner join forma_pago fp on fp.id = mc.id_forma_pago WHERE anulado=0 AND tipo_caja='Grande' AND tipo_movimiento='Ingreso' $where $filtroHastaSaldoAnterior";
  $q = $pdo->prepare($sql);
  //echo $sql;
  $q->execute(array());
  $data = $q->fetch(PDO::FETCH_ASSOC);
  $ingresos_externos=$data["ingresos_externos"];
  if(is_null($ingresos_externos)){
    $ingresos_externos=0;
  }

  //obtenemos las salidas de caja chica a caja grande para el saldo anterior
  $sql2 = "SELECT SUM(mc.monto) AS ingresos_caja_chica FROM movimientos_caja mc inner join almacenes a on a.id = mc.id_almacen inner join forma_pago fp on fp.id = mc.id_forma_pago WHERE anulado=0 AND tipo_caja='Chica' AND tipo_movimiento='Egreso' AND id_motivo=1 $where $filtroHastaSaldoAnterior";
  $q2 = $pdo->prepare($sql2);
  //echo $sql2;
  $q2->execute(array());
  $data2 = $q2->fetch(PDO::FETCH_ASSOC);
  $ingresos_caja_chica=$data2["ingresos_caja_chica"];
  if(is_null($ingresos_caja_chica)){
    $ingresos_caja_chica=0;
  }

  //obtenemos los egresos de caja grande para el saldo anterior
  $sql3 = "SELECT SUM(mc.monto) AS egresos_caja_grande FROM movimientos_caja mc inner join almacenes a on a.id = mc.id_almacen inner join forma_pago fp on fp.id = mc.id_forma_pago WHERE anulado=0 AND tipo_caja='Grande' AND tipo_movimiento='Egreso' $where $filtroHastaSaldoAnterior";
  $q3 = $pdo->prepare($sql3);
  //echo $sql;
  $q3->execute(array());
  $data3 = $q3->fetch(PDO::FETCH_ASSOC);
  $egresos_caja_grande=$data3["egresos_caja_grande"];
  if(is_null($egresos_caja_grande)){
    $egresos_caja_grande=0;
  }

  $total_pago_proveedores=0;
  //if($filtroMotivo==""){
    if($mostrarPagoProveedores==1){
      //obtenemos los pagos a proveedores que se hicieron desde esta caja para el saldo anterior
      $sql4 = " SELECT SUM(deuda_proveedor) AS total_pago_proveedores FROM ventas_detalle vd INNER JOIN ventas v ON vd.id_venta=v.id INNER JOIN almacenes a ON v.id_almacen=a.id WHERE pagado=1 AND caja_egreso='Grande' $wherePagoProv $filtroHastaSaldoAnteriorPagoProv";
      $q4 = $pdo->prepare($sql4);
      //echo $sql4."<br>";
      $q4->execute(array());
      $data4 = $q4->fetch(PDO::FETCH_ASSOC);
      $total_pago_proveedores=$data4["total_pago_proveedores"];
      if(is_null($total_pago_proveedores)){
        $total_pago_proveedores=0;
      }
    }
  //}

  $saldo_anterior=$ingresos_externos+$ingresos_caja_chica-$egresos_caja_grande-$total_pago_proveedores;

  $modo_debug=0;
  if($modo_debug==1){
    $detalle="ingresos_externos: $ingresos_externos<br>ingresos_caja_chica: $egresos_caja_grande<br>egresos_caja_grande: $ingresos_caja_chica<br>";
    $detalle.="data[ingresos_externos]: $data[ingresos_externos]<br>data2[ingresos_caja_chica]: $data2[ingresos_caja_chica]<br>data3[egresos_caja_grande]: $data3[egresos_caja_grande]<br>";
    $aCaja[]=[
      "id_venta"=>0,
      "fecha_hora"=>date("d-m-Y H:i",strtotime($desde)),
      "detalle"=>$detalle,
      "forma_pago"=>"",
      "credito"=>0,
      "debito"=>0,
      "saldo"=>0,
      "detalle_productos"=>"",
    ];
  }

  //echo $sql;
  $aCaja[]=[
    "id_venta"=>0,
    "fecha_hora"=>date("d-m-Y H:i",strtotime($desde)),
    "detalle"=>"Saldo anterior",
    "forma_pago"=>"",
    "credito"=>0,
    "debito"=>0,
    "saldo"=>$saldo_anterior,
    "detalle_productos"=>"",
  ];

  //obtenemos los egresos de caja chica a caja grande
  $sql = " SELECT mc.id AS id_egreso, a.almacen, date_format(mc.fecha_hora,'%d/%m/%Y %H:%i') AS fecha_hora_formatted,mc.fecha_hora, fp.forma_pago,mc.monto AS total,msc.motivo,mc.detalle,mc.id_cierre_caja,mc.tipo_movimiento FROM movimientos_caja mc inner join almacenes a on a.id = mc.id_almacen inner join forma_pago fp on fp.id = mc.id_forma_pago INNER JOIN motivos_salidas_caja msc ON mc.id_motivo=msc.id WHERE anulado=0 AND tipo_caja='Chica' AND tipo_movimiento='Egreso' AND id_motivo=1 $where $filtroDesde $filtroHasta";
  foreach ($pdo->query($sql) as $row) {
    //$iconVer="<a href='verMovimientoCajaGrande.php?id=".$row["id_egreso"]."' target='_blank' class='badge badge-primary'><i class='fa fa-eye' aria-hidden='true'></i></a>";
    $iconVer="<span data-id='".$row["id_egreso"]."' data-tipo='movimiento' class='ver badge badge-primary'><i class='fa fa-eye' aria-hidden='true'></i></span>";
    $iconEdit="";

    $cerrado="<i class='fa fa-lock' aria-hidden='true'></i> ";
    if($row["id_cierre_caja"]==0){
      $iconEdit="<a href='modificarMovimientoCajaChica.php?id=".$row["id_egreso"]."' target='_blank' class='badge badge-secondary'><i class='fa fa-pencil-square-o' aria-hidden='true'></i></a>";
      $cerrado="<i class='fa fa-unlock' aria-hidden='true'></i> ";
    }
    
    $aCaja[]=[
      "id_egreso"=>$row["id_egreso"],
      "fecha_hora"=>date("d-m-Y H:i",strtotime($row["fecha_hora"])),
      "detalle"=>$cerrado.$iconVer.$iconEdit." Ingreso ID ".$row["id_egreso"],
      "forma_pago"=>$row["forma_pago"],
      "credito"=>$row["total"],
      "debito"=>0,
      "saldo"=>0,
      "detalle_productos"=>$row["motivo"].": ".$row["detalle"],
    ];
  }

  //if($filtroMotivo==""){
    if($mostrarPagoProveedores==1){
      //obtenemos las pagos a proveedoras
      $sql = " SELECT p.id_proveedor,fecha_hora_pago,CONCAT(apellido,' ',nombre) AS proveedor,SUM(deuda_proveedor) AS suma_deuda_proveedor,GROUP_CONCAT('+',vd.cantidad,' ',p.descripcion,': $',FORMAT(vd.deuda_proveedor,2,'de_DE') SEPARATOR '<br>') AS detalle_productos FROM ventas_detalle vd INNER JOIN ventas v ON vd.id_venta=v.id INNER JOIN productos p ON vd.id_producto=p.id INNER JOIN proveedores pr ON p.id_proveedor=pr.id INNER JOIN almacenes a ON v.id_almacen=a.id WHERE pagado=1 AND caja_egreso='Grande' $wherePagoProv $filtroDesdePagoProv $filtroHastaPagoProv GROUP BY p.id_proveedor";
      //echo $sql."<br>";
      foreach ($pdo->query($sql) as $row) {
        $aCaja[]=[
          "id_proveedor"=>$row["id_proveedor"],
          "fecha_hora"=>date("d-m-Y H:i",strtotime($row["fecha_hora_pago"])),
          "detalle"=>"Pago a proveedores: ".$row["proveedor"]."",
          "forma_pago"=>"Efectivo",
          "credito"=>0,
          "debito"=>$row["suma_deuda_proveedor"],
          "saldo"=>0,
          "detalle_productos"=>$row["detalle_productos"],
        ];
      }
    }
  //}

  //obtenemos los movimientos de caja grande registrados por los usuarios
  $sql = " SELECT mc.id AS id_movimiento, a.almacen, date_format(mc.fecha_hora,'%d/%m/%Y %H:%i') AS fecha_hora_formatted,mc.fecha_hora, fp.forma_pago,mc.monto AS total,msc.motivo,mc.detalle,mc.id_cierre_caja,mc.tipo_movimiento FROM movimientos_caja mc inner join almacenes a on a.id = mc.id_almacen inner join forma_pago fp on fp.id = mc.id_forma_pago INNER JOIN motivos_salidas_caja msc ON mc.id_motivo=msc.id WHERE anulado=0 AND tipo_caja='Grande' $where $filtroDesde $filtroHasta";
  //echo $sql;
  foreach ($pdo->query($sql) as $row) {
    
    //$iconVer="<a href='verMovimientoCajaGrande.php?id=".$row["id_movimiento"]."' target='_blank' class='badge badge-primary'><i class='fa fa-eye' aria-hidden='true'></i></a>";
    $iconVer="<span data-id='".$row["id_movimiento"]."' data-tipo='movimiento' class='ver badge badge-primary'><i class='fa fa-eye' aria-hidden='true'></i></span>";
    $iconEdit="";
    $cerrado="<i class='fa fa-lock' aria-hidden='true'></i> ";
    if($row["id_cierre_caja"]==0){
      $iconEdit="<a href='modificarMovimientoCajaGrande.php?id=".$row["id_movimiento"]."' target='_blank' class='badge badge-secondary'><i class='fa fa-pencil-square-o' aria-hidden='true'></i></a>";
      $cerrado="<i class='fa fa-unlock' aria-hidden='true'></i> ";
    }

    if($row["tipo_movimiento"]=="Ingreso"){
      $aCaja[]=[
        "id_movimiento"=>$row["id_movimiento"],
        "fecha_hora"=>date("d-m-Y H:i",strtotime($row["fecha_hora"])),
        "detalle"=>$cerrado.$iconVer.$iconEdit." Mov. ID ".$row["id_movimiento"]."",
        "forma_pago"=>$row["forma_pago"],
        "credito"=>$row["total"],
        "debito"=>0,
        "saldo"=>0,
        "detalle_productos"=>$row["motivo"].": ".$row["detalle"],
      ];
    }else{
      $aCaja[]=[
        "id_movimiento"=>$row["id_movimiento"],
        "fecha_hora"=>date("d-m-Y H:i",strtotime($row["fecha_hora"])),
        "detalle"=>$cerrado.$iconVer.$iconEdit." Mov. ID ".$row["id_movimiento"]."",
        "forma_pago"=>$row["forma_pago"],
        "credito"=>0,
        "debito"=>$row["total"],
        "saldo"=>0,
        "detalle_productos"=>$row["motivo"].": ".$row["detalle"],
      ];
    }
  }

  Database::disconnect();

  function date_compare($a, $b){
      $t1 = strtotime($a['fecha_hora']);
      $t2 = strtotime($b['fecha_hora']);
      return $t1 - $t2;
  }

  usort($aCaja, 'date_compare');

  foreach ($aCaja as $key => $value) {
    $aCaja[$key]['fecha_hora']=date("d/m/Y H:i",strtotime($value['fecha_hora']));
  }
}

echo json_encode($aCaja);