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
  $filtroDesdePagoProv="AND DATE(fecha_hora_pago)>='$desde'";
}
$hasta=$_GET["hasta"];
$filtroHasta="";
if($hasta!=""){
  $filtroHasta="AND DATE(fecha_hora)<='$hasta'";
  $filtroHastaPagoProv="AND DATE(fecha_hora_pago)<='$hasta'";
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

  $where="$wherePagoProv $filtroFormaPago $filtroAlmacen";

  $whereVentas=$where." AND v.anulada = 0";

  //INICIO SALDO ANTERIOR

  $filtroHastaSaldoAnterior="AND DATE(fecha_hora)<'$desde'";
  $filtroHastaSaldoAnteriorPagoProv="AND DATE(fecha_hora_pago)<'$desde'";

  $total_facturas_recibos=0;
  if($filtroMotivo==""){
    //obtenemos las facturas y ventas para el saldo anterior
    $sql = "SELECT SUM(v.total_con_descuento) AS total_ventas FROM ventas v inner join almacenes a on a.id = v.id_almacen inner join forma_pago fp on fp.id = v.id_forma_pago WHERE tipo_comprobante IN ('R','A','B') $whereVentas $filtroHastaSaldoAnterior";
    $q = $pdo->prepare($sql);
    //echo $sql;
    $q->execute(array());
    $data = $q->fetch(PDO::FETCH_ASSOC);
    $total_facturas_recibos=$data["total_ventas"];
    if(is_null($total_facturas_recibos)){
      $total_facturas_recibos=0;
    }
  }

  $total_notas_credito=0;
  if($filtroMotivo==""){
    //obtenemos las notas de credito para el saldo anterior
    $sql = "SELECT SUM(v.total_con_descuento) AS total_notas_credito FROM ventas v inner join almacenes a on a.id = v.id_almacen inner join forma_pago fp on fp.id = v.id_forma_pago WHERE tipo_comprobante IN ('NCA','NCB') $whereVentas $filtroHastaSaldoAnterior";
    $q = $pdo->prepare($sql);
    //echo $sql;
    $q->execute(array());
    $data = $q->fetch(PDO::FETCH_ASSOC);
    $total_notas_credito=$data["total_notas_credito"];
    if(is_null($total_notas_credito)){
      $total_notas_credito=0;
    }
  }

  //obtenemos los movimientos registrados como ingresos a caja chica para el saldo anterior
  $sql2 = "SELECT SUM(mc.monto) AS ingresos_externos FROM movimientos_caja mc inner join almacenes a on a.id = mc.id_almacen inner join forma_pago fp on fp.id = mc.id_forma_pago WHERE anulado=0 AND tipo_caja='Chica' AND  tipo_movimiento='Ingreso' $where $filtroHastaSaldoAnterior $filtroMotivo";
  $q2 = $pdo->prepare($sql2);
  //echo $sql2;
  $q2->execute(array());
  $data2 = $q2->fetch(PDO::FETCH_ASSOC);
  $ingresos_externos=$data2["ingresos_externos"];
  if(is_null($ingresos_externos)){
    $ingresos_externos=0;
  }

  //obtenemos los movimientos registrados como egresos de caja chica para el saldo anterior
  $sql3 = "SELECT SUM(mc.monto) AS egresos_caja_chica FROM movimientos_caja mc inner join almacenes a on a.id = mc.id_almacen inner join forma_pago fp on fp.id = mc.id_forma_pago WHERE anulado=0 AND tipo_caja='Chica' AND tipo_movimiento='Egreso' $where $filtroHastaSaldoAnterior $filtroMotivo";
  $q3 = $pdo->prepare($sql3);
  //echo $sql;
  $q3->execute(array());
  $data3 = $q3->fetch(PDO::FETCH_ASSOC);
  $egresos_caja_chica=$data3["egresos_caja_chica"];
  if(is_null($egresos_caja_chica)){
    $egresos_caja_chica=0;
  }

  $total_pago_proveedores=0;
  //if($filtroMotivo==""){
  if($mostrarPagoProveedores==1){
    //obtenemos los pagos a proveedores que se hicieron desde esta caja para el saldo anterior
    $sql4 = " SELECT SUM(deuda_proveedor) AS total_pago_proveedores FROM ventas_detalle vd INNER JOIN ventas v ON vd.id_venta=v.id WHERE pagado=1 AND caja_egreso='Chica' $whereVentas $filtroHastaSaldoAnteriorPagoProv";
    $q4 = $pdo->prepare($sql4);
    //echo $sql4;
    $q4->execute(array());
    $data4 = $q4->fetch(PDO::FETCH_ASSOC);
    $total_pago_proveedores=$data4["total_pago_proveedores"];
    if(is_null($total_pago_proveedores)){
      $total_pago_proveedores=0;
    }
  }

  $saldo_anterior=$ingresos_externos+$total_facturas_recibos-$total_notas_credito-$egresos_caja_chica-$total_pago_proveedores;

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

  if($filtroMotivo==""){
    //obtenemos las ventas
    $sql = " SELECT v.id AS id_venta, a.almacen, date_format(v.fecha_hora,'%d/%m/%Y %H:%i') AS fecha_hora_formatted,v.fecha_hora, fp.forma_pago,(SELECT GROUP_CONCAT('+',vd.cantidad,' ',p.descripcion,': $',FORMAT(vd.subtotal,2,'de_DE') SEPARATOR '<br>') FROM ventas_detalle vd inner join productos p on p.id = vd.id_producto WHERE vd.id_venta=v.id) AS detalle_productos,v.total_con_descuento,v.id_cierre_caja,tipo_comprobante FROM ventas v inner join almacenes a on a.id = v.id_almacen inner join forma_pago fp on fp.id = v.id_forma_pago WHERE 1 $whereVentas $filtroDesde $filtroHasta";
    foreach ($pdo->query($sql) as $row) {
      $cerrado="<i class='fa fa-lock' aria-hidden='true'></i> ";
      if($row["id_cierre_caja"]==0){
        $cerrado="<i class='fa fa-unlock' aria-hidden='true'></i> ";
      }
      $credito=$row["total_con_descuento"];
      $debito=0;
      $tipo_comprobante="Venta";
      if(in_array($row["tipo_comprobante"],["NCA","NCB"])){
        $credito=0;
        $debito=$row["total_con_descuento"];
        $tipo_comprobante="Nota de Crédito";
      }
      $aCaja[]=[
        "id_venta"=>$row["id_venta"],
        "fecha_hora"=>date("d-m-Y H:i",strtotime($row["fecha_hora"])),
        "detalle"=>$cerrado."<a href='verVenta.php?id=".$row["id_venta"]."' target='_blank' class='badge badge-primary'><i class='fa fa-eye' aria-hidden='true'></i></a> ".$tipo_comprobante." ID ".$row["id_venta"]."",
        "forma_pago"=>$row["forma_pago"],
        "credito"=>$credito,
        "debito"=>$debito,
        "saldo"=>0,
        "detalle_productos"=>$row["detalle_productos"],
      ];
    }

  }

  if($mostrarPagoProveedores==1){
    //obtenemos las pagos a proveedoras
    $sql = " SELECT p.id_proveedor,fecha_hora_pago,CONCAT(apellido,' ',nombre) AS proveedor,SUM(deuda_proveedor) AS suma_deuda_proveedor,GROUP_CONCAT('+',vd.cantidad,' ',p.descripcion,': $',FORMAT(vd.deuda_proveedor,2,'de_DE') SEPARATOR '<br>') AS detalle_productos FROM ventas_detalle vd INNER JOIN ventas v ON vd.id_venta=v.id INNER JOIN productos p ON vd.id_producto=p.id INNER JOIN proveedores pr ON p.id_proveedor=pr.id WHERE pagado=1 AND caja_egreso='Chica' $whereVentas $filtroDesdePagoProv $filtroHastaPagoProv GROUP BY p.id_proveedor";
    //echo $sql;
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

  //obtenemos los movimientos de la caja chica y segun si son ingresos o egresos los mostramos en crédio o débito
  $sql = " SELECT mc.id AS id_movimiento, a.almacen, date_format(mc.fecha_hora,'%d/%m/%Y %H:%i') AS fecha_hora_formatted,mc.fecha_hora, fp.forma_pago,mc.monto AS total,msc.motivo,mc.detalle,mc.id_cierre_caja,mc.tipo_movimiento FROM movimientos_caja mc inner join almacenes a on a.id = mc.id_almacen inner join forma_pago fp on fp.id = mc.id_forma_pago INNER JOIN motivos_salidas_caja msc ON mc.id_motivo=msc.id WHERE anulado=0 AND tipo_caja='Chica' $where $filtroDesde $filtroHasta $filtroMotivo";
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