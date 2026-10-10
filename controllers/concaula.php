<?php
require_once ("models/conmaula.php");

$maula = new Maula();

$idaul = isset($_REQUEST['idaul']) ? $_REQUEST['idaul'] : NULL;
$nomaul = isset($_POST['nomaul']) ? $_POST['nomaul'] : NULL;
$piso = isset($_POST['piso']) ? $_POST['piso'] : NULL;
$bloqau = isset($_POST['bloqau']) ? $_POST['bloqau'] : NULL;
$taula = isset($_POST['taula']) ? $_POST['taula'] : NULL;
$esaula = isset($_POST['esaula']) ? $_POST['esaula'] : 1;
$desaula = isset($_POST['desaula']) ? $_POST['desaula']:NULL;
$tipaul = isset($_POST['tipaul']) ? $_POST['tipaul']:NULL;
$idcen = isset($_SESSION['idcen']) ? $_SESSION['idcen']:NULL;
$codubi = isset($_SESSION['codubi']) ? $_SESSION['codubi']:NULL;

$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : NULL;
$datOne = NULL;

$maula -> setIdaul($idaul);
if ($ope == "save") {
    $maula->setNomaul($nomaul);
    $maula->setPiso($piso);
    $maula->setBloqau($bloqau);
    $maula->setTaula($taula);
    $maula->setEsaula($esaula);
    $maula->setDesaula($desaula);
    $maula->setTipaul($tipaul);
    $maula->setIdcen($idcen);
    $maula->setCodubi($codubi);
    if (!$idaul) $maula->save();
    else $maula->edit();
}

if ($ope == "eli" && $idaul){ 
    $maula->del();

    echo '<div class="d-none" data-redir="home.php?pg=' . $pg . '&msg=eliminado"></div>';
    exit;
}

if ($ope == "edi" && $idaul) $datOne = $maula->getOne();

$maula->setPiso(null);
$maula->setBloqau(null);
$dtBloque = $maula->getAllBloque();
$dtPiso = $maula->getAllPiso();
$dtStat = $maula->getStats();
$dtArea = $maula->getAreas();
?> 