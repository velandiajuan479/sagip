<?php
require_once 'models/mcof.php';
$mcof = new Mcof();

$idcof= isset($_REQUEST['idcof'])? $_REQUEST['idcof']:1;
$logcof=NULL;
$titcof= isset($_POST['titcof'])? $_POST['titcof']:NULL;
$foocof= isset($_POST['foocof'])? $_POST['foocof']:NULL;
$arcimg= isset($_FILES['arcimg'])? $_FILES['arcimg']:NULL;

$ope= isset($_REQUEST['ope'])? $_REQUEST['ope']:NULL;

$val = NULL;

$dft= date("YmdHis");
if($arcimg) $logcof = opti($arcimg, "fotconf", "img", $dft);
$mcof->setIdcof($idcof);
$val = $mcof->getOne();
if($ope=="saveCF"){
    $mcof->setLogcof($logcof);
	$mcof->setTitcof($titcof);
    $mcof->setFoocof($foocof);
	$mcof->edit();
}

if($idcof) $val = $mcof->getOne();
?>