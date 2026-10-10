<?php 
include "models/conmubi.php";

$codubi = isset($_REQUEST['codubi']) ? $_REQUEST['codubi']:NULL;
$nomubi = isset($_POST['nomubi']) ? $_POST['nomubi']:NULL;
$depubi = isset($_POST['depubi']) ? $_POST['depubi']:NULL;
$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope']:NULL;
$ope2 = isset($_REQUEST['ope2']) ? $_REQUEST['ope2']:NULL;

$mubi=new Mubi();
$mubi->setCodubi($codubi);
if ($ope=="save") {
	$mubi->setNomubi($nomubi);
	$mubi->setDepubi($depubi);
	if($codubi && $ope2=='edit') $mubi->edit();
	else $mubi->save();
}

$m=2;
if ($ope=="del" && $codubi){
	$mubi->del();

	echo '<div class="d-none" data-redir="home.php?pg=' . $pg . '&msg=eliminado"></div>';
    exit;
} 
if ($ope=="edi" && $codubi){
	$dtOne = $mubi->getOne();
	$m=1;
}else{ 
	$dtOne=NULL;
}

$dat=$mubi->getAll();
$dtDtp =$mubi->getDep(0);
$stats = $mubi->getStats();
?>