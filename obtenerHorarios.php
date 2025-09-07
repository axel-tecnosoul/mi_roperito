<?php
require("admin/config.php");
require("admin/database.php");

header('Content-Type: application/json');

$idAlmacen = isset($_GET['id_almacen']) ? (int)$_GET['id_almacen'] : 0;
$fecha     = $_GET['fecha'] ?? '';
if(!$idAlmacen || !$fecha){
    echo json_encode([]);
    exit;
}

$diaSemana = (int)date('N', strtotime($fecha)) - 1; // 0=Lunes ... 6=Domingo

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sqlFranjas = "SELECT hora_inicio,hora_fin,frecuencia_minutos,bloqueo_minutos
               FROM almacenes_horarios
               WHERE id_almacen = ? AND dia_semana = ?
               ORDER BY hora_inicio";
$q = $pdo->prepare($sqlFranjas);
$q->execute([$idAlmacen,$diaSemana]);
$franjas = $q->fetchAll(PDO::FETCH_ASSOC);

$slots = [];
foreach ($franjas as $franja) {
    $inicio = new DateTime($franja['hora_inicio']);
    $fin    = new DateTime($franja['hora_fin']);
    $freq   = (int)$franja['frecuencia_minutos'];
    $bloq   = (int)$franja['bloqueo_minutos'];
    for ($t = clone $inicio; $t < $fin; $t->modify("+{$freq} minutes")) {
        $slots[] = ['hora' => $t->format('H:i'), 'bloqueo' => $bloq, 'frecuencia' => $freq];
    }
}

$sqlTurnos = "SELECT hora FROM turnos WHERE id_almacen = ? AND fecha = ? AND id_estado = 1";
$q = $pdo->prepare($sqlTurnos);
$q->execute([$idAlmacen,$fecha]);
$reservas = $q->fetchAll(PDO::FETCH_COLUMN);

$bloqueados = [];
foreach ($reservas as $res) {
    $r = new DateTime($res);
    $bloqueados[$r->format('H:i')] = true;
    // buscar franja correspondiente para obtener parametros
    foreach ($franjas as $franja) {
        $inicioFr = new DateTime($franja['hora_inicio']);
        $finFr    = new DateTime($franja['hora_fin']);
        if ($r >= $inicioFr && $r < $finFr) {
            $freq = (int)$franja['frecuencia_minutos'];
            $bloq = (int)$franja['bloqueo_minutos'];
            $inicioBloq = (clone $r)->modify("-{$bloq} minutes");
            $finBloq    = (clone $r)->modify("+{$bloq} minutes");
            for ($t = clone $r; $t < $finBloq; $t->modify("+{$freq} minutes")) {
                $bloqueados[$t->format('H:i')] = true;
            }
            for ($t = clone $r; $t > $inicioBloq; $t->modify("-{$freq} minutes")) {
                $bloqueados[$t->format('H:i')] = true;
            }
            break;
        }
    }
}

$disponibles = [];
foreach ($slots as $slot) {
    if (!isset($bloqueados[$slot['hora']])) {
        $disponibles[] = $slot['hora'];
    }
}

Database::disconnect();

echo json_encode($disponibles);
