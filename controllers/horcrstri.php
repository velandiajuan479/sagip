<?php
// controllers/horcrstri.php
require_once 'models/hormrstri.php';
require_once 'models/hormacceso.php';

// Alcance por rol (módulo HyA) para la cinta de alcance de la vista.
$hacceso = new HormAcceso();
$scopeCtx = $hacceso->getContexto();
$scopeFichas = $hacceso->getFichasPermitidas($scopeCtx);
$scopeRol = $hacceso->etiquetaRol($scopeCtx);
$scopeN = count($scopeFichas);
$scopeTotal = !empty($scopeCtx['is_admin']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$idper_actual = (int)($_SESSION['idper'] ?? ($_SESSION['idpergen'] ?? 0));
$idusu_actual = (int)($_SESSION['idusu'] ?? 0);
$esAdmin = in_array($idper_actual, [1, 2, 21, 28]);
$esInstructor = in_array($idper_actual, [7, 32, 42]);
$esAprendiz = in_array($idper_actual, [4, 8, 22, 23, 39, 41]);

$mhormrstri = new Hormrstri();

// Parámetros de petición
$idfic     = isset($_REQUEST['idfic']) && $_REQUEST['idfic'] !== '' ? trim($_REQUEST['idfic']) : null;
$trimestre = isset($_REQUEST['trimestre']) && $_REQUEST['trimestre'] !== '' ? intval($_REQUEST['trimestre']) : null;
$pg        = $_REQUEST['pg'] ?? 1548;

// Listado de fichas para el selector
$dtFichas = $mhormrstri->getFichas();


// Si no viene ficha seleccionada pero hay fichas, podemos tomar la primera si el usuario viene de un acceso directo o dejar que seleccione
$dtPrg          = [];
$dtFechas       = [];
$trimestresList = [];
$dtComRes       = [];

if ($idfic) {
    $mhormrstri->setIdfic($idfic);
    $dtPrg          = $mhormrstri->getFichaPrograma($idfic);
    $dtFechas       = $mhormrstri->getFechasFicha($idfic);
    $trimestresList = $mhormrstri->getTrimestresCalculados($idfic);
    $dtComRes       = $mhormrstri->getCompetenciasResultados($trimestre);
}

// Estadísticas para el dashboard superior
$kpi = $mhormrstri->getEstadisticas($idfic, $trimestre);
?>
