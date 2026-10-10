<?php
require_once("models/conmflu.php");

$pg = isset($_REQUEST["pg"]) ? $_REQUEST["pg"] : 2008;
$idflu = (isset($_REQUEST["idflu"]) && $_REQUEST["idflu"] !== '') ? intval($_REQUEST["idflu"]) : NULL;
$nomflu = isset($_POST["nomflu"]) ? trim($_POST["nomflu"]) : NULL;
$descflu = isset($_POST["descflu"]) ? trim($_POST["descflu"]) : NULL;
$fluacti = isset($_REQUEST["fluacti"]) ? intval($_REQUEST["fluacti"]) : (isset($_REQUEST["act"]) ? intval($_REQUEST["act"]) : 1);
$ope = isset($_REQUEST["ope"]) ? $_REQUEST["ope"] : (isset($_REQUEST["opera"]) ? $_REQUEST["opera"] : NULL);
$dtOn = NULL;

$mflu = new Mflu();
$mflu->setIdflu($idflu);

if ($ope == "save" || $ope == "Insertar" || $ope == "Actualizar") {
    if (!empty($nomflu)) {
        $mflu->setNomflu($nomflu);
        $mflu->setDescflu($descflu);
        $mflu->setFluacti($fluacti);
        if ($idflu) {
            $mflu->upd();
        } else {
            $mflu->save();
        }
    }
}

if (($ope == "act" || $ope == "ediact") && $idflu && $fluacti) {
    $mflu->updAct($fluacti);
}

if ($ope == "eli" && $idflu) {
    $mflu->del();
}

if ($ope == "edi" && $idflu) {
    $dtOn = $mflu->getOne();
}

$datOne = $dtOn;
$datAll = $mflu->getAll();
$dtTot = $mflu->getTotales();
$totFlu = isset($dtTot[0]['total']) ? (int)$dtTot[0]['total'] : 0;
$totAct = isset($dtTot[0]['activos']) ? (int)$dtTot[0]['activos'] : 0;
$totInact = isset($dtTot[0]['inactivos']) ? (int)$dtTot[0]['inactivos'] : 0;
$datAct = $mflu->getFluAct();
$datInact = $mflu->getFluInact();
$metrics = $mflu->getMetrics();
?>