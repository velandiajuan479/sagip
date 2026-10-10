<?php
require_once("../models/conexion.php");
require_once("../models/mvidgen.php");
$mvidgen = new mVidgen();
$datAll = $mvidgen->getVidAct();
header('Content-Type: application/json; charset=utf-8');
$json = json_encode($datAll);
if ($json === false) {
    echo 'Error al codificar JSON: ' . json_last_error_msg();
} else {
    echo $json;
}
?>