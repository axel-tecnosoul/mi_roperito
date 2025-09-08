<?php
require("config.php");
if(empty($_SESSION['user'])){
  header("Location: index.php");
  die("Redirecting to index.php"); 
}
require 'database.php';

$diasSemana = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
$horarios = [];

if (!empty($_POST)) {

  // Validaciones de horarios
  $errores = [];
  $freq = isset($_POST['frecuencia_minutos']) ? (int)$_POST['frecuencia_minutos'] : 0;
  $bloq = isset($_POST['bloqueo_minutos']) ? (int)$_POST['bloqueo_minutos'] : 0;
  if ($freq <= 0 || $freq % 5 !== 0) {
    $errores[] = 'Frecuencia inválida';
  }
  if ($bloq < $freq) {
    $errores[] = 'Bloqueo inválido';
  }
  $diasUsados = [];
  if (!empty($_POST['horarios'])) {
    foreach ($_POST['horarios'] as $grupo) {
      $dias = $grupo['dias'] ?? [];
      $inicios = $grupo['inicio'] ?? [];
      $fines = $grupo['fin'] ?? [];
      foreach ($dias as $d) {
        if (isset($diasUsados[$d])) {
          $errores[] = 'El día ' . $diasSemana[$d] . ' está repetido';
        }
        $diasUsados[$d] = true;
      }
      foreach ($inicios as $k => $ini) {
        $fin = $fines[$k] ?? null;
        if ($ini && $fin && $ini >= $fin) {
          $errores[] = 'Hora inicio debe ser menor a hora fin';
        }
      }
    }
  }
  if ($errores) {
    echo implode('<br>', $errores);
    exit;
  }

  // insert data
  $pdo = Database::connect();
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->beginTransaction();

  $sql = "INSERT INTO almacenes(almacen, iniciales_codigo_productos, direccion, punto_venta, id_tipo, activo) VALUES (?,?,?,?,?,1)";
  $q = $pdo->prepare($sql);
  $q->execute(array($_POST['almacen'], $_POST['iniciales_codigo_productos'], $_POST['direccion'], $_POST['punto_venta'], $_POST['id_tipo']));
  $idAlmacen = $pdo->lastInsertId();

  if (!empty($_POST['horarios'])) {
    $sqlH = "INSERT INTO almacenes_horarios(id_almacen,dia_semana,hora_inicio,hora_fin,frecuencia_minutos,bloqueo_minutos) VALUES (?,?,?,?,?,?)";
    $qH = $pdo->prepare($sqlH);
    foreach ($_POST['horarios'] as $grupo) {
      $dias = $grupo['dias'] ?? [];
      $inicios = $grupo['inicio'] ?? [];
      $fines = $grupo['fin'] ?? [];
      foreach ($dias as $d) {
        foreach ($inicios as $k => $ini) {
          $fin = $fines[$k] ?? null;
          if ($ini && $fin) {
            $qH->execute(array($idAlmacen, $d, $ini, $fin, $freq, $bloq));
          }
        }
      }
    }
  }

  $pdo->commit();
  Database::disconnect();

  header("Location: listarAlmacenes.php");
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include('head_forms.php');?>
          <link rel="stylesheet" type="text/css" href="assets/css/select2.css">
          <style>
            .schedule-title {
              margin-top: 1.5rem;
              margin-bottom: 1rem;
            }
            .block-label {
              font-weight: bold;
            }
          </style>
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
                      <li class="breadcrumb-item">Nuevo Almacen</li>
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
                    <h5>Nuevo Almacen</h5>
                  </div>
                                          <form class="form theme-form" role="form" method="post" action="nuevoAlmacen.php">
                    <div class="card-body">
                      <div class="row">
                        <div class="col">
						
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Almacen</label>
                            <div class="col-sm-9"><input name="almacen" type="text" maxlength="99" class="form-control" value="" required="required"></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Iniciales para los codigos de productos</label>
                            <div class="col-sm-9"><input name="iniciales_codigo_productos" type="text" maxlength="2" class="form-control" value="" required="required" oninput="convertirAMayusculas(this)"></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Tipo de almacen</label>
                            <div class="col-sm-9">
                              <select name="id_tipo" id="id_tipo" class="js-example-basic-single col-sm-12" required>
                                <option value="">Seleccione...</option>
                                <option value="1">Venta</option>
                                <option value="2">Deposito</option>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Direccion</label>
                            <div class="col-sm-9"><input name="direccion" type="text" maxlength="99" class="form-control" value=""></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Punto de venta Facturacion Electrónica</label>
                            <div class="col-sm-9"><input name="punto_venta" type="number" maxlength="99" class="form-control" value=""></div>
                          </div>
                          <h5 class="schedule-title">Configuración de horarios</h5>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">
                              Frecuencia (min)
                              <i class="fa fa-info-circle ml-1" data-toggle="tooltip" data-placement="top" title="Intervalo entre turnos (múltiplos de 5 minutos)"></i>
                            </label>
                            <div class="col-sm-3"><input type="number" step="5" name="frecuencia_minutos" class="form-control"></div>
                            <label class="col-sm-3 col-form-label">
                              Bloqueo (min)
                              <i class="fa fa-info-circle ml-1" data-toggle="tooltip" data-placement="top" title="Minutos adicionales bloqueados tras un turno reservado"></i>
                            </label>
                            <div class="col-sm-3"><input type="number" name="bloqueo_minutos" class="form-control"></div>
                          </div>
                          <div id="groups"></div>
                          <button type="button" class="btn btn-primary btn-sm" id="add-group">Agregar grupo de días</button>

                        </div>
                      </div>
                    </div>
                    <div class="card-footer">
                      <div class="col-sm-9 offset-sm-3">
                        <button class="btn btn-primary" type="submit">Crear</button>
						            <a href="listarAlmacenes.php" class="btn btn-light">Volver</a>
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
      <script src="assets/js/horarios_grupo.js"></script>
      <script>
        function convertirAMayusculas(input) {
          input.value = input.value.toUpperCase();
        }
      </script>
  </body>
</html>