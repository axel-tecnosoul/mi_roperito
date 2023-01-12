<?php 
	//session_start();
  require("config.php");
	require 'database.php';

	if(empty($_SESSION['user'])) {
		header("Location: index.php");
		die("Redirecting to index.php"); 
	}
	
	$pdo = Database::connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $bandera=0;
	if ( !empty($_POST)) {
		if ($_FILES['csv']['size'] > 0) {
			$file = $_FILES['csv']['tmp_name']; 
			$handle = fopen($file,"r"); 
			$data = fgetcsv($handle,1000,";","'");
      /*var_dump($file);
      var_dump($data);*/
			do {
				if ($data[0]) {
          if($bandera==0){
            $bandera=1;
            continue;
          }
					/* FORMATO CSV SEPARADO POR PUNTO Y COMA */
					//$data[0] = codigo 
					//$data[1] = id_categoria
					//$data[2] = descripcion
					//$data[3] = id_proveedor
					//$data[4] = precio
					//$data[5] = activo
					//$data[6] = id_almacen (optativo para modificacion)
					//$data[7] = cantidad (optativo para modificacion)
					//$data[8] = id_modalidad (optativo para modificacion)
					
					$cb = microtime(true)*10000;
					
					$sql2 = " SELECT id from productos where codigo = ? ";
					$q2 = $pdo->prepare($sql2);
					$q2->execute(array($data[0]));
					$data2 = $q2->fetch(PDO::FETCH_ASSOC);
					
					if (empty($data2)) {
						$sql = "INSERT INTO `productos`(`codigo`, `id_categoria`, `descripcion`, `id_proveedor`, `precio`, `activo`, `cb`) VALUES (?,?,?,?,?,?,?) ";
						$q = $pdo->prepare($sql);
						$q->execute(array($data[0],$data[1],$data[2],$data[3],$data[4],$data[5],$cb));
						
						$idProducto = $pdo->lastInsertId();
						$sql3 = "INSERT INTO `stock`(`id_producto`, `id_almacen`, `cantidad`, `id_modalidad`) VALUES (?,?,?,?)";
					    $q3 = $pdo->prepare($sql3);
					    $q3->execute(array($idProducto,$data[6],$data[7],$data[8]));
					} else {
						$sql = "UPDATE `productos` SET `id_categoria`=?, `descripcion`=?, `id_proveedor`=?, `precio`=?, `activo`=? WHERE `codigo`=? ";
						$q = $pdo->prepare($sql);
						$q->execute(array($data[1],$data[2],$data[3],$data[4],$data[5],$data[0]));
					}
					
				}
			} while ($data = fgetcsv($handle,1000,";","'")); 
		}
		
		header("Location: listarProductos.php");	
	}
	
	Database::disconnect();
?>