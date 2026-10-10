<?php 
require_once 'models/mmod.php';

$mmod = new Mmod();

$idmod=isset($_REQUEST['idmod']) ? $_REQUEST['idmod']:NULL;
$nommod=isset($_POST['nommod']) ? $_POST['nommod']:NULL;
$imgmod=isset($_POST['imgmod']) ? $_POST['imgmod']:NULL;
$actmod=isset($_REQUEST['actmod']) ? $_REQUEST['actmod']:NULL;
$desmod=isset($_POST['desmod']) ? $_POST['desmod']:NULL;
$idper=isset($_POST['idper']) ? $_POST['idper']:NULL;
$opera=isset($_REQUEST['opera']) ? $_REQUEST['opera']:NULL;

$foto = isset($_FILES['foto']['name']) ? $_FILES['foto']['name']:NULL;
if($foto){
    $imgmod = opti($_FILES['foto'], $nommod, 'img', "");
}

$pg = 1120;
$datOne = NULL;

$mmod->setIdmod($idmod);

if($opera=="Insertar"){
    $mmod->setNommod($nommod);
    $mmod->setImgmod($imgmod);
    $mmod->setActmod($actmod);
    $mmod->setIdper($idper);
    if(!$idmod && $nommod) $mmod->ins();else $mmod->upd();
    $idmod="";
}
//ACTUALIZAR
if($opera=="Actualizar"){
    if($idmod AND $nommod AND $actmod){
        $mmod->setNommod($nommod);
        $mmod->setImgmod($imgmod);
        $mmod->setActmod($actmod);
        $mmod->setIdper($idper);
        $mmod->upd();
    }
    $idmod="";
}
//ACTIVO
if($opera=="acti"){
    $mmod->setActmod($actmod);
    $mmod->editAct();
    $idmod="";
}
//ELIMINAR
    if($opera=="Eliminar"){
    try{
        if($idmod){
            $mmod->del();
        }
    }catch (Exception $e){
        echo "<script> alert('El modulo no se puede eliminar. Comuníquese con el administrador del sistema.')</script>";
        }
            $idmod="";
    }

//MOSTRAR TODOS LOS datos
$datPer = $mmod->getPerfil();
$dat = $mmod->getAll();
$datAct = $mmod->getAllAct();
$datMos = $mmod->getAllMos();
if($idmod) $datOne = $mmod->getOne();
?>