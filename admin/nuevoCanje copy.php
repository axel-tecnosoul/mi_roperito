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
		
		$sql = "INSERT INTO `canjes`(`fecha_hora`, `id_proveedor`, `id_almacen`, `total`, `id_usuario`) VALUES (now(),?,?,0,?)";
		$q = $pdo->prepare($sql);
		$q->execute(array($_POST['id_proveedor'],$_POST['id_almacen'],$_SESSION['user']['id']));
		$idCanje = $pdo->lastInsertId();
		
		$total = 0;
		$sql = " SELECT s.`id`, p.`codigo`, c.`categoria`, p.`descripcion`, p.`precio`, s.`cantidad`, s.`id_modalidad`, p.id_proveedor, p.id FROM `stock` s inner join productos p on p.id = s.id_producto inner join categorias c on c.id = p.id_categoria WHERE s.`cantidad` > 0 and p.`activo` = 1 and s.`id_almacen` = ".$_POST["id_almacen"];
		foreach ($pdo->query($sql) as $row) {
			if ($_POST['cantidad_'.$row[0]] > 0) {
				$idProducto = $row[8];
				$cantidad = $_POST['cantidad_'.$row[0]];
				$cantidadAnterior = $row[5];
				$precio = $row[4];
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
					$sql = "update `proveedores` set credito = credito + ? where id = ?";
					$q = $pdo->prepare($sql);
					$q->execute(array($credito,$idProveedor));
					$pagado = 1;
				}
				
				$sql = "INSERT INTO `canjes_detalle`(`id_canje`, `id_producto`, `cantidad`, `precio`, `subtotal`) VALUES (?,?,?,?,?)";
				$q = $pdo->prepare($sql);
				$q->execute(array($idCanje,$idProducto,$cantidad,$precio,$subtotal));
				
				$sql3 = "update `stock` set cantidad = cantidad - ? where id = ?";
				$q3 = $pdo->prepare($sql3);
				$q3->execute(array($cantidad,$row[0]));
				
				if ($cantidadAnterior == $cantidad) {
					$sql3 = "delete from `stock` where id = ?";
					$q3 = $pdo->prepare($sql3);
					$q3->execute(array($row[0]));
				}
				
			}
		}
		
		$sql = "update `canjes` set `total` = ? WHERE id = ?";
		$q = $pdo->prepare($sql);
		$q->execute(array($total,$idCanje));
		
		$sql = "update `proveedores` set `credito` = credito - ? WHERE id = ?";
		$q = $pdo->prepare($sql);
		$q->execute(array($total,$_POST['id_proveedor']));
		
		Database::disconnect();
		
		header("Location: listarCanjes.php");
	}
	
?>
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
								<select name="id_proveedor" id="id_proveedor" class="js-example-basic-single col-sm-12" required="required">
								<option value="">Seleccione...</option>
								<?php 
								$pdo = Database::connect();
								$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
								$sqlZon = "SELECT `id`, `dni`, `nombre`, `apellido`, `credito` FROM `proveedores` WHERE `activo` = 1";
								$q = $pdo->prepare($sqlZon);
								$q->execute();
								while ($fila = $q->fetch(PDO::FETCH_ASSOC)) {
									echo "<option value='".$fila['id']."'";
									echo ">".$fila['nombre']." ".$fila['apellido']." (".$fila['dni'].") - $".number_format($fila['credito'],2)."</option>";
								}
								Database::disconnect();
								?>
								</select>
								</div>
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
								<label class="col-sm-3 col-form-label">Productos</label>
								<div class="col-sm-9">
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
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
								</div>
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
  function jsListarProductos(val) {
    $("#dataTables-example666").dataTable().fnDestroy();
    $('#dataTables-example666').DataTable({
      "ajax": {
        "url" : "ajaxVentas.php?almacen="+val,//&id_vehiculo="+id_vehiculo+"
        "dataSrc": "",
      },
      "columns":[
        {"data": "cb"},//"fecha_mostrar"},
        {"data": "codigo"},//"vehiculo.marca"},
        {"data": "categoria"},//"vehiculo.modelo"},
        {"data": "descripcion"},//"vehiculo.patente"},
        {"data": "precio"},//"vehiculo.anio"},
        {"data": "cantidad"},//"detalle"},
        {"data": "input"},//"costo_estimado_mostrar"},
      ],
      stateSave: true,
      responsive: true,
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
        }}
      })
    }
	
	
	  $(document).ready(function() {
			jsListarProductos(0)
		});

		/*$(document).ready(function() {
			$('#dataTables-example666').DataTable({
				stateSave: true,
				responsive: true,
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
				}}
			});
		});
		
	function jsListarProductos(val) { 
		$.ajax({
			type: "POST",
			url: "ajaxCanjes.php",
			data: "almacen="+val,
			success: function(resp){
				$("#dataTables-example666").html(resp);
			}
		});
	}*/
		</script>
		<script src="https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"></script>
  </body>
</html>