<?php
require("config.php");
if(empty($_SESSION['user'])){
  header("Location: index.php");
  die("Redirecting to index.php"); 
}
require 'database.php';

$id = null;
if ( !empty($_GET['id'])) {
  $id = $_REQUEST['id'];
}

if ( null==$id ) {
  header("Location: listarCajaChica.php");
}

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//$sql = "SELECT ech.monto,fp.forma_pago,u.usuario,msc.motivo,detalle,a.almacen FROM egresos_caja_chica ech INNER JOIN almacenes a ON ech.id_almacen=a.id INNER JOIN forma_pago fp ON ech.id_forma_pago=fp.id INNER JOIN usuarios u ON ech.id_usuario=u.id INNER JOIN motivos_salidas_caja msc ON ech.id_motivo=msc.id WHERE ech.id = ? ";
$sql = "SELECT mc.monto,fp.forma_pago,u.usuario,msc.motivo,detalle,a.almacen,fecha_hora,tipo_movimiento FROM movimientos_caja mc INNER JOIN almacenes a ON mc.id_almacen=a.id INNER JOIN forma_pago fp ON mc.id_forma_pago=fp.id INNER JOIN usuarios u ON mc.id_usuario=u.id INNER JOIN motivos_salidas_caja msc ON mc.id_motivo=msc.id WHERE mc.id = ? ";
$q = $pdo->prepare($sql);
$q->execute(array($id));
$data = $q->fetch(PDO::FETCH_ASSOC);

Database::disconnect();?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include('head_forms.php');?>
	<link rel="stylesheet" type="text/css" href="assets/css/select2.css">
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
                      <li class="breadcrumb-item">Ver movimiento de caja chica</li>
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
                    <h5>Ver movimiento de caja chica</h5>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Fecha</label>
                          <div class="col-sm-9"><?=date("d-m-Y",strtotime($data["fecha_hora"]))?></div>
                        </div>
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Hora</label>
                          <div class="col-sm-9"><?=date("H:i",strtotime($data["fecha_hora"]))?></div>
                        </div>
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Almacen</label>
                          <div class="col-sm-9"><?=$data["almacen"]?></div>
                        </div>
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Forma de pago</label>
                          <div class="col-sm-9"><?=$data["forma_pago"]?></div>
                        </div>
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Tipo de movimiento</label>
                          <div class="col-sm-9"><?=$data["tipo_movimiento"]?></div>
                        </div>
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Monto</label>
                          <div class="col-sm-9">$<?=number_format($data["monto"],2,",",".")?></div>
                        </div>
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Motivo</label>
                          <div class="col-sm-9"><?=$data["motivo"]?></div>
                        </div>
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Detalle</label>
                          <div class="col-sm-9"><?=$data["detalle"]?></div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="card-footer">
                    <div class="col-sm-9 offset-sm-3">
                      <a href="listarCajaChica.php" class="btn btn-light">Volver</a>
                    </div>
                  </div>
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