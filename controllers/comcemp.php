<?php
require_once 'models/commemp.php';
require_once 'controllers/optimg.php';

$commemp = new Commemp();

$idemp = (isset($_REQUEST['idemp']) && $_REQUEST['idemp'] !== '') ? $_REQUEST['idemp'] : NULL;
$nurunt = (isset($_POST['nurunt']) && trim($_POST['nurunt']) !== '') ? trim($_POST['nurunt']) : NULL;
$numdocemp = (isset($_POST['numdocemp']) && trim($_POST['numdocemp']) !== '') ? trim($_POST['numdocemp']) : NULL;
$nomemp = (isset($_POST['nomemp']) && trim($_POST['nomemp']) !== '') ? trim($_POST['nomemp']) : NULL;
$tipoemp = (isset($_POST['tipoemp']) && $_POST['tipoemp'] !== '') ? $_POST['tipoemp'] : NULL;
$diremp = (isset($_POST['diremp']) && trim($_POST['diremp']) !== '') ? trim($_POST['diremp']) : NULL;
$codubi = (isset($_POST['ubiest']) && $_POST['ubiest'] !== '') ? $_POST['ubiest'] : NULL;
$nomconemp = (isset($_POST['nomconemp']) && trim($_POST['nomconemp']) !== '') ? trim($_POST['nomconemp']) : NULL;
$telemp = (isset($_POST['telemp']) && $_POST['telemp'] !== '') ? $_POST['telemp'] : NULL;
$coremp = (isset($_POST['coremp']) && trim($_POST['coremp']) !== '') ? trim($_POST['coremp']) : NULL;
$webbemp = (isset($_POST['webbemp']) && trim($_POST['webbemp']) !== '') ? trim($_POST['webbemp']) : NULL;
$reprleg = (isset($_POST['reprleg']) && trim($_POST['reprleg']) !== '') ? trim($_POST['reprleg']) : NULL;
$estado = (isset($_POST['estado']) && $_POST['estado'] !== '') ? $_POST['estado'] : 'Activa';
$tipcur = (isset($_POST['tipcur']) && trim($_POST['tipcur']) !== '') ? trim($_POST['tipcur']) : NULL;
$desemp = (isset($_POST['desemp']) && trim($_POST['desemp']) !== '') ? trim($_POST['desemp']) : NULL;
$idare = (isset($_POST['idare']) && $_POST['idare'] !== '') ? $_POST['idare'] : NULL;
$cupemp = (isset($_POST['cupemp']) && $_POST['cupemp'] !== '') ? $_POST['cupemp'] : NULL;
$modalidad = (isset($_POST['modalidad']) && $_POST['modalidad'] !== '') ? $_POST['modalidad'] : NULL;
$duraemp = (isset($_POST['duraemp']) && trim($_POST['duraemp']) !== '') ? trim($_POST['duraemp']) : NULL;
$infcon = (isset($_POST['infcon']) && trim($_POST['infcon']) !== '') ? trim($_POST['infcon']) : NULL;

$arch_runt = (isset($_FILES['arch_runt']['name']) && $_FILES['arch_runt']['name'] !== '') ? $_FILES['arch_runt']['name'] : NULL;
$rutrunt_actual = (isset($_POST['rutrunt']) && trim($_POST['rutrunt']) !== '') ? trim($_POST['rutrunt']) : NULL;

$rutrunt = $rutrunt_actual;
if($arch_runt){
	if(!is_dir('img/runt')){ @mkdir('img/runt', 0777, true); }
	$base = ($idemp ? $idemp : ($numdocemp ? $numdocemp : date('YmdHis')));
	$nomrunt = opti($_FILES['arch_runt'], $base, 'img/runt', 'runt');
	if($nomrunt){
		$rutrunt = "img/runt/".$nomrunt;
	}
}

$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope']:NULL;
$opera = isset($_POST['opera']) ? $_POST['opera']:NULL;
$pg = (isset($_REQUEST['pg']) && $_REQUEST['pg'] !== '') ? $_REQUEST['pg'] : 2014;

if($opera=="save"){
	if(!$numdocemp || !$nomemp){
		echo "<script> alert('El NIT y el nombre de la empresa son obligatorios'); window.location='home.php?pg=$pg'; </script>";
		exit();
	}
	$commemp->setNurunt($nurunt);
	$commemp->setRutrunt($rutrunt);
	$commemp->setNumdocemp($numdocemp);
	$commemp->setNomemp($nomemp);
	$commemp->setTipoemp($tipoemp);
	$commemp->setDiremp($diremp);
	$commemp->setCodubi($codubi);
	$commemp->setNomconemp($nomconemp);
	$commemp->setTelemp($telemp);
	$commemp->setCoremp($coremp);
	$commemp->setWebbemp($webbemp);
	$commemp->setReprleg($reprleg);
	$commemp->setEstado($estado);
	$commemp->setTipcur($tipcur);
	$commemp->setDesemp($desemp);
	$commemp->setIdare($idare);
	$commemp->setCupemp($cupemp);
	$commemp->setModalidad($modalidad);
	$commemp->setDuraemp($duraemp);
	$commemp->setInfcon($infcon);
	$res = $commemp->save();
	if($res !== false){
		echo "<script> alert('EMPRESA REGISTRADA CORRECTAMENTE'); window.location='home.php?pg=$pg'; </script>";
	} else {
		echo "<script> alert('ERROR AL REGISTRAR LA EMPRESA. VERIFIQUE LOS DATOS.'); window.location='home.php?pg=$pg'; </script>";
	}
	exit();
}

if($opera=="edi"){
	if(!$numdocemp || !$nomemp){
		echo "<script> alert('El NIT y el nombre de la empresa son obligatorios'); window.location='home.php?pg=$pg'; </script>";
		exit();
	}
	$commemp->setIdemp($idemp);
	$commemp->setNurunt($nurunt);
	$commemp->setRutrunt($rutrunt);
	$commemp->setNumdocemp($numdocemp);
	$commemp->setNomemp($nomemp);
	$commemp->setTipoemp($tipoemp);
	$commemp->setDiremp($diremp);
	$commemp->setCodubi($codubi);
	$commemp->setNomconemp($nomconemp);
	$commemp->setTelemp($telemp);
	$commemp->setCoremp($coremp);
	$commemp->setWebbemp($webbemp);
	$commemp->setReprleg($reprleg);
	$commemp->setEstado($estado);
	$commemp->setTipcur($tipcur);
	$commemp->setDesemp($desemp);
	$commemp->setIdare($idare);
	$commemp->setCupemp($cupemp);
	$commemp->setModalidad($modalidad);
	$commemp->setDuraemp($duraemp);
	$commemp->setInfcon($infcon);
	$res = $commemp->edit();
	if($res !== false){
		echo "<script> alert('SE ACTUALIZO CORRECTAMENTE'); window.location='home.php?pg=$pg'; </script>";
	} else {
		echo "<script> alert('ERROR AL ACTUALIZAR LA EMPRESA'); window.location='home.php?pg=$pg'; </script>";
	}
	exit();
}

if($ope=="eliminar"){
	if($idemp){
		$commemp->setIdemp($idemp);
		$res = $commemp->eliminar();
		if($res !== false){
			echo "<script> alert('SE ELIMINO CORRECTAMENTE'); window.location='home.php?pg=$pg'; </script>";
		} else {
			echo "<script> alert('ERROR AL ELIMINAR LA EMPRESA'); window.location='home.php?pg=$pg'; </script>";
		}
		exit();
	}
}

$datOne = NULL;

if($ope=="edi"){
	$commemp->setIdemp($idemp);
	$datOne = $commemp->getOne();
}

if($ope=="new"){
	$datOne = NULL;
}

$dat = $commemp->getAll();
$ddep = $commemp->getAllDep();
$dmunc = array();
if($datOne && isset($datOne[0]['depubi']) && $datOne[0]['depubi']){
	$dmunc = $commemp->getAllMun($datOne[0]['depubi']);
}
$dmod = $commemp->getVal(26);
$dare = $commemp->getAllAreas();
$cnt = $commemp->getConteos();
?>