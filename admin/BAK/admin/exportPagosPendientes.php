<?php
session_start(); 
header('Content-Disposition: attachment; filename="pagos_pendientes.xls"');
include 'database.php';
?>
<!doctype html>
<html lang="en">
<head>
	<?php include('head_tables.php');?>
</head>
<body>

			<div class="row">
				<div class="table-responsive">
				<a href="#" id="aExportar" onclick="$('#example2').tableExport({type:'excel',escape:'false'});"></a>
				<table id="example2" name="formularios" style="visibility:hidden;">
					<thead>
		                <tr>
						  <th>ID</th>
						  <th>Proveedor</th>
						  <th>Almacen</th>
						  <th>Fecha/Hora</th>
						  <th>Código</th>
						  <th>Categoría</th>
						  <th>Descripción</th>
						  <th>Cantidad</th>
						  <th>Precio</th>
						  <th>Subtotal</th>
						  <th>Deuda</th>
		                </tr>
		              </thead>
		             <tbody>
		              <?php     
						$pdo = Database::connect();
						$sql = " SELECT vd.id, a.almacen, v.fecha_hora, p.codigo, c.categoria, p.descripcion, vd.`cantidad`, vd.`precio`, vd.`subtotal`, m.`modalidad`, vd.`pagado`, pr.nombre, pr.apellido FROM `ventas_detalle` vd inner join ventas v on v.id = vd.id_venta inner join almacenes a on a.id = v.id_almacen inner join productos p on p.id = vd.id_producto inner join categorias c on c.id = p.id_categoria inner join modalidades m on m.id = vd.id_modalidad inner join proveedores pr on pr.id = p.id_proveedor WHERE v.anulada = 0 and m.id = 2 and vd.`pagado` = 0";
						if ($_SESSION['user']['id_perfil'] == 2) {
							$sql .= " and a.id = ".$_SESSION['user']['id_almacen']; 
						}
							foreach ($pdo->query($sql) as $row) {
								echo '<tr>';
								echo '<td>'. $row[0] . '</td>';
								echo '<td>'. $row[11] . ' ' . $row[12] . '</td>';
								echo '<td>'. $row[1] . '</td>';
								echo '<td>'. $row[2] . '</td>';
								echo '<td>'. $row[3] . '</td>';
								echo '<td>'. $row[4] . '</td>';
								echo '<td>'. $row[5] . '</td>';
								echo '<td>'. $row[6] . '</td>';
								echo '<td>'. $row[7] . '</td>';
								echo '<td>'. $row[8] . '</td>';
								echo '<td>'. $row[8]*0.4 . '</td>';
								echo '</tr>';
							}
					   Database::disconnect();
					  ?>
				      </tbody>
					</table>
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
		<script src="assets/js/bootstrap/tableExport.js"></script>
		<script src="assets/js/bootstrap/jquery.base64.js"></script>
		<!-- Plugins JS Ends-->
		<!-- Plugins JS Ends-->
		<!-- Theme js-->
		<script src="assets/js/script.js"></script>
</body>
</html>
<script>document.getElementById("aExportar").click();</script>