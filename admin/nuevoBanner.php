<?php
    require("config.php");
    if(empty($_SESSION['user']))
    {
        header("Location: index.php");
        die("Redirecting to index.php"); 
    }
	
	require 'database.php';
	
	if ( !empty($_POST)) {
    
    $subidajpg = 0;
    $subidawebp = 0;

    if($_POST['seccion'] == "1"){
      $seccion = "Home";
    }
    //Subir el archivo Webp
		if (isset($_FILES['imagen-banner-webp']) && $_FILES['imagen-banner-webp']['error'] === UPLOAD_ERR_OK) {
      $carpetaDestino = '../nueva_web/images/' . $seccion . '/WEBP/';
      $nombreArchivoWebp = uniqid() . '-' . $_FILES['imagen-banner-webp']['name'];

      if (move_uploaded_file($_FILES['imagen-banner-webp']['tmp_name'], $carpetaDestino . $nombreArchivoWebp)) {
        // El archivo se movió correctamente a la ubicación deseada
        $subidawebp = 1;
      }
    }

    //Subir el archivo JPG
		if (isset($_FILES['imagen-banner-jpg']) && $_FILES['imagen-banner-jpg']['error'] === UPLOAD_ERR_OK) {
      $carpetaDestino = '../nueva_web/images/' . $seccion . '/JPG/';
      $nombreArchivoJPG = uniqid() . '-' . $_FILES['imagen-banner-jpg']['name'];

      if (move_uploaded_file($_FILES['imagen-banner-jpg']['tmp_name'], $carpetaDestino . $nombreArchivoJPG)) {
        // El archivo se movió correctamente a la ubicación deseada
        $subidajpg = 1;
      }
    }

    if($subidawebp == 1 && $subidajpg == 1){
      // insert data
      $pdo = Database::connect();
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = "INSERT INTO banners(`nombre`, `seccion`, `url-jpg`, `url-webp`) VALUES (?,?,?,?)";
      $q = $pdo->prepare($sql);
      $q->execute(array($_POST['nombre'], $_POST['seccion'], $nombreArchivoJPG, $nombreArchivoWebp));
        
      Database::disconnect();
    }
      
    header("Location: listarBanners.php");
    
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
                      <li class="breadcrumb-item">Nuevo Banner</li>
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
                    <h5>Nuevo Banner</h5>
                  </div>
				          <form class="form theme-form" role="form" method="post" enctype="multipart/form-data" action="nuevoBanner.php">
                    <div class="card-body">
                      <div class="row">
                        <div class="col">
                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Nombre</label>
                            <div class="col-sm-9">
                              <input name="nombre" type="text" maxlength="99" class="form-control" value="" required="required">
                            </div>
                          </div>

                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Subida de imagen JPG</label>
                            
                            <div class="col-sm-9">
                              <input type="file" name="imagen-banner-jpg" required="required">
                            </div>
                          </div>

                          <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Seccion</label>
                            <div class="col-sm-9">
                              <select name="seccion" id="seccion" class="js-example-basic-single col-sm-12" required="required">
                                <option value="">Seleccione...</option>
                                <option value="1">Home</option>
                              </select>
                            </div>
                          </div>

                        </div>
                      </div>
                    </div>
                    <div class="card-footer">
                      <div class="col-sm-9 offset-sm-3">
                        <button class="btn btn-primary" type="submit">Crear</button>
						            <a href="listarBanner.php" class="btn btn-light">Volver</a>
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