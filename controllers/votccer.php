<?php

require_once 'models/votmcer.php';

$votmcer = new VotMcer();
$idusu = isset($_SESSION['idusu']) ? $_SESSION['idusu']:NULL;
$ndocusu = isset($_POST['ndocusu']) ? $_POST['ndocusu']:NULL;
$nomusu = isset($_POST['nomusu']) ? $_POST['nomusu']:NULL;
$idper = isset($_POST['idper']) ? $_POST['idper']:NULL;
$idfic = isset($_POST['idfic']) ? $_POST['idfic']:NULL;
$pasusu = isset($_POST['pasusu']) ? $_POST['pasusu']:NULL;
$idcen = isset($_POST['idcen']) ? $_POST['idcen']:NULL;
$actusu = isset($_POST['actusu']) ? $_POST['actusu']:NULL;
$opecer = isset($_REQUEST['opera']) ? $_REQUEST['opera']:NULL;
$datOne = NULL;

$pg = 1205;



//echo $idusu."-".$ndocusu."-".$nomusu."-".$idper."-".$idfic."-".$pasusu."-".$idcen."-".$actusu."-".$opera;


//Insertar
$votmcer->setIdusu($idusu);
if($idusu){
	
}

//mostrar todos los datos
$dat = $votmcer->selAll();
$dce = $votmcer->getCentro();
$dfi = $votmcer->getFicha();
$dpe = $votmcer->getPerfil();

if($idusu){
	$datOne = $votmcer->selOne();
}

?>
