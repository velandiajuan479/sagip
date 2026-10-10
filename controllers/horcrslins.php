<?php
// controllers/horcrslins.php
require_once 'models/hormrslins.php';
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

$modelo = new Hormrslins();

// Parámetros de filtrado
$idusu = isset($_REQUEST['idusu']) && $_REQUEST['idusu'] !== '' ? intval($_REQUEST['idusu']) : null;
$idfic = isset($_REQUEST['idfic']) && $_REQUEST['idfic'] !== '' ? trim($_REQUEST['idfic']) : null;
$pg    = $_REQUEST['pg'] ?? 1513;

// Si es instructor y no especificó otro, filtrar por sí mismo
if ($esInstructor && empty($idusu)) {
    $idusu = $idusu_actual;
}

// Lista de instructores para el select
$dtInstructores = $modelo->getInstructores();


// Datos condicionales según el instructor seleccionado
$dtInstructorInfo = null;
$dtFichas         = [];
$dtPrg            = [];
$dtComRes         = [];

if ($idusu) {
    $dtInstructorInfo = $modelo->getInstructorInfo($idusu);
    $dtFichas         = $modelo->getFichasPorInstructor($idusu);
    
    // Si hay ficha seleccionada, obtener información institucional del programa
    if ($idfic) {
        $dtPrg = $modelo->getFichaPrograma($idfic);
    }
    
    // Resultados de aprendizaje y competencias vinculadas
    $dtComRes = $modelo->getResultadosPorInstructorId($idusu, $idfic);
}

// Estadísticas para el dashboard superior
$kpi = $modelo->getEstadisticas($idusu, $idfic);
?>
