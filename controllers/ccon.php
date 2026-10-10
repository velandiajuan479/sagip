<?php
require_once('models/mcon.php');

$mcon = new Mcon();

$idcof = isset($_REQUEST['idcof'])? $_REQUEST['idcof']:NULL;
$titcof = isset($_POST['titcof'])? $_POST['titcof']:NULL;
$descof = isset($_POST['descof'])? $_POST['descof']:NULL;
$logcof = isset($_POST['logcof'])? $_POST['logcof']:NULL;
$foocof = isset($_POST['foocof'])? $_POST['foocof']:NULL;

$ope = isset($_REQUEST['ope'])? $_REQUEST['ope']:NULL;
$datOne = NULL;
$foto = isset($_FILES['foto']['name']) ? $_FILES['foto']['name']:NULL;
if($foto){
    $logcof = opti($_FILES['foto'], $idcof, 'fotcen', "");
}

$mcon->setIdcof($idcof);
if($ope=="save"){
    if($titcof){
        $mcon->setIdcof($idcof);
        $mcon->setTitcof($titcof);
        $mcon->setDescof($descof);
        $mcon->setLogcof($logcof);
        $mcon->setFoocof($foocof);
		$mcon->edit();
	}else echo '<script>err("Todos los datos son obligatorios.");</script>';
}

$datOne = $mcon->getOne();
?>
