<?php
require_once("models/hormrsl.php");
$mresu = new Hormrsl();

$pg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : 1528;
$idres = !empty($_REQUEST['idres']) ? trim($_REQUEST['idres']) : NULL;
$nomres = !empty($_POST['nomres']) ? trim($_POST['nomres']) : NULL;
$idcom = !empty($_POST['idcom']) ? trim($_POST['idcom']) : NULL;
$ndeses = (isset($_POST['ndeses']) && $_POST['ndeses'] !== '') ? (int)$_POST['ndeses'] : 1;

$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : (isset($_REQUEST['opera']) ? $_REQUEST['opera'] : NULL);
$ope2 = isset($_REQUEST['ope2']) ? $_REQUEST['ope2'] : NULL;
if ($ope === 'eli') { $ope = 'del'; }
if ($ope === 'save1') { $ope = 'save'; $ope2 = 'edit'; }

$mresu->setIdres($idres);

// 1. Guardar o Editar Resultado
if ($ope == "save") {
    if (!$idres || !$nomres || !$idcom) {
        echo "<script>window.location.href='home.php?pg={$pg}&msg=incompleto';</script>";
        exit();
    }

    $mresu->setNomres($nomres);
    $mresu->setIdcom($idcom);
    $mresu->setNdeses($ndeses);

    if ($ope2 == "edit") {
        $ok = $mresu->edit();
        if ($ok) {
            echo "<script>window.location.href='home.php?pg={$pg}&msg=editado';</script>";
        } else {
            $err = isset($_SESSION['last_crud_error']) ? urlencode($_SESSION['last_crud_error']) : 'error';
            echo "<script>window.location.href='home.php?pg={$pg}&msg=error_guardar&err={$err}';</script>";
        }
        exit();
    } else {
        if ($mresu->existeResultado($idres)) {
            echo "<script>window.location.href='home.php?pg={$pg}&msg=duplicado';</script>";
            exit();
        } else {
            $ok = $mresu->save();
            if ($ok) {
                echo "<script>window.location.href='home.php?pg={$pg}&msg=guardado';</script>";
            } else {
                $err = isset($_SESSION['last_crud_error']) ? urlencode($_SESSION['last_crud_error']) : 'error';
                echo "<script>window.location.href='home.php?pg={$pg}&msg=error_guardar&err={$err}';</script>";
            }
            exit();
        }
    }
}

// 2. Eliminar Resultado
if ($ope == "del" && $idres) {
    $ok = $mresu->del();
    $msg = $ok ? 'eliminado' : 'error_eliminar';
    echo "<script>window.location.href='home.php?pg={$pg}&msg={$msg}';</script>";
    exit();
}

// 3. Cargar datos para edición si aplica
if ($ope == "edi" && $idres) {
    $datOne = $mresu->getOne();
} else {
    $datOne = NULL;
}

// 4. Cargar listados y datos del Dashboard
$datAll = $mresu->getAllWithProgramas();
$dat = $datAll;
$dtComp = $mresu->getCompetenciasList();
$dtProg = $mresu->getProgramasList();
$resumen = $mresu->getResumenDashboard($datAll);
?>
