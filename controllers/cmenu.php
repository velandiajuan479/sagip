<?php
include('models/mmenu.php');

$mmenu = new Mmenu();
$mmenu->setIdper($_SESSION['idper']);
$dat = $mmenu->getMen();

function validar($idpag){
    $mmenu = new Mmenu();
    $mmenu->setIdpag($idpag);
    $mmenu->setIdper($_SESSION['idper']);
    $dat = $mmenu->getVal();
    return $dat;
}
?>