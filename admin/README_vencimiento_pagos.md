# Sistema de Vencimiento Automático de Pagos Pendientes

## Descripción
Este sistema marca automáticamente como "vencidos" los pagos pendientes a proveedores después de un período configurable (por defecto 6 meses).

El sistema vence pagos por **meses completos con lógica de semanas completas**:
- Determina el "mes efectivo" considerando semanas completas
- Si estamos en los primeros días del mes que pertenecen a la semana del mes anterior, se considera mes anterior
- Luego aplica vencimiento por meses completos desde el "mes efectivo"

**Ejemplo**: Si hoy es 3 de abril 2026 (que pertenece a la semana de marzo), y el parámetro es 5 meses, se vence todo hasta el 30 de septiembre 2025.

## ¿Qué hace?
- Los pagos pendientes (`pagado = 0`) se convierten automáticamente en vencidos (`pagado = 2`) cuando superan el tiempo límite
- Los vencidos ya no aparecen en la lista de pagos pendientes
- Se mantiene un registro de auditoría de todas las operaciones

## Archivos creados/modificados:

### ✅ Archivos nuevos:
- `admin/cronVencimientoPagosPendientes.php` - Script principal para el cron (soporta modo AJAX)
- `admin/testVencimientoPagos.php` - Interfaz de prueba con simulación y ejecución real via AJAX
- `admin/funciones.php` - Funciones compartidas para evitar duplicación de código
- `admin/log_pagos_vencidos.txt` - Log automático (se crea al ejecutar el script)

### ✅ Archivos modificados:
- `admin/listarParametros.php` - Ahora muestra el nuevo parámetro ID 10
- `admin/ajaxProductosVendidos.php` - Muestra "Vencido" para pagado=2
- `admin/cardVerVenta.php` - Muestra "Vencido" para pagado=2  
- `admin/exportProductosVendidos.php` - Incluye estado "Vencido"
- `admin/ajaxProductosVendidosDevolucion.php` - Incluye estado "Vencido"

## Instrucciones de instalación:

### 1. Ejecutar consulta SQL
```sql
-- En phpMyAdmin o desde línea de comandos:
INSERT INTO parametros (id, parametro, valor) VALUES 
(10, 'Meses para el vencimiento de pagos a proveedores', 6);
```

### 2. Configurar parámetro inicial
- Ir a Admin → Parámetros
- Modificar "Meses para el vencimiento de pagos a proveedores" 
- Cambiar el valor según necesidad (6 meses por defecto)

### 3. Configurar CRON
Ver configuración en `DEPLOYMENT.md` del proyecto principal.

## Estados de pagado:
- `0` = Pendiente (No pagado)  
- `1` = Pagado (Si, con fecha_hora_pago registrada)
- `2` = Vencido (perdió derecho al cobro, fecha_hora_pago = momento del vencimiento para auditoría)

## Monitoreo:
- Revisar el archivo `admin/log_pagos_vencidos.txt` para ver la actividad
- El log muestra cuántos registros se actualizaron en cada ejecución

## Pruebas:
```bash
# Ejecutar manualmente para probar (desde terminal)
php /path/to/admin/cronVencimientoPagosPendientes.php
```

### Interfaz de prueba web:
- Acceder a: `http://localhost/MiRoperito/admin/testVencimientoPagos.php`
- **Características**:
  - Simulación segura sin cambios reales en la BD
  - Números en formato argentino (1.234,56)
  - Permite personalizar fecha y meses para pruebas
  - Ejecutión real via AJAX con modal de confirmación
  - Pre-carga valores configurados en la BD
  - Link directo desde Admin → Parámetros

## Nota importante:
- Los pagos vencidos (`pagado = 2`) NO aparecen en reportes de "Pagos Pendientes"
- Pero SÍ se identifican como "Vencido" en listados de productos vendidos
- **Trazabilidad completa**: `fecha_hora_pago` registra exactamente cuándo se venciò cada pago
- El sistema es reversible a nivel técnico consultando los timestamps
- **Arquitectura DRY**: `funciones.php` centraliza la lógica, evitando duplicación
- testVencimientoPagos.php y cronVencimientoPagosPendientes.php usan funciones compartidas para consistencia perfecta