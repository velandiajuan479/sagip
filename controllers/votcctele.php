<?php
require_once 'models/votmctele.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['idusu']) || !isset($_SESSION['idper'])) {
    header("Location: ../index.php?error=sesion");
    exit();
}

$votmctele = new Votmctele();

$idusu = isset($_REQUEST['idusu']) ? $_REQUEST['idusu'] : NULL;
$fidcen = isset($_SESSION['idcen']) ? $_SESSION['idcen'] : NULL;
$tipo_voto = isset($_REQUEST['tipo_voto']) ? $_REQUEST['tipo_voto'] : 'representante';
$ver_resultados = isset($_REQUEST['ver_resultados']) ? true : false;

$opera = isset($_REQUEST['opera']) ? $_REQUEST['opera'] : NULL;
$pg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : 1200;

$datRep = NULL;
$datVoc = NULL;
$mensaje = '';
$tipoMensaje = '';
$votoRep = NULL;
$votoVoc = NULL;
$resultadosRep = NULL;
$resultadosVoc = NULL;
$periodoVotacion = false;
$configCentro = NULL;

if ($fidcen) {
    $configCentro = $votmctele->getConfiguracionCentro($fidcen);
    $periodoVotacion = $votmctele->verificarPeriodoVotacion($fidcen, 'votacion');
}

$datRep = $votmctele->getCandidatosRepresentante();
$datVoc = $votmctele->getCandidatosVocero();

if ($idusu) {
    $votoRep = $votmctele->getVotoUsuario($idusu, 'representante');
    $votoVoc = $votmctele->getVotoUsuario($idusu, 'vocero');
}

// Lemas de campaña informativos para las tarjetas del cartón electoral
$lemas = array(
    'Compromiso, liderazgo y trabajo en equipo al servicio de los aprendices.',
    'Una voz fuerte que representa a todos ante el centro de formación.',
    'Iniciativa, diálogo y gestión para mejorar la calidad de vida académica.',
    'Por una comunidad de aprendices más unida, participativa y visible.',
    'Escucha activa, propuestas claras y resultados que beneficien a todos.',
    'Transparencia, cercanía y dedicación al servicio de los aprendices.'
);

$datRepTar = array();
if ($datRep) {
    foreach ($datRep as $r) {
        $esBlanco = ($r['noca'] == '999' || stripos($r['nomusu'], 'BLANCO') !== false);
        $datRepTar[] = array_merge($r, array(
            'esblanco' => $esBlanco,
            'lema' => $esBlanco
                ? 'Opción legítima para manifestar que ninguna candidatura cuenta con tu respaldo.'
                : $lemas[array_rand($lemas)]
        ));
    }
}
// Opción institucional de voto en blanco para el cartón de representantes
$datRepTar[] = array(
    'idusu' => 999,
    'nomusu' => 'VOTO EN BLANCO',
    'noca' => '999',
    'fotcan' => '',
    'idfic' => '',
    'nomfic' => '',
    'esblanco' => true,
    'lema' => 'Opción legítima para manifestar que ninguna candidatura cuenta con tu respaldo.'
);

$datVocTar = array();
if ($datVoc) {
    foreach ($datVoc as $v) {
        $esBlanco = ($v['noca'] == '999' || stripos($v['nomusu'], 'BLANCO') !== false);
        $datVocTar[] = array_merge($v, array(
            'esblanco' => $esBlanco,
            'lema' => $esBlanco
                ? 'Opción legítima para manifestar que ninguna candidatura cuenta con tu respaldo.'
                : $lemas[array_rand($lemas)]
        ));
    }
}
// Opción institucional de voto en blanco para el cartón de voceros
$datVocTar[] = array(
    'idusu' => 999,
    'nomusu' => 'VOTO EN BLANCO',
    'noca' => '999',
    'fotcan' => '',
    'idfic' => '',
    'nomfic' => '',
    'esblanco' => true,
    'lema' => 'Opción legítima para manifestar que ninguna candidatura cuenta con tu respaldo.'
);

if ($ver_resultados) {
    $resultadosRep = $votmctele->getResultadosGenerales('representante', $fidcen);
    $resultadosVoc = $votmctele->getResultadosGenerales('vocero', $fidcen);
}