<?php 
session_start(); 
if(empty($_SESSION['user'])){
	header("Location: index.php");
	die("Redirecting to index.php"); 
}
include 'database.php';
$pdo = Database::connect();

$desde=$_GET["desde"];
$filtroDesde="";
if($desde!=""){
  $filtroDesde="AND DATE(fecha_hora)>='$desde'";
}
$hasta=$_GET["hasta"];
$filtroHasta="";
if($hasta!=""){
  $filtroHasta="AND DATE(fecha_hora)<='$hasta'";
}
$forma_pago=$_GET["forma_pago"];
$filtroFormaPago="";
if($forma_pago!=0){
  $filtroFormaPago="AND id_forma_pago IN ($forma_pago)";
}
$id_almacen=$_GET["id_almacen"];
$filtroAlmacen="";
if($id_almacen!=0){
  $filtroAlmacen="AND id_almacen IN ($id_almacen)";
}
//$tipo_comprobante=$_GET["tipo_comprobante"];

$aCaja=[];
if($desde<=$hasta and $id_almacen!=0){

  $where=" $filtroFormaPago $filtroAlmacen";

  $whereVentas=$where." AND v.anulada = 0";

  //INICIO SALDO ANTERIOR

  $filtroHastaSaldoAnterior="AND DATE(fecha_hora)<'$desde'";

  //obtenemos las ventas para el saldo anterior
  $sql = "SELECT SUM(v.total_con_descuento) AS total_ventas FROM ventas v inner join almacenes a on a.id = v.id_almacen inner join forma_pago fp on fp.id = v.id_forma_pago WHERE 1 $whereVentas $filtroHastaSaldoAnterior";
  $q = $pdo->prepare($sql);
  //echo $sql;
  $q->execute(array());
  $data = $q->fetch(PDO::FETCH_ASSOC);
  $total_ventas=$data["total_ventas"];
  if(is_null($total_ventas)){
    $total_ventas=0;
  }

  //obtenemos los movimientos registrados como ingresos a caja chica para el saldo anterior
  $sql2 = "SELECT SUM(mc.monto) AS ingresos_externos FROM movimientos_caja mc inner join almacenes a on a.id = mc.id_almacen inner join forma_pago fp on fp.id = mc.id_forma_pago WHERE tipo_caja='Chica' AND  tipo_movimiento='Ingreso' $where $filtroHastaSaldoAnterior";
  $q2 = $pdo->prepare($sql2);
  //echo $sql2;
  $q2->execute(array());
  $data2 = $q2->fetch(PDO::FETCH_ASSOC);
  $ingresos_externos=$data2["ingresos_externos"];
  if(is_null($ingresos_externos)){
    $ingresos_externos=0;
  }

  //obtenemos los movimientos registrados como egresos de caja chica para el saldo anterior
  $sql3 = "SELECT SUM(mc.monto) AS egresos_caja_chica FROM movimientos_caja mc inner join almacenes a on a.id = mc.id_almacen inner join forma_pago fp on fp.id = mc.id_forma_pago WHERE tipo_caja='Chica' AND tipo_movimiento='Egreso' $where $filtroHastaSaldoAnterior";
  $q3 = $pdo->prepare($sql3);
  //echo $sql;
  $q3->execute(array());
  $data3 = $q3->fetch(PDO::FETCH_ASSOC);
  $egresos_caja_chica=$data3["egresos_caja_chica"];
  if(is_null($egresos_caja_chica)){
    $egresos_caja_chica=0;
  }

  //obtenemos los pagos a proveedores que se hicieron desde esta caja para el saldo anterior
  $sql4 = " SELECT SUM(deuda_proveedor) AS total_pago_proveedores FROM ventas_detalle vd INNER JOIN ventas v ON vd.id_venta=v.id WHERE pagado=1 AND caja_egreso='Chica' $whereVentas $filtroHastaSaldoAnterior";
  $q4 = $pdo->prepare($sql4);
  //echo $sql4;
  $q4->execute(array());
  $data4 = $q4->fetch(PDO::FETCH_ASSOC);
  $total_pago_proveedores=$data4["total_pago_proveedores"];
  if(is_null($total_pago_proveedores)){
    $total_pago_proveedores=0;
  }

  $saldo_anterior=$ingresos_externos+$total_ventas-$egresos_caja_chica-$total_pago_proveedores;

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

  $modo_debug=0;
  //PARA PODER DEBUGUEAR MOSTRAMOS VARIABLES EN LA COLUMNA DETALLE
  if($modo_debug==1){
    $detalle="total_ventas: $total_ventas<br>ingresos_externos: $ingresos_externos<br>egresos_caja_chica: $egresos_caja_chica<br>total_pago_proveedores: $total_pago_proveedores<br>";
    $detalle.="data[total_ventas]: $data[total_ventas]<br>data2[ingresos_externos]: $data2[ingresos_externos]<br>data3[egresos_caja_chica]: $data3[egresos_caja_chica]<br>data4[total_pago_proveedores]: $data4[total_pago_proveedores]<br>";
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

  //FIN SALDO ANTERIOR

  //INICIO OBTENCION DE REGISTROS A MOSTRAR EN LA TABLA

  //obtenemos las ventas
  $sql = " SELECT v.id AS id_venta, a.almacen, date_format(v.fecha_hora,'%d/%m/%Y %H:%i') AS fecha_hora_formatted,v.fecha_hora, fp.forma_pago,(SELECT GROUP_CONCAT('+',vd.cantidad,' ',p.descripcion,': $',FORMAT(vd.subtotal,2,'de_DE') SEPARATOR '<br>') FROM ventas_detalle vd inner join productos p on p.id = vd.id_producto WHERE vd.id_venta=v.id) AS detalle_productos,v.total_con_descuento,v.id_cierre_caja FROM ventas v inner join almacenes a on a.id = v.id_almacen inner join forma_pago fp on fp.id = v.id_forma_pago WHERE 1 $whereVentas $filtroDesde $filtroHasta";
  foreach ($pdo->query($sql) as $row) {
    $cerrado="<i class='fa fa-lock' aria-hidden='true'></i> ";
    if($row["id_cierre_caja"]==0){
      $cerrado="<i class='fa fa-unlock' aria-hidden='true'></i> ";
    }
    $aCaja[]=[
      "id_venta"=>$row["id_venta"],
      "fecha_hora"=>date("d-m-Y H:i",strtotime($row["fecha_hora"])),
      "detalle"=>$cerrado."<a href='verVenta.php?id=".$row["id_venta"]."' target='_blank' class='badge badge-primary'><i class='fa fa-eye' aria-hidden='true'></i></a> Venta ID ".$row["id_venta"]."",
      "forma_pago"=>$row["forma_pago"],
      "credito"=>$row["total_con_descuento"],
      "debito"=>0,
      "saldo"=>0,
      "detalle_productos"=>$row["detalle_productos"],
    ];
  }

  //obtenemos las pagos a proveedoras
  $sql = " SELECT p.id_proveedor,fecha_hora_pago,CONCAT(apellido,' ',nombre) AS proveedor,SUM(deuda_proveedor) AS suma_deuda_proveedor,GROUP_CONCAT('+',vd.cantidad,' ',p.descripcion,': $',FORMAT(vd.deuda_proveedor,2,'de_DE') SEPARATOR '<br>') AS detalle_productos FROM ventas_detalle vd INNER JOIN ventas v ON vd.id_venta=v.id INNER JOIN productos p ON vd.id_producto=p.id INNER JOIN proveedores pr ON p.id_proveedor=pr.id WHERE pagado=1 AND caja_egreso='Chica' $whereVentas $filtroDesde $filtroHasta GROUP BY p.id_proveedor";
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

  //obtenemos los movimientos de la caja chica y segun si son ingresos o egresos los mostramos en crédio o débito
  $sql = " SELECT mc.id AS id_movimiento, a.almacen, date_format(mc.fecha_hora,'%d/%m/%Y %H:%i') AS fecha_hora_formatted,mc.fecha_hora, fp.forma_pago,mc.monto AS total,msc.motivo,mc.detalle,mc.id_cierre_caja,mc.tipo_movimiento FROM movimientos_caja mc inner join almacenes a on a.id = mc.id_almacen inner join forma_pago fp on fp.id = mc.id_forma_pago INNER JOIN motivos_salidas_caja msc ON mc.id_motivo=msc.id WHERE tipo_caja='Chica' $where $filtroDesde $filtroHasta";
  //echo $sql;
  foreach ($pdo->query($sql) as $row) {
    
    $iconVer="<a href='verMovimientoCajaChica.php?id=".$row["id_movimiento"]."' target='_blank' class='badge badge-primary'><i class='fa fa-eye' aria-hidden='true'></i></a>";

    $iconEdit="";
    $cerrado="<i class='fa fa-lock' aria-hidden='true'></i> ";
    if($row["id_cierre_caja"]==0){
      $iconEdit="<a href='modificarMovimientoCajaChica.php?id=".$row["id_movimiento"]."' target='_blank' class='badge badge-secondary'><i class='fa fa-pencil-square-o' aria-hidden='true'></i></a>";
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