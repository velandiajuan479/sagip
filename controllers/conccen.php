<?php
require_once('models/conmcen.php');

$mcen = new Mcen();

$idcen = isset($_REQUEST['idcen'])? $_REQUEST['idcen']:NULL;
$nomcen = isset($_POST['nomcen'])? $_POST['nomcen']:NULL;
$dircen = isset($_POST['dircen'])? $_POST['dircen']:NULL;
$telcen = isset($_POST['telcen'])? $_POST['telcen']:NULL;
$imgcen = isset($_POST['imgcen'])? $_POST['imgcen']:NULL;
$logocen = isset($_POST['logocen'])? $_POST['logocen']:NULL;
$descen = isset($_POST['descen'])? $_POST['descen']:NULL;
$fiicancen = isset($_POST['fiicancen'])? $_POST['fiicancen']:NULL;
$fficancen = isset($_POST['fficancen'])? $_POST['fficancen']:NULL;
$fiprocen = isset($_POST['fiprocen'])? $_POST['fiprocen']:NULL;
$ffprocen = isset($_POST['ffprocen'])? $_POST['ffprocen']:NULL;
$fivotcen = isset($_POST['fivotcen'])? $_POST['fivotcen']:NULL;
$ffvotcen = isset($_POST['ffvotcen'])? $_POST['ffvotcen']:NULL;

$opera = isset($_REQUEST['opera'])? $_REQUEST['opera']:NULL;
$ope = isset($_REQUEST['ope'])? $_REQUEST['ope']:NULL;
$datOne = NULL;

// Subida de la foto del centro y del logo del SENA (se guardan en la carpeta img/)
$foto = isset($_FILES['foto']['name']) ? $_FILES['foto']['name']:NULL;
if($foto){
    $imgcen = opti($_FILES['foto'], $idcen, 'img', 'cen');
}
$logo = isset($_FILES['logo']['name']) ? $_FILES['logo']['name']:NULL;
if($logo){
    $logocen = opti($_FILES['logo'], $idcen, 'img', 'logcen');
}

$mcen->setIdcen($idcen);
if($opera=="save"){
    if($nomcen){
        $mcen->setIdcen($idcen);
        $mcen->setNomcen($nomcen);
        $mcen->setDircen($dircen);
        $mcen->setTelcen($telcen);
        $mcen->setImgcen($imgcen);
        $mcen->setLogocen($logocen);
        $mcen->setDescen($descen);
        $mcen->setFiicancen($fiicancen);
        $mcen->setFficancen($fficancen);
        $mcen->setFiprocen($fiprocen);
        $mcen->setFfprocen($ffprocen);
        $mcen->setFivotcen($fivotcen);
        $mcen->setFfvotcen($ffvotcen);
        $existe = $idcen ? $mcen->getOne() : array();
        if(!$existe){
            $mcen->save();
            echo '<div class="d-none" data-notify="success" data-msg="Centro creado correctamente."></div>';
        }else{
            $mcen->edit();
            echo '<div class="d-none" data-notify="success" data-msg="Cambios guardados correctamente."></div>';
        }
	}else echo '<div class="d-none" data-notify="error" data-msg="Todos los datos son obligatorios."></div>';
}

if($opera=="eli" && $idcen){
    if($mcen->del()){
        echo "<script>window.location.href='home.php?pg=$pg&msg=eliminado';</script>";
    }else{
        echo '<script>err("No se pudo eliminar el centro. Está relacionado con otros registros.");</script>';
    }
    exit;
}
if($ope=="edi" && $idcen) $datOne = $mcen->getOne();

//MOSTRAR TODOS LOS DATOS
$datAll = $mcen->getAll();

// Totales del dashboard (centros, asociados, candidatos)
$datTot = $mcen->getTotales();
$totCen  = isset($datTot[0]['total_cen']) ? (int)$datTot[0]['total_cen'] : 0;
$totAsoc = isset($datTot[0]['total_usu']) ? (int)$datTot[0]['total_usu'] : 0;
$totFic  = isset($datTot[0]['total_fic']) ? (int)$datTot[0]['total_fic'] : 0;
$totAul  = isset($datTot[0]['total_aul']) ? (int)$datTot[0]['total_aul'] : 0;
$totCand = isset($datTot[0]['total_can']) ? (int)$datTot[0]['total_can'] : 0;
?>