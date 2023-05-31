<?php
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

  $modoDebug=1;

  if ($modoDebug==1) {
    $pdo->beginTransaction();
    var_dump($_POST);
    
  }
  
  $sql = "INSERT INTO canjes (fecha_hora, id_proveedor, id_almacen, total, id_usuario) VALUES (now(),?,?,0,?)";
  $q = $pdo->prepare($sql);
  $q->execute(array($_POST['id_proveedor_canje'],$_POST['id_almacen'],$_SESSION['user']['id']));
  $idCanje = $pdo->lastInsertId();

  if ($modoDebug==1) {
    $q->debugDumpParams();
    echo "<br><br>Afe: ".$q->rowCount();
    echo "<br><br>";
  }
  
  $total = 0;
  //$sql = " SELECT s.id, p.codigo, c.categoria, p.descripcion, p.precio, s.cantidad, s.id_modalidad, p.id_proveedor, p.id FROM stock s inner join productos p on p.id = s.id_producto inner join categorias c on c.id = p.id_categoria WHERE s.cantidad > 0 and p.activo = 1 and s.id_almacen = ".$_POST["id_almacen"];
  //foreach ($pdo->query($sql) as $row) {
    //if ($_POST['cantidad_'.$row[0]] > 0) {
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
      echo "<br><br>porcentaje: ".$porcentaje;
      echo "<br><br>minimo_compra: ".$minimo_compra;
      echo "<br><br>minimo_cantidad_prendas: ".$minimo_cantidad_prendas;
      echo "<br><br>cantidad de prendas: ".$cantPrendas;
      echo "<br><br>";
    }
    
  }

  $total_input = $_POST['total_input'];
  $credito_numero = $_POST['credito_numero'];
  if($total_input >= 0){
    //Datos proveedor 
    $sql3 = "SELECT email, dni, nombre, apellido, telefono, id_modalidad FROM proveedores WHERE id = ? ";
    $q3 = $pdo->prepare($sql3);
    $q3->execute(array($_POST['id_proveedor_canje']));
    $data3 = $q3->fetch(PDO::FETCH_ASSOC);

    $nombre_cliente = $data3['nombre'] . ' ' . $data3['apellido'];
    $dni = $data3['dni'];
    $email = $data3['email'];
    $telefono = $data3['telefono'];
    $direccion = '';

    //Alta Nueva Venta
    $sql = "INSERT INTO ventas(fecha_hora, nombre_cliente, dni, direccion, email, telefono, id_almacen, total, tipo_comprobante, id_usuario, id_forma_pago,modalidad_venta) VALUES (now(),?,?,?,?,?,?,0,?,?,?,?)";
    $q = $pdo->prepare($sql);
    $q->execute(array($nombre_cliente,$dni,$direccion,$email,$telefono,$_POST['id_almacen'], $tipo_comprobante,$_SESSION['user']['id'],$_POST['id_forma_pago'],$_POST["modalidad_venta"]));
    $idVenta = $pdo->lastInsertId();
  }

  $totalConDescuento = 0;
  foreach ($_POST['id_stock'] as $key => $id_stock) {

    $cantidad = $_POST['cantidad'][$key];
    $cantidadAnterior = $_POST['stock'][$key];
    $precio = $_POST['precio'][$key];
    $modalidad = $_POST["id_modalidad"][$key];
    $idProveedor = $_POST["id_proveedor"][$key];
    $idProducto = $_POST["id_producto"][$key];
    $total_input = $_POST['total_input'];
    $credito_numero = $_POST['credito_numero'];
    $forma_pago = 1;

    //$idProducto = $row[8];
    //$cantidad = $_POST['cantidad_'.$row[0]];
    //$cantidadAnterior = $row[5];
    //$precio = $row[4];
    $subtotal = $cantidad * $precio;
    $total += $subtotal;

    echo "<br><br>total de productos: ".$total;
    echo "<br><br>";
    
    echo "<br><br>subtotal: ".$subtotal;
    echo "<br><br>";
    //$modalidad = $row[6];
    //$idProveedor = $row[7];

    if ($minimo_compra!="" and $total>$minimo_compra and $minimo_cantidad_prendas!="" and $cantPrendas>=$minimo_cantidad_prendas) {
      //$totalConDescuento = $totalConDescuento - $monto_fijo;
      $subtotal-=(($subtotal*$porcentaje)/100);
      //var_dump("subtotal: " . $subtotal);

      if ($modoDebug==1) {
        //$q->debugDumpParams();
        echo "<br><br>Subtotal Con Descuento: ". $subtotal;
        echo "<br><br>";
      }
    }

    $totalConDescuento += $subtotal;

    $deuda_proveedor=calcularDeudaProveedor($forma_pago,$modalidad,$subtotal);

    $pagado = 0;
    $credito = 0;
    if ($modalidad == 1) {
      $pagado = 1;
    } else if ($modalidad == 40) {
      $pagado = 0;
    } else if ($modalidad == 50) {
      $credito = $subtotal/2;
      $sql = "UPDATE proveedores set credito = credito + ? where id = ?";
      $q = $pdo->prepare($sql);
      $q->execute(array($credito,$idProveedor));
      $pagado = 1;
      if ($modoDebug==1) {
        $q->debugDumpParams();
        echo "<br><br>Proveedores: ".$q->rowCount();
        echo "<br><br>";
      }
    }


    
    $sql = "INSERT INTO canjes_detalle (id_canje, id_producto, cantidad, precio, subtotal,id_modalidad, deuda_proveedor, pagado) VALUES (?,?,?,?,?,?,?,?)";
    $q = $pdo->prepare($sql);
    $q->execute(array($idCanje,$idProducto,$cantidad,$precio,$subtotal,$modalidad,$deuda_proveedor,$pagado));

    if ($modoDebug==1) {
      $q->debugDumpParams();
      echo "<br><br>Afe: ".$q->rowCount();
      echo "<br><br>";
    }
    
    //Se hace una venta en el caso de que el total sea mayor que el credito del proveedor
    if($total_input >= 0){
      $sql = "INSERT INTO ventas_detalle (id_venta, id_producto, cantidad, precio, subtotal, id_modalidad, deuda_proveedor, pagado) VALUES (?,?,?,?,?,?,?,?)";
      $q = $pdo->prepare($sql);
      $q->execute(array($idVenta,$id_producto,$cantidad,$precio,$subtotal,$modalidad,$deuda_proveedor,$pagado));
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
      
    //}
  }
  
  $total_input = $_POST['total_input'];
  $credito_numero = $_POST['credito_numero'];
  if($total_input >= 0){
    $total_pagar =  $credito_numero;
  }else{
    $total_pagar = $credito_numero - $total;
  }
  echo "<br><br>Total a Pagar: ".$total_pagar;
    echo "<br><br>";
  $sql = "UPDATE canjes set total = ? WHERE id = ?";
  $q = $pdo->prepare($sql);
  $q->execute(array($total_pagar,$idCanje));

  //Descuentos
  $id_descuento=NULL;
  if(isset($_POST['id_descuento']) and $_POST['id_descuento']!=""){
    $id_descuento=$_POST['id_descuento'];
  }

  if($id_descuento !== NULL){
    //Tiene descuento
    $sql = "UPDATE canjes set total = ?, id_descuento_aplicado = ?, total_con_descuento = ? WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($total,$id_descuento,$totalConDescuento,$idCanje));
    if ($modoDebug==1) {
      $q->debugDumpParams();
      echo "<br><br>Afe: ".$q->rowCount();
      echo "<br><br>";
    }
  }else{
    //No tiene descuento
    $sql = "UPDATE canjes set total = ?, id_descuento_aplicado = ?, total_con_descuento = ? WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($total,NULL,$totalConDescuento,$idCanje));
    if ($modoDebug==1) {
      $q->debugDumpParams();
      echo "<br><br>Afe: ".$q->rowCount();
      echo "<br><br>";
    }
  }

  //Si el total es mayor a cero quiere decir que tiene que pagar y el credito queda en cero. Si es menor se le tiene que restar el total al credito.
  $total_input = $_POST['total_input'];
  $credito_numero = $_POST['credito_numero'];
  if($total_input >= 0){
    $total_input = 0;
  }else{
    $total_input = $credito_numero + $total_input;
  }
  $sql = "UPDATE proveedores set credito = credito - ? WHERE id = ?";
  $q = $pdo->prepare($sql);
  $q->execute(array($total_input,$_POST['id_proveedor_canje']));
  if ($modoDebug==1) {
    $q->debugDumpParams();
    echo "<br><br>Afe: ".$q->rowCount();
    echo "<br><br>Total: ".$total_input;
    echo "<br><br>Credito sobrante: ".$credito_numero;
  }
  
  
  if ($modoDebug==1) {
    $pdo->rollBack();
    die();
  }
  Database::disconnect();
  
  header("Location: listarCanjes.php");
}?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include('head_forms.php');?>
	<link rel="stylesheet" type="text/css" href="assets/css/select2.css">
	<link rel="stylesheet" type="text/css" href="assets/css/datatables.css">
  </head>
  <body class="light-only">
    <!-- Loader ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper">
	  <?php include('header.php');?>
	  
      <!-- Page Header Start-->
      <div class="page-body-wrapper">
		<?php include('menu.php');?>
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
                      <li class="breadcrumb-item">Nuevo Canje</li>
                    </ol>
                  </div>
                </div>
                <!-- Bookmark Start-->
                <div class="col-2">
                  <div class="bookmark pull-right">
                    <ul>
                      <li><a  target="_blank" data-container="body" data-toggle="popover" data-placement="top" title="" data-original-title="<?php echo date('d-m-Y');?>"><i data-feather="calendar"></i></a></li>
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
                    <h5>Nuevo Canje</h5>
                  </div>
				          <form class="form theme-form" role="form" method="post" action="nuevoCanje.php">
                    <div class="card-body">
                      <div class="row">
                        <div class="col">
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Proveedor</label>
                            <div class="col-sm-9">
                              <select name="id_proveedor_canje" id="id_proveedor" class="js-example-basic-single col-sm-12" required="required">
                                <option value="">Seleccione...</option><?php
                                $pdo = Database::connect();
                                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                $sqlZon = "SELECT id, dni, nombre, apellido, credito FROM proveedores WHERE activo = 1 and credito>0";
                                $q = $pdo->prepare($sqlZon);
                                $q->execute();
                                while ($fila = $q->fetch(PDO::FETCH_ASSOC)) {
                                  echo "<option value='".$fila['id']."'data-credito='".$fila['credito']."'";
                                  echo ">".$fila['nombre']." ".$fila['apellido']." (".$fila['dni'].")</option>";
                                }
                                Database::disconnect();?>
                              </select>
                              <input type="hidden" id="credito_numero" name="credito_numero" value="">
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Credito Disponible</label>
                            <div class="col-sm-9"><label id="credito_disponible"><?= "$".number_format(0, 2, ',', '.');?></label></div>
                            <input type="hidden" id="credito_disponible" name="credito_disponible" value="">
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Almacen</label>
                            <div class="col-sm-9">
                            <select name="id_almacen" id="id_almacen" class="js-example-basic-single col-sm-12" required="required" onChange="jsListarProductos(this.value);">
                            <option value="">Seleccione...</option>
                            <?php 
                            $pdo = Database::connect();
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $sqlZon = "SELECT `id`, `almacen` FROM `almacenes` WHERE activo = 1";
                            if ($_SESSION['user']['id_perfil'] == 2) {
                              $sqlZon .= " and id = ".$_SESSION['user']['id_almacen']; 
                            }
                            $q = $pdo->prepare($sqlZon);
                            $q->execute();
                            while ($fila = $q->fetch(PDO::FETCH_ASSOC)) {
                              echo "<option value='".$fila['id']."'";
                              echo ">".$fila['almacen']."</option>";
                            }
                            Database::disconnect();
                            ?>
                            </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Productos en stock</label>
                          </div>
                          <div class="form-group row">
                            <div class="col-sm-12">
                              <table class="display" id="dataTables-example666">
                                <thead>
                                  <tr>
                                  <th>ID</th>
                                  <th>Código</th>
                                  <th>Categoría</th>
                                  <th>Descripción</th>
                                  <th>Stock</th>
                                  <th>Precio</th>
                                  <th>Accion</th>
                                  </tr>
                                </thead>
                                <tbody>
                                </tbody>
                              </table>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Productos a canjear</label>
                          </div>
                          <div class="form-group row">
                            <div class="col-sm-12">
                              <table class="table" id="productos_canjear">
                                <thead>
                                  <tr>
                                    <th>ID</th>
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
                            <label class="col-sm-3 col-form-label">Total productos a canjear</label>
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
                              <select name="id_descuento" id="id_descuento" class="js-example-basic-single col-sm-12">
                                <option value="">Seleccione...</option><?php
                                $pdo = Database::connect();
                                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                $sqlZon = "SELECT d.id as id_descuento, d.descripcion, d.minimo_compra, d.minimo_cantidad_prendas, d.monto_fijo, d.porcentaje, dfp.id_forma_pago, f.forma_pago FROM descuentos_x_formapago dfp INNER JOIN descuentos d on d.id = dfp.id_descuento INNER JOIN forma_pago f on f.id = dfp.id_forma_pago WHERE dfp.id_forma_pago = '1' AND vigencia_desde <= now() and vigencia_hasta >= now()";
                                $q = $pdo->prepare($sqlZon);
                                $q->execute();
                                while ($fila = $q->fetch(PDO::FETCH_ASSOC)) {
                                  echo "<option value='".$fila['id_descuento']. "' data-porcentaje='" . $fila['porcentaje'] ."'";
                                  echo ">".$fila['descripcion']."</option>";
                                }
                                Database::disconnect();?>
                                
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Credito a Usar</label>
                            <div class="col-sm-9"><label id="credito_usar"><?= "$".number_format(0, 2, ',', '.');?></label></div>
                            <input type="hidden" id="credito_usar" name="credito_usar" value="">
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Total a Pagar</label>
                            <div class="col-sm-9"><label id="total_compra"><?= "$".number_format(0, 2, ',', '.');?></label></div>
                            <input type="hidden" id="total_input" name="total_input" value="">
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="card-footer">
                      <div class="col-sm-9 offset-sm-3">
                        <button class="btn btn-primary" type="submit">Crear</button>
						<a href="listarCanjes.php" class="btn btn-light">Volver</a>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <!-- Container-fluid Ends-->
        </div>
        <!-- footer start-->
		<?php include("footer.php"); ?>
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
      $("form").on("submit",function(e){
        e.preventDefault();
        let cant_productos=$('#productos_canjear tbody tr').length;
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
          }
        }else{
          alert("Añada algún producto")
        }
      });
      
      function jsListarProductos(val) {
        $("#dataTables-example666").dataTable().fnDestroy();
        $('#dataTables-example666').DataTable({
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
                  <input type="hidden" disabled name="precio[]" class="enviar_form precio" value="${row.precio}">`+
                  new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(row.precio);
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
        jsListarProductos(0)
      });
      $("#id_descuento").change(function() {
        //mostrarTotalDescuento();
        actualizarMontoTotal();
      })

      $("#id_proveedor").on("change", function () {
        let option_elegido = $(this).find("option:selected");
        let credito = option_elegido.data("credito");
        $("#credito_numero").val(credito);
        $("#credito_disponible").html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(credito));
        $("#credito_usar").html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(credito));
        console.log("Credito: " + credito);
        actualizarMontoTotal();
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
              <input type='number' name='cantidad[]' class='form-control form-control-sm cantidad mx-auto' style='width: 60px;' min='1' max='${this.dataset.cantidad}' value="1" required></input>
            `);
            clon.append(`
              <td class='text-center'>
                <img src='img/icon_baja.png' class='btnEliminar' width='24' height='25' border='0' alt='Eliminar' title='Eliminar'>
              </td>
            `);
            /*clon.find("input[name='precio[]']").attr("disabled",false);
            clon.find("input[name='stock[]']").attr("disabled",false);*/
            clon.find(".enviar_form").attr("disabled",false);

            $("#productos_canjear tbody").append(clon[0]);

            actualizarMontoTotal();
          }else{
            alert("No hay stock suficiente")
          }
        }else{
          alert("El producto ya fue añadido")
        }
      })
      
      
      function calcularTotalCompra(){
        let total=0;
        $("#productos_canjear tbody tr").each(function(){
          total+=parseInt($(this).find(".precio").val())*parseInt($(this).find(".cantidad").val());
        })
        if(isNaN(total)){total=0;}
        return total
      }

      /*function actualizarMontoTotal(){
        let total=calcularTotalCompra();
        $("#subtotal_compra").html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(total))
    
        var porcentaje = $("#id_descuento option:selected").data("porcentaje");
        console.log('Total: '+ total + " Porcentaje: " + porcentaje);
       
        let totalConDescuento=total;
        console.log('Porcentaje: ' + porcentaje + ' ' + 'Total con descuento: ' + totalConDescuento);
        if(porcentaje!=undefined){
          console.log('Porcentaje: ' + porcentaje + ' ' + 'Total con descuento: ' + totalConDescuento);
          let descuento=porcentaje*total/100;
          totalConDescuento=total-descuento;
        }
        
        //console.log(parseInt(total)-parseInt(totalConDescuento))
        if(isNaN(totalConDescuento)){totalConDescuento=0;}
        $("#total_compra").html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(totalConDescuento));
        
      }*/

      function actualizarMontoTotal(){
        let total=calcularTotalCompra();
        let credito=$("#credito_numero").val();
        console.log("Credito en actualizar" + credito);
        $("#subtotal_compra").html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(total));
        if(credito){
          let credito_diferencia = credito - total;
          credito_usar = credito - credito_diferencia;
          if(credito_usar >= credito){
            credito_usar = credito;
          }
          $("#credito_usar").html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(credito_usar));
          console.log("pasa por credito");
        }
        
        mostrarTotalDescuento(total)
      }

      function mostrarTotalDescuento(total){
        let totalFinal = 0;
        let porcentaje=$("#id_descuento option:selected").data("porcentaje");
        let subtotal=$("#subtotal_compra").val();
        let totalConDescuento= total;
        let totalConCredito = parseFloat($("#credito_numero").val());
        //console.log("totalConDescuento: " + totalConDescuento + ", totalCredito: " + totalConCredito);

        if(porcentaje!=undefined){
          let descuento=porcentaje*total/100;
          console.log("Total sin descuento: " + total);
          console.log("Porcentaje: " + porcentaje, "Subtotal: " + subtotal);
          let porcentaje_p = (((total)*porcentaje)/100);
          let totalFinal = totalConDescuento - totalConCredito;
          if(isNaN(totalFinal)){totalFinal=0;}
          totalFinal = totalFinal - porcentaje_p;
          console.log("Total con descuento: " + totalFinal);
          $("#total_compra").html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(totalFinal));
        }else{
          console.log("Total sin descuento: " + totalConDescuento + ", Credito: " + totalConCredito);
          if(isNaN(totalFinal)){totalFinal=0;}
          totalFinal = totalConDescuento - totalConCredito;
          if(totalFinal <= 0){
            totalFinal = 0;
          }
          $("#total_compra").html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(totalFinal));
        }
      }

      $(document).on("keyup change",".cantidad",function(){
        actualizarMontoTotal()
      })

      $(document).on("click",".btnEliminar",function(){
        $(this).parent().parent().remove();
        actualizarMontoTotal()
      })
		
		</script>
		<script src="https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"></script>
  </body>
</html>