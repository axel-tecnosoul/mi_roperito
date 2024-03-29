<?php
    require("config.php");
    if(empty($_SESSION['user']))
    {
        header("Location: index.php");
        die("Redirecting to index.php"); 
    }
	
	require 'database.php';
	
	if ( !empty($_POST)) {
		
		// insert data
		$pdo = Database::connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$sql = "INSERT INTO empleados(nombre, apellido, dni, telefono, domicilio, email, nro_legajo, activo) VALUES (?,?,?,?,?,?,?,1)";
		$q = $pdo->prepare($sql);
		$q->execute(array($_POST['nombre'], $_POST['apellido'], $_POST['dni'], $_POST['telefono'], $_POST['domicilio'], $_POST['email'], $_POST['nro_legajo']));
		
		Database::disconnect();
		
		header("Location: listarEmpleados.php");
	}
	
?>
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
                      <li class="breadcrumb-item">Nuevo Empleado</li>
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
                    <h5>Nuevo Empleado</h5>
                  </div>
				          <form class="form theme-form" role="form" method="post" action="nuevoEmpleado.php">
                    <div class="card-body">
                      <div class="row">
                        <div class="col">
						
							<div class="form-group row">
								<label class="col-sm-3 col-form-label">Nombres</label>
								<div class="col-sm-9"><input name="nombre" type="text" maxlength="99" class="form-control" value="" required="required"></div>
							</div>
              <div class="form-group row">
								<label class="col-sm-3 col-form-label">Apellidos</label>
								<div class="col-sm-9"><input name="apellido" type="text" maxlength="99" class="form-control" value="" required="required"></div>
							</div>
              <div class="form-group row">
								<label class="col-sm-3 col-form-label">DNI</label>
								<div class="col-sm-9"><input name="dni" type="text" maxlength="99" class="form-control" value="" required="required"></div>
							</div>
              <div class="form-group row">
								<label class="col-sm-3 col-form-label">Teléfono</label>
								<div class="col-sm-9"><input name="telefono" type="text" maxlength="99" class="form-control" value="" required="required"></div>
							</div>
              <div class="form-group row">
								<label class="col-sm-3 col-form-label">Domicilio</label>
								<div class="col-sm-9"><input name="domicilio" type="text" maxlength="99" class="form-control" value="" required="required"></div>
							</div>
              <div class="form-group row">
								<label class="col-sm-3 col-form-label">Email</label>
								<div class="col-sm-9"><input name="email" type="text" maxlength="99" class="form-control" value="" required="required"></div>
							</div>
              <div class="form-group row">
								<label class="col-sm-3 col-form-label">Nº de Legajo</label>
								<div class="col-sm-9"><input name="nro_legajo" type="text" maxlength="99" class="form-control" value="" required="required"></div>
							</div>

                        </div>
                      </div>
                    </div>
                    <div class="card-footer">
                      <div class="col-sm-9 offset-sm-3">
                        <button class="btn btn-primary" type="submit">Crear</button>
						<a href="listarEmpleados.php" class="btn btn-light">Volver</a>
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