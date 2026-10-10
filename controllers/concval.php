<?php
require_once "models/conmval.php";
require_once "models/conmdom.php";

$mval = new Mval();
$mdom = new conMdom();

$idval  = isset($_REQUEST['idval']) ? $_REQUEST['idval'] : NULL;
$nomval = isset($_POST['nomval']) ? trim($_POST['nomval']) : NULL;
$iddom  = isset($_POST['iddom']) ? $_POST['iddom'] : NULL;
$parval = isset($_POST['parval']) ? trim($_POST['parval']) : NULL;
$act    = isset($_REQUEST['act']) ? $_REQUEST['act'] : NULL;

$opera   = isset($_REQUEST['opera']) ? $_REQUEST['opera'] : NULL;
$datOne  = NULL;

$mval->setIdval($idval);


if ($opera == "save") {
    $mval->setIddom($iddom);
    $mval->setNomval($nomval);
    $mval->setParval($parval);
    $mval->setAct($act);
    
    if (!$idval) {
        $mval->save();
    } else {
        $mval->edit();
    }
}


if ($opera == "acti" && $idval && $act) {
    $mval->setAct($act);
    $mval->editAct();
}

// 3. OPERACIÓN: ELIMINAR
if ($opera == "eli" && $idval) {
    $mval->del();
    echo "<script>window.location.href='home.php?pg=$pg&msg=eliminado';</script>";
    exit;
}


if ($opera == "edi" && $idval) {
    $datOne = $mval->getOne();
}


$datAll = $mval->getAll();
$datDom = $mdom->getAll();


$stats = $mval->getStats();
$chartDominios = $mval->getValoresPorDominio();


$labelsDom = array_column($chartDominios, 'nomdom');
$dataDom   = array_column($chartDominios, 'cantidad');


require_once "views/convval.php";
?>