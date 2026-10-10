<?php
require_once 'models/etamgeins.php';

$pg      = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : 1102;
$idusu   = isset($_REQUEST['idusu']) ? $_REQUEST['idusu'] : null;
$idficha = isset($_REQUEST['idficha']) ? $_REQUEST['idficha'] : null;
$ope     = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : null;

$etamgeins = new Etamgeins();

if ($idusu)   $etamgeins->setIdusu($idusu);
if ($idficha) $etamgeins->setIdficha($idficha);

// 1. Convertir instructor a instructor de seguimiento
if ($ope == "convertir" && $idusu) {
    if ($etamgeins->convertirASeguimiento()) {
        echo "<script>alert('Instructor convertido exitosamente'); window.location.href='home.php?pg={$pg}';</script>";
    } else {
        echo "<script>alert('Error al convertir el instructor'); window.location.href='home.php?pg={$pg}';</script>";
    }
    exit;
}

// 2. Asignar Ficha Completa
if ($ope == "asigfi") {
    if (empty($idusu)) {
        echo "<script>alert('Por favor, seleccione un instructor'); window.location.href='home.php?pg={$pg}';</script>";
        exit;
    }
    if (empty($idficha)) {
        echo "<script>alert('Seleccione una ficha'); window.location.href='home.php?pg={$pg}';</script>";
        exit;
    }
    if ($etamgeins->existeAsignacionFicha()) {
        echo "<script>alert('Esta ficha ya está asignada a un instructor'); window.location.href='home.php?pg={$pg}';</script>";
        exit;
    }
    if ($etamgeins->asignarFicha()) {
        echo "<script>alert('Ficha asignada exitosamente'); window.location.href='home.php?pg={$pg}';</script>";
    } else {
        echo "<script>alert('Error al asignar la ficha'); window.location.href='home.php?pg={$pg}';</script>";
    }
    exit;
}

// 3. Eliminar Instructor de Seguimiento (desde la tabla principal)
if ($ope === 'eliminarInst' && !empty($idusu)) {
    if ($etamgeins->eliminarInstructorSeg()) {
        echo "<script>alert('Instructor de seguimiento eliminado correctamente'); window.location.href='home.php?pg={$pg}';</script>";
    } else {
        echo "<script>alert('Error al eliminar el instructor de seguimiento'); window.location.href='home.php?pg={$pg}';</script>";
    }
    exit;
}

// 4. Eliminar una ficha específica de un instructor (desde el modal)
if ($ope === 'eli' && $idusu && $idficha) {
    if ($etamgeins->eliminarInsSeg()) {
        echo "<script>alert('Asignación eliminada exitosamente'); window.location.href='home.php?pg={$pg}&idusu={$idusu}&ope=modal';</script>";
    } else {
        echo "<script>alert('Error al eliminar el registro'); window.location.href='home.php?pg={$pg}&idusu={$idusu}&ope=modal';</script>";
    }
    exit;
}

// --- Consultas para la vista ---
$datInst            = $etamgeins->getInst();
$datInstSegCompleto = $etamgeins->getInstSegCompleto();
$datFicDisp         = $etamgeins->getFichasDisponibles();

// Métricas de tarjetas KPI
$totInsSeg  = $etamgeins->getTotInsSeg();
$totInsNseg = $etamgeins->getTotInsNSeg();
$totFic     = $etamgeins->getTotFic();

// Agrupar instructores de seguimiento por fichas asignadas
$instructoresConFichas = [];
$instructoresSinFichas = [];

foreach ($datInstSegCompleto as $inst) {
    $etamgeins->setIdusu($inst['idusu']);
    $inst['fichas'] = $etamgeins->getFichasPorInstructor();

    if (!empty($inst['fichas'])) {
        $instructoresConFichas[] = $inst;
    } else {
        $instructoresSinFichas[] = $inst;
    }
}

// Lista completa (con + sin fichas), ordenada alfabéticamente por nombre
$instructores = array_merge($instructoresConFichas, $instructoresSinFichas);
usort($instructores, function($a, $b) {
    return strcasecmp($a['nomusu'], $b['nomusu']);
});

// Contadores para las píldoras
$totConFichas = count($instructoresConFichas);
$totSinFichas = count($instructoresSinFichas);
$totTodos     = count($instructores);

// Lógica del modal de detalle
$detAsig = NULL;
$detAsigAgrupado = NULL;
if ($idusu && $ope === "modal") {
    $etamgeins->setIdusu($idusu);
    $detAsig = $etamgeins->getDetAsigInst();
    $detAsigAgrupado = $etamgeins->getDetAsigInstAgrupado();
}