<?php
    require("config.php");
    if(empty($_SESSION['user']))
    {
        header("Location: index.php");
        die("Redirecting to index.php"); 
    }
	
	require 'database.php';

  $id = null;
	if ( !empty($_GET['id'])) {
		$id = $_REQUEST['id'];
	}
	
	if ( null==$id ) {
		header("Location: listarBanners.php");
	}

  if (!empty($_POST)) {
    
    $idRegistro = $_GET['id'];

    $subidajpg = 0;

    if($_POST['seccion'] == "1"){
      $seccion = "Home";
    }elseif($_POST['seccion'] == "2"){
      $seccion = "Proveedores";
    }

    // Verificamos si se ha enviado una nueva imagen
    if (isset($_FILES['imagen-banner-jpg']) && $_FILES['imagen-banner-jpg']['error'] === UPLOAD_ERR_OK) {
      $carpetaDestino = '../nueva_web/images/Banners/' . $seccion;
        $nombreArchivoJPG = uniqid() . '-' . $_FILES['imagen-banner-jpg']['name'];

        if (move_uploaded_file($_FILES['imagen-banner-jpg']['tmp_name'], $carpetaDestino . $nombreArchivoJPG)) {
            // El archivo se movió correctamente a la ubicación deseada
            $subidajpg = 1;
        }
    } else {
        // No se envió una nueva imagen, mantenemos la imagen actual
        $subidajpg = 0;
        $nombreArchivoJPG = $_POST['imagenActual'];
    }

    // update data
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "UPDATE banners SET nombre=?, seccion=?, `url-jpg`=?, `activo`=? WHERE id=?";
    $q = $pdo->prepare($sql);
    $q->execute(array($_POST['nombre'], $_POST['seccion'], $nombreArchivoJPG, $_POST['activo'], $idRegistro));

    Database::disconnect();

    header("Location: listarBanners.php");
  } else {
      $pdo = Database::connect();
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = "SELECT `id`, `nombre`, `seccion`, `url-jpg`, `activo` FROM `banners` WHERE id = ? ";
      $q = $pdo->prepare($sql);
      $q->execute(array($id));

      $data = $q->fetch(PDO::FETCH_ASSOC);

      Database::disconnect();
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include('head_forms.php');?>
	<link rel="stylesheet" type="text/css" href="assets/css/select2.css">
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
                      <li class="breadcrumb-item">Modificar Banner</li>
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
                    <h5>Modificar Banner</h5>
                  </div>
				          <form class="form theme-form" role="form" method="post" enctype="multipart/form-data" action="modificarBanner.php?id=<?php echo $id?>">
                    <div class="card-body">
                      <div class="row">
                        <div class="col">
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Nombre</label>
                            <div class="col-sm-9">
                              <input name="nombre" type="text" maxlength="99" class="form-control" value="<?php echo $data['nombre']; ?>" required="required">
                            </div>
                          </div>

                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Subida de imagen JPG</label>
                            <div class="col-sm-9">
                                <input type="file" name="imagen-banner-jpg">
                                <input type="checkbox" name="mantenerImagenActual" value="1"> Mantener imagen actual
                                <input type="hidden" name="imagenActual" value="<?php echo $data['url-jpg']; ?>">
                            </div>
                          </div>

                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Seccion</label>
                            <div class="col-sm-9">
                              <select name="seccion" id="seccion" class="js-example-basic-single col-sm-12" required="required">
                                <option value="">Seleccione...</option>
                                <option value="1" <?php if ($data['seccion']==1) echo " selected ";?>>"Sabes que se usa?" - Home web</option>
                                <option value="2" <?php if ($data['seccion']==2) echo " selected ";?>>Home Proveedores</option>
                              </select>
                            </div>
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

                        </div>
                      </div>
                    </div>
                    <div class="card-footer">
                      <div class="col-sm-9 offset-sm-3">
                        <button class="btn btn-primary" type="submit">Modificar</button>
						            <a href="listarBanners.php" class="btn btn-light">Volver</a>
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
  </body>
</html>