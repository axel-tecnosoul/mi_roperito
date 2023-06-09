<?php
	include 'database.php';
	$aProductos=[];
	if(!empty($_GET["desde"])) {
		$filtroDesde=" AND DATE(fecha_hora)>='".$_GET["desde"]."'";
	}else{
		$filtroDesde="";
	}

	if(!empty($_GET["hasta"])){
		$filtroHasta=" AND DATE(fecha_hora)<='".$_GET["hasta"]."'";
	}else{
		$filtroHasta="";
	}

	if(!empty($_GET["proveedor"] && $_GET["proveedor"] != 0)) {
		$filtroProveedor=" AND pr.id=".$_GET["proveedor"];
	}else{
		$filtroProveedor="";
	}

	if(!empty($_GET["id_almacen"]&& $_GET["id_almacen"] != 0)) {
		$filtroAlmacen=" AND a.id=".$_GET["id_almacen"];
	}	else{
		$filtroAlmacen="";
	}

	//Ventas
	$pdo = Database::connect();
	$sql = "SELECT v.id as id_venta,vd.id AS id_detalle_venta, v.total_con_descuento, vd.pagado, a.almacen, p.codigo, c.categoria, p.descripcion, vd.cantidad, vd.precio, vd.subtotal, m.modalidad, vd.pagado, pr.nombre, pr.apellido, vd.id_forma_pago, fp.forma_pago, vd.id_venta,vd.deuda_proveedor,date_format(v.fecha_hora,'%d/%m/%Y %H:%i') AS fecha_hora,caja_egreso,forma_pago FROM ventas_detalle vd INNER JOIN ventas v ON vd.id_venta=v.id inner join productos p on p.id = vd.id_producto inner join categorias c on c.id = p.id_categoria inner join modalidades m on m.id = vd.id_modalidad inner join proveedores pr on pr.id = p.id_proveedor LEFT join almacenes a on a.id = v.id_almacen LEFT join forma_pago fp on fp.id = vd.id_forma_pago WHERE v.anulada = 0 AND v.id_venta_cbte_relacionado IS NULL $filtroDesde $filtroHasta $filtroProveedor $filtroAlmacen";
			
	foreach ($pdo->query($sql) as $row) {
		$pagado = "";
		$total_deuda = 0;
		if($row["pagado"] == 1){
			$pagado = "SI";
		}else{
			$pagado = "NO";
		}
		if($row["forma_pago"] == NULL || $row["forma_pago"] == ""){
			$forma_pago = "NINGUNA";
		}else{
			$forma_pago = $row["forma_pago"];
		}
		if($row["caja_egreso"] == NULL || $row["caja_egreso"] == ""){
			$caja_egreso = "NINGUNA";
		}else{
			$caja_egreso = $row["caja_egreso"];
		}
		$total_con_descuento = $row["total_con_descuento"];
		$aProductos[]=[
				"tipo"=>"Venta",
				"id"=>$row["id_venta"],
				"fecha_hora"=>$row["fecha_hora"],
				"descripcion"=>$row["descripcion"],
				"total_con_descuento"=>'$' . number_format($total_con_descuento,2),
				"caja_egreso"=>$caja_egreso,
				"forma_pago"=>$forma_pago,
				"almacen"=>$row["almacen"],
				"pagado"=>$pagado,
				"input" => '<a href="verVenta.php?id='. $row["id_venta"]. '"><img src="img/eye.png" width="24" height="15" border="0" alt="Ver Venta" title="Ver Venta"></a>',
				"precio"=>number_format($row["precio"],2),
				"subtotal"=>number_format($row["subtotal"],2),
				"cantidad"=>$row["cantidad"],
				"codigo"=>$row["codigo"],
				"categoria"=>$row["categoria"]
		];
	}
	//Canjes
	$sql = "SELECT cj.id AS id_canje, cd.id AS id_detalle_canje, cj.total_con_descuento, cd.pagado, a.almacen, p.codigo, c.categoria, p.descripcion, cd.cantidad, cd.precio, cd.subtotal, m.modalidad, cd.pagado, pr.nombre, pr.apellido, cd.id_forma_pago, fp.forma_pago, cd.id_canje,cd.deuda_proveedor,date_format(cj.fecha_hora,'%d/%m/%Y %H:%i') AS fecha_hora,caja_egreso,forma_pago FROM canjes_detalle cd INNER JOIN canjes cj ON cd.id_canje=cj.id inner join productos p on p.id = cd.id_producto inner join categorias c on c.id = p.id_categoria inner join modalidades m on m.id = cd.id_modalidad inner join proveedores pr on pr.id = p.id_proveedor LEFT join almacenes a on a.id = cj.id_almacen LEFT join forma_pago fp on fp.id = cd.id_forma_pago WHERE cj.anulado = 0 $filtroDesde $filtroHasta $filtroProveedor $filtroAlmacen";
			
	foreach ($pdo->query($sql) as $row) {
		$pagado = "";
		if($row["pagado"] == 1){
			$pagado = "SI";
		}else{
			$pagado = "NO";
		}
		if($row["forma_pago"] == NULL || $row["forma_pago"] == ""){
			$forma_pago = "NINGUNA";
		}else{
			$forma_pago = $row["forma_pago"];
		}
		if($row["caja_egreso"] == NULL || $row["caja_egreso"] == ""){
			$caja_egreso = "NINGUNA";
		}else{
			$caja_egreso = $row["caja_egreso"];
		}
		$total_con_descuento = $row["total_con_descuento"];
		$aProductos[]=[
				"tipo"=>"Canje",
				"id"=>$row["id_canje"],
				"fecha_hora"=>$row["fecha_hora"],
				"descripcion"=>$row["descripcion"],
				"total_con_descuento"=>'$' . number_format($total_con_descuento,2),
				"caja_egreso"=>$caja_egreso,
				"forma_pago"=>$forma_pago,
				"almacen"=>$row["almacen"],
				"pagado"=>$pagado,
				"input" => '<a href="verCanje.php?id='. $row["id_canje"]. '"><img src="img/eye.png" width="24" height="15" border="0" alt="Ver Canje" title="Ver Canje"></a>',
				"precio"=>number_format($row["precio"],2),
				"subtotal"=>number_format($row["subtotal"],2),
				"cantidad"=>$row["cantidad"],
				"codigo"=>$row["codigo"],
				"categoria"=>$row["categoria"]
		];	
	}
	Database::disconnect();
	
	if (empty($aProductos)) {
		$aProductos = []; // Asignar un array vacío si no hay resultados
	}

	echo json_encode($aProductos);