<?php
session_start();
include 'database.php';
$aProductos=[];

$pdo = Database::connect();

$orderBy = " ORDER BY ";
foreach ($_GET['order'] as $order) {
    $orderBy .= $order['column'] + 1 . " {$order['dir']}, ";
}

$orderBy = substr($orderBy, 0, -2);
$where = '';

$columns = $_GET['columns'];
//$fields = ['id', 'name', 'price'];
$fields = ["s.id", "p.codigo", "c.categoria", "p.descripcion", "pr.nombre", "pr.apellido", "a.almacen", "s.cantidad", "m.modalidad"];
$where = '';

foreach ($columns as $k => $column) {
    if ($search = $column['search']['value']) {
        $where .= $fields[$k].' = '.$search.' AND ';
    }
}

$where = substr($where, 0, -5);

$globalSearch = $_GET['search'];
/*if ( $globalSearchValue = $globalSearch['value'] ) {
	$where .= ($where ? $where.' AND ' : '' )."name LIKE '%$globalSearchValue%'";
}*/
if ( $globalSearchValue = $globalSearch['value'] ) {
  $aWhere=[];
  foreach ($fields as $k => $field) {
    $aWhere[]=$field.' LIKE "%'.$globalSearchValue.'%"';
    //$where .= ($where ? $where.' AND ' : '' )."name LIKE '%$globalSearchValue%'";
  }
  $where .= ' AND ('.implode(' OR ', $aWhere).')';
}

$length = $_GET['length'];
$start = $_GET['start'];

//OBTENEMOS EL TOTAL DE REGISTROS
$countSql = "SELECT count(s.id) as Total FROM stock s inner join productos p on p.id = s.id_producto inner join almacenes a on a.id = s.id_almacen inner join modalidades m on m.id = s.id_modalidad inner join categorias c on c.id = p.id_categoria inner join proveedores pr on pr.id = p.id_proveedor";
$countSt = $pdo->query($countSql);

$total = $countSt->fetch()['Total'];


//$filtroUsuario="";
if ($_SESSION['user']['id_perfil'] == 2) {
  $where.= " and a.id = ".$_SESSION['user']['id_almacen'];
}

//OBTENEMOS EL TOTAL DE REGISTROS CON FILTRO APLICADO
// Data set length after filtering
//$resFilterLength = self::sql_exec( $db, $bindings,"SELECT COUNT(`id`) FROM productos ".($where ? "WHERE $where " : ''));
//$recordsFiltered = $resFilterLength[0][0];
$queryFiltered="SELECT COUNT(s.id) AS recordsFiltered FROM `stock` s inner join productos p on p.id = s.id_producto inner join almacenes a on a.id = s.id_almacen inner join modalidades m on m.id = s.id_modalidad inner join categorias c on c.id = p.id_categoria inner join proveedores pr on pr.id = p.id_proveedor ".($where ? "WHERE 1 $where " : '');
//var_dump($queryFiltered);

$resFilterLength = $pdo->query($queryFiltered);
$recordsFiltered = $resFilterLength->fetch()['recordsFiltered'];

//$sql = "SELECT * FROM productos ".($where ? "WHERE $where " : '')."$orderBy LIMIT $length OFFSET $start";
//$sql = "SELECT p.`id`, p.`codigo`, c.`categoria`, p.`descripcion`, pr.`nombre`, pr.`apellido`, p.`precio`, p.`activo`,p.cb FROM productos p inner join categorias c on c.id = p.id_categoria inner join proveedores pr on pr.id = p.id_proveedor ".($where ? "WHERE $where " : '')."$orderBy LIMIT $length OFFSET $start";

$sql = " SELECT s.`id`, p.`codigo`, c.`categoria`, p.`descripcion`, pr.`nombre`, pr.`apellido`, a.`almacen`, s.`cantidad`, m.`modalidad` FROM `stock` s inner join productos p on p.id = s.id_producto inner join almacenes a on a.id = s.id_almacen inner join modalidades m on m.id = s.id_modalidad inner join categorias c on c.id = p.id_categoria inner join proveedores pr on pr.id = p.id_proveedor WHERE s.`cantidad` > 0 $where $orderBy LIMIT $length OFFSET $start";
error_log($sql);
//var_dump($sql);
$st = $pdo->query($sql);

if ($st) {
    //$rs = $st->fetchAll(PDO::FETCH_FUNC, fn($id, $name, $price) => [$id, $name, $price] );
    //$rs = $st->fetchAll(PDO::FETCH_FUNC, fn($id, $codigo, $categoria) => [$id, $codigo, $categoria] );
    foreach ($pdo->query($sql) as $row) {
      $aProductos[]=[
        $row[0],
        $row[1],
        $row[2],
        $row[3],
        $row[4]." ".$row[5],
        $row[6],
        $row[8],
        $row[7],
        '<a href="modificarStock.php?id='.$row[0].'"><img src="img/icon_modificar.png" width="24" height="25" border="0" alt="Ajustar Cantidad" title="Ajustar Cantidad"></a>',
      ];
    }

    echo json_encode([
        'data' => $aProductos,
        'recordsTotal' => $total,
        'recordsFiltered' => $recordsFiltered,//count($aProductos),
    ]);
} else {
    var_dump($pdo->errorInfo());
    die;
}


/*$sql = " SELECT p.`id`, p.`codigo`, c.`categoria`, p.`descripcion`, pr.`nombre`, pr.`apellido`, p.`precio`, p.`activo`,p.cb FROM `productos` p inner join categorias c on c.id = p.id_categoria inner join proveedores pr on pr.id = p.id_proveedor WHERE 1 ";

foreach ($result=$pdo->query($sql) as $row) {
  $activo="No";
  if ($row[7]==1) {
    $activo='Si';
  }
  $aProductos[]=[
    "cb"=>$row[8],
    "codigo"=>$row[1],
    "categoria"=>$row[2],
    "descripcion"=>$row[3],
    "proveedor"=>$row[4]." ".$row[5],
    "precio"=>'$'. number_format($row[6],2),
    "activo"=>$activo,
    "id_producto"=>$row[0],
    "acciones"=>'<a href="modificarProducto.php?id='.$row[0].'"><img src="img/icon_modificar.png" width="24" height="25" border="0" alt="Modificar" title="Modificar"></a>&nbsp;&nbsp;<a href="etiquetarProducto.php?cb='.$row[8].'"><img src="img/pdf.png" width="24" height="25" border="0" alt="Etiquetar" title="Etiquetar"></a>&nbsp;&nbsp;<a href="#" data-toggle="modal" data-original-title="Confirmación" data-target="#eliminarModal_'.$row[0].'"><img src="img/icon_baja.png" width="24" height="25" border="0" alt="Eliminar" title="Eliminar"></a>&nbsp;&nbsp;',
  ];
}
$cant_registros=$result->fetchColumn();
Database::disconnect();

//echo json_encode($aProductos);
echo json_encode([
  'data' => $aProductos,
  'recordsTotal' => count($aProductos),
  //'recordsFiltered' => count($aProductos),
  'recordsFiltered' => $_GET["length"],
]);*/