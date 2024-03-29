<?php 
session_start(); 
if(empty($_SESSION['user']))
{
	header("Location: index.php");
	die("Redirecting to index.php"); 
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
	<?php include('head_tables.php');?>
  </head>
  <body class="light-only">
    <!-- page-wrapper Start-->
    <div class="page-wrapper">
      <!-- Page Header Start-->
      <?php include('header.php');?>
     
      <!-- Page Header Ends                              -->
      <!-- Page Body Start-->
      <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        <?php include('menu.php');?>
        <!-- Page Sidebar Ends-->
        <!-- Right sidebar Start-->
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
                      <li class="breadcrumb-item">Pagos Pendientes</li>
                    </ol>
                  </div>
                </div>
                <!-- Bookmark Start-->
                <div class="col">
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
              <!-- Zero Configuration  Starts-->
              <div class="col-sm-12">
                <div class="card">
                  <div class="card-header">
                    <h5>Pagos Pendientes
					&nbsp;<a href="#"><img src="img/dolar.png" width="24" height="25" border="0" alt="Marcar Ventas Rendidas" id="pagado-masivo" title="Marcar Ventas Rendidas"></a>
					&nbsp;<a href="exportPagosPendientes.php"><img src="img/xls.png" width="24" height="25" border="0" alt="Exportar Pagos Pendientes" title="Exportar Pagos Pendientes"></a>
					</h5>
                  </div>
                  <div class="card-body">
                    <div class="dt-ext table-responsive">
                      <table class="display" id="dataTables-example666">
                        <thead>
                          <tr>
						  <th></th>
						  <th>ID</th>
						  <th>Proveedor</th>
						  <th>Almacen</th>
						  <th>Fecha/Hora</th>
						  <th>Código</th>
						  <th>Categoría</th>
						  <th>Descripción</th>
						  <th>Cantidad</th>
						  <th>Forma de Pago</th>
						  <th>Precio</th>
						  <th>Subtotal</th>
						  <th>Deuda</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php 
							include 'database.php';
							$pdo = Database::connect();
							$sql = " SELECT vd.id, a.almacen, date_format(v.fecha_hora,'%d/%m/%Y %H:%i'), p.codigo, c.categoria, p.descripcion, vd.`cantidad`, vd.`precio`, vd.`subtotal`, m.`modalidad`, vd.`pagado`, pr.nombre, pr.apellido, v.id_forma_pago, fp.forma_pago FROM `ventas_detalle` vd inner join ventas v on v.id = vd.id_venta inner join almacenes a on a.id = v.id_almacen inner join productos p on p.id = vd.id_producto inner join categorias c on c.id = p.id_categoria inner join modalidades m on m.id = vd.id_modalidad inner join proveedores pr on pr.id = p.id_proveedor inner join forma_pago fp on fp.id = v.id_forma_pago WHERE v.anulada = 0 and m.id = 2 and vd.`pagado` = 0 ";
							if ($_SESSION['user']['id_perfil'] == 2) {
								$sql .= " and a.id = ".$_SESSION['user']['id_almacen']; 
							}
							foreach ($pdo->query($sql) as $row) {
								echo '<tr>';
								echo '<td><input type="checkbox" class="no-sort customer-selector" value="'.$row[0].'" /> </td>';
								echo '<td>'. $row[0] . '</td>';
								echo '<td>'. $row[11] . ' ' . $row[12] . '</td>';
								echo '<td>'. $row[1] . '</td>';
								echo '<td>'. $row[2] . 'hs</td>';
								echo '<td>'. $row[3] . '</td>';
								echo '<td>'. $row[4] . '</td>';
								echo '<td>'. $row[5] . '</td>';
								echo '<td>'. $row[6] . '</td>';
								echo '<td>'. $row[14] . '</td>';
								echo '<td>$'. number_format($row[7],2) . '</td>';
								echo '<td>$'. number_format($row[8],2) . '</td>';
								$subtotal = $row[8];
								$modalidad = 0.4;
								$deuda = 0;
								if ($row[13] == 1) {
									$fp = 1;
								} else {
									$fp = 0.85;
								}
								$deuda = $subtotal*$modalidad*$fp;
								echo '<td>$'. number_format($deuda,2) . '</td>';
								echo '</tr>';
							}
						   Database::disconnect();
						  ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Zero Configuration  Ends-->
              <!-- Feature Unable /Disable Order Starts-->
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
    <script src="assets/js/script.js"></script>
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
		
		jQuery('.customer-selector').on('click', function () {
        jQuery('.toggle-checkboxes').prop('checked', false);
    });

	jQuery('#pagado-masivo').on('click', function (e) {
	    e.preventDefault();
	    if (jQuery('.customer-selector:checked').length < 1) {
	        alert("Debe seleccionar una operación como mínimo");
        } else {
            var arr = [];
            jQuery('.customer-selector:checked').each(function (i,o) { arr.push(jQuery(o).val()); });
            window.location.href= window.location.href.replace("listarPagosPendientes.php", "marcarVentasPagadas.php?id=" + arr.join(",") );
        }

    });

	var toggle = true;
    jQuery('.toggle-checkboxes').on('click', function (e) {
        e.preventDefault();
        jQuery('.customer-selector').prop('checked', toggle);
        toggle = !toggle;

    })
		
		</script>
		<script src="https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"></script>
    <!-- Plugin used-->
  </body>
</html>