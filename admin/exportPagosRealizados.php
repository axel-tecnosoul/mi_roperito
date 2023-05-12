<?php
session_start(); 
header('Content-Disposition: attachment; filename="pagos_realizados.xls"');
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
						  <th>Fecha/Hora</th>
						  <th>Descripción</th>
						  <th>Pagado</th>
						  <th>Caja</th>
						  <th>Categoría</th>
						  <th>Forma de Pago</th>
						  <th>Almacen</th>
		                </tr>
		              </thead>
		             <tbody>
		              <?php     
						$pdo = Database::connect();
						$sql = " SELECT v.id as id_venta,vd.id AS id_detalle_venta, a.almacen, p.codigo, c.categoria, p.descripcion, vd.cantidad, vd.precio, vd.subtotal, m.modalidad, vd.pagado, pr.nombre, pr.apellido, vd.id_forma_pago, fp.forma_pago, vd.id_venta,vd.deuda_proveedor,date_format(vd.fecha_hora_pago,'%d/%m/%Y %H:%i') AS fecha_hora_pago,caja_egreso,forma_pago FROM ventas_detalle vd INNER JOIN ventas v ON vd.id_venta=v.id inner join productos p on p.id = vd.id_producto inner join categorias c on c.id = p.id_categoria inner join modalidades m on m.id = vd.id_modalidad inner join proveedores pr on pr.id = p.id_proveedor LEFT join almacenes a on a.id = vd.id_almacen LEFT join forma_pago fp on fp.id = vd.id_forma_pago WHERE v.anulada = 0 and vd.id_modalidad = 40 and vd.pagado = 1 AND v.id_venta_cbte_relacionado IS NULL";
						if ($_SESSION['user']['id_perfil'] == 2) {
							$sql .= " and a.id = ".$_SESSION['user']['id_almacen']; 
						}
							foreach ($pdo->query($sql) as $row) {
								echo '<tr>';
								echo '<td>VD#'. $row['id_detalle_venta'] . '</td>';
								echo '<td>'. $row['fecha_hora_pago'] . '</td>';
								echo '<td>'. $row['descripcion'] . '</td>';
								echo '<td>'. $row['deuda_proveedor'] . '</td>';
								echo '<td>'. $row['caja_egreso'] . '</td>';
								echo '<td>'. $row['forma_pago'] . '</td>';
								echo '<td>'. $row['almacen'] . '</td>';
								echo '</tr>';
							}
						$sql2 = "SELECT cd.id AS id_detalle_canje, a.almacen, p.codigo, c.categoria, p.descripcion, cd.cantidad, cd.precio, cd.subtotal, m.modalidad, cd.pagado, pr.nombre, pr.apellido, cd.id_forma_pago, fp.forma_pago, cd.id_canje,cd.deuda_proveedor,date_format(cd.fecha_hora_pago,'%d/%m/%Y %H:%i') AS fecha_hora_pago,caja_egreso,forma_pago FROM canjes_detalle cd INNER JOIN canjes cj ON cd.id_canje=cj.id inner join productos p on p.id = cd.id_producto inner join categorias c on c.id = p.id_categoria inner join modalidades m on m.id = cd.id_modalidad inner join proveedores pr on pr.id = p.id_proveedor LEFT join almacenes a on a.id = cd.id_almacen LEFT join forma_pago fp on fp.id = cd.id_forma_pago WHERE cj.anulado = 0 and cd.id_modalidad = 40 and cd.pagado = 1";
						if ($_SESSION['user']['id_perfil'] == 2) {
							$sql .= " and a.id = ".$_SESSION['user']['id_almacen']; 
						}
							foreach ($pdo->query($sql2) as $row) {
								echo '<tr>';
								echo '<td>CD#'. $row['id_detalle_canje'] . '</td>';
								echo '<td>'. $row['fecha_hora_pago'] . '</td>';
								echo '<td>'. $row['descripcion'] . '</td>';
								echo '<td>'. $row['deuda_proveedor'] . '</td>';
								echo '<td>'. $row['caja_egreso'] . '</td>';
								echo '<td>'. $row['forma_pago'] . '</td>';
								echo '<td>'. $row['almacen'] . '</td>';
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