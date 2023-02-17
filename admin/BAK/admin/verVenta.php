<?php
require("config.php");
if(empty($_SESSION['user'])){
    header("Location: index.php");
    die("Redirecting to index.php"); 
}
require 'database.php';
require 'funciones.php';

$id = null;
if ( !empty($_GET['id'])) {
  $id = $_REQUEST['id'];
}

if ( null==$id ) {
  header("Location: listarVentas.php");
}

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = "SELECT v.id, date_format(v.fecha_hora,'%d/%m/%Y %H:%i') AS fecha_hora, v.nombre_cliente, v.dni, v.direccion, v.email, v.telefono, a.almacen, v.total, d.descripcion, d.minimo_compra, d.minimo_cantidad_prendas, d.monto_fijo, d.porcentaje, v.total_con_descuento, fp.forma_pago,v.tipo_comprobante,v.estado,v.punto_venta,v.numero_comprobante,v.cae,date_format(v.fecha_vencimiento_cae,'%d/%m/%Y') AS fecha_vencimiento_cae FROM ventas v inner join almacenes a on a.id = v.id_almacen left join descuentos d on d.id = v.id_descuento_aplicado INNER JOIN forma_pago fp ON v.id_forma_pago = fp.id WHERE v.id = ? ";
$q = $pdo->prepare($sql);
$q->execute(array($id));
$data = $q->fetch(PDO::FETCH_ASSOC);

$descuento=$data['descripcion'];
if($data['porcentaje']>0){
  $descuento.=" (".$data['porcentaje']."%)";
}
if($data['monto_fijo']>0){
  $descuento.=" ($".number_format($data['monto_fijo'],0,",",".").")";
}
if($data['minimo_cantidad_prendas']>0){
  $descuento.=" Cantidad prendas minimo: ".$data['minimo_cantidad_prendas'];
}
if($data['minimo_compra']>0){
  $descuento.=" Compra minima: ".number_format($data['minimo_compra'],0,",",".");
}
  
Database::disconnect();?>
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
                      <li class="breadcrumb-item">Ver Venta</li>
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
                    <h5>Ver Venta</h5>
                  </div>
				          <form class="form theme-form" role="form" method="post" action="#">
                    <div class="card-body">
                      <div class="row">
                        <div class="col">
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Fecha Hora Venta</label>
                            <div class="col-sm-9"><?php echo $data['fecha_hora']; ?>hs</div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Cliente</label>
                            <div class="col-sm-9"><?php echo $data['nombre_cliente']; ?></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">DNI</label>
                            <div class="col-sm-9"><?php echo $data['dni']; ?></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Dirección</label>
                            <div class="col-sm-9"><?php echo $data['direccion']; ?></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">E-Mail</label>
                            <div class="col-sm-9"><?php echo $data['email']; ?></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Teléfono</label>
                            <div class="col-sm-9"><?php echo $data['telefono']; ?></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Almacen</label>
                            <div class="col-sm-9"><?php echo $data['almacen']; ?></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Productos</label>
                          </div>
                          <div class="form-group row">
                            <div class="col-sm-12">
                            <table class="display" id="dataTables-example666">
                              <thead>
                                <tr>
                                  <th>Proveedor</th>
                                  <th>Código</th>
                                  <th>Categoría</th>
                                  <th>Descripción</th>
                                  <th>Precio</th>
                                  <th>Cantidad</th>
                                  <th>Subtotal</th>
                                  <th>Modalidad</th>
                                  <th>Pagado</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php 
                                $pdo = Database::connect();
                                $sql = " SELECT p.codigo, c.categoria, p.descripcion, vd.precio, vd.cantidad, vd.subtotal, m.modalidad, vd.pagado, pr.apellido, pr.nombre, p.id_proveedor FROM ventas_detalle vd INNER JOIN ventas v ON v.id = vd.id_venta INNER JOIN productos p ON p.id = vd.id_producto INNER JOIN categorias c ON c.id = p.id_categoria INNER JOIN modalidades m ON m.id = vd.id_modalidad INNER JOIN proveedores pr ON p.id_proveedor=pr.id WHERE v.anulada = 0 and vd.id_venta = ".$data['id'];
                                //var_dump($sql);
                                
                                foreach ($pdo->query($sql) as $row) {
                                  echo '<tr>';
                                  echo '<td>('. $row["id_proveedor"] .') '. $row["nombre"] .' '. $row["apellido"] .' </td>';
                                  echo '<td>'. $row[0] . '</td>';
                                  echo '<td>'. $row[1] . '</td>';
                                  echo '<td>'. $row[2] . '</td>';
                                  echo '<td>$'. number_format($row[3],2) . '</td>';
                                  echo '<td>'. $row[4] . '</td>';
                                  echo '<td>$'. number_format($row[5],2) . '</td>';
                                  echo '<td>'. $row[6] . '</td>';
                                  if ($row[7] == 1) {
                                    echo '<td>Si</td>';	
                                  } else {
                                    echo '<td>No</td>';	
                                  }
                                  echo '</tr>';
                                }
                                Database::disconnect();
                                ?>
                              </tbody>
                              </table>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Subtotal</label>
                            <div class="col-sm-9">$<?php echo number_format($data['total'],2); ?></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Forma de pago</label>
                            <div class="col-sm-9"><?php echo $data['forma_pago']; ?></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Descuento</label>
                            <div class="col-sm-9"><?php echo $descuento//$data['descripcion']; ?></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Total</label>
                            <div class="col-sm-9">$<?php echo number_format($data['total_con_descuento'],2); ?></div>
                          </div><?php
                          if($data['tipo_comprobante']!="R"){
                            $estado=get_estado_comprobante($data['estado']);
                            $cbte=format_numero_comprobante($data['punto_venta'],$data['numero_comprobante']);
                            $tipo_cbte=get_nombre_comprobante($data["tipo_comprobante"]);?>
                            <div class="form-group row">
                              <label class="col-sm-3 col-form-label">Tipo de comprobante</label>
                              <div class="col-sm-9"><?=$tipo_cbte?></div>
                            </div>
                            <div class="form-group row">
                              <label class="col-sm-3 col-form-label">Estado</label>
                              <div class="col-sm-9"><?php
                              $class="";
                              if($data['estado']=="A"){
                                $class="badge badge-success";
                              }
                              if($data['estado']=="R" or $data['estado']=="E"){
                                $class="badge badge-danger";
                              }?>
                              <span class="<?=$class?>"><?=$estado?></span></div>
                            </div>
                            <div class="form-group row">
                              <label class="col-sm-3 col-form-label">Nro. de comprobante</label>
                              <div class="col-sm-9"><?=$cbte?></div>
                            </div>
                            <div class="form-group row">
                              <label class="col-sm-3 col-form-label">CAE</label>
                              <div class="col-sm-9"><?=$data['cae']?></div>
                            </div>
                            <div class="form-group row">
                              <label class="col-sm-3 col-form-label">Fecha vencimiento CAE</label>
                              <div class="col-sm-9"><?=$data['fecha_vencimiento_cae']?></div>
                            </div><?php
                          }?>
                        </div>
                      </div>
                    </div>
                    <div class="card-footer">
                      <div class="col-sm-9 offset-sm-3">
                        <a href='listarVentas.php' class="btn btn-light">Volver</a>
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
	
	<script>
		$(document).ready(function() {
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
		
		$(document).ready(function() {
			$('#dataTables-example667').DataTable({
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
		
		$(document).ready(function() {
			$('#dataTables-example668').DataTable({
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
		
		$(document).ready(function() {
			$('#dataTables-example669').DataTable({
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
		
		</script>
		<script src="https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"></script>
  </body>
</html>