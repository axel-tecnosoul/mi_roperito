<?php
require("config.php");
if(empty($_SESSION['user'])){
  header("Location: index.php");
  die("Redirecting to index.php"); 
}
require 'database.php';

$id = null;
if ( !empty($_GET['id'])) {
  $id = $_REQUEST['id'];
}

if ( null==$id ) {
  header("Location: listarProductos.php");
}

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$array = explode(',', $id);?>
<div class='contenedor' style='max-width:100%; margin: 0;'>
  <div style='display: grid; grid-template-columns: repeat(2, 1fr); justify-content: space-evenly; gap: 30px;'><?php
    foreach ($array as $value){
      $sql = "SELECT codigo, descripcion, precio, cb FROM productos WHERE id = ? ";
      $q = $pdo->prepare($sql);
      $q->execute(array($value));
      $data = $q->fetch(PDO::FETCH_ASSOC);
      
      $nombre = $data['descripcion'];
      $codigo = $data['codigo'];
      $precio = $data['precio'];
      $cb = $data['cb'];?>
      
      <div class='row' style='border: solid 1px black; border-radius: 10px;'>
        <div class='col'>
          <div style='display:flex; justify-content: center; height: 100px; flex-wrap: wrap; margin: 20px;'>
            <div>
              <img style='max-width:100%; max-height:100%; display: block; height: 120px; align-items: center;' alt='testing' src='barcode.phcodetype=Code39&size=50&text=<?=$cb?>&print=true'/>
            </div>
          </div>
          <p style='text-align: center; margin: 0; font-size: 25px; margin-bottom: 20px;'><?=$codigo." $ ".number_format($precio,2)?></p>
        </div>
      </div><?php
    }?>
  </div>
</div><?php
Database::disconnect();
?>