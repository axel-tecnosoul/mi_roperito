<?php
session_start();
if(empty($_SESSION['user'])) {
  header("Location: index.php");
  die("Redirecting to index.php");
}?>
<!DOCTYPE html>
<html lang="en">
  <head><?php
    include('head_tables.php');?>
  </head>
  <body class="light-only">
    <!-- page-wrapper Start-->
    <div class="page-wrapper">
      <!-- Page Header Start--><?php
      include('header.php');?>
      <!-- Page Header Ends                              -->
      <!-- Page Body Start-->
      <div class="page-body-wrapper">
        <!-- Page Sidebar Start--><?php
        include('menu.php');?>
        <!-- Page Sidebar Ends-->
        <div class="page-body">
          <!-- Container-fluid starts-->
          <div class="container-fluid">
            <div class="page-header">
              <div class="row">
                <div class="col-10">
                  <div class="page-header-left">
                    <h3><?php include("title.php"); ?></h3>
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="#"><i data-feather="home"></i></a></li>
                      <li class="breadcrumb-item">Banners</li>
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
          <div class="container-fluid">
            <div class="row">
              <!-- Zero Configuration  Starts-->
              <div class="col-sm-12">
                <div class="card">
                  <div class="card-header">
                    <h5>Banner&nbsp;<a href="nuevoBanner.php"><img src="img/icon_alta.png" width="24" height="25" border="0" alt="Nuevo" title="Nuevo"></a></h5>
                  </div>
                  <div class="card-body">
                    <div class="dt-ext table-responsive">
                      <table class="display" id="dataTables-example666">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Seccion</th>
                            <th>Activo</th>
                            <th>Opciones</th>
                          </tr>
                        </thead>
                        <tbody><?php
                          include 'database.php';
                          $pdo = Database::connect();
                          $sql = "SELECT id, seccion, `url-jpg`, activo FROM banners";
                            
                          foreach ($pdo->query($sql) as $row) {
                            echo '<tr>';
                            echo '<td>' . $row['id'] . '</td>';
                            echo '<td>' . $row['url-jpg'] . '</td>';
                                
                            if ($row['seccion'] == 1) {
                              echo '<td>Home web - "Sabes que se usa?"</td>';
                            } elseif ($row['seccion'] == 2) {
                              echo '<td>Home Proveedores</td>';
                            }
                            if ($row['activo'] == 1) {
                              echo '<td>SI</td>';
                            } else {
                              echo '<td>NO</td>';
                            }
                            echo '<td>';
                            echo '<a href="#" data-toggle="modal" data-target="#imagenModal' . $row['id'] . '" data-imagen="' . $row['url-jpg'] . '" data-seccion="' . $row['seccion'] . '">
                            <img src="img/eye.png" id="mostrar_imagen" width="24" height="15" border="0" alt="Ver" title="Ver Imagen">
                            </a>';
                            echo '&nbsp;&nbsp;';
                            echo '<a href="modificarBanner.php?id='.$row['id'].'"><img src="img/icon_modificar.png" width="24" height="25" border="0" alt="Modificar" title="Modificar"></a>';
                            echo '&nbsp;&nbsp;';
                            echo '<a href="#" data-toggle="modal" data-target="#eliminarModal_'.$row['id'].'"><img src="img/icon_baja.png" width="24" height="25" border="0" alt="Eliminar" title="Eliminar"></a>';
                            echo '&nbsp;&nbsp;';
                            echo '</td>';
                            echo '</tr>';
                                
                            // Crea el modal para esta imagen
                            echo '<div class="modal fade" id="imagenModal'.$row['id'].'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">';
                            echo '<div class="modal-dialog" role="document">';
                            echo '<div class="modal-content">';
                            echo '<div class="modal-header">';
                            echo '<h5 class="modal-title" id="exampleModalLabel">Imagen Banner ID:'.$row['id'].'</h5>';
                            echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
                            echo '<span aria-hidden="true">&times;</span>';
                            echo '</button>';
                            echo '</div>';
                            echo '<div class="modal-body" style="display: flex;">';
                                
                            // Aquí muestra la imagen correspondiente desde la base de datos
                            echo '<img src="" alt="Imagen" id="modalImagen' . $row['id'] . '" style="margin: auto; display: block; width: 100%; height: 100%; object-fit: cover;">';
                                
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
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
        <!-- footer start--><?php
        include("footer.php"); ?>
      </div>
    </div>
    <?php 
    $pdo = Database::connect();
    $sql = " SELECT `id`, seccion, `url-jpg`, activo FROM `banners` WHERE 1 ";
    foreach ($pdo->query($sql) as $row) {?>
      <div class="modal fade" id="eliminarModal_<?php echo $row['id'];?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Confirmación</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          </div>
          <div class="modal-body">¿Está seguro que desea eliminar el Banner?</div>
          <div class="modal-footer">
          <a href="eliminarBanner.php?id=<?php echo $row['id'];?>" class="btn btn-primary">Eliminar</a>
          <a onclick="document.location.href='listarBanners.php'" class="btn btn-light">Volver</a>
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
            }
          }
        });

        function abrirModalPorDataTarget(dataTarget) {
          $(dataTarget).on('show.bs.modal', function (e) {
              console.log('Modal abierto: ' + dataTarget);
              
              var imagenURL = $(e.relatedTarget).data('imagen');
              var modalID = $(this).attr('id');
              var seccion = $(e.relatedTarget).data('seccion');

              console.log(imagenURL);
              console.log(seccion);

              // Obtén la imagen dentro del modal actual y actualiza su fuente solo si está vacía
              var $modalImagen = $('#modalImagen' + modalID.substring(11));

             
              var rutaBase = seccion === 1 ? '../nueva_web/images/Banners/Home/' : '../nueva_web/images/Banners/Proveedores/';

              var imagenCompletaURL = rutaBase + imagenURL;

              if ($modalImagen.attr('src') === "") {
                  $modalImagen.attr('src', imagenCompletaURL);
              }
          });
      }

      $('[data-toggle="modal"]').on('click', function (e) {
          e.preventDefault(); 
          var dataTarget = $(this).data('target');
          abrirModalPorDataTarget(dataTarget);
      });

      });
    </script>
  </body>
</html>