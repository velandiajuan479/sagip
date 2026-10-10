<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . '/../models/mesmestp.php');
$mesmestp = new Mesmestp();

$idusu_session = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : NULL;
$idsop = isset($_REQUEST['idsop']) ? $_REQUEST['idsop'] : NULL;

$dtOne = NULL;

$mesmestp->setIdsop($idsop);

// Empleados/personal para alimentar el selector de la vista
$dtEmpleados = $mesmestp->getSelSop();

if (isset($_GET['grafico'])) {
    header('Content-Type: application/json');
    $filter = isset($_GET['filter']) ? $_GET['filter'] : null;
    $empleado = isset($_GET['empleado']) && $_GET['empleado'] !== '' ? $_GET['empleado'] : null;

    // Si se especifica un empleado, se aplica el filtro sobre el modelo
    if ($empleado) {
        $mesmestp->setIdusu($empleado);
    }

    switch ($_GET['grafico']) {
        case 'por_falla':
            echo json_encode($mesmestp->getSoporteFinalizadoPorFallaPer($filter));
            break;
        case 'por_cargo':
            echo json_encode($mesmestp->getSoportePorCargoPer($filter));
            break;
        case 'por_usuario':
            echo json_encode($mesmestp->getSoportePorUsuarioPer($filter));
            break;
        default:
            echo json_encode([]);
    }
    exit;
}

$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : NULL;
if ($ope == "del" && $idsop) $mesmestp->del();
if ($ope == "edit" && $idsop){
    $dtOne = $mesmestp->getOne();
} else {
    $dtOne = NULL;
}

$dtRppr = $mesmestp->getReporteEstPer();
?>