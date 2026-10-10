<?php 
require_once ('models/conmdom.php');

$mdom = new conMdom();

$iddom  = isset($_REQUEST['iddom']) ? $_REQUEST['iddom'] : NULL;
$nomdom = isset($_POST['nomdom']) ? trim($_POST['nomdom']) : NULL;
$opera  = isset($_REQUEST['opera']) ? $_REQUEST['opera'] : NULL;

$datOne = NULL;

$mdom->setIddom($iddom);

// GUARDAR / EDITAR
if($opera=="save"){
    if($nomdom){
        $mdom->setNomdom($nomdom);
        if(!$iddom) $mdom->save(); else $mdom->edit();
    } else {
        echo '<script>err("Todos los datos son obligatorios.");</script>';
    }
}

// ELIMINAR
if($opera=="eli" && $iddom){
    $mdom->del();
    echo "<script>window.location.href='home.php?pg=$pg&msg=eliminado';</script>";
    exit;
}

// EDITAR (OBTENER REGISTRO)
if($opera=="edi" && $iddom) {
    $datOne = $mdom->getOne();
}

// CONSULTA GENERAL
$dat = $mdom->getAll();

// CONSULTAS PARA EL DASHBOARD
$stats = $mdom->getStats();
$topDominios = $mdom->getTopDominiosConValores();

$labelsDom = array_column($topDominios, 'nomdom');
$dataDom   = array_column($topDominios, 'cantidad');
?>