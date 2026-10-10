<?php
require_once("models/hormpgcm.php");
$mprxcm = new Hormpgcm();

$pg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : 1529;
$codpro = isset($_REQUEST['codpro']) ? trim($_REQUEST['codpro']) : NULL;
$idcom = isset($_REQUEST['idcom']) ? trim($_REQUEST['idcom']) : NULL;
$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : (isset($_REQUEST['opera']) ? $_REQUEST['opera'] : NULL);
if ($ope === 'eli') { $ope = 'del'; }

$mprxcm->setCodpro($codpro);
$mprxcm->setIdcom($idcom);

// 1. Vincular Competencia a Programa
if ($ope == "save") {
    if (!$codpro || !$idcom) {
        echo "<script>window.location.href='home.php?pg={$pg}&msg=incompleto';</script>";
        exit();
    }
    if ($mprxcm->existeVinculacion($codpro, $idcom)) {
        echo "<script>window.location.href='home.php?pg={$pg}&msg=duplicado';</script>";
        exit();
    } else {
        $ok = $mprxcm->save();
        $msg = $ok ? 'vinculado' : 'error_vincular';
        echo "<script>window.location.href='home.php?pg={$pg}&msg={$msg}';</script>";
        exit();
    }
}

// 2. Desvincular Competencia de Programa
if ($ope == "del" && $codpro && $idcom) {
    $ok = $mprxcm->del();
    $msg = $ok ? 'desvinculado' : 'error_desvincular';
    echo "<script>window.location.href='home.php?pg={$pg}&msg={$msg}';</script>";
    exit();
}

// 3. Cargar datos para el Dashboard y Catálogo
$datAll = $mprxcm->getAll();
$dtProg = $mprxcm->getProgramasList();
$dtComp = $mprxcm->getCompetenciasList();
$resumen = $mprxcm->getResumenDashboard();
$resumen['programas_con_malla'] = $resumen['programas_vinculados'] ?? 0;
$resumen['competencias_en_malla'] = $resumen['competencias_asignadas'] ?? 0;
?>
