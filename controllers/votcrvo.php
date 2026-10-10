<?php
require_once 'models/votmrvo.php';

$votmrvo = new Votmrvo();

$fidcen = isset($_REQUEST['fidcen']) ? $_REQUEST['fidcen'] : 951310;
$fidjor = isset($_REQUEST['fidjor']) ? $_REQUEST['fidjor'] : 1;

$dcen = $votmrvo->getCen();
$djor = $votmrvo->getJor();

$dat = $votmrvo->selAll($fidjor, $fidcen);
?>
