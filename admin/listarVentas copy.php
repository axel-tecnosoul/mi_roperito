<?php 
session_start(); 
if(empty($_SESSION['user']['id_perfil'])){
	header("Location: index.php");
	die("Redirecting to index.php"); 
}
include_once("funciones.php");
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
                <div class="col-10">
                  <div class="page-header-left">
                    <h3><?php include("title.php"); ?></h3>
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="#"><i data-feather="home"></i></a></li>
                      <li class="breadcrumb-item">Ventas</li>
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
              <!-- Zero Configuration  Starts-->
              <div class="col-sm-12">
                <div class="card">
                  <div class="card-header">
                    <h5>Ventas
                      &nbsp;<a href="nuevaVenta.php"><img src="img/icon_alta.png" width="24" height="25" border="0" alt="Nueva Venta" title="Nueva Venta"></a>
                      &nbsp;<a href="exportVentas.php"><img src="img/xls.png" width="24" height="25" border="0" alt="Exportar Ventas" title="Exportar Ventas"></a>
                      &nbsp;<a href="listarVentasAnuladas.php"><img src="img/canceled.png" width="24" height="25" border="0" alt="Ventas Eliminadas" title="Ventas Eliminadas"></a>
                    </h5>
                  </div>
                  <div class="card-body">
                    <div class="dt-ext table-responsive">
                      <table class="display" id="dataTables-example666">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Fecha/Hora</th>
                            <th>Tipo Cbte.</th>
                            <th>Almacen</th>
                            <th>Forma de pago</th>
                            <th>Total</th>
                            <th>Opciones</th>
                            <th class="none">Subtotal</th>
                            <th class="none">Descuento</th>
                            <th class="none">Cliente</th>
                            <th class="none">DNI</th>
                            <th class="none">Dirección</th>
                            <th class="none">E-Mail</th>
                            <th class="none">Teléfono</th>
                          </tr>
                        </thead>
                        <tfoot>
                          <tr>
                            <th>ID</th>
                            <th>Fecha/Hora</th>
                            <th>Tipo Cbte.</th>
                            <th>Almacen</th>
                            <th>Forma de pago</th>
                            <th>Total</th>
                            <th>Opciones</th>
                            <th class="none">Subtotal</th>
                            <th class="none">Descuento</th>
                            <th class="none">Cliente</th>
                            <th class="none">DNI</th>
                            <th class="none">Dirección</th>
                            <th class="none">E-Mail</th>
                            <th class="none">Teléfono</th>
                          </tr>
                        </tfoot>
                        <tbody><?php
                          include 'database.php';
                          $pdo = Database::connect();
                          $sql = " SELECT v.id, date_format(v.fecha_hora,'%d/%m/%Y %H:%i') AS fecha_hora, a.almacen, v.nombre_cliente, v.dni, v.direccion, v.email, v.telefono, v.total, d.descripcion, v.total_con_descuento, v.id_cierre_caja,v.tipo_comprobante,fp.forma_pago,v.estado FROM ventas v inner join almacenes a on a.id = v.id_almacen left join descuentos d on d.id = v.id_descuento_aplicado INNER JOIN forma_pago fp ON v.id_forma_pago=fp.id WHERE v.anulada = 0 ";
                          if ($_SESSION['user']['id_perfil'] != 1) {
                            $sql .= " and a.id = ".$_SESSION['user']['id_almacen']; 
                          }
                          foreach ($pdo->query($sql) as $row) {
                            echo '<tr>';
                            echo '<td>'. $row["id"] . '</td>';
                            echo '<td>'. $row["fecha_hora"] . 'hs</td>';
                            $tipo_cbte=get_nombre_comprobante($row["tipo_comprobante"]);
                            $estado=$row["estado"];
                            $class="";
                            if($estado=="A"){
                              $class="badge badge-success";
                            }
                            if($estado=="R" or $estado=="E"){
                              $class="badge badge-danger";
                            }
                            echo '<td><span class="'.$class.'">'. $tipo_cbte . '</span></td>';
                            echo '<td>'. $row["almacen"] . '</td>';
                            echo '<td>'. $row["forma_pago"] . '</td>';
                            //echo '<td>$'. number_format($row["total_con_descuento"],2,",",".") . '</td>';
                            echo '<td>$'. number_format($row["total_con_descuento"],2) . '</td>';
                            echo '<td>';
                            echo '<a href="verVenta.php?id='.$row["id"].'"><img src="img/eye.png" width="24" height="15" border="0" alt="Ver Venta" title="Ver Venta"></a>';
                            echo '&nbsp;&nbsp;';
                            if(($_SESSION["user"]["id_perfil"]==1 or $row["id_cierre_caja"]==0) and $row["tipo_comprobante"]=="R"){
                              echo '<a href="#" data-toggle="modal" data-original-title="Confirmación" data-target="#eliminarModal_'.$row["id"].'"><img src="img/icon_baja.png" width="24" height="25" border="0" alt="Anular" title="Anular"></a>';
                              echo '&nbsp;&nbsp;';
                            }
                            echo '</td>';
                            echo '<td>$'. number_format($row["total"],2) . '</td>';
                            echo '<td>'. $row["descripcion"] . '</td>';
                            echo '<td>'. $row["nombre_cliente"] . '</td>';
                            echo '<td>'. $row["dni"] . '</td>';
                            echo '<td>'. $row["direccion"] . '</td>';
                            echo '<td>'. $row["email"] . '</td>';
                            echo '<td>'. $row["telefono"] . '</td>';
                            echo '</tr>';
                          }
                          Database::disconnect();?>
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
	<?php 
	$pdo = Database::connect();
	$sql = " SELECT v.`id`, date_format(v.`fecha_hora`,'%d/%m/%Y %H:%i'), a.almacen, v.`nombre_cliente`, v.`dni`, v.`direccion`, v.`email`, v.`telefono`, v.`total`, d.descripcion, v.total_con_descuento FROM `ventas` v inner join almacenes a on a.id = v.`id_almacen` left join descuentos d on d.id = v.id_descuento_aplicado WHERE v.anulada = 0 ";
	if ($_SESSION['user']['id_perfil'] != 1) {
		$sql .= " and a.id = ".$_SESSION['user']['id_almacen']; 
	}
	foreach ($pdo->query($sql) as $row) {?>
    <div class="modal fade" id="eliminarModal_<?php echo $row[0];?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Confirmación</h5>
            <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          </div>
          <div class="modal-body">¿Está seguro que desea anular la venta?</div>
          <div class="modal-footer">
            <a href="anularVenta.php?id=<?php echo $row[0];?>" class="btn btn-primary">Anular</a>
            <button data-dismiss="modal" class="btn btn-light">Volver</button>
          </div>
        </div>
      </div>
    </div><?php 
	}
	Database::disconnect();
	?>
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
      let table=$('#dataTables-example666')
      table.DataTable({
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
          }
        },
        initComplete: function(){
          this.api().columns.adjust().draw();//Columns sin parentesis
          this.api().columns().every(function(){//Columns() con parentesis
            var column=this;
            if(column.footer().innerHTML!="Total"){
              var select=$("<select class=' form-control form-control-sm'><option value=''>Todos</option></select>")
                .appendTo($(column.footer()).empty())
                .on("change",function(){
                  var val=$.fn.dataTable.util.escapeRegex(
                    $(this).val()
                  );
                  column.search(val ? '^'+val+'$':'',true,false).draw();
                });
              column.data().unique().sort().each(function(d,j){
                var val=$("<div/>").html(d).text();
                if(column.search()==='^'+val+'$'){
                  select.append("<option value='"+val+"' selected='selected'>"+val+"</option>");
                }else{
                  select.append("<option value='"+val+"'>"+val+"</option>");
                }
              })
            }else{
              getTotalVentas(table)
            }
          })
        }
			}).on( 'search.dt', function () {
        getTotalVentas(table)
      } );;
		});

    function getTotalVentas(table){
      let total=0;
      table=table.DataTable()
      table.rows( {order:'index', search:'applied'} ).nodes().each(function(d){
        
        let col_tipo_cbte=$(d).find(":nth-child(3)").html();
        //console.log(col_tipo_cbte);
        let tipo_cbte=$("<div/>").html(col_tipo_cbte).text();
        //console.log(tipo_cbte);
        var val=$(d).find(":nth-child(6)").html();
        let number=Number(val.replace(/[^0-9.-]+/g,""));
        console.log(number);
        if(tipo_cbte=="Nota de Crédito A" || tipo_cbte=="Nota de Crédito B"){
          number=number*-1;
          //console.log(number);
        }
        total+=number;
      })
      let column_a_cobrar=table.columns(5).footer()
      $(column_a_cobrar).html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(total));
    }
		
		</script>
		<script src="https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"></script>
    <!-- Plugin used-->
  </body>
</html>