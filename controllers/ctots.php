<?php
require_once 'models/mtots.php';

$mtots = new Mtots();


function totFics() {
    $mtots = new Mtots();
    $res = $mtots->getTotfics();
    echo $res['COUNT(*)'];
}

function totUsus() {
    $mtots = new Mtots();
    $res = $mtots->getTotusus();
    echo $res['COUNT(*)'];
}

function usuForFics() {
    $mtots = new Mtots();
    $res = $mtots->getTotUsuForFic();

    $output = '';
    foreach ($res as $fila) {
        $output .= '<p># ' . $fila['idfic'] . ' : ' . $fila['usuarios_por_ficha'] . '</p>';
    }
    return $output;
}


function usuForFicsId($idUsuario) {
    $mtots = new Mtots();
    $res = $mtots->getTotUsuForFicId($idUsuario);

    $output = '';
    foreach ($res as $fila) {
        $output .= '<p>';
        $output .= '# ' . $fila['idfic'] . ' : ' . $fila['usuarios_por_ficha'];
        $output .= '</p>';
    }
    return $output;
}


function cantUsuFichaById($idUsuario) {
    $mtots = new Mtots();
    return $mtots->getCantUsuFic($idUsuario);
}



function totInst() {
    $mtots = new Mtots();
    return $mtots->getTotInst();
}


function totEle() {
    $mtots = new Mtots();
    return $mtots->getTotEle();  
}

function totPresEle() {
    $mtots = new Mtots();
    return $mtots->getTotPresEle();  
}

function getCantUsuPer() {
    $mtots = new Mtots();
    return $mtots->getCantUsuPer();  
}

function getCantUsuPerJor() {
    $mtots = new Mtots();
    return $mtots->getCantUsuPerJor();  
}

function getVotTot() {
    $idusu = $_SESSION['idusu']; 
    $mtots = new Mtots();
    return $mtots->getVotTot($idusu);  
}

function getCantIna() {
    $mtots = new Mtots();
    return $mtots->getCantIna();
}

function getCantBit() {
    $mtots = new Mtots();
    return $mtots->getCantBi();
}

function getTotAproApro() {
    $mtots = new Mtots();
    return $mtots->getTotAproApro();
}


function getTotalApro() {
    $mtots = new Mtots();
    return $mtots->getTotalApro();
}


function getTotalAspirantes() {
    $mtots = new Mtots();
    return $mtots->getTotalAspirantes();
}


function getPersonasDocumentosCompletos() {
    $mtots = new Mtots();
    return $mtots->getPersonasDocumentosCompletos();
}


function getPersonasFaltanDocumentos() {
    $mtots = new Mtots();
    return $mtots->getPersonasFaltanDocumentos();
}

function getTotalSoportesSolicitados() {
    $mtots = new Mtots();
    return $mtots->getTotalSoportesSolicitados();
}

function getTotalSoportesSolucionados() {
    $mtots = new Mtots();
    return $mtots->getTotalSoportesSolucionados();
}

?>