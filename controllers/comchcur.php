<?php
require_once("models/commhcur.php");

$mhcm = new Mhcm();
$success = ""; 

if (isset($_POST["opera"]) && $_POST["opera"] == "save") {
    $mhcm->setCodpro($_POST["codpro"]);
    $mhcm->setIdemp($_POST["idemp"]);
    $mhcm->setCodproesp($_POST["codproesp"]);
    $mhcm->setFeclini($_POST["feclini"]);
    $mhcm->setFeclin($_POST["feclin"]);
    $mhcm->setCupo($_POST["cupo"]);
    $mhcm->setJornada($_POST["jornada"]);
    $mhcm->setIdfic($_POST["idfic"]);

    if (!empty($_POST["idnorad"])) {
        $mhcm->setIdnorad($_POST["idnorad"]);
        $mhcm->actualizar();
        $success = "Registro actualizado correctamente";
    } else {
        $mhcm->guardar();
        $success = "Registro guardado correctamente";
    }
}

$anioFiltro = isset($_GET["anio"]) && $_GET["anio"] !== "" ? $_GET["anio"] : null;
$datAll = $mhcm->getAll($anioFiltro);

$datPr = $mhcm->getAllPro();
$datEm = $mhcm->getAllEmp();
$datPe = $mhcm->getAllProEsp();
$datJo = $mhcm->getAllJornada();
$datFic = $mhcm->getAllFic();

$idnorad = isset($_GET["idnorad"]) ? $_GET["idnorad"] : NULL;

if ($idnorad) {
    $mhcm->setIdnorad($idnorad);
    $dtaEdit = $mhcm->getOne();
    if ($dtaEdit) {
        $dtaEdit = $dtaEdit[0]; 
    }
}

$success = isset($_GET["success"]) ? $_GET["success"] : $success;
?>