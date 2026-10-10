<?php
require_once 'models/commdoc.php';

$commdoc = new Commdoc();

$idasp = isset($_REQUEST['idasp']) ? $_REQUEST['idasp']:NULL;
$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope']:NULL;
$pg = isset($_REQUEST['pg']) ? $_REQUEST['pg']:NULL;

$datOne = NULL;

if($idasp){
	$commdoc->setIdasp($idasp);
	$datOne = $commdoc->getOne();
}
?>