<?php 
session_start(); 
if(empty($_SESSION['user']))
{
	header("Location: index.php");
	die("Redirecting to index.php"); 
}
include 'database.php';
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
                      <li class="breadcrumb-item">Balance de Almacenes</li>
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
                    <h5>Balance de Almacenes</h5>
                  </div>
                  <div class="card-body"><?php
                    include_once 'database.php';
                    $pdo = Database::connect();
                    $sql = " SELECT a.almacen, date_format(v.fecha_hora,'%d/%m/%Y') AS fecha, fp.forma_pago,SUM(v.total_con_descuento) AS total FROM ventas v inner join almacenes a on a.id = v.id_almacen inner join forma_pago fp on fp.id = v.id_forma_pago WHERE anulada=0 GROUP BY a.id,fp.id,date(v.fecha_hora)";
                    //echo $sql;
                    $pdo->fetch(PDO::FETCH_ASSOC);
                    $aVentas=[];
                    foreach ($pdo->query($sql) as $row) {
                      var_dump($row);
                      echo $row["fecha"]." ".$row["almacen"]." ".$row["forma_pago"]." ".$row["total"]."<hr>";
                      $aVentas[]=[

                      ];
                    }

                    /*$sql = " SELECT a.almacen, date_format(v.fecha_hora,'%d/%m/%Y') AS fecha, fp.forma_pago,SUM(v.total_con_descuento) FROM ventas v inner join almacenes a on a.id = v.id_almacen inner join forma_pago fp on fp.id = v.id_forma_pago WHERE anulada=0 GROUP BY a.id,fp.id,date(v.fecha_hora)";
                    //echo $sql;
                    foreach ($pdo->query($sql) as $row) {
                      var_dump($row);
                    }*/

                    $_GET["desde"]="2023-04-01";
                    $_GET["hasta"]="2023-04-22";
                    $_GET["forma_pago"]="";
                    $_GET["id_almacen"]="";
                    $_GET["motivo"]="";
                    //include_once("listarCajaChicaGetData.php");
                    //include_once("listarGrandeChicaGetData.php");?>
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
      getCaja()
      function getCaja(){
        let desde=$("#desde").val();
        let hasta=$("#hasta").val();
        let forma_pago=$("#forma_pago").val();
        let motivo=$("#id_motivo").val();
        let id_almacen=$("#id_almacen").val();

        desde="2023-04-01"
        hasta="2023-04-22"
        forma_pago=""
        motivo=""
        id_almacen=""
        
        /*$.get("listarCajaChicaGetData.php", { desde: desde,hasta: hasta,forma_pago: forma_pago,motivo: motivo,id_almacen: id_almacen}, function(data, status){
          console.log("Data: " + data + "\nStatus: " + status);
        });*/
      }
		
		</script>
		<script src="https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"></script>
    <!-- Plugin used-->
  </body>
</html>