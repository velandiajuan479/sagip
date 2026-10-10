<?php
require_once ('models/conmpag.php');

$mpag = new conMpag();

$idpag  = isset($_REQUEST['idpag'])  ? $_REQUEST['idpag']  : NULL;
$nompag = isset($_POST['nompag'])    ? $_POST['nompag']   : NULL;
$rutpag = isset($_POST['rutpag'])    ? $_POST['rutpag']   : NULL;
$mospas = isset($_REQUEST['mospas'])  ? $_REQUEST['mospas'] : NULL;
$ordpag = isset($_POST['ordpag'])    ? $_POST['ordpag']   : NULL;
$icopag = isset($_POST['icopag'])    ? $_POST['icopag']   : NULL;
$idmod  = isset($_POST['idmod'])     ? $_POST['idmod']    : NULL;
$despag = isset($_POST['despag'])    ? $_POST['despag']   : NULL;

$opera  = isset($_REQUEST['opera'])  ? $_REQUEST['opera']  : NULL;
$pg     = isset($_REQUEST['pg'])     ? $_REQUEST['pg']     : 'pag';
$datOne = NULL;

$mpag->setIdpag($idpag);

// GUARDAR O ACTUALIZAR
if ($opera == "save") {
    $mpag->setNompag($nompag);
    $mpag->setRutpag($rutpag);
    $mpag->setMospas($mospas);
    $mpag->setOrdpag($ordpag);
    $mpag->setIcopag($icopag);
    $mpag->setIdmod($idmod);
    $mpag->setDespag($despag);

    if (!$idpag) {
        $mpag->save();
    } else {
        $mpag->edit();
    }

    echo "<script>window.location.href='home.php?pg=$pg';</script>";
    exit;
}

// CAMBIAR VISIBILIDAD
if ($idpag && $opera == "acti") {
    $mpag->setMospas($mospas);
    $mpag->editMospas();

    echo "<script>window.location.href='home.php?pg=$pg';</script>";
    exit;
}

// ELIMINAR REGISTRO
if ($opera == "eli" && $idpag) {
    $mpag->del();

    echo "<script>window.location.href='home.php?pg=$pg&msg=eliminado';</script>";
    exit;
}

// OBTENER REGISTRO PARA EDICIÓN
if ($opera == "edi" && $idpag) {
    $datOne = $mpag->getOne();
}

// CONSULTAS GENERALES
$datAll = $mpag->getAll();
$datMod = $mpag->getAllMod();
$datPdf = $mpag->getPdfPag();

// MÉTRICAS PARA EL DASHBOARD
$totalPaginas = is_array($datAll) ? count($datAll) : 0;
$paginasVisibles = 0;
$paginasOcultas = 0;

if (!empty($datAll)) {
    foreach ($datAll as $p) {
        if ($p['mospas'] == 1) {
            $paginasVisibles++;
        } else {
            $paginasOcultas++;
        }
    }
}
?>