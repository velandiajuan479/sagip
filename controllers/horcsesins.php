<?php 
include("models/hormsesins.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$idper_actual = (int)($_SESSION['idper'] ?? ($_SESSION['idpergen'] ?? 0));
$idusu_actual = (int)($_SESSION['idusu'] ?? 0);
$esAdmin = in_array($idper_actual, [1, 2, 21, 28]);
$esInstructor = in_array($idper_actual, [7, 32, 42]);
$esAprendiz = in_array($idper_actual, [4, 8, 22, 23, 39, 41]);

$idses = isset($_REQUEST['idses']) ? $_REQUEST['idses']:NULL;
$idage = isset($_POST['idage']) ? $_POST['idage']:NULL;
$fecses = isset($_POST['fecses']) ? $_POST['fecses']:NULL;
$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope']:NULL;

$horcsesins=new Hormsesins();

// Solo admin o instructor pueden crear, editar o eliminar sesiones
if (!$esAprendiz) {
    if ($ope=="save") {
        $horcsesins->setIdage($idage);
        $horcsesins->setFecses($fecses);
        if($idses) {
            $horcsesins->setIdses($idses);
            $horcsesins->edit();
        }
        else {
            $nuevoId = $horcsesins->getNextId();
            $horcsesins->setIdses($nuevoId);
            $horcsesins->save();
        }
    }

    if ($idses) $horcsesins->setIdses($idses);
    if ($ope=="del" && $idses) $horcsesins->del();
}

$m=2;
if (!$esAprendiz && $ope=="edit" && $idses){
    $horcsesins->setIdses($idses);
    $dtOne = $horcsesins->getOne();
    $m=1;
}else{ 
    $dtOne=NULL;
}

$filtroIdusu = $esAdmin ? null : $idusu_actual;

// Alcance por rol (módulo HyA): sesiones/agendas de las fichas permitidas.
// (Antes se pasaba el idusu como idfic, lo que devolvía datos cruzados.)
require_once 'models/hormacceso.php';
$hacceso = new HormAcceso();
$scopeCtx = $hacceso->getContexto();
$scopeFichas = $hacceso->getFichasPermitidas($scopeCtx);
$scopeRol = $hacceso->etiquetaRol($scopeCtx);
$scopeN = count($scopeFichas);
$scopeTotal = !empty($scopeCtx['is_admin']);
$idsFichas = [];
foreach ($scopeFichas as $sf) { $idsFichas[] = $sf['idfic']; }
$dat = $esAdmin ? $horcsesins->getAllIn([]) : $horcsesins->getAllIn($idsFichas);
$dage = $esAdmin ? $horcsesins->getAgeIn([]) : $horcsesins->getAgeIn($idsFichas);
?>