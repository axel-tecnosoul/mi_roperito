<?php
require("config.php");
if(empty($_SESSION['user']['id_perfil'])){
  header("Location: index.php");
  die("Redirecting to index.php"); 
}
$diasSemana = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
$grupos = [];
$freqGlobal = null;
$bloqGlobal = null;
require 'database.php';

$id = null;
if ( !empty($_GET['id'])) {
  $id = $_REQUEST['id'];
}

if ( null==$id ) {
  header("Location: listarAlmacenes.php");
}

if ( !empty($_POST)) {

  // Validaciones de horarios
  $errores = [];
  $freqGlobal = isset($_POST['frecuencia_minutos']) ? (int)$_POST['frecuencia_minutos'] : 0;
  $bloqGlobal = isset($_POST['bloqueo_minutos']) ? (int)$_POST['bloqueo_minutos'] : 0;
  if ($freqGlobal <= 0 || $freqGlobal % 5 !== 0) {
    $errores[] = 'Frecuencia inválida';
  }
  if ($bloqGlobal < $freqGlobal) {
    $errores[] = 'Bloqueo inválido';
  }
  $diasUsados = [];
  if (!empty($_POST['horarios'])) {
    foreach ($_POST['horarios'] as $grupo) {
      $dias = $grupo['dias'] ?? [];
      $inicios = $grupo['inicio'] ?? [];
      $fines   = $grupo['fin'] ?? [];
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

  // update data
  $pdo = Database::connect();
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->beginTransaction();

  $sql = "UPDATE almacenes set almacen = ?, iniciales_codigo_productos = ?, direccion = ?, id_tipo = ?, punto_venta = ?, activo = ? where id = ?";
  $q = $pdo->prepare($sql);
  $q->execute(array($_POST['almacen'],$_POST['iniciales_codigo_productos'],$_POST['direccion'],$_POST['id_tipo'],$_POST['punto_venta'],$_POST['activo'],$_GET['id']));

  $pdo->prepare("DELETE FROM almacenes_horarios WHERE id_almacen = ?")->execute(array($_GET['id']));

  if (!empty($_POST['horarios'])) {
    $sqlH = "INSERT INTO almacenes_horarios(id_almacen,dia_semana,hora_inicio,hora_fin,frecuencia_minutos,bloqueo_minutos) VALUES (?,?,?,?,?,?)";
    $qH = $pdo->prepare($sqlH);
    foreach ($_POST['horarios'] as $grupo) {
      $dias = $grupo['dias'] ?? [];
      $inicios = $grupo['inicio'] ?? [];
      $fines   = $grupo['fin'] ?? [];
      foreach ($dias as $d) {
        foreach ($inicios as $k => $ini) {
          $fin = $fines[$k] ?? null;
          if ($ini && $fin) {
            $qH->execute(array($_GET['id'],$d,$ini,$fin,$freqGlobal,$bloqGlobal));
          }
        }
      }
    }
  }

  $pdo->commit();
  Database::disconnect();

  header("Location: listarAlmacenes.php");

} else {

  $pdo = Database::connect();
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $sql = "SELECT id, almacen, iniciales_codigo_productos, direccion, id_tipo, punto_venta, activo FROM almacenes WHERE id = ? ";
  $q = $pdo->prepare($sql);
  $q->execute(array($id));
  $data = $q->fetch(PDO::FETCH_ASSOC);

  $sqlH = "SELECT dia_semana,hora_inicio,hora_fin,frecuencia_minutos,bloqueo_minutos FROM almacenes_horarios WHERE id_almacen = ? ORDER BY dia_semana,hora_inicio";
  $qH = $pdo->prepare($sqlH);
  $qH->execute(array($id));
  $diaHorarios = [];
  while($row = $qH->fetch(PDO::FETCH_ASSOC)){
    if ($freqGlobal === null) $freqGlobal = $row['frecuencia_minutos'];
    if ($bloqGlobal === null) $bloqGlobal = $row['bloqueo_minutos'];
    $d = $row['dia_semana'];
    $diaHorarios[$d][] = ['inicio'=>$row['hora_inicio'], 'fin'=>$row['hora_fin']];
  }
  $usados = [];
  foreach ($diaHorarios as $d => $blocks) {
    if (isset($usados[$d])) continue;
    $grupo = [
      'dias' => [$d],
      'inicio' => array_column($blocks, 'inicio'),
      'fin' => array_column($blocks, 'fin')
    ];
    $usados[$d] = true;
    foreach ($diaHorarios as $d2 => $blocks2) {
      if ($d2 === $d || isset($usados[$d2])) continue;
      if ($blocks2 == $blocks) {
        $grupo['dias'][] = $d2;
        $usados[$d2] = true;
      }
    }
    $grupos[] = $grupo;
  }

  Database::disconnect();
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
                      <li class="breadcrumb-item">Modificar Almacen</li>
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
                    <h5>Modificar Almacen</h5>
                  </div>
				          <form class="form theme-form" role="form" method="post" action="modificarAlmacen.php?id=<?=$id?>">
                    <div class="card-body">
                      <div class="row">
                        <div class="col">
						
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Almacen</label>
                            <div class="col-sm-9"><input name="almacen" type="text" maxlength="99" class="form-control" value="<?=$data['almacen']; ?>" required="required"></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Iniciales para los codigos de productos</label>
                            <div class="col-sm-9"><input name="iniciales_codigo_productos" type="text" maxlength="2" class="form-control" value="<?=$data['iniciales_codigo_productos']; ?>" required="required" oninput="convertirAMayusculas(this)"></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Tipo de almacen</label>
                            <div class="col-sm-9">
                              <select name="id_tipo" id="id_tipo" class="js-example-basic-single col-sm-12" required>
                                <option value="">Seleccione...</option>
                                <option value="1" <?php if ($data['id_tipo']==1) echo " selected ";?>>Venta</option>
                                <option value="2" <?php if ($data['id_tipo']==2) echo " selected ";?>>Deposito</option>
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Direccion</label>
                            <div class="col-sm-9"><input name="direccion" type="text" maxlength="99" class="form-control" value="<?=$data["direccion"]?>"></div>
                          </div>
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Punto de venta Facturacion Electrónica</label>
                            <div class="col-sm-9"><input name="punto_venta" type="number" maxlength="99" class="form-control" value="<?=$data['punto_venta']; ?>"></div>
                          </div>
                            <div class="form-group row">
                              <label class="col-sm-3 col-form-label">Activo</label>
                              <div class="col-sm-9">
                                <select name="activo" id="activo" class="js-example-basic-single col-sm-12" required="required">
                                  <option value="">Seleccione...</option>
                                  <option value="1" <?php if ($data['activo']==1) echo " selected ";?>>Si</option>
                                  <option value="0" <?php if ($data['activo']==0) echo " selected ";?>>No</option>
                                </select>
                              </div>
                            </div>

  <h5 class="schedule-title">Configuración de horarios</h5>
  <div class="form-group row">
    <label class="col-sm-3 col-form-label">
      Frecuencia (min)
      <i class="fa fa-info-circle ml-1" data-toggle="tooltip" data-placement="top" title="Intervalo entre turnos (múltiplos de 5 minutos)"></i>
    </label>
    <div class="col-sm-3"><input type="number" step="5" name="frecuencia_minutos" class="form-control" value="<?= $freqGlobal ?>"></div>
    <label class="col-sm-3 col-form-label">
      Bloqueo (min)
      <i class="fa fa-info-circle ml-1" data-toggle="tooltip" data-placement="top" title="Minutos adicionales bloqueados tras un turno reservado"></i>
    </label>
    <div class="col-sm-3"><input type="number" name="bloqueo_minutos" class="form-control" value="<?= $bloqGlobal ?>"></div>
  </div>
  <div id="groups">
  <?php foreach($grupos as $i => $g): ?>
    <div class="group-row row mb-3">
      <div class="group-block col-sm-11 border p-3" data-index="<?= $i ?>">
        <div class="mb-2">
          <label class="col-form-label mb-0">Días</label>
        </div>
        <div class="form-group">
          <select multiple class="dias-select" name="horarios[<?= $i ?>][dias][]">
            <?php for($d=0;$d<7;$d++): ?>
              <option value="<?= $d ?>" <?php if(in_array($d,$g['dias'])) echo 'selected'; ?>><?= $diasSemana[$d] ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="blocks">
          <?php foreach($g['inicio'] as $k=>$ini): $fin=$g['fin'][$k] ?? ''; ?>
          <div class="block form-group row">
            <span class="block-label col-12">Bloque <?= $k + 1 ?></span>
            <div class="col-sm-5">
              <label>Inicio</label>
              <input type="time" step="300" name="horarios[<?= $i ?>][inicio][]" class="form-control" value="<?= $ini ?>">
            </div>
            <div class="col-sm-5">
              <label>Fin</label>
              <input type="time" step="300" name="horarios[<?= $i ?>][fin][]" class="form-control" value="<?= $fin ?>">
            </div>
            <div class="col-sm-2 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm remove-block">X</button></div>
          </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-secondary btn-sm add-block">Agregar bloque</button>
      </div>
      <div class="col-sm-1 d-flex align-items-center justify-content-center">
        <button type="button" class="btn btn-danger btn-sm remove-group">Eliminar grupo</button>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="btn btn-primary btn-sm" id="add-group">Agregar grupo de días</button>

                          </div>
                        </div>
                      </div>
                      <div class="card-footer">
                      <div class="col-sm-9 offset-sm-3">
                        <button class="btn btn-primary" type="submit">Modificar</button>
						            <a href='listarAlmacenes.php' class="btn btn-light">Volver</a>
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