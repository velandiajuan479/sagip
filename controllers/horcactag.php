
<?php
require_once('models/hormactag.php'); 

$idact = isset($_REQUEST['idact']) ? $_REQUEST['idact']:NULL;
$idses = isset($_REQUEST['idses']) ? $_REQUEST['idses']:NULL;
$nomact = isset($_POST['nomact']) ? $_POST['nomact']:NULL;
$desact = isset($_POST['desact']) ? $_POST['desact']:NULL;
$duract = isset($_POST['duract']) ? $_POST['duract']:NULL;
$tipact = isset($_POST['tipact']) ? $_POST['tipact']:NULL;
$ordact = isset($_POST['ordact']) ? $_POST['ordact']:NULL;
$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope']:NULL;


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$idper_actual = (int)($_SESSION['idper'] ?? ($_SESSION['idpergen'] ?? 0));
$idusu_actual = (int)($_SESSION['idusu'] ?? 0);
$esAdmin = in_array($idper_actual, [1, 2, 21, 28]);
$esInstructor = in_array($idper_actual, [7, 32, 42]);
$esAprendiz = in_array($idper_actual, [4, 8, 22, 23, 39, 41]);

$hormactag=new Hormactag();
$hormactag->setIdact($idact);

// Los aprendices tienen acceso de sólo lectura
if (!$esAprendiz) {
    if ($ope=="save") {
        $hormactag->setIdact($idact);
        $hormactag->setNomact($nomact);
        $hormactag->setIdses($idses);
        $hormactag->setDesact($desact);
        $hormactag->setDuract($duract);
        $hormactag->setTipact($tipact);
        $hormactag->setOrdact($ordact);
        if($idact) $hormactag->edit();
        else $hormactag->save();
    } 

    if ($ope=="del" && $idact) {$hormactag->del();}
}

$m=2;

if (!$esAprendiz && $ope=="edi" && $idact){
    $dtOne = $hormactag->getOne();
    $m=1;
}else{ 
    $dtOne=NULL;
}

// Filtrar según rol
$filtroIdusu = $esAdmin ? null : $idusu_actual;
$dat=$hormactag->getAll($filtroIdusu);
$dses = $hormactag->getSesion($filtroIdusu);


// Cálculo de Métricas KPI para el Dashboard de Actividades
$totalDuracionAct = 0;
$sesionesAct = [];
$tiposAct = [];

if (!empty($dat)) {
    foreach ($dat as $actItem) {
        if (isset($actItem['duract']) && is_numeric($actItem['duract'])) {
            $totalDuracionAct += floatval($actItem['duract']);
        }
        if (!empty($actItem['idses'])) {
            $sesionesAct[$actItem['idses']] = true;
        }
        if (isset($actItem['tipact']) && $actItem['tipact'] !== '') {
            $tiposAct[$actItem['tipact']] = true;
        }
    }
}

$totalActividadesCount = count($dat ?? []);
$promedioDuracion = $totalActividadesCount > 0 ? round($totalDuracionAct / $totalActividadesCount, 1) : 0;

if ($totalDuracionAct >= 60) {
    $horasDuracion = floor($totalDuracionAct / 60);
    $minDuracion = $totalDuracionAct % 60;
    $duracionFormateada = $horasDuracion . 'h ' . ($minDuracion > 0 ? $minDuracion . 'm' : '');
} else {
    $duracionFormateada = $totalDuracionAct . ' min';
}

$kpiActividad = [
    'total_actividades'  => $totalActividadesCount,
    'total_duracion'     => $duracionFormateada,
    'total_sesiones'     => count($sesionesAct),
    'total_tipos'        => count($tiposAct),
    'promedio_duracion'  => $promedioDuracion . ' min'
];
?>
