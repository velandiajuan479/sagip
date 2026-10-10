<?php
require_once __DIR__ . '/../models/commasp.php';

// Endpoint AJAX para búsqueda de usuario por documento en tiempo real
if(isset($_GET['ajax_user'])){
	if(!headers_sent()){
		header('Content-Type: application/json; charset=utf-8');
	}
	$doc = isset($_GET['ndoc']) ? trim($_GET['ndoc']) : '';
	if(!$doc){
		echo json_encode(['success' => false, 'message' => 'Documento requerido']);
		exit;
	}
	$commasp = new Commasp();
	$u = $commasp->getUsuByDoc($doc);
	if($u && count($u) > 0){
		echo json_encode([
			'success' => true,
			'exists' => true,
			'idusu' => $u[0]['idusu'],
			'nomusu' => $u[0]['nomusu']
		]);
	} else {
		echo json_encode([
			'success' => true,
			'exists' => false,
			'nomusu' => ''
		]);
	}
	exit;
}

$commasp = new Commasp();

$idasp = isset($_REQUEST['idasp']) ? $_REQUEST['idasp']:NULL;
$ndocusu = isset($_POST['ndocusu']) ? trim($_POST['ndocusu']):NULL;
$nomusu = isset($_POST['nomusu']) ? trim($_POST['nomusu']):NULL;
$codpro = isset($_REQUEST['codpro']) ? $_REQUEST['codpro']:NULL;
$fecreg = isset($_POST['fecreg']) ? $_POST['fecreg']:NULL;
$estado = isset($_POST['estado']) ? $_POST['estado']:'Pendiente';
$pg = (isset($_REQUEST['pg']) && $_REQUEST['pg'] !== '') ? $_REQUEST['pg'] : 2001;

$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope']:NULL;
$opera = isset($_POST['opera']) ? $_POST['opera']:NULL;

if($opera=="save"){
	if(!$ndocusu){
		echo "<script> alert('El número de documento del aspirante es obligatorio'); window.location='home.php?pg=$pg'; </script>";
		exit();
	}

	$usu = $commasp->getUsuByDoc($ndocusu);
	$idusu = NULL;
	if($usu && count($usu) > 0){
		$idusu = $usu[0]['idusu'];
		if(empty($nomusu)) $nomusu = $usu[0]['nomusu'];
	} elseif(!empty($nomusu)){
		// Si no existe pero se digitó el nombre, se crea el usuario automáticamente
		$idusu = $commasp->createUser($ndocusu, $nomusu);
	} else {
		echo "<script> alert('El documento no está registrado en el sistema. Por favor ingrese el nombre del aspirante para registrarlo.'); window.location='home.php?pg=$pg'; </script>";
		exit();
	}

	$commasp->setIdusu($idusu);
	$commasp->setCodpro($codpro);
	$commasp->setFecreg(date("Y-m-d H:i:s"));
	$commasp->setEstado($estado);
	$commasp->setNdocusu($ndocusu);
	$commasp->setNomasp($nomusu);
	$res = $commasp->save();
	if($res !== false){
		echo "<script> alert('ASPIRANTE REGISTRADO CORRECTAMENTE'); window.location='home.php?pg=$pg'; </script>";
	} else {
		echo "<script> alert('ERROR AL REGISTRAR EL ASPIRANTE'); window.location='home.php?pg=$pg'; </script>";
	}
	exit();
}

if($opera=="edi"){
	if(!$ndocusu){
		echo "<script> alert('El número de documento del aspirante es obligatorio'); window.location='home.php?pg=$pg'; </script>";
		exit();
	}

	$usu = $commasp->getUsuByDoc($ndocusu);
	$idusu = NULL;
	if($usu && count($usu) > 0){
		$idusu = $usu[0]['idusu'];
		if(empty($nomusu)) $nomusu = $usu[0]['nomusu'];
	} elseif(!empty($nomusu)){
		$idusu = $commasp->createUser($ndocusu, $nomusu);
	}

	$commasp->setIdasp($idasp);
	if($idusu) $commasp->setIdusu($idusu);
	$commasp->setCodpro($codpro);
	if($fecreg){
		$commasp->setFecreg($fecreg);
	}else{
		$commasp->setFecreg(date("Y-m-d H:i:s"));
	}
	$commasp->setEstado($estado);
	$commasp->setNdocusu($ndocusu);
	$commasp->setNomasp($nomusu);
	$res = $commasp->edit();
	if($res !== false){
		echo "<script> alert('SE ACTUALIZO CORRECTAMENTE'); window.location='home.php?pg=$pg'; </script>";
	} else {
		echo "<script> alert('ERROR AL ACTUALIZAR EL ASPIRANTE'); window.location='home.php?pg=$pg'; </script>";
	}
	exit();
}

if($ope=="eliminar"){
	if($idasp){
		$commasp->setIdasp($idasp);
		$res = $commasp->eliminar();
		if($res !== false){
			echo "<script> alert('SE ELIMINO CORRECTAMENTE'); window.location='home.php?pg=$pg'; </script>";
		} else {
			echo "<script> alert('ERROR AL ELIMINAR EL ASPIRANTE'); window.location='home.php?pg=$pg'; </script>";
		}
		exit();
	}
}

$datOne = NULL;

if($ope=="edi"){
	$commasp->setIdasp($idasp);
	$datOne = $commasp->getOne();
}

if($ope=="new"){
	$datOne = NULL;
}

$dat = $commasp->getAll();
$dcm = $commasp->getAllComplementarias();
?>