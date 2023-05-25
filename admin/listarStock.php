<?php 
session_start(); 
if(empty($_SESSION['user'])){
	header("Location: index.php");
	die("Redirecting to index.php"); 
}?>
<!DOCTYPE html>
<html lang="en">
  <head>
	  <?php include('head_tables.php');?>
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap-select-1.13.14/dist/css/bootstrap-select.min.css">
  </head>
  <style>
    .multiselect{
      color:#212529 !important;
      background-color:#fff;
      border-color:#ccc;
    }
  </style>
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
                      <li class="breadcrumb-item">Stock</li>
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
                    <h5>Stock
                      &nbsp;<a href="nuevaCompra.php"><img src="img/icon_alta.png" width="24" height="25" border="0" alt="Ingresar Stock" title="Ingresar Stock"></a>
                      &nbsp;<a href="nuevoMovimientoStock.php"><img src="img/import.png" width="24" height="25" border="0" alt="Movimientos Entre Almacenes" title="Movimientos Entre Almacenes"></a>
                      &nbsp;<a href="exportStock.php"><img src="img/xls.png" width="24" height="25" border="0" alt="Exportar Stock" title="Exportar Stock"></a>
                      &nbsp;<a href="actualizarPrecioMasivo.php"><img src="img/update_price.png" height="25" border="0" alt="Exportar Stock" title="Actualizar precio masivo"></a>
                    </h5>
                  </div>
                  <div class="card-body">
                    <div class="row mb-2">
                      <table class="table">
                        <tr>
                          <td class="text-right border-0 p-1">Proveedor:</td>
                          <td class="border-0 p-1">
                            <select id="proveedor" class="form-control form-control-sm filtraTabla selectpicker" data-style="multiselect" data-selected-text-format="count > 1" data-actions-box="true" multiple><?php
                              include 'database.php';
                              $pdo = Database::connect();
                              $sql = " SELECT id, CONCAT(nombre,' ',apellido) AS proveedor FROM proveedores";
                              foreach ($pdo->query($sql) as $row) {?>
                                <option value="<?=$row["id"]?>"><?=$row["proveedor"]?></option><?php
                              }
                              Database::disconnect();?>
                            </select>
                          </td>
                          <td class="text-right border-0 p-1">Modalidad:</td>
                          <td class="border-0 p-1">
                            <select id="modalidad" class="form-control form-control-sm filtraTabla selectpicker" data-style="multiselect" data-selected-text-format="count > 1" multiple><?php
                              $pdo = Database::connect();
                              $sql = " SELECT id, modalidad FROM modalidades";
                              foreach ($pdo->query($sql) as $row) {?>
                                <option value="<?=$row["id"]?>"><?=$row["modalidad"]?></option><?php
                              }
                              Database::disconnect();?>
                            </select>
                          </td>
                          <td class="text-right border-0 p-1">Categoria:</td>
                          <td class="border-0 p-1">
                            <select id="categoria" class="form-control form-control-sm filtraTabla selectpicker" data-style="multiselect" data-selected-text-format="count > 1" multiple><?php
                              $pdo = Database::connect();
                              $sql = " SELECT id, categoria FROM categorias";
                              foreach ($pdo->query($sql) as $row) {?>
                                <option value="<?=$row["id"]?>"><?=$row["categoria"]?></option><?php
                              }
                              Database::disconnect();?>
                            </select>
                          </td>
                          <td class="text-right border-0 p-1"><?php
                            if ($_SESSION['user']['id_perfil'] == 1) {
                              echo "Almacen: ";
                            }?>
                            <!-- Tipo comprobante: -->
                          </td>
                          <td class="border-0 p-1"><?php
                            if ($_SESSION['user']['id_perfil'] == 1) {?>
                              <select id="id_almacen" class="form-control form-control-sm filtraTabla selectpicker" data-style="multiselect">
                                <option value="0">- Todos -</option><?php
                                $pdo = Database::connect();
                                $sql = " SELECT id, almacen FROM almacenes";
                                foreach ($pdo->query($sql) as $row) {?>
                                  <option value="<?=$row["id"]?>"><?=$row["almacen"]?></option><?php
                                }
                                Database::disconnect();?>
                              </select><?php
                            }else{?>
                              <input type="hidden" id="id_almacen" value="<?=$_SESSION['user']['id_almacen']?>"><?php
                            }?>
                          </td>
                        </tr>
                      </table>
                    </div>
                    <div class="dt-ext table-responsive">
                      <table class="display" id="dataTables-example666">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Proveedor</th>
                            <th>Almacen</th>
                            <th>Modalidad</th>
                            <th>Activo</th>
                            <th>Opciones</th>
                          </tr>
                        </thead>
                        <tfoot>
                          <tr>
                            <th style="text-align: right;">Total</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                          </tr>
                        </tfoot>
                        <tbody><?php
							/*include 'database.php';
							$pdo = Database::connect();
							$sql = " SELECT s.id, p.codigo, c.categoria, p.descripcion, pr.nombre, pr.apellido, a.almacen, s.cantidad, m.modalidad, p.precio,p.activo FROM stock s inner join productos p on p.id = s.id_producto inner join almacenes a on a.id = s.id_almacen left join modalidades m on m.id = s.id_modalidad left join categorias c on c.id = p.id_categoria left join proveedores pr on pr.id = p.id_proveedor WHERE s.cantidad > 0 ";
							if ($_SESSION['user']['id_perfil'] == 2) {
								$sql .= " and a.id = ".$_SESSION['user']['id_almacen'];
							}
              //echo $sql;
							foreach ($pdo->query($sql) as $row) {
								echo '<tr>';
								echo '<td>'. $row["id"] . '</td>';
								echo '<td>'. $row["codigo"] . '</td>';
								echo '<td>'. $row["categoria"] . '</td>';
								echo '<td>'. $row["descripcion"] . '</td>';
                echo '<td>$'. number_format($row["precio"],2). '</td>';
								echo '<td>'. $row["nombre"] .' '.$row["apellido"]. '</td>';
								echo '<td>'. $row["almacen"] . '</td>';
                if ($row["activo"] == 1) {
                  echo '<td>Si</td>';
                } else {
                  echo '<td>No</td>';
                }
								echo '<td>'. $row["modalidad"] . '</td>';
								echo '<td>'. $row["cantidad"] . '</td>';
								echo '<td>';
								echo '<a href="modificarStock.php?id='.$row["id"].'"><img src="img/icon_modificar.png" width="24" height="25" border="0" alt="Ajustar Cantidad" title="Ajustar Cantidad"></a>';
								echo '</td>';
								echo '</tr>';
						   }
						   Database::disconnect();*/
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

    <script src="vendor/bootstrap-select-1.13.14/dist/js/bootstrap-select.js"></script>
    <script src="vendor/bootstrap-select-1.13.14/js/i18n/defaults-es_ES.js"></script>
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
      getStock();
      $(".filtraTabla").on("change",getStock);
		});

    function getStock(){

      /*let table=$('#dataTables-example666')
      table.DataTable({
        'ajax': 'ajaxListarStock.php',
				stateSave: true,
				responsive: true,
        serverSide: true,
        processing: true,
        scrollY: false,
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
        drawCallback: function(settings, json){
          let total_stock=settings.json.queryInfo.total_stock

          var api = this.api();
          // Update footer
          $(api.column(5).footer()).html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(total_stock));

          $('[title]').tooltip();
        }
			})*/

      let proveedor=$("#proveedor").val();
      let modalidad=$("#modalidad").val();
      let categoria=$("#categoria").val();
      let id_almacen=$("#id_almacen").val();

      let id_perfil="<?=$_SESSION["user"]["id_perfil"]?>";

      let table=$('#dataTables-example666')
      table.DataTable().destroy();
      table.DataTable({
        //dom: 'rtip',
        serverSide: true,
        processing: true,
        ajax:{url:'ajaxListarStock.php?proveedor='+proveedor+'&modalidad='+modalidad+'&categoria='+categoria+'&id_almacen='+id_almacen},
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
        /*"columns":[
          {"data": "id_venta"},
          {render: function(data, type, row, meta) {
            return row.fecha_hora+"hs";
          }},
          {render: function(data, type, row, meta) {
            let estado=row.estado;
            let clase="";
            if(estado=="A"){
              clase="badge badge-success";
            }
            if(estado=="R" || estado=="E"){
              clase="badge badge-danger";
            }
            return '<span class="'+clase+'">'+row.tipo_comprobante+'</span>';
          }},
          {"data": "almacen"},
          {"data": "forma_pago"},
          {
            render: function(data, type, row, meta) {
              return new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(row.total_con_descuento);
            },
            className: 'dt-body-right text-right',
          },
          {render: function(data, type, row, meta) {
            let btnVer='<a href="verVenta.php?id='+row.id_venta+'"><img src="img/eye.png" width="24" height="15" border="0" alt="Ver Venta" title="Ver Venta"></a>&nbsp;&nbsp;'
            let btnAnular="";
            if((id_perfil=="1" || row.id_cierre_caja==0) && row.tipo_comprobante=="Recibo"){
              //btnAnular='<a href="#" data-toggle="modal" data-original-title="Confirmación" data-target="#eliminarModal_'+row["id"]+'"><img src="img/icon_baja.png" width="24" height="25" border="0" alt="Anular" title="Anular"></a>&nbsp;&nbsp;'
              btnAnular='<a href="#" title="Eliminar" onclick="openModalEliminarVenta('+row.id_venta+')"><img src="img/icon_baja.png" width="24" height="25" border="0" alt="Eliminar"></a>&nbsp;&nbsp;'
            }
            return btnVer+btnAnular;
          }},
          { render: function(data, type, row, meta) {
              return new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(row.total);
            },
            className: 'dt-body-right text-right',
          },
          {"data": "descuento"},
          {"data": "nombre_cliente"},
          {"data": "dni"},
          {"data": "direccion"},
          {"data": "email"},
          {"data": "telefono"},
        ],*/
        /*initComplete: function(settings, json){
          let total_facturas_recibos=json.queryInfo.total_facturas_recibos

          var api = this.api();
          // Update footer
          $(api.column(5).footer()).html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(total_facturas_recibos));

          $('[title]').tooltip();
        }*/
        drawCallback: function(settings, json){
          let total_stock=settings.json.queryInfo.total_stock

          var api = this.api();
          // Update footer
          $(api.column(5).footer()).html(new Intl.NumberFormat('es-AR', {currency: 'ARS', style: 'currency'}).format(total_stock));

          $('[title]').tooltip();
        }
			})
    }

		</script>
		<script src="https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"></script>
    <!-- Plugin used-->
  </body>
</html>