<?php
require_once 'models/votmrcv.php';

$votmrcv = new Votmrcv();
$idusu = isset($_REQUEST['idusu']) ? $_REQUEST['idusu'] : NULL;
$fidjor = isset($_REQUEST['fidjor']) ? $_REQUEST['fidjor'] : NULL;
$fidfic = isset($_REQUEST['fidfic']) ? $_REQUEST['fidfic'] : NULL;

// Obtiene todas las jornadas para el primer selector
$jornadas = $votmrcv->getJor();

// obtiene las fichas, filtrando por la jornada si se ha seleccionado alguna
$fichas = $votmrcv->getFichas($fidjor);

// obtiene informacion de la ficha seleccionada
$fichaActual = [];
$dat = [];
if($fidfic) {
    $fichaActual = current(array_filter($fichas, function($f) use ($fidfic) {
        return $f['idfic'] == $fidfic;
    }));
    
    // obtiene voceros elegidos de la ficha seleccionada
    $dat = $votmrcv->getVocerosElectosPorFicha($fidfic);

    // Opcional: El arreglo ya viene ordenado por total_votos DESC desde el modelo
    // El índice 0 será el Vocero, y el índice 1 será el Suplente.
}
?>
