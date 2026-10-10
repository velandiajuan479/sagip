<?php
require_once("models/hormprg.php");

$mprg = new Hormprg();

$pg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : 1527;
$codpro = !empty($_REQUEST['codpro']) ? trim($_REQUEST['codpro']) : NULL;
$nompro = !empty($_POST['nompro']) ? trim($_POST['nompro']) : NULL;
$despro = !empty($_POST['despro']) ? trim($_POST['despro']) : NULL;
$verpro = (isset($_POST['verpro']) && $_POST['verpro'] !== '') ? (int)$_POST['verpro'] : 1;
$horlpro = (isset($_POST['horlpro']) && $_POST['horlpro'] !== '') ? (int)$_POST['horlpro'] : 0;
$horppro = (isset($_POST['horppro']) && $_POST['horppro'] !== '') ? (int)$_POST['horppro'] : 0;
$crelpro = (isset($_POST['crelpro']) && $_POST['crelpro'] !== '') ? (int)$_POST['crelpro'] : 0;
$creppro = (isset($_POST['creppro']) && $_POST['creppro'] !== '') ? (int)$_POST['creppro'] : 0;
$tippro = (isset($_POST['tippro']) && $_POST['tippro'] !== '') ? (int)$_POST['tippro'] : NULL;
$just = !empty($_POST['just']) ? trim($_POST['just']) : NULL;
$redcon = !empty($_POST['redcon']) ? trim($_POST['redcon']) : NULL;
$reqing = !empty($_POST['reqing']) ? trim($_POST['reqing']) : NULL;
$reqcer = !empty($_POST['reqcer']) ? trim($_POST['reqcer']) : NULL;
$idare = (isset($_POST['idare']) && $_POST['idare'] !== '') ? (int)$_POST['idare'] : 0;

$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : (isset($_REQUEST['opera']) ? $_REQUEST['opera'] : NULL);
$ope2 = isset($_REQUEST['ope2']) ? $_REQUEST['ope2'] : NULL;
if ($ope === 'eli') { $ope = 'del'; }
if ($ope === 'save1') { $ope = 'save'; $ope2 = 'edit'; }

$mprg->setCodpro($codpro);

// 1. Guardar o editar Programa
if ($ope == "save") {
    if (!$codpro || !$nompro) {
        echo "<script>window.location.href='home.php?pg={$pg}&msg=incompleto';</script>";
        exit();
    }

    $mprg->setNompro($nompro);
    $mprg->setDespro($despro);
    $mprg->setVerpro($verpro);
    $mprg->setHorlpro($horlpro);
    $mprg->setHorppro($horppro);
    $mprg->setCrelpro($crelpro);
    $mprg->setCreppro($creppro);
    $mprg->setTippro($tippro);
    $mprg->setJust($just);
    $mprg->setRedcon($redcon);
    $mprg->setReqing($reqing);
    $mprg->setReqcer($reqcer);
    $mprg->setIdare($idare);

    if ($ope2 == "edit") {
        $ok = $mprg->edit();
        if ($ok) {
            echo "<script>window.location.href='home.php?pg={$pg}&msg=editado';</script>";
        } else {
            $err = isset($_SESSION['last_crud_error']) ? urlencode($_SESSION['last_crud_error']) : 'error';
            echo "<script>window.location.href='home.php?pg={$pg}&msg=error_guardar&err={$err}';</script>";
        }
        exit();
    } else {
        if ($mprg->existePrograma($codpro)) {
            echo "<script>window.location.href='home.php?pg={$pg}&msg=duplicado';</script>";
            exit();
        } else {
            $ok = $mprg->save();
            if ($ok) {
                echo "<script>window.location.href='home.php?pg={$pg}&msg=guardado';</script>";
            } else {
                $err = isset($_SESSION['last_crud_error']) ? urlencode($_SESSION['last_crud_error']) : 'error';
                echo "<script>window.location.href='home.php?pg={$pg}&msg=error_guardar&err={$err}';</script>";
            }
            exit();
        }
    }
}

// 2. Eliminar programa
if ($ope == "del" && $codpro) {
    $ok = $mprg->del();
    $msg = $ok ? 'eliminado' : 'error_eliminar';
    echo "<script>window.location.href='home.php?pg={$pg}&msg={$msg}';</script>";
    exit();
}

// 3. Cargar datos para edición si aplica
if ($ope == "edi" && $codpro) {
    $dtOne = $mprg->getOne();
} else { 
    $dtOne = NULL;
}

// 4. Cargar listados y métricas del Dashboard
$dat = $mprg->getAllWithCounts();
$datfil = $dat; // Compatibilidad total
$dtArea = $mprg->getArea();
$dtTippr = $mprg->getTipPr();
$resumenPrg = $mprg->getResumenDashboard();
$distTipos = $mprg->getDistTipos();
$distAreas = $mprg->getDistAreas();
$distJornadas = $mprg->getDistJornadas();
?>
