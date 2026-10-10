<?php
date_default_timezone_set('America/Bogota');
require_once 'models/esmmin.php';

$esmmin = new Esmmin();

$fecha = date("Y-m-d");
$fechos = isset($_POST['fechos']) && $_POST['fechos'] != '' ? $_POST['fechos'] : $fecha;
$fhlle = isset($_POST['fhlle']) && $_POST['fhlle'] != '' ? $_POST['fhlle'] : $fechos;
$ndocusu = isset($_POST['ndocusu']) ? trim($_POST['ndocusu']) : '';
$idJornadaSel = (isset($_POST['idjornada']) && $_POST['idjornada'] !== '') ? (int)$_POST['idjornada'] : null;

$esmmin->setIdusu($ndocusu);

// Consulta de datos de minuta
$dat = $esmmin->getTodo($fechos, $fhlle);

// Separar registros para las pestañas de cards estilo vvidgen
$datDentro = [];
$datSalieron = [];
if (!empty($dat) && is_array($dat)) {
    foreach ($dat as $d) {
        if (($d['tipmin'] ?? '') === 'I' && empty($d['fhlle'])) {
            $datDentro[] = $d;
        } else {
            $datSalieron[] = $d;
        }
    }
}

// Variables para el reporte mensual
$anioActual = (int)date("Y");
$mesActual = (int)date("m");

$mes = isset($_POST['mes']) ? (int)$_POST['mes'] : (int)$mesActual;
$anio = isset($_POST['anio']) ? (int)$_POST['anio'] : (int)$anioActual;

$nomMeses = [
    1 => "Enero", 2 => "Febrero", 3 => "Marzo", 4 => "Abril",
    5 => "Mayo", 6 => "Junio", 7 => "Julio", 8 => "Agosto",
    9 => "Septiembre", 10 => "Octubre", 11 => "Noviembre", 12 => "Diciembre"
];

$nomMesSel = $nomMeses[$mes] ?? '';

// Reporte mensual de asistencia (SOLO APRENDICES, sin otros perfiles)
$totAprend = (int)$esmmin->getTotAprendices();
$diasActivos = (int)$esmmin->getDiasActivosMes($mes, $anio);
$asistDiaria = $esmmin->getAsistenciaDiariaMes($mes, $anio);
$porAprendiz = $esmmin->getAsistenciaPorAprendizMes($mes, $anio);
$aprendices = $esmmin->getAprendices();

$sumAsist = 0;
foreach ($asistDiaria as $fila) { 
    $sumAsist += (int)$fila['asistentes']; 
}
$promAsist = ($diasActivos > 0) ? (int)round($sumAsist / $diasActivos) : 0;
$promFalt  = max(0, $totAprend - $promAsist);
$pctAsist  = ($totAprend > 0) ? (int)round($promAsist / $totAprend * 100) : 0;
$pctFalt   = ($totAprend > 0) ? max(0, 100 - $pctAsist) : 0;

$mapAtendidos = [];
foreach ($porAprendiz as $pa) { 
    $mapAtendidos[$pa['idusu']] = (int)$pa['atendidos']; 
}

// Listado de aprendices con métricas de asistencia para el mes
$listadoAprendices = [];
foreach ($aprendices as $ap) {
    $atendidos = $mapAtendidos[$ap['idusu']] ?? 0;
    $faltas = max(0, $diasActivos - $atendidos);
    $pct = ($diasActivos > 0) ? (int)round($atendidos / $diasActivos * 100) : 0;
    if ($diasActivos <= 0) {
        $estado = 'sin';
    } elseif ($pct >= 75) {
        $estado = 'bien';
    } elseif ($pct >= 50) {
        $estado = 'regular';
    } else {
        $estado = 'critico';
    }
    $listadoAprendices[] = [
        'idusu'      => $ap['idusu'],
        'ndocusu'    => $ap['ndocusu'],
        'nomusu'     => $ap['nomusu'],
        'idfic'      => $ap['idfic'] ?? '',
        'nomfic'     => $ap['nomfic'] ?? '',
        'nomjornada' => $ap['nomjornada'] ?? '',
        'atendidos'  => $atendidos,
        'faltas'     => $faltas,
        'pct'        => $pct,
        'estado'     => $estado,
    ];
}

// Top 5 aprendices con más faltas en el mes (orden descendente de faltas)
$topFaltas = $listadoAprendices;
usort($topFaltas, function ($a, $b) {
    if ($a['faltas'] !== $b['faltas']) return $b['faltas'] - $a['faltas'];
    return strcmp($a['nomusu'], $b['nomusu']);
});
$topFaltas = array_slice($topFaltas, 0, 5);

// Obtención de métricas del dashboard según la jornada
$metricas = $esmmin->getMetricasDashboard($fechos, $fhlle, $idJornadaSel);

$regAprendices    = $metricas['regAprendices'] ?? 0;
$totAprendices    = $metricas['totAprendices'] ?? 0;

$regFichas        = $metricas['regFichas'] ?? 0;
$totFichas        = $metricas['totFichas'] ?? 0;

$regEntradas      = $metricas['regEntradas'] ?? 0;
$regSalidas       = $metricas['regSalidas'] ?? 0;

$regElementos     = $metricas['regElementos'] ?? 0;
$totElementos     = $metricas['totElementos'] ?? 0;
$nomJornadaActual = $metricas['nomJornadaActual'] ?? 'Mañana';
$nomJornadaAuto   = $metricas['nomJornadaAuto'] ?? $nomJornadaActual;
$idJornadaActual  = $metricas['idJornadaActual'] ?? 1;

function segAhoras($segundos) {
    if (!$segundos || $segundos <= 0) return '00:00:00';
    $horas = floor($segundos / 3600);
    $minutos = floor(($segundos % 3600) / 60);
    $segs = $segundos % 60;
    return sprintf('%02d:%02d:%02d', $horas, $minutos, $segs);
}
?>