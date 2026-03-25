<?php
/**
 * Test de vencimiento de pagos pendientes
 * Simula el proceso sin hacer cambios reales en la BD
 * Usa las funciones compartidas corregidas de funciones.php
 */

require 'database.php';
require 'funciones.php';

// Permitir parámetros personalizados via GET
$fecha_personalizada = isset($_GET['fecha']) ? $_GET['fecha'] : null;
$meses_personalizados = isset($_GET['meses']) ? (int)$_GET['meses'] : null;

// Obtener meses por defecto de la BD para pre-rellenar el formulario
$meses_bd_defecto = obtenerMesesVencimientoPagos();

// Función para formatear fechas en español
function formatearFechaEspanol($fecha) {
    $meses = [
        'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
        'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
        'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',  
        'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
    ];
    $fecha_obj = new DateTime($fecha);
    $mes_ingles = $fecha_obj->format('F');
    $mes_espanol = $meses[$mes_ingles];
    return $fecha_obj->format('d') . ' de ' . $mes_espanol . ' de ' . $fecha_obj->format('Y');
}

try {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test - Vencimiento de Pagos</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .warning { background-color: #fff3cd; padding: 10px; border-radius: 5px; margin: 15px 0; }
        .success { background-color: #d4edda; padding: 10px; border-radius: 5px; margin: 15px 0; }
        .error { background-color: #f8d7da; padding: 10px; border-radius: 5px; margin: 15px 0; }
        table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .form-section { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .btn { background-color: #007bff; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background-color: #0056b3; }
        .highlight { font-weight: bold; color: #dc3545; }
        .input-meses { width: 80px; }
        .modal-header { border-bottom: 1px solid #dee2e6; }
        .text-danger { color: #dc3545 !important; }
        .btn-cancel { background-color: #6c757d; color: white; border: none; }
        .btn-cancel:hover { background-color: #545b62; }
        .btn-execute { background-color: #dc3545; color: white; border: 2px solid #dc3545; font-weight: bold; font-size: 1.1em; padding: 10px 25px; }
        .btn-execute:hover { background-color: #c82333; border-color: #c82333; transform: scale(1.05); }
        .btn-execute:active { transform: scale(0.98); }
    </style>
</head>
<body>

<h2>🧪 Test de Vencimiento de Pagos Pendientes</h2>
<p><em>Esta herramienta simula el proceso sin hacer cambios reales en la base de datos</em></p>

<!-- Opciones de prueba ARRIBA -->
<div class="form-section">
    <h3>🎛️ Configuración de simulación:</h3>
    <form method="get">
        <label><strong>Simular fecha actual:</strong></label>
        <input type="date" name="fecha" value="<?= $fecha_personalizada ? $fecha_personalizada : date('Y-m-d') ?>">
        
        &nbsp;&nbsp;
        
        <label><strong>Meses vencimiento:</strong></label>
        <input type="number" name="meses" value="<?= $meses_personalizados ? $meses_personalizados : $meses_bd_defecto ?>" min="1" max="24" class="input-meses">
        
        &nbsp;&nbsp;
        
        <button type="submit" class="btn">🔄 Simular</button>
        
        <?php if ($fecha_personalizada || $meses_personalizados): ?>
            &nbsp;&nbsp;<a href="testVencimientoPagos.php" style="color: #007bff;">← Modo normal</a>
        <?php endif; ?>
    </form>
</div>

<?php
    if ($fecha_personalizada): ?>
        <div class="warning">
            <strong>⚠️ MODO TEST:</strong> Simulando como si hoy fuera <strong><?= formatearFechaEspanol($fecha_personalizada) ?></strong>
            <?php if ($meses_personalizados): ?>
                con <strong><?= $meses_personalizados ?> meses</strong> de vencimiento configurados
            <?php endif; ?>
        </div>
    <?php endif;
    
    // Calcular fecha límite usando la función unificada con parámetros opcionales
    if ($fecha_personalizada || $meses_personalizados) {
        // Usar parámetros personalizados en la función unificada
        $fecha_info = calcularFechaLimiteVencimiento($meses_personalizados, $fecha_personalizada);
    } else {
        // Comportamiento normal: usar parámetros de BD
        $fecha_info = calcularFechaLimiteVencimiento();
    }
    
    $fecha_limite = $fecha_info['fecha_limite'];
    $primer_dia_pendiente = $fecha_info['primer_dia_pendiente'];
    $meses_vencimiento = $fecha_info['meses'];
    
    echo "<div class='success'>";
    echo "<strong>✅ Configuración:</strong> $meses_vencimiento meses de vencimiento configurados en el sistema";
    echo "</div>";
    
    // Obtener pagos que se vencerían usando función compartida CORREGIDA
    $pagos_vencimiento = obtenerPagosVencidos($fecha_limite);
    
    // Calcular qué mes se está venciendo para descripción mejorada
    $fecha_mes_venciendo = new DateTime($fecha_limite);
    $mes_ano_vencimiento = $fecha_mes_venciendo->format('F Y');
    $meses_espanol = [
        'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
        'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
        'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',  
        'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
    ];
    $mes_ingles = $fecha_mes_venciendo->format('F');
    $ano = $fecha_mes_venciendo->format('Y');
    $mes_ano_espanol = $meses_espanol[$mes_ingles] . ' ' . $ano;
    ?>

<h3>📅 Información de fechas:</h3>
<table>
    <tr>
        <th>Concepto</th>
        <th>Fecha</th>
        <th>Descripción</th>
    </tr>
    <tr>
        <td><strong>Fecha límite de vencimiento</strong></td>
        <td class="highlight"><?= formatearFechaEspanol($fecha_limite) ?></td>
        <td>Se vencerán <strong>TODOS los pagos HASTA esta fecha</strong> (incluyendo todo <?= $mes_ano_espanol ?> y meses anteriores)</td>
    </tr>
    <tr>
        <td>Primer día que sigue pendiente</td>
        <td><?= formatearFechaEspanol($primer_dia_pendiente) ?></td>
        <td>A partir de esta fecha los pagos seguirán pendientes (no se vencen)</td>
    </tr>
</table>

<h3>📊 Resultado de la simulación:</h3>

<?php if ($pagos_vencimiento['totales']['total_registros'] == 0): ?>
    <div class="success">
        <strong>✅ No hay pagos para vencer</strong><br>
        No existen pagos pendientes que superen los <?= $meses_vencimiento ?> meses configurados.
    </div>
<?php else: ?>
    <div class="error">
        <strong>⚠️ ATENCIÓN: Se encontraron pagos para vencer</strong>
    </div>
    
    <table>
        <tr>
            <th>Tipo de operación</th>
            <th>Cantidad de registros</th>
            <th>Monto total</th>
        </tr>
        <tr>
            <td>Ventas</td>
            <td><?= number_format($pagos_vencimiento['ventas']['total'], 0, ',', '.') ?></td>
            <td class="amount">$<?= number_format($pagos_vencimiento['ventas']['monto_total'], 2, ',', '.') ?></td>
        </tr>
        <tr>
            <td>Canjes</td>
            <td><?= number_format($pagos_vencimiento['canjes']['total'], 0, ',', '.') ?></td>
            <td class="amount">$<?= number_format($pagos_vencimiento['canjes']['monto_total'], 2, ',', '.') ?></td>
        </tr>
        <tr style="background-color: #f8d7da;">
            <td><strong>TOTAL A VENCER</strong></td>
            <td><strong><?= number_format($pagos_vencimiento['totales']['total_registros'], 0, ',', '.') ?> registros</strong></td>
            <td class="amount"><strong>$<?= number_format($pagos_vencimiento['totales']['total_monto'], 2, ',', '.') ?></strong></td>
        </tr>
    </table>
    
    <div class="warning">
        <strong>💰 IMPACTO ECONÓMICO:</strong><br>
        Si se ejecuta el proceso, <span class="highlight"><?= number_format($pagos_vencimiento['totales']['total_registros'], 0, ',', '.') ?> registros</span> 
        perderán el derecho al cobro por un monto total de 
        <span class="highlight amount">$<?= number_format($pagos_vencimiento['totales']['total_monto'], 2, ',', '.') ?></span>
    </div>
<?php endif; ?>

<hr style="margin: 30px 0;">

<div style="text-align: center; color: #666;">
    <p><strong>¿Los resultados son correctos?</strong></p>
    <p>Si estás satisfecho con la simulación, puedes ejecutar el proceso real:</p>
    <p>
        <button id="ejecutarBtn" class="btn btn-danger" data-toggle="modal" data-target="#confirmModal">
           🚀 Ejecutar Vencimiento REAL
        </button>
    </p>
    <!-- <p><small>También desde terminal: <code>php cronVencimientoPagosPendientes.php</code></small></p> -->
    <p><small>Para cambiar la configuración de meses, ve a: <strong>Admin → Parámetros → Meses para vencimiento de pagos</strong></small></p>
</div>

<!-- Modal de confirmación Bootstrap -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header text-center">
                <div class="w-100">
                    <div style="font-size: 3em; color: #dc3545;">⚠️</div>
                    <h4 class="modal-title text-danger">CONFIRMACIÓN DE EJECUCIÓN</h4>
                </div>
            </div>
            <div class="modal-body text-center">
                <div class="alert alert-danger">
                    <strong>ATENCIÓN: Esta acción requiere intervención del desarrollador para revertir</strong>
                </div>
                
                <p>Al ejecutar este proceso se cambiarán <strong>PERMANENTEMENTE</strong> los pagos pendientes a estado "VENCIDO" (pagado = 2).</p>
                
                <div class="alert alert-warning">
                    <strong>🚫IMPORTANTE:</strong><br>
                    La ejecución real usará los <strong>parámetros reales de la base de datos</strong>,
                    <u>NO</u> los valores de esta simulación.
                </div>
                
                <p><strong>No se puede deshacer desde la interfaz web</strong></p>
                <p>¿Estás completamente seguro de continuar?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-cancel" data-dismiss="modal">
                    ❌ Cancelar
                </button>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" id="confirmBtn" class="btn btn-execute">
                    🚀 SÍ, EJECUTAR AHORA
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de resultado -->
<div class="modal fade" id="resultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">📊 Resultado de Ejecución de Vencimiento</h4>
            </div>
            <div class="modal-body">
                <div id="loadingSpinner" style="text-align: center; display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Ejecutando...</span>
                    </div>
                    <p class="mt-2">Procesando vencimiento de pagos...</p>
                </div>
                <div id="resultContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="window.close();">
                    ✅ Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery PRIMERO -->
<script src="assets/js/jquery-3.2.1.min.js"></script>
<!-- Bootstrap JS -->
<script src="assets/js/bootstrap/popper.min.js"></script>
<script src="assets/js/bootstrap/bootstrap.js"></script>

<script>
$(document).ready(function() {
    // Ejecutar proceso real via AJAX
    $('#confirmBtn').click(function() {
        // Ocultar modal de confirmación
        $('#confirmModal').modal('hide');
        
        // Mostrar modal de resultado con loading
        $('#resultModal').modal('show');
        $('#loadingSpinner').show();
        $('#resultContent').html('');
        
        // Ejecutar AJAX
        $.ajax({
            url: 'cronVencimientoPagosPendientes.php?ajax=1',
            method: 'POST',
            dataType: 'json',
            timeout: 30000, // 30 segundos timeout
            success: function(response) {
                $('#loadingSpinner').hide();
                
                if (response.success) {
                    $('#resultContent').html(`
                        <div class="alert alert-success">
                            <h5>✅ Proceso ejecutado exitosamente</h5>
                        </div>
                        <table class="table table-bordered">
                            <tr><th>Total registros procesados</th><td>${response.data.total_registros.toLocaleString('es-AR')}</td></tr>
                            <tr><th>Ventas vencidas</th><td>${response.data.ventas_vencidas.toLocaleString('es-AR')}</td></tr>
                            <tr><th>Canjes vencidos</th><td>${response.data.canjes_vencidos.toLocaleString('es-AR')}</td></tr>
                            <tr><th>Monto total vencido</th><td>$${response.data.monto_total.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td></tr>
                            <tr><th>Fecha de ejecución</th><td>${response.data.fecha_ejecucion}</td></tr>
                        </table>
                        <div class="alert alert-info">
                            <strong>📅 IMPORTANTE:</strong> Todos los registros fueron marcados con fecha y hora de vencimiento para auditoría.
                        </div>
                    `);
                } else {
                    $('#resultContent').html(`
                        <div class="alert alert-danger">
                            <h5>❌ Error en la ejecución</h5>
                            <p>${response.message}</p>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                $('#loadingSpinner').hide();
                let errorMsg = 'Error desconocido';
                
                if (status === 'timeout') {
                    errorMsg = 'Tiempo de espera agotado (más de 30 segundos)';
                } else if (xhr.responseText) {
                    errorMsg = xhr.responseText;
                }
                
                $('#resultContent').html(`
                    <div class="alert alert-danger">
                        <h5>❌ Error de conexión</h5>
                        <p><strong>Status:</strong> ${status}</p>
                        <p><strong>Error:</strong> ${errorMsg}</p>
                    </div>
                `);
            }
        });
    });
});
</script>

</body>
</html>

<?php
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}
?>