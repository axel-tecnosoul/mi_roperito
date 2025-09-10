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
	
	$pdo = Database::connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
	$sql_select = "SELECT seccion,`url-jpg` FROM Banners WHERE id = ?";
	$q_select = $pdo->prepare($sql_select);
	$q_select->execute(array($id));
	$row = $q_select->fetch(PDO::FETCH_ASSOC);

	if ($row) {
		$imagen_nombre = $row['url-jpg'];
		$seccion = $row['seccion'];

		if ($seccion == 1) {
			$seccion = "Home";
		} elseif ($seccion == 2) {
			$seccion = "Proveedores";
		}
		// Construir la ruta completa de la imagen
		$ruta_imagen = '../images/Banners/'. $seccion . '/' . $imagen_nombre;

		// Verificar si la imagen existe en el sistema de archivos y eliminarla
		if (file_exists($ruta_imagen)) {
			unlink($ruta_imagen);
		}

		$sql_delete = "DELETE FROM Banners WHERE id = ?";
		$q_delete = $pdo->prepare($sql_delete);
		$q_delete->execute(array($id));
	}
	
	Database::disconnect();
		
	header("Location: listarBanners.php");
	
?>