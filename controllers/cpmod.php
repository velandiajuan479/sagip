<?php 
define('ROOT_PATH', dirname(__DIR__));

require_once "optimg.php";
if(!class_exists("conexion")){
	require_once ROOT_PATH . '/models/conexion.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once ROOT_PATH . '/models/mmod.php';

$mmod = new mmod();

$_SESSION["idper"] = NULL;
$_SESSION["pefnom"] = "Módulos";

$idmod = isset($_POST["idmod"]) ? $_POST["idmod"]:NULL;
$idper = isset($_POST["idper"]) ? $_POST["idper"]:NULL;
$nomper = isset($_POST["nomper"]) ? $_POST["nomper"]:NULL;
$pg = isset($_POST["pg"]) ? $_POST["pg"]:NULL;
$ope = isset($_REQUEST["ope"]) ? $_REQUEST["ope"]:NULL;

if($ope=="dircc"){
	$_SESSION["idper"] = $idper;
	$_SESSION["pefnom"] = $nomper;
	echo '<script>window.location=\'home.php?pg='.$pg.'\';</script>';
}


$datAll = $mmod->getAllAct();
$datAccDir = $mmod->getAccesosDirectos($_SESSION['idusu']);

function getPagsXMod($idmod, $idusu, $idper){
	$mmod = new mmod();
	return $mmod->getPaginasXModulo($idmod, $idusu, $idper);
}

function actualizarAccesosDirectosModulo($idmod, $pagsSeleccionadas) {
    $mmod = new mmod();
    $mmod->resetAccesos($_SESSION['idusu'], $idmod); // Pone accpag = 0 para todas las páginas del módulo

    foreach ($pagsSeleccionadas as $index => $idpag) {
        $orden = $index + 1; // accpag = 1, 2, 3
        $mmod->updateAccesosDirectos($_SESSION['idusu'], $idpag, $orden);
    }
}

if (($_POST["opera"] ?? '') === "savepxp") {
 $idmod = $_POST["idmod"];
    $paginasSeleccionadas = $_POST["mdl"] ?? [];

    // Limita a máximo 3 accesos
    $paginasSeleccionadas = array_slice($paginasSeleccionadas, 0, 3);

    // Llama a la función que actualiza los accesos
    actualizarAccesosDirectosModulo($idmod, $paginasSeleccionadas);

    header("Location: ../mod.php");
}


if (session_status() === PHP_SESSION_ACTIVE) {
    $mmod->setIdusu($_SESSION["idusu"]);
}

if($datAll){
	foreach ($datAll as $dtm) {
		$mmod->setIdmod($dtm['idmod']);
		$doUP = $mmod->getOneUsuPef();
		if($doUP AND $doUP[0]['can']==0){
			if($mmod->getIdmod()==1){ $mmod->setIdper(4); $mmod->insUsuPef(); }
			if($mmod->getIdmod()==2){ $mmod->setIdper(8); $mmod->insUsuPef(); }
			//if($mmod->getIdmod()==3){ $mmod->setIdper(17); $mmod->insUsuPef(); }
			if($mmod->getIdmod()==4){ $mmod->setIdper(5); $mmod->insUsuPef(); }
		}
	}
}

$datMd = $mmod->getAllMod();
$datMod = limVec($datMd);
$dtCtn = $mmod->getCtnMod();



function limVec($vec){
	$dat = array("oo");
	if($vec){ foreach ($vec as $dt) {
		$dat[] = $dt["idmod"];
	}}
	return $dat;
}
?>