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
  
  $sql = "INSERT INTO ventas(fecha_hora, nombre_cliente, dni, direccion, email, telefono, id_almacen, total, id_usuario,id_forma_pago) VALUES (now(),?,?,?,?,?,?,0,?,?)";
  $q = $pdo->prepare($sql);
  $q->execute(array($nombre_cliente,$dni,$direccion,$email,$telefono,$_POST['id_almacen'],$_SESSION['user']['id'],$_POST['id_forma_pago']));
  $idVenta = $pdo->lastInsertId();

  if ($modoDebug==1) {
    $q->debugDumpParams();
    echo "<br><br>Afe: ".$q->rowCount();
    echo "<br><br>";
  }
  
  $total = 0;
  $cantPrendas = 0;
  /*$sql = " SELECT s.id, p.codigo, c.categoria, p.descripcion, p.precio, s.cantidad, s.id_modalidad, p.id_proveedor, p.id FROM stock s inner join productos p on p.id = s.id_producto inner join categorias c on c.id = p.id_categoria WHERE s.cantidad > 0 and p.activo = 1 and s.id_almacen = ".$_POST["id_almacen"];

  if ($modoDebug==1) {
    echo $sql;
    echo "<br><br>";
  }

  foreach ($pdo->query($sql) as $row) {
    if (isset($_POST['cantidad_'.$row[0]]) and $_POST['cantidad_'.$row[0]] > 0) {
      $idProducto = $row[8];
      $cantidad = $_POST['cantidad_'.$row[0]];
      $cantidadAnterior = $row[5];
      $precio = $row[4];
      if ($_POST['id_forma_pago'] == 1) {
        $precio = $precio*0.9; //10% off por pago en efectivo
      }
      $subtotal = $cantidad * $precio;
      $total += $subtotal;
      $modalidad = $row[6];
      $idProveedor = $row[7];
      $pagado = 0;
      $credito = 0;
      if ($modalidad == 1) {
        $pagado = 1;
      } else if ($modalidad == 2) {
        $pagado = 0;
      } else if ($modalidad == 3) {
        $credito = $subtotal/2;
        $sql = "UPDATE proveedores set credito = credito + ? where id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($credito,$idProveedor));
        $pagado = 1;
      }
      
      $sql = "INSERT INTO ventas_detalle(id_venta, id_producto, cantidad, precio, subtotal, id_modalidad, pagado) VALUES (?,?,?,?,?,?,?)";
      $q = $pdo->prepare($sql);
      $q->execute(array($idVenta,$idProducto,$cantidad,$precio,$subtotal,$modalidad,$pagado));

      if ($modoDebug==1) {
        $q->debugDumpParams();
        echo "<br><br>Afe: ".$q->rowCount();
        echo "<br><br>";
      }
      
      $sql3 = "UPDATE stock set cantidad = cantidad - ? where id = ?";
      $q3 = $pdo->prepare($sql3);
      $q3->execute(array($cantidad,$row[0]));

      if ($modoDebug==1) {
        $q3->debugDumpParams();
        echo "<br><br>Afe: ".$q3->rowCount();
        echo "<br><br>";
      }
      
      if ($cantidadAnterior == $cantidad) {
        $sql3 = "DELETE from stock where id = ?";
        $q3 = $pdo->prepare($sql3);
        $q3->execute(array($row[0]));

        if ($modoDebug==1) {
          $q3->debugDumpParams();
          echo "<br><br>Afe: ".$q3->rowCount();
          echo "<br><br>";
        }
      }
      $cantPrendas++;
    }
  }*/

  foreach ($_POST['id_stock'] as $key => $id_stock) {

    /*$sql2 = " SELECT s.id_modalidad, p.id_proveedor, s.id_producto FROM stock s inner join productos p on p.id = s.id_producto WHERE s.id = $id_stock";
    $q2 = $pdo->prepare($sql2);
    $q2->execute();
    $data2 = $q2->fetch(PDO::FETCH_ASSOC);

    if ($modoDebug==1) {
      echo $sql2;
      echo "<br><br>";
      var_dump($data2);
    }

    $modalidad = $data2["id_modalidad"];
    $idProveedor = $data2["id_proveedor"];
    $id_producto = $data2["id_producto"];*/
    
    //$idProducto = $row[8];
    $cantidad = $_POST['cantidad'][$key];
    $cantidadAnterior = $_POST['stock'][$key];
    $precio = $_POST['precio'][$key];
    $modalidad = $_POST["id_modalidad"][$key];
    $idProveedor = $_POST["id_proveedor"][$key];
    $id_producto = $_POST["id_producto"][$key];

    if ($_POST['id_forma_pago'] == 1) {
      $precio = $precio*0.9; //10% off por pago en efectivo
    }
    $subtotal = $cantidad * $precio;
    $total += $subtotal;

    $pagado = 0;
    $credito = 0;
    if ($modalidad == 1) {
      $pagado = 1;
    } else if ($modalidad == 2) {
      $pagado = 0;
    } else if ($modalidad == 3) {
      $credito = $subtotal/2;
      $sql = "UPDATE proveedores set credito = credito + ? where id = ?";
      $q = $pdo->prepare($sql);
      $q->execute(array($credito,$idProveedor));
      $pagado = 1;
    }
    
    $sql = "INSERT INTO ventas_detalle(id_venta, id_producto, cantidad, precio, subtotal, id_modalidad, pagado) VALUES (?,?,?,?,?,?,?)";
    $q = $pdo->prepare($sql);
    $q->execute(array($idVenta,$id_producto,$cantidad,$precio,$subtotal,$modalidad,$pagado));

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
  
  $totalConDescuento = $total;
  if (!empty($_POST['id_descuento'])) {
    $sql2 = "SELECT minimo_compra, minimo_cantidad_prendas, monto_fijo, porcentaje FROM descuentos WHERE id = ? ";
    $q2 = $pdo->prepare($sql2);
    $q2->execute(array($_POST['id_descuento']));
    $data2 = $q2->fetch(PDO::FETCH_ASSOC);

    if ($modoDebug==1) {
      $q2->debugDumpParams();
      echo "<br><br>Afe: ".$q2->rowCount();
      echo "<br><br>";
    }

    if ($data2['minimo_compra'] > 0) {
      if ($total > $data2['minimo_compra']) {
        $totalConDescuento = $totalConDescuento - $data2['monto_fijo'];
        $totalConDescuento = $totalConDescuento - (($totalConDescuento*$data2['porcentaje'])/100);
      }
    } else if ($data2['minimo_cantidad_prendas'] > 1) {
      if ($cantPrendas >= $data2['minimo_cantidad_prendas']) {
        $totalConDescuento = $totalConDescuento - $data2['monto_fijo'];
        $totalConDescuento = $totalConDescuento - (($totalConDescuento*$data2['porcentaje'])/100);
      }
    }
  }
  
  $sql = "UPDATE ventas set total = ?, id_descuento_aplicado = ?, total_con_descuento = ? WHERE id = ?";
  $q = $pdo->prepare($sql);
  $q->execute(array($total,$_POST['id_descuento'],$totalConDescuento,$idVenta));

  if ($modoDebug==1) {
    $q->debugDumpParams();
    echo "<br><br>Afe: ".$q->rowCount();
    echo "<br><br>";
  }

  /*
  include './../external/afip/Afip.php';

  $afip = new Afip(array('CUIT' => 20351290340));

  $server_status = $afip->ElectronicBilling->GetServerStatus();
  echo 'Este es el estado del servidor:';
  var_dump($server_status);*/
  /*
  $punto_venta=1;
  //var_dump($_POST);
  
  $tipo_comprobante=$_POST["tipo_comprobante"];
  if($tipo_comprobante!="R"){
    $ImpTotal=$total;
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
    
    $CAE=$res['CAE'];
    $CAEFchVto=$res['CAEFchVto'];
    $voucher_number=$res['voucher_number'];
    var_dump($CAE); //CAE asignado el comprobante
    var_dump($CAEFchVto); //Fecha de vencimiento del CAE (yyyy-mm-dd)
    var_dump($voucher_number); //Número asignado al comprobante

  }
  */

  if ($modoDebug==1) {
    $pdo->rollBack();
    die();
  }
  Database::disconnect();
  
  header("Location: listarVentas.php");
}?>
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
                <div class="col">
                  <div class="page-header-left">
                    <h3><?php include("title.php"); ?></h3>
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="#"><i data-feather="home"></i></a></li>
                      <li class="breadcrumb-item">Nueva Venta</li>
                    </ol>
                  </div>
                </div>
                <!-- Bookmark Start-->
                <div class="col">
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
                    <h5>Nueva Venta</h5>
                  </div>
                  <form class="form theme-form" role="form" method="post" action="nuevaVenta.php">
                    <div class="card-body">
                      <div class="row">
                        <div class="col">
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Almacen</label>
                            <div class="col-sm-9">
                              <select name="id_almacen" id="id_almacen" class="js-example-basic-single col-sm-12" required="required" onChange="jsListarProductos(this.value);">
                                <option value="">Seleccione...</option><?php
                                $pdo = Database::connect();
                                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                $sqlZon = "SELECT `id`, `almacen` FROM `almacenes` WHERE activo = 1";
                                if ($_SESSION['user']['id_perfil'] != 1) {
                                  $sqlZon .= " and id = ".$_SESSION['user']['id_almacen']; 
                                }
                                $q = $pdo->prepare($sqlZon);
                                $q->execute();
                                while ($fila = $q->fetch(PDO::FETCH_ASSOC)) {
                                  echo "<option value='".$fila['id']."'";
                                  echo ">".$fila['almacen']."</option>";
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
                              <table class="display" id="dataTables-example666">
                                <thead>
                                  <tr>
                                    <th>ID</th>
                                    <th>Código</th>
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Cantidad</th>
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
                                    <th>ID</th>
                                    <th>Código</th>
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
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
                            <label class="col-sm-3 col-form-label">Total</label>
                            <div class="col-sm-9"><label id="total_compra">$ 0</label></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Tipo de comprobante</label>
                            <div class="col-sm-9">
                            <select name="tipo_comprobante" id="tipo_comprobante" class="js-example-basic-single col-sm-12" required="required">
                                <option value="">Seleccione...</option>
                                <option value="A" disabled>Factura A</option>
                                <option value="B" disabled>Factura B</option>
                                <option value="R">Recibo</option>
                              </select>
                            </div>
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
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Forma de Pago</label>
                            <div class="col-sm-9">
                              <select name="id_forma_pago" id="id_forma_pago" class="js-example-basic-single col-sm-12" required="required">
                                <option value="">Seleccione...</option><?php
                                $pdo = Database::connect();
                                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                $sqlZon = "SELECT `id`, `forma_pago` FROM `forma_pago` WHERE 1";
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
                              <select name="id_descuento" id="id_descuento" class="js-example-basic-single col-sm-12">
                                <option value="">Seleccione...</option><?php
                                $pdo = Database::connect();
                                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                $sqlZon = "SELECT `id`, `descripcion` FROM `descuentos` WHERE activo = 1 and vigencia_desde <= now() and vigencia_hasta >= now() ";
                                $q = $pdo->prepare($sqlZon);
                                $q->execute();
                                while ($fila = $q->fetch(PDO::FETCH_ASSOC)) {
                                  echo "<option value='".$fila['id']."'";
                                  echo ">".$fila['descripcion']."</option>";
                                }
                                Database::disconnect();?>
                              </select>
                            </div>
                          </div>
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
    <!-- <script src="assets/js/typeahead/handlebars.js"></script>
    <script src="assets/js/typeahead/typeahead.bundle.js"></script>
    <script src="assets/js/typeahead/typeahead.custom.js"></script> -->
    <script src="assets/js/chat-menu.js"></script>
    <script src="assets/js/tooltip-init.js"></script>
    <!-- <script src="assets/js/typeahead-search/handlebars.js"></script>
    <script src="assets/js/typeahead-search/typeahead-custom.js"></script> -->
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
      if(this.value=="A"){
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
    })

    /*$("form").on("submit",function(e){
      e.preventDefault();
      $('#dataTables-example666').DataTable().search( '' ).columns().search( '' ).draw();
      $(".cantidad").each(function(){
        if(this.value<1 || isNaN(this.value)){
          this.disabled=true;
        }
      })
      this.submit();
    });*/
    $("form").on("submit",function(e){
      e.preventDefault();
      if($('#productos_vender tbody tr').length){
        //console.log("submit");
        this.submit();
      }else{
        alert("Añada algún producto")
      }
    });
		
    function jsListarProductos(val) {
      $("#dataTables-example666").dataTable().fnDestroy();
      $('#dataTables-example666').DataTable({
        //'ajax': 'ajaxListarProductos.php',
        "ajax" : "ajaxVentas.php?almacen="+val,//&id_vehiculo="+id_vehiculo+"
				stateSave: true,
				responsive: true,
        serverSide: true,
        processing: true,
        scrollY: false,
        "columns":[
          {"data": "cb"},//"fecha_mostrar"},
          {"data": "codigo"},//"vehiculo.marca"},
          {"data": "categoria"},//"vehiculo.modelo"},
          {"data": "descripcion"},//"vehiculo.patente"},
          {
            render: function(data, type, row, meta) {
              return `
                <input type="hidden" disabled name="id_modalidad[]" class="enviar_form id_modalidad" value="${row.id_modalidad}">
                <input type="hidden" disabled name="id_proveedor[]" class="enviar_form id_proveedor" value="${row.id_proveedor}">
                <input type="hidden" disabled name="id_producto[]" class="enviar_form id_producto" value="${row.id_producto}">
                <input type="hidden" disabled name="stock[]" class="enviar_form stock" value="${row.cantidad}">
                <input type="hidden" disabled name="precio[]" class="enviar_form precio" value="${row.precio}">`+
                new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(row.precio);
            },
            className: 'dt-body-right',
            orderDataType: "num-fmt"
          },{
            "data": "cantidad",
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
    }
	
	  $(document).ready(function() {
			jsListarProductos(0)
		});

    $(document).on("click",".btnAnadir",function(){
      let prod_anadido=$("input[name='id_stock[]'][value='"+this.dataset.id_stock+"']");
      //console.log(prod_anadido)
      //console.log("cantidad encontrada");
      if(prod_anadido.length==0){
        if(parseInt(this.dataset.cantidad)>0){
          let fila=$(this).parent().parent();
          let clon=fila.clone();
          let btn=clon.find("button");
          //console.log(btn);
          btn.parent().html(`
            <input type='hidden' name='id_stock[]' value='${this.dataset.id_stock}'></input>
            <input type='number' name='cantidad[]' class='form-control form-control-sm cantidad' min='1' max='${this.dataset.cantidad}' value="1" required></input>
          `);
          clon.append(`
            <td class='text-center'>
              <img src='img/icon_baja.png' class='btnEliminar' width='24' height='25' border='0' alt='Eliminar' title='Eliminar'>
            </td>
          `);
          /*clon.find("input[name='precio[]']").attr("disabled",false);
          clon.find("input[name='stock[]']").attr("disabled",false);*/
          clon.find(".enviar_form").attr("disabled",false);

          $("#productos_vender tbody").append(clon[0]);

          actualizarMontoTotal();
        }else{
          alert("No hay stock suficiente")
        }
      }else{
        alert("El producto ya fue añadido")
      }
    })

    function actualizarMontoTotal(){
      let total=0;
      $("#productos_vender tbody tr").each(function(){
        total+=parseInt($(this).find(".precio").val())*parseInt($(this).find(".cantidad").val());
      })
      if(isNaN(total)){total=0;}
      $("#total_compra").html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(total))
    }

    $(document).on("keyup change",".cantidad",function(){
      actualizarMontoTotal()
    })

    $(document).on("click",".btnEliminar",function(){
      $(this).parent().parent().remove();
      actualizarMontoTotal()
    })

    /*function cargarTabla(){
      $('#dataTables-example666').DataTable({
				
			});
    }*/
		</script>
		<script src="https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"></script>
		
  </body>
</html>