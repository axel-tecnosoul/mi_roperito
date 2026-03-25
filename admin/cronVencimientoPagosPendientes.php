<?php
/**
 * CRON - Script para marcar automáticamente como vencidos (pagado=2) 
 * los pagos pendientes según el parámetro configurado.
 * Vence por MESES COMPLETOS con lógica de semanas completas.
 * 
 * Configuración CRON RECOMENDADA:
 * 0 2 * * 1 /usr/bin/php /path/to/admin/cronVencimientoPagosPendientes.php
 * (Ejecuta todos los lunes a las 2:00 AM - equilibrio perfecto entre responsividad y eficiencia)
 * 
 * Modo AJAX: ?ajax=1 devuelve JSON para interfaz web
 */

require 'database.php';
require 'funciones.php';

$es_ajax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

try {
    
    // Calcular fecha límite usando funciones compartidas
    $fecha_info = calcularFechaLimiteVencimiento(); // Consulta parámetro internamente
    $fecha_limite = $fecha_info['fecha_limite'];
    $primer_dia_pendiente = $fecha_info['primer_dia_pendiente'];
    $meses_vencimiento = $fecha_info['meses'];
    
    // Log de información de debug
    $fecha_actual_log = date('Y-m-d H:i:s');
    $info_debug = "[$fecha_actual_log] VENCIMIENTO MENSUAL: Venciendo pagos hasta $fecha_limite (meses completos). Pendientes desde $primer_dia_pendiente. Parámetro: {$meses_vencimiento} meses\n";
    file_put_contents(__DIR__ . '/log_pagos_vencidos.txt', $info_debug, FILE_APPEND | LOCK_EX);
    
    // Marcar pagos como vencidos usando función compartida (AHORA con fecha_hora_pago)
    $resultado = marcarPagosVencidos($fecha_limite);
    
    $ventas_actualizadas = $resultado['ventas_actualizadas'];
    $canjes_actualizados = $resultado['canjes_actualizados'];
    $monto_total_vencido = $resultado['monto_total_vencido'];
    
    // Log del resultado
    $fecha = date('Y-m-d H:i:s');
    $mensaje = "[$fecha] Pagos marcados como vencidos: $ventas_actualizadas ventas, $canjes_actualizados canjes. Monto total: $" . number_format($monto_total_vencido, 2) . " (>{$meses_vencimiento} meses)\n";
    
    file_put_contents(__DIR__ . '/log_pagos_vencidos.txt', $mensaje, FILE_APPEND | LOCK_EX);
    
    if ($es_ajax) {
        // Respuesta JSON para AJAX
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Proceso ejecutado exitosamente',
            'data' => [
                'ventas_vencidas' => $ventas_actualizadas,
                'canjes_vencidos' => $canjes_actualizados,
                'total_registros' => $ventas_actualizadas + $canjes_actualizados,
                'monto_total' => $monto_total_vencido,
                'fecha_ejecucion' => $fecha,
                'parametro_meses' => $meses_vencimiento,
                'fecha_limite_aplicada' => $fecha_limite
            ]
        ]);
    } else {
        // Respuesta de texto para CRON
        echo $mensaje;
    }
    
} catch (Exception $e) {
    $error = "Error al marcar pagos vencidos: " . $e->getMessage();
    file_put_contents(__DIR__ . '/log_pagos_vencidos.txt', "[" . date('Y-m-d H:i:s') . "] ERROR: $error\n", FILE_APPEND | LOCK_EX);
    
    if ($es_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $error
        ]);
    } else {
        die($error);
    }
}
?>