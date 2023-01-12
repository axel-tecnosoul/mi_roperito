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

    $id_usuario=$_SESSION["user"]["id"];
		
		//egresar stock
		$sql = " SELECT `id`, `id_producto`, `cantidad`, `id_modalidad` FROM `stock` WHERE `id_almacen` = ".$_POST['id_almacen_origen'];
    $aMovimientoStock=[];
		foreach ($pdo->query($sql) as $row) {
			if (isset($_POST['cantidad_'.$row[0]]) and $_POST['cantidad_'.$row[0]] > 0) {
				
				$sql = "UPDATE stock set cantidad = cantidad - ? where id = ?";
				$q = $pdo->prepare($sql);
				$q->execute(array($_POST['cantidad_'.$row[0]],$row[0]));
				
				$cantidad = $_POST['cantidad_'.$row[0]]*(-1);
				
				//ingresar stock
				$sql2 = "SELECT `id` from stock where id_producto = ? and id_almacen = ? and id_modalidad = ?";
				$q2 = $pdo->prepare($sql2);
				$q2->execute(array($row[1],$_POST['id_almacen_destino'],$row[3]));
				$data = $q2->fetch(PDO::FETCH_ASSOC);
				if (!empty($data)) {
					$sql3 = "UPDATE `stock` set cantidad = cantidad + ? where id = ?";
					$q3 = $pdo->prepare($sql3);
					$q3->execute(array($_POST['cantidad_'.$row[0]],$data['id']));
				} else {
					$sql3 = "INSERT INTO `stock`(`id_producto`, `id_almacen`, `cantidad`,`id_modalidad`) VALUES (?,?,?,?)";
					$q3 = $pdo->prepare($sql3);
					$q3->execute(array($row[1],$_POST['id_almacen_destino'],$_POST['cantidad_'.$row[0]],$row[3]));
				}

        $sql4 = "INSERT INTO stock_movimientos (id_producto, id_almacen_origen, id_almacen_destino, cantidad, id_usuario, fecha_hora) VALUES (?,?,?,?,?,NOW())";
        $q4 = $pdo->prepare($sql4);
        $q4->execute(array($row[1],$_POST['id_almacen_origen'],$_POST['id_almacen_destino'],$_POST['cantidad_'.$row[0]],$id_usuario));

        $id_movimiento_stock=$pdo->lastInsertId();
        $aMovimientoStock[]=$id_movimiento_stock;
			}
		}

    Database::disconnect();

    //var_dump($aMovimientoStock);
    if(count($aMovimientoStock)>0){
      $stringMovimientoStock=implode("i",$aMovimientoStock);
      //var_dump($stringMovimientoStock);?>
      <script>
        //window.open("imprimirMovimientoStock.php?id=<?=$stringMovimientoStock?>","_blank")
      </script><?php
      header("Location: imprimirMovimientoStock.php?id=".$stringMovimientoStock);
    }else{
      header("Location: listarStock.php");
    }

    //die();
    //header("Location: listarStock.php");
    
		
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
                      <li class="breadcrumb-item">Movimiento de Stock</li>
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
                    <h5>Movimiento entre almacenes</h5>
                  </div>
				  <form class="form theme-form" role="form" method="post" action="nuevoMovimientoStock.php">
                    <div class="card-body">
                      <div class="row">
                        <div class="col">
							<div class="form-group row">
								<label class="col-sm-3 col-form-label">Almacen Origen</label>
								<div class="col-sm-9">
								<select name="id_almacen_origen" id="id_almacen_origen" class="js-example-basic-single col-sm-12" required="required" onChange="jsListarProductos(this.value);">
								<option value="">Seleccione...</option>
								<?php 
								$pdo = Database::connect();
								$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
								$sqlZon = "SELECT `id`, `almacen` FROM `almacenes` WHERE `activo` = 1 ";
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
								<label class="col-sm-3 col-form-label">Almacen Destino</label>
								<div class="col-sm-9">
								<select name="id_almacen_destino" id="id_almacen_destino" class="js-example-basic-single col-sm-12" required="required">
								<option value="">Seleccione...</option>
								<?php 
								$pdo = Database::connect();
								$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
								$sqlZon = "SELECT `id`, `almacen` FROM `almacenes` WHERE activo = 1";
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
                    <th>Traspasar</th>
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
                        <button class="btn btn-primary" type="submit">Traspasar</button>
						<a href="listarStock.php" class="btn btn-light">Volver</a>
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
          "url" : "ajaxProductosTraspaso.php?almacen="+val,//&id_vehiculo="+id_vehiculo+"
          "dataSrc": "",
        },
        "columns":[
          {"data": "cb"},//"fecha_mostrar"},
          {"data": "codigo"},//"vehiculo.marca"},
          {"data": "categoria"},//"vehiculo.modelo"},
          {"data": "descripcion"},//"vehiculo.patente"},
          //{"data": "precio"},//"vehiculo.anio"},
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
			url: "ajaxProductosTraspaso.php",
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