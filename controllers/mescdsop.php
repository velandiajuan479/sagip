<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/mesmdsop.php';

$mesmdsop = new Mesmdsop();

$idcen = isset($_SESSION['idcen']) ? (int) $_SESSION['idcen'] : null;
$idper = isset($_SESSION['idper']) ? (int) $_SESSION['idper'] : null;
$idusu_session = isset($_SESSION['idusu']) ? (int) $_SESSION['idusu'] : null;

$idsop = isset($_REQUEST['idsop']) ? (int) $_REQUEST['idsop'] : 0;
$ope = isset($_REQUEST['ope']) ? trim($_REQUEST['ope']) : '';

$fec_filtro = isset($_GET['fec_filtro']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['fec_filtro'])
    ? $_GET['fec_filtro']
    : null;

$mes = isset($_GET['mes']) && $_GET['mes'] !== '' ? (int) $_GET['mes'] : null;
$anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? (int) $_GET['anio'] : null;

if ($mes !== null && ($mes < 1 || $mes > 12)) { $mes = null; }
if ($anio !== null && ($anio < 2000 || $anio > 2100)) { $anio = null; }

if ($ope === 'save') {
    $mesmdsop->setIdsop($idsop);
    $mesmdsop->setIdusu(isset($_POST['idusu']) && $_POST['idusu'] !== '' ? (int) $_POST['idusu'] : $idusu_session);
    $mesmdsop->setIdusu2(isset($_POST['idusu2']) && $_POST['idusu2'] !== '' ? (int) $_POST['idusu2'] : null);
    $mesmdsop->setFalrep(isset($_POST['falrep']) ? (int) $_POST['falrep'] : 0);
    $mesmdsop->setCarper(isset($_POST['carper']) ? (int) $_POST['carper'] : 0);
    $mesmdsop->setNomper(isset($_POST['nomper']) ? trim($_POST['nomper']) : '');
    $mesmdsop->setDesser(isset($_POST['desser']) ? trim($_POST['desser']) : '');

    if (!$idsop) {
        $mesmdsop->setEvisop(null);
        $idsop = $mesmdsop->save();
        $mesmdsop->setIdsop($idsop);
    }

    if (!empty($_FILES['foto']['name']) && isset($_FILES['foto']['error']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        if (function_exists('opti')) {
            $filename = opti($_FILES['foto'], $idsop, 'img/fevi', 'fevisop');
            if ($filename) {
                $mesmdsop->setEvisop('img/fevi/' . $filename);
                $mesmdsop->updateEvisop();
            }
        }
    }

    if ($idsop && isset($_POST['modo']) && $_POST['modo'] === 'edit') {
        $mesmdsop->edit();
    }
}

if ($ope === 'del' && $idsop) {
    $mesmdsop->setIdsop($idsop);
    $mesmdsop->del();
    $pg = isset($_GET['pg']) ? $_GET['pg'] : '2202';
    echo "<script>window.location.href='home.php?pg=" . $pg . "';</script>";
    exit;
}

$dtOne = null;
if ($ope === 'edit' && $idsop) {
    $mesmdsop->setIdsop($idsop);
    $dtOne = $mesmdsop->getOne();
}

$dat = $mesmdsop->getAll($idper, $idusu_session, $mes, $anio, $fec_filtro);
$metricas = $mesmdsop->getMetricasDashboard($fec_filtro ?: date('Y-m-d'), $mes, $anio);

$dtSelsop = $mesmdsop->getSelSop($idcen);
$dtCargo = $mesmdsop->getCargoPer();
$dtFalla = $mesmdsop->getFallaRep();