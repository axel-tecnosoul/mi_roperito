<?php
function get_nombre_comprobante($tipo_comprobante){
  switch ($tipo_comprobante) {
    case 'R':
      $tipo_cbte="Recibo";
      break;
    case 'A':
      $tipo_cbte="Factura A";
      break;
    case 'B':
      $tipo_cbte="Factura B";
      break;
    case 'NCA':
      $tipo_cbte="Nota de Crédito A";
      break;
    case 'NCB':
      $tipo_cbte="Nota de Crédito B";
      break;
    default:
      $tipo_cbte="";
      break;
  }
  return $tipo_cbte;
}

function get_estado_comprobante($estado_abreviado){
  switch ($estado_abreviado) {
    case 'R':
      $estado_completo="Rechazado";
      break;
    case 'A':
      $estado_completo="Aprobado";
      break;
    case 'E':
      $estado_completo="ERROR";
      break;
    default:
      $estado_completo="";
      break;
  }
  return $estado_completo;
}

function format_numero_comprobante($punto_venta,$numero_comprobante){
  return str_pad($punto_venta,4,"0",STR_PAD_LEFT)."-".str_pad($numero_comprobante,8,"0",STR_PAD_LEFT);
}

function calcularDeudaProveedor($id_forma_pago,$id_modalidad,$precio_final){
  $fp = 1;
  //si el pago no es en efectivo se le hace un descuento a la proveedora
  if ($id_forma_pago != 1) {
    //$fp = 0.85;
    $fp = 0.80;
  }

  //$pagado = 0;
  //$credito = 0;
  $porcentaje_modalidad = 0;
  if ($id_modalidad == 1) {//COMPRA DIRECTA
    //$pagado = 1;
  } else if ($id_modalidad == 40) {//CONSIGNACION POR PORCENTAJE
    //$pagado = 0;
    $porcentaje_modalidad = 0.4;
  } else if ($id_modalidad == 50) {//CONSIGNACION POR CREDITO
    //$pagado = 1;
    $porcentaje_modalidad = 0.5;
  }
  
  $deuda_proveedor = $precio_final*$porcentaje_modalidad*$fp;
  return $deuda_proveedor;
}