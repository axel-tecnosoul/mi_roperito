<?php
	include 'database.php';
  $aProductos=[];
	if(!empty($_GET["almacen"]) and $_GET["almacen"]>0) {

			$pdo = Database::connect();
			$sql = " SELECT s.id, p.codigo, c.categoria, p.descripcion, p.precio, s.cantidad, p.cb FROM stock s inner join productos p on p.id = s.id_producto inner join categorias c on c.id = p.id_categoria WHERE s.cantidad > 0 and p.activo = 1 and s.id_almacen = ".$_GET["almacen"];
			//echo $sql;
			foreach ($pdo->query($sql) as $row) {
        $aProductos[]=[
				  "cb"=>$row[6],
				  "codigo"=>$row[1],
				  "categoria"=>$row[2],
				  "descripcion"=>$row[3],
				  "precio"=>'$'. number_format($row[4],2),
				  "cantidad"=>$row[5],
          "id_producto"=>$row[0],
				  "input"=>'<input type="number" name="cantidad_'.$row[0].'" id="cantidad_'.$row[0].'" min="0" max="'.$row[5].'" value="" placeholder="0" />',
        ];
		   }
		   Database::disconnect();
	}
  echo json_encode($aProductos);