<?php
	include 'database.php';
	$aProductos=[];
	if(!empty($_GET["desde"] && $_GET["hasta"] && $_GET["proveedor"] && $_GET["id_almacen"])) {

		$filtroDesde=" AND DATE(fecha_hora_pago)>='".$_GET["desde"]."'";
		$filtroHasta=" AND DATE(fecha_hora_pago)<='".$_GET["hasta"]."'";
		$filtroProveedor=" AND pr.id=".$_GET["proveedor"];
		$filtroAlmacen=" AND a.id=".$_GET["id_almacen"];
		
		//Ventas
		$pdo = Database::connect();
		$sql = "SELECT v.id as id_venta,vd.id AS id_detalle_venta, vd.pagado, a.almacen, p.codigo, c.categoria, p.descripcion, vd.cantidad, vd.precio, vd.subtotal, m.modalidad, vd.pagado, pr.nombre, pr.apellido, vd.id_forma_pago, fp.forma_pago, vd.id_venta,vd.deuda_proveedor,date_format(vd.fecha_hora_pago,'%d/%m/%Y %H:%i') AS fecha_hora_pago,caja_egreso,forma_pago FROM ventas_detalle vd INNER JOIN ventas v ON vd.id_venta=v.id inner join productos p on p.id = vd.id_producto inner join categorias c on c.id = p.id_categoria inner join modalidades m on m.id = vd.id_modalidad inner join proveedores pr on pr.id = p.id_proveedor LEFT join almacenes a on a.id = vd.id_almacen LEFT join forma_pago fp on fp.id = vd.id_forma_pago WHERE v.anulada = 0 AND v.id_venta_cbte_relacionado IS NULL $filtroDesde $filtroHasta $filtroProveedor $filtroAlmacen";
			
		foreach ($pdo->query($sql) as $row) {
			$pagado = "";
			$total_deuda = 0;
			if($row["pagado"] == 1){
				$pagado = "SI";
			}else{
				$pagado = "NO";
			}
			$deuda = $row["deuda_proveedor"];
            $total_deuda+=$deuda;
			$aProductos[]=[
				//"tipo"=>"v",
				"id"=>"id_venta",
				"fecha_hora"=>$row["fecha_hora_pago"],
				"descripcion"=>$row["descripcion"],
				"deuda"=>'$' . number_format($deuda,2),
				"caja_egreso"=>$row["caja_egreso"],
				"forma_pago"=>$row["forma_pago"],
				"almacen"=>$row["almacen"],
				"pagado"=>$pagado,
				"input"=>'<a href="verVenta.php?id=<?=$row["id_venta"]?>"><img src="img/eye.png" width="24" height="15" border="0" alt="Ver Venta" title="Ver Venta"></a>',
				"precio"=>number_format($row["precio"],2),
				"subtotal"=>number_format($row["subtotal"],2),
				"cantidad"=>$row["cantidad"],
				"codigo"=>$row["codigo"],
				"categoria"=>$row["categoria"],
				//"total_deuda"=>$total_deuda
			];
		}
		//Canjes
		$sql = "SELECT cj.id AS id_canje, cd.id AS id_detalle_canje, cd.pagado, a.almacen, p.codigo, c.categoria, p.descripcion, cd.cantidad, cd.precio, cd.subtotal, m.modalidad, cd.pagado, pr.nombre, pr.apellido, cd.id_forma_pago, fp.forma_pago, cd.id_canje,cd.deuda_proveedor,date_format(cd.fecha_hora_pago,'%d/%m/%Y %H:%i') AS fecha_hora_pago,caja_egreso,forma_pago FROM canjes_detalle cd INNER JOIN canjes cj ON cd.id_canje=cj.id inner join productos p on p.id = cd.id_producto inner join categorias c on c.id = p.id_categoria inner join modalidades m on m.id = cd.id_modalidad inner join proveedores pr on pr.id = p.id_proveedor LEFT join almacenes a on a.id = cd.id_almacen LEFT join forma_pago fp on fp.id = cd.id_forma_pago WHERE cj.anulado = 0 $filtroDesde $filtroHasta $filtroProveedor $filtroAlmacen";
			
		foreach ($pdo->query($sql) as $row) {
			$pagado = "";
			if($row["pagado"] == 1){
				$pagado = "SI";
			}else{
				$pagado = "NO";
			}
			$deuda = $row["deuda_proveedor"];
            $total_deuda+=$deuda;
			$aProductos[]=[
				//"tipo"=>"c",
				"id"=>"id_canje",
				"fecha_hora"=>$row["fecha_hora_pago"],
				"descripcion"=>$row["descripcion"],
				"deuda"=>'$' . number_format($deuda,2),
				"caja_egreso"=>$row["caja_egreso"],
				"forma_pago"=>$row["forma_pago"],
				"almacen"=>$row["almacen"],
				"pagado"=>$pagado,
				"input"=>'<a href="verCanje.php?id=<?=$row["id_canje"]?>"><img src="img/eye.png" width="24" height="15" border="0" alt="Ver Canje" title="Ver Canje"></a>',
				"precio"=>number_format($row["precio"],2),
				"subtotal"=>number_format($row["subtotal"],2),
				"cantidad"=>$row["cantidad"],
				"codigo"=>$row["codigo"],
				"categoria"=>$row["categoria"],
				//"total_deuda"=>$total_deuda
			];	
		}
		Database::disconnect();
	}
	echo json_encode($aProductos);