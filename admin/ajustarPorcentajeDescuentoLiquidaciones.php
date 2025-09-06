<?php
require("config.php");
if(empty($_SESSION['user']['id_perfil'])){
  header("Location: index.php");
  die("Redirecting to index.php"); 
}
require 'database.php';
require 'funciones.php';
$porcentaje="";
$desde=$hasta=$hoy=date("Y-m-d");?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include('head_forms.php');?>
	  <link rel="stylesheet" type="text/css" href="assets/css/select2.css">
    <style>
      .card-header .title {
        font-size: 17px !important;
        color: #000 !important;
        display: inline !important;
      }
      .card-header .accicon {
        float: right;
        font-size: 20px;  
        width: 1.2em;
      }
      .card-header{
        cursor: pointer;
        border-bottom: none;
      }
      .card{
        border: 1px solid #ddd;
      }
      .card-body{
        border-top: 1px solid #ddd;
      }
      .card-header:not(.collapsed) .rotate-icon {
        transform: rotate(180deg);
      }
    </style>
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
                      <li class="breadcrumb-item">Ajustar el porcentaje a descontar en liquidadciones pendientes a proveedoras</li>
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
            <div class="row"><?php

if ( !empty($_POST)) {
  
  // insert data
  $pdo = Database::connect();
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $modoDebug=0;

  if ($modoDebug==1) {
    $pdo->beginTransaction();
    var_dump($_POST);
  }

  $desde=$_POST["desde"];
  $hasta=$_POST["hasta"];
  $porcentaje=$_POST["porcentaje"];
  
  $aProductosVendidos=[];
  $sql = " SELECT vd.id,v.id_forma_pago,vd.id_modalidad,vd.subtotal,vd.deuda_proveedor,vd.id_venta,fp.forma_pago,m.modalidad,date_format(v.fecha_hora,'%d/%m/%Y %H:%i') AS fecha_hora FROM ventas_detalle vd INNER JOIN ventas v ON vd.id_venta=v.id INNER JOIN forma_pago fp ON v.id_forma_pago=fp.id INNER JOIN modalidades m ON vd.id_modalidad=m.id WHERE DATE(v.fecha_hora)>='$desde' AND DATE(v.fecha_hora)<='$hasta' AND vd.pagado=0 AND v.id_forma_pago!=1";
  foreach ($pdo->query($sql) as $row) {
    $aProductosVendidos[]=[
      "tipo"                  =>"V",
      "id_detalle"            =>$row["id"],
      "id_padre"              =>$row["id_venta"],
      "fecha_hora"            =>$row["fecha_hora"],
      "id_forma_pago"         =>$row["id_forma_pago"],
      "forma_pago"            =>$row["forma_pago"],
      "id_modalidad"          =>$row["id_modalidad"],
      "modalidad"             =>$row["modalidad"],
      "subtotal"              =>$row["subtotal"],
      "vieja_deuda_proveedor" =>$row["deuda_proveedor"],
    ];
  }

  $sql = " SELECT cd.id,v.id_forma_pago,fp.forma_pago,cd.id_modalidad,cd.subtotal,cd.deuda_proveedor,cd.id_canje,m.modalidad,date_format(c.fecha_hora,'%d/%m/%Y %H:%i') AS fecha_hora FROM canjes_detalle cd INNER JOIN canjes c ON cd.id_canje=c.id INNER JOIN modalidades m ON cd.id_modalidad=m.id INNER JOIN ventas v ON c.id_venta=v.id INNER JOIN forma_pago fp ON v.id_forma_pago=fp.id WHERE DATE(c.fecha_hora)>='$desde' AND DATE(c.fecha_hora)<='$hasta' AND cd.pagado=0 AND c.id_venta IS NOT NULL AND v.id_forma_pago!=1";
  foreach ($pdo->query($sql) as $row) {
    /*$sql = "SELECT forma_pago FROM formas_pago WHERE id = ? ";
    $q = $pdo->prepare($sql);
    $q->execute(array($row["id_forma_pago"]));
    $data = $q->fetch(PDO::FETCH_ASSOC);*/

    $aProductosVendidos[]=[
      "tipo"                  =>"C",
      "id_detalle"            =>$row["id"],
      "id_padre"              =>$row["id_canje"],
      "fecha_hora"            =>$row["fecha_hora"],
      "id_forma_pago"         =>$row["id_forma_pago"],
      "forma_pago"            =>$row["forma_pago"],
      "id_modalidad"          =>$row["id_modalidad"],
      "modalidad"             =>$row["modalidad"],
      "subtotal"              =>$row["subtotal"],
      "vieja_deuda_proveedor" =>$row["deuda_proveedor"],
    ];
  }

  $c=$c2=0;
  $tbody="";
  foreach ($aProductosVendidos as $row) {
    $c2++;
    //var_dump($row);
    
    $tipo=$row["tipo"];
    $id_padre=$row["id_padre"];
    $fecha_hora=$row["fecha_hora"];
    $id_forma_pago=$row["id_forma_pago"];
    $forma_pago=$row["forma_pago"];
    $id_modalidad=$row["id_modalidad"];
    $modalidad=$row["modalidad"];
    $subtotal=$row["subtotal"];
    $vieja_deuda_proveedor=$row["vieja_deuda_proveedor"];

    $nueva_deuda_proveedor = calcularDeudaProveedorConPorcentaje($row["id_forma_pago"],$row["id_modalidad"],$row["subtotal"],$porcentaje);

    /*calculo inverso*/
    $porcentaje_modalidad=porcentaje_segun_modalidad($id_modalidad);
    $porcentaje_vieja_deuda_proveedor=100-($vieja_deuda_proveedor*100/($subtotal*$porcentaje_modalidad));
    /*calculo inverso*/

    if($tipo=="V"){
      $lugar="Venta";
      $table_name="ventas_detalle";
    }else{
      $lugar="Canje";
      $table_name="canjes_detalle";
    }

    $tbody.="
    <tr>
      <td>".$c2."</td>
      <td><a href='ver".$lugar.".php?id=".$id_padre."'><img src='img/eye.png' width='24' height='15' border='0' alt='Ver ".$lugar."' title='Ver ".$lugar."'> ".$tipo."#".$id_padre."</a></td>
      <td>".$fecha_hora."</td>
      <td>".$modalidad." (".($porcentaje_modalidad*100)."%)</td>
      <td>".$forma_pago."</td>
      <td>$".number_format($subtotal,2,",",".")."</td>
      <td align='right'>$".number_format($vieja_deuda_proveedor,2,",",".")." (".$porcentaje_vieja_deuda_proveedor."%)</td>
      <td align='right'>$".number_format($nueva_deuda_proveedor,2,",",".")." (".$porcentaje."%)</td>
    </tr>";

    if($nueva_deuda_proveedor!=$vieja_deuda_proveedor){
      $sql = "UPDATE $table_name set deuda_proveedor = ? where id = ?";
      $q = $pdo->prepare($sql);
      $q->execute(array($nueva_deuda_proveedor,$row['id_detalle']));
      $afe = $q->rowCount();

      if($afe==1){
        $c++;
      }

      if ($modoDebug==1) {
        $q->debugDumpParams();
        echo "<br><br>Afe: ".$afe;
        echo "<br><br>";
      }
    }else{
      if ($modoDebug==1) {
        echo "SON IGUALES<br>";
      }
    }
  }

  if ($modoDebug==1) {
    $pdo->rollBack();
    //die();
  }

  Database::disconnect();

  if($c>0){
    $class="success";
    $text=$c." liquidaciones sin pagar actualizadas correctamente!";
    //echo "<div class='row text-'><div class='col-sm-12'><h5></h5></div></div>";
  }else{
    $class="warning";
    $text="No se han encontrado liquidaciones sin pagar para actualizar!";
    //echo "<div class='row text-warning'><div class='col-sm-12'><h5></h5></div></div>";
  }?>
  <!-- <div class="col-12 alert alert-<?=$class?>" role="alert"><?=$text?></div> -->
  <div class="accordion col-12" id="accordionExample">
    <div class="card">
      <div class="card-header bg-<?=$class?>" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true">     
        <span class="title"><?=$text?></span>
        <span class="accicon"><i class="fa fa-angle-up rotate-icon"></i></span>
      </div>
      <!-- <div class="card-header bg-<?=$class?>" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true">     
        <p class="title"><?=$text?></p>
        <p class="accicon"><i class="fa fa-angle-up rotate-icon"></i></p>
      </div>
      <div class="card-header bg-<?=$class?>" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true">     
        <div style="display: inline;" class="title"><?=$text?></div>
        <div style="display: inline;" class="accicon"><i class="fa fa-angle-down rotate-icon"></i></div>
      </div> -->
      <div id="collapseOne" class="collapse" data-parent="#accordionExample">
        <div class="card-body">
          <table class="table">
            <thead>
              <th>#</th>
              <th>Venta</th>
              <th>Fecha y hora</th>
              <th>Modalidad</th>
              <th>Forma de Pago</th>
              <th>Subtotal</th>
              <th>Deuda proveedor anterior</th>
              <th>Nueva deuda proveedor</th>
            </thead>
            <tbody><?=$tbody?></tbody>
          </table>
        </div>
      </div>
    </div>
  </div><?php
}?>
              <div class="col-sm-12">
                <div class="card">
                  <div class="card-header">
                    <h5>Ajustar el porcentaje a descontar en liquidadciones pendientes a proveedoras</h5>
                  </div>

				          <form class="form theme-form" role="form" method="post" action="ajustarPorcentajeDescuentoLiquidaciones.php">
                    <div class="card-body">
                      <div class="row">
                        <div class="col">

                          <div class="form-group row">
                            <div class="col-sm-3">Desde:</div>
                            <div class="col-sm-9"><input type="date" class="form-control" name="desde" id="desde" value="<?=$desde?>" required></div>
                          </div>
                          <div class="form-group row">
                            <div class="col-sm-3">Hasta:</div>
                            <div class="col-sm-9"><input type="date" class="form-control" name="hasta" id="hasta" value="<?=$hasta?>" required></div>
                          </div>
                          <div class="form-group row">
                            <div class="col-sm-3">Porcentaje a descontar:</div>
                            <div class="col-sm-9"><input type="number" class="form-control" name="porcentaje" id="porcentaje" value="<?=$porcentaje?>" required></div>
                          </div>

                        </div>
                      </div>
                    </div>
                    <div class="card-footer">
                      <div class="col-sm-9 offset-sm-3">
                        <button class="btn btn-primary" type="submit">Modificar</button>
						            <a href='listarParametros.php' class="btn btn-light">Volver</a>
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
    <script src="assets/js/typeahead/handlebars.js"></script>
    <script src="assets/js/typeahead/typeahead.bundle.js"></script>
    <script src="assets/js/typeahead/typeahead.custom.js"></script>
    <script src="assets/js/chat-menu.js"></script>
    <script src="assets/js/tooltip-init.js"></script>
    <script src="assets/js/typeahead-search/handlebars.js"></script>
    <script src="assets/js/typeahead-search/typeahead-custom.js"></script>
    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="assets/js/script.js"></script>
    <!-- Plugin used-->
	<script src="assets/js/select2/select2.full.min.js"></script>
    <script src="assets/js/select2/select2-custom.js"></script>
  </body>
</html>