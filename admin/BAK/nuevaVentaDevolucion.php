<?php
ini_set("display_errors",1);
ini_set("display_startup_errors",1);
error_reporting(E_ALL);
require("config.php");
if(empty($_SESSION['user'])){
  header("Location: index.php");
  die("Redirecting to index.php"); 
}
require 'database.php';
require 'funciones.php';

if ( !empty($_POST)) {
  // insert data
  $pdo = Database::connect();
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $modoDebug=0;

  if ($modoDebug==1) {
    $pdo->beginTransaction();
    var_dump($_POST);
  }

  $nombre_cliente=($_POST['nombre_cliente']) ?: "";
  $dni=($_POST['dni']) ?: "";
  $direccion=($_POST['direccion']) ?: "";
  $email=($_POST['email']) ?: "";
  $telefono=($_POST['telefono']) ?: "";
  $tipo_comprobante=$_POST["tipo_comprobante"];
  $id_venta = $_POST['id_venta'];
  $total_con_descuentos = $_POST['total_input'];
  $id_almacen = $_POST['id_almacen'];
  $modalidad_venta = $_POST['modalidad_venta'];
  $id_forma_pago = $_POST['id_forma_pago'];
  $id_producto = $_POST['id_producto'];
  $precio_devolucion = 0;
  $cantidad = $_POST['cantidad'];

  $total_cantidad = 0;
  if(isset($_POST['cantidad'])) {
      foreach($_POST['cantidad'] as $cantidad) {
          $total_cantidad += $cantidad;
      }
  }

  $id_venta_detalle_arr = explode(',', $_POST['id_venta_detalle']);
  $id_venta_detalle_arr = implode(',', $id_venta_detalle_arr);
  $sql = "SELECT id, id_producto, cantidad, precio, subtotal, id_modalidad, deuda_proveedor, pagado, fecha_hora_pago, caja_egreso, id_almacen, id_forma_pago FROM ventas_detalle WHERE id IN ($id_venta_detalle_arr) ";                             
  $q = $pdo->prepare($sql);
  $q->execute();
  $devoluciones = $q->fetchAll(PDO::FETCH_ASSOC);
  foreach ($devoluciones as $data){
    $precio_devolucion += $data['subtotal'];
    if ($modoDebug==1) {
      $q->debugDumpParams();
      echo "<br><br>Precio Devolucion: ".$precio_devolucion;
      echo "<br><br>";
    }
  }

  $precio_venta = 0;
  if(isset($_POST['precio'])) {
      foreach($_POST['precio'] as $precio) {
          $precio_venta += $precio;
          if ($modoDebug==1) {
            $q->debugDumpParams();
            echo "<br><br>Precio Venta: ".$precio_venta;
            echo "<br><br>";
          }
      }
  }

  $total_ventas= $precio_venta - $precio_devolucion;
  $total_devolucion = $precio_devolucion;
  if ($modoDebug==1) {
    echo "<br><br>Total_Ventas: ".$total_ventas;
    echo "<br><br>";
    echo "<br><br>Total_devolucion: ".$total_devolucion;
    echo "<br><br>";
  }

  //Alta Nueva Venta
  $sql = "INSERT INTO ventas(fecha_hora, nombre_cliente, dni, direccion, email, telefono, id_almacen, total, tipo_comprobante, id_usuario,id_forma_pago,modalidad_venta) VALUES (now(),?,?,?,?,?,?,?,?,?,?,?)";
  $q = $pdo->prepare($sql);
  $q->execute(array($nombre_cliente,$dni,$direccion,$email,$telefono,$_POST['id_almacen'],$total_ventas, $tipo_comprobante,$_SESSION['user']['id'],$_POST['id_forma_pago'],$_POST["modalidad_venta"]));
  $idVenta = $pdo->lastInsertId();

  if ($modoDebug==1) {
    $q->debugDumpParams();
    echo "<br><br>Afe: ".$q->rowCount();
    echo "<br><br>";
  }

  // Alta Nueva Devolución
  $sql2 = "INSERT INTO devoluciones(fecha_hora, id_venta, id_nueva_venta, total) VALUES (now(),?,?,?)";
  $q2 = $pdo->prepare($sql2);
  $q2->execute(array($id_venta,$idVenta, $total_devolucion));
  $idDevolucion = $pdo->lastInsertId();

  if ($modoDebug==1) {
    $q->debugDumpParams();
    echo "<br><br>Afe: ".$q->rowCount();
    echo "<br><br>";
  }

  // Alta Nueva Devolución Detalle
  foreach ($devoluciones as $data) {
    $sql5 = "INSERT INTO devoluciones_detalle (id_devolucion, id_venta_detalle) VALUES (?,?)";
    $q5= $pdo->prepare($sql5);
    $q5->execute(array($idDevolucion,$data['id']));

    // Devolucion del producto en la tabla
    $sql = " SELECT vd.`id_producto`, vd.`cantidad`, vd.`subtotal`, vd.`id_modalidad`, p.id_proveedor, v.id_almacen from ventas_detalle vd inner join ventas v on v.id = vd.id_venta inner join productos p on p.id = vd.`id_producto` where vd.`id` = ".$data['id'];
    foreach ($pdo->query($sql) as $row) {
      $sql = "SELECT `id` FROM `stock` WHERE id_producto = ? and id_almacen = ?";
      $q = $pdo->prepare($sql);
      $q->execute(array($row['id_producto'], $row['id_almacen']));
      $data = $q->fetch(PDO::FETCH_ASSOC);
      if (!empty($data)) {
        $sql3 = "UPDATE `stock` set cantidad = cantidad + ? where id = ?";
        $q3 = $pdo->prepare($sql3);
        $q3->execute(array($row['cantidad'],$data['id']));
      } else {
        $sql3 = "INSERT INTO `stock`(`id_producto`, `id_almacen`, `cantidad`, `id_modalidad`) VALUES (?,?,?,?)";
        $q3 = $pdo->prepare($sql3);
        $q3->execute(array($row['id_producto'],$row['id_almacen'],$row['cantidad'],$row['id_modalidad']));
      }
    }
  }

  if ($modoDebug==1) {
    $q->debugDumpParams();
    echo "<br><br>Afe: ".$q->rowCount();
    echo "<br><br>";
  }
  
  $total = 0;
  $cantPrendas = count($_POST["id_producto"]);

  $minimo_compra="";
  $monto_fijo="";
  $porcentaje="";
  $minimo_cantidad_prendas="";
  if (!empty($_POST['id_descuento'])) {
    $sql2 = "SELECT minimo_compra, minimo_cantidad_prendas, monto_fijo, porcentaje FROM descuentos WHERE id = ? ";
    $q2 = $pdo->prepare($sql2);
    $q2->execute(array($_POST['id_descuento']));
    $data2 = $q2->fetch(PDO::FETCH_ASSOC);

    $minimo_compra=$data2['minimo_compra'];
    //$monto_fijo=$data2['monto_fijo'];
    $porcentaje=$data2['porcentaje'];
    $minimo_cantidad_prendas=$data2['minimo_cantidad_prendas'];

    if ($modoDebug==1) {
      $q2->debugDumpParams();
      echo "<br><br>Afe: ".$q2->rowCount();
      echo "<br><br>";
    }

  }
  $total=0;
  foreach ($_POST['id_stock'] as $key => $id_stock) {
    $total+=($_POST['cantidad'][$key]*$_POST['precio'][$key]);
  }

  //var_dump($total);

  $totalConDescuento = 0;
  foreach ($_POST['id_stock'] as $key => $id_stock) {
    
    //$idProducto = $row[8];
    $cantidad = $_POST['cantidad'][$key];
    $cantidadAnterior = $_POST['stock'][$key];
    $precio = $_POST['precio'][$key];
    $modalidad = $_POST["id_modalidad"][$key];
    $idProveedor = $_POST["id_proveedor"][$key];
    $id_producto = $_POST["id_producto"][$key];

    /*if ($_POST['id_forma_pago'] == 1) {
      $precio = $precio*0.9; //10% off por pago en efectivo
    }*/
    $subtotal = $cantidad * $precio;
    //var_dump($subtotal);
  
    if ($minimo_compra!="" and $total>$minimo_compra and $minimo_cantidad_prendas!="" and $cantPrendas>=$minimo_cantidad_prendas) {
      //$totalConDescuento = $totalConDescuento - $monto_fijo;
      $subtotal-=(($subtotal*$porcentaje)/100);
      //var_dump($subtotal);
    }
    
    $totalConDescuento += $subtotal;
    //var_dump($totalConDescuento);
    //$deuda_proveedor=0;

    $deuda_proveedor=calcularDeudaProveedor($_POST['id_forma_pago'],$modalidad,$subtotal);

    $pagado = 0;
    if($modalidad==1 or $modalidad==50){
      $pagado = 1;
      if($modalidad==50){
        $sql = "UPDATE proveedores set credito = credito + ? where id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($deuda_proveedor,$idProveedor));
      }
    }
    
    $sql = "INSERT INTO ventas_detalle (id_venta, id_producto, cantidad, precio, subtotal, id_modalidad, deuda_proveedor, pagado) VALUES (?,?,?,?,?,?,?,?)";
    $q = $pdo->prepare($sql);
    //$q->execute(array($idVenta,$id_producto,$cantidad,$precio,$subtotal,$modalidad,$pagado));
    $q->execute(array($idVenta,$id_producto,$cantidad,$_POST['precio'][$key],$subtotal,$modalidad,$deuda_proveedor,$pagado));

    if ($modoDebug==1) {
      $q->debugDumpParams();
      echo "<br><br>Afe: ".$q->rowCount();
      echo "<br><br>";
    }
    
    $sql3 = "UPDATE stock set cantidad = cantidad - ? where id = ?";
    $q3 = $pdo->prepare($sql3);
    $q3->execute(array($cantidad,$id_stock));

    if ($modoDebug==1) {
      $q3->debugDumpParams();
      echo "<br><br>Afe: ".$q3->rowCount();
      echo "<br><br>";
    }
    
    if ($cantidadAnterior == $cantidad) {
      $sql3 = "DELETE from stock where id = ?";
      $q3 = $pdo->prepare($sql3);
      $q3->execute(array($id_stock));

      if ($modoDebug==1) {
        $q3->debugDumpParams();
        echo "<br><br>Afe: ".$q3->rowCount();
        echo "<br><br>";
      }
    }
    $cantPrendas++;
  }

  $id_descuento=NULL;
  if(isset($_POST['id_descuento']) and $_POST['id_descuento']!=""){
    $id_descuento=$_POST['id_descuento'];
  }

  if ($modoDebug==1) {
    $q->debugDumpParams();
    echo "<br><br>Total: ".$total;
    echo "<br><br>totalConDescuento: ".$totalConDescuento;
    echo "<br><br>";
  }

  $sql = "UPDATE ventas set total = ?, id_descuento_aplicado = ?, total_con_descuento = ? WHERE id = ?";
  $q = $pdo->prepare($sql);
  $q->execute(array($total,$id_descuento,$totalConDescuento,$idVenta));

  if ($modoDebug==1) {
    $q->debugDumpParams();
    echo "<br><br>Afe: ".$q->rowCount();
    echo "<br><br>";
  }
  
  //Descuentos Nueva Venta
  $id_descuento=NULL;
  if(isset($_POST['id_descuento']) and $_POST['id_descuento']!=""){
    $id_descuento = $_POST['id_descuento'];
    $total_d= $totalConDescuento - $precio_devolucion;
    if ($modoDebug==1) {
      echo "Ultimo UPDATE:";
      $q->debugDumpParams();
      echo "<br><br>Precio Devolucion: ".$precio_devolucion;
      echo "<br><br>Total con descuentos Final: ".$total_d;
      echo "<br><br>";
      echo "<br><br>Total_Ventas con dscuento: ".$total_ventas;
      echo "<br><br>";
      echo "<br><br>Total_devolucion con dscuento: ".$total_devolucion;
      echo "<br><br>";
    }
    if ($modoDebug==1) {
      
    }
    //Si tiene descuentos cambia el total
    $sql5 = "UPDATE ventas set total = ?, id_descuento_aplicado = ?, total_con_descuento = ? WHERE id = ?";
    $q5 = $pdo->prepare($sql5);
    $q5->execute(array($totalConDescuento,$id_descuento,$total_d,$idVenta));
    if ($modoDebug==1) {
      echo "Ultimo UPDATE:";
      $q->debugDumpParams();
      echo "<br><br>Precio Devolucion: ".$precio_devolucion;
      echo "<br><br>Total con descuentos Final: ".$total_d;
      
      echo "<br><br>";
    }
  }else {
    //Si no tiene descuentos cambia el total
    $sql2 = "UPDATE ventas set total = ?, id_descuento_aplicado = ?, total_con_descuento = ? WHERE id = ?";
    $q2 = $pdo->prepare($sql2);
    $q2->execute(array($totalConDescuento,$id_descuento,$total_ventas,$idVenta));
    if ($modoDebug==1) {
      $q->debugDumpParams();
      echo "<br><br>Total sin descuentos Final: ".$totalConDescuento;
      echo "<br><br>";
      echo "<br><br>Total_Ventas sin dscuento: ".$total_ventas;
      echo "<br><br>";
      echo "<br><br>Total_devolucion sin dscuento: ".$total_devolucion;
      echo "<br><br>";
    }
  }

  if($tipo_comprobante!="R"){

    include './../external/afip/Afip.php';

    /*$cuit=30717754200;
    $produccion=true;
    if ($modoDebug==1) {
      $cuit=20351290340;
      $produccion=false;
    }

    //$afip = new Afip(array('CUIT' => 20351290340,$production=true));
    $afip = new Afip(array('CUIT' => $cuit,'production'=>$produccion));*/

    include 'config_facturacion_electronica.php';//poner $homologacion=1 para facturar en modo homologacion. Retorna $aInitializeAFIP.
    $afip = new Afip($aInitializeAFIP);

    $sql4 = "SELECT punto_venta FROM almacenes WHERE id = ? ";
    $q4 = $pdo->prepare($sql4);
    $q4->execute(array($_POST['id_almacen']));
    $data4 = $q4->fetch(PDO::FETCH_ASSOC);

    if ($modoDebug==1) {
      $q4->debugDumpParams();
      echo "<br><br>Afe: ".$q4->rowCount();
      echo "<br><br>";
    }
    $punto_venta=$data4["punto_venta"];

    $server_status = $afip->ElectronicBilling->GetServerStatus();
    /*echo 'Este es el estado del servidor:';
    var_dump($server_status);*/

    $ImpTotal=$totalConDescuento;
    //$total=121;
    if($tipo_comprobante=="A"){
      $tipo_comprobante=1;//1 -> Factura A
      $DocTipo=80;
      $DocNro=$_POST["dni"];

      $ImpNeto=$ImpTotal/1.21;
      $ImpIVA=$ImpTotal-$ImpNeto;
    }elseif($tipo_comprobante=="B"){
      $tipo_comprobante=6;//6 -> Factura B
      $DocTipo=99;
      $DocNro=0;
      
      $ImpNeto=$ImpTotal/1.21;
      $ImpIVA=$ImpTotal-$ImpNeto;
    }
    $ImpNeto=number_format($ImpNeto,2,".","");
    $ImpIVA=number_format($ImpIVA,2,".","");
    

    $data = array(
      'CantReg' 	=> 1,  // Cantidad de comprobantes a registrar
      'PtoVta' 	=> $punto_venta,  // Punto de venta
      'CbteTipo' 	=> $tipo_comprobante,  // Tipo de comprobante (ver tipos disponibles) 
      'Concepto' 	=> 1,  // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
      'DocTipo' 	=> $DocTipo, // Tipo de documento del comprador (99 consumidor final, ver tipos disponibles). Para comprobantes clase A y M el campo DocTipo debe ser igual a 80 (CUIT)
      'DocNro' 	=> $DocNro,  // Número de documento del comprador (0 consumidor final)
      'CbteDesde' 	=> 2,  // Número de comprobante o numero del primer comprobante en caso de ser mas de uno
      'CbteHasta' 	=> 2,  // Número de comprobante o numero del último comprobante en caso de ser mas de uno
      'CbteFch' 	=> intval(date('Ymd')), // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
      'ImpTotal' 	=> $ImpTotal,//121, // Importe total del comprobante
      'ImpTotConc' 	=> 0,   // Importe neto no gravado
      'ImpNeto' 	=> $ImpNeto,//100, // Importe neto gravado
      'ImpOpEx' 	=> 0,   // Importe exento de IVA
      'ImpIVA' 	=> $ImpIVA,//21,  //Importe total de IVA
      'ImpTrib' 	=> 0,   //Importe total de tributos
      'MonId' 	=> 'PES', //Tipo de moneda usada en el comprobante (ver tipos disponibles)('PES' para pesos argentinos) 
      'MonCotiz' 	=> 1,     // Cotización de la moneda usada (1 para pesos argentinos)  
      'Iva' 		=> array( // (Opcional) Alícuotas asociadas al comprobante
        array(
          'Id' 		=> 5, // Id del tipo de IVA (5 para 21%)(ver tipos disponibles) 
          'BaseImp' 	=> $ImpNeto,//100, // Base imponible -> ES IGUAL A ImpNeto?
          'Importe' 	=> $ImpIVA,//21 // Importe -> ES IGUAL A ImpIVA?
        )
      ), 
    );
    
    //$res = $afip->ElectronicBilling->CreateVoucher($data);
    $res = $afip->ElectronicBilling->CreateNextVoucher($data);
    
    $estado="E";
    if(isset($res['CAE'])){
      $estado="A";
      $CAE=$res['CAE'];//CAE asignado el comprobante
      $CAEFchVto=$res['CAEFchVto'];//Fecha de vencimiento del CAE (yyyy-mm-dd)
      $voucher_number=$res['voucher_number'];//Número asignado al comprobante
      //var_dump($res);
    }
    
    if ($modoDebug==1) {
      var_dump($res);
      var_dump($CAE);
      var_dump($CAEFchVto);
      var_dump($voucher_number);
    }

    $sql = "UPDATE ventas SET tipo_doc = ?, estado = ?, punto_venta = ?, numero_comprobante = ?, cae = ?, fecha_vencimiento_cae = ? WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($DocTipo,$estado,$punto_venta,$voucher_number,$CAE,$CAEFchVto,$idVenta));

    if ($modoDebug==1) {
      $q->debugDumpParams();
      echo "<br><br>Afe: ".$q->rowCount();
      echo "<br><br>";
    }

  }

  if ($modoDebug==1) {
    $pdo->rollBack();
    die();
  }
  Database::disconnect();
  
  header("Location: listarVentas.php");
} else {
  $pdo = Database::connect();

  $array_devolucion = explode(',', $_GET['id_venta_detalle']);

  $id_lista = implode(',', $array_devolucion); // Convertimos el array de IDs en una cadena separada por comas

  $sql = "SELECT v.id AS id_venta, v.fecha_hora, v.id_almacen, v.id_descuento_aplicado, d.descripcion AS descuento_aplicado, fp.forma_pago FROM ventas v INNER JOIN ventas_detalle vd ON vd.id_venta = v.id LEFT JOIN descuentos d ON d.id = v.id_descuento_aplicado LEFT JOIN forma_pago fp ON v.id_forma_pago = fp.id INNER JOIN usuarios u ON v.id_usuario=u.id WHERE vd.id IN ($id_lista) "; // Utilizamos la cláusula WHERE IN para buscar los detalles de varias ventas al mismo tiempo
  //echo $sql;
  $q = $pdo->prepare($sql);
  $q->execute();
  $data = $q->fetch(PDO::FETCH_ASSOC);
  $id_venta=$data["id_venta"];
  $fecha_hora=$data["fecha_hora"];
  $id_almacen=$data["id_almacen"];
  $id_descuento_aplicado=$data["id_descuento_aplicado"];
  $descuento_aplicado=$data["descuento_aplicado"];
  $forma_pago=$data["forma_pago"];

  //var_dump($devoluciones);
  //die;
}

$id_perfil=$_SESSION["user"]["id_perfil"];?>
<!DOCTYPE html>
<html lang="en">
  <head><?php
    include('head_forms.php');?>
    <link rel="stylesheet" type="text/css" href="assets/css/datatables.css">
    <link rel="stylesheet" type="text/css" href="assets/css/select2.css">
    <style>
      .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0;}
    </style>
  </head>
  <body class="light-only">
    <!-- Loader ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper"><?php
      include('header.php');?>
      <!-- Page Header Start-->
      <div class="page-body-wrapper"><?php
        include('menu.php');?>
        <!-- Page Sidebar Start-->
        <!-- Right sidebar Ends-->
        <div class="page-body">
          <div class="container-fluid">
            <div class="page-header">
              <div class="row">
                <div class="col-10">
                  <div class="page-header-left">
                    <h3><?php include("title.php"); ?></h3>
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="#"><i data-feather="home"></i></a></li>
                      <li class="breadcrumb-item">Nueva Devolucion</li>
                    </ol>
                  </div>
                </div>
                <!-- Bookmark Start-->
                <div class="col-2">
                  <div class="bookmark pull-right">
                    <ul>
                      <li><a target="_blank" data-container="body" data-toggle="popover" data-placement="top" title="" data-original-title="<?php echo date('d-m-Y');?>"><i data-feather="calendar"></i></a></li>
                    </ul>
                  </div>
                </div>
                <!-- Bookmark Ends-->
              </div>
            </div>
          </div>
          <!-- Container-fluid starts-->
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-12">
                <div class="card">
                  <div class="card-header">
                    <h5>Nueva Devolucion</h5>
                  </div>
                  <form class="form theme-form" role="form" method="post" action="nuevaVentaDevolucion.php">
                    <div class="card-body">
                      <div class="row">
                        <div class="col">
                          
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Modalidad de venta</label>
                            <div class="col-sm-9">
                              <label class="d-block" for="edo-ani">
                                <input class="radio_animated" value="Presencial" checked required id="edo-ani" type="radio" name="modalidad_venta"><label for="edo-ani">Presencial</label>
                              </label>
                              <label class="d-block" for="edo-ani1">
                                <input class="radio_animated" value="Online" required id="edo-ani1" type="radio" name="modalidad_venta"><label for="edo-ani1">Online</label>
                              </label>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Almacen</label>
                            <div class="col-sm-9">
                              <select name="id_almacen" id="id_almacen" class="js-example-basic-single col-sm-12" required="required" onChange="jsListarProductos(this.value);">
                                <option value="">Seleccione...</option><?php
                                $pdo = Database::connect();
                                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                $sqlZon = "SELECT id, almacen, punto_venta FROM almacenes WHERE activo = 1";
                                if ($_SESSION['user']['id_perfil'] != 1) {
                                  $sqlZon .= " and id = ".$_SESSION['user']['id_almacen']; 
                                }
                                $q = $pdo->prepare($sqlZon);
                                $q->execute();
                                while ($fila = $q->fetch(PDO::FETCH_ASSOC)) {
                                  $selected="";
                                  if($fila['id']==$id_almacen){
                                    $selected="selected";
                                  }
                                  echo "<option value='".$fila['id']."' data-punto_venta='".$fila['punto_venta']."'";
                                  echo $selected .">".$fila['almacen']."</option>";
                                 }
                                Database::disconnect();?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Productos en Stock</label>
                          </div>
                          <div class="form-group row">
                            <div class="col-sm-12" id="tabla_productos">
                              <table class="table table-sm display" id="dataTables-example666">
                                <thead>
                                  <tr>
                                    <!-- <th>ID</th> -->
                                    <th>Proveedor</th>
                                    <th>Código</th>
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th>Stock</th>
                                    <th>Precio</th>
                                    <th>Accion</th>
                                    <!-- <th class="d-none">Precio</th> -->
                                  </tr>
                                </thead>
                                <tbody></tbody>
                              </table>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Productos a vender</label>
                          </div>
                          <div class="form-group row">
                            <div class="col-sm-12">
                              <table class="table" id="productos_vender">
                                <thead>
                                  <tr>
                                    <!-- <th>ID</th> -->
                                    <th>Proveedor</th>
                                    <th>Código</th>
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th>Stock</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <!-- <th class="d-none">Precio</th> -->
                                    <th>Eliminar</th>
                                  </tr>
                                </thead>
                                <tbody></tbody>
                              </table>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Total Productos a Vender</label>
                            <div class="col-sm-9"><label id="subtotal_compra">$ 0</label></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Tipo de comprobante</label>
                            <div class="col-sm-9">
                              <select name="tipo_comprobante" id="tipo_comprobante" class="js-example-basic-single col-sm-12" required="required">
                                <option value="">Seleccione...</option>
                                <!-- <option value="A" class="cbte_only_punto_venta" disabled>Factura A</option> -->
                                <option value="B" class="cbte_only_punto_venta">Factura B</option>
                                <option value="R">Recibo</option>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Forma de Pago</label>
                            <div class="col-sm-9">
                              <select name="id_forma_pago" id="id_forma_pago" class="js-example-basic-single col-sm-12" required>
                              <option value="">Seleccione...</option><?php
                                $pdo = Database::connect();
                                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                $sqlZon = "SELECT id, forma_pago FROM forma_pago WHERE activo = 1 ORDER BY forma_pago";
                                $q = $pdo->prepare($sqlZon);
                                $q->execute();
                                while ($fila = $q->fetch(PDO::FETCH_ASSOC)) {
                                  echo "<option value='".$fila['id']."'";
                                  echo ">".$fila['forma_pago']."</option>";
                                }
                                Database::disconnect();?>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Descuentos Vigentes</label>
                            <div class="col-sm-9">
                              <select name="id_descuento" id="id_descuento" disabled class="js-example-basic-single col-sm-12">
                                <option value="">Seleccione...</option>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Total</label>
                            <div class="col-sm-9"><label id="total_venta"><?= "$".number_format(0, 2, ',', '.');?></label></div>
                          </div>
                          <div class="form-group row">
                            <div class="col-sm-12">
                              <table class="table">
                                <thead>
                                  <tr>
                                    <th>Fecha</th>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                    <th>Cantidad</th>
                                    <th>Forma de Pago</th>
                                    <th>Descuentos Aplicados</th>
                                  </tr>
                                </thead>
                                <tbody><?php
                                  //$sql = "SELECT v.id, v.fecha_hora, v.nombre_cliente, v.dni, v.direccion, v.email, v.telefono, v.total, v.tipo_comprobante, v.punto_venta, v.id_forma_pago, v.id_almacen, v.id_descuento_aplicado, d.descripcion as descuento_aplicado, vd.cantidad as cantidad_producto, vd.subtotal,p.id as producto_id, p.codigo, p.descripcion, p.precio, fp.forma_pago, vd.subtotal FROM ventas v INNER JOIN ventas_detalle vd ON vd.id_venta = v.id INNER JOIN almacenes a on a.id = v.id_almacen LEFT JOIN descuentos d ON d.id = v.id_descuento_aplicado LEFT JOIN forma_pago fp ON v.id_forma_pago = fp.id INNER JOIN usuarios u ON v.id_usuario=u.id INNER JOIN productos p ON p.id = vd.id_producto WHERE vd.id IN ($id_lista) "; // Utilizamos la cláusula WHERE IN para buscar los detalles de varias ventas al mismo tiempo
                                  $sql = "SELECT vd.cantidad as cantidad_producto, vd.subtotal, p.codigo, p.descripcion, p.precio FROM ventas_detalle vd INNER JOIN productos p ON p.id = vd.id_producto WHERE vd.id IN ($id_lista) "; // Utilizamos la cláusula WHERE IN para buscar los detalles de varias ventas al mismo tiempo
                                  $q = $pdo->prepare($sql);
                                  $q->execute();
                                  $devoluciones = $q->fetchAll(PDO::FETCH_ASSOC);
                                  $precio_total = 0;
                                  foreach ($devoluciones as $data) { 
                                    $precio_total += $data["subtotal"];
                                    $descuentos_aplicados = '';
                                    if ($id_descuento_aplicado == NULL || $id_descuento_aplicado == 0) {
                                      //$descuentos_aplicados = 'Sin descuentos aplicados';
                                    } else {
                                      $descuentos_aplicados = $descuento_aplicado;
                                    }?>
                                    <tr>
                                      <td><?=date("d/m/Y", strtotime($fecha_hora))?></td>
                                      <td><?="(" . $data["codigo"] . ") " . $data["descripcion"]?></td>
                                      <td>$<?=number_format($data["precio"], 2, ",", ".");?></td>
                                      <td>$<?=number_format($data["subtotal"], 2, ",", ".");?></td>
                                      <td><?=$data["cantidad_producto"]?></td>
                                      <td><?=$forma_pago?></td>
                                      <td><?=$descuentos_aplicados;?></td>
                                    </tr><?php
                                  }?>
                                </tbody>
                              </table>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Total Devolucion</label>
                            <div class="col-sm-9"><label id="precio_total"><?= "$".number_format($precio_total, 2, ',', '.');?></label></div>
                          </div>
                          <input type="hidden" name="precio_total" value="<?php echo $precio_total; ?>">
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Total a Pagar</label>
                            <div class="col-sm-9"><label id="total_compra"><?= "$".number_format(0, 2, ',', '.');?></label></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Nombre y apellido</label>
                            <div class="col-sm-9"><input name="nombre_cliente" type="text" maxlength="99" class="form-control" value=""></div>
                          </div>
                          <div id="dni_group" class="form-group row">
                            <label class="col-sm-3 col-form-label">DNI</label>
                            <div class="col-sm-9"><input name="dni" id="dni" type="text" maxlength="99" class="form-control" value=""></div>
                          </div>
                          <div id="cuit_group" class="form-group row d-none">
                            <label class="col-sm-3 col-form-label">CUIT</label>
                            <div class="col-sm-9"><input name="cuit" id="cuit" disabled type="text" maxlength="99" class="form-control" value=""></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Dirección</label>
                            <div class="col-sm-9"><input name="direccion" type="text" maxlength="99" class="form-control" value=""></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">E-Mail</label>
                            <div class="col-sm-9"><input name="email" type="email" maxlength="99" class="form-control" value=""></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Teléfono</label>
                            <div class="col-sm-9"><input name="telefono" type="text" maxlength="99" class="form-control" value=""></div>
                          </div>
                          <input name="id_venta_detalle" class="form-control" type="hidden" value=<?=$id_lista?>>
                          <input name="id_venta" id="id_venta" class="form-control" type="hidden" value=<?=$id_venta;?>>
                          <input type="hidden" id="total_input" name="total_input" value="">
                        </div>
                      </div>
                    </div>
                    <div class="card-footer">
                      <div class="col-sm-9 offset-sm-3">
                        <button class="btn btn-primary" type="submit">Crear</button>
                        <a href="listarVentas.php" class="btn btn-light">Volver</a>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <!-- Container-fluid Ends-->
        </div>
        <!-- footer start--><?php 
        include("footer.php"); ?>
      </div>
    </div>
    <!-- latest jquery-->
    <script src="assets/js/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap js-->
    <script src="assets/js/bootstrap/popper.min.js"></script>
    <script src="assets/js/bootstrap/bootstrap.js"></script>
    <!-- feather icon js-->
    <script src="assets/js/icons/feather-icon/feather.min.js"></script>
    <script src="assets/js/icons/feather-icon/feather-icon.js"></script>
    <!-- Sidebar jquery-->
    <script src="assets/js/sidebar-menu.js"></script>
    <script src="assets/js/config.js"></script>
    <!-- Plugins JS start-->
    <script src="assets/js/chat-menu.js"></script>
    <script src="assets/js/tooltip-init.js"></script>
    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="assets/js/script.js"></script>
    <!-- Plugin used-->
	  <script src="assets/js/select2/select2.full.min.js"></script>
    <script src="assets/js/select2/select2-custom.js"></script>
	
	  <script src="assets/js/datatable/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/dataTables.buttons.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/jszip.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/buttons.colVis.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/pdfmake.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/vfs_fonts.js"></script>
    <script src="assets/js/datatable/datatable-extension/dataTables.autoFill.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/dataTables.select.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/buttons.bootstrap4.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/buttons.html5.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/buttons.print.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/dataTables.responsive.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/responsive.bootstrap4.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/dataTables.keyTable.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/dataTables.colReorder.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/dataTables.fixedHeader.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/dataTables.rowReorder.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/dataTables.scroller.min.js"></script>
    <script src="assets/js/datatable/datatable-extension/custom.js"></script>
    <script src="assets/js/chat-menu.js"></script>
    <script src="assets/js/tooltip-init.js"></script>
    <!-- Plugins JS Ends-->
    <!-- Plugins JS Ends-->
    <!-- Theme js-->

	<script>
    $("#tipo_comprobante").on("change",function(){
      changeTipoDNI();
    })

	  var toggle = true;
    $('.toggle-checkboxes').on('click', function (e) {
      e.preventDefault();
      $('.customer-selector').prop('checked', toggle);
      toggle = !toggle;
    })

    function changeTipoDNI(){
      let tipo_comprobante=$("#tipo_comprobante").val()
      if(tipo_comprobante=="A"){
        $("#dni_group").addClass("d-none")
        $("#cuit_group").removeClass("d-none")
        $("#dni").attr("disabled",true)
        $("#cuit").attr("disabled",false).attr("required",true)
      }else{
        $("#dni_group").removeClass("d-none")
        $("#cuit_group").addClass("d-none")
        $("#dni").attr("disabled",false)
        $("#cuit").attr("disabled",true).attr("required",false)
      }
    }

    $("form").on("submit",function(e){
      e.preventDefault();
      let cant_productos=$('#productos_vender tbody tr').length;
      if(cant_productos){
        let total = $('#total_compra');
        total = total.text();
        // Eliminar el signo de moneda y el punto de separación de miles
        total = total.replace('$', '').replace(/\./g, '');
        //Eliminar los espacios
        total = total.replace(/\s/g, '');
        //Reemplazar la coma por un punto
        total = total.replace(/,/g, '.');
        total = parseInt(total);
        console.log("Total: " + total);
        if(total >= 0){
          $('#total_input').val(total);
          //console.log("submit");
          let precio_en_cero=0;
          $("input[type='number'].precio").each(function(){
            if(this.value==0){
              precio_en_cero=1;
            }
          });
          if(precio_en_cero==1){
            alert("Tiene productos sin precio")
          }else{
            let descuento=$('#id_descuento option:selected')
            console.log(descuento);
            let minimo_cantidad_prendas=descuento.data("minimo_cantidad_prendas")
            if(minimo_cantidad_prendas!=undefined && minimo_cantidad_prendas>cant_productos){
              alert("La cantidad de productos añadidos ("+cant_productos+") no alcanza para aplicar el descuento ("+minimo_cantidad_prendas+")")
              return false
            }
            let minimo_compra=descuento.data("minimo_compra")
            let total=calcularTotalCompra();
            if(minimo_compra!=undefined && minimo_compra>total){
              alert("El monto total ("+new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(total)+") no alcanza para aplicar el descuento ("+new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(minimo_compra)+")")
              return false
            }
            //console.log("submit")
            this.submit();
          }
        }else{
          alert("El monto total a pagar debe ser mayor a 0")
        }
      }else{
        alert("Añada algún producto")
      }
    });


    function jsListarProductos(val) {
      console.log("almacen= " + val);
      $("#dataTables-example666").dataTable().fnDestroy();
      $('#dataTables-example666').DataTable({
        "ajax" : "ajaxVentas.php?almacen="+val,//&id_vehiculo="+id_vehiculo+"
				stateSave: true,
				responsive: true,
        serverSide: true,
        processing: true,
        scrollY: false,
        "columns":[
          //{"data": "cb"},//"fecha_mostrar"},
          {render: function(data, type, row, meta) {
            return `(${row.id_proveedor}) ${row.proveedor}`;
          }},
          {"data": "codigo"},//"vehiculo.marca"},
          {"data": "categoria"},//"vehiculo.modelo"},
          //{"data": "descripcion"},//"vehiculo.patente"},
          {render: function(data, type, row, meta) {
            //return row.descripcion
            return `(${row.id_modalidad}%) ${row.descripcion}`;
          }},
          {
            "data": "cantidad",
            orderDataType: "num-fmt",
            className: 'dt-body-center text-center',
          },{
            render: function(data, type, row, meta) {
              return `
                <input type="hidden" disabled name="id_modalidad[]" class="enviar_form id_modalidad" value="${row.id_modalidad}">
                <input type="hidden" disabled name="id_proveedor[]" class="enviar_form id_proveedor" value="${row.id_proveedor}">
                <input type="hidden" disabled name="id_producto[]" class="enviar_form id_producto" value="${row.id_producto}">
                <input type="hidden" disabled name="stock[]" class="enviar_form stock" value="${row.cantidad}">
                <input type="hidden" disabled name="precio[]" class="enviar_form precio" value="${row.precio}">
                <label class="precio">`+new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(row.precio)+`</label>`;
            },
            className: 'dt-body-right text-right',
            orderDataType: "num-fmt"
          },{
            render: function(data, type, row, meta) {
              let disabled=""
              if(row.cantidad<1){
                disabled="disabled"
              }
              return `<button type="button" class="btn btn-success btn-sm btnAnadir" ${disabled} data-id_stock="${row.id_stock}" data-cantidad="${row.cantidad}">Añadir</button>`;
            }
          }/*,{
            "data": "precio",
            className: 'd-none precio'
          }*/
        ],
				language: {
          "decimal": "",
          "emptyTable": "No hay información",
          "info": "Mostrando _START_ a _END_ de _TOTAL_ Registros",
          "infoEmpty": "Mostrando 0 to 0 of 0 Registros",
          "infoFiltered": "(Filtrado de _MAX_ total registros)",
          "infoPostFix": "",
          "thousands": ",",
          "lengthMenu": "Mostrar _MENU_ Registros",
          "loadingRecords": "Cargando...",
          "processing": "Procesando...",
          "search": "Buscar:",
          "zeroRecords": "No hay resultados",
          "paginate": {
            "first": "Primero",
            "last": "Ultimo",
            "next": "Siguiente",
            "previous": "Anterior"
          }
        },
        initComplete: function(){
          $('[title]').tooltip();
        },
      })

      var table = $("#dataTables-example666").DataTable();
      table.on( 'draw', function () {
        let filtrado=table.rows({search:'applied'}).nodes()
        let search=$('input[type=search]')
        if(search.val()!='' && filtrado.length==1){
        //if(filtrado.length==1){
          $(filtrado[0]).find("button.btnAnadir").click();
          search.select();
          /*search.val('').change();
          table.search('').draw();*/
          //search.val('').trigger('change');
          //table.search('').columns().search('').draw();
          //table.rows().nodes().draw();
        }
      });
    }
	
	  $(document).ready(function() {
      var id_almacen = document.getElementById('id_almacen');
      var id_almacen = id_almacen.value;
			jsListarProductos(id_almacen);

      $("#id_almacen").on("change",function(){

        $("#tipo_comprobante").val(null).trigger('change');

        let punto_venta=parseInt($(this).find(":checked").data("punto_venta"))
        let cbte_only_punto_venta=$(".cbte_only_punto_venta")
        
        if(isNaN(punto_venta)){
          cbte_only_punto_venta.prop("disabled","disabled");
        }else{
          cbte_only_punto_venta.prop("disabled",false);
        }
        $("#tipo_comprobante").select2("destroy").select2();

        changeTipoDNI();
      })
      
      $("#id_forma_pago").on("change",function(){

          var formaPagoId = $(this).val();
          
          if (formaPagoId) {
            $.ajax({
              url: 'obtener_descuentos.php', 
              method: 'POST',
              data: { forma_pago_id: formaPagoId }, 
              dataType: 'json', 
            }).done(function(data){
              
              var descuentosSelect = $('#id_descuento');
              descuentosSelect.html("");
              var descuentos = $(data);
              
              if (descuentos.length) {
                descuentosSelect.append('<option value="">Seleccione...</option>');
                console.log(descuentos);
                $.each(descuentos, function(i, descuento) {
                  let selected=""
                  if(descuento.id==1){
                    selected="selected"
                  }
                  descuentosSelect.append('<option '+selected+' data-minimo_cantidad_prendas="'+descuento.minimo_cantidad_prendas+'" data-minimo_compra="'+descuento.minimo_compra+'" data-porcentaje="'+descuento.porcentaje+'" value="'+descuento.id+'">'+descuento.nombre+'</option>');
                });
                  
                descuentosSelect.prop('disabled', false); 
              } else {
                descuentosSelect.append('<option value="">No se encontraron descuentos</option>');
                descuentosSelect.prop('disabled', true);
              }
              actualizarMontoTotal();

              //Descuento por defecto 10%
              
            }).fail(function(data){
              console.log(data);
              alert('Error al obtener las descuentos');
              actualizarMontoTotal();
            });
          } else {
            $('#id_descuento').empty().prop('disabled', true);
            actualizarMontoTotal();
          }
          
 
        /*let habilitarOFF=0;
        if(this.value){
          //id_descuento.value=0;
          id_descuento.prop("disabled",false);
          if(id_descuento.find("option").length==2){
            habilitarOFF=1;
          }
        }else{
          //id_descuento.value=0;
          id_descuento.prop("disabled",true);
        }

        if(habilitarOFF==1){
          id_descuento.val(1).trigger('change');
          //mostrarTotalDescuento()
          actualizarMontoTotal();
        }else{
          id_descuento.val(null).trigger('change');
        }*/
      })
		});

    $("#id_descuento").on("change",function(){
      //mostrarTotalDescuento();
      actualizarMontoTotal();
    })

    $(document).on("click",".btnAnadir",function(){
      let prod_anadido=$("input[name='id_stock[]'][value='"+this.dataset.id_stock+"']");
      //console.log(prod_anadido)
      //console.log("cantidad encontrada");
      if(prod_anadido.length==0){//controlamos que el producto ya no haya sido añadido
        if(parseInt(this.dataset.cantidad)>0){//controlamos que haya stock del producto
          let fila=$(this).parent().parent();
          let clon=fila.clone();
          let precio=clon.find("input.precio");
          let id_perfil="<?=$id_perfil?>";
          console.log("Perfil: " + id_perfil);
          if(id_perfil!=1 && precio.val()==0){//controlamos que los usuarios NO adminsitradores no puedan añadir productos sin precio
            alert("No puede añadir un producto sin precio")
          }else{//los usuarios admin pueden añadir productos sin precio pero tienen que modifcarlo
            let btn=clon.find("button");
            //console.log(btn);
            btn.parent().html(`
              <input type='hidden' name='id_stock[]' value='${this.dataset.id_stock}'></input>
              <input type='number' name='cantidad[]' class='form-control form-control-sm cantidad mx-auto' style='width: 60px;' min='1' max='${this.dataset.cantidad}' value="1" required></input>
            `);
            clon.append(`
              <td class='text-center'>
                <img src='img/icon_baja.png' class='btnEliminar' width='24' height='25' border='0' alt='Eliminar' title='Eliminar'>
              </td>
            `);
            if(id_perfil==1 && precio.val()==0){
              precio.attr("type","number").addClass("form-control form-control-sm mx-auto").attr("style","width: 60px;");//.attr("disabled",false)
              clon.find("label.precio").remove();
            }
            /*clon.find("input[name='precio[]']").attr("disabled",false);
            clon.find("input[name='stock[]']").attr("disabled",false);*/
            clon.find(".enviar_form").attr("disabled",false);

            $("#productos_vender tbody").append(clon[0]);

            actualizarMontoTotal();
          }
        }else{
          alert("No hay stock suficiente")
        }
      }else{
        alert("El producto ya fue añadido")
      }
    })

    function calcularTotalCompra(){
      var total=0;
      $("#productos_vender tbody tr").each(function(){
        total+=parseInt($(this).find(".precio").val())*parseInt($(this).find(".cantidad").val());
      })
      if(isNaN(total)){total=0;}
      return total
    }
    function actualizarMontoTotal(){
      let total=calcularTotalCompra()
      $("#subtotal_compra").html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(total))
       mostrarTotalDescuento(total)
      
    }

    function mostrarTotalDescuento(total){
      let porcentaje=$("#id_descuento option:selected").data("porcentaje");
      let subtotal=$("#subtotal_compra").val();
      let precio_devolucion=$("#precio_devolucion").val();
      let precio_total = document.querySelector('input[name="precio_total"]').value;
      let totalConDescuento= total ;
      
      if(porcentaje!=undefined){
        let descuento=porcentaje*total/100;
        console.log("Total: " + total);
        console.log("Porcentaje: " + porcentaje, "Subtotal: " + subtotal, "Precio D: " + precio_total);
        let porcentaje_p = (((total)*porcentaje)/100);
        totalConDescuento = totalConDescuento - porcentaje_p;
        
      }
      //console.log(parseInt(total)-parseInt(totalConDescuento))

      if(isNaN(totalConDescuento)){totalConDescuento=0;}
      $("#total_venta").html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(totalConDescuento));
      $("#total_compra").html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(totalConDescuento - precio_total));
    }

    $(document).on("keyup change",".cantidad, .precio",function(){
      actualizarMontoTotal()
    })

    $(document).on("click",".btnEliminar",function(){
      $(this).parent().parent().remove();
      actualizarMontoTotal()
    });

		</script>
		<script src="https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"></script>
		
  </body>
</html>