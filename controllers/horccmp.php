<?php 
require_once("models/hormcmp.php");
require_once("models/hormprg.php");

$mprg = new Hormprg();
$mcom = new Hormcmp();

$pg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : 1510;
$codpro = isset($_REQUEST['codpro']) ? trim($_REQUEST['codpro']) : NULL;

$idcom = !empty($_REQUEST['idcom']) ? trim($_REQUEST['idcom']) : NULL;
$descom = !empty($_POST['descom']) ? trim($_POST['descom']) : NULL;
$vercom = (isset($_POST['vercom']) && $_POST['vercom'] !== '') ? (int)preg_replace('/[^0-9]/', '', (string)$_POST['vercom']) : 1;
$horcom = (isset($_POST['horcom']) && $_POST['horcom'] !== '') ? (int)$_POST['horcom'] : 40;
$idval = (isset($_POST['idval']) && $_POST['idval'] !== '') ? (int)$_POST['idval'] : 1;

$idres = !empty($_REQUEST['idres']) ? trim($_REQUEST['idres']) : NULL;
$nomres = !empty($_POST['nomres']) ? trim($_POST['nomres']) : NULL;
$ndeses = (isset($_POST['ndeses']) && $_POST['ndeses'] !== '') ? (int)$_POST['ndeses'] : 1;

$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : (isset($_REQUEST['opera']) ? $_REQUEST['opera'] : NULL);
$ope2 = isset($_REQUEST['ope2']) ? $_REQUEST['ope2'] : NULL;
if ($ope === 'eli') { $ope = 'del'; }
if ($ope === 'save1') { $ope = 'save'; $ope2 = 'edit'; }

$mcom->setIdcom($idcom);
$mcom->setCodpro($codpro);

// 1. Guardar o Editar Competencia
if ($ope == "save") {
	if (!$idcom || !$descom) {
		echo "<script>window.location.href='home.php?pg={$pg}&codpro={$codpro}&msg=incompleto';</script>";
		exit();
	}

	$mcom->setDescom($descom);
	$mcom->setVercom($vercom);
	$mcom->setHorcom($horcom);
	$mcom->setIdval($idval);

	if ($ope2 == 'edit') {
		$ok = $mcom->edit();
		if ($ok) {
			echo "<script>window.location.href='home.php?pg={$pg}&codpro={$codpro}&msg=editado';</script>";
		} else {
			$err = isset($_SESSION['last_crud_error']) ? urlencode($_SESSION['last_crud_error']) : 'error';
			echo "<script>window.location.href='home.php?pg={$pg}&codpro={$codpro}&msg=error_guardar&err={$err}';</script>";
		}
		exit();
	} else {
		if ($mcom->existeCompetencia($idcom)) {
			echo "<script>window.location.href='home.php?pg={$pg}&codpro={$codpro}&msg=duplicado';</script>";
			exit();
		}
		$ok = $mcom->save();
		if ($ok) {
			if ($codpro && is_numeric($codpro) && $codpro !== 'todos') {
				$mcom->delPxC();
				$mcom->savePxC();
			}
			echo "<script>window.location.href='home.php?pg={$pg}&codpro={$codpro}&msg=guardado';</script>";
		} else {
			$err = isset($_SESSION['last_crud_error']) ? urlencode($_SESSION['last_crud_error']) : 'error';
			echo "<script>window.location.href='home.php?pg={$pg}&codpro={$codpro}&msg=error_guardar&err={$err}';</script>";
		}
		exit();
	}
}

// 2. Vincular competencia existente al programa
if ($ope == "vincular") {
	$idcom_vin = isset($_POST['idcom_vincular']) ? trim($_POST['idcom_vincular']) : $idcom;
	if ($codpro && is_numeric($codpro) && $codpro !== 'todos' && $idcom_vin) {
		$mcom->setIdcom($idcom_vin);
		$ok = $mcom->savePxC();
		$msg = $ok ? 'vinculado' : 'error_vincular';
		echo "<script>window.location.href='home.php?pg={$pg}&codpro={$codpro}&msg={$msg}';</script>";
		exit();
	}
}

// 3. Desvincular competencia del programa (mantiene el registro maestro de la competencia)
if ($ope == "desvincular" && $idcom && $codpro) {
	$mcom->delPxC();
	echo "<script>window.location.href='home.php?pg={$pg}&codpro={$codpro}&msg=desvinculado';</script>";
	exit();
}

// 4. Eliminar competencia definitivamente
if ($ope == "del" && $idcom) { 
	$mcom->delPxC();
	$mcom->del();
	echo "<script>window.location.href='home.php?pg={$pg}&codpro={$codpro}&msg=eliminado';</script>";
	exit();
}

// 5. Guardar o Editar Resultado de Aprendizaje
if ($ope == "saveRs") {
	$mcom->setIdres($idres);
	$mcom->setNomres($nomres);
	$mcom->setNdeses($ndeses);
	$mcom->setIdcom($idcom);

	if ($ope2 == 'editRS') {
		$ok = $mcom->editRs();
		if ($ok) {
			echo "<script>window.location.href='home.php?pg={$pg}&codpro={$codpro}&msg=rs_editado';</script>";
		} else {
			$err = isset($_SESSION['last_crud_error']) ? urlencode($_SESSION['last_crud_error']) : 'error';
			echo "<script>window.location.href='home.php?pg={$pg}&codpro={$codpro}&msg=error_guardar&err={$err}';</script>";
		}
		exit();
	} else {
		if ($mcom->existeResultado($idres)) {
			echo "<script>window.location.href='home.php?pg={$pg}&codpro={$codpro}&msg=rs_duplicado';</script>";
			exit();
		}
		$ok = $mcom->saveRs();
		if ($ok) {
			echo "<script>window.location.href='home.php?pg={$pg}&codpro={$codpro}&msg=rs_guardado';</script>";
		} else {
			$err = isset($_SESSION['last_crud_error']) ? urlencode($_SESSION['last_crud_error']) : 'error';
			echo "<script>window.location.href='home.php?pg={$pg}&codpro={$codpro}&msg=error_guardar&err={$err}';</script>";
		}
		exit();
	}
}

// 6. Eliminar Resultado de Aprendizaje
if ($ope == "delRS" && $idres) { 
	$mcom->setIdres($idres);
	$mcom->delRs();
	echo "<script>window.location.href='home.php?pg={$pg}&codpro={$codpro}&msg=rs_eliminado';</script>";
	exit();
}

// 7. Cargar datos para edición si se solicita por GET
if ($ope == "edi" && $idcom) {
	$datOne = $mcom->getOne();
} else { 
	$datOne = NULL;
}

// 8. Cargar lista general de programas y datos del programa seleccionado
$listaProgramas = $mprg->getAll();
if (empty($codpro)) {
	$codpro = 'todos';
}
$mcom->setCodpro($codpro);

$dtPrg = NULL;
if ($codpro && $codpro !== 'todos') {
	$mprg->setCodpro($codpro);
	$dtPrg = $mprg->getOne();
}

// 9. Cargar datos para el Dashboard y la Vista
$datVal = $mcom->getTipComp();
$dtTipcom = $datVal; // Alias para selector de formulario

$datAll = $mcom->getAllWithStats($codpro);
$dat = $datAll; // Asignar explícitamente $dat con las competencias para sobrescribir el $dat de home.php

$resumen = $mcom->getResumenProg($codpro);
// Mapeo exhaustivo y seguro de claves para el Dashboard
$resumen['total_competencias'] = $resumen['tot_comp'] ?? 0;
$resumen['competencias_activas'] = $resumen['comp_activas'] ?? 0;
$resumen['competencias_inactivas'] = $resumen['comp_inactivas'] ?? 0;
$resumen['tot_horas'] = $resumen['tot_horas'] ?? 0;
$resumen['promedio_horas'] = $resumen['prom_horas_comp'] ?? 0;
$resumen['tot_resultados'] = $resumen['tot_res'] ?? 0;

$distTipo = $mcom->getDistTipoComp($codpro);
$compDisponibles = ($codpro && $codpro !== 'todos') ? $mcom->getCompetenciasDisponibles() : [];
$dtDisponibles = $compDisponibles;
$dtPrgList = $listaProgramas;
$rsProg = ($codpro && $codpro !== 'todos') ? $mcom->getAllRsProg() : [];

// Compatibilidad en caso de llamada heredada
function modal($idcom, $descom, $pg, $codpro){
	// Modales renderizados dinámicamente en views/horvcmp.php
}
?>
