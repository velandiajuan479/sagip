<?php
require_once 'models/votmdpe.php';
require_once 'models/conexion.php';

// Inicializar variables antes de su uso
$datOne = null;
$yaVotoVocero = false;
$yaVotoRepresentante = false;
$votacionAbierta = true;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$idusu = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : null;

$mdpe = new Mdpe();

// Verificar estado general del módulo de votaciones (modulo 1)
$conexion = new conexion();
$modVotacion = $conexion->getOneModV();
if (!empty($modVotacion) && isset($modVotacion[0]['actmod'])) {
    $votacionAbierta = ($modVotacion[0]['actmod'] == 1);
}

if ($idusu) {
    $mdpe->setIdusu($idusu);

    // Ejecutar consulta para obtener los datos del usuario
    $datOne = $mdpe->selOne();

    // Verifica votos independientemente usando metodos de Mdpe
    $yaVotoVocero = $mdpe->yaVotoVocero($idusu);      
    $yaVotoRepresentante = $mdpe->yaVotoRepresentante($idusu);
}
?>