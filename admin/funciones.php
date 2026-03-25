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
      case 'NDA':
        $tipo_cbte="Nota de Débito A";
        break;
      case 'NDB':
        $tipo_cbte="Nota de Débito B";
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
  require_once "config.php";
  require_once 'database.php';

  $pdo = Database::connect();
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $sql4 = "SELECT valor FROM parametros WHERE id = 8 ";
  $q4 = $pdo->prepare($sql4);
  $q4->execute();
  $data4 = $q4->fetch(PDO::FETCH_ASSOC);
  $porcentaje_pagar_no_efectivo=$data4["valor"];
  //var_dump($fp);
  $pdo = Database::disconnect();

  $deuda_proveedor = calcularDeudaProveedorConPorcentaje($id_forma_pago,$id_modalidad,$precio_final,$porcentaje_pagar_no_efectivo);
  /*$fp = 1;
  //si el pago no es en efectivo se le hace un descuento a la proveedora
  if ($id_forma_pago != 1) {
    //$fp = 0.85;
    //$fp = 0.80;
    $fp = $porcentaje_pagar_no_efectivo;
  }

  $porcentaje_modalidad = 0;
  if ($id_modalidad == 1) {//COMPRA DIRECTA

  } else if ($id_modalidad == 40) {//CONSIGNACION POR PORCENTAJE
    $porcentaje_modalidad = 0.4;
  } else if ($id_modalidad == 50) {//CONSIGNACION POR CREDITO
    $porcentaje_modalidad = 0.5;
  }
  
  $deuda_proveedor = number_format($precio_final*$porcentaje_modalidad*$fp,2,".","");*/
  return $deuda_proveedor;
}

function calcularDeudaProveedorConPorcentaje($id_forma_pago,$id_modalidad,$precio_final,$porcentaje){

  $fp = 1;
  //si el pago no es en efectivo se le hace un descuento a la proveedora
  if ($id_forma_pago != 1) {
    //$fp = 0.85;
    //$fp = 0.80;
    $fp = (100-$porcentaje)/100;
  }

  $porcentaje_modalidad=porcentaje_segun_modalidad($id_modalidad);
  
  //echo $precio_final."*".$porcentaje_modalidad."*".$fp."<br>";
  $deuda_proveedor = number_format($precio_final*$porcentaje_modalidad*$fp,2,".","");
  return $deuda_proveedor;
}

function porcentaje_segun_modalidad($id_modalidad){
  $porcentaje_modalidad = 0;
  if ($id_modalidad == 1) {//COMPRA DIRECTA

  } else if ($id_modalidad == 40) {//CONSIGNACION POR PORCENTAJE
    $porcentaje_modalidad = 0.4;
  } else if ($id_modalidad == 50) {//CONSIGNACION POR CREDITO
    $porcentaje_modalidad = 0.5;
  }
  return $porcentaje_modalidad;
}


function calcularDeudaProveedorViejo($id_forma_pago,$id_modalidad,$precio_final){
  $fp = 1;
  //si el pago no es en efectivo se le hace un descuento a la proveedora
  if ($id_forma_pago != 1) {
    $fp = 0.85;
    //$fp = 0.80;
  }

  $porcentaje_modalidad = 0;
  if ($id_modalidad == 1) {//COMPRA DIRECTA

  } else if ($id_modalidad == 40) {//CONSIGNACION POR PORCENTAJE
    $porcentaje_modalidad = 0.4;
  } else if ($id_modalidad == 50) {//CONSIGNACION POR CREDITO
    $porcentaje_modalidad = 0.5;
  }
  
  $deuda_proveedor = number_format($precio_final*$porcentaje_modalidad*$fp,2,".","");
  return $deuda_proveedor;
}

function ultimosDoceMeses($mesAnio=0){
  if($mesAnio==0){
      $mesAnio=date("m-Y");
  }
  $label=[$mesAnio=>0];
  $c=0;
  while($c<11){
    $mesAnio=date("m-Y",strtotime("01-".$mesAnio." - 1 month"));
    $label[$mesAnio]=0;
    $c++;
  }
  $label=array_reverse($label);
  return $label;
}

function formatFechaGraficoLineasPorMeses($array){
  $newArray=[];
  foreach ($array as $key => $value) {
      $newArray[]=date("Y M", strtotime("01-".$key));
  }
  return $newArray;
}

function randomColor(){
  $str = "#";
  for ($i = 0 ; $i < 6 ; $i++) {
      $randNum = rand(0, 15);
      switch ($randNum) {
          case 10: $randNum = "A";
          break;
          case 11: $randNum = "B";
          break;
          case 12: $randNum = "C";
          break;
          case 13: $randNum = "D";
          break;
          case 14: $randNum = "E";
          break;
          case 15: $randNum = "F";
          break;
      }
      $str .= $randNum;
  }
  return $str;
}

function get_codigo_producto($pdo, $inicial_almacen){
  //$sql3 = "SELECT CONCAT(SUBSTRING(codigo, 1, 2), LPAD(MAX(CAST(SUBSTRING(codigo, 3) AS SIGNED)) + 1, 4, '0')) AS nuevo_codigo FROM productos WHERE SUBSTRING(codigo, 1, 2) = ?";
  $sql3 = "SELECT CONCAT(
    SUBSTRING(codigo, 1, 2),
    IF(
      MAX(CAST(SUBSTRING(codigo, 3) AS UNSIGNED)) + 1 < 10000,
      LPAD(MAX(CAST(SUBSTRING(codigo, 3) AS UNSIGNED)) + 1, 4, '0'),
      MAX(CAST(SUBSTRING(codigo, 3) AS UNSIGNED)) + 1
    )
  ) AS nuevo_codigo
  FROM productos
  WHERE SUBSTRING(codigo, 1, 2) = ?";
  $q3 = $pdo->prepare($sql3);
  $q3->execute(array($inicial_almacen));
  $data3 = $q3->fetch(PDO::FETCH_ASSOC);
  if (empty($data3["nuevo_codigo"])) {
    $codigo=$inicial_almacen."0001";
  }else{
    $codigo=$data3["nuevo_codigo"];
  }
  
  return $codigo;
}

/**
 * ========================================
 * SISTEMA DE VENCIMIENTO DE PAGOS
 * ========================================
 */

/**
 * Genera la query base para ventas con todos los JOINs necesarios
 * @param string $select_clause What to SELECT (e.g., "COUNT(*) as total, SUM(...)")
 * @param string $where_extra Additional WHERE conditions
 * @return string Query SQL completa
 */
function generarQueryVentas($select_clause, $where_extra = '') {
    $query = "
        SELECT $select_clause
        FROM ventas_detalle vd 
        INNER JOIN ventas v ON v.id = vd.id_venta 
        INNER JOIN productos p ON p.id = vd.id_producto 
        INNER JOIN categorias c ON c.id = p.id_categoria 
        INNER JOIN modalidades m ON m.id = vd.id_modalidad 
        INNER JOIN proveedores pr ON pr.id = p.id_proveedor 
        INNER JOIN almacenes a ON a.id = pr.id_almacen 
        INNER JOIN forma_pago fp ON fp.id = v.id_forma_pago 
        LEFT JOIN devoluciones_detalle de ON de.id_venta_detalle = vd.id 
        WHERE v.anulada = 0 
        AND vd.id_modalidad = 40 
        AND vd.pagado = 0 
        AND de.id_devolucion IS NULL 
        AND pr.activo = 1
        $where_extra
    ";
    return $query;
}

/**
 * Genera la query base para canjes con todos los JOINs necesarios
 * @param string $select_clause What to SELECT (e.g., "COUNT(*) as total, SUM(...)")
 * @param string $where_extra Additional WHERE conditions  
 * @return string Query SQL completa
 */
function generarQueryCanjes($select_clause, $where_extra = '') {
    $query = "
        SELECT $select_clause
        FROM canjes_detalle cd 
        INNER JOIN canjes c ON c.id = cd.id_canje 
        INNER JOIN productos p ON p.id = cd.id_producto 
        INNER JOIN categorias c2 ON c2.id = p.id_categoria 
        INNER JOIN modalidades m ON m.id = cd.id_modalidad 
        INNER JOIN proveedores pr ON pr.id = p.id_proveedor 
        INNER JOIN almacenes a ON a.id = pr.id_almacen 
        LEFT JOIN forma_pago fp ON fp.id = cd.id_forma_pago 
        LEFT JOIN devoluciones_detalle de ON de.id_canje_detalle = cd.id 
        WHERE c.anulado = 0 
        AND cd.id_modalidad = 40 
        AND cd.pagado = 0 
        AND de.id_devolucion IS NULL 
        $where_extra
    ";
    return $query;
}

/**
 * Obtiene los meses configurados para vencimiento de pagos desde parámetros
 * @return int Número de meses del parámetro ID 10
 * @throws Exception Si no encuentra el parámetro o es inválido
 */
function obtenerMesesVencimientoPagos() {
    require_once 'database.php';
    
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    try {
        $sql = "SELECT valor FROM parametros WHERE id = 10"; // meses_vencimiento_pagos_proveedores
        $q = $pdo->prepare($sql);
        $q->execute();
        $data = $q->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            throw new Exception("Parámetro de meses de vencimiento no encontrado (ID 10)");
        }
        
        $meses_vencimiento = (int)$data['valor'];
        
        if ($meses_vencimiento <= 0) {
            throw new Exception("Parámetro de meses debe ser mayor a 0");
        }
        
        return $meses_vencimiento;
    } finally {
        Database::disconnect();
    }
}

/**
 * Calcula la fecha límite para vencimiento según lógica mensual completa
 * Consulta automáticamente los meses desde parámetros si no se especifican
 * 
 * @param int|null $meses_vencimiento Meses a descontar (opcional, si no se pasa consulta BD)
 * @param string|null $fecha_base Fecha base para cálculo (opcional, por defecto fecha actual)
 * @return array ['fecha_limite' => 'Y-m-d', 'primer_dia_pendiente' => 'Y-m-d', 'meses' => int]
 */
function calcularFechaLimiteVencimiento($meses_vencimiento = null, $fecha_base = null) {
    if ($meses_vencimiento === null) {
        $meses_vencimiento = obtenerMesesVencimientoPagos();
    }
    
    // Usar fecha base personalizada o fecha actual del sistema
    $fecha_actual = $fecha_base ? new DateTime($fecha_base) : new DateTime();
    
    // PASO 1: Determinar el "mes efectivo" usando lógica de semanas completas
    // Si estamos en los últimos días del mes + primeros días del siguiente,
    // se considera que seguimos en el mes anterior hasta completar la semana
    
    $fecha_para_calculo = clone $fecha_actual;
    
    // Obtener el primer día del mes actual
    $primer_dia_mes = new DateTime($fecha_actual->format('Y-m-01'));
    $dia_semana_primer_dia = $primer_dia_mes->format('N'); // 1=lunes, 7=domingo
    
    // Si estamos en los primeros días del mes Y no hemos completado la primera semana,
    // consideramos que efectivamente seguimos en el mes anterior
    $dia_actual = $fecha_actual->format('j'); // día del mes (1-31)
    
    if ($dia_actual <= 7 && $dia_semana_primer_dia != 1) {
        // Estamos en los primeros días del mes y el mes no empezó en lunes
        // Verificar si aún pertenecemos a la semana del mes anterior
        $dias_desde_lunes = $fecha_actual->format('N') - 1; // 0=lunes, 6=domingo
        $ultimo_lunes = clone $fecha_actual;
        $ultimo_lunes->sub(new DateInterval("P{$dias_desde_lunes}D"));
        
        // Si el último lunes fue del mes anterior, entonces seguimos en el mes anterior
        if ($ultimo_lunes->format('m') != $fecha_actual->format('m')) {
            $fecha_para_calculo->sub(new DateInterval('P1M'));
        }
    }
    
    // PASO 2: Aplicar lógica normal de meses completos desde el "mes efectivo"
    // Retroceder los meses configurados desde el mes efectivo
    $fecha_mes_limite = clone $fecha_para_calculo;
    $fecha_mes_limite->sub(new DateInterval("P{$meses_vencimiento}M"));
    
    // Obtener el MES ANTERIOR al mes límite (para vencer meses completos)
    $fecha_mes_limite->sub(new DateInterval('P1M'));
    
    // Fecha límite = Último día del mes a vencer (VENCIMIENTO POR MES COMPLETO)
    $fecha_limite = $fecha_mes_limite->format('Y-m-t'); // 't' = último día del mes
    
    // Primer día que sigue pendiente
    $fecha_siguiente_mes = new DateTime($fecha_limite);
    $fecha_siguiente_mes->add(new DateInterval('P1D'));
    $primer_dia_pendiente = $fecha_siguiente_mes->format('Y-m-d');
    
    return [
        'fecha_limite' => $fecha_limite,
        'primer_dia_pendiente' => $primer_dia_pendiente,
        'meses' => $meses_vencimiento
    ];
}

/**
 * Obtiene los IDs específicos de registros que se vencerían hasta una fecha límite
 * Esta función garantiza consistencia: los mismos registros evaluados son los que se actualizan
 * 
 * @param string $fecha_limite Fecha límite en formato Y-m-d
 * @return array ['ventas_ids' => [id1, id2, ...], 'canjes_ids' => [id1, id2, ...]]
 */
function obtenerIdsRegistrosVencidos($fecha_limite) {
    require_once 'database.php';
    
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    try {
        // OBTENER IDs de VENTAS que se vencerían
        $sql_ventas_ids = generarQueryVentas(
            "vd.id",
            "AND DATE(v.fecha_venta) <= ?"
        );
        
        $q = $pdo->prepare($sql_ventas_ids);
        $q->execute([$fecha_limite]);
        $ventas_ids = $q->fetchAll(PDO::FETCH_COLUMN);
        
        // OBTENER IDs de CANJES que se vencerían
        $sql_canjes_ids = generarQueryCanjes(
            "cd.id",
            "AND DATE(c.fecha_canje) <= ?"
        );
        
        $q = $pdo->prepare($sql_canjes_ids);
        $q->execute([$fecha_limite]);
        $canjes_ids = $q->fetchAll(PDO::FETCH_COLUMN);
        
        return [
            'ventas_ids' => $ventas_ids,
            'canjes_ids' => $canjes_ids
        ];
    } finally {
        Database::disconnect();
    }
}

/**
 * Obtiene los pagos (ventas y canjes) que se vencerían hasta una fecha límite
 * Esta función es para SIMULACIÓN - no hace cambios en la BD
 * 
 * @param string $fecha_limite Fecha límite en formato Y-m-d
 * @return array ['ventas' => [...], 'canjes' => [...], 'totales' => [...]]
 */
function obtenerPagosVencidos($fecha_limite) {
    require_once 'database.php';
    
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    try {
        // VENTAS: Usar query base reutilizable
        $sql_ventas = generarQueryVentas(
            "COUNT(*) as total, SUM(CASE WHEN v.tipo_comprobante = 'NCB' THEN vd.deuda_proveedor * -1 ELSE vd.deuda_proveedor END) as monto_total",
            "AND DATE(v.fecha_venta) <= ?"
        );
        
        $q = $pdo->prepare($sql_ventas);
        $q->execute([$fecha_limite]);
        $result_ventas = $q->fetch(PDO::FETCH_ASSOC);
        
        // CANJES: Usar query base reutilizable
        $sql_canjes = generarQueryCanjes(
            "COUNT(*) as total, SUM(cd.deuda_proveedor) as monto_total",
            "AND DATE(c.fecha_canje) <= ?"
        );
        
        $q = $pdo->prepare($sql_canjes);
        $q->execute([$fecha_limite]);
        $result_canjes = $q->fetch(PDO::FETCH_ASSOC);
        
        return [
            'ventas' => $result_ventas,
            'canjes' => $result_canjes,
            'totales' => [
                'total_registros' => $result_ventas['total'] + $result_canjes['total'],
                'total_monto' => $result_ventas['monto_total'] + $result_canjes['monto_total']
            ]
        ];
    } finally {
        Database::disconnect();
    }
}

/**
 * Marca como vencidos (pagado=2) los pagos pendientes hasta la fecha límite
 * Usa exactamente los mismos IDs que se evaluaron en la simulación para garantizar consistencia
 * 
 * @param string $fecha_limite Fecha límite en formato Y-m-d
 * @return array ['ventas_actualizadas' => int, 'canjes_actualizados' => int, 'monto_total_vencido' => float]
 * @throws Exception Si hay error en la transacción
 */
function marcarPagosVencidos($fecha_limite) {
    require_once 'database.php';
    
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->beginTransaction();
    
    try {
        // 1. Obtener el monto total que se va a vencer (para log)
        $pagos_a_vencer = obtenerPagosVencidos($fecha_limite);
        $monto_total = $pagos_a_vencer['totales']['total_monto'];
        
        // 2. Obtener IDs exactos de los registros que se van a vencer
        $ids_vencidos = obtenerIdsRegistrosVencidos($fecha_limite);
        
        // 3. Obtener fecha actual UNA SOLA VEZ para ambos updates
        $fecha_vencimiento = date('Y-m-d H:i:s');
        
        $ventas_actualizadas = 0;
        $canjes_actualizados = 0;
        
        // 4. Marcar VENTAS como vencidas usando IDs específicos (con fecha_hora_pago)
        if (!empty($ids_vencidos['ventas_ids'])) {
            $placeholders_ventas = str_repeat('?,', count($ids_vencidos['ventas_ids']) - 1) . '?';
            $sql_ventas = "UPDATE ventas_detalle SET pagado = 2, fecha_hora_pago = ? WHERE id IN ($placeholders_ventas)";
            
            $params_ventas = array_merge([$fecha_vencimiento], $ids_vencidos['ventas_ids']);
            $q = $pdo->prepare($sql_ventas);
            $q->execute($params_ventas);
            $ventas_actualizadas = $q->rowCount();
        }
        
        // 5. Marcar CANJES como vencidos usando IDs específicos (con fecha_hora_pago)
        if (!empty($ids_vencidos['canjes_ids'])) {
            $placeholders_canjes = str_repeat('?,', count($ids_vencidos['canjes_ids']) - 1) . '?';
            $sql_canjes = "UPDATE canjes_detalle SET pagado = 2, fecha_hora_pago = ? WHERE id IN ($placeholders_canjes)";
            
            $params_canjes = array_merge([$fecha_vencimiento], $ids_vencidos['canjes_ids']);
            $q = $pdo->prepare($sql_canjes);
            $q->execute($params_canjes);
            $canjes_actualizados = $q->rowCount();
        }
        
        $pdo->commit();
        
        return [
            'ventas_actualizadas' => $ventas_actualizadas,
            'canjes_actualizados' => $canjes_actualizados,
            'monto_total_vencido' => $monto_total
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    } finally {
        Database::disconnect();
    }
}

/**
 * Obtiene los pagos pendientes visibles en la interfaz (para comparar con test)
 * Usa la misma lógica de fecha que listarPagosPendientes.php
 * 
 * @param string|null $fecha_hasta Fecha hasta para filtrar (opcional, calcula automáticamente)
 * @return array ['ventas' => [...], 'canjes' => [...], 'totales' => [...]]
 */
function obtenerPagosPendientesInterfaz($fecha_hasta = null) {
    require_once 'database.php';
    
    if ($fecha_hasta === null) {
        // Calcular fecha límite igual que en listarPagosPendientes.php
        $dia = (int)date("d");
        $meses_descontar = 1;
        if($dia <= 5) {
            $meses_descontar = 2;
        }
        $fecha_hasta = date("Y-m-t", strtotime(date("Y-m-01") . " -$meses_descontar month"));
    }
    
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    try {
        // VENTAS: Usar query base reutilizable
        $sql_ventas = generarQueryVentas(
            "COUNT(*) as total, SUM(CASE WHEN v.tipo_comprobante = 'NCB' THEN vd.deuda_proveedor * -1 ELSE vd.deuda_proveedor END) as monto_total",
            "AND DATE(v.fecha_venta) <= ?"
        );
        
        $q = $pdo->prepare($sql_ventas);
        $q->execute([$fecha_hasta]);
        $result_ventas = $q->fetch(PDO::FETCH_ASSOC);
        
        // CANJES: Usar query base reutilizable
        $sql_canjes = generarQueryCanjes(
            "COUNT(*) as total, SUM(cd.deuda_proveedor) as monto_total",
            "AND DATE(c.fecha_canje) <= ?"
        );
        
        $q = $pdo->prepare($sql_canjes);
        $q->execute([$fecha_hasta]);
        $result_canjes = $q->fetch(PDO::FETCH_ASSOC);
        
        return [
            'ventas' => $result_ventas,
            'canjes' => $result_canjes,
            'totales' => [
                'total_registros' => $result_ventas['total'] + $result_canjes['total'],
                'total_monto' => $result_ventas['monto_total'] + $result_canjes['monto_total']
            ],
            'fecha_hasta_usada' => $fecha_hasta
        ];
    } finally {
        Database::disconnect();
    }
}