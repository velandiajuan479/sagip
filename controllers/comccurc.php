<?php require_once("models/commcurc.php");

$mccm = new Mccm();

$icono = 'fa-solid fa-book';

$codpro  = isset($_REQUEST["codpro"])  ? trim($_REQUEST["codpro"])  : NULL;
$nompro  = isset($_POST["nompro"])     ? trim($_POST["nompro"])     : NULL;
$despro  = isset($_POST["despro"])     ? trim($_POST["despro"])     : NULL;
$verpro  = isset($_POST["verpro"])     ? trim($_POST["verpro"])     : NULL;
$horlpro = isset($_POST["horlpro"])    ? trim($_POST["horlpro"])    : NULL;
$tippro  = isset($_POST["tippro"])     ? $_POST["tippro"]           : 1083;
$opera   = isset($_REQUEST["opera"])   ? $_REQUEST["opera"]         : NULL;

$datOne  = NULL;
$msgccm  = "";
$tipoccm = "success";

$mccm->setCodpro($codpro);

// Alta de un curso nuevo
if($opera == "save" && $codpro){

    if($mccm->existe($codpro)){
        $msgccm  = "El código $codpro ya está registrado. Use la opción editar.";
        $tipoccm = "danger";
    } else {
        $mccm->setCodpro($codpro);
        $mccm->setNompro($nompro);
        $mccm->setDespro($despro);
        $mccm->setVerpro($verpro);
        $mccm->setHorlpro($horlpro);
        $mccm->setTippro(1083);
        $mccm->save();
        $msgccm  = "Curso complementario registrado correctamente.";
        $tipoccm = "success";
    }
}

// Actualización de un curso existente
if($opera == "edit" && $codpro){
    $mccm->setCodpro($codpro);
    $mccm->setNompro($nompro);
    $mccm->setDespro($despro);
    $mccm->setVerpro($verpro);
    $mccm->setHorlpro($horlpro);
    $mccm->setTippro(1083);
    $mccm->edit();
    $msgccm  = "Curso complementario actualizado correctamente.";
    $tipoccm = "success";
}

// Eliminación: se bloquea si el curso ya fue usado
if($opera == "eli" && $codpro){

    $usos = $mccm->vecesUsado($codpro);

    if($usos > 0){
        $msgccm  = "No se puede eliminar: el curso está siendo usado en $usos hoja(s) de trabajo.";
        $tipoccm = "danger";
    } else {
        $mccm->del();
        $msgccm  = "Curso complementario eliminado.";
        $tipoccm = "success";
        $codpro  = NULL;
    }
}

// Carga el registro en el formulario
if($opera == "edi" && $codpro){
    $datOne = $mccm->getOne();
}

$datAll = $mccm->getAll();

// Datos del tablero
$dash      = $mccm->getDashboard();
$topUsados = $mccm->getTopUsados(5);

$sinUsar = $dash['registrados'] - $dash['utilizados'];
$aprove  = $dash['registrados'] > 0
         ? round(($dash['utilizados'] / $dash['registrados']) * 100)
         : 0;
?>