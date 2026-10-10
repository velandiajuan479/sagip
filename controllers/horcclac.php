<?php
require_once("models/hormclac.php");
require_once("models/hormacceso.php");

// Alcance por rol (módulo HyA) para la cinta de alcance de la vista.
$hacceso = new HormAcceso();
$scopeCtx = $hacceso->getContexto();
$scopeFichas = $hacceso->getFichasPermitidas($scopeCtx);
$scopeRol = $hacceso->etiquetaRol($scopeCtx);
$scopeN = count($scopeFichas);
$scopeTotal = !empty($scopeCtx['is_admin']);

$mclac = new Mclac();

$pg = $pg ?? '';

$idcla = (isset($_REQUEST["idcla"]) && $_REQUEST["idcla"] !== "") ? (int)$_REQUEST["idcla"] : null;
if ($idcla === 0) { $idcla = null; }

$titulo       = isset($_POST["titulo"]) ? trim($_POST["titulo"]) : null;
$descripcion  = isset($_POST["descripcion"]) ? trim($_POST["descripcion"]) : null;
$fecha_inicio = (isset($_POST["fecha_inicio"]) && $_POST["fecha_inicio"] !== "") ? $_POST["fecha_inicio"] : null;
$fecha_fin    = (isset($_POST["fecha_fin"]) && $_POST["fecha_fin"] !== "") ? $_POST["fecha_fin"] : null;
$tipo_evento  = isset($_POST["tipo_evento"]) ? trim($_POST["tipo_evento"]) : null;
$anio         = (isset($_POST["anio"]) && $_POST["anio"] !== "") ? (int)$_POST["anio"] : null;
$trimestre    = isset($_POST["trimestre"]) ? trim($_POST["trimestre"]) : null;
$opera        = isset($_REQUEST["opera"]) ? $_REQUEST["opera"] : null;

$datOne = null;
$datAll = [];

// Solo administrador del sistema (misma regla que Área)
$idper    = $_SESSION['idper'] ?? null;
$idpergen = $_SESSION['idpergen'] ?? null;
$isAdmin  = ($idper == 21 || $idper == 2 || $idpergen == 2);

if ($idcla !== null) {
    $mclac->setId($idcla);
}

if ($opera == "save") {
    if (!$isAdmin) {
        echo "<script>alert('Acceso denegado. Solo el administrador del sistema puede gestionar el calendario.'); window.location.href='home.php?pg=$pg';</script>";
        exit;
    }

    $mclac->setTitulo($titulo);
    $mclac->setDescripcion($descripcion);
    $mclac->setFechaInicio($fecha_inicio);
    $mclac->setFechaFin($fecha_fin);
    $mclac->setTipoEvento($tipo_evento);
    $mclac->setAnio($anio);
    $mclac->setTrimestre($trimestre);

    if ($idcla === null) {
        $mclac->save();
    } else {
        $mclac->edit();
    }

    $idcla  = null;
    $datOne = null;
}

if ($opera == "eli" && $idcla !== null) {
    if (!$isAdmin) {
        echo "<script>alert('Acceso denegado. Solo el administrador del sistema puede eliminar eventos del calendario.'); window.location.href='home.php?pg=$pg';</script>";
        exit;
    }

    $mclac->setId($idcla);

    if ($mclac->del()) {
        echo "<script>window.location.href='home.php?pg=$pg&msg=eliminado';</script>";
    } else {
        echo "<script>setTimeout(() => { window.location.href = 'home.php?pg=$pg'; }, 3000);</script>";
    }
    exit;
}

if ($opera == "edi" && $idcla !== null && $isAdmin) {
    $datOne = $mclac->getOne();
}

$datAll = $mclac->getAll();
$dtTot = $mclac->getTotales();
$totCal = isset($dtTot[0]['total']) ? (int)$dtTot[0]['total'] : 0;
$totVig = isset($dtTot[0]['vigentes']) ? (int)$dtTot[0]['vigentes'] : 0;
$totPas = isset($dtTot[0]['pasados']) ? (int)$dtTot[0]['pasados'] : 0;
$datAct = $mclac->getCalAct();
$datInact = $mclac->getCalInact();
?>