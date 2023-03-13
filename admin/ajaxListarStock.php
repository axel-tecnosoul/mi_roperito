<?php
include 'database.php';
$aStock=[];

$pdo = Database::connect();
$columns = $_GET['columns'];

//$sql = " SELECT s.id, p.codigo, c.categoria, p.descripcion, pr.nombre, pr.apellido, a.almacen, s.cantidad, m.modalidad, p.precio,p.activo FROM stock s inner join productos p on p.id = s.id_producto inner join almacenes a on a.id = s.id_almacen left join modalidades m on m.id = s.id_modalidad left join categorias c on c.id = p.id_categoria left join proveedores pr on pr.id = p.id_proveedor WHERE s.cantidad > 0 ";

$data_columns = ['s.id', 'p.codigo', 'c.categoria', 'p.descripcion', 'p.precio', "CONCAT(pr.nombre,' ',pr.apellido)", 'a.almacen','p.activo', 'm.modalidad', 's.cantidad'];
//$data_columns = ["p.cb","p.codigo","c.categoria","p.descripcion","CONCAT(pr.nombre,' ',pr.apellido)","p.precio","p.activo"];

$fields = ['s.id', 'p.codigo', 'c.categoria', 'p.descripcion', 'p.precio', 'nombre', 'apellido', 'a.almacen','p.activo', 'm.modalidad', 's.cantidad'];


$from="FROM stock s inner join productos p on p.id = s.id_producto inner join almacenes a on a.id = s.id_almacen left join modalidades m on m.id = s.id_modalidad left join categorias c on c.id = p.id_categoria left join proveedores pr on pr.id = p.id_proveedor";

$orderBy = " ORDER BY ";
foreach ($_GET['order'] as $order) {
    //$orderBy .= $order['column'] + 1 . " {$order['dir']}, ";
    //if($order['column']!=0){
      $orderBy .= $data_columns[$order['column']] . " {$order['dir']}, ";
    //}
    /*var_dump($order['column']);
    var_dump($order['dir']);
    var_dump($orderBy);*/
}

$proveedor=$_GET["proveedor"];
$filtroProveedor="";
if($proveedor!=""){
  $filtroProveedor=" AND p.id_proveedor IN ($proveedor)";
}

$modalidad=$_GET["modalidad"];
$filtroModalidad="";
if($modalidad!=""){
  $filtroModalidad=" AND s.id_modalidad IN ($modalidad)";
}

$categoria=$_GET["categoria"];
$filtroCategoria="";
if($categoria!=""){
  $filtroCategoria=" AND p.id_categoria IN ($categoria)";
}

$id_almacen=$_GET["id_almacen"];
$filtroAlmacen="";
if($id_almacen!=0){
  $filtroAlmacen=" AND s.id_almacen IN ($id_almacen)";
}

//var_dump($orderBy);
$orderBy = substr($orderBy, 0, -2);
//var_dump($orderBy);
$where = 's.cantidad > 0 AND p.activo = 1 AND ';

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

$whereFiltered=$where.$filtroProveedor.$filtroModalidad.$filtroCategoria.$filtroAlmacen;

$length = $_GET['length'];
$start = $_GET['start'];

//OBTENEMOS EL TOTAL DE REGISTROS
$countSql = "SELECT count(s.id) as Total $from";
$countSt = $pdo->query($countSql);

$total = $countSt->fetch()['Total'];


//OBTENEMOS EL TOTAL DE REGISTROS CON FILTRO APLICADO
// Data set length after filtering
//$resFilterLength = self::sql_exec( $db, $bindings,"SELECT COUNT(`id`) FROM productos ".($where ? "WHERE $where " : ''));
//$recordsFiltered = $resFilterLength[0][0];
$queryFiltered="SELECT COUNT(s.id) AS recordsFiltered $from ".($whereFiltered ? "WHERE $whereFiltered " : '');
//echo $queryFiltered;

$resFilterLength = $pdo->query($queryFiltered);
$recordsFiltered = $resFilterLength->fetch()['recordsFiltered'];

$campos=implode(",", $fields);

$sql2 = "SELECT SUM(s.cantidad*p.precio) AS total_stock $from WHERE $whereFiltered ";
//echo $sql2;
$row2 = $pdo->query($sql2)->fetch();

$total_stock = ($row2['total_stock'] ?: 0);

//$fields = ['cb','codigo','categoria','descripcion','nombre','apellido','precio','p.activo','p.id'];

//$sql = "SELECT * FROM productos ".($where ? "WHERE $where " : '')."$orderBy LIMIT $length OFFSET $start";
//$sql = "SELECT p.id, p.codigo, c.categoria, p.descripcion, pr.nombre, pr.apellido, p.precio, p.activo,p.cb $from ".($where ? "WHERE $where " : '')."$orderBy LIMIT $length OFFSET $start";
$sql = "SELECT $campos $from ".($whereFiltered ? "WHERE $whereFiltered " : '')."$orderBy LIMIT $length OFFSET $start";
error_log($sql);
//var_dump($sql);
$st = $pdo->query($sql);
$queryInfo="";
if ($st) {
    //$rs = $st->fetchAll(PDO::FETCH_FUNC, fn($id, $name, $price) => [$id, $name, $price] );
    //$rs = $st->fetchAll(PDO::FETCH_FUNC, fn($id, $codigo, $categoria) => [$id, $codigo, $categoria] );
    foreach ($pdo->query($sql) as $row) {
      $activo="No";
      if ($row[8]==1) {
        $activo='Si';
      }
      $aStock[]=[
        $row['id'],
        $row['codigo'],
        $row['categoria'],
        $row['descripcion'],
        $row['cantidad'],
        '$'. number_format($row['precio'],2),
        $row['nombre']." ".$row['apellido'],
        $row['almacen'],
        $row['modalidad'],
        $activo,
        '<a href="modificarStock.php?id='.$row["id"].'"><img src="img/icon_modificar.png" width="24" height="25" border="0" alt="Ajustar Cantidad" title="Ajustar Cantidad"></a>',
      ];
    }
    $queryInfo=[
      'campos' => $campos,
      'from' => $from,
      'where' => $where,
      'orderBy' => $orderBy,
      'length' => $length,
      'start' => $start,
      'query' => $sql,
      'query_total_stock' => $sql2,
      'total_stock'=>$total_stock,
    ];
} else {
    var_dump($pdo->errorInfo());
    die;
}

echo json_encode([
  'data' => $aStock,
  'recordsTotal' => $total,
  'recordsFiltered' => $recordsFiltered,//count($aStock),
  'queryInfo'=>$queryInfo,
]);

