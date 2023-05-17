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

$array = explode(',', $_GET['id']);?>
<style>
  body{
    margin: 2px;
    margin-top: 0;
  }
  .borde_etiqueta{
    border: solid 1px black;
    border-radius: 10px;
    width:175px;
    height:82.5px;
    float: left;
    /*margin: 16.5px;*/
    margin: 8.5px;
    margin-bottom: 0;
    margin-top: 5px;
  }
  .img_etiqueta{
    margin:10 auto; max-width:100%; max-height:100%; display: block; height: 66px; align-items: center;
  }
  .container_img{
    max-height: 55px; max-width: 160px; margin: 0 auto;
  }
  .lbl_etiqueta{
    text-align: center; margin: 0; font-size: 14px; margin-bottom: 0px;
  }
  .contenedor{
    width:100%;
    height:100%;
    /*margin: 0;*/
    /*margin-left: -12px;
    margin-right: -12px;*/
    margin-bottom: 0;
    margin-top: 0;
  }
</style>

<!-- margin-top: -15px; -->
<div class='contenedor'><?php
  foreach ($array as $value){
    
    $sql = "SELECT id_proveedor, codigo, descripcion, precio, cb FROM productos WHERE id = ? ";
    $q = $pdo->prepare($sql);
    $q->execute(array($value));
    $data = $q->fetch(PDO::FETCH_ASSOC);
    
    $nombre = $data['descripcion'];
    $proveedor = $data['id_proveedor'];
    $codigo = $data['codigo'];
    $precio = $data['precio'];
    $cb = $data['cb'];?>
    <div class="borde_etiqueta">
      <div>
        <div>
          <div class="container_img">
            <img class="img_etiqueta" alt='testing' src='barcode.php?codetype=Code39&size=50&text=<?="      ".$cb?>&print=true'/>
          </div>
        </div>
        <p class="lbl_etiqueta"><?=$proveedor." "?><?=$codigo?> $<?=number_format($precio,2)?></p>
      </div>
    </div>

    <!-- <div style='border: solid 1px black; border-radius: 10px; width:153px; height:82.5px; float: left; margin: 16.5px;'>
      <div>
        <div>
          <div style='max-height: 55px; max-width: 137.5px; margin: 0 auto;'>
            <img style='margin:10 auto; max-width:100%; max-height:100%; display: block; height: 66px; align-items: center;' alt='testing' src='barcode.php?codetype=Code39&size=50&text=<?=$cb?>&print=true'/>
          </div>
        </div>
        <p style='text-align: center; margin: 0; font-size: 14px; margin-bottom: 11px;'><?=$codigo?> $<?=number_format($precio,2)?></p>
      </div>
    </div> -->

    <!-- <div style='border: solid 1px black; border-radius: 10px; width:150px; height:75px; float: left; margin: 15px;'>
      <div>
        <div>
          <div style='max-height: 50px; max-width: 125px; margin: 0 auto;'>
            <img style='margin:10 auto; max-width:100%; max-height:100%; display: block; height: 60px; align-items: center;' alt='testing' src='barcode.php?codetype=Code39&size=50&text=<?=$cb?>&print=true'/>
          </div>
        </div>
        <p style='text-align: center; margin: 0; font-size: 14px; margin-bottom: 10px;'><?=$codigo?> $<?=number_format($precio,2)?></p>
      </div>
    </div> -->
    <?php

  }?>
</div><?php

Database::disconnect();?>